<?php
/**
 * Plugin Name: HTTP/2 Server Push
 * Plugin URI:  https://github.com/wearerequired/h2push/
 * Description: Sends Link headers to bring HTTP/2 Server Push for scripts and styles to WordPress.
 * Version:     1.2.0
 * Author:      required
 * Author URI:  https://required.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Copyright (c) 2017-2018 required (email: info@required.ch)
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
 *
 * @package Required\H2Push
 */

namespace Required\H2Push;

/**
 * Sends preload Link headers for script and style resources.
 *
 * @since 1.0.0
 */
function add_link_headers() {
	if ( headers_sent() ) {
		return;
	}

	$resources = get_push_resources();

	foreach ( $resources as $resource ) {
		$header = sprintf(
			'Link: <%s>; rel=preload; as=%s',
			esc_url_raw( $resource['href'] ),
			esc_attr( $resource['as'] )
		);

		header( $header, false );
	}
}
add_action( 'wp_head', __NAMESPACE__ . '\add_link_headers', 2, 0 );

/**
 * Retrieves script and style resources that can be used for HTTP/2 Server Push.
 *
 * @since 1.0.0
 *
 * @return array List of script and style resources.
 */
function get_push_resources() {
	global $wp_version;

	$push_resources = [];
	$home_url       = untrailingslashit( home_url() );
	$home_url_host  = wp_parse_url( $home_url, PHP_URL_HOST );

	// Stylesheets.
	$wp_styles = wp_styles();
	$wp_styles->all_deps( $wp_styles->queue );
	$files = $wp_styles->to_do;

	foreach ( $files as $i => $handle ) {
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
			$src_host = wp_parse_url( $src, PHP_URL_HOST );
			if ( $home_url_host !== $src_host ) {
				continue;
			}
		}

		// Append the style version.
		if ( null !== $style->ver ) {
			$src = add_query_arg(
				'ver', $style->ver ? $style->ver : $wp_version,
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

	foreach ( $files as $i => $handle ) {
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
			$src_host = wp_parse_url( $src, PHP_URL_HOST );
			if ( $home_url_host !== $src_host ) {
				continue;
			}
		}

		// Append the script version.
		if ( null !== $script->ver ) {
			$src = add_query_arg(
				'ver', $script->ver ? $script->ver : $wp_version,
				$src
			);
		}

		$src = str_replace( $home_url, '', $src );

		$push_resources[] = [
			'href' => $src,
			'as'   => 'script',
		];
	}

	return $push_resources;
}
