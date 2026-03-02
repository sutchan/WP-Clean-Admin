<?php
/**
 * WPCleanAdmin Comments Cleanup Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Comments Cleanup class
 */
class Comments_Cleanup {
    
    /**
     * Singleton instance
     *
     * @var Comments_Cleanup
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     *
     * @return Comments_Cleanup
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {}
    
    /**
     * Run comments cleanup
     *
     * Provides functionality to clean comments according to the OpenSpec
     * admin-cleanup specification.
     *
     * @param array $options Cleanup options including:
     *                       - spam_comments: Clean spam comments (default: true)
     *                       - unapproved_comments: Clean unapproved comments (default: false)
     *                       - duplicate_comments: Clean duplicate comments (default: false)
     *                       - old_comments: Clean comments older than X days (default: 30)
     *                       - post_ids: Only clean comments for specific post IDs (default: all)
     * @return array Cleanup results
     * @global $wpdb WordPress database object
     */
    public function run_comments_cleanup( array $options = array() ): array {
        global $wpdb;
        
        $results = array(
            'success' => true,
            'message' => __( 'Comments cleanup completed successfully', WPCA_TEXT_DOMAIN ),
            'cleaned' => array()
        );
        
        $default_options = array(
            'spam_comments' => true,
            'unapproved_comments' => false,
            'duplicate_comments' => false,
            'old_comments' => 30,
            'post_ids' => array()
        );
        
        $options = $this->wp_parse_args( $options, $default_options );
        
        if ( $options['spam_comments'] ) {
            $spam_count = $wpdb->get_var( "
                SELECT COUNT(*) 
                FROM {$wpdb->comments} 
                WHERE comment_approved = 'spam'
            " );
            
            $deleted = $this->wp_delete_comments_with_status( 'spam' );
            
            $results['cleaned']['spam_comments'] = $deleted;
        }
        
        if ( $options['unapproved_comments'] ) {
            $unapproved_count = $wpdb->get_var( "
                SELECT COUNT(*) 
                FROM {$wpdb->comments} 
                WHERE comment_approved = '0'
            " );
            
            $deleted = $this->wp_delete_comments_with_status( 'unapproved' );
            
            $results['cleaned']['unapproved_comments'] = $deleted;
        }
        
        if ( $options['old_comments'] > 0 ) {
            $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$options['old_comments']} days" ) );
            
            $old_count = $wpdb->get_var( $wpdb->prepare( "
                SELECT COUNT(*) 
                FROM {$wpdb->comments} 
                WHERE comment_date < %s
                AND comment_approved = '1'
            ", $cutoff_date ) );
            
            $deleted = $this->wp_delete_old_comments( $options['old_comments'] );
            
            $results['cleaned']['old_comments'] = $deleted;
        }
        
        if ( $options['duplicate_comments'] ) {
            $duplicates = $this->get_duplicate_comments();
            
            $deleted = 0;
            foreach ( $duplicates as $comment ) {
                \wp_delete_comment( $comment->comment_ID, true );
                $deleted++;
            }
            
            $results['cleaned']['duplicate_comments'] = $deleted;
        }
        
        return $results;
    }
    
    /**
     * Delete comments with specific status
     *
     * @param string $status Comment status (spam, unapproved, trash)
     * @return int Number of comments deleted
     * @global $wpdb WordPress database object
     */
    private function wp_delete_comments_with_status( string $status ): int {
        global $wpdb;
        
        $comment_ids = $wpdb->get_col( $wpdb->prepare( "
            SELECT comment_ID 
            FROM {$wpdb->comments} 
            WHERE comment_approved = %s
        ", $status ) );
        
        $deleted = 0;
        foreach ( $comment_ids as $comment_id ) {
            \wp_delete_comment( $comment_id, true );
            $deleted++;
        }
        
        return $deleted;
    }
    
    /**
     * Delete old comments
     *
     * @param int $days_old Number of days to consider comment as old
     * @return int Number of comments deleted
     * @global $wpdb WordPress database object
     */
    private function wp_delete_old_comments( int $days_old ): int {
        global $wpdb;
        
        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_old} days" ) );
        
        $comment_ids = $wpdb->get_col( $wpdb->prepare( "
            SELECT comment_ID 
            FROM {$wpdb->comments} 
            WHERE comment_date < %s
            AND comment_approved = '1'
        ", $cutoff_date ) );
        
        $deleted = 0;
        foreach ( $comment_ids as $comment_id ) {
            \wp_delete_comment( $comment_id, true );
            $deleted++;
        }
        
        return $deleted;
    }
    
    /**
     * Get duplicate comments
     *
     * Comments with identical content from the same IP.
     *
     * @return array Array of duplicate comment objects
     * @global $wpdb WordPress database object
     */
    private function get_duplicate_comments(): array {
        global $wpdb;
        
        $duplicates = $wpdb->get_results( "
            SELECT c1.comment_ID, c1.comment_content, c1.comment_author_IP, c1.comment_date
            FROM {$wpdb->comments} c1
            INNER JOIN {$wpdb->comments} c2 
                ON c1.comment_content = c2.comment_content
                AND c1.comment_author_IP = c2.comment_author_IP
                AND c1.comment_ID != c2.comment_ID
            WHERE c1.comment_approved = '1'
            ORDER BY c1.comment_date ASC
        " );
        
        return $duplicates ? $duplicates : array();
    }
    
    /**
     * Wrapper for wp_parse_args function
     *
     * @param array|string $args Arguments to parse
     * @param array $defaults Default values
     * @return array Parsed arguments
     */
    private function wp_parse_args( $args, $defaults ) {
        if ( function_exists( '\wp_parse_args' ) ) {
            return \wp_parse_args( $args, $defaults );
        }
        return array_merge( $defaults, (array) $args );
    }
}
