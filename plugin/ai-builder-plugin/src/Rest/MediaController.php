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

    /**
     * POST /media/upload — import an image into the media library.
     * Accepts EITHER a multipart `file` field OR a JSON body `{ "source_url": "https://..." }`.
     * The Express content-sync worker uses the JSON form so the HMAC signature
     * (which covers the request body) stays a simple, reproducible string.
     */
    public static function upload(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();
        $importer = new MediaImporter();

        $files = $request->get_file_params();
        if (isset($files['file']) && is_array($files['file'])) {
            $result = $importer->sideload($files['file']);
        } else {
            $params = $request->get_json_params() ?: $request->get_body_params();
            $sourceUrl = is_array($params) ? esc_url_raw((string) ($params['source_url'] ?? '')) : '';
            if ($sourceUrl === '') {
                return $self->err(
                    'media.no_file',
                    '`file` (multipart) or `source_url` (JSON) is required',
                    400
                );
            }
            $result = $importer->sideloadFromUrl($sourceUrl);
        }

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
