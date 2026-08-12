<?php
/**
 * WPCleanAdmin Content Cleanup Queries
 *
 * 承载内容清理的纯查询与短代码处理逻辑，
 * 从 Content_Cleanup 主类抽取，保持单一职责。
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
 * 内容清理查询类
 */
class Content_Cleanup_Queries {

    use Cleanup_Helpers;

    /**
     * Get registered shortcodes
     *
     * @return array Registered shortcodes
     */
    public function get_registered_shortcodes(): array {
        global $shortcode_tags;
        if ( isset( $shortcode_tags ) && is_array( $shortcode_tags ) ) {
            return array_keys( $shortcode_tags );
        }
        return array();
    }

    /**
     * Clean unused shortcodes from posts
     *
     * Finds posts containing shortcodes that are no longer registered
     * and removes the shortcode tags from the content.
     *
     * @global wpdb $wpdb WordPress database object
     * @return int Number of posts cleaned
     */
    public function cleanup_unused_shortcodes(): int {
        global $wpdb;

        $cleaned_count = 0;

        // Get all registered shortcodes
        $registered_shortcodes = $this->get_registered_shortcodes();

        if ( empty( $registered_shortcodes ) ) {
            return 0;
        }

        // Build pattern to match all registered shortcodes
        $shortcode_patterns = array();
        foreach ( $registered_shortcodes as $shortcode ) {
            $shortcode_patterns[] = '\[' . preg_quote( $shortcode, '/' ) . '(?:\s+[^=\]]+)?(?:\s*=\s*["\'][^"\']*["\'])?(?:\s*|\/)*\]';
            $shortcode_patterns[] = '\[\/' . preg_quote( $shortcode, '/' ) . '\]';
        }

        // Build pattern to match any shortcode
        $all_shortcodes_pattern = '/' . implode( '|', $shortcode_patterns ) . '/s';

        // Find posts with content that might contain shortcodes
        $posts = $wpdb->get_results(
            "SELECT ID, post_content FROM {$wpdb->posts}
             WHERE post_type IN ('post', 'page')
             AND post_status IN ('publish', 'draft', 'pending', 'private')
             AND post_content LIKE '%[%'"
        );

        foreach ( $posts as $post ) {
            $original_content = $post->post_content;
            $cleaned_content  = preg_replace( $all_shortcodes_pattern, '', $original_content );

            // If content changed, update the post
            if ( $original_content !== $cleaned_content ) {
                $wpdb->update(
                    $wpdb->posts,
                    array( 'post_content' => $cleaned_content ),
                    array( 'ID' => $post->ID ),
                    array( '%s' ),
                    array( '%d' )
                );
                $cleaned_count++;
            }
        }

        return $cleaned_count;
    }

    /**
     * Get orphaned shortcodes
     *
     * Finds shortcodes that are registered but have no corresponding posts using them.
     *
     * @return array Array of orphaned shortcode names
     * @global wpdb $wpdb WordPress database object
     */
    public function get_orphaned_shortcodes(): array {
        global $wpdb;

        $registered_shortcodes = $this->get_registered_shortcodes();

        if ( empty( $registered_shortcodes ) ) {
            return array();
        }

        $orphaned = array();

        foreach ( $registered_shortcodes as $shortcode ) {
            // Check if shortcode is used in any published content
            $usage_count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*)
                    FROM {$wpdb->posts}
                    WHERE post_status IN ('publish', 'draft', 'pending')
                    AND post_content LIKE %s",
                    '%[' . $shortcode . '%'
                )
            );

            if ( (int) $usage_count === 0 ) {
                $orphaned[] = $shortcode;
            }
        }

        return $orphaned;
    }

    /**
     * Cleanup duplicate posts
     *
     * Finds and deletes duplicate posts keeping oldest or newest based on option.
     *
     * @param array $options Cleanup options
     * @return array Cleanup result
     * @global $wpdb WordPress database object
     */
    public function cleanup_duplicate_posts( array $options = array() ): array {
        global $wpdb;

        $default_options = array(
            'post_types'      => array( 'post', 'page' ),
            'delete_method'   => 'keep_oldest',
            'compare_fields'  => array( 'title', 'content' )
        );

        $options = $this->wp_parse_args( $options, $default_options );

        // Build post type filter
        $post_types_placeholders = implode( ', ', array_fill( 0, count( $options['post_types'] ), '%s' ) );

        // Find duplicates based on post_title and post_content
        $duplicates = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p1.ID, p1.post_title, p1.post_date
                FROM {$wpdb->posts} p1
                INNER JOIN {$wpdb->posts} p2
                    ON p1.post_title = p2.post_title
                    AND p1.post_content = p2.post_content
                    AND p1.ID != p2.ID
                WHERE p1.post_type IN ({$post_types_placeholders})
                AND p1.post_status IN ('publish', 'draft', 'pending')
                ORDER BY p1.post_date ASC",
                $options['post_types']
            )
        );

        if ( empty( $duplicates ) ) {
            return array(
                'cleaned_count'    => 0,
                'duplicates_found' => 0,
                'type'             => 'duplicate_posts',
                'message'          => __( 'No duplicate posts found', WPCA_TEXT_DOMAIN ),
            );
        }

        // Group duplicates by content
        $duplicate_groups = array();
        foreach ( $duplicates as $post ) {
            $key = md5( $post->post_title . $post->post_date );
            if ( ! isset( $duplicate_groups[ $key ] ) ) {
                $duplicate_groups[ $key ] = array();
            }
            $duplicate_groups[ $key ][] = $post;
        }

        // Delete duplicates keeping the first (oldest or newest based on option)
        $deleted_count = 0;
        foreach ( $duplicate_groups as $group ) {
            if ( count( $group ) > 1 ) {
                // Skip first, delete rest
                $start_index = ( $options['delete_method'] === 'keep_newest' ) ? 0 : 1;
                $end_index   = count( $group );

                for ( $i = $start_index; $i < $end_index; $i++ ) {
                    \wp_delete_post( $group[ $i ]->ID, true );
                    $deleted_count++;
                }
            }
        }

        return array(
            'cleaned_count'    => $deleted_count,
            'duplicates_found' => count( $duplicates ),
            'type'             => 'duplicate_posts',
            'message'          => sprintf(
                __( 'Cleaned %d duplicate posts from %d found', WPCA_TEXT_DOMAIN ),
                $deleted_count,
                count( $duplicates )
            ),
        );
    }
}
