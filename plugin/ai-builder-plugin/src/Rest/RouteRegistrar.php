<?php

declare(strict_types=1);

namespace AiBuilder\Rest;

use AiBuilder\Auth\HmacAuthenticator;

final class RouteRegistrar
{
    public function register(): void
    {
        $auth = [HmacAuthenticator::class, 'verify'];
        $ns = AIB_REST_NAMESPACE;

        register_rest_route($ns, '/health', [
            'methods'             => 'GET',
            'callback'            => [HealthController::class, 'handle'],
            'permission_callback' => $auth,
        ]);

        register_rest_route($ns, '/status', [
            'methods'             => 'GET',
            'callback'            => [StatusController::class, 'handle'],
            'permission_callback' => $auth,
        ]);

        register_rest_route($ns, '/content/pages', [
            'methods'             => 'POST',
            'callback'            => [ContentController::class, 'upsertPage'],
            'permission_callback' => $auth,
        ]);

        register_rest_route($ns, '/content/posts', [
            'methods'             => 'POST',
            'callback'            => [ContentController::class, 'upsertPost'],
            'permission_callback' => $auth,
        ]);

        register_rest_route($ns, '/content/products', [
            'methods'             => 'POST',
            'callback'            => [ContentController::class, 'upsertProduct'],
            'permission_callback' => $auth,
        ]);

        register_rest_route($ns, '/media/upload', [
            'methods'             => 'POST',
            'callback'            => [MediaController::class, 'upload'],
            'permission_callback' => $auth,
        ]);

        register_rest_route($ns, '/fields', [
            'methods'             => 'POST',
            'callback'            => [FieldsController::class, 'set'],
            'permission_callback' => $auth,
        ]);

        register_rest_route($ns, '/elementor/rebuild', [
            'methods'             => 'POST',
            'callback'            => [ElementorController::class, 'rebuild'],
            'permission_callback' => $auth,
        ]);

        register_rest_route($ns, '/cache/flush', [
            'methods'             => 'POST',
            'callback'            => [CacheController::class, 'flush'],
            'permission_callback' => $auth,
        ]);

        // Theme management — CRUD over the wp-content/themes directory.
        $themeSlug = '(?P<slug>[A-Za-z0-9][A-Za-z0-9._-]*)';

        register_rest_route($ns, '/themes', [
            [
                'methods'             => 'GET',
                'callback'            => [ThemesController::class, 'index'],
                'permission_callback' => $auth,
            ],
            [
                'methods'             => 'POST',
                'callback'            => [ThemesController::class, 'install'],
                'permission_callback' => $auth,
            ],
        ]);

        register_rest_route($ns, '/themes/' . $themeSlug, [
            [
                'methods'             => 'GET',
                'callback'            => [ThemesController::class, 'show'],
                'permission_callback' => $auth,
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [ThemesController::class, 'delete'],
                'permission_callback' => $auth,
            ],
        ]);

        register_rest_route($ns, '/themes/' . $themeSlug . '/activate', [
            'methods'             => 'POST',
            'callback'            => [ThemesController::class, 'activate'],
            'permission_callback' => $auth,
        ]);
    }
}
