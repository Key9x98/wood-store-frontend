<?php

declare(strict_types=1);

namespace AiBuilder\Services;

use WP_Error;
use WP_Theme;

/**
 * Theme-level management for the wp-content/themes directory:
 * list, inspect, install (from a .zip package), activate and delete whole themes.
 *
 * Operates on themes as units — it does not edit individual files inside a theme.
 */
final class ThemeManager
{
    /**
     * Every installed theme with its metadata.
     *
     * @return array<int,array<string,mixed>>
     */
    public function all(): array
    {
        $active = get_stylesheet();
        $themes = [];
        foreach (wp_get_themes() as $slug => $theme) {
            $themes[] = $this->present((string) $slug, $theme, $active);
        }
        return $themes;
    }

    /**
     * A single theme by its directory slug.
     *
     * @return array<string,mixed>|WP_Error
     */
    public function find(string $slug)
    {
        $slug = $this->sanitizeSlug($slug);
        if ($slug === '') {
            return new WP_Error('themes.bad_slug', 'Invalid theme slug.', ['status' => 400]);
        }
        $theme = wp_get_theme($slug);
        if (!$theme->exists()) {
            return new WP_Error('themes.not_found', sprintf('Theme "%s" is not installed.', $slug), ['status' => 404]);
        }
        return $this->present($slug, $theme, get_stylesheet());
    }

    /**
     * Install a theme from an uploaded .zip package.
     *
     * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int} $file
     * @return array<string,mixed>|WP_Error
     */
    public function installFromZip(array $file, bool $overwrite)
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            return new WP_Error('themes.upload_error', 'File upload failed (PHP error code ' . $error . ').', ['status' => 400]);
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_file($tmp)) {
            return new WP_Error('themes.no_file', 'A `file` field with a .zip package is required (multipart/form-data).', ['status' => 400]);
        }
        $ext = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            return new WP_Error('themes.bad_type', 'Theme package must be a .zip archive.', ['status' => 400]);
        }

        $this->loadUpgrader();

        // Quiet skin — collects errors instead of echoing HTML, suitable for REST.
        $skin = new \WP_Ajax_Upgrader_Skin();
        $upgrader = new \Theme_Upgrader($skin);
        // `overwrite_package` clears the destination first, allowing an existing theme to be replaced.
        $result = $upgrader->install($tmp, ['overwrite_package' => $overwrite]);

        if ($result instanceof WP_Error) {
            return new WP_Error('themes.install_failed', $result->get_error_message() ?: 'Theme installation failed.', ['status' => 422]);
        }
        if ($result !== true) {
            $skinErrors = $skin->get_errors();
            $message = ($skinErrors instanceof WP_Error && $skinErrors->has_errors())
                ? implode('; ', $skinErrors->get_error_messages())
                : 'Theme installation failed (the package may already exist — pass overwrite=1 to replace it).';
            return new WP_Error('themes.install_failed', $message, ['status' => 422]);
        }

        wp_clean_themes_cache();

        $info = $upgrader->theme_info();
        $slug = $info instanceof WP_Theme
            ? $info->get_stylesheet()
            : (string) ($upgrader->result['destination_name'] ?? '');
        if ($slug === '') {
            return new WP_Error('themes.install_failed', 'Theme installed but its directory could not be resolved.', ['status' => 500]);
        }

        $found = $this->find($slug);
        if ($found instanceof WP_Error) {
            return $found;
        }
        return ['installed' => true, 'overwritten' => $overwrite] + $found;
    }

    /**
     * Switch the active theme.
     *
     * @return array<string,mixed>|WP_Error
     */
    public function activate(string $slug)
    {
        $slug = $this->sanitizeSlug($slug);
        if ($slug === '') {
            return new WP_Error('themes.bad_slug', 'Invalid theme slug.', ['status' => 400]);
        }
        $theme = wp_get_theme($slug);
        if (!$theme->exists()) {
            return new WP_Error('themes.not_found', sprintf('Theme "%s" is not installed.', $slug), ['status' => 404]);
        }
        $errors = $theme->errors();
        if ($errors instanceof WP_Error) {
            return new WP_Error(
                'themes.broken',
                'Theme cannot be activated: ' . implode('; ', $errors->get_error_messages()),
                ['status' => 409]
            );
        }

        switch_theme($theme->get_stylesheet());

        $found = $this->find($slug);
        if ($found instanceof WP_Error) {
            return $found;
        }
        return ['activated' => true] + $found;
    }

    /**
     * Delete a theme directory. The active theme — and the parent of an active
     * child theme — cannot be deleted.
     *
     * @return array<string,mixed>|WP_Error
     */
    public function delete(string $slug)
    {
        $slug = $this->sanitizeSlug($slug);
        if ($slug === '') {
            return new WP_Error('themes.bad_slug', 'Invalid theme slug.', ['status' => 400]);
        }
        $theme = wp_get_theme($slug);
        if (!$theme->exists()) {
            return new WP_Error('themes.not_found', sprintf('Theme "%s" is not installed.', $slug), ['status' => 404]);
        }

        $active = wp_get_theme();
        $stylesheet = $theme->get_stylesheet();
        if ($stylesheet === $active->get_stylesheet() || $stylesheet === $active->get_template()) {
            return new WP_Error(
                'themes.active',
                'The active theme cannot be deleted. Activate another theme first.',
                ['status' => 409]
            );
        }

        $this->loadUpgrader();
        $result = delete_theme($stylesheet);

        if ($result instanceof WP_Error) {
            return new WP_Error('themes.delete_failed', $result->get_error_message() ?: 'Theme deletion failed.', ['status' => 500]);
        }
        if ($result !== true) {
            return new WP_Error('themes.delete_failed', 'Theme deletion failed (filesystem not writable?).', ['status' => 500]);
        }

        return ['deleted' => true, 'slug' => $stylesheet];
    }

    /**
     * Render a WP_Theme as a plain, JSON-safe array.
     *
     * @return array<string,mixed>
     */
    private function present(string $slug, WP_Theme $theme, string $activeSlug): array
    {
        $parent = $theme->parent();
        $errors = $theme->errors();

        return [
            'slug'         => $slug,
            'name'         => (string) $theme->get('Name'),
            'version'      => (string) $theme->get('Version'),
            'description'  => wp_strip_all_tags((string) $theme->get('Description')),
            'author'       => wp_strip_all_tags((string) $theme->get('Author')),
            'active'       => $slug === $activeSlug,
            'parent'       => $parent instanceof WP_Theme ? $parent->get_stylesheet() : null,
            'template'     => $theme->get_template(),
            'screenshot'   => $theme->get_screenshot() ?: null,
            'theme_root'   => $theme->get_theme_root(),
            'requires_wp'  => (string) $theme->get('RequiresWP'),
            'requires_php' => (string) $theme->get('RequiresPHP'),
            'errors'       => $errors instanceof WP_Error ? $errors->get_error_messages() : [],
        ];
    }

    /**
     * Allow only plain theme directory names. Rejecting `/` and `\` makes path
     * traversal impossible; the slug must also start with an alphanumeric so it
     * cannot be a dot-segment.
     */
    private function sanitizeSlug(string $slug): string
    {
        $slug = trim($slug);
        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $slug) === 1 ? $slug : '';
    }

    /**
     * Pull in the wp-admin helpers needed for installing and deleting themes.
     * Core only loads these on admin requests, so REST handlers must require them.
     */
    private function loadUpgrader(): void
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        if (!class_exists('Theme_Upgrader')) {
            require_once ABSPATH . 'wp-admin/includes/misc.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
        if (!function_exists('delete_theme')) {
            require_once ABSPATH . 'wp-admin/includes/theme.php';
        }
    }
}
