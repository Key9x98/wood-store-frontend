<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

add_action('customize_register', static function (\WP_Customize_Manager $wp_customize): void {
    $wp_customize->add_section('aib_furniture_about', [
        'title' => __('Xưởng — Thông tin chung', 'ai-builder-furniture'),
        'priority' => 30,
    ]);

    $wp_customize->add_setting('aib_show_quote_cta', [
        'default' => 'yes',
        'sanitize_callback' => static fn ($v) => $v === 'no' ? 'no' : 'yes',
    ]);
    $wp_customize->add_control('aib_show_quote_cta', [
        'label' => __('Hiển thị CTA "Yêu cầu báo giá" ở header', 'ai-builder-furniture'),
        'section' => 'aib_furniture_about',
        'type' => 'select',
        'choices' => ['yes' => 'Có', 'no' => 'Không'],
    ]);
});
