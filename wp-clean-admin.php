<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI: https://github.com/sutchan/WP-Clean-Admin
 * Description: Simplifies and optimizes the WordPress admin interface, providing a cleaner backend experience.
 * Version: 1.1.0
 * Author: Sut
 * Author URI: https://github.com/sutchan/
 * License: GPLv2 or later
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'WPCA_VERSION', '1.1.0' );

// Include the main plugin file from the wpcleanadmin directory
require_once plugin_dir_path( __FILE__ ) . 'wpcleanadmin/wp-clean-admin.php';