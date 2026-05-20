<?php

declare(strict_types=1);

namespace AiBuilder\Rest;

use AiBuilder\Services\ContentSync;
use AiBuilder\Support\Logger;
use WP_REST_Request;
use WP_REST_Response;

final class ContentController
{
    use EnvelopeTrait;

    public static function upsertPage(WP_REST_Request $request): WP_REST_Response
    {
        return (new self())->doUpsert($request, 'page', '/content/pages');
    }

    public static function upsertPost(WP_REST_Request $request): WP_REST_Response
    {
        return (new self())->doUpsert($request, 'post', '/content/posts');
    }

    public static function upsertProduct(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();

        if (!class_exists('WooCommerce')) {
            $resp = $self->err('products.woocommerce_not_active', 'WooCommerce is required', 412);
            Logger::warn('/content/products', ['status' => $resp->get_status()]);
            return $resp;
        }

        $params = $request->get_json_params() ?: $request->get_body_params();
        if (!is_array($params)) {
            return $self->err('products.invalid_input', 'JSON body required', 400);
        }

        $slug = sanitize_title((string) ($params['slug'] ?? ''));
        $name = sanitize_text_field((string) ($params['name'] ?? ''));
        if ($slug === '' || $name === '') {
            return $self->err('products.invalid_input', 'slug and name required', 400);
        }

        $product = (new ContentSync())->upsertProduct([
            'slug' => $slug,
            'name' => $name,
            'description' => wp_kses_post((string) ($params['description'] ?? '')),
            'short_description' => wp_kses_post((string) ($params['short_description'] ?? '')),
            'sku' => sanitize_text_field((string) ($params['sku'] ?? '')),
            'regular_price' => isset($params['regular_price']) ? (string) $params['regular_price'] : '',
            'sale_price' => isset($params['sale_price']) ? (string) $params['sale_price'] : '',
            'featured_image_id' => isset($params['featured_image_id']) ? (int) $params['featured_image_id'] : 0,
            'gallery_ids' => array_map('intval', (array) ($params['gallery_ids'] ?? [])),
            'category_slugs' => array_map('sanitize_title', (array) ($params['category_slugs'] ?? [])),
            'meta' => is_array($params['meta'] ?? null) ? $params['meta'] : [],
        ]);

        $resp = $self->ok($product);
        Logger::info('/content/products', [
            'status' => 200,
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            'slug' => $slug,
        ]);
        return $resp;
    }

    /**
     * DELETE /content/products/{slug} — trash a product. Idempotent: a slug
     * that no longer exists still returns 200 with deleted=false.
     */
    public static function deleteProduct(WP_REST_Request $request): WP_REST_Response
    {
        $start = microtime(true);
        $self = new self();

        if (!class_exists('WooCommerce')) {
            return $self->err('products.woocommerce_not_active', 'WooCommerce is required', 412);
        }

        $slug = sanitize_title((string) $request->get_param('slug'));
        if ($slug === '') {
            return $self->err('products.invalid_input', 'slug required', 400);
        }

        $result = (new ContentSync())->deleteProductBySlug($slug);

        $resp = $self->ok($result);
        Logger::info('/content/products/{slug}', [
            'method' => 'DELETE',
            'status' => $resp->get_status(),
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            'slug' => $slug,
            'deleted' => $result['deleted'],
        ]);
        return $resp;
    }

    private function doUpsert(WP_REST_Request $request, string $postType, string $route): WP_REST_Response
    {
        $start = microtime(true);
        $params = $request->get_json_params() ?: $request->get_body_params();
        if (!is_array($params)) {
            return $this->err('content.invalid_input', 'JSON body required', 400);
        }

        $slug = sanitize_title((string) ($params['slug'] ?? ''));
        $title = sanitize_text_field((string) ($params['title'] ?? ''));
        $content = wp_kses_post((string) ($params['content'] ?? ''));
        if ($slug === '' || $title === '') {
            return $this->err('content.invalid_input', 'slug and title required', 400);
        }

        $meta = is_array($params['meta'] ?? null) ? $params['meta'] : [];

        $result = (new ContentSync())->upsert([
            'post_type' => $postType,
            'slug' => $slug,
            'title' => $title,
            'content' => $content,
            'meta' => $meta,
        ]);

        if ($result instanceof \WP_Error) {
            return $this->err('content.persist_failed', $result->get_error_message(), 500);
        }

        $resp = $this->ok($result);
        Logger::info($route, [
            'status' => 200,
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            'post_id' => $result['id'] ?? null,
            'slug' => $slug,
        ]);
        return $resp;
    }
}
