<?php
/**
 * Error logger script to capture fatal errors during plugin activation
 * 
 * This script helps identify the exact cause of fatal errors when activating
 * the WP Clean Admin plugin.
 */

// Define constants
define( 'ABSPATH', __DIR__ . '/' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );

// Set error reporting to maximum
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
ini_set( 'log_errors', 1 );
ini_set( 'error_log', __DIR__ . '/wpca-error.log' );

// Mock minimal WordPress functions
if ( ! function_exists( 'plugin_dir_path' ) ) {
    function plugin_dir_path( $file ) {
        return dirname( $file ) . '/';
    }
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
    function plugin_dir_url( $file ) {
        return 'http://localhost/wp-content/plugins/wpcleanadmin/';
    }
}

if ( ! function_exists( 'load_plugin_textdomain' ) ) {
    function load_plugin_textdomain( $domain, $abs_rel_path, $plugin_rel_path ) {
        return true;
    }
}

if ( ! function_exists( 'plugin_basename' ) ) {
    function plugin_basename( $file ) {
        return 'wpcleanadmin/wp-clean-admin.php';
    }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $function_to_add, $priority = 10, $accepted_args = 1 ) {
        return true;
    }
}

if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook( $file, $function ) {
        return true;
    }
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook( $file, $function ) {
        return true;
    }
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        return $default;
    }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option( $option, $value, $autoload = null ) {
        return true;
    }
}

if ( ! function_exists( 'wp_parse_args' ) ) {
    function wp_parse_args( $args, $defaults = array() ) {
        return array_merge( $defaults, $args );
    }
}

if ( ! function_exists( 'flush_rewrite_rules' ) ) {
    function flush_rewrite_rules( $hard = true ) {
        return true;
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
        return true;
    }
}

if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can( $capability ) {
        return true;
    }
}

if ( ! function_exists( 'sanitize_key' ) ) {
    function sanitize_key( $key ) {
        return preg_replace( '/[^a-z0-9_\-]/i', '', $key );
    }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $tag, $value, ...$args ) {
        return $value;
    }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'admin_url' ) ) {
    function admin_url( $path = '', $scheme = 'admin' ) {
        return 'http://localhost/wp-admin/' . ltrim( $path, '/' );
    }
}

if ( ! function_exists( 'wp_mkdir_p' ) ) {
    function wp_mkdir_p( $path, $chmod = 0755, $chown = false, $chgrp = false ) {
        return mkdir( $path, $chmod, true );
    }
}

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

// Custom error handler to capture detailed error information
function custom_error_handler( $errno, $errstr, $errfile, $errline ) {
    $error_type = array(
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE ERROR',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE ERROR',
        E_CORE_WARNING => 'CORE WARNING',
        E_COMPILE_ERROR => 'COMPILE ERROR',
        E_COMPILE_WARNING => 'COMPILE WARNING',
        E_USER_ERROR => 'USER ERROR',
        E_USER_WARNING => 'USER WARNING',
        E_USER_NOTICE => 'USER NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER DEPRECATED'
    );
    
    $error_msg = sprintf(
        "[%s] %s: %s in %s:%d\n",
        date( 'Y-m-d H:i:s' ),
        $error_type[$errno] ?? 'UNKNOWN',
        $errstr,
        $errfile,
        $errline
    );
    
    // Write to error log
    error_log( $error_msg, 3, __DIR__ . '/wpca-error.log' );
    
    // Display error
    echo $error_msg;
    
    return false;
}

// Custom exception handler
function custom_exception_handler( $exception ) {
    $error_msg = sprintf(
        "[%s] EXCEPTION: %s in %s:%d\nStack trace:\n%s\n",
        date( 'Y-m-d H:i:s' ),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    
    // Write to error log
    error_log( $error_msg, 3, __DIR__ . '/wpca-error.log' );
    
    // Display error
    echo $error_msg;
}

// Custom shutdown function to capture fatal errors
function custom_shutdown_function() {
    $error = error_get_last();
    if ( $error !== null ) {
        custom_error_handler( $error['type'], $error['message'], $error['file'], $error['line'] );
    }
}

// Set error handlers
set_error_handler( 'custom_error_handler' );
set_exception_handler( 'custom_exception_handler' );
register_shutdown_function( 'custom_shutdown_function' );

// Test loading the plugin
echo "\n=== Testing WP Clean Admin Plugin Activation ===\n";

try {
    echo "Loading plugin main file...\n";
    require_once __DIR__ . '/wpcleanadmin/wp-clean-admin.php';
    echo "✓ Plugin main file loaded successfully\n";
    
    echo "\nTesting plugin initialization...\n";
    if ( function_exists( 'wpca_init' ) ) {
        wpca_init();
        echo "✓ Plugin initialized successfully\n";
    }
    
    echo "\n=== All tests completed ===\n";
    echo "Check wpca-error.log for detailed error information\n";
    
} catch ( Exception $e ) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
} catch ( Error $e ) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
}
?>