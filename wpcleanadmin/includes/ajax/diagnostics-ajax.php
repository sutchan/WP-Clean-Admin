<?php
/**
 * WPCleanAdmin Diagnostics AJAX Class
 *
 * @package WPCleanAdmin
 * @version 1.8.3
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.8.0
 */
namespace WPCleanAdmin\AJAX;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Diagnostics {

    private static function verify_ajax_request( string $action ): bool {
        if ( ! function_exists( '\wp_verify_nonce' ) || ! isset( $_POST['_wpnonce'] ) || ! \wp_verify_nonce( $_POST['_wpnonce'], 'wpca_ajax_nonce' ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( \__( 'Invalid nonce', 'wp-clean-admin' ) );
            }
            return false;
        }
        
        if ( ! function_exists( '\current_user_can' ) || ! \current_user_can( 'manage_options' ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( \__( 'Insufficient permissions', 'wp-clean-admin' ) );
            }
            return false;
        }
        
        return true;
    }

    public static function run_diagnostics() {
        if ( ! self::verify_ajax_request( 'wpca_run_diagnostics' ) ) {
            return;
        }
        
        $diagnostics = \WPCleanAdmin\Diagnostics::getInstance();
        $results = $diagnostics->run_all_checks();
        
        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( $results );
        }
    }

    public static function run_single_check() {
        if ( ! self::verify_ajax_request( 'wpca_run_single_check' ) ) {
            return;
        }
        
        $check_id = isset( $_POST['check_id'] ) ? \sanitize_text_field( $_POST['check_id'] ) : '';
        
        if ( empty( $check_id ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( \__( 'Check ID is required', 'wp-clean-admin' ) );
            }
            return;
        }
        
        $diagnostics = \WPCleanAdmin\Diagnostics::getInstance();
        $result = $diagnostics->run_check( $check_id );
        
        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( $result );
        }
    }

    public static function get_checks_list() {
        if ( ! self::verify_ajax_request( 'wpca_get_checks_list' ) ) {
            return;
        }
        
        $diagnostics = \WPCleanAdmin\Diagnostics::getInstance();
        $checks = $diagnostics->get_checks();
        $categories = $diagnostics->get_categories();
        
        $result = array(
            'checks' => $checks,
            'categories' => $categories
        );
        
        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( $result );
        }
    }
}