<?php
/**
 * WPCleanAdmin Comments Cleanup Class
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */
require_once __DIR__ . '/class-wpca-cleanup-helpers.php';
require_once __DIR__ . '/class-wpca-comments-cleanup-queries.php';

namespace WPCleanAdmin;

use WPCleanAdmin\Cleanup_Helpers;
use WPCleanAdmin\Comments_Cleanup_Queries;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Comments Cleanup class
 */
class Comments_Cleanup {

    use Cleanup_Helpers;

    /**
     * Singleton instance
     *
     * @var Comments_Cleanup
     */
    private static $instance = null;

    /**
     * 评论清理查询器
     *
     * @var Comments_Cleanup_Queries
     */
    private $queries;

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
    private function __construct() {
        $this->queries = new Comments_Cleanup_Queries();
    }

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
            'message' => \__( 'Comments cleanup completed successfully', 'wpcleanadmin' ),
            'cleaned' => array()
        );

        $default_options = array(
            'spam_comments'        => true,
            'unapproved_comments'  => false,
            'duplicate_comments'   => false,
            'old_comments'         => 30,
            'post_ids'             => array()
        );

        $options = $this->wp_parse_args( $options, $default_options );

        if ( $options['spam_comments'] ) {
            $wpdb->get_var( "
                SELECT COUNT(*)
                FROM {$wpdb->comments}
                WHERE comment_approved = 'spam'
            " );

            $deleted = $this->queries->wp_delete_comments_with_status( 'spam' );

            $results['cleaned']['spam_comments'] = $deleted;
        }

        if ( $options['unapproved_comments'] ) {
            $wpdb->get_var( "
                SELECT COUNT(*)
                FROM {$wpdb->comments}
                WHERE comment_approved = '0'
            " );

            $deleted = $this->queries->wp_delete_comments_with_status( 'unapproved' );

            $results['cleaned']['unapproved_comments'] = $deleted;
        }

        if ( $options['old_comments'] > 0 ) {
            $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$options['old_comments']} days" ) );

            $wpdb->get_var(
                $wpdb->prepare( "
                    SELECT COUNT(*)
                    FROM {$wpdb->comments}
                    WHERE comment_date < %s
                    AND comment_approved = '1'
                ", $cutoff_date )
            );

            $deleted = $this->queries->wp_delete_old_comments( $options['old_comments'] );

            $results['cleaned']['old_comments'] = $deleted;
        }

        if ( $options['duplicate_comments'] ) {
            $duplicates = $this->queries->get_duplicate_comments();

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
     * Get duplicate comments (delegated to query handler)
     *
     * @return array
     */
    public function get_duplicate_comments(): array {
        return $this->queries->get_duplicate_comments();
    }
}
