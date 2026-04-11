<?php
/**
 * GetBlitz Payment Gateway Class
 */

defined('ABSPATH') || exit;

class WC_Gateway_GetBlitz extends WC_Payment_Gateway {

    /**
     * @var string
     */
    public $api_key;

    /**
     * @var string
     */
    public $webhook_secret;

    /**
     * @var string
     */
    public $api_url;

    /**
     * @var string
     */
    public $wss_url;

    public function __construct() {
        $this->id                 = 'getblitz';
        $this->icon               = plugins_url('assets/images/logo.png', dirname(__DIR__) . '/getblitz-payment-gateway.php');
        $this->has_fields         = false;
        $this->method_title       = __('GetBlitz SEPA Instant', 'getblitz-payment-gateway');
        $this->method_description = __('Accept SEPA Instant Transfers securely via GetBlitz.', 'getblitz-payment-gateway');
        
        $this->init_form_fields();
        $this->init_settings();

        $this->title          = $this->get_option('title');
        $this->description    = $this->get_option('description');
        $this->api_key        = $this->get_option('api_key');
        $this->webhook_secret = $this->get_option('webhook_secret');
        $this->api_url        = rtrim($this->get_option('api_url', 'https://app.getblitz.io'), '/');
        $this->wss_url        = rtrim($this->get_option('wss_url', 'wss://app.getblitz.io'), '/');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        
        // WooCommerce API hook for webhook processing (e.g., SITE_URL/wc-api/wc_gateway_getblitz)
        add_action('woocommerce_api_wc_gateway_getblitz', array($this, 'webhook_handler'));
        add_action('woocommerce_api_wc_gateway_getblitz_verify', array($this, 'verify_handler'));
    }

    public function admin_options() {
        $webhook_url = add_query_arg('wc-api', 'wc_gateway_getblitz', home_url('/'));
        ?>
        <h2><?php echo esc_html($this->get_method_title()); ?></h2>
        <?php echo wp_kses_post(wpautop($this->get_method_description())); ?>

        <details style="background: #fff; border: 1px solid #c3c4c7; padding: 15px 20px; margin: 20px 0;">
            <summary style="font-size: 1.2em; font-weight: 600; cursor: pointer; outline: none; margin-bottom: 0;"><?php _e('Step-by-Step Configuration', 'getblitz-payment-gateway'); ?></summary>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
            <ol style="margin-left: 20px; list-style-type: decimal; line-height: 1.6;">
                <li style="margin-bottom: 20px;">
                    <strong><?php _e('Whitelist your Allowed Origin', 'getblitz-payment-gateway'); ?></strong><br>
                    <?php _e('The allowed origin determines where your GetBlitz payment widget can be loaded.', 'getblitz-payment-gateway'); ?><br>
                    <em><?php _e('Go to:', 'getblitz-payment-gateway'); ?> <strong>Getblitz &rarr; Settings &rarr; Allowed Origins &rarr; Add</strong></em><br>
                    <?php _e('Value to add:', 'getblitz-payment-gateway'); ?> 
                    <code id="getblitz-allowed-origin"><?php echo esc_url(home_url('/')); ?></code>
                    <a href="#" class="button button-small" onclick="event.preventDefault(); const btn = this; navigator.clipboard.writeText(document.getElementById('getblitz-allowed-origin').innerText).then(() => { btn.innerHTML = '&#10004;'; setTimeout(() => btn.innerHTML = '<?php echo esc_js(__('Copy Link', 'getblitz-payment-gateway')); ?>', 2000); });"><?php esc_html_e('Copy Link', 'getblitz-payment-gateway'); ?></a>
                </li>
                <li style="margin-bottom: 20px;">
                    <strong><?php _e('Configure the Webhook URL', 'getblitz-payment-gateway'); ?></strong><br>
                    <?php _e('We use webhooks to get real-time status updates on payments.', 'getblitz-payment-gateway'); ?><br>
                    <em><?php _e('Go to:', 'getblitz-payment-gateway'); ?> <strong>Getblitz &rarr; Settings &rarr; Webhooks &rarr; Add Webhook</strong></em><br>
                    <?php _e('URL to add:', 'getblitz-payment-gateway'); ?> 
                    <code id="getblitz-webhook-url"><?php echo esc_url($webhook_url); ?></code> 
                    <a href="#" class="button button-small" onclick="event.preventDefault(); const btn = this; navigator.clipboard.writeText(document.getElementById('getblitz-webhook-url').innerText).then(() => { btn.innerHTML = '&#10004;'; setTimeout(() => btn.innerHTML = '<?php echo esc_js(__('Copy Link', 'getblitz-payment-gateway')); ?>', 2000); });"><?php esc_html_e('Copy Link', 'getblitz-payment-gateway'); ?></a>
                    <br>
                    <span style="color: #d63638; font-size: 0.9em;"><?php _e('Important: Make sure to copy the Webhook Secret from the GetBlitz dashboard and paste it in the "Webhook Secret" field below.', 'getblitz-payment-gateway'); ?></span>
                </li>
                <li>
                    <strong><?php _e('Generate an API Key', 'getblitz-payment-gateway'); ?></strong><br>
                    <?php _e('The API key allows this plugin to communicate with GetBlitz.', 'getblitz-payment-gateway'); ?><br>
                    <em><?php _e('Go to:', 'getblitz-payment-gateway'); ?> <strong>Getblitz &rarr; Settings &rarr; API Keys &rarr; Generate New Key</strong></em><br>
                    <span style="font-size: 0.9em;"><?php _e('Paste the generated API Key in the "API Key" field below.', 'getblitz-payment-gateway'); ?></span>
                </li>
            </ol>
            </div>
        </details>

        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table>
        <?php
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __('Enable/Disable', 'getblitz-payment-gateway'),
                'type'        => 'checkbox',
                'label'       => __('Enable GetBlitz Payment', 'getblitz-payment-gateway'),
                'default'     => 'yes',
            ),
            'title' => array(
                'title'       => __('Title', 'getblitz-payment-gateway'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'getblitz-payment-gateway'),
                'default'     => __('GetBlitz SEPA Instant Transfer', 'getblitz-payment-gateway'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'getblitz-payment-gateway'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'getblitz-payment-gateway'),
                'default'     => __('Pay instantly from your bank account via SEPA Instant.', 'getblitz-payment-gateway'),
            ),
            'api_key' => array(
                'title'       => __('API Key', 'getblitz-payment-gateway'),
                'type'        => 'password',
                'description' => __('Your GetBlitz Organization API Key. Generate it at Getblitz -> Settings -> API Keys -> Generate New Key.', 'getblitz-payment-gateway'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'webhook_secret' => array(
                'title'       => __('Webhook Secret', 'getblitz-payment-gateway'),
                'type'        => 'password',
                'description' => __('The secret used to verify webhook signatures. Retrieve from Getblitz -> Settings -> Webhooks -> Add Webhook.', 'getblitz-payment-gateway'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'api_url' => array(
                'title'       => __('Base API URL', 'getblitz-payment-gateway'),
                'type'        => 'text',
                'description' => __('Base API URL for self-hosted instances. Defaults to https://app.getblitz.io', 'getblitz-payment-gateway'),
                'default'     => 'https://app.getblitz.io',
                'desc_tip'    => true,
            ),
            'wss_url' => array(
                'title'       => __('WebSocket URL', 'getblitz-payment-gateway'),
                'type'        => 'text',
                'description' => __('Optional WebSocket URL for real-time updates on self-hosted instances.', 'getblitz-payment-gateway'),
                'default'     => '',
                'desc_tip'    => true,
            ),
        );
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
    
        if (empty($this->api_key)) {
            wc_add_notice(__('GetBlitz arrangement error: Ensure API Key is configured.', 'getblitz-payment-gateway'), 'error');
            return;
        }

        $amount_cents = round($order->get_total() * 100);
        $currency = $order->get_currency();

        if ($currency !== 'EUR') {
            wc_add_notice(__('GetBlitz currently only supports EUR currency.', 'getblitz-payment-gateway'), 'error');
            return;
        }

        // POST /api/v1/challenge
        $url = $this->api_url . '/api/v1/challenge';
        
        $unique_merchant_ref = strval($order->get_id()) . '-' . time();

        $body = wp_json_encode(array(
            'amount'              => $amount_cents,
            'currency'            => $currency,
            'merchantReferenceId' => $unique_merchant_ref
        ));

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'body'    => $body,
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            error_log('GetBlitz API Error: ' . $response->get_error_message());
            wc_add_notice(__('An error occurred communicating with the payment gateway.', 'getblitz-payment-gateway'), 'error');
            return;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($response_code >= 400 || empty($data['sessionId']) || empty($data['clientToken'])) {
            error_log('GetBlitz Error Response: ' . $response_body);
            wc_add_notice(__('Could not create payment session. Please try again.', 'getblitz-payment-gateway'), 'error');
            return;
        }

        // Store details in order meta 
        $order->update_meta_data('_getblitz_session_id', sanitize_text_field($data['sessionId']));
        $order->update_meta_data('_getblitz_client_token', sanitize_text_field($data['clientToken']));

        // Mark order as pending payment so WooCommerce knows it's awaiting confirmation
        // and get_return_url() will return the order-received page, not checkout.
        $order->update_status('pending-payment', __('Awaiting GetBlitz SEPA payment.', 'getblitz-payment-gateway'));
        $order->save();

        return array(
            'result'   => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }

    public function receipt_page($order_id) {
        $order = wc_get_order($order_id);
        $session_id = $order->get_meta('_getblitz_session_id');
        $client_token = $order->get_meta('_getblitz_client_token');

        if (empty($session_id) || empty($client_token)) {
            echo '<p>' . esc_html__('Missing payment session details. Please try placing your order again.', 'getblitz-payment-gateway') . '</p>';
            return;
        }

        $js_api_url  = esc_js($this->api_url);
        $js_wss_url  = esc_js($this->wss_url);
        $verify_url  = esc_js(add_query_arg('wc-api', 'wc_gateway_getblitz_verify', home_url('/')));
        
        // Output widget UI container
        ?>
        <style>
            #getblitz-payment-wrapper { position: relative; }
            #getblitz-payment-status {
                display: none;
                text-align: center;
                padding: 2em 1em;
                font-size: 1.1em;
            }
            #getblitz-payment-status .getblitz-spinner {
                display: inline-block;
                width: 2em;
                height: 2em;
                border: 3px solid rgba(0,0,0,0.1);
                border-top-color: #7c3aed;
                border-radius: 50%;
                animation: getblitz-spin 0.8s linear infinite;
                margin-bottom: 0.75em;
            }
            @keyframes getblitz-spin { to { transform: rotate(360deg); } }
            #getblitz-payment-status.error { color: #b91c1c; }
        </style>
        <div id="getblitz-payment-wrapper">
            <div id="getblitz-payment-container"></div>
            <div id="getblitz-payment-status">
                <div class="getblitz-spinner"></div>
                <p id="getblitz-status-message"><?php esc_html_e('Confirming your payment, please wait…', 'getblitz-payment-gateway'); ?></p>
            </div>
        </div>
        <script src="https://unpkg.com/@getblitz/client/dist/getblitz.umd.cjs"></script>
        <script>
            (function() {
                var config = {
                    sessionId: "<?php echo esc_js($session_id); ?>",
                    clientToken: "<?php echo esc_js($client_token); ?>"
                };

                <?php if (!empty($js_api_url)) : ?>
                    config.apiUrl = "<?php echo $js_api_url; ?>";
                <?php endif; ?>

                <?php if (!empty($js_wss_url)) : ?>
                    config.wssUrl = "<?php echo $js_wss_url; ?>";
                <?php endif; ?>

                // Ensure GetBlitz is available
                if (typeof GetBlitz === 'undefined') {
                    console.error("GetBlitz widget failed to load.");
                    document.getElementById('getblitz-payment-container').innerHTML = '<p><?php echo esc_js(__('Unable to load payment widget. Please refresh and try again.', 'getblitz-payment-gateway')); ?></p>';
                    return;
                }

                var GetBlitzClass = (typeof GetBlitz.GetBlitz === 'function') ? GetBlitz.GetBlitz : GetBlitz;
                var payment = new GetBlitzClass(config);

                payment.mount("#getblitz-payment-container").catch(function(err) {
                    console.error("Failed to mount GetBlitz client:", err);
                });

                payment
                    .on("onSuccess", function(token) {
                        // Show loading state while we verify with the server
                        document.getElementById('getblitz-payment-container').style.display = 'none';
                        document.getElementById('getblitz-payment-status').style.display = 'block';

                        var verifyUrl = "<?php echo $verify_url; ?>";
                        var formData  = new FormData();
                        formData.append('order_id',   "<?php echo esc_js($order_id); ?>");
                        formData.append('session_id', "<?php echo esc_js($session_id); ?>");

                        fetch(verifyUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(function(response) { return response.json(); })
                        .then(function(json) {
                            var redirectUrl = (json && json.data && json.data.redirect) ? json.data.redirect : null;
                            if (redirectUrl) {
                                window.location.href = redirectUrl;
                            } else {
                                // Unexpected: show a soft error but don't loop back to checkout
                                document.getElementById('getblitz-payment-status').classList.add('error');
                                document.getElementById('getblitz-status-message').textContent = "<?php echo esc_js(__('Payment received — your order is being processed. You will receive a confirmation email shortly.', 'getblitz-payment-gateway')); ?>";
                                document.querySelector('#getblitz-payment-status .getblitz-spinner').style.display = 'none';
                            }
                        })
                        .catch(function(err) {
                            console.error('GetBlitz verify error:', err);
                            document.getElementById('getblitz-payment-status').classList.add('error');
                            document.getElementById('getblitz-status-message').textContent = "<?php echo esc_js(__('Payment received — your order is being processed. You will receive a confirmation email shortly.', 'getblitz-payment-gateway')); ?>";
                            document.querySelector('#getblitz-payment-status .getblitz-spinner').style.display = 'none';
                        });
                    })
                    .on("onError", function(error) {
                        console.error("GetBlitz Payment Error:", error);
                    })
                    .on("onExpired", function() {
                        console.warn("GetBlitz Payment Session Expired");
                    });
            })();
        </script>
        <?php
    }

    public function webhook_handler() {
        $payload = file_get_contents('php://input');
        
        if (empty($payload)) {
            status_header(400);
            exit('Missing payload');
        }

        // Webhook Secret Check
        if (empty($this->webhook_secret)) {
            status_header(500);
            error_log('GetBlitz Webhook Error: Webhook secret not configured in gateway settings.');
            exit('Webhook not configured');
        }

        // Locate Signature via multiple forms it could take in headers
        $signature = '';
        if (isset($_SERVER['HTTP_X_GETBLITZ_SIGNATURE'])) {
            $signature = $_SERVER['HTTP_X_GETBLITZ_SIGNATURE'];
        } else {
            if (function_exists('getallheaders')) {
                $headers = getallheaders();
                foreach($headers as $key => $val) {
                    if (strtolower($key) === 'x-getblitz-signature') {
                        $signature = $val;
                        break;
                    }
                }
            }
        }

        if (empty($signature)) {
            status_header(401);
            exit('Missing signature header');
        }

        $expected_signature = hash_hmac('sha256', $payload, $this->webhook_secret);

        if (!hash_equals($expected_signature, $signature)) {
            status_header(401);
            exit('Invalid signature');
        }

        $event_data = json_decode($payload, true);
        if (!$event_data || empty($event_data['event'])) {
            status_header(400);
            exit('Invalid JSON or missing event');
        }

        $event = $event_data['event'];
        $merchant_reference_id = isset($event_data['merchantReferenceId']) ? $event_data['merchantReferenceId'] : '';

        if (empty($merchant_reference_id)) {
            status_header(400);
            exit('Missing merchantReferenceId');
        }

        // Extract the actual order ID (removes the -time suffix if present)
        $order_id_parts = explode('-', $merchant_reference_id);
        $wc_order_id = $order_id_parts[0];

        // Try getting order
        $order = wc_get_order($wc_order_id);
        
        if (!$order) {
            status_header(404);
            exit('Order not found');
        }

        switch ($event) {
            case 'payment.success':
                // Check if already paid
                if (!$order->has_status('completed') && !$order->has_status('processing')) {
                    $order->payment_complete(isset($event_data['referenceId']) ? $event_data['referenceId'] : '');
                    $order->add_order_note(__('GetBlitz payment successfully confirmed via webhook.', 'getblitz-payment-gateway'));
                }
                break;

            case 'payment.partial':
                $paid = isset($event_data['amountPaidCents']) ? ($event_data['amountPaidCents'] / 100) : 0;
                $order->add_order_note(sprintf(__('GetBlitz: Partial payment received online: %s EUR.', 'getblitz-payment-gateway'), number_format($paid, 2)));
                break;

            case 'payment.failed':
                $order->update_status('failed', __('GetBlitz payment failed from provider.', 'getblitz-payment-gateway'));
                break;

            case 'payment.expired':
                if ($order->needs_payment()) {
                    $order->update_status('cancelled', __('GetBlitz payment session expired.', 'getblitz-payment-gateway'));
                }
                break;
        }

        status_header(200);
        exit('OK');
    }

    public function verify_handler() {
        if (empty($_POST['order_id']) || empty($_POST['session_id'])) {
            wp_send_json_error(array('message' => 'Missing parameters'));
        }

        $order_id   = absint($_POST['order_id']);
        $session_id = sanitize_text_field($_POST['session_id']);
        
        $order = wc_get_order($order_id);
        
        if (!$order || $order->get_meta('_getblitz_session_id') !== $session_id) {
            wp_send_json_error(array('message' => 'Invalid order or session'));
        }

        $redirect_url = $this->get_return_url($order);

        // If webhook already confirmed payment, just redirect
        if ($order->has_status('completed') || $order->has_status('processing')) {
            wp_send_json_success(array('redirect' => $redirect_url));
        }

        // Re-query the API to verify current session status
        $url = $this->api_url . '/api/v1/sessions/' . $session_id;

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept'        => 'application/json',
            ),
            'timeout' => 15,
        ));

        if (!is_wp_error($response)) {
            $response_code = wp_remote_retrieve_response_code($response);
            $body          = json_decode(wp_remote_retrieve_body($response), true);

            if ($response_code === 200 && !empty($body['status'])) {
                $status = strtoupper($body['status']);
                if (in_array($status, array('COMPLETED', 'SUCCESS', 'PAID', 'SETTLED'))) {
                    $order->payment_complete($session_id);
                    $order->add_order_note(__('GetBlitz payment verified via API during checkout.', 'getblitz-payment-gateway'));
                    wp_send_json_success(array('redirect' => $redirect_url));
                }
            }
        }

        // Payment not yet confirmed by the API (webhook may arrive shortly).
        // Put the order on-hold so it's no longer in "needs payment" state,
        // ensuring get_return_url() returns the order-received page and not checkout.
        if ($order->has_status('pending-payment') || $order->needs_payment()) {
            $order->update_status(
                'on-hold',
                __('GetBlitz payment received by client; awaiting final server confirmation.', 'getblitz-payment-gateway')
            );
        }

        // Always return the order-received redirect so the frontend never
        // loops back to the checkout page.
        wp_send_json_success(array('redirect' => $redirect_url));
    }
}

