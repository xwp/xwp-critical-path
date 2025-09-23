<?php
/**
 * Defer scripts using WordPress 6.3+ script loading strategies.
 * 
 * This implementation uses the modern WordPress script loading API to defer scripts
 *
 * @package Optimization
 */

namespace XWP\Performance\Includes\DeferScripts;

use function XWP\Performance\Includes\AdminSettings\get_settings;
use function XWP\Performance\Includes\AdminSettings\parse_textarea_lines;
use const XWP\Performance\PRIORITY_VERY_LATE;

// Hook into script registration to modify loading strategies
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\modify_script_loading_strategies', PRIORITY_VERY_LATE );

/**
 * Modify script loading strategies after scripts are registered.
 * 
 * This function updates the loading strategy for registered scripts to 'defer'
 * except for scripts that should remain blocking.
 * 
 * @since WordPress 6.3
 */
function modify_script_loading_strategies() {
	// Get settings
	$settings = get_settings();
	
	// Check if defer scripts is enabled
	if ( empty( $settings['defer_scripts_enabled'] ) ) {
		return;
	}
	
	// Skip if admin bar is showing (frontend admin bar needs its scripts to work properly)
	if ( is_admin_bar_showing() ) {
		return;
	}
	
	// Check if WordPress version supports script strategies (6.3+)
	if ( ! function_exists( 'wp_script_add_data' ) ) {
		return;
	}
	
	// Get blocking scripts from settings
	$blocking_scripts = ! empty( $settings['defer_scripts_blocking_handles'] ) 
		? parse_textarea_lines( $settings['defer_scripts_blocking_handles'] )
		: array();
	
	// Get all registered scripts
	$wp_scripts = wp_scripts();
	
	// Iterate through all registered scripts and defer non-blocking ones
	foreach ( $wp_scripts->registered as $handle => $script ) {
		// Skip if this script should remain blocking
		if ( in_array( $handle, $blocking_scripts, true ) ) {
			continue;
		}
		
		// Skip inline scripts and scripts without src
		if ( empty( $script->src ) ) {
			continue;
		}
		
		// Set the loading strategy to 'defer' for this script
		// This uses the modern WordPress API for script loading strategies
		$script->extra['strategy'] = 'defer';
	}
}
