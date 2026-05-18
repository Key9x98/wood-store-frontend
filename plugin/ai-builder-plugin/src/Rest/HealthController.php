<?php

declare(strict_types=1);

namespace AiBuilder\Rest;

use AiBuilder\Support\Logger;
use WP_REST_Request;
use WP_REST_Response;

final class HealthController
{
    use EnvelopeTrait;

    public static function handle(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();

        global $wpdb;
        $dbOk = false;
        try {
            $dbOk = $wpdb->get_var('SELECT 1') === '1';
        } catch (\Throwable $_e) {
            $dbOk = false;
        }

        $resp = $self->ok([
            'version' => AIB_PLUGIN_VERSION,
            'php' => PHP_VERSION,
            'wp' => get_bloginfo('version'),
            'db_ok' => $dbOk,
        ]);

        Logger::info('/health', [
            'method' => 'GET',
            'status' => $resp->get_status(),
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
        ]);

        return $resp;
    }
}
