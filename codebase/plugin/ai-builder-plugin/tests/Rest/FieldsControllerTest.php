<?php

declare(strict_types=1);

namespace AiBuilder\Tests\Rest;

use AiBuilder\Rest\FieldsController;
use AiBuilder\Tests\TestCase;

final class FieldsControllerTest extends TestCase
{
    public function test_persists_fields_and_drops_unsafe_keys(): void
    {
        $req = $this->buildSignedRequest('POST', '/ai-builder/v1/fields', [
            'fields' => [
                'shop_name' => 'Xưởng đồ gỗ',
                'hotline' => '0900000000',
                'Bad-Key' => 'dropped',
                '_underscore' => 'dropped',
            ],
        ]);
        $data = $this->dispatchOk($req);
        $this->assertSame(2, $data['count']);

        $opt = get_option(FieldsController::OPTION_KEY);
        $this->assertSame('Xưởng đồ gỗ', $opt['shop_name']);
        $this->assertArrayNotHasKey('Bad-Key', $opt);
    }
}
