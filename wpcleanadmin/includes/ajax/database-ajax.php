<?php
/**
 * WPCleanAdmin Database AJAX Class
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
 * WPCleanAdmin Database AJAX Handler
 *
 * This class handles all database related AJAX requests for the WPCleanAdmin plugin.
 *
 * @package WPCleanAdmin
 * @subpackage AJAX
 * @since 1.7.15
 */

class Database {

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
     * Get database information.
     *
     * @since 1.7.15
     */
    public static function get_database_info() {
        if ( ! self::verify_ajax_request( 'wpca_get_database_info' ) ) {
            return;
        }
        
        $database = new \WPCleanAdmin\Database();
        $info = $database->get_database_info();
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $info );
        }
    }

    /**
     * Backup database.
     *
     * @since 1.7.15
     */
    public static function backup_database() {
        if ( ! self::verify_ajax_request( 'wpca_backup_database' ) ) {
            return;
        }
        
        $options = isset( $_POST['options'] ) ? ( function_exists( 'wp_unslash' ) ? wp_unslash( $_POST['options'] ) : $_POST['options'] ) : array();
        
        $database = new \WPCleanAdmin\Database();
        $result = $database->backup_database( $options );
        
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
     * Restore database.
     *
     * @since 1.7.15
     */
    public static function restore_database() {
        if ( ! self::verify_ajax_request( 'wpca_restore_database' ) ) {
            return;
        }
        
        $backup_file = isset( $_POST['backup_file'] ) ? sanitize_text_field( $_POST['backup_file'] ) : '';
        
        $database = new \WPCleanAdmin\Database();
        $result = $database->restore_database( $backup_file );
        
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
     * Get database backups.
     *
     * @since 1.7.15
     */
    public static function get_database_backups() {
        if ( ! self::verify_ajax_request( 'wpca_get_database_backups' ) ) {
            return;
        }
        
        $database = new \WPCleanAdmin\Database();
        $backups = $database->get_database_backups();
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $backups );
        }
    }

    /**
     * Delete database backup.
     *
     * @since 1.7.15
     */
    public static function delete_database_backup() {
        if ( ! self::verify_ajax_request( 'wpca_delete_database_backup' ) ) {
            return;
        }
        
        $backup_file = isset( $_POST['backup_file'] ) ? sanitize_text_field( $_POST['backup_file'] ) : '';
        
        $database = new \WPCleanAdmin\Database();
        $result = $database->delete_database_backup( $backup_file );
        
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
     * Save database settings.
     *
     * @since 1.7.15
     */
    public static function save_database_settings() {
        if ( ! self::verify_ajax_request( 'wpca_save_database_settings' ) ) {
            return;
        }
        
        $settings = isset( $_POST['settings'] ) ? ( function_exists( 'wp_unslash' ) ? wp_unslash( $_POST['settings'] ) : $_POST['settings'] ) : array();
        
        // Get current settings
        $current_settings = function_exists( 'get_option' ) ? get_option( 'wpca_settings', array() ) : array();
        
        // Update database settings
        $current_settings['database'] = $settings;
        $result = function_exists( 'update_option' ) ? update_option( 'wpca_settings', $current_settings ) : false;
        
        if ( $result ) {
            if ( function_exists( 'wp_send_json_success' ) ) {
                wp_send_json_success( array( 'message' => __( 'Database settings saved successfully', 'wp-clean-admin' ) ) );
            }
        } else {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( __( 'Failed to save database settings', 'wp-clean-admin' ) );
            }
        }
    }

    /**
     * Get database settings.
     *
     * @since 1.7.15
     */
    public static function get_database_settings() {
        if ( ! self::verify_ajax_request( 'wpca_get_database_settings' ) ) {
            return;
        }
        
        // Get database settings
        $settings = function_exists( 'get_option' ) ? get_option( 'wpca_settings', array() ) : array();
        $database_settings = isset( $settings['database'] ) ? $settings['database'] : array();
        
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( $database_settings );
        }
    }

    /**
     * Reset database settings.
     *
     * @since 1.7.15
     */
    public static function reset_database_settings() {
        if ( ! self::verify_ajax_request( 'wpca_reset_database_settings' ) ) {
            return;
        }
        
        // Get current settings
        $current_settings = function_exists( 'get_option' ) ? get_option( 'wpca_settings', array() ) : array();
        
        // Remove database settings (reset to default)
        unset( $current_settings['database'] );
        $result = function_exists( 'update_option' ) ? update_option( 'wpca_settings', $current_settings ) : false;
        
        if ( $result ) {
            if ( function_exists( 'wp_send_json_success' ) ) {
                wp_send_json_success( array( 'message' => __( 'Database settings reset to default', 'wp-clean-admin' ) ) );
            }
        } else {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( __( 'Failed to reset database settings', 'wp-clean-admin' ) );
            }
        }
    }
}
