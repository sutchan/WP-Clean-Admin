<?php
/**
 * Test script to check plugin activation and initialization
 *
 * This script simulates a WordPress environment to test if the plugin
 * can be activated and initialized without errors.
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @since 1.8.0
 */

// Set up minimal WordPress environment
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

if ( ! defined( 'WPCA_VERSION' ) ) {
    define( 'WPCA_VERSION', '1.8.0' );
}

if ( ! defined( 'WPCA_PLUGIN_DIR' ) ) {
    define( 'WPCA_PLUGIN_DIR', ABSPATH );
}

if ( ! defined( 'WPCA_PLUGIN_URL' ) ) {
    define( 'WPCA_PLUGIN_URL', 'http://localhost/wp-content/plugins/wpcleanadmin/' );
}

if ( ! defined( 'WPCA_TEXT_DOMAIN' ) ) {
    define( 'WPCA_TEXT_DOMAIN', 'wp-clean-admin' );
}

// Mock WordPress functions
if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        echo "✓ add_action called for hook: {$hook}\n";
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        echo "✓ add_filter called for hook: {$hook}\n";
    }
}

if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook( $file, $callback ) {
        echo "✓ register_activation_hook called\n";
    }
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook( $file, $callback ) {
        echo "✓ register_deactivation_hook called\n";
    }
}

if ( ! function_exists( 'load_plugin_textdomain' ) ) {
    function load_plugin_textdomain( $domain, $abs_rel_path = false, $plugin_rel_path = '' ) {
        echo "✓ load_plugin_textdomain called for domain: {$domain}\n";
        return true;
    }
}

if ( ! function_exists( 'plugin_basename' ) ) {
    function plugin_basename( $file ) {
        return 'wpcleanadmin/wp-clean-admin.php';
    }
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        return $default;
    }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option( $option, $value ) {
        echo "✓ update_option called for option: {$option}\n";
        return true;
    }
}

if ( ! function_exists( 'wp_parse_args' ) ) {
    function wp_parse_args( $args, $defaults = '' ) {
        if ( is_array( $defaults ) ) {
            return array_merge( $defaults, $args );
        }
        return $args;
    }
}

if ( ! function_exists( 'flush_rewrite_rules' ) ) {
    function flush_rewrite_rules( $hard = true ) {
        echo "✓ flush_rewrite_rules called\n";
    }
}

if ( ! function_exists( 'headers_sent' ) ) {
    function headers_sent( &$file = null, &$line = null ) {
        return false;
    }
}

if ( ! function_exists( 'header' ) ) {
    function header( $string, $replace = true, $http_response_code = null ) {
        echo "✓ header called: {$string}\n";
    }
}

if ( ! function_exists( 'error_log' ) ) {
    function error_log( $message, $message_type = 0, $destination = null, $extra_headers = null ) {
        echo "✗ Error: {$message}\n";
    }
}

// Mock translation function
if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

// Start test
echo "=====================================\n";
echo "Testing WP Clean Admin Plugin Activation\n";
echo "=====================================\n";

// Include the plugin main file
try {
    echo "Loading plugin main file...\n";
    require_once ABSPATH . 'wp-clean-admin.php';
    echo "✓ Plugin main file loaded successfully\n";
    
    // Test initialization
    echo "\nInitializing plugin...\n";
    if ( function_exists( 'wpca_init' ) ) {
        wpca_init();
        echo "✓ Plugin initialized successfully\n";
    } else {
        echo "✗ wpca_init function not found\n";
    }
    
    // Test Core class instantiation
    echo "\nTesting Core class instantiation...\n";
    if ( class_exists( 'WPCleanAdmin\Core' ) ) {
        $core_instance = WPCleanAdmin\Core::getInstance();
        if ( $core_instance instanceof WPCleanAdmin\Core ) {
            echo "✓ Core class instantiated successfully\n";
        } else {
            echo "✗ Core class instantiation failed\n";
        }
    } else {
        echo "✗ WPCleanAdmin\Core class not found\n";
    }
    
    // Test Settings class instantiation
    echo "\nTesting Settings class instantiation...\n";
    if ( class_exists( 'WPCleanAdmin\Settings' ) ) {
        $settings_instance = WPCleanAdmin\Settings::getInstance();
        if ( $settings_instance instanceof WPCleanAdmin\Settings ) {
            echo "✓ Settings class instantiated successfully\n";
        } else {
            echo "✗ Settings class instantiation failed\n";
        }
    } else {
        echo "✗ WPCleanAdmin\Settings class not found\n";
    }
    
    echo "\n=====================================\n";
    echo "Test completed successfully!\n";
    echo "=====================================\n";
    
} catch ( Exception $e ) {
    echo "\n=====================================\n";
    echo "Test failed with exception:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "=====================================\n";
} catch ( Error $e ) {
    echo "\n=====================================\n";
    echo "Test failed with error:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "=====================================\n";
}
