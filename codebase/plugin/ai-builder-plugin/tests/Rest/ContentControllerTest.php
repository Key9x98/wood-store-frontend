<?php

declare(strict_types=1);

namespace AiBuilder\Tests\Rest;

use AiBuilder\Tests\TestCase;

final class ContentControllerTest extends TestCase
{
    public function test_upsert_page_creates_then_updates_same_post(): void
    {
        $payload = ['slug' => 'home', 'title' => 'Home', 'content' => '<p>hi</p>'];
        $req = $this->buildSignedRequest('POST', '/ai-builder/v1/content/pages', $payload);
        $first = $this->dispatchOk($req);
        $this->assertGreaterThan(0, $first['id']);

        // Second call with same slug must return the SAME post id (idempotent)
        $payload2 = ['slug' => 'home', 'title' => 'Home v2', 'content' => '<p>updated</p>'];
        $req2 = $this->buildSignedRequest('POST', '/ai-builder/v1/content/pages', $payload2);
        $second = $this->dispatchOk($req2);
        $this->assertSame($first['id'], $second['id']);

        $post = get_post($second['id']);
        $this->assertSame('Home v2', $post->post_title);
        $this->assertSame('page', $post->post_type);
    }

    public function test_upsert_post_creates_post_type_post(): void
    {
        $req = $this->buildSignedRequest('POST', '/ai-builder/v1/content/posts', [
            'slug' => 'first-news',
            'title' => 'First News',
            'content' => 'hello',
        ]);
        $data = $this->dispatchOk($req);
        $this->assertSame('post', get_post($data['id'])->post_type);
    }

    public function test_upsert_page_rejects_missing_fields(): void
    {
        $req = $this->buildSignedRequest('POST', '/ai-builder/v1/content/pages', ['slug' => 'x']);
        $resp = rest_do_request($req);
        $this->assertSame(400, $resp->get_status());
        $body = $resp->get_data();
        $this->assertFalse($body['ok']);
        $this->assertSame('content.invalid_input', $body['error']['code']);
    }

    public function test_upsert_product_412_when_woocommerce_not_active(): void
    {
        // WooCommerce is not loaded in test env → expect 412.
        $req = $this->buildSignedRequest('POST', '/ai-builder/v1/content/products', [
            'slug' => 'tu-tho',
            'name' => 'Tủ thờ',
        ]);
        $resp = rest_do_request($req);
        $this->assertSame(412, $resp->get_status());
        $body = $resp->get_data();
        $this->assertSame('products.woocommerce_not_active', $body['error']['code']);
    }

    public function test_delete_product_route_is_registered_and_hmac_signed(): void
    {
        // WooCommerce is not loaded in test env → the DELETE route exists,
        // passes HMAC auth, reaches the controller and hits the Woo guard.
        $req = $this->buildSignedRequest('DELETE', '/ai-builder/v1/content/products/tu-tho');
        $resp = rest_do_request($req);
        $this->assertSame(412, $resp->get_status());
        $this->assertSame('products.woocommerce_not_active', $resp->get_data()['error']['code']);
    }

    public function test_delete_product_rejects_unsigned_request(): void
    {
        $req = new \WP_REST_Request('DELETE', '/ai-builder/v1/content/products/tu-tho');
        $resp = rest_do_request($req);
        $this->assertSame(401, $resp->get_status());
    }
}
