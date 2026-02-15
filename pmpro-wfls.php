<?php
/**
 * Plugin Name: WFLS 2FA for Paid Memberships Pro
 * Plugin URI: https://github.com/raphaelsuzuki/pmpro-wfls-2fa
 * Description: Enables Wordfence Login Security 2FA on Paid Memberships Pro login forms
 * Version: 1.0.1
 * Author: Raphael Suzuki
 * Author URI: https://raybeam.jp
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: pmpro-wfls-2fa
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants (Added conditional checks for safety)
if (!defined('PMPRO_WFLS_2FA_VERSION')) {
    define('PMPRO_WFLS_2FA_VERSION', '1.0.1');
}
if (!defined('PMPRO_WFLS_2FA_PLUGIN_DIR')) {
    define('PMPRO_WFLS_2FA_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('PMPRO_WFLS_2FA_PLUGIN_URL')) {
    define('PMPRO_WFLS_2FA_PLUGIN_URL', plugin_dir_url(__FILE__));
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
            echo esc_html__('PMPro Wordfence 2FA Integration requires the following plugins to be active:', 'pmpro-wfls-2fa') . ' ';
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
    load_plugin_textdomain('pmpro-wfls-2fa', false, dirname(plugin_basename(__FILE__)) . '/languages');

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
 * Wordfence only enqueues its login script on wp-login.php and WooCommerce hooks.
 * We call Wordfence's public _login_enqueue_scripts() so 2FA/CAPTCHA run on PMPro login.
 *
 * @since 1.0.1
 * @return void
 */
function pmpro_wfls_enqueue_scripts() {
    if (!function_exists('pmpro_is_login_page') || !pmpro_is_login_page()) {
        return;
    }
    if (!class_exists('WordfenceLS\Controller_WordfenceLS')) {
        return;
    }
    \WordfenceLS\Controller_WordfenceLS::shared()->_login_enqueue_scripts();
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

        // Add login-submit class to submit paragraphs for styling (main form + WFLS overlay when it appears)
        $(".pmpro_form p.submit, .pmpro_login_wrap p.submit").addClass("login-submit");
        var observer = new MutationObserver(function() {
            $("#wfls-prompt-overlay p.submit").addClass("login-submit");
            $("#wfls-token").closest("p").addClass("login-password");
            if ($("#wfls-prompt-overlay").length) {
                $("#wp-submit").hide();
            } else {
                $("#wp-submit").show();
            }
        });
        var formEl = $(".pmpro_form, .pmpro_login_wrap").get(0);
        if (formEl) {
            observer.observe(formEl, { childList: true, subtree: true });
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

/**
 * Show an admin notice when Wordfence 2FA and reCAPTCHA are both disabled.
 *
 * In that case Wordfence does not enqueue its login script, so our form detection
 * is never attached and the integration is inactive. This notice avoids silent
 * failure so admins know to enable 2FA or reCAPTCHA in Wordfence if they want the
 * integration on PMPro login pages.
 *
 * @since 1.0.2
 * @return void
 */
function pmpro_wfls_maybe_notice_integration_inactive() {
    if (!current_user_can('manage_options')) {
        return;
    }
    if (!class_exists('WordfenceLS\Controller_CAPTCHA') || !class_exists('WordfenceLS\Controller_Users')) {
        return;
    }
    $captcha_enabled = \WordfenceLS\Controller_CAPTCHA::shared()->enabled();
    $any_2fa = \WordfenceLS\Controller_Users::shared()->any_2fa_active();
    if ($captcha_enabled || $any_2fa) {
        return;
    }
    $url = is_multisite() ? network_admin_url('admin.php?page=WFLS') : admin_url('admin.php?page=WFLS');
    $message = sprintf(
        /* translators: %s: link to Wordfence Login Security settings */
        __('PMPro Wordfence 2FA Integration is active but not running on PMPro login: 2FA and reCAPTCHA are both disabled in %s. Enable at least one for the integration to run.', 'pmpro-wfls-2fa'),
        '<a href="' . esc_url($url) . '">' . esc_html__('Wordfence Login Security', 'pmpro-wfls-2fa') . '</a>'
    );
    echo '<div class="notice notice-info"><p>' . wp_kses($message, array('a' => array('href' => array()))) . '</p></div>';
}

// Initialize the plugin
add_action('plugins_loaded', 'pmpro_wfls_init');