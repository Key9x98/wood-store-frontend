<?php

declare(strict_types=1);

namespace AiBuilder\Support;

final class Logger
{
    private const RETENTION_DAYS = 14;
    private const REDACT_KEYS = [
        'password', 'passwordHash', 'secret', 'token', 'apiKey', 'signature',
        'x_aib_signature', 'x_aib_timestamp', 'authorization',
    ];

    public static function info(string $route, array $context = []): void
    {
        self::write('info', $route, $context);
    }

    public static function warn(string $route, array $context = []): void
    {
        self::write('warn', $route, $context);
    }

    public static function error(string $route, array $context = []): void
    {
        self::write('error', $route, $context);
    }

    public static function write(string $level, string $route, array $context = []): void
    {
        $line = json_encode(array_merge(
            [
                'ts' => gmdate('Y-m-d\TH:i:s\Z'),
                'level' => $level,
                'route' => $route,
            ],
            self::redact($context),
        ), JSON_UNESCAPED_SLASHES);

        if ($line === false) {
            return;
        }

        $file = self::filePathForToday();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        // FILE_APPEND + LOCK_EX is good enough for daily file; one line per request.
        @file_put_contents($file, $line . "\n", FILE_APPEND | LOCK_EX);
    }

    public static function filePathForToday(): string
    {
        return self::baseDir() . '/' . gmdate('Y-m-d') . '.log';
    }

    public static function baseDir(): string
    {
        $upload = wp_upload_dir();
        $base = isset($upload['basedir']) ? (string) $upload['basedir'] : sys_get_temp_dir();
        return rtrim($base, '/') . '/ai-builder-logs';
    }

    /**
     * Remove old log files. Called daily via wp-cron.
     */
    public static function cleanup(): void
    {
        $dir = self::baseDir();
        if (!is_dir($dir)) {
            return;
        }
        $threshold = time() - self::RETENTION_DAYS * 86400;
        foreach (glob($dir . '/*.log') ?: [] as $path) {
            if (is_file($path) && (int) filemtime($path) < $threshold) {
                @unlink($path);
            }
        }
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private static function redact(array $data): array
    {
        foreach ($data as $k => $v) {
            if (is_string($k) && in_array(strtolower($k), self::REDACT_KEYS, true)) {
                $data[$k] = '[REDACTED]';
                continue;
            }
            if (is_array($v)) {
                $data[$k] = self::redact($v);
            }
        }
        return $data;
    }
}
