<?php
/**
 * Uninstall: clean up plugin options. Site content (pages/posts/products) is left intact.
 *
 * @package AiBuilder
 */

declare(strict_types=1);

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$options = [
    'ai_builder_secret',
    'ai_builder_allowed_ips',
    'ai_builder_fields',
    'ai_builder_version',
];
foreach ($options as $opt) {
    delete_option($opt);
}
