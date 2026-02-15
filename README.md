[![Codacy Badge](https://app.codacy.com/project/badge/Grade/659e7985a0e743f78f6ee93a10487d9f)](https://app.codacy.com?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

# PMPro Wordfence 2FA

Bridges the gap between Wordfence Login Security and Paid Memberships Pro, enabling secure Two-Factor Authentication (2FA) directly on your membership login forms.

Keep your membership site secure by requiring 2FA for all users, including those logging in through custom PMPro forms. This plugin ensures that Wordfence's robust security features are fully operational wherever your users sign in.

**Notice:** This plugin requires both Paid Memberships Pro and Wordfence Login Security to be installed and active.

---

## Key Features

- **Seamless 2FA Integration**: Automatically detects PMPro login forms and injects the 2FA prompt when required.
- **Native Wordfence Support**: Uses your existing Wordfence settings, tokens, and recovery codes without additional configuration.
- **Improved User Experience**: Provides a consistent 2FA experience for members, matching the core WordPress and WooCommerce login flows.
- **Lightweight & Efficient**: Zero-configuration, lightweight bridge that hook directly into Wordfence's authentication system.
- **Admin Notifications**: In-dashboard alerts to help you ensure the integration is properly configured and active.

---

## Quick Start

### Installation

1. Upload the `pmpro-wfls-2fa` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Ensure **Wordfence Login Security** has 2FA or reCAPTCHA enabled.

### Requirements

- **Wordfence Login Security** (active)
- **Paid Memberships Pro** (active)
- **WordPress** (v5.0 or higher)
- **PHP** (v7.2 or higher)

---

## Technical Details

The plugin works by:
1. **Form Detection**: Extending Wordfence's JavaScript locator to recognize PMPro-specific form classes and input names.
2. **Script Enqueueing**: Ensuring Wordfence's frontend assets are loaded on any page where a PMPro login form appears.
3. **Authentication Hooks**: Filtering custom login requests to ensure Wordfence processes the 2FA verification before authentication is completed.

---

## Support

- **Issues:** [GitHub Issues](https://github.com/raphaelsuzuki/pmpro-wfls-2fa/issues)
- **Updates:** Automatic via Git Updater

## Contributing

Contributions are welcome! Please submit pull requests or open issues on GitHub.

- **Repository:** https://github.com/raphaelsuzuki/pmpro-wfls-2fa
- **Pull Requests:** Follow WordPress Coding Standards

## License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

## Disclaimer

This repository and its documentation were created with the assistance of AI. While efforts have been made to ensure accuracy and completeness, no guarantee is provided. Use at your own risk. Always test in a safe environment before deploying to production.
