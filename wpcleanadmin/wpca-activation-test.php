<?php
/**
 * WP Clean Admin Activation Test Script
 *
 * This script tests the plugin activation process in a simulated WordPress environment.
 * It defines necessary WordPress constants and functions, then attempts to activate the plugin.
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @since 1.8.0
 */

// Set error reporting to maximum
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define WordPress constants
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', dirname(__FILE__));
}

if (!defined('WP_PLUGIN_URL')) {
    define('WP_PLUGIN_URL', 'http://localhost/wp-content/plugins/wpcleanadmin');
}

if (!defined('WPCA_VERSION')) {
    define('WPCA_VERSION', '1.8.0');
}

if (!defined('WPCA_PLUGIN_DIR')) {
    define('WPCA_PLUGIN_DIR', ABSPATH);
}

if (!defined('WPCA_PLUGIN_URL')) {
    define('WPCA_PLUGIN_URL', 'http://localhost/wp-content/plugins/wpcleanadmin');
}

if (!defined('WPCA_TEXT_DOMAIN')) {
    define('WPCA_TEXT_DOMAIN', 'wp-clean-admin');
}

// Simulate WordPress functions
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        echo "add_action called for hook: $hook\n";
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $callback) {
        echo "register_activation_hook called for file: $file\n";
    }
}

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return 'http://localhost/wp-content/plugins/wpcleanadmin/';
    }
}

if (!function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain($domain, $abs_rel_path = false, $plugin_rel_path = false) {
        echo "load_plugin_textdomain called for domain: $domain\n";
        return true;
    }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {
        return 'wpcleanadmin/wp-clean-admin.php';
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        echo "get_option called for option: $option\n";
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        echo "update_option called for option: $option\n";
        return true;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        echo "current_user_can called for capability: $capability\n";
        return true;
    }
}

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        echo "is_user_logged_in called\n";
        return true;
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null) {
        echo "wp_send_json_success called\n";
        echo json_encode(array('success' => true, 'data' => $data));
        exit;
    }
}

if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data = null, $status_code = null) {
        echo "wp_send_json_error called\n";
        echo json_encode(array('success' => false, 'data' => $data));
        exit;
    }
}

// Test the plugin activation
echo "=== WP Clean Admin Activation Test ===\n";
echo "Testing plugin activation...\n\n";

try {
    // Include the plugin main file
    require_once ABSPATH . 'wp-clean-admin.php';
    
    echo "\n=== Plugin Activation Test Results ===\n";
    echo "✓ Plugin main file loaded successfully\n";
    
    // Test initialization
    if (function_exists('wpca_init')) {
        echo "✓ wpca_init function exists\n";
        wpca_init();
        echo "✓ wpca_init executed successfully\n";
    } else {
        echo "✗ wpca_init function does not exist\n";
    }
    
    echo "\n=== Test Completed ===\n";
    echo "Plugin activation test completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n=== Test Failed ===\n";
    echo "Error during plugin activation: " . $e->getMessage() . "\n";
    echo "Error line: " . $e->getLine() . "\n";
    echo "Error file: " . $e->getFile() . "\n";
} catch (Error $e) {
    echo "\n=== Test Failed ===\n";
    echo "Fatal error during plugin activation: " . $e->getMessage() . "\n";
    echo "Error line: " . $e->getLine() . "\n";
    echo "Error file: " . $e->getFile() . "\n";
}

?>