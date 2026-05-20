<?php

declare(strict_types=1);

namespace AiBuilder\Rest;

use AiBuilder\Support\Logger;
use WP_REST_Request;
use WP_REST_Response;

final class CacheController
{
    use EnvelopeTrait;

    public static function flush(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();

        wp_cache_flush();
        /**
         * Filter / action hook so other cache plugins (W3TC, WP Super Cache, LiteSpeed) can clear.
         */
        do_action('ai_builder_cache_flush');

        $resp = $self->ok(['flushed' => true]);
        Logger::info('/cache/flush', [
            'status' => 200,
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
        ]);
        return $resp;
    }
}
