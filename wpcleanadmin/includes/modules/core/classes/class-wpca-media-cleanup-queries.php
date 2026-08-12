<?php
/**
 * WPCleanAdmin Media Cleanup Queries
 *
 * 承载媒体清理的数据库查询（孤立/未用/重复媒体），
 * 从 Media_Cleanup 主类抽取，保持单一职责。
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
 * 媒体清理查询类
 */
class Media_Cleanup_Queries {

    use Cleanup_Helpers;

    /**
     * Get orphaned media files
     *
     * Media files that are not attached to any post or have no references in postmeta.
     *
     * @return array Array of orphaned media post objects
     * @global wpdb $wpdb WordPress database object
     */
    public function get_orphaned_media(): array {
        global $wpdb;

        $orphaned_media = $wpdb->get_results(
            "SELECT p.ID, p.guid, p.post_title, p.post_date
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON pm.meta_value LIKE CONCAT('%', p.ID, '%')
            WHERE p.post_type = 'attachment'
            AND pm.meta_id IS NULL
            GROUP BY p.ID"
        );

        return $orphaned_media ? $orphaned_media : array();
    }

    /**
     * Get unused media files
     *
     * Media files that are not attached to any published content.
     *
     * @param int $age_days Only include media older than X days (0 = all)
     * @return array Array of unused media post objects
     * @global wpdb $wpdb WordPress database object
     */
    public function get_unused_media( int $age_days = 0 ): array {
        global $wpdb;

        $age_filter = '';
        if ( $age_days > 0 ) {
            $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$age_days} days" ) );
            $age_filter  = $wpdb->prepare( ' AND p.post_date < %s', $cutoff_date );
        }

        $unused_media = $wpdb->get_results(
            "SELECT DISTINCT p.ID, p.guid, p.post_title, p.post_date
            FROM {$wpdb->posts} p
            WHERE p.post_type = 'attachment'
            AND p.ID NOT IN (
                SELECT DISTINCT pm.post_id
                FROM {$wpdb->postmeta} pm
                WHERE pm.meta_key = '_thumbnail_id'
            )
            AND p.ID NOT IN (
                SELECT DISTINCT CAST(pm.meta_value AS UNSIGNED)
                FROM {$wpdb->postmeta} pm
                WHERE pm.meta_value LIKE CONCAT('%', p.ID, '%')
                AND pm.meta_key IN ( '_wp_attachment_metadata', '_elementor_data' )
            )
            AND p.post_status = 'inherit'
            {$age_filter}"
        );

        return $unused_media ? $unused_media : array();
    }

    /**
     * Get duplicate media files
     *
     * Media files that have duplicate filenames or hashes.
     *
     * @return array Array of duplicate media post objects
     * @global wpdb $wpdb WordPress database object
     */
    public function get_duplicate_media(): array {
        global $wpdb;

        $duplicates = $wpdb->get_results(
            "SELECT p1.ID, p1.guid, p1.post_title, p1.post_name
            FROM {$wpdb->posts} p1
            INNER JOIN (
                SELECT post_name, COUNT(*) as cnt
                FROM {$wpdb->posts}
                WHERE post_type = 'attachment'
                AND post_status = 'inherit'
                GROUP BY post_name
                HAVING COUNT(*) > 1
            ) p2 ON p1.post_name = p2.post_name
            WHERE p1.post_type = 'attachment'
            AND p1.post_status = 'inherit'
            ORDER BY p1.post_date ASC"
        );

        return $duplicates ? $duplicates : array();
    }
}
