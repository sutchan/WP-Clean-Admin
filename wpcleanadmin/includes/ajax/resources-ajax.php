<?php
/**
 * WPCleanAdmin Resources AJAX Class
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
if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field() {}
}
if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash() {}
}
if ( ! function_exists( '__' ) ) {
    function __() {}
}

/**
 * WPCleanAdmin Resources AJAX Handler
 *
 * This class handles all resources related AJAX requests for the WPCleanAdmin plugin.
 *
 * @package WPCleanAdmin
 * @subpackage AJAX
 * @since 1.7.15
 */

class Resources {

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
     * Get resources statistics.
     *
     * @since 1.7.15
     */
    public static function get_resources_stats() {
        if ( ! self::verify_ajax_request( 'wpca_get_resources_stats' ) ) {
            return;
        }
        
        $resources = new \WPCleanAdmin\Resources();
        $stats = $resources->get_resources_stats();
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $stats );
        }
    }

    /**
     * Get resource details.
     *
     * @since 1.7.15
     */
    public static function get_resource_details() {
        if ( ! self::verify_ajax_request( 'wpca_get_resource_details' ) ) {
            return;
        }
        
        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
        
        $resources = new \WPCleanAdmin\Resources();
        $details = $resources->get_resource_details( $type );
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $details );
        }
    }

    /**
     * Optimize resources.
     *
     * @since 1.7.15
     */
    public static function optimize_resources() {
        if ( ! self::verify_ajax_request( 'wpca_optimize_resources' ) ) {
            return;
        }
        
        $options = isset( $_POST['options'] ) ? ( function_exists( 'wp_unslash' ) ? wp_unslash( $_POST['options'] ) : $_POST['options'] ) : array();
        
        $resources = new \WPCleanAdmin\Resources();
        $result = $resources->optimize_resources( $options );
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $result );
        }
    }

    /**
     * Disable resource.
     *
     * @since 1.7.15
     */
    public static function disable_resource() {
        if ( ! self::verify_ajax_request( 'wpca_disable_resource' ) ) {
            return;
        }
        
        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
        $handle = isset( $_POST['handle'] ) ? sanitize_text_field( $_POST['handle'] ) : '';
        
        $resources = new \WPCleanAdmin\Resources();
        $result = $resources->disable_resource( $type, $handle );
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $result );
        }
    }

    /**
     * Enable resource.
     *
     * @since 1.7.15
     */
    public static function enable_resource() {
        if ( ! self::verify_ajax_request( 'wpca_enable_resource' ) ) {
            return;
        }
        
        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
        $handle = isset( $_POST['handle'] ) ? sanitize_text_field( $_POST['handle'] ) : '';
        
        $resources = new \WPCleanAdmin\Resources();
        $result = $resources->enable_resource( $type, $handle );
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $result );
        }
    }
}
