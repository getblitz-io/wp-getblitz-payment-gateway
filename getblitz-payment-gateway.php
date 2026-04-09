<?php
/**
 * Plugin Name: GetBlitz Payment Gateway
 * Plugin URI: https://github.com/getblitz-io/wp-getblitz-payment-gateway
 * Description: Accept SEPA Instant Transfers across Europe with the GetBlitz Payment Gateway.
 * Version: 0.1.0
 * Author: GetBlitz
 * Author URI: https://getblitz.io/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: getblitz-payment-gateway
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 *
 * @package GetBlitz
 */

defined('ABSPATH') || exit;

define('GETBLITZ_PLUGIN_URL', plugin_dir_url(__FILE__));

// Declare compatibility with WooCommerce Blocks checkout
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

// Load plugin translations
add_action('plugins_loaded', 'getblitz_load_textdomain', 1);

function getblitz_load_textdomain() {
    load_plugin_textdomain(
        'getblitz-payment-gateway',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}

// Hook to check for WooCommerce before initializing our gateway
add_action('plugins_loaded', 'getblitz_payment_gateway_init', 11);

function getblitz_payment_gateway_init() {
    // Make sure WooCommerce is active
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-gateway-getblitz.php';

    add_filter('woocommerce_payment_gateways', 'getblitz_add_gateway_class');

    // Add settings link on the plugin page
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'getblitz_plugin_settings_link');

    // Register Blocks Integration
    add_action('woocommerce_blocks_payment_method_type_registration', 'getblitz_blocks_payment_method_registration');

    // Register standalone blocks
    add_action('init', 'getblitz_register_standalone_blocks');
}

/**
 * Register standalone Gutenberg blocks
 */
function getblitz_register_standalone_blocks() {
    $script_url = plugin_dir_url(__FILE__) . 'assets/js/messaging-block.js';
    wp_register_script(
        'getblitz-messaging-block',
        $script_url,
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor')
    );

    wp_localize_script('getblitz-messaging-block', 'getblitzMessagingVars', array(
        'logoUrl' => plugin_dir_url(__FILE__) . 'assets/images/logo.png'
    ));

    register_block_type('getblitz/messaging', array(
        'editor_script' => 'getblitz-messaging-block',
    ));
}

/**
 * Register the Blocks integration for GetBlitz
 */
function getblitz_blocks_payment_method_registration($payment_method_registry) {
    // Only register if the base class exists
    if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wc-gateway-getblitz-blocks-support.php';
        $payment_method_registry->register(new WC_Gateway_GetBlitz_Blocks_Support());
    }
}

/**
 * Add the Gateway to WooCommerce
 **/
function getblitz_add_gateway_class($gateways) {
    $gateways[] = 'WC_Gateway_GetBlitz';
    return $gateways;
}

/**
 * Add Settings link to plugin list
 */
function getblitz_plugin_settings_link($links) {
    $settings_link = '<a href="admin.php?page=wc-settings&tab=checkout&section=getblitz">' . __('Settings', 'getblitz-payment-gateway') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
