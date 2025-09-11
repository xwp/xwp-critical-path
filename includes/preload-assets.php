<?php
/**
 * Preload Assets.
 *
 * @package Optimization
 */

namespace XWP\Performance\Includes\PreloadAssets;

use function XWP\Performance\Includes\AdminSettings\get_settings;
use function XWP\Performance\Includes\AdminSettings\parse_textarea_lines;
use const XWP\Performance\PRIORITY_EARLY;

// ======================================================================
// PRELOAD GENERIC CSS
// ======================================================================

add_action( 'wp_head', __NAMESPACE__ . '\preload_stylesheets', PRIORITY_EARLY );

/**
 * Preload stylesheet file with version.
 * To not be render blocking if the file is not lazy-load.
 * Indeed not all the style could be lazy-loaded, to avoid a 'naked' page (& CLS).
 *
 * @package Optimization
 */
function preload_stylesheets() {
	// Get settings
	$settings = get_settings();
	
	// Check if preload assets is enabled
	if ( empty( $settings['preload_assets_enabled'] ) ) {
		return;
	}

	global $wp_styles;

	// Get stylesheet handles from settings
	$handle_list = ! empty( $settings['preload_css_handles'] ) 
		? parse_textarea_lines( $settings['preload_css_handles'] )
		: array();

	foreach ( $handle_list as $handle ) {
		$style = isset( $wp_styles->registered[ $handle ] ) ? $wp_styles->registered[ $handle ] : null;

		// Don't preload the style if not available.
		if ( null === $style ) {
			continue;
		}

		$source = $style->src . ( $style->ver ? "?ver={$style->ver}" : '' );
		echo '<link rel="preload" href="' . esc_url( $source ) . '" as="style" />' . PHP_EOL;
	}
}

// ======================================================================
// PRELOAD CUSTOM URLS (FONTS, ETC)
// ======================================================================

add_action( 'wp_head', __NAMESPACE__ . '\preload_custom_urls', PRIORITY_EARLY );

/**
 * Preload custom URLs like fonts
 */
function preload_custom_urls() {
	// Get settings
	$settings = get_settings();
	
	// Check if preload assets is enabled
	if ( empty( $settings['preload_assets_enabled'] ) ) {
		return;
	}
	
	// Get custom URLs from settings
	$custom_urls = ! empty( $settings['preload_custom_urls'] ) 
		? parse_textarea_lines( $settings['preload_custom_urls'] )
		: array();
	
	foreach ( $custom_urls as $url ) {
		// Determine the resource type based on file extension
		$extension = pathinfo( $url, PATHINFO_EXTENSION );
		$as_type = 'fetch'; // Default type
		$type_attr = '';
		$crossorigin = '';
		
		// Set appropriate preload hints based on file type
		if ( in_array( $extension, array( 'woff', 'woff2', 'ttf', 'otf', 'eot' ), true ) ) {
			$as_type = 'font';
			$crossorigin = ' crossorigin';
			
			// Set specific type for fonts
			if ( $extension === 'woff2' ) {
				$type_attr = ' type="font/woff2"';
			} elseif ( $extension === 'woff' ) {
				$type_attr = ' type="font/woff"';
			}
		} elseif ( in_array( $extension, array( 'css' ), true ) ) {
			$as_type = 'style';
		} elseif ( in_array( $extension, array( 'js' ), true ) ) {
			$as_type = 'script';
		} elseif ( in_array( $extension, array( 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp' ), true ) ) {
			$as_type = 'image';
		}
		
		// Handle relative URLs
		if ( strpos( $url, 'http' ) !== 0 && strpos( $url, '//' ) !== 0 ) {
			// If it starts with /, it's relative to site root
			if ( strpos( $url, '/' ) === 0 ) {
				$url = home_url( $url );
			} else {
				// Otherwise, assume it's relative to theme directory
				$url = get_stylesheet_directory_uri() . '/' . $url;
			}
		}
		
		printf( '<link rel="preload" href="%s" as="%s"%s%s>' . PHP_EOL, 
			esc_url( $url ), 
			esc_attr( $as_type ),
			$type_attr,
			$crossorigin
		);
	}
}
