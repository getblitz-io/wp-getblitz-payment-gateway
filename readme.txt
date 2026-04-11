=== GetBlitz Payment Gateway ===
Contributors: getblitz-io
Tags: sepa, instant-transfer, payments, europe, woocommerce, bank-transfer, getblitz
Requires at least: 6.0
Tested up to: 6.4
Stable tag: 0.1.2 <!-- x-release-please-version -->
License: MIT
License URI: https://opensource.org/licenses/MIT

Accept SEPA Instant Transfers across Europe with the GetBlitz Payment Gateway. Secure, fast, and open-source.

== Description ==

GetBlitz is an open-source SEPA Instant Transfer gateway that runs on your infrastructure — zero middlemen, zero tracking, zero lock-in. Built for European businesses that value sovereignty.

With this WooCommerce plugin, you can accept real-time EUR bank transfers across all 36 SEPA countries. Funds settle in under 10 seconds, directly to your bank account.

Key Features:
* **SEPA Instant Transfers**: Funds settle in seconds.
* **Direct Settlement**: Funds flow directly to your bank account (Qonto, Revolut Business, etc.).
* **Zero Transaction Fees**: Pay a flat monthly fee to GetBlitz, not a percentage of your sales.
* **Data Sovereignty**: Your customer data stays on your infrastructure.
* **Real-time Notifications**: Instant order updates via WebSockets.
* **Plugin Source Code**: [GitHub Repository](https://github.com/getblitz-io/wp-getblitz-payment-gateway)
* **Latest Releases**: [GitHub Releases](https://github.com/getblitz-io/wp-getblitz-payment-gateway/releases)

== Installation ==

1. Sign up/Self-host GetBlitz at [getblitz.io](https://getblitz.io).
2. Install and activate the plugin in your WordPress dashboard.
3. Navigate to WooCommerce > Settings > Payments > GetBlitz SEPA Instant.

== Configuration ==

To complete the setup, please follow these steps in your GetBlitz Dashboard:

* **Allowed Origins**: The allowed origin determines where your widget can load. You must add your WordPress site URL.
  * Go to `Getblitz -> Settings -> Allowed Origins -> Add`
* **Webhooks**: Webhooks receive real-time payment status updates.
  * Go to `Getblitz -> Settings -> Webhooks -> Add Webhook`
  * Add your webhook URL (you can copy this from the plugin settings page) and copy the Webhook Secret into the plugin settings.
* **API Key**: The API key allows the plugin to communicate securely with GetBlitz.
  * Go to `Getblitz -> Settings -> API Keys -> Generate New Key`
  * Copy this key into the plugin settings.

== Frequently Asked Questions ==

= Does this work with any bank? =
It works with any bank in the SEPA zone that supports Instant Transfers. You need to connect your business bank account to your GetBlitz instance.

= Is it really zero fees? =
Yes, GetBlitz does not charge per transaction. Depending on your setup, you pay a flat monthly fee for the SaaS dashboard or run it for free on your own servers.

== Screenshots ==
1. WooCommerce payment settings showing GetBlitz SEPA Instant in the list of payment methods.
2. GetBlitz SEPA Instant plugin general settings page.
3. GetBlitz SEPA Instant plugin settings with Step-by-Step configuration details.
4. WooCommerce checkout page showing GetBlitz payment option selected.
5. Order receipt page displaying the GetBlitz SEPA instant QR code and payment details.
6. Order received/thank you page after successful payment.

== Changelog ==

= 0.0.1 =
* Initial release.
* Support for SEPA Instant Transfers via GetBlitz SDK.
* Real-time payment verification.
* Webhook support for automated order status updates.
* Internationalization support.
