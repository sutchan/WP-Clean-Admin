<?php
/**
 * WP Clean Admin Tests Bootstrap
 *
 * @package WPCleanAdmin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Set up WordPress testing environment
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Define plugin constants
if ( ! defined( 'WPCA_VERSION' ) ) {
    define( 'WPCA_VERSION', '1.8.0' );
}

if ( ! defined( 'WPCA_PLUGIN_DIR' ) ) {
    define( 'WPCA_PLUGIN_DIR', dirname( dirname( __FILE__ ) ) . '/wpcleanadmin/' );
}

if ( ! defined( 'WPCA_PLUGIN_URL' ) ) {
    define( 'WPCA_PLUGIN_URL', 'http://localhost/wpcleanadmin/' );
}

if ( ! defined( 'WPCA_TEXT_DOMAIN' ) ) {
    define( 'WPCA_TEXT_DOMAIN', 'wp-clean-admin' );
}

// Include PHPUnit stubs for IDE support
require_once __DIR__ . '/phpunit-stub.php';

// Include autoloader
require_once WPCA_PLUGIN_DIR . 'includes/autoload.php';

// Load core functions
require_once WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php';
