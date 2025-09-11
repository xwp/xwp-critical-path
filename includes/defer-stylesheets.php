<?php
/**
 * Defer stylesheets.
 * 
 * This implementation defers all stylesheets except those explicitly marked as render-blocking.
 *
 * @package Optimization
 */

namespace XWP\Performance\Includes\DeferStylesheets;

use function XWP\Performance\Includes\AdminSettings\get_settings;
use function XWP\Performance\Includes\AdminSettings\parse_textarea_lines;

// Hook into WordPress's style loader tag filter
add_filter( 'style_loader_tag', __NAMESPACE__ . '\defer_non_critical_stylesheets', 10, 4 );

// Cache for render-blocking handles to improve performance
$GLOBALS['xwp_render_blocking_cache'] = null;

/**
 * Get cached render-blocking handles or load from settings
 * 
 * @return array Array of render-blocking stylesheet handles
 */
function get_render_blocking_handles() {
	global $xwp_render_blocking_cache;
	
	// Return cached value if available
	if ( is_array( $xwp_render_blocking_cache ) ) {
		return $xwp_render_blocking_cache;
	}
	
	// Get settings
	$settings = get_settings();
	
	// Get render-blocking handles from settings
	$render_blocking_handles = ! empty( $settings['defer_stylesheets_blocking_handles'] ) 
		? parse_textarea_lines( $settings['defer_stylesheets_blocking_handles'] )
		: array();
	
	// Create cache key based on configuration
	$cache_key = 'xwp_defer_styles_' . md5( serialize( $render_blocking_handles ) );
	
	// Try to get from transient
	$cached_handles = get_transient( $cache_key );
	
	if ( false === $cached_handles ) {
		// Store in transient for 24 hours
		set_transient( $cache_key, $render_blocking_handles, 24 * HOUR_IN_SECONDS );
		$cached_handles = $render_blocking_handles;
	}
	
	// Store in global cache for this request
	$xwp_render_blocking_cache = $cached_handles;
	
	return $cached_handles;
}

/**
 * Defer non-critical stylesheets by modifying their media attribute.
 *
 * Stylesheets NOT in the $render_blocking_handles array will be deferred.
 * 
 * @param string $tag    The <link> tag for the enqueued style.
 * @param string $handle The style's registered handle.
 * @param string $href   The stylesheet's source URL.
 * @param string $media  The stylesheet's media attribute.
 * 
 * @return string Modified <link> tag with deferred loading if applicable.
 */
function defer_non_critical_stylesheets( $tag, $handle, $href, $media ) {
	// Get settings
	$settings = get_settings();
	
	// Check if defer stylesheets is enabled
	if ( empty( $settings['defer_stylesheets_enabled'] ) ) {
		return $tag;
	}
	
	// If admin bar is showing, don't defer stylesheets or when in admin.
	if ( is_admin_bar_showing() || is_admin() ) {
		return $tag;
	}
	
	// Get render-blocking handles from cache
	$render_blocking_handles = get_render_blocking_handles();
	
	// Check if this stylesheet should remain render-blocking
	$is_render_blocking = in_array( $handle, $render_blocking_handles, true );
	
	// Don't defer print stylesheets or render-blocking stylesheets
	// Use regex for more robust matching of media attribute variations
	if ( ! $is_render_blocking && ! preg_match( '/media=["\']print["\']/', $tag ) ) {
		// Create a noscript fallback
		$noscript_tag = '<noscript>' . str_replace( " id='", " id='fallback-", $tag ) . '</noscript>';
		
		// Modify the original tag to load asynchronously
		$deferred_tag = str_replace( " media='{$media}'", " media='print' onload='this.media=\"all\"; this.onload=null;'", $tag );
		
		return $noscript_tag . $deferred_tag;
	}
	
	return $tag;
}
