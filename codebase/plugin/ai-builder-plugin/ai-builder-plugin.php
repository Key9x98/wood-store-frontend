<?php
/**
 * Plugin Name:       AI Builder
 * Plugin URI:        https://example.com/ai-builder-plugin
 * Description:       Remote-control REST endpoints for the Express CMS to drive a WordPress site (content sync, media upload, fields, Elementor, cache, themes).
 * Version:           0.5.1
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            CMS Platform
 * License:           Proprietary
 * Text Domain:       ai-builder-plugin
 *
 * @package AiBuilder
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

define('AIB_PLUGIN_FILE', __FILE__);
define('AIB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIB_PLUGIN_VERSION', '0.5.1');
define('AIB_REST_NAMESPACE', 'ai-builder/v1');

// Autoload — prefer composer's, fall back to PSR-4 shim for environments without composer install.
$composer_autoload = AIB_PLUGIN_DIR . 'vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        if (strpos($class, 'AiBuilder\\') !== 0) {
            return;
        }
        $relative = substr($class, strlen('AiBuilder\\'));
        $path = AIB_PLUGIN_DIR . 'src/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    });
}

\AiBuilder\Plugin::instance()->boot();
