<?php
/**
 * WordPress will only load the style (inline) or script files when the block is used, this works for both archive and single pages.
 *
 * Link: https://developer.wordpress.org/reference/hooks/should_load_separate_core_block_assets/
 * Link: https://www.amsivedigital.com/insights/performance-creative/how-new-wordpress-5-8-block-loading-enhancement-supports-your-core-web-vitals/
 *
 * @package Optimization
 */

namespace XWP\Performance\Includes\LoadGutenbergStyleInline;

use function XWP\Performance\Includes\AdminSettings\get_settings;

/**
 * Enable separate loading of core block assets based on settings
 */
function maybe_load_separate_block_assets() {
	// Get settings
	$settings = get_settings();
	
	// Check if Gutenberg CSS inline is enabled
	if ( ! empty( $settings['gutenberg_css_inline_enabled'] ) ) {
		return true;
	}
	
	return false;
}

add_filter( 'should_load_separate_core_block_assets', __NAMESPACE__ . '\maybe_load_separate_block_assets' );
