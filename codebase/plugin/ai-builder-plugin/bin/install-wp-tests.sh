#!/usr/bin/env bash
# Install WordPress core + test library for PHPUnit (community standard scaffolding).
#
# Usage: bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]
# Example: bash bin/install-wp-tests.sh wordpress_test root '' 127.0.0.1 latest
set -euo pipefail

DB_NAME=${1-}
DB_USER=${2-}
DB_PASS=${3-}
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress}

if [ -z "${DB_NAME}" ]; then
  echo "Usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]" >&2
  exit 1
fi

download() {
  if command -v curl >/dev/null 2>&1; then
    curl -sSL "$1" -o "$2"
  elif command -v wget >/dev/null 2>&1; then
    wget -nv -O "$2" "$1"
  else
    echo "curl or wget required" >&2
    exit 1
  fi
}

install_wp() {
  if [ -d "${WP_CORE_DIR}" ]; then
    echo "WP core already at ${WP_CORE_DIR}"
    return
  fi
  mkdir -p "${WP_CORE_DIR}"
  if [ "${WP_VERSION}" = "latest" ]; then
    URL="https://wordpress.org/latest.tar.gz"
  else
    URL="https://wordpress.org/wordpress-${WP_VERSION}.tar.gz"
  fi
  TMP=$(mktemp)
  download "${URL}" "${TMP}"
  tar --strip-components=1 -C "${WP_CORE_DIR}" -xzf "${TMP}"
  rm -f "${TMP}"
}

install_test_suite() {
  if [ -d "${WP_TESTS_DIR}" ]; then
    echo "WP test suite already at ${WP_TESTS_DIR}"
    return
  fi
  mkdir -p "${WP_TESTS_DIR}"
  TMP=$(mktemp -d)
  download "https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/" "${TMP}/index.html" || true

  # Use git rather than svn for simplicity.
  if command -v svn >/dev/null 2>&1; then
    svn co --quiet "https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/" "${WP_TESTS_DIR}/includes"
    svn co --quiet "https://develop.svn.wordpress.org/trunk/tests/phpunit/data/" "${WP_TESTS_DIR}/data"
  else
    echo "subversion (svn) recommended to fetch WP test suite. Falling back to git clone."
    git clone --depth=1 --filter=blob:none --no-checkout "https://github.com/WordPress/wordpress-develop.git" "${TMP}/wd"
    (cd "${TMP}/wd" && git sparse-checkout init --cone && git sparse-checkout set tests/phpunit/includes tests/phpunit/data && git checkout trunk)
    cp -r "${TMP}/wd/tests/phpunit/includes" "${WP_TESTS_DIR}/"
    cp -r "${TMP}/wd/tests/phpunit/data" "${WP_TESTS_DIR}/"
  fi
  rm -rf "${TMP}"

  download "https://develop.svn.wordpress.org/trunk/wp-tests-config-sample.php" "${WP_TESTS_DIR}/wp-tests-config.php"
  sed -i.bak \
    -e "s:dirname( __FILE__ ) . '/src/':'${WP_CORE_DIR}/':" \
    -e "s/youremptytestdbnamehere/${DB_NAME}/" \
    -e "s/yourusernamehere/${DB_USER}/" \
    -e "s/yourpasswordhere/${DB_PASS}/" \
    -e "s|localhost|${DB_HOST}|" \
    "${WP_TESTS_DIR}/wp-tests-config.php"
  rm -f "${WP_TESTS_DIR}/wp-tests-config.php.bak"
}

create_db() {
  if [ "${SKIP_DB_CREATE}" = "true" ]; then return; fi
  if ! command -v mysql >/dev/null 2>&1 && ! command -v mysqladmin >/dev/null 2>&1; then
    echo "mysql client not found; create DB manually" >&2
    return
  fi
  MYSQL_OPTS="--user=${DB_USER} --host=${DB_HOST}"
  if [ -n "${DB_PASS}" ]; then MYSQL_OPTS="${MYSQL_OPTS} --password=${DB_PASS}"; fi
  mysqladmin create "${DB_NAME}" ${MYSQL_OPTS} || true
}

install_wp
install_test_suite
create_db

echo "Done. Now run: composer install && vendor/bin/phpunit"
