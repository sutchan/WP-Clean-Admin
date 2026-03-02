<?php
/**
 * WPCleanAdmin Performance AJAX Class
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
if ( ! function_exists( 'get_option' ) ) {
    function get_option() {}
}
if ( ! function_exists( 'update_option' ) ) {
    function update_option() {}
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
 * WPCleanAdmin Performance AJAX Handler
 *
 * This class handles all performance related AJAX requests for the WPCleanAdmin plugin.
 *
 * @package WPCleanAdmin
 * @subpackage AJAX
 * @since 1.7.15
 */

class Performance {

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
     * Optimize database.
     *
     * @since 1.7.15
     */
    public static function optimize_database() {
        if ( ! self::verify_ajax_request( 'wpca_optimize_database' ) ) {
            return;
        }
        
        $database = new \WPCleanAdmin\Database();
        $result = $database->optimize_database();
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $result );
        }
    }

    /**
     * Clear cache.
     *
     * @since 1.7.15
     */
    public static function clear_cache() {
        if ( ! self::verify_ajax_request( 'wpca_clear_cache' ) ) {
            return;
        }
        
        $performance = new \WPCleanAdmin\Performance();
        $result = $performance->clear_cache();
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $result );
        }
    }

    /**
     * Get performance statistics.
     *
     * @since 1.7.15
     */
    public static function get_performance_stats() {
        if ( ! self::verify_ajax_request( 'wpca_get_performance_stats' ) ) {
            return;
        }
        
        $performance = new \WPCleanAdmin\Performance();
        $stats = $performance->get_performance_stats();
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $stats );
        }
    }

    /**
     * Save performance settings.
     *
     * @since 1.7.15
     */
    public static function save_performance_settings() {
        if ( ! self::verify_ajax_request( 'wpca_save_performance_settings' ) ) {
            return;
        }
        
        $settings = isset( $_POST['settings'] ) ? ( function_exists( 'wp_unslash' ) ? wp_unslash( $_POST['settings'] ) : $_POST['settings'] ) : array();
        
        // Get current settings
        $current_settings = function_exists( 'get_option' ) ? get_option( 'wpca_settings', array() ) : array();
        
        // Update performance settings
        $current_settings['performance'] = $settings;
        $result = function_exists( 'update_option' ) ? update_option( 'wpca_settings', $current_settings ) : false;
        
        if ( $result ) {
            if ( function_exists( 'wp_send_json_success' ) ) {
                wp_send_json_success( array( 'message' => __( 'Performance settings saved successfully', 'wp-clean-admin' ) ) );
            }
        } else {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( __( 'Failed to save performance settings', 'wp-clean-admin' ) );
            }
        }
    }

    /**
     * Get performance settings.
     *
     * @since 1.7.15
     */
    public static function get_performance_settings() {
        if ( ! self::verify_ajax_request( 'wpca_get_performance_settings' ) ) {
            return;
        }
        
        // Get performance settings
        $settings = function_exists( 'get_option' ) ? get_option( 'wpca_settings', array() ) : array();
        $performance_settings = isset( $settings['performance'] ) ? $settings['performance'] : array();
        
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $performance_settings );
        }
    }

    /**
     * Reset performance settings.
     *
     * @since 1.7.15
     */
    public static function reset_performance_settings() {
        if ( ! self::verify_ajax_request( 'wpca_reset_performance_settings' ) ) {
            return;
        }
        
        // Get current settings
        $current_settings = function_exists( 'get_option' ) ? get_option( 'wpca_settings', array() ) : array();
        
        // Remove performance settings (reset to default)
        unset( $current_settings['performance'] );
        $result = function_exists( 'update_option' ) ? update_option( 'wpca_settings', $current_settings ) : false;
        
        if ( $result ) {
            if ( function_exists( 'wp_send_json_success' ) ) {
                wp_send_json_success( array( 'message' => __( 'Performance settings reset to default', 'wp-clean-admin' ) ) );
            }
        } else {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( __( 'Failed to reset performance settings', 'wp-clean-admin' ) );
            }
        }
    }
}
