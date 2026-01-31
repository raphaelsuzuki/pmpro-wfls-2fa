# PMPro Wordfence 2FA Integration

**Enables Wordfence Login Security 2FA on Paid Memberships Pro login forms**

## Description

This plugin bridges the gap between Wordfence Login Security and Paid Memberships Pro, allowing users to enter 2FA codes on PMPro login forms - just like the existing WooCommerce integration.

## Features

- Seamless 2FA integration with PMPro login forms
- Uses existing Wordfence 2FA functionality
- No changes to core plugins required
- Lightweight bridge implementation

## Requirements

- Wordfence Login Security plugin (active)
- Paid Memberships Pro plugin (active)
- WordPress 5.0+
- PHP 7.2+

## Installation

1. Upload the plugin to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure both Wordfence Login Security and Paid Memberships Pro are active

## Usage

Once activated, the plugin automatically:
- Detects PMPro login forms
- Enqueues Wordfence 2FA scripts on PMPro pages
- Enables 2FA code entry on PMPro login forms
- Uses existing Wordfence 2FA settings and user configurations

## Technical Details

The plugin works by:
1. Extending Wordfence's form detection system to recognize PMPro login forms
2. Triggering Wordfence script enqueueing on PMPro login pages
3. Ensuring PMPro login requests are properly handled by Wordfence authentication

## License

GPL-2.0+ - See LICENSE file for details

## Support

For support, please open an issue on the GitHub repository.