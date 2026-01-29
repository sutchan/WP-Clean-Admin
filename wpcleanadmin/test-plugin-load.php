<?php
/**
 * Test script to diagnose plugin loading issues
 */

// Define ABSPATH if not defined
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/../' );
}

// Load the plugin main file
require_once __DIR__ . '/wp-clean-admin.php';

// Test plugin initialization
echo "=== WP Clean Admin Plugin Test ===\n";

// Check constants
echo "\n1. Constants:\n";
echo "WPCA_VERSION: " . ( defined( 'WPCA_VERSION' ) ? WPCA_VERSION : 'NOT DEFINED' ) . "\n";
echo "WPCA_PLUGIN_DIR: " . ( defined( 'WPCA_PLUGIN_DIR' ) ? WPCA_PLUGIN_DIR : 'NOT DEFINED' ) . "\n";
echo "WPCA_PLUGIN_URL: " . ( defined( 'WPCA_PLUGIN_URL' ) ? WPCA_PLUGIN_URL : 'NOT DEFINED' ) . "\n";
echo "WPCA_TEXT_DOMAIN: " . ( defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'NOT DEFINED' ) . "\n";

// Check files
echo "\n2. Files:\n";
echo "wp-clean-admin.php: " . ( file_exists( __DIR__ . '/wp-clean-admin.php' ) ? 'EXISTS' : 'MISSING' ) . "\n";
echo "autoload.php: " . ( file_exists( __DIR__ . '/includes/autoload.php' ) ? 'EXISTS' : 'MISSING' ) . "\n";
echo "class-wpca-core.php: " . ( file_exists( __DIR__ . '/includes/class-wpca-core.php' ) ? 'EXISTS' : 'MISSING' ) . "\n";
echo "wpca-core-functions.php: " . ( file_exists( __DIR__ . '/includes/wpca-core-functions.php' ) ? 'EXISTS' : 'MISSING' ) . "\n";

// Test autoloader
echo "\n3. Autoloader Test:\n";
try {
    // Try to load Core class
    if ( class_exists( 'WPCleanAdmin\Core' ) ) {
        echo "Core class: LOADED\n";
        
        // Try to get instance
        $core = WPCleanAdmin\Core::getInstance();
        echo "Core instance: CREATED\n";
    } else {
        echo "Core class: NOT LOADED\n";
    }
} catch ( Exception $e ) {
    echo "Error loading Core class: " . $e->getMessage() . "\n";
}

// Test core functions
echo "\n4. Core Functions:\n";
echo "wpca_get_settings: " . ( function_exists( 'wpca_get_settings' ) ? 'DEFINED' : 'NOT DEFINED' ) . "\n";

// Test plugin initialization
echo "\n5. Plugin Initialization:\n";
try {
    wpca_init();
    echo "wpca_init(): EXECUTED\n";
} catch ( Exception $e ) {
    echo "Error in wpca_init(): " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
