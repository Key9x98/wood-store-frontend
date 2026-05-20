<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Render filter sidebar for shop / product archive pages.
 * Filters: category, wood, finish, price range.
 * Submits via GET; query strings are read by WooCommerce / WP query loop.
 */
function aib_furniture_filter_sidebar(): void
{
    $woods = get_terms(['taxonomy' => 'pa_wood', 'hide_empty' => false]);
    $finishes = get_terms(['taxonomy' => 'pa_finish', 'hide_empty' => false]);
    $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
    $current = wp_unslash($_GET);
    ?>
    <aside class="aib-filter">
        <form method="get" action="<?php echo esc_url(function_exists('wc_get_page_permalink') ? (string) wc_get_page_permalink('shop') : home_url('/shop')); ?>">
            <h3><?php esc_html_e('Lọc sản phẩm', 'ai-builder-furniture'); ?></h3>

            <details open>
                <summary><?php esc_html_e('Danh mục', 'ai-builder-furniture'); ?></summary>
                <ul>
                    <?php foreach ((array) $cats as $cat): if (!is_object($cat)) continue; ?>
                        <li>
                            <label>
                                <input type="checkbox" name="product_cat[]" value="<?php echo esc_attr($cat->slug); ?>"
                                    <?php checked(in_array($cat->slug, (array) ($current['product_cat'] ?? []), true)); ?> />
                                <?php echo esc_html($cat->name); ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </details>

            <details open>
                <summary><?php esc_html_e('Chất liệu', 'ai-builder-furniture'); ?></summary>
                <ul>
                    <?php foreach ((array) $woods as $w): if (!is_object($w)) continue; ?>
                        <li>
                            <label>
                                <input type="checkbox" name="filter_wood[]" value="<?php echo esc_attr($w->slug); ?>"
                                    <?php checked(in_array($w->slug, (array) ($current['filter_wood'] ?? []), true)); ?> />
                                <?php echo esc_html($w->name); ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </details>

            <details>
                <summary><?php esc_html_e('Bề mặt', 'ai-builder-furniture'); ?></summary>
                <ul>
                    <?php foreach ((array) $finishes as $f): if (!is_object($f)) continue; ?>
                        <li>
                            <label>
                                <input type="checkbox" name="filter_finish[]" value="<?php echo esc_attr($f->slug); ?>"
                                    <?php checked(in_array($f->slug, (array) ($current['filter_finish'] ?? []), true)); ?> />
                                <?php echo esc_html($f->name); ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </details>

            <details>
                <summary><?php esc_html_e('Khoảng giá (VND)', 'ai-builder-furniture'); ?></summary>
                <div class="aib-price">
                    <input type="number" min="0" step="1000000" name="min_price" placeholder="Từ"
                        value="<?php echo esc_attr((string) ($current['min_price'] ?? '')); ?>" />
                    <input type="number" min="0" step="1000000" name="max_price" placeholder="Đến"
                        value="<?php echo esc_attr((string) ($current['max_price'] ?? '')); ?>" />
                </div>
            </details>

            <button type="submit" class="aib-btn"><?php esc_html_e('Áp dụng', 'ai-builder-furniture'); ?></button>
            <a class="aib-link" href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? (string) wc_get_page_permalink('shop') : home_url('/shop')); ?>"><?php esc_html_e('Xoá lọc', 'ai-builder-furniture'); ?></a>
        </form>
    </aside>
    <?php
}
