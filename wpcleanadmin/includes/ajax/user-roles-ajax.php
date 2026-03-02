<?php
/**
 * WPCleanAdmin User Roles AJAX Class
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
 * WPCleanAdmin User Roles AJAX Handler
 *
 * This class handles all user roles related AJAX requests for the WPCleanAdmin plugin.
 *
 * @package WPCleanAdmin
 * @subpackage AJAX
 * @since 1.7.15
 */

class User_Roles {

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
     * Get user roles.
     *
     * @since 1.7.15
     */
    public static function get_user_roles() {
        if ( ! self::verify_ajax_request( 'wpca_get_user_roles' ) ) {
            return;
        }
        
        $user_roles = new \WPCleanAdmin\User_Roles();
        $roles = $user_roles->get_user_roles();
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $roles );
        }
    }

    /**
     * Update role capabilities.
     *
     * @since 1.7.15
     */
    public static function update_role_capabilities() {
        if ( ! self::verify_ajax_request( 'wpca_update_role_capabilities' ) ) {
            return;
        }
        
        $role_slug = isset( $_POST['role_slug'] ) ? sanitize_text_field( $_POST['role_slug'] ) : '';
        $capabilities = isset( $_POST['capabilities'] ) ? ( function_exists( 'wp_unslash' ) ? wp_unslash( $_POST['capabilities'] ) : $_POST['capabilities'] ) : array();
        
        $user_roles = new \WPCleanAdmin\User_Roles();
        $result = $user_roles->update_role_capabilities( $role_slug, $capabilities );
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $result );
        }
    }

    /**
     * Create new role.
     *
     * @since 1.7.15
     */
    public static function create_role() {
        if ( ! self::verify_ajax_request( 'wpca_create_role' ) ) {
            return;
        }
        
        $role_name = isset( $_POST['role_name'] ) ? sanitize_text_field( $_POST['role_name'] ) : '';
        $role_slug = isset( $_POST['role_slug'] ) ? sanitize_text_field( $_POST['role_slug'] ) : '';
        $capabilities = isset( $_POST['capabilities'] ) ? ( function_exists( 'wp_unslash' ) ? wp_unslash( $_POST['capabilities'] ) : $_POST['capabilities'] ) : array();
        
        $user_roles = new \WPCleanAdmin\User_Roles();
        $result = $user_roles->create_role( $role_slug, $role_name, $capabilities );
        
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

    /**
     * Delete role.
     *
     * @since 1.7.15
     */
    public static function delete_role() {
        if ( ! self::verify_ajax_request( 'wpca_delete_role' ) ) {
            return;
        }
        
        $role_slug = isset( $_POST['role_slug'] ) ? sanitize_text_field( $_POST['role_slug'] ) : '';
        
        $user_roles = new \WPCleanAdmin\User_Roles();
        $result = $user_roles->delete_role( $role_slug );
        
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

    /**
     * Duplicate role.
     *
     * @since 1.7.15
     */
    public static function duplicate_role() {
        if ( ! self::verify_ajax_request( 'wpca_duplicate_role' ) ) {
            return;
        }
        
        $role_slug = isset( $_POST['role_slug'] ) ? sanitize_text_field( $_POST['role_slug'] ) : '';
        $new_role_name = isset( $_POST['new_role_name'] ) ? sanitize_text_field( $_POST['new_role_name'] ) : '';
        $new_role_slug = isset( $_POST['new_role_slug'] ) ? sanitize_text_field( $_POST['new_role_slug'] ) : '';
        
        $user_roles = new \WPCleanAdmin\User_Roles();
        $result = $user_roles->duplicate_role( $role_slug, $new_role_name, $new_role_slug );
        
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
