<?php

declare(strict_types=1);

namespace AiBuilder\Rest;

use AiBuilder\Services\MediaImporter;
use AiBuilder\Support\Logger;
use WP_REST_Request;
use WP_REST_Response;

final class MediaController
{
    use EnvelopeTrait;

    public static function upload(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();

        $files = $request->get_file_params();
        if (!isset($files['file']) || !is_array($files['file'])) {
            return $self->err('media.no_file', '`file` field is required (multipart)', 400);
        }

        $importer = new MediaImporter();
        $result = $importer->sideload($files['file']);

        if ($result instanceof \WP_Error) {
            $code = $result->get_error_code() ?: 'media.import_failed';
            return $self->err((string) $code, $result->get_error_message(), 400);
        }

        $resp = $self->ok($result, 201);
        Logger::info('/media/upload', [
            'status' => $resp->get_status(),
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            'attachment_id' => $result['id'] ?? null,
        ]);
        return $resp;
    }
}
