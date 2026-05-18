<?php

declare(strict_types=1);

namespace AiBuilder\Rest;

use WP_REST_Response;

trait EnvelopeTrait
{
    /**
     * @param array<string,mixed>|mixed $data
     */
    protected function ok($data = [], int $status = 200): WP_REST_Response
    {
        return new WP_REST_Response(['ok' => true, 'data' => $data], $status);
    }

    /**
     * @param array<string,mixed>|mixed $detail
     */
    protected function err(string $code, string $message, int $status = 400, $detail = null): WP_REST_Response
    {
        $body = ['ok' => false, 'error' => ['code' => $code, 'message' => $message]];
        if ($detail !== null) {
            $body['error']['detail'] = $detail;
        }
        return new WP_REST_Response($body, $status);
    }
}
