<?php

declare(strict_types=1);

namespace AiBuilder\Tests\Rest;

use AiBuilder\Tests\TestCase;

final class MediaControllerTest extends TestCase
{
    public function test_rejects_missing_file(): void
    {
        $req = $this->buildSignedRequest('POST', '/ai-builder/v1/media/upload');
        $resp = rest_do_request($req);
        $this->assertSame(400, $resp->get_status());
        $body = $resp->get_data();
        $this->assertSame('media.no_file', $body['error']['code']);
    }

    public function test_rejects_disallowed_mime(): void
    {
        $tmp = wp_tempnam('aib-test');
        file_put_contents($tmp, 'noop');
        $req = $this->buildSignedRequest('POST', '/ai-builder/v1/media/upload');
        $req->set_file_params([
            'file' => [
                'name' => 'evil.exe',
                'type' => 'application/x-msdownload',
                'tmp_name' => $tmp,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmp) ?: 0,
            ],
        ]);
        $resp = rest_do_request($req);
        $this->assertSame(400, $resp->get_status());
        $body = $resp->get_data();
        $this->assertSame('media.bad_mime', $body['error']['code']);
        @unlink($tmp);
    }

    public function test_rejects_json_body_without_source_url(): void
    {
        // JSON branch: a body with neither a `file` part nor `source_url`.
        $req = $this->buildSignedRequest('POST', '/ai-builder/v1/media/upload', ['foo' => 'bar']);
        $resp = rest_do_request($req);
        $this->assertSame(400, $resp->get_status());
        $this->assertSame('media.no_file', $resp->get_data()['error']['code']);
    }
}
