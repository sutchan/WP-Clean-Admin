<?php
/**
 * WPCleanAdmin Menu Manager AJAX Class
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
 * WPCleanAdmin Menu Manager AJAX Handler
 *
 * This class handles all menu manager related AJAX requests for the WPCleanAdmin plugin.
 *
 * @package WPCleanAdmin
 * @subpackage AJAX
 * @since 1.7.15
 */

class Menu_Manager {

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
     * Get menu items.
     *
     * @since 1.7.15
     */
    public static function get_menu_items() {
        if ( ! self::verify_ajax_request( 'wpca_get_menu_items' ) ) {
            return;
        }
        
        $menu_manager = new \WPCleanAdmin\Menu_Manager();
        $menu_items = $menu_manager->get_menu_items();
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $menu_items );
        }
    }

    /**
     * Save menu items.
     *
     * @since 1.7.15
     */
    public static function save_menu_items() {
        if ( ! self::verify_ajax_request( 'wpca_save_menu_items' ) ) {
            return;
        }
        
        $menu_items = isset( $_POST['menu_items'] ) ? ( function_exists( 'wp_unslash' ) ? wp_unslash( $_POST['menu_items'] ) : $_POST['menu_items'] ) : array();
        
        $menu_manager = new \WPCleanAdmin\Menu_Manager();
        $result = $menu_manager->save_menu_items( $menu_items );
        
        if ( $result['success'] ) {
            if ( function_exists( 'wp_send_json_success' ) ) {
                wp_send_json_success( $result );
            }
        } else {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( $result['message'] );
            }
        }
    }
}
