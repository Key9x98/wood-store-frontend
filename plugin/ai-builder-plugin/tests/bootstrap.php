<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap. Requires the WordPress test scaffolding installed first:
 *   bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
 */

$_tests_dir = getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib';

if (!file_exists($_tests_dir . '/includes/functions.php')) {
    fwrite(STDERR, "Could not find {$_tests_dir}/includes/functions.php. " .
        "Run bin/install-wp-tests.sh first.\n");
    exit(1);
}

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter('muplugins_loaded', static function (): void {
    require dirname(__DIR__) . '/ai-builder-plugin.php';
});

require $_tests_dir . '/includes/bootstrap.php';

require_once __DIR__ . '/TestCase.php';
