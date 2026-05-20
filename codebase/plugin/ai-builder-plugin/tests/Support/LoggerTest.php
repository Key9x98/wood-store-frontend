<?php

declare(strict_types=1);

namespace AiBuilder\Tests\Support;

use AiBuilder\Support\Logger;
use AiBuilder\Tests\TestCase;

final class LoggerTest extends TestCase
{
    public function test_writes_one_json_line_per_call(): void
    {
        $path = Logger::filePathForToday();
        @unlink($path);
        Logger::info('/x', ['status' => 200, 'duration_ms' => 5]);
        $this->assertFileExists($path);
        $line = trim((string) file_get_contents($path));
        $decoded = json_decode($line, true);
        $this->assertIsArray($decoded);
        $this->assertSame('/x', $decoded['route']);
        $this->assertSame(200, $decoded['status']);
        @unlink($path);
    }

    public function test_redacts_sensitive_keys(): void
    {
        $path = Logger::filePathForToday();
        @unlink($path);
        Logger::info('/x', ['secret' => 'hunter2', 'nested' => ['token' => 'leak']]);
        $line = trim((string) file_get_contents($path));
        $this->assertStringNotContainsString('hunter2', $line);
        $this->assertStringNotContainsString('"leak"', $line);
        $this->assertStringContainsString('[REDACTED]', $line);
        @unlink($path);
    }
}
