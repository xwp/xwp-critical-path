# XWP Critical Path

A comprehensive WordPress performance optimization plugin focused on improving Largest Contentful Paint (LCP) and overall site performance through careful asset management and loading strategies.

## Features

- **Asset Deferral & Optimization**
  - Globally defer non-critical JavaScript with configurable exceptions
  - Defer non-critical stylesheets while preserving render-blocking critical CSS
  - Smart detection of print stylesheets
  - WordPress 6.3+ script loading strategies support

- **Asset Management**
  - Remove unused stylesheets from specific pages
  - Remove unnecessary scripts to reduce payload
  - Granular control over WordPress core and plugin assets
  - Handle-based configuration for precise control

- **Resource Preloading**
  - Preload critical CSS files with automatic versioning
  - Preload custom resources (fonts, images, etc.)
  - Intelligent resource type detection
  - Support for both absolute and relative URLs

- **Gutenberg Optimization**
  - Load block CSS inline only when blocks are used
  - Reduces unused CSS on pages without specific blocks
  - Automatic per-page optimization

- **Performance Features**
  - Transient caching for improved performance on high-traffic sites
  - Automatic cache invalidation on configuration changes
  - Request-level caching to minimize database queries
  - Smart cache clearing on plugin/theme changes

## Installation

1. Upload the `xwp-critical-path` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings under Settings > Performance Custom

## Configuration

### Dequeue Stylesheets

Remove unnecessary stylesheets from loading:

1. Enable the feature via checkbox
2. Enter stylesheet handles (one per line) to remove
3. Example: `unnecessary-plugin-style`

### Dequeue Scripts

Remove unused JavaScript files:

1. Enable the feature
2. List script handles to remove
3. Example: `jquery-migrate` (if not needed)

### Globally Defer Stylesheets

Defer all stylesheets except critical ones:

1. Enable stylesheet deferral
2. List handles that should remain render-blocking (critical CSS)
3. Example: `twenty-twenty-one-style` for main theme styles

### Globally Defer Scripts

Use modern loading strategies for JavaScript:

1. Enable script deferral
2. List handles that must remain blocking
3. Scripts not listed will automatically use defer loading

### Load Gutenberg CSS Inline

Optimize block editor styles:

1. Simple checkbox to enable/disable
2. Automatically loads block CSS only when blocks are present

### Preload Assets

Accelerate loading of critical resources:

1. Enable preloading
2. **CSS Handles**: Enter registered stylesheet handles to preload
3. **Custom URLs**: Add direct URLs for fonts and other resources
   - Relative: `/wp-content/themes/your-theme/fonts/font.woff2`
   - Absolute: `https://example.com/critical.css`

## Architecture

### File Structure

```text
xwp-critical-path/
├── xwp-critical-path.php         # Main plugin file with constants
├── includes/
│   ├── admin-settings.php       # Settings page and admin interface
│   ├── defer-scripts.php        # Script deferral logic
│   ├── defer-stylesheets.php    # Stylesheet deferral logic
│   ├── dequeue-scripts.php      # Script removal functionality
│   ├── dequeue-stylesheet.php   # Stylesheet removal functionality
│   ├── load-gutenberg-css-inline.php # Block CSS optimization
│   └── preload-assets.php       # Resource preloading
├── assets/
│   ├── css/
│   │   └── admin-settings.css   # Admin interface styles
│   └── js/
│       └── admin-settings.js    # Admin UI enhancements
└── README.md
```

### Core Components

- **Admin Settings**: Centralized configuration management with sanitization
- **Defer Scripts**: Modern script loading using WordPress 6.3+ strategies
- **Defer Stylesheets**: Non-blocking CSS with noscript fallbacks
- **Dequeue Assets**: Selective removal of unnecessary resources
- **Preload Assets**: Critical resource prioritization
- **Gutenberg Optimization**: Smart block CSS loading

### Performance Optimizations

- **Transient Caching**: 12-hour cache for processed handles
- **Request-Level Caching**: Global variables prevent redundant processing
- **Smart Invalidation**: Automatic cache clearing on:
  - Settings updates
  - Plugin activation/deactivation
  - Theme switches
  - WordPress updates

## Hooks and Actions

### Actions Used

- `wp_enqueue_scripts` - Asset modifications (priorities 100, 999)
- `wp_head` - Preload tag injection (priority 1)
- `admin_menu` - Settings page registration
- `admin_init` - Settings initialization
- `style_loader_tag` - Stylesheet deferral
- `should_load_separate_core_block_assets` - Gutenberg optimization

### Cache Management Hooks

- `activated_plugin` - Clear cache on plugin activation
- `deactivated_plugin` - Clear cache on plugin deactivation
- `after_switch_theme` - Clear cache on theme change
- `upgrader_process_complete` - Clear cache after updates

## Developer API

### Accessing Plugin Settings

```php
use function XWP\Performance\Includes\AdminSettings\get_settings;

// Get all settings
$settings = get_settings();

// Check if a feature is enabled
if ( ! empty( $settings['defer_scripts_enabled'] ) ) {
    // Feature is active
}
```

### Parsing Configuration

```php
use function XWP\Performance\Includes\AdminSettings\parse_textarea_lines;

// Convert textarea input to array
$handles = parse_textarea_lines( $settings['dequeue_scripts_handles'] );
```

### Manual Cache Clearing

```php
use function XWP\Performance\Includes\AdminSettings\clear_performance_transients;

// Clear all performance-related caches
clear_performance_transients();
```

## Security

- **Input Sanitization**: All user inputs validated and sanitized
- **Output Escaping**: Proper escaping with `esc_attr()`, `esc_url()`, `esc_textarea()`
- **Nonce Verification**: WordPress settings API handles nonces
- **Capability Checks**: Admin-only access via `manage_options`
- **Safe Regex Patterns**: Validated handle names and URLs

## Performance Impact

### Benefits

- Reduced render-blocking resources
- Improved LCP (Largest Contentful Paint)
- Optimized Critical Rendering Path

## Requirements

- WordPress 6.3 or higher
- PHP 8.0 or higher

## Best Practices

### Critical CSS

1. Identify above-the-fold styles
2. Keep these as render-blocking in settings
3. Defer everything else

### Font Loading

1. Host fonts locally when possible
2. Preload the specific custom font files used by the title or body text

### Script Deferral

1. Keep user-critical scripts blocking
2. Defer analytics and non-critical functionality
3. Test thoroughly after changes

## Troubleshooting

### Styles Not Loading

- Check if critical styles are in the render-blocking list
- Verify handle names match exactly
- Clear browser cache after changes

### JavaScript Errors

- Some scripts may need to remain blocking
- Check browser console for dependency issues, as for example inline scripts that have dependencies will require them to be added to the blocking script handles allow list.
- Add problematic scripts to blocking list

### Cache Issues

- Settings changes automatically clear cache
- Cache is also cleared when plugins/themes change
- Transients expire after 12 hours automatically

## License

GPLv2 or later

## Support

For issues, feature requests, or contributions, please contact XWP or use the plugin's support channels.

## Changelog

### 1.0.0

- Initial release with six optimization modules
- Admin interface for configuration
- Transient caching system
- Smart cache invalidation
- WordPress VIP coding standards compliance
- Comprehensive input sanitization
- Performance-focused architecture

### 1.0.1

- Remove transient caching system
- Add support for internationalization
