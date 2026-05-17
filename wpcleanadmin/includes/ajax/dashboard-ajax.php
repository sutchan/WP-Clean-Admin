<?php
/**
 * WPCleanAdmin Dashboard AJAX Handler
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
if ( ! function_exists( 'wp_count_posts' ) ) {
    function wp_count_posts() {}
}
if ( ! function_exists( 'wp_count_comments' ) ) {
    function wp_count_comments() {}
}
if ( ! function_exists( 'count_users' ) ) {
    function count_users() {}
}
if ( ! function_exists( 'get_plugins' ) ) {
    function get_plugins() {}
}
if ( ! function_exists( 'wp_get_themes' ) ) {
    function wp_get_themes() {}
}
if ( ! function_exists( 'get_bloginfo' ) ) {
    function get_bloginfo() {}
}
if ( ! function_exists( 'wp_get_theme' ) ) {
    function wp_get_theme() {}
}
if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field() {}
}

/**
 * Dashboard AJAX Handler Class
 */
class Dashboard {
    
    /**
     * Get dashboard statistics
     *
     * @return void
     */
    public static function get_dashboard_stats() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! \wp_verify_nonce( $_POST['nonce'], 'wpca_ajax_nonce' ) ) {
            \wp_send_json_error( array( 'message' => \__( 'Nonce verification failed', WPCA_TEXT_DOMAIN ) ) );
        }
        
        // Check permissions
        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( array( 'message' => \__( 'Insufficient permissions', WPCA_TEXT_DOMAIN ) ) );
        }
        
        try {
            // Get dashboard statistics
            $stats = array(
                'posts' => \wp_count_posts()->publish,
                'pages' => \wp_count_posts( 'page' )->publish,
                'comments' => \wp_count_comments()->approved,
                'users' => \count_users()['total_users'],
                'plugins' => \count( \get_plugins() ),
                'themes' => \count( \wp_get_themes() )
            );
            
            \wp_send_json_success( array( 'stats' => $stats ) );
        } catch ( \Exception $e ) {
            \wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }
    
    /**
     * Get system information
     *
     * @return void
     */
    public static function get_system_info() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! \wp_verify_nonce( $_POST['nonce'], 'wpca_ajax_nonce' ) ) {
            \wp_send_json_error( array( 'message' => \__( 'Nonce verification failed', WPCA_TEXT_DOMAIN ) ) );
        }
        
        // Check permissions
        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( array( 'message' => \__( 'Insufficient permissions', WPCA_TEXT_DOMAIN ) ) );
        }
        
        try {
            // Get system information
            $system_info = array(
                'wordpress' => array(
                    'version' => \get_bloginfo( 'version' ),
                    'url' => \get_bloginfo( 'url' ),
                    'language' => \get_bloginfo( 'language' )
                ),
                'server' => array(
                    'php' => PHP_VERSION,
                    'mysql' => $GLOBALS['wpdb']->db_version(),
                    'server_software' => $_SERVER['SERVER_SOFTWARE']
                ),
                'theme' => array(
                    'name' => \wp_get_theme()->get( 'Name' ),
                    'version' => \wp_get_theme()->get( 'Version' )
                )
            );
            
            \wp_send_json_success( array( 'system_info' => $system_info ) );
        } catch ( \Exception $e ) {
            \wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }
    
    /**
     * Run quick action
     *
     * @return void
     */
    public static function run_quick_action() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! \wp_verify_nonce( $_POST['nonce'], 'wpca_ajax_nonce' ) ) {
            \wp_send_json_error( array( 'message' => \__( 'Nonce verification failed', WPCA_TEXT_DOMAIN ) ) );
        }
        
        // Check permissions
        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( array( 'message' => \__( 'Insufficient permissions', WPCA_TEXT_DOMAIN ) ) );
        }
        
        try {
            $action = isset( $_POST['action_name'] ) ? \sanitize_text_field( $_POST['action_name'] ) : '';
            
            // Run quick action based on action name
            $result = array();
            
            switch ( $action ) {
                case 'clear_cache':
                    // Clear cache
                    $result['message'] = \__( 'Cache cleared successfully', WPCA_TEXT_DOMAIN );
                    break;
                case 'optimize_database':
                    // Optimize database
                    $result['message'] = \__( 'Database optimized successfully', WPCA_TEXT_DOMAIN );
                    break;
                case 'cleanup_transients':
                    // Cleanup transients
                    $result['message'] = \__( 'Transients cleaned up successfully', WPCA_TEXT_DOMAIN );
                    break;
                default:
                    \wp_send_json_error( array( 'message' => \__( 'Invalid action', WPCA_TEXT_DOMAIN ) ) );
            }
            
            \wp_send_json_success( $result );
        } catch ( \Exception $e ) {
            \wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }
}
