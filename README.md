# HTTP/2 Server Push

[![PHPUnit Tests](https://github.com/wearerequired/h2push/actions/workflows/phpunit-tests.yml/badge.svg)](https://github.com/wearerequired/h2push/actions/workflows/phpunit-tests.yml) [![Coding Standards](https://github.com/wearerequired/h2push/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/wearerequired/h2push/actions/workflows/coding-standards.yml)

Sends Link headers to bring HTTP/2 Server Push for scripts and styles to WordPress. Falls back to `<link>` element if headers are already sent.  
Provides filters to customize and extend the resources to push.

![Screenshot](https://user-images.githubusercontent.com/617637/31279476-7c3dffd6-aaa9-11e7-91d8-57ec4435d067.png)

## Installation

Install the latest version with

`composer install wearerequired/h2push`

The plugin requires at least PHP 7.4 and WordPress 5.6.

## Hooks reference

### `h2push.as_header`

By default the plugin will use the Link header if no headers are sent yet and falls back to the `<link>` element. To change this behavior you can use the `h2push.as_header` filter. Example:

```php
// Force H2 Push to always use the `<link>` element.
add_filter( 'h2push.as_header', '__return_false' );
```

This filter is also useful if the server doesn't support HTTP/2 yet and you still want to benefit from preloading.

### `h2push.push_resources`

By default the plugin collects all enqueued scripts and styles which are have been registered before or at the `wp_enqueue_scripts` hook. The `h2push.push_resources` filters allows to customize the list of resources. Example:

```php
/**
 * Add web font and hero image to the list of resources to push/preload.
 *
 * @param array $resources List of resources.
 * @return array List of resources.
 */
function my_theme_push_resources( array $resources ): array {
	$relative_template_directory_uri = wp_parse_url( get_template_directory_uri(), PHP_URL_PATH );

	// Push web font.
	$resources[] = [
		'href' => $relative_template_directory_uri . '/assets/fonts/fancy.woff2',
		'as'   => 'font',
		'type' => 'font/woff2',
		'crossorigin',
	];

	if ( is_front_page() && ! is_paged() ) {
		// Push hero image.
		$resources[] = [
			'href' => $relative_template_directory_uri . '/assets/images/hero.webp',
			'as'   => 'image',
			'type' => 'image/webp',
		];
	}

	return $resources;
}
add_filter( 'h2push.push_resources', 'my_theme_push_resources' );
```
