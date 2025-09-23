<?php
/**
 * Admin Settings Page for XWP Performance Optimizations
 *
 * @package Optimization
 */

namespace XWP\Performance\Includes\AdminSettings;

use const XWP\Performance\VERSION;

// Hook into WordPress admin
add_action( 'admin_menu', __NAMESPACE__ . '\add_admin_menu' );
add_action( 'admin_init', __NAMESPACE__ . '\settings_init' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_admin_scripts' );

// Clear cache when plugins/themes change
add_action( 'activated_plugin', __NAMESPACE__ . '\clear_performance_transients' );
add_action( 'deactivated_plugin', __NAMESPACE__ . '\clear_performance_transients' );
add_action( 'after_switch_theme', __NAMESPACE__ . '\clear_performance_transients' );
add_action( 'upgrader_process_complete', __NAMESPACE__ . '\clear_performance_transients' );

/**
 * Add admin menu item
 */
function add_admin_menu() {
	add_options_page(
		__( 'Performance - Critical Path', 'xwp-critical-path' ),
		__( 'Critical Path', 'xwp-critical-path' ),
		'manage_options',
		'xwp_critical_path',
		__NAMESPACE__ . '\options_page'
	);
}

/**
 * Initialize settings
 */
function settings_init() {
	register_setting( 'xwp_critical_path', 'xwp_performance_settings', array(
		'sanitize_callback' => __NAMESPACE__ . '\sanitize_settings'
	) );

	// Section 1: Dequeue Stylesheets
	add_settings_section(
		'xwp_dequeue_stylesheets_section',
		__( 'Dequeue Stylesheets', 'xwp-critical-path' ),
		__NAMESPACE__ . '\dequeue_stylesheets_section_callback',
		'xwp_critical_path'
	);

	add_settings_field(
		'dequeue_stylesheets_enabled',
		__( 'Enable Dequeue Stylesheets', 'xwp-critical-path' ),
		__NAMESPACE__ . '\dequeue_stylesheets_enabled_render',
		'xwp_critical_path',
		'xwp_dequeue_stylesheets_section'
	);

	add_settings_field(
		'dequeue_stylesheets_handles',
		__( 'Stylesheet Handles', 'xwp-critical-path' ),
		__NAMESPACE__ . '\dequeue_stylesheets_handles_render',
		'xwp_critical_path',
		'xwp_dequeue_stylesheets_section'
	);

	// Section 2: Dequeue Scripts
	add_settings_section(
		'xwp_dequeue_scripts_section',
		__( 'Dequeue Scripts', 'xwp-critical-path' ),
		__NAMESPACE__ . '\dequeue_scripts_section_callback',
		'xwp_critical_path'
	);

	add_settings_field(
		'dequeue_scripts_enabled',
		__( 'Enable Dequeue Scripts', 'xwp-critical-path' ),
		__NAMESPACE__ . '\dequeue_scripts_enabled_render',
		'xwp_critical_path',
		'xwp_dequeue_scripts_section'
	);

	add_settings_field(
		'dequeue_scripts_handles',
		__( 'Script Handles', 'xwp-critical-path' ),
		__NAMESPACE__ . '\dequeue_scripts_handles_render',
		'xwp_critical_path',
		'xwp_dequeue_scripts_section'
	);

	// Section 3: Defer Stylesheets
	add_settings_section(
		'xwp_defer_stylesheets_section',
		__( 'Globally Defer Stylesheets', 'xwp-critical-path' ),
		__NAMESPACE__ . '\defer_stylesheets_section_callback',
		'xwp_critical_path'
	);

	add_settings_field(
		'defer_stylesheets_enabled',
		__( 'Enable Defer Stylesheets', 'xwp-critical-path' ),
		__NAMESPACE__ . '\defer_stylesheets_enabled_render',
		'xwp_critical_path',
		'xwp_defer_stylesheets_section'
	);

	add_settings_field(
		'defer_stylesheets_blocking_handles',
		__( 'Render-Blocking Handles', 'xwp-critical-path' ),
		__NAMESPACE__ . '\defer_stylesheets_blocking_handles_render',
		'xwp_critical_path',
		'xwp_defer_stylesheets_section'
	);

	// Section 4: Defer Scripts
	add_settings_section(
		'xwp_defer_scripts_section',
		__( 'Globally Defer Scripts', 'xwp-critical-path' ),
		__NAMESPACE__ . '\defer_scripts_section_callback',
		'xwp_critical_path'
	);

	add_settings_field(
		'defer_scripts_enabled',
		__( 'Enable Defer Scripts', 'xwp-critical-path' ),
		__NAMESPACE__ . '\defer_scripts_enabled_render',
		'xwp_critical_path',
		'xwp_defer_scripts_section'
	);

	add_settings_field(
		'defer_scripts_blocking_handles',
		__( 'Blocking Script Handles', 'xwp-critical-path' ),
		__NAMESPACE__ . '\defer_scripts_blocking_handles_render',
		'xwp_critical_path',
		'xwp_defer_scripts_section'
	);

	// Section 5: Load Gutenberg CSS Inline
	add_settings_section(
		'xwp_gutenberg_css_section',
		__( 'Load Gutenberg CSS Inline', 'xwp-critical-path' ),
		__NAMESPACE__ . '\gutenberg_css_section_callback',
		'xwp_critical_path'
	);

	add_settings_field(
		'gutenberg_css_inline_enabled',
		__( 'Enable Gutenberg CSS Inline', 'xwp-critical-path' ),
		__NAMESPACE__ . '\gutenberg_css_inline_enabled_render',
		'xwp_critical_path',
		'xwp_gutenberg_css_section'
	);

	// Section 6: Preload Assets
	add_settings_section(
		'xwp_preload_assets_section',
		__( 'Preload Assets', 'xwp-critical-path' ),
		__NAMESPACE__ . '\preload_assets_section_callback',
		'xwp_critical_path'
	);

	add_settings_field(
		'preload_assets_enabled',
		__( 'Enable Preload Assets', 'xwp-critical-path' ),
		__NAMESPACE__ . '\preload_assets_enabled_render',
		'xwp_critical_path',
		'xwp_preload_assets_section'
	);

	add_settings_field(
		'preload_css_handles',
		__( 'CSS Handles', 'xwp-critical-path' ),
		__NAMESPACE__ . '\preload_css_handles_render',
		'xwp_critical_path',
		'xwp_preload_assets_section'
	);

	add_settings_field(
		'preload_custom_urls',
		__( 'Custom URLs', 'xwp-critical-path' ),
		__NAMESPACE__ . '\preload_custom_urls_render',
		'xwp_critical_path',
		'xwp_preload_assets_section'
	);
}

/**
 * Section callbacks
 */
function dequeue_stylesheets_section_callback() {
	echo '<p>' . esc_html__( 'Remove unused stylesheets from loading on your site.', 'xwp-critical-path' ) . '</p>';
}

function dequeue_scripts_section_callback() {
	echo '<p>' . esc_html__( 'Remove unused scripts from loading on your site.', 'xwp-critical-path' ) . '</p>';
}

function defer_stylesheets_section_callback() {
	echo '<p>' . esc_html__( 'Defer non-critical stylesheets to improve page load speed. Stylesheets NOT in the render-blocking list will be deferred.', 'xwp-critical-path' ) . '</p>';
}

function defer_scripts_section_callback() {
	echo '<p>' . esc_html__( 'Defer non-critical scripts using WordPress 6.3+ script loading strategies. Scripts NOT in the blocking list will be deferred.', 'xwp-critical-path' ) . '</p>';
}

function gutenberg_css_section_callback() {
	echo '<p>' . esc_html__( 'Load Gutenberg block CSS inline only when blocks are used on the page.', 'xwp-critical-path' ) . '</p>';
}

function preload_assets_section_callback() {
	echo '<p>' . esc_html__( 'Preload critical assets like CSS files and fonts to improve LCP (Largest Contentful Paint).', 'xwp-critical-path' ) . '</p>';
}

/**
 * Field render functions
 */
function dequeue_stylesheets_enabled_render() {
	$options = get_option( 'xwp_performance_settings' );
	$checked = isset( $options['dequeue_stylesheets_enabled'] ) ? $options['dequeue_stylesheets_enabled'] : 0;
	?>
	<input type='checkbox' name='xwp_performance_settings[dequeue_stylesheets_enabled]' 
		   value='1' <?php checked( $checked, 1 ); ?> 
		   class="xwp-feature-toggle" data-target="dequeue-stylesheets-config">
	<?php
}

function dequeue_stylesheets_handles_render() {
	$options = get_option( 'xwp_performance_settings' );
	$value = isset( $options['dequeue_stylesheets_handles'] ) ? $options['dequeue_stylesheets_handles'] : '';
	$enabled = isset( $options['dequeue_stylesheets_enabled'] ) ? $options['dequeue_stylesheets_enabled'] : 0;
	?>
	<div class="xwp-config-field" id="dequeue-stylesheets-config" style="<?php echo esc_attr( $enabled ? '' : 'display:none;' ); ?>">
		<textarea name='xwp_performance_settings[dequeue_stylesheets_handles]' 
				  rows='5' cols='50' 
				  placeholder="<?php echo esc_attr__( 'Enter stylesheet handles, one per line', 'xwp-critical-path' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description"><?php echo esc_html__( 'Enter the stylesheet handles to dequeue, one per line (e.g., handle-a)', 'xwp-critical-path' ); ?></p>
	</div>
	<?php
}

function dequeue_scripts_enabled_render() {
	$options = get_option( 'xwp_performance_settings' );
	$checked = isset( $options['dequeue_scripts_enabled'] ) ? $options['dequeue_scripts_enabled'] : 0;
	?>
	<input type='checkbox' name='xwp_performance_settings[dequeue_scripts_enabled]' 
		   value='1' <?php checked( $checked, 1 ); ?> 
		   class="xwp-feature-toggle" data-target="dequeue-scripts-config">
	<?php
}

function dequeue_scripts_handles_render() {
	$options = get_option( 'xwp_performance_settings' );
	$value = isset( $options['dequeue_scripts_handles'] ) ? $options['dequeue_scripts_handles'] : '';
	$enabled = isset( $options['dequeue_scripts_enabled'] ) ? $options['dequeue_scripts_enabled'] : 0;
	?>
	<div class="xwp-config-field" id="dequeue-scripts-config" style="<?php echo esc_attr( $enabled ? '' : 'display:none;' ); ?>">
		<textarea name='xwp_performance_settings[dequeue_scripts_handles]' 
				  rows='5' cols='50' 
				  placeholder="<?php echo esc_attr__( 'Enter script handles, one per line', 'xwp-critical-path' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description"><?php echo esc_html__( 'Enter the script handles to dequeue, one per line (e.g., handle-a)', 'xwp-critical-path' ); ?></p>
	</div>
	<?php
}

function defer_stylesheets_enabled_render() {
	$options = get_option( 'xwp_performance_settings' );
	$checked = isset( $options['defer_stylesheets_enabled'] ) ? $options['defer_stylesheets_enabled'] : 0;
	?>
	<input type='checkbox' name='xwp_performance_settings[defer_stylesheets_enabled]' 
		   value='1' <?php checked( $checked, 1 ); ?> 
		   class="xwp-feature-toggle" data-target="defer-stylesheets-config">
	<?php
}

function defer_stylesheets_blocking_handles_render() {
	$options = get_option( 'xwp_performance_settings' );
	$value = isset( $options['defer_stylesheets_blocking_handles'] ) ? $options['defer_stylesheets_blocking_handles'] : '';
	$enabled = isset( $options['defer_stylesheets_enabled'] ) ? $options['defer_stylesheets_enabled'] : 0;
	?>
	<div class="xwp-config-field" id="defer-stylesheets-config" style="<?php echo esc_attr( $enabled ? '' : 'display:none;' ); ?>">
		<textarea name='xwp_performance_settings[defer_stylesheets_blocking_handles]' 
				  rows='5' cols='50' 
				  placeholder="<?php echo esc_attr__( 'Enter render-blocking stylesheet handles, one per line', 'xwp-critical-path' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description"><?php echo esc_html__( 'Enter stylesheet handles that should remain render-blocking (not deferred), one per line (e.g., twenty-twenty-one-style)', 'xwp-critical-path' ); ?></p>
	</div>
	<?php
}

function defer_scripts_enabled_render() {
	$options = get_option( 'xwp_performance_settings' );
	$checked = isset( $options['defer_scripts_enabled'] ) ? $options['defer_scripts_enabled'] : 0;
	?>
	<input type='checkbox' name='xwp_performance_settings[defer_scripts_enabled]' 
		   value='1' <?php checked( $checked, 1 ); ?> 
		   class="xwp-feature-toggle" data-target="defer-scripts-config">
	<?php
}

function defer_scripts_blocking_handles_render() {
	$options = get_option( 'xwp_performance_settings' );
	$value = isset( $options['defer_scripts_blocking_handles'] ) ? $options['defer_scripts_blocking_handles'] : '';
	$enabled = isset( $options['defer_scripts_enabled'] ) ? $options['defer_scripts_enabled'] : 0;
	?>
	<div class="xwp-config-field" id="defer-scripts-config" style="<?php echo esc_attr( $enabled ? '' : 'display:none;' ); ?>">
		<textarea name='xwp_performance_settings[defer_scripts_blocking_handles]' 
				  rows='5' cols='50' 
				  placeholder="<?php echo esc_attr__( 'Enter blocking script handles, one per line', 'xwp-critical-path' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description"><?php echo esc_html__( 'Enter script handles that should NOT be deferred (remain blocking), one per line (e.g., handle-a)', 'xwp-critical-path' ); ?></p>
	</div>
	<?php
}

function gutenberg_css_inline_enabled_render() {
	$options = get_option( 'xwp_performance_settings' );
	$checked = isset( $options['gutenberg_css_inline_enabled'] ) ? $options['gutenberg_css_inline_enabled'] : 0;
	?>
	<input type='checkbox' name='xwp_performance_settings[gutenberg_css_inline_enabled]' 
		   value='1' <?php checked( $checked, 1 ); ?>>
	<p class="description"><?php echo esc_html__( 'Load Gutenberg block CSS inline only when the block is used on the page', 'xwp-critical-path' ); ?></p>
	<?php
}

function preload_assets_enabled_render() {
	$options = get_option( 'xwp_performance_settings' );
	$checked = isset( $options['preload_assets_enabled'] ) ? $options['preload_assets_enabled'] : 0;
	?>
	<input type='checkbox' name='xwp_performance_settings[preload_assets_enabled]' 
		   value='1' <?php checked( $checked, 1 ); ?> 
		   class="xwp-feature-toggle" data-target="preload-assets-config">
	<?php
}

function preload_css_handles_render() {
	$options = get_option( 'xwp_performance_settings' );
	$value = isset( $options['preload_css_handles'] ) ? $options['preload_css_handles'] : '';
	$enabled = isset( $options['preload_assets_enabled'] ) ? $options['preload_assets_enabled'] : 0;
	?>
	<div class="xwp-config-field" id="preload-assets-config-css" style="<?php echo esc_attr( $enabled ? '' : 'display:none;' ); ?>">
		<textarea name='xwp_performance_settings[preload_css_handles]' 
				  rows='5' cols='50' 
				  placeholder="<?php echo esc_attr__( 'Enter CSS handles to preload, one per line', 'xwp-critical-path' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description"><?php echo esc_html__( 'Enter CSS stylesheet handles to preload, one per line (e.g., handle-a)', 'xwp-critical-path' ); ?></p>
	</div>
	<?php
}

function preload_custom_urls_render() {
	$options = get_option( 'xwp_performance_settings' );
	$value = isset( $options['preload_custom_urls'] ) ? $options['preload_custom_urls'] : '';
	$enabled = isset( $options['preload_assets_enabled'] ) ? $options['preload_assets_enabled'] : 0;
	?>
	<div class="xwp-config-field" id="preload-assets-config-urls" style="<?php echo esc_attr( $enabled ? '' : 'display:none;' ); ?>">
		<textarea name='xwp_performance_settings[preload_custom_urls]' 
				  rows='5' cols='50' 
				  placeholder="<?php echo esc_attr__( 'Enter custom URLs to preload, one per line', 'xwp-critical-path' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description"><?php echo esc_html__( 'Enter custom URLs to preload (e.g., font URLs), one per line. You can use relative paths like /wp-content/themes/your-theme/assets/fonts/font.woff2', 'xwp-critical-path' ); ?></p>
	</div>
	<?php
}

/**
 * Options page output
 */
function options_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Performance Critical Path', 'xwp-critical-path' ); ?></h1>
		<form action='options.php' method='post'>
			<?php
			settings_fields( 'xwp_critical_path' );
			do_settings_sections( 'xwp_critical_path' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Enqueue admin scripts
 */
function enqueue_admin_scripts( $hook ) {
	if ( 'settings_page_xwp_critical_path' !== $hook ) {
		return;
	}
	
	// Enqueue admin JavaScript
	wp_enqueue_script(
		'xwp-admin-settings',
		plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/admin-settings.js',
		array(),
		VERSION,
		true
	);
	
	// Enqueue admin CSS
	wp_enqueue_style(
		'xwp-admin-settings',
		plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/admin-settings.css',
		array(),
		VERSION
	);
}

/**
 * Helper function to get settings
 */
function get_settings() {
	return get_option( 'xwp_performance_settings', array() );
}

/**
 * Clear performance-related transients
 */
function clear_performance_transients() {
	global $wpdb;
	
	// Clear all transients related to our performance optimizations
	// Using a SQL query to clear all transients with our prefix
	$wpdb->query( 
		$wpdb->prepare( 
			"DELETE FROM {$wpdb->options} 
			WHERE option_name LIKE %s 
			OR option_name LIKE %s
			OR option_name LIKE %s
			OR option_name LIKE %s",
			'_transient_xwp_defer_scripts_%',
			'_transient_timeout_xwp_defer_scripts_%',
			'_transient_xwp_defer_styles_%',
			'_transient_timeout_xwp_defer_styles_%'
		)
	);
	
	// Also clear object cache if available
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
}

/**
 * Sanitize settings before saving
 */
function sanitize_settings( $input ) {
	// Clear transients when settings are updated
	clear_performance_transients();
	
	$sanitized = array();
	
	// Checkboxes - ensure they're either 1 or 0
	$checkbox_fields = array(
		'dequeue_stylesheets_enabled',
		'dequeue_scripts_enabled',
		'defer_stylesheets_enabled',
		'defer_scripts_enabled',
		'gutenberg_css_inline_enabled',
		'preload_assets_enabled'
	);
	
	foreach ( $checkbox_fields as $field ) {
		$sanitized[ $field ] = ! empty( $input[ $field ] ) ? 1 : 0;
	}
	
	// Textarea fields - sanitize handles (alphanumeric, dashes, underscores)
	$textarea_fields = array(
		'dequeue_stylesheets_handles',
		'dequeue_scripts_handles',
		'defer_stylesheets_blocking_handles',
		'defer_scripts_blocking_handles',
		'preload_css_handles'
	);
	
	foreach ( $textarea_fields as $field ) {
		if ( isset( $input[ $field ] ) ) {
			// Split by newlines, sanitize each handle
			$handles = explode( "\n", $input[ $field ] );
			$clean_handles = array();
			foreach ( $handles as $handle ) {
				$handle = trim( $handle );
				// Allow alphanumeric, dashes, underscores, and dots (for namespaced handles)
				if ( preg_match( '/^[a-zA-Z0-9_\-\.]+$/', $handle ) ) {
					$clean_handles[] = $handle;
				}
			}
			$sanitized[ $field ] = implode( "\n", $clean_handles );
		} else {
			$sanitized[ $field ] = '';
		}
	}
	
	// Custom URLs field - sanitize URLs
	if ( isset( $input['preload_custom_urls'] ) ) {
		$urls = explode( "\n", $input['preload_custom_urls'] );
		$clean_urls = array();
		foreach ( $urls as $url ) {
			$url = trim( $url );
			// Basic URL validation - allow relative and absolute URLs
			if ( ! empty( $url ) ) {
				// If it's a relative URL or absolute URL
				if ( strpos( $url, '/' ) === 0 || filter_var( $url, FILTER_VALIDATE_URL ) ) {
					$clean_urls[] = esc_url_raw( $url );
				} elseif ( preg_match( '/^[a-zA-Z0-9_\-\.\/]+\.(woff2?|ttf|otf|eot|css|js|jpg|jpeg|png|gif|svg|webp)$/i', $url ) ) {
					// Allow relative paths with valid extensions
					$clean_urls[] = $url;
				}
			}
		}
		$sanitized['preload_custom_urls'] = implode( "\n", $clean_urls );
	} else {
		$sanitized['preload_custom_urls'] = '';
	}
	
	return $sanitized;
}

/**
 * Helper function to parse textarea values into array
 */
function parse_textarea_lines( $value ) {
	if ( empty( $value ) ) {
		return array();
	}
	
	$lines = explode( "\n", $value );
	$lines = array_map( 'trim', $lines );
	$lines = array_filter( $lines ); // Remove empty lines
	
	return $lines;
}
