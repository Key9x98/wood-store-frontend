<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Register product categories and global attributes used by the furniture sample data.
 * Runs once on `init`; idempotent (skips if term/attribute already exists).
 */
add_action('init', static function (): void {
    if (!function_exists('wc_create_attribute')) {
        return;
    }

    $categories = [
        'tu-tho' => 'Tủ thờ',
        'tu-quan-ao' => 'Tủ quần áo',
        'ban-an' => 'Bàn ăn',
        'ban-tra' => 'Bàn trà',
        'ghe-sofa' => 'Ghế sofa',
        'sap-go' => 'Sập gỗ',
        'ban-tho' => 'Ban thờ',
        'ke' => 'Kệ',
    ];
    foreach ($categories as $slug => $name) {
        if (!term_exists($slug, 'product_cat')) {
            wp_insert_term($name, 'product_cat', ['slug' => $slug]);
        }
    }

    $attributes = [
        'wood' => [
            'label' => 'Chất liệu (gỗ)',
            'terms' => ['go-mit' => 'Gỗ Mít', 'go-gu' => 'Gỗ Gụ', 'go-soi' => 'Gỗ Sồi', 'go-lim' => 'Gỗ Lim', 'go-huong' => 'Gỗ Hương', 'go-cao-su' => 'Gỗ Cao Su', 'go-xoan-dao' => 'Gỗ Xoan Đào'],
        ],
        'finish' => [
            'label' => 'Bề mặt',
            'terms' => ['son-pu' => 'Sơn PU', 'vecni' => 'Vecni', 'dau-lau' => 'Dầu lau', 'tu-nhien' => 'Tự nhiên'],
        ],
        'style' => [
            'label' => 'Phong cách',
            'terms' => ['hien-dai' => 'Hiện đại', 'co-dien' => 'Cổ điển', 'tan-co-dien' => 'Tân cổ điển', 'a-dong' => 'Á Đông'],
        ],
        'color' => [
            'label' => 'Màu sắc',
            'terms' => ['nau' => 'Nâu', 'vang' => 'Vàng', 'den' => 'Đen', 'trang' => 'Trắng', 'do-nau' => 'Đỏ nâu'],
        ],
    ];

    foreach ($attributes as $slug => $cfg) {
        $taxName = 'pa_' . $slug;
        $attrId = aib_furniture_ensure_attribute($slug, $cfg['label']);
        if ($attrId === 0) {
            continue;
        }
        if (!taxonomy_exists($taxName)) {
            register_taxonomy($taxName, 'product', [
                'hierarchical' => false,
                'show_ui' => false,
                'query_var' => true,
                'rewrite' => ['slug' => $slug],
            ]);
        }
        foreach ($cfg['terms'] as $termSlug => $termName) {
            if (!term_exists($termSlug, $taxName)) {
                wp_insert_term($termName, $taxName, ['slug' => $termSlug]);
            }
        }
    }
}, 20);

function aib_furniture_ensure_attribute(string $slug, string $label): int
{
    global $wpdb;
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s",
        $slug
    ));
    if ($existing) {
        return (int) $existing;
    }
    $id = wc_create_attribute([
        'name' => $label,
        'slug' => $slug,
        'type' => 'select',
        'order_by' => 'menu_order',
        'has_archives' => true,
    ]);
    return is_wp_error($id) ? 0 : (int) $id;
}
