<?php
/**
 * Test script to verify plugin activation without fatal errors
 * 
 * This script simulates a minimal WordPress environment to test
 * if the plugin can be loaded without fatal errors.
 */

// Define minimal WordPress constants
define( 'ABSPATH', __DIR__ . '/' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );

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

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

// Test loading the plugin
try {
    echo "Testing plugin activation...\n";
    require_once __DIR__ . '/wpcleanadmin/wp-clean-admin.php';
    echo "✓ Plugin loaded successfully without fatal errors!\n";
    
    // Test initialization
    if ( function_exists( 'wpca_init' ) ) {
        echo "Testing plugin initialization...\n";
        wpca_init();
        echo "✓ Plugin initialized successfully!\n";
    }
    
    echo "\nAll tests passed! The plugin should activate correctly in WordPress.\n";
} catch ( Exception $e ) {
    echo "✗ Error loading plugin: " . $e->getMessage() . "\n";
    exit( 1 );
} catch ( Error $e ) {
    echo "✗ Fatal error loading plugin: " . $e->getMessage() . "\n";
    exit( 1 );
}
?>