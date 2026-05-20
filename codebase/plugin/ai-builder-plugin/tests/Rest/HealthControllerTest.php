<?php

declare(strict_types=1);

namespace AiBuilder\Tests\Rest;

use AiBuilder\Tests\TestCase;

final class HealthControllerTest extends TestCase
{
    public function test_returns_version_and_db_ok(): void
    {
        $data = $this->dispatchOk($this->buildSignedRequest('GET', '/ai-builder/v1/health'));
        $this->assertSame(AIB_PLUGIN_VERSION, $data['version']);
        $this->assertTrue($data['db_ok']);
        $this->assertNotEmpty($data['wp']);
        $this->assertNotEmpty($data['php']);
    }
}
