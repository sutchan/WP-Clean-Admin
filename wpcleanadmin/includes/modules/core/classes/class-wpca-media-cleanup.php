<?php
/**
 * WPCleanAdmin Media Cleanup Class
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */
require_once __DIR__ . '/class-wpca-cleanup-helpers.php';
require_once __DIR__ . '/class-wpca-media-cleanup-queries.php';

namespace WPCleanAdmin;

use WPCleanAdmin\Cleanup_Helpers;
use WPCleanAdmin\Media_Cleanup_Queries;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Media Cleanup class
 */
class Media_Cleanup {

    use Cleanup_Helpers;

    /**
     * Singleton instance
     *
     * @var Media_Cleanup
     */
    private static $instance = null;

    /**
     * 媒体清理查询器
     *
     * @var Media_Cleanup_Queries
     */
    private $queries;

    /**
     * Get singleton instance
     *
     * @return Media_Cleanup
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
        $this->queries = new Media_Cleanup_Queries();
    }

    /**
     * Run media cleanup
     *
     * Provides functionality to clean orphaned media and unused media files
     * according to the OpenSpec admin-cleanup specification.
     *
     * @param array $options Cleanup options including:
     *                       - orphaned_media: Clean media not attached to any post (default: true)
     *                       - unused_media: Clean media not attached to published content (default: true)
     *                       - duplicate_media: Clean duplicate media files (default: false)
     *                       - media_age_days: Only clean media older than X days (default: 0 = all)
     * @return array Cleanup results with success status, message, and cleaned item counts
     * @global $wpdb WordPress database object
     */
    public function run_media_cleanup( array $options = array() ): array {
        $results = array(
            'success' => true,
            'message' => 'Media cleanup completed successfully',
            'cleaned' => array()
        );

        $default_options = array(
            'orphaned_media'   => true,
            'unused_media'     => true,
            'duplicate_media'  => false,
            'media_age_days'   => 0
        );

        $options = $this->wp_parse_args( $options, $default_options );

        if ( $options['orphaned_media'] ) {
            $orphaned_media = $this->queries->get_orphaned_media();

            $deleted = 0;
            foreach ( $orphaned_media as $media ) {
                $this->wp_delete_attachment( $media->ID, true );
                $deleted++;
            }

            $results['cleaned']['orphaned_media'] = $deleted;
        }

        if ( $options['unused_media'] ) {
            $unused_media = $this->queries->get_unused_media( $options['media_age_days'] );

            $deleted = 0;
            foreach ( $unused_media as $media ) {
                $this->wp_delete_attachment( $media->ID, true );
                $deleted++;
            }

            $results['cleaned']['unused_media'] = $deleted;
        }

        if ( $options['duplicate_media'] ) {
            $duplicates = $this->queries->get_duplicate_media();

            $deleted = 0;
            foreach ( $duplicates as $media ) {
                $this->wp_delete_attachment( $media->ID, true );
                $deleted++;
            }

            $results['cleaned']['duplicate_media'] = $deleted;
        }

        return $results;
    }

    /**
     * Get orphaned media files (delegated to query handler)
     *
     * @return array
     */
    public function get_orphaned_media(): array {
        return $this->queries->get_orphaned_media();
    }

    /**
     * Get unused media files (delegated to query handler)
     *
     * @param int $age_days
     * @return array
     */
    public function get_unused_media( int $age_days = 0 ): array {
        return $this->queries->get_unused_media( $age_days );
    }

    /**
     * Get duplicate media files (delegated to query handler)
     *
     * @return array
     */
    public function get_duplicate_media(): array {
        return $this->queries->get_duplicate_media();
    }
}
