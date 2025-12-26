<?php
/**
 * Test bootstrap file for WP Clean Admin
 *
 * @package WPCleanAdmin
 */

// Define ABSPATH if not already defined
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( dirname( __FILE__ ) ) . '/' );
}

// Load plugin main file
require_once ABSPATH . 'wpcleanadmin/wp-clean-admin.php';

// Load WordPress test utilities
if ( file_exists( ABSPATH . 'vendor/autoload.php' ) ) {
    require_once ABSPATH . 'vendor/autoload.php';
}

// Setup test environment
function wpca_test_setup() {
    // Mock WordPress functions
    if ( ! function_exists( 'wpca_get_settings' ) ) {
        function wpca_get_settings() {
            return array(
                'general' => array(
                    'clean_admin_bar' => 1,
                    'remove_wp_logo' => 1
                ),
                'menu' => array(
                    'remove_dashboard_widgets' => 1,
                    'simplify_admin_menu' => 1,
                    'role_based_restrictions' => 1
                ),
                'security' => array(
                    'hide_wp_version' => 1,
                    'two_factor_auth' => 1
                )
            );
        }
    }
}

wpca_test_setup();