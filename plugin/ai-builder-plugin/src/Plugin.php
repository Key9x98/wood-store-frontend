<?php

declare(strict_types=1);

namespace AiBuilder;

use AiBuilder\Rest\RouteRegistrar;
use AiBuilder\Support\Logger;

final class Plugin
{
    private static ?Plugin $instance = null;
    private bool $booted = false;

    public static function instance(): Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }
        $this->booted = true;

        register_activation_hook(AIB_PLUGIN_FILE, [$this, 'onActivate']);
        register_deactivation_hook(AIB_PLUGIN_FILE, [$this, 'onDeactivate']);

        add_action('rest_api_init', static function (): void {
            (new RouteRegistrar())->register();
        });

        // Daily cleanup of old log files (older than 14 days).
        add_action('ai_builder_log_cleanup', [Logger::class, 'cleanup']);
        if (!wp_next_scheduled('ai_builder_log_cleanup')) {
            wp_schedule_event(time() + 3600, 'daily', 'ai_builder_log_cleanup');
        }
    }

    public function onActivate(): void
    {
        if (!get_option('ai_builder_secret')) {
            // Generate a secure default secret on first activation.
            update_option('ai_builder_secret', bin2hex(random_bytes(32)), false);
        }
        if (get_option('ai_builder_allowed_ips', null) === null) {
            update_option('ai_builder_allowed_ips', '', false);
        }
        update_option('ai_builder_version', AIB_PLUGIN_VERSION, false);
    }

    public function onDeactivate(): void
    {
        $ts = wp_next_scheduled('ai_builder_log_cleanup');
        if ($ts !== false) {
            wp_unschedule_event($ts, 'ai_builder_log_cleanup');
        }
    }
}
