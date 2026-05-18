<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Read a field from the `ai_builder_fields` option pushed by the Express CMS
 * via the ai-builder-plugin REST endpoint `/wp-json/ai-builder/v1/fields`.
 */
function aib_field(string $key, $default = '')
{
    static $cache = null;
    if ($cache === null) {
        $opt = get_option('ai_builder_fields', []);
        $cache = is_array($opt) ? $opt : [];
    }
    return $cache[$key] ?? $default;
}

function aib_field_escaped(string $key, string $default = ''): string
{
    return esc_html((string) aib_field($key, $default));
}

function aib_field_url(string $key, string $default = ''): string
{
    return esc_url((string) aib_field($key, $default));
}

function aib_shortcode_field(array $atts): string
{
    $atts = shortcode_atts(['key' => '', 'default' => ''], $atts);
    if ($atts['key'] === '') {
        return '';
    }
    return aib_field_escaped((string) $atts['key'], (string) $atts['default']);
}
add_shortcode('aib_field', 'aib_shortcode_field');
