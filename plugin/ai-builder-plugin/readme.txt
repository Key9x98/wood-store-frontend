=== AI Builder ===
Contributors: cms-platform
Tags: api, sync, automation, rest
Requires at least: 6.4
Tested up to: 6.6
Requires PHP: 8.1
Stable tag: 0.2.0
License: Proprietary

Remote-control plugin that exposes a HMAC-protected REST API for the Express CMS to manage content, media, fields, and caches on a WordPress site.

== Description ==

System plugin. Not for end users.

Endpoints (namespace `ai-builder/v1`, all HMAC-signed):

* `GET  /health`
* `POST /content/pages`
* `POST /content/posts`
* `POST /content/products` (requires WooCommerce)
* `POST /media/upload`
* `POST /fields`
* `POST /elementor/rebuild`
* `POST /cache/flush`
* `GET  /status`
* `GET    /themes` — list installed themes
* `POST   /themes` — install a theme from a `.zip` (`overwrite=1` to replace)
* `GET    /themes/{slug}` — theme metadata
* `POST   /themes/{slug}/activate` — switch the active theme
* `DELETE /themes/{slug}` — delete a theme (not the active one)

== Installation ==

1. Install plugin folder or zip via WP-CLI:
   `wp plugin install ai-builder-plugin.zip --activate`

2. Set the shared secret used to sign requests:
   `wp option update ai_builder_secret "<64-char-secret>"`

3. (Optional) Restrict caller IPs:
   `wp option update ai_builder_allowed_ips "203.0.113.10,203.0.113.11"`

== Testing ==

1. Install the WordPress test scaffolding (once):
   `bash bin/install-wp-tests.sh wordpress_test root '' localhost latest`
2. `composer install`
3. `vendor/bin/phpunit`

== Changelog ==

= 0.2.0 =
* Add theme management endpoints: list, inspect, install (.zip), activate, delete whole themes.

= 0.1.0 =
* Initial release: HMAC auth + 9 REST endpoints + JSON-line logger.
