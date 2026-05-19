<?php

declare(strict_types=1);

namespace AiBuilder\Auth;

use WP_REST_Request;

final class HmacAuthenticator
{
    public const TIMESTAMP_WINDOW_SECONDS = 300;
    public const HEADER_TS = 'x_aib_timestamp';
    public const HEADER_SIG = 'x_aib_signature';

    /**
     * REST permission_callback compatible verify.
     */
    public static function verify(WP_REST_Request $request): bool
    {
        return (new self())->check($request);
    }

    public function check(WP_REST_Request $request): bool
    {
        $secret = (string) get_option('ai_builder_secret', '');
        if ($secret === '') {
            return false;
        }

        $ts = (string) $request->get_header(self::HEADER_TS);
        $sig = (string) $request->get_header(self::HEADER_SIG);
        if ($ts === '' || $sig === '') {
            return false;
        }

        if (!ctype_digit($ts)) {
            return false;
        }
        $tsInt = (int) $ts;
        if (abs(time() - $tsInt) > self::TIMESTAMP_WINDOW_SECONDS) {
            return false;
        }

        if (!$this->ipAllowed($this->clientIp())) {
            return false;
        }

        $method = strtoupper($request->get_method());
        $path = $this->canonicalPath($request);
        $body = (string) $request->get_body();

        $stringToSign = $ts . "\n" . $method . "\n" . $path . "\n" . $body;
        $expected = hash_hmac('sha256', $stringToSign, $secret);

        return hash_equals($expected, $sig);
    }

    /**
     * Path + query as the client signed it. `$request->get_route()` returns `/ai-builder/v1/<route>`
     * which matches what we expect the client to canonicalize.
     */
    private function canonicalPath(WP_REST_Request $request): string
    {
        $route = $request->get_route();
        $query = $request->get_query_params();
        // `rest_route` is a routing artifact, present when the API is reached
        // via /?rest_route=… instead of pretty /wp-json/ permalinks. It is not a
        // real query parameter — drop it so the signature is identical whether
        // the caller used pretty permalinks or the rest_route fallback.
        unset($query['rest_route']);
        if (!empty($query)) {
            ksort($query);
            $qs = http_build_query($query);
            return $route . '?' . $qs;
        }
        return $route;
    }

    private function clientIp(): string
    {
        $remote = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
        return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '';
    }

    private function ipAllowed(string $ip): bool
    {
        $raw = (string) get_option('ai_builder_allowed_ips', '');
        $raw = trim($raw);
        if ($raw === '') {
            return true; // dev default — restrict in production via option
        }
        $allow = array_filter(array_map('trim', explode(',', $raw)));
        return $ip !== '' && in_array($ip, $allow, true);
    }
}
