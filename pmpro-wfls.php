<?php
/**
 * Plugin Name: PMPro Wordfence 2FA Integration
 * Plugin URI: https://github.com/yourusername/pmpro-wfls
 * Description: Enables Wordfence Login Security 2FA on Paid Memberships Pro login forms
 * Version: 1.0.1
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: pmpro-wfls
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants (Added conditional checks for safety)
if (!defined('PMPRO_WFLS_VERSION')) {
    define('PMPRO_WFLS_VERSION', '1.0.1');
}
if (!defined('PMPRO_WFLS_PLUGIN_DIR')) {
    define('PMPRO_WFLS_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('PMPRO_WFLS_PLUGIN_URL')) {
    define('PMPRO_WFLS_PLUGIN_URL', plugin_dir_url(__FILE__));
}

/**
 * Check if required plugins are active.
 *
 * @since 1.0.0
 * @return bool True if all dependencies are met, false otherwise.
 */
function pmpro_wfls_check_dependencies() {
    $missing_plugins = array();

    if (!class_exists('WordfenceLS\Controller_WordfenceLS')) {
        $missing_plugins[] = 'Wordfence Login Security';
    }

    if (!function_exists('pmpro_is_login_page')) {
        $missing_plugins[] = 'Paid Memberships Pro';
    }

    if (!empty($missing_plugins)) {
        add_action('admin_notices', function() use ($missing_plugins) {
            // Added capability check and proper escaping for security (XSS fix)
            if (!current_user_can('activate_plugins')) {
                return;
            }
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('PMPro Wordfence 2FA Integration requires the following plugins to be active:', 'pmpro-wfls') . ' ';
            echo esc_html(implode(', ', $missing_plugins));
            echo '</p></div>';
        });
        return false;
    }

    return true;
}

/**
 * Initialize the integration.
 *
 * @since 1.0.0
 * @return void
 */
function pmpro_wfls_init() {
    if (!pmpro_wfls_check_dependencies()) {
        return;
    }

    // Load text domain for translations
    load_plugin_textdomain('pmpro-wfls', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Add PMPro form detection to Wordfence
    add_action('wp_enqueue_scripts', 'pmpro_wfls_enqueue_scripts');

    // Extend Wordfence form detection via JavaScript (now using wp_add_inline_script)
    add_action('wp_enqueue_scripts', 'pmpro_wfls_extend_form_detection', 20); // Run after wordfence scripts are likely enqueued

    // Add PMPro login detection to Wordfence authentication
    add_filter('wfls_is_custom_login', 'pmpro_wfls_is_pmpro_login', 10, 1);
}

/**
 * Enqueue Wordfence scripts on PMPro login pages.
 *
 * NOTE: The original code used a fragile WooCommerce hook.
 * We now rely on Wordfence's default enqueueing or the inline script.
 * If Wordfence's script is not enqueued by default on PMPro login pages,
 * this function should be replaced with the correct Wordfence API call to enqueue the script.
 *
 * @since 1.0.1
 * @return void
 */
function pmpro_wfls_enqueue_scripts() {
    if (function_exists('pmpro_is_login_page') && pmpro_is_login_page()) {
        // Enqueue jQuery as a dependency for the inline script
        wp_enqueue_script('jquery');

        // Attempt to trigger Wordfence's login script enqueueing if necessary.
        // The correct hook/function is unknown without Wordfence API docs.
        // A common pattern is to use a hook that Wordfence listens to, e.g.,
        // do_action('login_enqueue_scripts'); // If PMPro page acts like a login page
        // For now, we rely on the inline script to check for the Wordfence script.
    }
}

/**
 * Extend Wordfence form detection to include PMPro forms.
 *
 * Replaced inline script with wp_add_inline_script for security and best practice.
 *
 * @since 1.0.1
 * @return void
 */
function pmpro_wfls_extend_form_detection() {
    if (!function_exists('pmpro_is_login_page') || !pmpro_is_login_page()) {
        return;
    }

    // Only proceed if Wordfence scripts are loaded
    if (!wp_script_is('wordfence-ls-login', 'enqueued')) {
        return;
    }

    // Output JavaScript to extend Wordfence's form detection using wp_add_inline_script (CSP/Security fix)
    $inline_script = '
    jQuery(document).ready(function($) {
        // Extend Wordfence login locator to include PMPro forms
        if (typeof loginLocator !== "undefined") {
            var originalLocate = loginLocator.locate;
            loginLocator.locate = function() {
                // First try original detection
                if (originalLocate.call(this)) {
                    return true;
                }

                // Try PMPro-specific detection
                var pmproInput = $(".pmpro_form input[name=username], .pmpro_login_wrap input[name=username]").first();
                if (pmproInput.length) {
                    this.input = pmproInput;
                    this.form = pmproInput.closest("form");
                    return this.form.length === 1;
                }

                return false;
            };
        }
    });
    ';

    // Add the script to the wordfence-ls-login handle
    wp_add_inline_script('wordfence-ls-login', $inline_script, 'after');
}

/**
 * Add PMPro login detection to Wordfence.
 *
 * @since 1.0.0
 * @param bool $is_custom_login Whether Wordfence has detected a custom login form.
 * @return bool
 */
function pmpro_wfls_is_pmpro_login($is_custom_login) {
    if ($is_custom_login) {
        return $is_custom_login;
    }

    // Check if this is a PMPro login request
    if (function_exists('pmpro_is_login_page') && pmpro_is_login_page()) {
        // High-priority fix: Use !empty() for better practice and check for required fields.
        // NOTE: Nonce verification (CSRF protection) should ideally be checked here,
        // but since this function only *detects* a login attempt for Wordfence,
        // and Wordfence/PMPro handle the actual authentication, we proceed with detection.
        if (!empty($_POST['username']) && !empty($_POST['password'])) {
            return true;
        }
    }

    return $is_custom_login;
}

// Initialize the plugin
add_action('plugins_loaded', 'pmpro_wfls_init');
