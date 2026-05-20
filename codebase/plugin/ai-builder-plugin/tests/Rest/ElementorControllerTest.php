<?php

declare(strict_types=1);

namespace AiBuilder\Tests\Rest;

use AiBuilder\Tests\TestCase;

final class ElementorControllerTest extends TestCase
{
    public function test_no_op_when_elementor_not_active(): void
    {
        $data = $this->dispatchOk(
            $this->buildSignedRequest('POST', '/ai-builder/v1/elementor/rebuild', ['post_ids' => [1, 2]])
        );
        $this->assertTrue($data['skipped'] ?? false);
        $this->assertSame('elementor_not_active', $data['reason']);
    }
}
