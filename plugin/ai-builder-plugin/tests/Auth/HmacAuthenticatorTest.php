<?php

declare(strict_types=1);

namespace AiBuilder\Tests\Auth;

use AiBuilder\Tests\TestCase;
use WP_REST_Request;

final class HmacAuthenticatorTest extends TestCase
{
    public function test_passes_with_valid_signature_and_fresh_timestamp(): void
    {
        $req = $this->buildSignedRequest('GET', '/ai-builder/v1/health');
        $resp = rest_do_request($req);
        $this->assertSame(200, $resp->get_status());
    }

    public function test_rejects_invalid_signature(): void
    {
        $req = $this->buildSignedRequest('GET', '/ai-builder/v1/health');
        $req->set_header('X-AIB-Signature', str_repeat('0', 64));
        $resp = rest_do_request($req);
        $this->assertSame(401, $resp->get_status());
    }

    public function test_rejects_expired_timestamp(): void
    {
        $req = $this->buildSignedRequest('GET', '/ai-builder/v1/health', null, time() - 600);
        $resp = rest_do_request($req);
        $this->assertSame(401, $resp->get_status());
    }

    public function test_rejects_missing_headers(): void
    {
        $req = new WP_REST_Request('GET', '/ai-builder/v1/health');
        $resp = rest_do_request($req);
        $this->assertSame(401, $resp->get_status());
    }

    public function test_rejects_ip_not_on_whitelist(): void
    {
        update_option('ai_builder_allowed_ips', '10.10.10.10', false);
        $_SERVER['REMOTE_ADDR'] = '203.0.113.99';
        $req = $this->buildSignedRequest('GET', '/ai-builder/v1/health');
        $resp = rest_do_request($req);
        $this->assertSame(401, $resp->get_status());
    }
}
