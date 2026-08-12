<?php
/**
 * WPCleanAdmin Comments Cleanup Queries
 *
 * 承载评论清理的数据库查询（按状态/重复/旧评论），
 * 从 Comments_Cleanup 主类抽取，保持单一职责。
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 评论清理查询类
 */
class Comments_Cleanup_Queries {

    use Cleanup_Helpers;

    /**
     * Delete comments with specific status
     *
     * @param string $status Comment status (spam, trash, unapproved)
     * @return int Number of comments deleted
     * @global wpdb $wpdb WordPress database object
     */
    public function wp_delete_comments_with_status( string $status ): int {
        global $wpdb;

        $valid_statuses = array( 'spam', 'trash', 'unapproved' );
        if ( ! in_array( $status, $valid_statuses, true ) ) {
            return 0;
        }

        $comment_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved = %s",
                $status
            )
        );

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
     * @return array Array of duplicate comment objects
     * @global wpdb $wpdb WordPress database object
     */
    public function get_duplicate_comments(): array {
        global $wpdb;

        $duplicates = $wpdb->get_results(
            "SELECT comment_post_ID, comment_author_email, comment_content, COUNT(*) as count
            FROM {$wpdb->comments}
            WHERE comment_approved = '1'
            GROUP BY comment_post_ID, comment_author_email, comment_content
            HAVING COUNT(*) > 1"
        );

        return $duplicates ? $duplicates : array();
    }

    /**
     * Delete old comments
     *
     * @param int $days Old comments older than X days
     * @return int Number of comments deleted
     * @global wpdb $wpdb WordPress database object
     */
    public function wp_delete_old_comments( int $days ): int {
        global $wpdb;

        if ( $days <= 0 ) {
            return 0;
        }

        $cutoff = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_date < %s",
                $cutoff
            )
        );

        if ( $count > 0 ) {
            $wpdb->delete(
                $wpdb->comments,
                array( 'comment_date <' => $cutoff ),
                array( '%s' )
            );
        }

        return (int) $count;
    }
}
