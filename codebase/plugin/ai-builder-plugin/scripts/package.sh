#!/usr/bin/env bash
# Build a distributable plugin zip.
#
# Usage: bash scripts/package.sh
#   Requires composer in PATH.
#
# Output: dist/ai-builder-plugin-<version>.zip
set -euo pipefail

cd "$(dirname "$0")/.."

if ! command -v composer >/dev/null 2>&1; then
  echo "composer is required (https://getcomposer.org/)" >&2
  exit 1
fi
if ! command -v zip >/dev/null 2>&1; then
  echo "zip is required" >&2
  exit 1
fi

VERSION=$(grep -m1 '^ \* Version:' ai-builder-plugin.php | sed -E 's/.*Version: *([0-9A-Za-z.+-]+).*/\1/')
if [ -z "${VERSION}" ]; then
  echo "Could not detect Version from plugin header" >&2
  exit 1
fi

echo "Building ai-builder-plugin v${VERSION}"

composer install --no-dev --optimize-autoloader --no-interaction

mkdir -p dist
ZIP="dist/ai-builder-plugin-${VERSION}.zip"
rm -f "${ZIP}"

# Stage a clean copy under dist/staging to ensure the zip top-level folder
# is `ai-builder-plugin/` (matches `wp plugin install <zip>` expectations).
STAGE="dist/staging/ai-builder-plugin"
rm -rf dist/staging
mkdir -p "${STAGE}"

cp ai-builder-plugin.php "${STAGE}/"
cp composer.json "${STAGE}/"
cp readme.txt "${STAGE}/"
cp uninstall.php "${STAGE}/"
cp -r src "${STAGE}/"
[ -d vendor ] && cp -r vendor "${STAGE}/"

(cd dist/staging && zip -qr "../../${ZIP}" ai-builder-plugin)
rm -rf dist/staging

echo "Built ${ZIP}"
