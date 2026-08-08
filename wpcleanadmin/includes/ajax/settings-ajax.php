<?php
/**
 * WPCleanAdmin Settings AJAX Handler
 *
 * @package WPCleanAdmin
 * @version 1.8.2
 * @update_date 2026-01-30
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

namespace WPCleanAdmin\AJAX;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
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
        if ( ! function_exists( '\wp_verify_nonce' ) || ! isset( $_POST['_wpnonce'] ) || ! \wp_verify_nonce( $_POST['_wpnonce'], 'wpca_ajax_nonce' ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => \__( 'Nonce verification failed', WPCA_TEXT_DOMAIN ) ) );
            }
            return;
        }
        
        // Check permissions
        if ( ! function_exists( '\current_user_can' ) || ! \current_user_can( 'manage_options' ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => \__( 'Insufficient permissions', WPCA_TEXT_DOMAIN ) ) );
            }
            return;
        }
        
        try {
            // Get settings data
            $raw_settings = isset( $_POST['settings'] ) ? ( function_exists( '\wp_unslash' ) ? \wp_unslash( $_POST['settings'] ) : $_POST['settings'] ) : array();

            // 递归清理所有设置值，防止存储型 XSS / 注入
            $settings = self::sanitize_settings( $raw_settings );

            // Validate and save settings
            if ( function_exists( '\update_option' ) ) {
                \update_option( 'wpca_settings', $settings );
            }
            
            if ( function_exists( '\wp_send_json_success' ) ) {
                \wp_send_json_success( array( 'message' => \__( 'Settings saved successfully', WPCA_TEXT_DOMAIN ) ) );
            }
        } catch ( \Exception $e ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => $e->getMessage() ) );
            }
        }
    }
    
    /**
     * Get settings
     *
     * @return void
     */
    public static function get_settings() {
        // Verify nonce
        if ( ! function_exists( '\wp_verify_nonce' ) || ! isset( $_POST['_wpnonce'] ) || ! \wp_verify_nonce( $_POST['_wpnonce'], 'wpca_ajax_nonce' ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => \__( 'Nonce verification failed', WPCA_TEXT_DOMAIN ) ) );
            }
            return;
        }
        
        // Check permissions
        if ( ! function_exists( '\current_user_can' ) || ! \current_user_can( 'manage_options' ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => \__( 'Insufficient permissions', WPCA_TEXT_DOMAIN ) ) );
            }
            return;
        }
        
        try {
            // Get settings
            $settings = array();
            if ( function_exists( '\get_option' ) ) {
                $settings = \get_option( 'wpca_settings', array() );
            }
            
            if ( function_exists( '\wp_send_json_success' ) ) {
                \wp_send_json_success( array( 'settings' => $settings ) );
            }
        } catch ( \Exception $e ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => $e->getMessage() ) );
            }
        }
    }
    
    /**
     * Reset settings
     *
     * @return void
     */
    public static function reset_settings() {
        // Verify nonce
        if ( ! function_exists( '\wp_verify_nonce' ) || ! isset( $_POST['_wpnonce'] ) || ! \wp_verify_nonce( $_POST['_wpnonce'], 'wpca_ajax_nonce' ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => \__( 'Nonce verification failed', WPCA_TEXT_DOMAIN ) ) );
            }
            return;
        }
        
        // Check permissions
        if ( ! function_exists( '\current_user_can' ) || ! \current_user_can( 'manage_options' ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => \__( 'Insufficient permissions', WPCA_TEXT_DOMAIN ) ) );
            }
            return;
        }
        
        try {
            // Reset settings
            if ( function_exists( '\delete_option' ) ) {
                \delete_option( 'wpca_settings' );
            }
            
            if ( function_exists( '\wp_send_json_success' ) ) {
                \wp_send_json_success( array( 'message' => \__( 'Settings reset successfully', WPCA_TEXT_DOMAIN ) ) );
            }
        } catch ( \Exception $e ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => $e->getMessage() ) );
            }
        }
    }

    /**
     * 递归清理设置数组，防止存储型 XSS / 注入
     *
     * @param mixed $value 待清理的值（可为标量、数组）
     * @return mixed 清理后的值
     */
    private static function sanitize_settings( $value ) {
        if ( is_array( $value ) ) {
            $clean = array();
            foreach ( $value as $key => $item ) {
                $clean[ $key ] = self::sanitize_settings( $item );
            }
            return $clean;
        }

        if ( is_string( $value ) ) {
            $func = function_exists( '\sanitize_text_field' ) ? '\sanitize_text_field' : null;
            return $func ? $func( $value ) : strip_tags( trim( $value ) );
        }

        // 布尔 / 数字等标量原样返回
        return $value;
    }
}
