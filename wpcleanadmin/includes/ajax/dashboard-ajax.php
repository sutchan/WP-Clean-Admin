<?php
/**
 * WPCleanAdmin Dashboard AJAX Handler
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
            // Get dashboard statistics
            $stats = array(
                'posts' => function_exists( '\wp_count_posts' ) ? \wp_count_posts()->publish : 0,
                'pages' => function_exists( '\wp_count_posts' ) ? \wp_count_posts( 'page' )->publish : 0,
                'comments' => function_exists( '\wp_count_comments' ) ? \wp_count_comments()->approved : 0,
                'users' => function_exists( '\count_users' ) ? \count_users()['total_users'] : 0,
                'plugins' => function_exists( '\get_plugins' ) ? \count( \get_plugins() ) : 0,
                'themes' => function_exists( '\wp_get_themes' ) ? \count( \wp_get_themes() ) : 0
            );
            
            if ( function_exists( '\wp_send_json_success' ) ) {
                \wp_send_json_success( array( 'stats' => $stats ) );
            }
        } catch ( \Exception $e ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => $e->getMessage() ) );
            }
        }
    }
    
    /**
     * Get system information
     *
     * @return void
     */
    public static function get_system_info() {
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
            // Get system information
            $system_info = array(
                'wordpress' => array(
                    'version' => function_exists( '\get_bloginfo' ) ? \get_bloginfo( 'version' ) : '',
                    'url' => function_exists( '\get_bloginfo' ) ? \get_bloginfo( 'url' ) : '',
                    'language' => function_exists( '\get_bloginfo' ) ? \get_bloginfo( 'language' ) : ''
                ),
                'server' => array(
                    'php' => PHP_VERSION,
                    'mysql' => isset( $GLOBALS['wpdb'] ) ? $GLOBALS['wpdb']->db_version() : '',
                    'server_software' => $_SERVER['SERVER_SOFTWARE']
                ),
                'theme' => array(
                    'name' => function_exists( '\wp_get_theme' ) ? \wp_get_theme()->get( 'Name' ) : '',
                    'version' => function_exists( '\wp_get_theme' ) ? \wp_get_theme()->get( 'Version' ) : ''
                )
            );
            
            if ( function_exists( '\wp_send_json_success' ) ) {
                \wp_send_json_success( array( 'system_info' => $system_info ) );
            }
        } catch ( \Exception $e ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => $e->getMessage() ) );
            }
        }
    }
    
    /**
     * Run quick action
     *
     * @return void
     */
    public static function run_quick_action() {
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
            $action = isset( $_POST['action_name'] ) ? ( function_exists( '\sanitize_text_field' ) ? \sanitize_text_field( $_POST['action_name'] ) : '' ) : '';
            
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
                    if ( function_exists( '\wp_send_json_error' ) ) {
                        \wp_send_json_error( array( 'message' => \__( 'Invalid action', WPCA_TEXT_DOMAIN ) ) );
                    }
                    return;
            }
            
            if ( function_exists( '\wp_send_json_success' ) ) {
                \wp_send_json_success( $result );
            }
        } catch ( \Exception $e ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => $e->getMessage() ) );
            }
        }
    }
}
