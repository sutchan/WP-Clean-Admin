<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI: https://github.com/sutchan/WP-Clean-Admin
 * Description: Simplifies and optimizes the WordPress admin interface, providing a cleaner backend experience.
 * Version: 1.7.15
 * Author: Sut
 * Author URI: https://github.com/sutchan/
 * License: GPLv2 or later
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 * 
 * @file wp-clean-admin.php
 * @version 1.7.15
 * @updated 2025-11-27
 */

// Exit if accessed directly with proper function_exists check
if ( ! defined( 'ABSPATH' ) && ! function_exists( 'add_action' ) ) {
    // Define ABSPATH if not defined
    if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', dirname( __FILE__ ) . '/' );
    }
}

// Define plugin version
if ( ! defined( 'WPCA_VERSION' ) ) {
	define( 'WPCA_VERSION', '1.7.15' );
}

if ( ! defined( 'WPCA_MAIN_FILE' ) ) {
	define( 'WPCA_MAIN_FILE', __FILE__ );
}

// Define debug constant
if (!defined('WPCA_DEBUG')) {
    define('WPCA_DEBUG', (defined('WP_DEBUG') && WP_DEBUG));
}

// Define audit logs settings
// Enable audit logs (true/false)
if (!defined('WPCA_ENABLE_AUDIT_LOGS')) {
    define('WPCA_ENABLE_AUDIT_LOGS', true);
}

// Save audit logs to database (true/false)
if (!defined('WPCA_SAVE_AUDIT_TO_DB')) {
    define('WPCA_SAVE_AUDIT_TO_DB', true);
}

// Audit log retention days
if (!defined('WPCA_AUDIT_LOG_RETENTION_DAYS')) {
    define('WPCA_AUDIT_LOG_RETENTION_DAYS', 30);
}

// Include the main plugin file
if ( function_exists( 'dirname' ) ) {
    $main_plugin_file = dirname( __FILE__ ) . '/wpcleanadmin/wp-clean-admin.php';
    if ( file_exists( $main_plugin_file ) ) {
        require_once $main_plugin_file;
    }
}
?>