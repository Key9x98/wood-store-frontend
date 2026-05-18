<?php

declare(strict_types=1);

namespace AiBuilder\Rest;

use AiBuilder\Support\Logger;
use WP_REST_Request;
use WP_REST_Response;

final class FieldsController
{
    use EnvelopeTrait;

    public const OPTION_KEY = 'ai_builder_fields';

    public static function set(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();

        $params = $request->get_json_params() ?: $request->get_body_params();
        if (!is_array($params) || !isset($params['fields']) || !is_array($params['fields'])) {
            return $self->err('fields.invalid_input', '`fields` object required', 400);
        }

        $cleaned = [];
        foreach ($params['fields'] as $k => $v) {
            if (!is_string($k) || !preg_match('/^[a-z][a-z0-9_]*$/', $k)) {
                continue;
            }
            if (is_scalar($v)) {
                $cleaned[$k] = is_string($v) ? wp_kses_post($v) : $v;
            } elseif (is_array($v)) {
                $cleaned[$k] = self::sanitizeArray($v);
            }
        }

        update_option(self::OPTION_KEY, $cleaned, false);

        $resp = $self->ok(['count' => count($cleaned)]);
        Logger::info('/fields', [
            'status' => 200,
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            'count' => count($cleaned),
        ]);
        return $resp;
    }

    /**
     * @param array<int|string,mixed> $arr
     * @return array<int|string,mixed>
     */
    private static function sanitizeArray(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if (is_string($v)) {
                $out[$k] = wp_kses_post($v);
            } elseif (is_scalar($v)) {
                $out[$k] = $v;
            } elseif (is_array($v)) {
                $out[$k] = self::sanitizeArray($v);
            }
        }
        return $out;
    }
}
