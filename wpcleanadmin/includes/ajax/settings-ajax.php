<?php
/**
 * WPCleanAdmin Settings AJAX Handler
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @update_date 2026-01-30
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */

namespace WPCleanAdmin\AJAX;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
if ( ! function_exists( '__' ) ) {
    function __() {}
}
if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash() {}
}
if ( ! function_exists( 'update_option' ) ) {
    function update_option() {}
}
if ( ! function_exists( 'get_option' ) ) {
    function get_option() {}
}
if ( ! function_exists( 'delete_option' ) ) {
    function delete_option() {}
}

/**
 * Settings AJAX Handler Class
 */
class Settings {
    
    /**
     * Save settings
     *
     * @return void
     */
    public static function save_settings() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! \wp_verify_nonce( $_POST['nonce'], 'wpca_ajax_nonce' ) ) {
            \wp_send_json_error( array( 'message' => \__( 'Nonce verification failed', WPCA_TEXT_DOMAIN ) ) );
        }
        
        // Check permissions
        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( array( 'message' => \__( 'Insufficient permissions', WPCA_TEXT_DOMAIN ) ) );
        }
        
        try {
            // Get settings data
            $settings = isset( $_POST['settings'] ) ? ( function_exists( 'wp_unslash' ) ? \wp_unslash( $_POST['settings'] ) : $_POST['settings'] ) : array();
            
            // Validate and save settings
            if ( function_exists( 'update_option' ) ) {
                \update_option( 'wpca_settings', $settings );
            }
            
            \wp_send_json_success( array( 'message' => \__( 'Settings saved successfully', WPCA_TEXT_DOMAIN ) ) );
        } catch ( \Exception $e ) {
            \wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }
    
    /**
     * Get settings
     *
     * @return void
     */
    public static function get_settings() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! \wp_verify_nonce( $_POST['nonce'], 'wpca_ajax_nonce' ) ) {
            \wp_send_json_error( array( 'message' => \__( 'Nonce verification failed', WPCA_TEXT_DOMAIN ) ) );
        }
        
        // Check permissions
        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( array( 'message' => \__( 'Insufficient permissions', WPCA_TEXT_DOMAIN ) ) );
        }
        
        try {
            // Get settings
            $settings = array();
            if ( function_exists( 'get_option' ) ) {
                $settings = \get_option( 'wpca_settings', array() );
            }
            
            \wp_send_json_success( array( 'settings' => $settings ) );
        } catch ( \Exception $e ) {
            \wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }
    
    /**
     * Reset settings
     *
     * @return void
     */
    public static function reset_settings() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! \wp_verify_nonce( $_POST['nonce'], 'wpca_ajax_nonce' ) ) {
            \wp_send_json_error( array( 'message' => \__( 'Nonce verification failed', WPCA_TEXT_DOMAIN ) ) );
        }
        
        // Check permissions
        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( array( 'message' => \__( 'Insufficient permissions', WPCA_TEXT_DOMAIN ) ) );
        }
        
        try {
            // Reset settings
            if ( function_exists( 'delete_option' ) ) {
                \delete_option( 'wpca_settings' );
            }
            
            \wp_send_json_success( array( 'message' => \__( 'Settings reset successfully', WPCA_TEXT_DOMAIN ) ) );
        } catch ( \Exception $e ) {
            \wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }
}
