#!/bin/bash
# Build an installable Joomla plugin ZIP from this repository.
# Usage: ./build.sh

set -e

PLUGIN_NAME="plg_system_zoo_timezone"
VERSION=$(grep -oP '(?<=<version>)[^<]+' zoo_timezone.xml)

echo "Building ${PLUGIN_NAME} v${VERSION}..."

rm -f "${PLUGIN_NAME}.zip"

zip -r "${PLUGIN_NAME}.zip" \
    zoo_timezone.php \
    zoo_timezone.xml \
    script.php \
    elements/ \
    -x ".*"

echo "Created ${PLUGIN_NAME}.zip"
echo "Install via: Joomla → System → Install → Extensions → Upload Package File"
