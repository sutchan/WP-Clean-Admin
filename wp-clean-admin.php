<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI:  https://github.com/sutchan/WP-Clean-Admin
 * Description: Simplify and customize your WordPress admin dashboard with a flat, minimal, and fresh UI.
 * Version:     1.0.0
 * Author:      sutchan
 * Author URI:  https://github.com/sutchan
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
if ( ! defined( 'WPCA_VERSION' ) ) {
    define( 'WPCA_VERSION', '1.0.0' );
}
if ( ! defined( 'WPCA_PLUGIN_DIR' ) ) {
    define( 'WPCA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WPCA_PLUGIN_URL' ) ) {
    define( 'WPCA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Load plugin textdomain.
 */
function wpca_load_textdomain() {
    load_plugin_textdomain( 'wp-clean-admin', false, basename( WPCA_PLUGIN_DIR ) . '/languages' );
}
add_action( 'plugins_loaded', 'wpca_load_textdomain' );

// Include core files
require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-settings.php';
require_once WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php';

/**
 * Initialize the plugin.
 */
function wpca_run_plugin() {
    new WPCA_Settings();
    // Core functions are hooked directly in wpca-core-functions.php
}
add_action( 'plugins_loaded', 'wpca_run_plugin' );

/**
 * Activation hook.
 * Set default options if they don't exist.
 */
function wpca_activate() {
    if ( ! get_option( 'wpca_settings' ) ) {
        update_option( 'wpca_settings', WPCA_Settings::get_default_settings() );
    }
}
register_activation_hook( __FILE__, 'wpca_activate' );
