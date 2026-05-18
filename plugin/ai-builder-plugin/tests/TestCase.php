<?php

declare(strict_types=1);

namespace AiBuilder\Tests;

use WP_REST_Request;
use WP_UnitTestCase;

abstract class TestCase extends WP_UnitTestCase
{
    protected string $secret = 'unit-test-secret-' . '0123456789abcdef';

    public function set_up(): void
    {
        parent::set_up();
        update_option('ai_builder_secret', $this->secret, false);
        update_option('ai_builder_allowed_ips', '', false);
        do_action('rest_api_init');
    }

    /**
     * @param array<string,mixed>|null $body
     */
    protected function buildSignedRequest(string $method, string $path, ?array $body = null, ?int $timestamp = null): WP_REST_Request
    {
        $request = new WP_REST_Request($method, $path);
        $ts = $timestamp ?? time();
        $rawBody = '';
        if ($body !== null) {
            $rawBody = (string) json_encode($body);
            $request->set_body($rawBody);
            $request->set_header('content-type', 'application/json');
            $request->set_json_params($body);
        }
        $request->set_header('X-AIB-Timestamp', (string) $ts);
        $stringToSign = $ts . "\n" . strtoupper($method) . "\n" . $path . "\n" . $rawBody;
        $sig = hash_hmac('sha256', $stringToSign, $this->secret);
        $request->set_header('X-AIB-Signature', $sig);
        return $request;
    }

    protected function dispatchOk(WP_REST_Request $request): array
    {
        $resp = rest_do_request($request);
        $this->assertSame(200, $resp->get_status(), 'expected 200');
        $data = $resp->get_data();
        $this->assertTrue($data['ok'] ?? false, 'envelope.ok=true');
        return $data['data'] ?? [];
    }
}
