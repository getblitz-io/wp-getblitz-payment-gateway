#!/bin/bash

# Exit on error
set -e

PLUGIN_SLUG="getblitz-payment-gateway"
VERSION=$(grep -m 1 "Version:" getblitz-payment-gateway.php | awk '{print $NF}')
ZIP_NAME="${PLUGIN_SLUG}-${VERSION}.zip"

echo "🧹 Cleaning up previous builds..."
rm -rf "/tmp/${PLUGIN_SLUG}"
rm -f "${ZIP_NAME}"

echo "📦 Preparing files for ${PLUGIN_SLUG}..."
# Create a temporary directory with the plugin slug
mkdir -p "/tmp/${PLUGIN_SLUG}"

# Copy all files from current directory to the temporary directory
# Excluding files listed in .distignore
rsync -a --exclude-from='.distignore' ./ "/tmp/${PLUGIN_SLUG}/"

echo "🗜️ Zipping the plugin..."
# Navigate to the /tmp directory so the zip structure is correct
cd /tmp
zip -q -r "${ZIP_NAME}" "${PLUGIN_SLUG}/"
cd - > /dev/null

echo "🚚 Moving zip file to project root..."
mv "/tmp/${ZIP_NAME}" "./${ZIP_NAME}"

echo "🧹 Cleaning up temporary directory..."
rm -rf "/tmp/${PLUGIN_SLUG}"

echo "✅ Build completed successfully! You can now upload ${ZIP_NAME} to WordPress.org."
