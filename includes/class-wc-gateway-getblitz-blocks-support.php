<?php
/**
 * WooCommerce Blocks Support for GetBlitz Gateway
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

defined('ABSPATH') || exit;

final class WC_Gateway_GetBlitz_Blocks_Support extends AbstractPaymentMethodType {

    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'getblitz';

    /**
     * Initialize the payment method type.
     */
    public function initialize() {
        $this->settings = get_option('woocommerce_getblitz_settings', []);
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active() {
        return !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'];
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles() {
        wp_register_script(
            'getblitz-blocks-integration',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/blocks.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-blocks',
            ],
            null, // Could use version if desired
            true
        );

        return ['getblitz-blocks-integration'];
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data() {
        return [
            'name'        => $this->name,
            'title'       => $this->get_setting('title', 'GetBlitz SEPA Instant Transfer'),
            'description' => $this->get_setting('description', 'Pay instantly from your bank account via SEPA Instant.'),
            'icon'        => GETBLITZ_PLUGIN_URL . 'assets/images/logo-icon.png',
        ];
    }
}
