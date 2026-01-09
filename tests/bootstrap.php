<?php
/**
 * WP Clean Admin Test Bootstrap
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @since 1.8.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Set up the WordPress test environment
if ( file_exists( dirname( __FILE__ ) . '/../wp-clean-admin.php' ) ) {
    define( 'WPCA_PLUGIN_FILE', dirname( __FILE__ ) . '/../wp-clean-admin.php' );
} else {
    define( 'WPCA_PLUGIN_FILE', dirname( __FILE__ ) . '/../wpcleanadmin/wp-clean-admin.php' );
}

// Load WordPress test library
if ( file_exists( dirname( __FILE__ ) . '/phpunit-stub.php' ) ) {
    require_once dirname( __FILE__ ) . '/phpunit-stub.php';
}

// Load the plugin
require_once WPCA_PLUGIN_FILE;

// Set up test environment
function wpca_set_up_test_environment() {
    // Define test constants
    define( 'WPCA_TESTING', true );
    define( 'WPCA_TEST_DIR', dirname( __FILE__ ) );
}

// Run test setup
wpca_set_up_test_environment();
