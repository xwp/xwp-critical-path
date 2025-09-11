<?php
/**
 * Sometimes you don't need to load a specific stylesheet.
 *
 * @package Optimization
 */

namespace XWP\Performance\Includes\DequeueStylesheet;

use function XWP\Performance\Includes\AdminSettings\get_settings;
use function XWP\Performance\Includes\AdminSettings\parse_textarea_lines;
use const XWP\Performance\PRIORITY_LATE;

// ======================================================================
// REMOVE GENERIC CSS
// ======================================================================

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\remove_unused_styles', PRIORITY_LATE );

/**
 * Remove styles not used on the website
 */
function remove_unused_styles() {
	// Get settings
	$settings = get_settings();
	
	// Check if dequeue stylesheets is enabled
	if ( empty( $settings['dequeue_stylesheets_enabled'] ) ) {
		return;
	}
	
	// Get stylesheet handles from settings
	$unused_styles = ! empty( $settings['dequeue_stylesheets_handles'] ) 
		? parse_textarea_lines( $settings['dequeue_stylesheets_handles'] )
		: array();

	foreach ( $unused_styles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}
}
