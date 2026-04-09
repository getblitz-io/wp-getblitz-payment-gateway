#!/bin/bash
set -e

echo "Waiting for WordPress to be available..."
while ! curl -s http://wordpress:80 > /dev/null; do
  sleep 5
done

echo "Installing WordPress..."
wp core install \
  --url="http://localhost:8080" \
  --title="GetBlitz Test Store" \
  --admin_user="admin" \
  --admin_password="password" \
  --admin_email="test@getblitz.io" \
  --skip-email \
  --allow-root \
  --path="/var/www/html" || true # Ignore if already installed

echo "Updating WordPress options..."
wp option update siteurl "http://localhost:8080" --allow-root --path="/var/www/html" || true
wp option update home "http://localhost:8080" --allow-root --path="/var/www/html" || true
wp config set WP_DEBUG_DISPLAY false --raw --allow-root --path="/var/www/html" || true
wp config set WP_DEBUG_LOG true --raw --allow-root --path="/var/www/html" || true

echo "Installing WooCommerce..."
wp plugin install woocommerce --activate --allow-root --path="/var/www/html" || true

echo "Activating GetBlitz Plugin..."
wp plugin activate getblitz-wordpress --allow-root --path="/var/www/html" || true

echo "Setup complete!"
