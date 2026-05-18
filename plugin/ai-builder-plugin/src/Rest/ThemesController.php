<?php

declare(strict_types=1);

namespace AiBuilder\Rest;

use AiBuilder\Services\ThemeManager;
use AiBuilder\Support\Logger;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * CRUD over the wp-content/themes directory at the theme-package level
 * (list, inspect, install from .zip, activate, delete).
 */
final class ThemesController
{
    use EnvelopeTrait;

    /** GET /themes — list every installed theme. */
    public static function index(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();

        $themes = (new ThemeManager())->all();
        $resp = $self->ok(['themes' => $themes, 'count' => count($themes)]);

        Logger::info('/themes', [
            'method' => 'GET',
            'status' => $resp->get_status(),
            'duration_ms' => self::ms($start),
        ]);
        return $resp;
    }

    /** GET /themes/{slug} — one theme's metadata. */
    public static function show(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();
        $slug = (string) $request->get_param('slug');

        $result = (new ThemeManager())->find($slug);
        $resp = $result instanceof WP_Error ? $self->fromError($result) : $self->ok($result);

        Logger::info('/themes/{slug}', [
            'method' => 'GET',
            'slug' => $slug,
            'status' => $resp->get_status(),
            'duration_ms' => self::ms($start),
        ]);
        return $resp;
    }

    /** POST /themes — install a theme from an uploaded .zip (`overwrite=1` to replace an existing one). */
    public static function install(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();

        $files = $request->get_file_params();
        if (!isset($files['file']) || !is_array($files['file'])) {
            Logger::warn('/themes', ['method' => 'POST', 'status' => 400, 'duration_ms' => self::ms($start)]);
            return $self->err('themes.no_file', 'A `file` field with a .zip package is required (multipart/form-data).', 400);
        }

        $overwrite = filter_var($request->get_param('overwrite'), FILTER_VALIDATE_BOOLEAN);
        $result = (new ThemeManager())->installFromZip($files['file'], $overwrite);
        $resp = $result instanceof WP_Error ? $self->fromError($result) : $self->ok($result, 201);

        Logger::info('/themes', [
            'method' => 'POST',
            'overwrite' => $overwrite,
            'slug' => is_array($result) ? ($result['slug'] ?? null) : null,
            'status' => $resp->get_status(),
            'duration_ms' => self::ms($start),
        ]);
        return $resp;
    }

    /** POST /themes/{slug}/activate — switch the active theme. */
    public static function activate(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();
        $slug = (string) $request->get_param('slug');

        $result = (new ThemeManager())->activate($slug);
        $resp = $result instanceof WP_Error ? $self->fromError($result) : $self->ok($result);

        Logger::info('/themes/{slug}/activate', [
            'method' => 'POST',
            'slug' => $slug,
            'status' => $resp->get_status(),
            'duration_ms' => self::ms($start),
        ]);
        return $resp;
    }

    /** DELETE /themes/{slug} — remove a theme directory. */
    public static function delete(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();
        $slug = (string) $request->get_param('slug');

        $result = (new ThemeManager())->delete($slug);
        $resp = $result instanceof WP_Error ? $self->fromError($result) : $self->ok($result);

        Logger::info('/themes/{slug}', [
            'method' => 'DELETE',
            'slug' => $slug,
            'status' => $resp->get_status(),
            'duration_ms' => self::ms($start),
        ]);
        return $resp;
    }

    /** Map a WP_Error (carrying a `status` data key) onto the error envelope. */
    private function fromError(WP_Error $error): WP_REST_Response
    {
        $data = $error->get_error_data();
        $status = is_array($data) && isset($data['status']) ? (int) $data['status'] : 400;
        $code = (string) ($error->get_error_code() ?: 'themes.error');
        return $this->err($code, $error->get_error_message(), $status);
    }

    private static function ms(float $start): int
    {
        return (int) ((microtime(true) - $start) * 1000);
    }
}
