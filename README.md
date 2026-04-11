# GetBlitz Payment Gateway for WooCommerce

This is the WooCommerce payment gateway plugin for **GetBlitz**. 

[GetBlitz](https://www.getblitz.io) is an open-source SEPA Instant Transfer gateway that runs on your infrastructure — zero middlemen, zero tracking, zero lock-in. Built for European businesses that value sovereignty.

With this WooCommerce plugin, you can accept real-time EUR bank transfers across all 36 SEPA countries. Funds settle in under 10 seconds, directly to your bank account.

## Links

- **Main Website:** [https://www.getblitz.io](https://www.getblitz.io)
- **Documentation:** [https://docs.getblitz.io](https://docs.getblitz.io)
- **Open Source Repository:** [https://github.com/getblitz-io/getblitz](https://github.com/getblitz-io/getblitz)
- **Plugin Repository:** [https://github.com/getblitz-io/wp-getblitz-payment-gateway](https://github.com/getblitz-io/wp-getblitz-payment-gateway)
- **Latest Release:** [https://github.com/getblitz-io/wp-getblitz-payment-gateway/releases](https://github.com/getblitz-io/wp-getblitz-payment-gateway/releases)

## Features

- **SEPA Instant Transfers**: Funds settle in seconds.
- **Direct Settlement**: Funds flow directly to your bank account.
- **Zero Transaction Fees**: Pay a flat monthly fee snippet or host yourself for free.
- **Data Sovereignty**: Your customer data stays on your infrastructure.
- **Real-time Notifications**: Instant order updates via WebSockets.

## Installation

You can find the standard WordPress plugin installation instructions and more details in the `readme.txt` file.

## Configuration

To complete the setup, you'll need to configure your webhook and API keys:

1. **Allowed Origins**: Add your WordPress site URL to allow the payment widget to load.
   - Go to `Getblitz -> Settings -> Allowed Origins -> Add`
2. **Webhooks**: Add your WordPress site webhook URL to receive payment status updates. You must copy the webhook secret into the WooCommerce plugin settings.
   - Go to `Getblitz -> Settings -> Webhooks -> Add Webhook`
3. **API Key**: Generate a new API key to allow communication. Copy this key into the WooCommerce plugin settings.
   - Go to `Getblitz -> Settings -> API Keys -> Generate New Key`

You can easily copy your exact webhook URL from the plugin configuration screen under `WooCommerce -> Settings -> Payments -> GetBlitz SEPA Instant`.

## GitHub Actions & Automated Releases

This repository utilizes **GitHub Actions** for continuous integration and automated deployments. 
We use **Semantic Release** to automatically version the plugin based on Conventional Commits, update the `readme.txt` and plugin headers, and package the release ZIP file for the WordPress Plugin directory. 

## Contributing

We welcome contributions from the community! If you'd like to help improve the GetBlitz WooCommerce plugin:
1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/amazing-feature`).
3. Commit your changes using Conventional Commits (`git commit -m 'feat: add amazing feature'`).
4. Push to the branch (`git push origin feature/amazing-feature`).
5. Open a Pull Request.

Please check the main [GetBlitz repository](https://github.com/getblitz-io/getblitz) for broader architectural discussions and core contribution guidelines.

## License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.
