<?php

declare(strict_types=1);

namespace AiBuilder\Tests\Rest;

use AiBuilder\Tests\TestCase;

final class ThemesControllerTest extends TestCase
{
    public function test_index_lists_installed_themes(): void
    {
        $req = $this->buildSignedRequest('GET', '/ai-builder/v1/themes');
        $data = $this->dispatchOk($req);

        $this->assertArrayHasKey('themes', $data);
        $this->assertIsArray($data['themes']);
        $this->assertSame(count($data['themes']), $data['count']);
        $this->assertNotEmpty($data['themes'], 'expected at least one installed theme');

        $active = array_filter($data['themes'], static fn (array $t): bool => $t['active'] === true);
        $this->assertCount(1, $active, 'exactly one theme should be active');
    }

    public function test_show_returns_active_theme(): void
    {
        $req = $this->buildSignedRequest('GET', '/ai-builder/v1/themes/' . get_stylesheet());
        $data = $this->dispatchOk($req);

        $this->assertSame(get_stylesheet(), $data['slug']);
        $this->assertTrue($data['active']);
    }

    public function test_show_unknown_theme_returns_404(): void
    {
        $req = $this->buildSignedRequest('GET', '/ai-builder/v1/themes/no-such-theme');
        $resp = rest_do_request($req);

        $this->assertSame(404, $resp->get_status());
        $this->assertSame('themes.not_found', $resp->get_data()['error']['code']);
    }

    public function test_install_rejects_missing_file(): void
    {
        $req = $this->buildSignedRequest('POST', '/ai-builder/v1/themes');
        $resp = rest_do_request($req);

        $this->assertSame(400, $resp->get_status());
        $this->assertSame('themes.no_file', $resp->get_data()['error']['code']);
    }

    public function test_install_rejects_non_zip_package(): void
    {
        $tmp = wp_tempnam('aib-theme-test');
        file_put_contents($tmp, 'not a zip');

        $req = $this->buildSignedRequest('POST', '/ai-builder/v1/themes');
        $req->set_file_params([
            'file' => [
                'name' => 'theme.txt',
                'type' => 'text/plain',
                'tmp_name' => $tmp,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmp) ?: 0,
            ],
        ]);
        $resp = rest_do_request($req);

        $this->assertSame(400, $resp->get_status());
        $this->assertSame('themes.bad_type', $resp->get_data()['error']['code']);
        @unlink($tmp);
    }

    public function test_install_rejects_invalid_base64(): void
    {
        $req = $this->buildSignedRequest('POST', '/ai-builder/v1/themes', ['zip_b64' => '!!!not-base64!!!']);
        $resp = rest_do_request($req);

        $this->assertSame(400, $resp->get_status());
        $this->assertSame('themes.bad_payload', $resp->get_data()['error']['code']);
    }

    public function test_activate_unknown_theme_returns_404(): void
    {
        $req = $this->buildSignedRequest('POST', '/ai-builder/v1/themes/no-such-theme/activate');
        $resp = rest_do_request($req);

        $this->assertSame(404, $resp->get_status());
        $this->assertSame('themes.not_found', $resp->get_data()['error']['code']);
    }

    public function test_delete_unknown_theme_returns_404(): void
    {
        $req = $this->buildSignedRequest('DELETE', '/ai-builder/v1/themes/no-such-theme');
        $resp = rest_do_request($req);

        $this->assertSame(404, $resp->get_status());
        $this->assertSame('themes.not_found', $resp->get_data()['error']['code']);
    }

    public function test_delete_active_theme_is_rejected(): void
    {
        $req = $this->buildSignedRequest('DELETE', '/ai-builder/v1/themes/' . get_stylesheet());
        $resp = rest_do_request($req);

        $this->assertSame(409, $resp->get_status());
        $this->assertSame('themes.active', $resp->get_data()['error']['code']);
    }
}
