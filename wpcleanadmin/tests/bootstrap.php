<?php
/**
 * WP Clean Admin PHPUnit Bootstrap
 *
 * @package WPCleanAdmin
 */

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/../../../' );
}

if ( ! defined( 'WPCA_VERSION' ) ) {
    define( 'WPCA_VERSION', '1.8.0' );
}

if ( ! defined( 'WPCA_PLUGIN_DIR' ) ) {
    define( 'WPCA_PLUGIN_DIR', dirname( __FILE__ ) . '/../' );
}

if ( ! defined( 'WPCA_TEXT_DOMAIN' ) ) {
    define( 'WPCA_TEXT_DOMAIN', 'wp-clean-admin' );
}

// Load autoloader
if ( file_exists( WPCA_PLUGIN_DIR . 'includes/autoload.php' ) ) {
    require_once WPCA_PLUGIN_DIR . 'includes/autoload.php';
}

// Load core functions
if ( file_exists( WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php' ) ) {
    require_once WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php';
}
