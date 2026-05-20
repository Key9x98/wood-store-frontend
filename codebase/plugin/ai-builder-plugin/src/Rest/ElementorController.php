<?php

declare(strict_types=1);

namespace AiBuilder\Rest;

use AiBuilder\Services\ElementorBridge;
use AiBuilder\Support\Logger;
use WP_REST_Request;
use WP_REST_Response;

final class ElementorController
{
    use EnvelopeTrait;

    public static function rebuild(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();

        $params = $request->get_json_params() ?: [];
        $postIds = isset($params['post_ids']) && is_array($params['post_ids'])
            ? array_values(array_filter(array_map('intval', $params['post_ids'])))
            : [];

        $bridge = new ElementorBridge();
        if (!$bridge->isAvailable()) {
            $resp = $self->ok(['skipped' => true, 'reason' => 'elementor_not_active']);
            Logger::warn('/elementor/rebuild', ['skipped' => true]);
            return $resp;
        }

        $count = $bridge->rebuild($postIds);

        $resp = $self->ok(['rebuilt' => $count]);
        Logger::info('/elementor/rebuild', [
            'status' => 200,
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            'count' => $count,
        ]);
        return $resp;
    }
}
