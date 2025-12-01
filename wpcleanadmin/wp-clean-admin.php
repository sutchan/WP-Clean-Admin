<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI: https://github.com/sutchan/WPCleanAdmin
 * Description: A comprehensive WordPress admin cleanup and optimization plugin
 * Version: 1.7.15
 * Author: Sut
 * Author URI: https://github.com/sutchan
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'WPCA_VERSION', '1.7.15' );
define( 'WPCA_PLUGIN_DIR', ( function_exists( 'plugin_dir_path' ) ? plugin_dir_path( __FILE__ ) : dirname( __FILE__ ) . '/' ) );
define( 'WPCA_PLUGIN_URL', ( function_exists( 'plugin_dir_url' ) ? plugin_dir_url( __FILE__ ) : '' ) );
define( 'WPCA_TEXT_DOMAIN', 'wp-clean-admin' );

// Load WordPress function stubs for IDE support
if ( file_exists( WPCA_PLUGIN_DIR . 'includes/wpca-wordpress-stubs.php' ) ) {
    require_once WPCA_PLUGIN_DIR . 'includes/wpca-wordpress-stubs.php';
}

// Load autoloader
require_once WPCA_PLUGIN_DIR . 'includes/autoload.php';

/**
 * Initialize the WP Clean Admin plugin
 *
 * This function loads the plugin text domain and initializes the core class.
 * It's hooked to the 'plugins_loaded' action.
 *
 * @since 1.7.15
 */
function wpca_init() {
    // Load text domain for translations
    if ( function_exists( 'load_plugin_textdomain' ) && function_exists( 'plugin_basename' ) ) {
        load_plugin_textdomain( WPCA_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
    
    // Initialize core class
    WPCleanAdmin\Core::get_instance();
}

// Hook into WordPress initialization
if ( function_exists( 'add_action' ) ) {
    add_action( 'plugins_loaded', 'wpca_init' );
}

// Register activation hook
if ( function_exists( 'register_activation_hook' ) ) {
    register_activation_hook( __FILE__, function() {
        WPCleanAdmin\Core::get_instance()->activate();
    });
}

// Register deactivation hook
if ( function_exists( 'register_deactivation_hook' ) ) {
    register_deactivation_hook( __FILE__, function() {
        WPCleanAdmin\Core::get_instance()->deactivate();
    });
}
