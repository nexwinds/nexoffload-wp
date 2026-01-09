=== Nexoffload ===
Contributors: nexwinds
Tags: media, optimization, performance
Requires at least: 5.3
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Seamlessly optimize WordPress media for blazing-fast delivery and lighter server load.

## Description

Nexoffload is a WordPress plugin that offers powerful media optimization:

1. **Optimization**: Converts local media files to modern formats (AVIF/WebP) using the NW Optimization API, with batch processing of up to 5 images at a time.

## Features

- Automatically optimize media on upload (optional)
- Batch processing for efficient operations
- Media Library integration with status indicators
- Detailed statistics and progress tracking
- Transparent eligibility criteria with detailed counts for each requirement

## Requirements

- WordPress 5.3 or higher
- PHP 7.2 or higher
- HTTPS enabled (required for NOFB API)
- Nexwinds NOFB API key for optimization

## Installation

1. Upload the `nexoffload-for-bunny` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add your API credentials to `wp-config.php`:

```php
// Nexwinds NOFB API credentials
define('NOFB_API_KEY', 'your-nofb-api-key');
define('NOFB_API_REGION', 'us'); // or 'eu' for EU region
```

4. Configure settings in the Nexoffload Media admin page

## Configuration

- **Auto Optimize**: Automatically optimize media files on upload
- **File Versioning**: Add version parameters to URLs for cache busting
- **Maximum File Size**: Size threshold for optimization eligibility

## Usage

### Optimization

1. Go to Nexoffload Media > Optimize
2. Click "Scan Media Library" to find eligible files
3. Click "Process Queue" to start optimization
4. Monitor progress in the dashboard

#### Optimization Eligibility Criteria

**Optimization support:** JPEG, JPG, PNG, HEIC, TIFF, AVIF and WEBP.

Files must meet ALL of the following criteria to be eligible for optimization:
- Must be stored locally
- File size must be larger than the configured threshold and less than 10MB
- Must be one of the supported file types: JPEG, JPG, PNG, HEIC, TIFF, AVIF, WEBP

**Note:** Files of type JPEG, JPG, PNG, HEIC, TIFF are eligible for optimization regardless of size.

### Media Library Integration

The Media Library list view includes a "Nexoffload Media" column showing the status of each image:
- Not Image: Non-image files (not applicable)
- Not Eligible: Files that don't meet criteria
- Eligible for Optimization: Files ready for optimization
- Optimized: Files that have been optimized

## License

This plugin is licensed under the GPLv3.

## Changelog

### 1.0.0
* Initial release

