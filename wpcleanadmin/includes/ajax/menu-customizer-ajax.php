<?php
/**
 * WPCleanAdmin Menu Customizer AJAX Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin\AJAX;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Declare WordPress functions for IDE compatibility
if ( ! function_exists( 'wp_verify_nonce' ) ) {
    function wp_verify_nonce() {}
}
if ( ! function_exists( 'wp_send_json_error' ) ) {
    function wp_send_json_error() {}
}
if ( ! function_exists( 'wp_send_json_success' ) ) {
    function wp_send_json_success() {}
}
if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can() {}
}
if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash() {}
}
if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field() {}
}
if ( ! function_exists( '__' ) ) {
    function __() {}
}

/**
 * WPCleanAdmin Menu Customizer AJAX Handler
 *
 * This class handles all menu customizer related AJAX requests for the WPCleanAdmin plugin.
 *
 * @package WPCleanAdmin
 * @subpackage AJAX
 * @since 1.7.15
 */

class Menu_Customizer {

    /**
     * Verify AJAX nonce and check user capabilities.
     *
     * @since 1.7.15
     * @param string $action The AJAX action name.
     * @return bool True if the nonce is valid and user has capabilities, false otherwise.
     */
    private static function verify_ajax_request( string $action ): bool {
        if ( ! function_exists( 'wp_verify_nonce' ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wpca_ajax_nonce' ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( __( 'Invalid nonce', 'wp-clean-admin' ) );
            }
            return false;
        }
        
        if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'manage_options' ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( __( 'Insufficient permissions', 'wp-clean-admin' ) );
            }
            return false;
        }
        
        return true;
    }

    /**
     * Save menu customizer settings.
     *
     * @since 1.7.15
     */
    public static function save_menu_customizer_settings() {
        if ( ! self::verify_ajax_request( 'wpca_save_menu_customizer_settings' ) ) {
            return;
        }
        
        $settings = isset( $_POST['settings'] ) ? ( function_exists( 'wp_unslash' ) ? wp_unslash( $_POST['settings'] ) : $_POST['settings'] ) : array();
        
        $menu_customizer = new \WPCleanAdmin\Menu_Customizer();
        $result = $menu_customizer->save_settings( $settings );
        
        if ( $result ) {
            if ( function_exists( 'wp_send_json_success' ) ) {
                wp_send_json_success( array( 'message' => __( 'Menu customizer settings saved successfully', 'wp-clean-admin' ) ) );
            }
        } else {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( __( 'Failed to save menu customizer settings', 'wp-clean-admin' ) );
            }
        }
    }

    /**
     * Get menu customizer settings.
     *
     * @since 1.7.15
     */
    public static function get_menu_customizer_settings() {
        if ( ! self::verify_ajax_request( 'wpca_get_menu_customizer_settings' ) ) {
            return;
        }
        
        $menu_customizer = new \WPCleanAdmin\Menu_Customizer();
        $settings = $menu_customizer->get_settings();
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $settings );
        }
    }

    /**
     * Reset menu customizer settings.
     *
     * @since 1.7.15
     */
    public static function reset_menu_customizer_settings() {
        if ( ! self::verify_ajax_request( 'wpca_reset_menu_customizer_settings' ) ) {
            return;
        }
        
        $menu_customizer = new \WPCleanAdmin\Menu_Customizer();
        $result = $menu_customizer->reset_settings();
        
        if ( $result ) {
            if ( function_exists( 'wp_send_json_success' ) ) {
                wp_send_json_success( array( 'message' => __( 'Menu customizer settings reset to default', 'wp-clean-admin' ) ) );
            }
        } else {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( __( 'Failed to reset menu customizer settings', 'wp-clean-admin' ) );
            }
        }
    }
}
