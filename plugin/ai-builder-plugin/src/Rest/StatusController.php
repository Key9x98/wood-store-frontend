<?php

declare(strict_types=1);

namespace AiBuilder\Rest;

use AiBuilder\Support\Logger;
use WP_REST_Request;
use WP_REST_Response;

final class StatusController
{
    use EnvelopeTrait;

    public static function handle(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugins = get_plugins();
        $active = (array) get_option('active_plugins', []);

        $resp = $self->ok([
            'version' => AIB_PLUGIN_VERSION,
            'wp' => get_bloginfo('version'),
            'php' => PHP_VERSION,
            'theme' => [
                'slug' => get_stylesheet(),
                'name' => wp_get_theme()->get('Name'),
            ],
            'plugins' => array_values(array_map(static function (string $file) use ($plugins, $active): array {
                return [
                    'file' => $file,
                    'name' => $plugins[$file]['Name'] ?? $file,
                    'version' => $plugins[$file]['Version'] ?? '',
                    'active' => in_array($file, $active, true),
                ];
            }, array_keys($plugins))),
        ]);

        Logger::info('/status', [
            'method' => 'GET',
            'status' => $resp->get_status(),
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
        ]);

        return $resp;
    }
}
