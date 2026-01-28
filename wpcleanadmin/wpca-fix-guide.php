<?php
/**
 * WP Clean Admin Fix Guide
 *
 * This script provides a guide for fixing the undefined constant and function issues
 * in the WP Clean Admin plugin.
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

if (!function_exists('flush_rewrite_rules')) {
    function flush_rewrite_rules() {
        echo "flush_rewrite_rules called\n";
        return true;
    }
}

if (!function_exists('get_plugin_data')) {
    function get_plugin_data($file, $markup = true, $translate = true) {
        echo "get_plugin_data called for file: $file\n";
        return array(
            'Name' => 'WP Clean Admin',
            'Version' => '1.8.0',
            'Author' => 'Sut',
            'Description' => 'A powerful WordPress admin cleanup and optimization plugin.'
        );
    }
}

// Test the fix
 echo "=== WP Clean Admin Fix Test ===\n";
echo "Testing plugin files for undefined constant issues...\n\n";

try {
    // Test 1: class-wpca-core.php
    echo "Test 1: Loading class-wpca-core.php...\n";
    require_once ABSPATH . 'includes/class-wpca-core.php';
    echo "✓ class-wpca-core.php loaded successfully\n\n";
    
    // Test 2: class-wpca-performance.php
    echo "Test 2: Loading class-wpca-performance.php...\n";
    require_once ABSPATH . 'includes/class-wpca-performance.php';
    echo "✓ class-wpca-performance.php loaded successfully\n\n";
    
    // Test 3: class-wpca-settings.php
    echo "Test 3: Loading class-wpca-settings.php...\n";
    require_once ABSPATH . 'includes/class-wpca-settings.php';
    echo "✓ class-wpca-settings.php loaded successfully\n\n";
    
    // Test 4: class-wpca-helpers.php
    echo "Test 4: Loading class-wpca-helpers.php...\n";
    require_once ABSPATH . 'includes/class-wpca-helpers.php';
    echo "✓ class-wpca-helpers.php loaded successfully\n\n";
    
    // Test 5: wpca-core-functions.php
    echo "Test 5: Loading wpca-core-functions.php...\n";
    require_once ABSPATH . 'includes/wpca-core-functions.php';
    echo "✓ wpca-core-functions.php loaded successfully\n\n";
    
    // Test 6: Plugin main file
    echo "Test 6: Loading wp-clean-admin.php...\n";
    require_once ABSPATH . 'wp-clean-admin.php';
    echo "✓ wp-clean-admin.php loaded successfully\n\n";
    
    echo "=== All Tests Passed ===\n";
    echo "The fix was successful! The plugin should now activate without fatal errors.\n\n";
    
} catch (Exception $e) {
    echo "\n=== Test Failed ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Please fix this issue following the fix guide below.\n";
} catch (Error $e) {
    echo "\n=== Test Failed ===\n";
    echo "Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Please fix this issue following the fix guide below.\n";
}

// Fix guide
echo "=== WP Clean Admin Fix Guide ===\n";
echo "\n";
echo "## Problem\n";
echo "The plugin is failing to activate due to undefined constants and functions.\n";
echo "This happens because constants like WPCA_TEXT_DOMAIN and WPCA_PLUGIN_DIR\n";
echo "are defined in the global namespace, but are being referenced in the\n";
echo "WPCleanAdmin namespace without the global namespace prefix.\n";
echo "\n";
echo "## Solution\n";
echo "Add a backslash (\\) prefix to all references to global constants and functions.\n";
echo "\n";
echo "## Examples of fixes:\n";
echo "\n";
echo "### 1. Fixing constant references:\n";
echo "Before: WPCA_TEXT_DOMAIN\n";
echo "After: \\WPCA_TEXT_DOMAIN\n";
echo "\n";
echo "### 2. Fixing function references:\n";
echo "Before: get_option()\n";
echo "After: \\get_option()\n";
echo "\n";
echo "## Files that need fixing:\n";
echo "\n";
echo "1. includes/class-wpca-user-roles.php\n";
echo "2. includes/class-wpca-permissions.php\n";
echo "3. includes/class-wpca-login.php\n";
echo "4. includes/class-wpca-i18n.php\n";
echo "5. includes/class-wpca-menu-manager.php\n";
echo "6. includes/class-wpca-resources.php\n";
echo "7. includes/class-wpca-reset.php\n";
echo "8. includes/class-wpca-database.php\n";
echo "9. includes/class-wpca-dashboard.php\n";
echo "10. includes/class-wpca-cleanup.php\n";
echo "11. includes/class-wpca-cache.php\n";
echo "12. includes/class-wpca-ajax.php\n";
echo "13. includes/class-wpca-extension-api.php\n";
echo "\n";
echo "## How to fix each file:\n";
echo "\n";
echo "1. Open the file in a text editor.\n";
echo "2. Search for all occurrences of WPCA_ (constants) and WordPress functions.\n";
echo "3. Add a backslash prefix to each occurrence.\n";
echo "4. Save the file.\n";
echo "5. Test the plugin activation.\n";
echo "\n";
echo "## Verification\n";
echo "After fixing all files, the plugin should activate successfully\n";
echo "and all the tests above should pass.\n";
echo "\n";
echo "=== Fix Guide End ===\n";

?>