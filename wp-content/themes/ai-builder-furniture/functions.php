<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

define('AIB_FURNITURE_VERSION', '1.0.0');
define('AIB_FURNITURE_DIR', get_template_directory());
define('AIB_FURNITURE_URI', get_template_directory_uri());

require_once AIB_FURNITURE_DIR . '/inc/theme-fields.php';
require_once AIB_FURNITURE_DIR . '/inc/customizer.php';
require_once AIB_FURNITURE_DIR . '/inc/woo-setup.php';
require_once AIB_FURNITURE_DIR . '/inc/filter-sidebar.php';

add_action('after_setup_theme', static function (): void {
    load_theme_textdomain('ai-builder-furniture', AIB_FURNITURE_DIR . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', [
        'height' => 80,
        'width' => 240,
        'flex-height' => true,
        'flex-width' => true,
    ]);
    add_theme_support('woocommerce', [
        'thumbnail_image_width' => 480,
        'single_image_width' => 1200,
        'product_grid' => ['min_columns' => 1, 'max_columns' => 4, 'default_columns' => 3],
    ]);
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    register_nav_menus([
        'primary' => __('Primary Menu', 'ai-builder-furniture'),
        'footer' => __('Footer Menu', 'ai-builder-furniture'),
    ]);
});

add_action('wp_enqueue_scripts', static function (): void {
    wp_enqueue_style(
        'aib-furniture-main',
        AIB_FURNITURE_URI . '/assets/css/main.css',
        [],
        AIB_FURNITURE_VERSION
    );
    wp_add_inline_style('aib-furniture-main', aib_furniture_css_variables());

    // Filter sidebar JS only on shop/product archives, and only when WooCommerce
    // is active (is_shop/is_product_taxonomy are WC functions).
    if (
        function_exists('is_shop')
        && function_exists('is_product_taxonomy')
        && (is_shop() || is_product_taxonomy())
    ) {
        wp_enqueue_script(
            'aib-furniture-filter',
            AIB_FURNITURE_URI . '/assets/js/filter.js',
            [],
            AIB_FURNITURE_VERSION,
            true
        );
    }
});

/**
 * Helper: is WooCommerce active right now? Used to guard WC-only code paths.
 */
function aib_furniture_woo_active(): bool
{
    return class_exists('WooCommerce');
}

/**
 * Admin notice when theme active without WooCommerce. Theme will still render,
 * but shop pages and product features won't work.
 */
add_action('admin_notices', static function (): void {
    if (aib_furniture_woo_active() || !current_user_can('activate_plugins')) {
        return;
    }
    echo '<div class="notice notice-warning"><p>';
    echo esc_html__(
        'AI Builder Furniture: WooCommerce chưa được kích hoạt. Trang shop / sản phẩm sẽ không hiển thị đúng. Vui lòng cài và kích hoạt WooCommerce.',
        'ai-builder-furniture',
    );
    echo '</p></div>';
});

/**
 * Render CSS custom properties from `ai_builder_fields.primary_color`.
 */
function aib_furniture_css_variables(): string
{
    $primary = aib_field('primary_color', '#7B3F00');
    return ":root { --aib-primary: {$primary}; --aib-primary-dark: " . aib_furniture_darken($primary, 15) . "; }";
}

function aib_furniture_darken(string $hex, int $percent): string
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) {
        return '#' . $hex;
    }
    $r = max(0, hexdec(substr($hex, 0, 2)) - $percent);
    $g = max(0, hexdec(substr($hex, 2, 2)) - $percent);
    $b = max(0, hexdec(substr($hex, 4, 2)) - $percent);
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

/**
 * Sale percent calculator, used in product card and single-product templates.
 * Accepts a generic object so PHP doesn't fatal when WooCommerce (and the
 * `WC_Product` class) is not loaded.
 */
function aib_furniture_sale_percent($product): int
{
    if (!is_object($product) || !method_exists($product, 'is_on_sale') || !$product->is_on_sale()) {
        return 0;
    }
    $regular = (float) $product->get_regular_price();
    $sale = (float) $product->get_sale_price();
    if ($regular <= 0 || $sale <= 0 || $sale >= $regular) {
        return 0;
    }
    return (int) round(($regular - $sale) / $regular * 100);
}
