<?php
/**
 * PHPUnit Bootstrap for WPCleanAdmin Tests
 *
 * @package WPCleanAdmin
 */

// Define WordPress constants
if ( ! defined( 'WPINC' ) ) {
    define( 'WPINC', 'wp-includes' );
}

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'WPCA_PLUGIN_DIR' ) ) {
    define( 'WPCA_PLUGIN_DIR', ABSPATH . 'wpcleanadmin/' );
}

if ( ! defined( 'WPCA_PLUGIN_URL' ) ) {
    define( 'WPCA_PLUGIN_URL', 'http://localhost/wpcleanadmin/' );
}

if ( ! defined( 'WPCA_VERSION' ) ) {
    define( 'WPCA_VERSION', '1.7.15' );
}

if ( ! defined( 'WPCA_TEXT_DOMAIN' ) ) {
    define( 'WPCA_TEXT_DOMAIN', 'wp-clean-admin' );
}

// Include WordPress stubs for testing
require_once ABSPATH . 'wpcleanadmin/includes/wpca-wordpress-stubs.php';

// Include the plugin's autoloader
require_once ABSPATH . 'wpcleanadmin/includes/autoload.php';

// Include the main plugin file
require_once ABSPATH . 'wpcleanadmin/wp-clean-admin.php';

// Set up test environment
date_default_timezone_set( 'UTC' );
