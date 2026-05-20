<?php

declare(strict_types=1);

namespace AiBuilder\Services;

final class ElementorBridge
{
    public function isAvailable(): bool
    {
        return class_exists('\\Elementor\\Plugin');
    }

    /**
     * Clear Elementor caches; optionally regenerate per-post CSS.
     *
     * @param int[] $postIds
     */
    public function rebuild(array $postIds): int
    {
        if (!$this->isAvailable()) {
            return 0;
        }

        try {
            // Global file cache + CSS cache
            $plugin = \Elementor\Plugin::$instance;
            if (isset($plugin->files_manager) && method_exists($plugin->files_manager, 'clear_cache')) {
                $plugin->files_manager->clear_cache();
            }
            if (isset($plugin->posts_css_manager) && method_exists($plugin->posts_css_manager, 'clear_cache')) {
                $plugin->posts_css_manager->clear_cache();
            }
        } catch (\Throwable $_e) {
            // best-effort
        }

        if (empty($postIds)) {
            return 0;
        }

        $count = 0;
        foreach ($postIds as $id) {
            $id = (int) $id;
            if ($id <= 0) {
                continue;
            }
            // Touching _elementor_data forces regeneration on next view.
            $existing = get_post_meta($id, '_elementor_data', true);
            if ($existing !== '') {
                update_post_meta($id, '_elementor_data', $existing);
                $count++;
            }
        }
        return $count;
    }
}
