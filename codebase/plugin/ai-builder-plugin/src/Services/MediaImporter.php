<?php

declare(strict_types=1);

namespace AiBuilder\Services;

use WP_Error;

final class MediaImporter
{
    private const ALLOWED_MIME = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'video/mp4',
        'application/pdf',
    ];

    /**
     * @param array{name:string, type:string, tmp_name:string, error:int, size:int} $file
     * @return array{id:int, url:string}|WP_Error
     */
    public function sideload(array $file)
    {
        if ((int) ($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
            return new WP_Error('media.upload_error', 'Upload error: ' . (int) ($file['error'] ?? 0));
        }
        $mime = (string) ($file['type'] ?? '');
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return new WP_Error('media.bad_mime', 'MIME type not allowed: ' . $mime);
        }

        if (!function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $attachmentId = media_handle_sideload($file, 0);
        if ($attachmentId instanceof WP_Error) {
            return $attachmentId;
        }
        $url = (string) wp_get_attachment_url((int) $attachmentId);

        return ['id' => (int) $attachmentId, 'url' => $url];
    }

    /**
     * Download a remote image by URL and import it into the media library.
     * Used by the Express content-sync worker (JSON `source_url`).
     *
     * @return array{id:int, url:string}|WP_Error
     */
    public function sideloadFromUrl(string $url)
    {
        if (!function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $tmp = download_url($url);
        if ($tmp instanceof WP_Error) {
            return $tmp;
        }

        $name = basename((string) parse_url($url, PHP_URL_PATH));
        if ($name === '') {
            $name = 'image-' . substr(md5($url), 0, 12);
        }

        // Validate against the downloaded file's real type, not the URL.
        $filetype = wp_check_filetype_and_ext($tmp, $name);
        $mime = (string) ($filetype['type'] ?: '');
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            @unlink($tmp);
            return new WP_Error('media.bad_mime', 'MIME type not allowed: ' . ($mime ?: 'unknown'));
        }
        if (!empty($filetype['proper_filename'])) {
            $name = (string) $filetype['proper_filename'];
        }

        $file = [
            'name' => $name,
            'type' => $mime,
            'tmp_name' => $tmp,
            'error' => UPLOAD_ERR_OK,
            'size' => (int) (filesize($tmp) ?: 0),
        ];

        $attachmentId = media_handle_sideload($file, 0);
        if ($attachmentId instanceof WP_Error) {
            @unlink($tmp);
            return $attachmentId;
        }

        return [
            'id' => (int) $attachmentId,
            'url' => (string) wp_get_attachment_url((int) $attachmentId),
        ];
    }
}
