<?php

declare(strict_types=1);

namespace AiBuilder\Tests\Rest;

use AiBuilder\Tests\TestCase;

final class StatusControllerTest extends TestCase
{
    public function test_returns_versions_theme_and_plugin_list(): void
    {
        $data = $this->dispatchOk($this->buildSignedRequest('GET', '/ai-builder/v1/status'));
        $this->assertSame(AIB_PLUGIN_VERSION, $data['version']);
        $this->assertNotEmpty($data['wp']);
        $this->assertNotEmpty($data['theme']['slug']);
        $this->assertIsArray($data['plugins']);
    }
}
