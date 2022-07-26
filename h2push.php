<?php
/**
 * Plugin Name: HTTP/2 Server Push
 * Plugin URI: https://github.com/wearerequired/h2push/
 * Description: Sends Link headers to bring HTTP/2 Server Push for scripts and styles to WordPress.
 * Version: 2.1.0-beta
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Author: required
 * Author URI: https://required.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Copyright (c) 2017-2021 required (email: info@required.ch)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace Required\H2Push;

/**
 * Starts an output buffer for `wp_head`.
 *
 * The output buffer is necessary so that we can call `header()` during
 * the `wp_head` action which is the only action that allows us to collect
 * all script and style resources.
 *
 * @since 2.0.0
 */
function start_output_buffer(): void {
	ob_start();
}
add_action( 'wp_head', __NAMESPACE__ . '\start_output_buffer', 0 );

/**
 * Stops the output buffer for `wp_head`.
 *
 * @since 2.0.0
 */
function stop_output_buffer(): void {
	if ( ob_get_length() ) {
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
add_action( 'wp_head', __NAMESPACE__ . '\stop_output_buffer', PHP_INT_MAX );

/**
 * Sends preload Link headers for script and style resources.
 *
 * @since 1.0.0
 * @since 1.3.0 Falls back to <link> element if headers are already sent.
 */
function add_link_headers(): void {
	$as_header = ! headers_sent();
	/**
	 * Filters whether to use Link headers or the <link> element.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $as_header Whether to use Link headers or the <link> element.
	 */
	$as_header = apply_filters( 'h2push.as_header', $as_header );

	$resources = get_push_resources();

	foreach ( $resources as $attributes ) {
		if ( $as_header ) {
			header(
				get_link_header( $attributes ),
				false
			);
		} else {
			echo get_link_tag( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
add_action( 'wp_head', __NAMESPACE__ . '\add_link_headers', 2, 0 );

/**
 * Builds the Link header.
 *
 * @since 2.0.0
 *
 * @param array<mixed,string> $attributes Resource attributes.
 * @return string The Link header.
 */
function get_link_header( array $attributes ): string {
	$parameters = 'rel="preload";';

	foreach ( $attributes as $attr => $value ) {
		if (
			! is_scalar( $value ) ||
			( ! \in_array( $attr, [ 'as', 'crossorigin', 'type', 'nopush' ], true ) && ! is_numeric( $attr ) )
		) {
			continue;
		}

		if ( ! \is_string( $attr ) ) {
			$parameters .= " $value;";
		} else {
			$parameters .= " $attr=\"$value\";";
		}
	}

	$parameters = trim( $parameters );
	$parameters = rtrim( $parameters, ';' );

	return sprintf(
		'Link: <%s>; %s',
		esc_url_raw( $attributes['href'] ),
		$parameters
	);
}

/**
 * Builds the <link> tag.
 *
 * @since 2.0.0
 *
 * @param array<mixed,string> $attributes Resource attributes.
 * @return string The <link> tag.
 */
function get_link_tag( array $attributes ): string {
	$html = "rel='preload'";

	foreach ( $attributes as $attr => $value ) {
		if (
			! is_scalar( $value ) ||
			( ! \in_array( $attr, [ 'href', 'as', 'crossorigin', 'type' ], true ) && ! is_numeric( $attr ) )
		) {
			continue;
		}

		$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );

		if ( ! \is_string( $attr ) ) {
			$html .= " $value";
		} else {
			$html .= " $attr='$value'";
		}
	}

	$html = trim( $html );

	return "<link $html />\n";
}

/**
 * Retrieves script and style resources that can be used for HTTP/2 Server Push.
 *
 * @since 1.0.0
 *
 * @return array<string,mixed> List of script and style resources.
 */
function get_push_resources(): array {
	global $wp_version;

	$push_resources = [];
	$home_url       = untrailingslashit( home_url() );
	$home_url_host  = wp_parse_url( $home_url, PHP_URL_HOST );

	// Stylesheets.
	$wp_styles = wp_styles();
	$wp_styles->all_deps( $wp_styles->queue );
	$files = $wp_styles->to_do;

	foreach ( $files as $i => $handle ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$style = $wp_styles->registered[ $handle ];
		$src   = $style->src;

		if ( empty( $src ) ) {
			continue;
		}

		// Don't push files with conditional tags.
		if ( isset( $style->extra['conditional'] ) ) {
			continue;
		}

		// Prepend a protocol.
		if ( '//' === substr( $src, 0, 2 ) || ( false === strpos( $src, ':' ) && '/' !== $src[0] ) ) {
			$src = ( is_ssl() ? 'https:' : 'http:' ) . $src;
		}

		// Check if it's a local resource.
		if ( '/' !== $src[0] ) {
			$src_host        = wp_parse_url( $src, PHP_URL_HOST );
			$is_local        = $home_url_host === $src_host;
			$is_allowed_host = apply_filters( 'h2push.is_allowed_push_host', $is_local, $src_host );
			if ( ! $is_allowed_host ) {
				continue;
			}
		}

		// Append the style version.
		if ( null !== $style->ver ) {
			$src = add_query_arg(
				'ver',
				$style->ver ? $style->ver : $wp_version,
				$src
			);
		}

		$src = str_replace( $home_url, '', $src );

		$push_resources[] = [
			'href' => $src,
			'as'   => 'style',
		];
	}

	// Scripts.
	$wp_scripts = wp_scripts();
	$wp_scripts->all_deps( $wp_scripts->queue );
	$files = $wp_scripts->to_do;

	foreach ( $files as $i => $handle ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$script = $wp_scripts->registered[ $handle ];
		$src    = $script->src;

		if ( empty( $src ) ) {
			continue;
		}

		// Don't push files with conditional tags.
		if ( isset( $script->extra['conditional'] ) ) {
			continue;
		}

		// Prepend a protocol.
		if ( '//' === substr( $src, 0, 2 ) || ( false === strpos( $src, ':' ) && '/' !== $src[0] ) ) {
			$src = ( is_ssl() ? 'https:' : 'http:' ) . $src;
		}

		// Check if it's a local resource.
		if ( '/' !== $src[0] ) {
			$src_host        = wp_parse_url( $src, PHP_URL_HOST );
			$is_local        = $home_url_host === $src_host;
			$is_allowed_host = apply_filters( 'h2push.is_allowed_push_host', $is_local, $src_host );
			if ( ! $is_allowed_host ) {
				continue;
			}
		}

		// Append the script version.
		if ( null !== $script->ver ) {
			$src = add_query_arg(
				'ver',
				$script->ver ? $script->ver : $wp_version,
				$src
			);
		}

		$src = str_replace( $home_url, '', $src );

		$push_resources[] = [
			'href' => $src,
			'as'   => 'script',
		];
	}

	/**
	 * Filters URLs for resources to preload.
	 *
	 * @param array $push_resources URLs and types for resources to preload.
	 */
	$push_resources = apply_filters( 'h2push.push_resources', $push_resources );

	return $push_resources;
}
