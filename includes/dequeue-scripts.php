<?php
/**
 * Sometimes you don't need to load a specific script.
 *
 * @package Optimization
 */

namespace XWP\Performance\Includes\DequeueScripts;

use function XWP\Performance\Includes\AdminSettings\get_settings;
use function XWP\Performance\Includes\AdminSettings\parse_textarea_lines;
use const XWP\Performance\PRIORITY_LATE;

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\dequeue_scripts', PRIORITY_LATE );

/**
 * Remove unused scripts.
 */
function dequeue_scripts() {
	// Get settings
	$settings = get_settings();
	
	// Check if dequeue scripts is enabled
	if ( empty( $settings['dequeue_scripts_enabled'] ) ) {
		return;
	}
	
	// Get script handles from settings
	$handle_list = ! empty( $settings['dequeue_scripts_handles'] ) 
		? parse_textarea_lines( $settings['dequeue_scripts_handles'] )
		: array();

	foreach ( $handle_list as $handle ) {
		wp_dequeue_script( $handle );
	}
}
