<?php
/**
 * Plugin Name: XWP Critical Path
 * Plugin URI: https://xwp.co/
 * Description: Site-specific LCP-focused performance optimizations
 * Version: 1.0.0
 * Author: XWP
 * Author URI: https://xwp.co/
 *
 * @package Optimization
 */

namespace XWP\Performance;

const MAIN_DIR = __DIR__;
const VERSION = '1.0.1';

// Initialize text domain for internationalization
add_action( 'init', __NAMESPACE__ . '\load_textdomain' );

/**
 * Load plugin text domain for internationalization
 */
function load_textdomain() {
	load_plugin_textdomain( 
		'xwp-critical-path', 
		false, 
		dirname( plugin_basename( __FILE__ ) ) . '/languages/' 
	);
}

// Priority constants for better maintainability
const PRIORITY_EARLY = 1;       // For actions that need to run very early
const PRIORITY_DEFAULT = 10;    // WordPress default priority
const PRIORITY_LATE = 100;      // For cleanup and late modifications
const PRIORITY_VERY_LATE = 999; // For final modifications

// ======================================================================
// Admin Settings
// ======================================================================

// Load admin settings page
require_once MAIN_DIR . '/includes/admin-settings.php';

// Add settings link on plugins page
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), __NAMESPACE__ . '\add_settings_link' );

/**
 * Add settings link to plugins page
 * 
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links.
 */
function add_settings_link( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=xwp_critical_path' ) ) . '">' . __( 'Settings', 'xwp-critical-path' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

// ======================================================================
// Disable assets.
// ======================================================================

// Disable stylesheets.
require_once MAIN_DIR . '/includes/dequeue-stylesheet.php';
// Disable scripts.
require_once MAIN_DIR . '/includes/dequeue-scripts.php';

// ======================================================================
// Set assets as non render blocking.
// ======================================================================

// Defer stylesheets.
require_once MAIN_DIR . '/includes/defer-stylesheets.php';
// Defer JS scripts.
require_once MAIN_DIR . '/includes/defer-scripts.php';

// ======================================================================
// Load assets quickly.
// ======================================================================

// Load Gutenberg block CSS library inline.
require_once MAIN_DIR . '/includes/load-gutenberg-css-inline.php';
// Preload assets (CSS, fonts).
require_once MAIN_DIR . '/includes/preload-assets.php';
