<?php

declare(strict_types=1);

namespace AiBuilder\Services;

use WP_Error;

final class ContentSync
{
    /**
     * Idempotent upsert for any post type by slug.
     *
     * @param array{post_type:string, slug:string, title:string, content:string, meta?:array<string,mixed>} $args
     * @return array{id:int, slug:string, post_type:string}|WP_Error
     */
    public function upsert(array $args)
    {
        $existing = get_page_by_path($args['slug'], OBJECT, $args['post_type']);

        $payload = [
            'post_type' => $args['post_type'],
            'post_status' => 'publish',
            'post_title' => $args['title'],
            'post_content' => $args['content'],
            'post_name' => $args['slug'],
            'meta_input' => $args['meta'] ?? [],
        ];

        if ($existing) {
            $payload['ID'] = $existing->ID;
        }

        $id = wp_insert_post($payload, true);
        if ($id instanceof WP_Error) {
            return $id;
        }

        return [
            'id' => (int) $id,
            'slug' => $args['slug'],
            'post_type' => $args['post_type'],
        ];
    }

    /**
     * Upsert WooCommerce product by slug. Requires Woo classes.
     *
     * @param array<string,mixed> $args
     */
    public function upsertProduct(array $args): array
    {
        $slug = (string) $args['slug'];
        $existing = get_page_by_path($slug, OBJECT, 'product');

        $payload = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'post_title' => (string) $args['name'],
            'post_name' => $slug,
            'post_content' => (string) ($args['description'] ?? ''),
            'post_excerpt' => (string) ($args['short_description'] ?? ''),
        ];
        if ($existing) {
            $payload['ID'] = $existing->ID;
        }

        $id = wp_insert_post($payload, true);
        if ($id instanceof WP_Error) {
            throw new \RuntimeException($id->get_error_message());
        }
        $id = (int) $id;

        // Core product meta
        if (!empty($args['sku'])) {
            update_post_meta($id, '_sku', (string) $args['sku']);
        }
        $reg = (string) ($args['regular_price'] ?? '');
        $sale = (string) ($args['sale_price'] ?? '');
        if ($reg !== '') {
            update_post_meta($id, '_regular_price', $reg);
            update_post_meta($id, '_price', $sale !== '' ? $sale : $reg);
        }
        if ($sale !== '') {
            update_post_meta($id, '_sale_price', $sale);
        }
        if (!empty($args['featured_image_id'])) {
            set_post_thumbnail($id, (int) $args['featured_image_id']);
        }
        if (!empty($args['gallery_ids'])) {
            update_post_meta($id, '_product_image_gallery', implode(',', array_map('intval', (array) $args['gallery_ids'])));
        }

        // WooCommerce catalog visibility + stock — bắt buộc set để
        // `_wc_term_recount()` (term count callback của product_cat) đếm
        // product này. Thiếu các meta dưới → sản phẩm vẫn publish, nhưng
        // term count báo 0 vì WC coi như "không bán được".
        if (get_post_meta($id, '_visibility', true) === '') {
            update_post_meta($id, '_visibility', 'visible');
        }
        if (get_post_meta($id, '_stock_status', true) === '') {
            update_post_meta($id, '_stock_status', 'instock');
        }
        if (get_post_meta($id, '_manage_stock', true) === '') {
            update_post_meta($id, '_manage_stock', 'no');
        }
        if (get_post_meta($id, '_backorders', true) === '') {
            update_post_meta($id, '_backorders', 'no');
        }

        if (!empty($args['category_slugs'])) {
            // Truyền raw string (đã được sanitize_text_field upstream). Với
            // taxonomy hierarchical như product_cat, wp_set_object_terms gọi
            // term_exists($value, 'product_cat') — match cả slug lẫn name. Nếu
            // không match, wp_insert_term($value, 'product_cat') tạo term mới
            // với name = $value, slug = sanitize_title($value) tự động.
            // Nhờ vậy user gửi "Tủ thờ" sẽ tạo term tên "Tủ thờ" slug "tu-tho".
            wp_set_object_terms($id, (array) $args['category_slugs'], 'product_cat', false);

            // Force WC term recount: wp_set_object_terms gọi callback của
            // taxonomy (đối với product_cat là `_wc_term_recount`), nhưng cache
            // count của WC có thể stale. Gọi explicit để đảm bảo count chính
            // xác ngay sau sync — quan trọng để trang /danh-muc/ hiển thị đúng.
            $term_tt_ids = wp_get_post_terms($id, 'product_cat', ['fields' => 'tt_ids']);
            if (!is_wp_error($term_tt_ids) && !empty($term_tt_ids)) {
                $tax = get_taxonomy('product_cat');
                if ($tax && is_callable($tax->update_count_callback)) {
                    call_user_func($tax->update_count_callback, $term_tt_ids, $tax);
                } else {
                    wp_update_term_count_now($term_tt_ids, 'product_cat');
                }
            }
        }
        foreach ((array) ($args['meta'] ?? []) as $k => $v) {
            if (is_string($k)) {
                update_post_meta($id, $k, $v);
            }
        }

        return [
            'id' => $id,
            'slug' => $slug,
            'post_type' => 'product',
        ];
    }

    /**
     * Trash a WooCommerce product by slug. Idempotent — a missing slug is not
     * an error, it returns deleted=false so a repeated delete-sync is safe.
     *
     * @return array{slug:string, id:int, deleted:bool}
     */
    public function deleteProductBySlug(string $slug): array
    {
        $existing = get_page_by_path($slug, OBJECT, 'product');
        if (!$existing) {
            return ['slug' => $slug, 'id' => 0, 'deleted' => false];
        }

        wp_trash_post((int) $existing->ID);

        return ['slug' => $slug, 'id' => (int) $existing->ID, 'deleted' => true];
    }
}
