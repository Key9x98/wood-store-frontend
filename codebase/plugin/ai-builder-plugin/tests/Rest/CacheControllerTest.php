<?php

declare(strict_types=1);

namespace AiBuilder\Tests\Rest;

use AiBuilder\Tests\TestCase;

final class CacheControllerTest extends TestCase
{
    public function test_flush_calls_hook_and_returns_ok(): void
    {
        $hookCalled = false;
        add_action('ai_builder_cache_flush', static function () use (&$hookCalled): void {
            $hookCalled = true;
        });
        $data = $this->dispatchOk($this->buildSignedRequest('POST', '/ai-builder/v1/cache/flush'));
        $this->assertTrue($data['flushed']);
        $this->assertTrue($hookCalled);
    }
}
