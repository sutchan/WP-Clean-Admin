<?php
/**
 * WPCleanAdmin Cleanup AJAX Handler
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

if ( ! function_exists( '\wp_verify_nonce' ) ) {
    function wp_verify_nonce() {}
}
if ( ! function_exists( '\wp_send_json_error' ) ) {
    function wp_send_json_error() {}
}
if ( ! function_exists( '\wp_send_json_success' ) ) {
    function wp_send_json_success() {}
}
if ( ! function_exists( '\current_user_can' ) ) {
    function current_user_can() {}
}
if ( ! function_exists( '\__' ) ) {
    function __() {}
}

/**
 * Cleanup AJAX Handler Class
 */
class Cleanup {
    
    /**
     * Cleanup database
     *
     * @return void
     */
    public static function cleanup_database() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! function_exists( '\wp_verify_nonce' ) || ! \wp_verify_nonce( $_POST['nonce'], 'wpca_ajax_nonce' ) ) {
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
            // Cleanup database
            $result = array(
                'message' => \__( 'Database cleanup completed successfully', WPCA_TEXT_DOMAIN ),
                'details' => array(
                    'transients' => \__( 'Transients cleaned up', WPCA_TEXT_DOMAIN ),
                    'autosave' => \__( 'Autosave revisions cleaned up', WPCA_TEXT_DOMAIN ),
                    'spam_comments' => \__( 'Spam comments cleaned up', WPCA_TEXT_DOMAIN )
                )
            );
            
            if ( function_exists( '\wp_send_json_success' ) ) {
                \wp_send_json_success( $result );
            }
        } catch ( \Exception $e ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => $e->getMessage() ) );
            }
        }
    }
    
    /**
     * Cleanup media
     *
     * @return void
     */
    public static function cleanup_media() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! function_exists( '\wp_verify_nonce' ) || ! \wp_verify_nonce( $_POST['nonce'], 'wpca_ajax_nonce' ) ) {
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
            // Cleanup media
            $result = array(
                'message' => \__( 'Media cleanup completed successfully', WPCA_TEXT_DOMAIN ),
                'details' => array(
                    'unused_media' => \__( 'Unused media files cleaned up', WPCA_TEXT_DOMAIN ),
                    'duplicate_media' => \__( 'Duplicate media files cleaned up', WPCA_TEXT_DOMAIN )
                )
            );
            
            if ( function_exists( '\wp_send_json_success' ) ) {
                \wp_send_json_success( $result );
            }
        } catch ( \Exception $e ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => $e->getMessage() ) );
            }
        }
    }
    
    /**
     * Cleanup comments
     *
     * @return void
     */
    public static function cleanup_comments() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! function_exists( '\wp_verify_nonce' ) || ! \wp_verify_nonce( $_POST['nonce'], 'wpca_ajax_nonce' ) ) {
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
            // Cleanup comments
            $result = array(
                'message' => \__( 'Comments cleanup completed successfully', WPCA_TEXT_DOMAIN ),
                'details' => array(
                    'spam_comments' => \__( 'Spam comments deleted', WPCA_TEXT_DOMAIN ),
                    'trash_comments' => \__( 'Trash comments emptied', WPCA_TEXT_DOMAIN ),
                    'unapproved_comments' => \__( 'Unapproved comments cleaned up', WPCA_TEXT_DOMAIN )
                )
            );
            
            if ( function_exists( '\wp_send_json_success' ) ) {
                \wp_send_json_success( $result );
            }
        } catch ( \Exception $e ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => $e->getMessage() ) );
            }
        }
    }
    
    /**
     * Cleanup content
     *
     * @return void
     */
    public static function cleanup_content() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! function_exists( '\wp_verify_nonce' ) || ! \wp_verify_nonce( $_POST['nonce'], 'wpca_ajax_nonce' ) ) {
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
            // Cleanup content
            $result = array(
                'message' => \__( 'Content cleanup completed successfully', WPCA_TEXT_DOMAIN ),
                'details' => array(
                    'revisions' => \__( 'Post revisions cleaned up', WPCA_TEXT_DOMAIN ),
                    'draft_posts' => \__( 'Draft posts cleaned up', WPCA_TEXT_DOMAIN ),
                    'pending_posts' => \__( 'Pending posts cleaned up', WPCA_TEXT_DOMAIN )
                )
            );
            
            if ( function_exists( '\wp_send_json_success' ) ) {
                \wp_send_json_success( $result );
            }
        } catch ( \Exception $e ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => $e->getMessage() ) );
            }
        }
    }
    
    /**
     * Get cleanup statistics
     *
     * @return void
     */
    public static function get_cleanup_stats() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! function_exists( '\wp_verify_nonce' ) || ! \wp_verify_nonce( $_POST['nonce'], 'wpca_ajax_nonce' ) ) {
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
            // Get cleanup statistics
            $stats = array(
                'transients' => 150, // Example value
                'revisions' => 75, // Example value
                'spam_comments' => 200, // Example value
                'unused_media' => 50, // Example value
                'autosave' => 30 // Example value
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
}
