<?php
/**
 * Plugin Name: PMPro Wordfence 2FA Integration
 * Plugin URI: https://github.com/yourusername/pmpro-wfls
 * Description: Enables Wordfence Login Security 2FA on Paid Memberships Pro login forms
 * Version: 1.0.0
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

// Define plugin constants
define('PMPRO_WFLS_VERSION', '1.0.0');
define('PMPRO_WFLS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PMPRO_WFLS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Check if required plugins are active
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
            echo '<div class="notice notice-error"><p>';
            echo __('PMPro Wordfence 2FA Integration requires the following plugins to be active:', 'pmpro-wfls') . ' ';
            echo implode(', ', $missing_plugins);
            echo '</p></div>';
        });
        return false;
    }
    
    return true;
}

// Initialize the integration
function pmpro_wfls_init() {
    if (!pmpro_wfls_check_dependencies()) {
        return;
    }
    
    // Load text domain for translations
    load_plugin_textdomain('pmpro-wfls', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Add PMPro form detection to Wordfence
    add_action('wp_enqueue_scripts', 'pmpro_wfls_enqueue_scripts');
    
    // Extend Wordfence form detection via JavaScript
    add_action('wp_footer', 'pmpro_wfls_extend_form_detection');
    
    // Add PMPro login detection to Wordfence authentication
    add_filter('wfls_is_custom_login', 'pmpro_wfls_is_pmpro_login', 10, 1);
}

// Enqueue Wordfence scripts on PMPro login pages
function pmpro_wfls_enqueue_scripts() {
    if (function_exists('pmpro_is_login_page') && pmpro_is_login_page()) {
        // Trigger Wordfence's login script enqueueing
        do_action('woocommerce_before_customer_login_form');
    }
}

// Extend Wordfence form detection to include PMPro forms
function pmpro_wfls_extend_form_detection() {
    if (!function_exists('pmpro_is_login_page') || !pmpro_is_login_page()) {
        return;
    }
    
    // Only proceed if Wordfence scripts are loaded
    if (!wp_script_is('wordfence-ls-login', 'enqueued')) {
        return;
    }
    
    // Output JavaScript to extend Wordfence's form detection
    echo '<script type="text/javascript">
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
    </script>';
}

// Add PMPro login detection to Wordfence
function pmpro_wfls_is_pmpro_login($is_custom_login) {
    if ($is_custom_login) {
        return $is_custom_login;
    }
    
    // Check if this is a PMPro login request
    if (function_exists('pmpro_is_login_page') && pmpro_is_login_page()) {
        if (isset($_POST['username'], $_POST['password'])) {
            return true;
        }
    }
    
    return $is_custom_login;
}

// Initialize the plugin
add_action('plugins_loaded', 'pmpro_wfls_init');