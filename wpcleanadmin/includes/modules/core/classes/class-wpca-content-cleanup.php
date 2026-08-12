<?php
/**
 * WPCleanAdmin Content Cleanup Class
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */
require_once __DIR__ . '/class-wpca-cleanup-helpers.php';
require_once __DIR__ . '/class-wpca-content-cleanup-queries.php';

namespace WPCleanAdmin;

use WPCleanAdmin\Cleanup_Helpers;
use WPCleanAdmin\Content_Cleanup_Queries;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Content Cleanup class
 */
class Content_Cleanup {

    use Cleanup_Helpers;

    /**
     * Singleton instance
     *
     * @var Content_Cleanup
     */
    private static $instance = null;

    /**
     * 内容清理查询器
     *
     * @var Content_Cleanup_Queries
     */
    private $queries;

    /**
     * Get singleton instance
     *
     * @return Content_Cleanup
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
        $this->queries = new Content_Cleanup_Queries();
    }

    /**
     * Run content cleanup
     *
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function run_content_cleanup( array $options = array() ): array {
        global $wpdb;

        $results = array(
            'success' => true,
            'message' => __( 'Content cleanup completed successfully', WPCA_TEXT_DOMAIN ),
            'cleaned' => array()
        );

        // Set default options
        $default_options = array(
            'unused_shortcodes' => true,
            'empty_posts'       => true,
            'duplicate_posts'   => false
        );

        $options = $this->wp_parse_args( $options, $default_options );

        // Clean unused shortcodes
        if ( $options['unused_shortcodes'] ) {
            $results['cleaned']['unused_shortcodes'] = $this->queries->cleanup_unused_shortcodes();
        }

        // Clean empty posts
        if ( $options['empty_posts'] ) {
            $deleted                             = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->posts} WHERE post_content = %s AND post_type = %s AND post_status = %s",
                    '',
                    'post',
                    'publish'
                )
            );
            $results['cleaned']['empty_posts'] = $deleted;
        }

        return $results;
    }

    /**
     * Remove specific shortcode from content
     *
     * @param string $content Post content
     * @param string $shortcode Shortcode name
     * @return string Content with shortcode removed
     */
    public function remove_shortcode( $content, $shortcode ) {
        $pattern = '/\[' . preg_quote( $shortcode, '/' ) . '(?:\s+[^=\]]+)?(?:\s*=\s*["\'][^"\']*["\'])?(?:\s*|\/)*\](\[\/' . preg_quote( $shortcode, '/' ) . '\])?/s';
        return preg_replace( $pattern, '', $content );
    }

    /**
     * Cleanup empty posts
     *
     * @param array $options Cleanup options
     * @return array Cleanup result
     * @global $wpdb WordPress database object
     */
    public function cleanup_empty_posts( $options = array() ) {
        global $wpdb;

        $default_options = array(
            'post_types'     => array( 'post', 'page' ),
            'post_statuses'  => array( 'draft', 'publish' ),
            'age_days'       => 0
        );

        $options = $this->wp_parse_args( $options, $default_options );

        // Build post type filter
        $post_types_placeholders     = implode( ', ', array_fill( 0, count( $options['post_types'] ), '%s' ) );
        $post_statuses_placeholders = implode( ', ', array_fill( 0, count( $options['post_statuses'] ), '%s' ) );

        $query = $wpdb->prepare(
            "SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_type IN ({$post_types_placeholders})
            AND post_status IN ({$post_statuses_placeholders})
            AND (
                post_content = ''
                OR post_content LIKE '%%'
            )",
            array_merge( $options['post_types'], $options['post_statuses'] )
        );

        // Add age filter
        if ( $options['age_days'] > 0 ) {
            $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$options['age_days']} days" ) );
            $query      .= $wpdb->prepare( ' AND post_date < %s', $cutoff_date );
        }

        $count = $wpdb->get_var( $query );

        return array(
            'cleaned_count' => (int) $count,
            'posts_type'    => 'empty_posts',
            'message'       => sprintf(
                __( 'Found %d empty posts to clean', WPCA_TEXT_DOMAIN ),
                $count
            ),
        );
    }

    /**
     * Clean unused shortcodes (delegated to query handler)
     *
     * @return int
     */
    public function cleanup_unused_shortcodes(): int {
        return $this->queries->cleanup_unused_shortcodes();
    }

    /**
     * Get registered shortcodes (delegated to query handler)
     *
     * @return array
     */
    public function get_registered_shortcodes(): array {
        return $this->queries->get_registered_shortcodes();
    }

    /**
     * Get orphaned shortcodes (delegated to query handler)
     *
     * @return array
     */
    public function get_orphaned_shortcodes(): array {
        return $this->queries->get_orphaned_shortcodes();
    }

    /**
     * Cleanup duplicate posts (delegated to query handler)
     *
     * @param array $options
     * @return array
     */
    public function cleanup_duplicate_posts( array $options = array() ): array {
        return $this->queries->cleanup_duplicate_posts( $options );
    }
}
