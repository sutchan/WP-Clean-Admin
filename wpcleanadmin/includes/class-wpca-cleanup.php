<?php
/**
 * WPCleanAdmin Cleanup Class
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
 * Cleanup class
 */
class Cleanup {
    
    /**
     * Singleton instance
     *
     * @var Cleanup
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     *
     * @return Cleanup
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
        $this->init();
    }
    
    /**
     * Initialize the cleanup module
     */
    public function init(): void {
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'wpca_cleanup_database', array( $this, 'run_database_cleanup' ) );
            \add_action( 'wpca_cleanup_media', array( $this, 'run_media_cleanup' ) );
            \add_action( 'wpca_cleanup_comments', array( $this, 'run_comments_cleanup' ) );
            \add_action( 'wpca_cleanup_content', array( $this, 'run_content_cleanup' ) );
        }
    }
    
    /**
     * Get cleanup statistics
     *
     * @return array Cleanup statistics
     */
    public function get_cleanup_stats(): array {
        global $wpdb;
        
        $stats = array();
        
        // Get transients count
        $stats['transients'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%_transient%'" );
        
        // Get orphaned postmeta count
        $stats['orphaned_postmeta'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} LEFT JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.ID IS NULL" );
        
        // Get orphaned termmeta count
        $stats['orphaned_termmeta'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->termmeta} LEFT JOIN {$wpdb->terms} ON {$wpdb->termmeta}.term_id = {$wpdb->terms}.term_id WHERE {$wpdb->terms}.term_id IS NULL" );
        
        // Get orphaned relationships count
        $stats['orphaned_relationships'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->term_relationships} LEFT JOIN {$wpdb->posts} ON {$wpdb->term_relationships}.object_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.ID IS NULL" );
        
        // Get spam comments count
        $stats['spam_comments'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );
        
        // Get trash comments count
        $stats['trash_comments'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'trash'" );
        
        // Get unapproved comments count
        $stats['unapproved_comments'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '0'" );
        
        // Get orphaned media count
        $stats['orphaned_media'] = $this->get_orphaned_media_count();
        
        return $stats;
    }
    
    /**
     * Get orphaned media count
     *
     * @return int Orphaned media count
     */
    private function get_orphaned_media_count(): int {
        global $wpdb;
        
        $orphaned_count = $wpdb->get_var( "
            SELECT COUNT(*) 
            FROM {$wpdb->posts} p 
            LEFT JOIN {$wpdb->postmeta} pm ON pm.meta_value LIKE CONCAT('%', p.ID, '%') 
            WHERE p.post_type = 'attachment' 
            AND pm.meta_id IS NULL
        " );
        
        return intval( $orphaned_count );
    }
    
    /**
     * Run database cleanup
     *
     * @param array $options Cleanup options including transients, orphaned_postmeta, orphaned_termmeta, orphaned_relationships, and expired_crons
     * @return array Cleanup results with success status, message, and cleaned item counts
     * @global $wpdb WordPress database object
     */
    public function run_database_cleanup( array $options = array() ): array {
        global $wpdb;
        
        $results = array(
            'success' => true,
            'message' => \__( 'Database cleanup completed successfully', WPCA_TEXT_DOMAIN ),
            'cleaned' => array()
        );
        
        // Set default options
        $default_options = array(
            'transients' => true,
            'orphaned_postmeta' => true,
            'orphaned_termmeta' => true,
            'orphaned_relationships' => true,
            'expired_crons' => true
        );
        
        $options = $this->wp_parse_args( $options, $default_options );
        
        // Clean transients
        if ( $options['transients'] ) {
            $deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d", '%_transient_timeout_%', time() ) );
            $deleted += $wpdb->query( $wpdb->prepare( "DELETE t1 FROM {$wpdb->options} t1 INNER JOIN {$wpdb->options} t2 ON t1.option_name = CONCAT( '_transient_', SUBSTRING( t2.option_name, 19 ) ) WHERE t2.option_name LIKE %s", '%_transient_timeout_%' ) );
            $results['cleaned']['transients'] = $deleted;
        }
        
        // Clean orphaned postmeta
        if ( $options['orphaned_postmeta'] ) {
            $deleted = $wpdb->query( $wpdb->prepare(
                "DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE p.ID IS NULL"
            ) );
            $results['cleaned']['orphaned_postmeta'] = $deleted;
        }
        
        // Clean orphaned termmeta
        if ( $options['orphaned_termmeta'] ) {
            $deleted = $wpdb->query( $wpdb->prepare(
                "DELETE tm FROM {$wpdb->termmeta} tm LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id WHERE t.term_id IS NULL"
            ) );
            $results['cleaned']['orphaned_termmeta'] = $deleted;
        }
        
        // Clean orphaned relationships
        if ( $options['orphaned_relationships'] ) {
            $deleted = $wpdb->query( $wpdb->prepare(
                "DELETE tr FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID WHERE p.ID IS NULL"
            ) );
            $results['cleaned']['orphaned_relationships'] = $deleted;
        }
        
        // Clean expired crons
        if ( $options['expired_crons'] ) {
            $crons = $this->_get_cron_array();
            $now = time();
            $deleted = 0;
            
            foreach ( $crons as $timestamp => $cronhooks ) {
                if ( $timestamp < $now ) {
                    foreach ( $cronhooks as $hook => $events ) {
                        foreach ( $events as $sig => $data ) {
                            $this->wp_unschedule_event( $timestamp, $hook, $data['args'] );
                            $deleted++;
                        }
                    }
                }
            }
            
            $results['cleaned']['expired_crons'] = $deleted;
        }
        
        return $results;
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
        global $wpdb;
        
        $results = array(
            'success' => true,
            'message' => \__( 'Media cleanup completed successfully', WPCA_TEXT_DOMAIN ),
            'cleaned' => array()
        );
        
        $default_options = array(
            'orphaned_media' => true,
            'unused_media' => true,
            'duplicate_media' => false,
            'media_age_days' => 0
        );
        
        $options = $this->wp_parse_args( $options, $default_options );
        
        if ( $options['orphaned_media'] ) {
            $orphaned_media = $this->get_orphaned_media();
            
            $deleted = 0;
            foreach ( $orphaned_media as $media ) {
                $this->wp_delete_attachment( $media->ID, true );
                $deleted++;
            }
            
            $results['cleaned']['orphaned_media'] = $deleted;
        }
        
        if ( $options['unused_media'] ) {
            $unused_media = $this->get_unused_media( $options['media_age_days'] );
            
            $deleted = 0;
            foreach ( $unused_media as $media ) {
                $this->wp_delete_attachment( $media->ID, true );
                $deleted++;
            }
            
            $results['cleaned']['unused_media'] = $deleted;
        }
        
        if ( $options['duplicate_media'] ) {
            $duplicates = $this->get_duplicate_media();
            
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
     * Get orphaned media files
     *
     * Media files that are not attached to any post or have no references in postmeta.
     *
     * @return array Array of orphaned media post objects
     * @global $wpdb WordPress database object
     */
    private function get_orphaned_media(): array {
        global $wpdb;
        
        $orphaned_media = $wpdb->get_results( "
            SELECT p.ID, p.guid, p.post_title, p.post_date
            FROM {$wpdb->posts} p 
            LEFT JOIN {$wpdb->postmeta} pm ON pm.meta_value LIKE CONCAT('%', p.ID, '%') 
            WHERE p.post_type = 'attachment' 
            AND pm.meta_id IS NULL
            GROUP BY p.ID
        " );
        
        return $orphaned_media ? $orphaned_media : array();
    }
    
    /**
     * Get unused media files
     *
     * Media files that are not attached to any published content.
     *
     * @param int $age_days Only include media older than X days (0 = all)
     * @return array Array of unused media post objects
     * @global $wpdb WordPress database object
     */
    private function get_unused_media( int $age_days = 0 ): array {
        global $wpdb;
        
        $age_filter = '';
        if ( $age_days > 0 ) {
            $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$age_days} days" ) );
            $age_filter = $wpdb->prepare( " AND p.post_date < %s", $cutoff_date );
        }
        
        $unused_media = $wpdb->get_results( "
            SELECT DISTINCT p.ID, p.guid, p.post_title, p.post_date
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
            {$age_filter}
        " );
        
        return $unused_media ? $unused_media : array();
    }
    
    /**
     * Get duplicate media files
     *
     * Media files that have duplicate filenames or hashes.
     *
     * @return array Array of duplicate media post objects (keeping first, marking others for deletion)
     * @global $wpdb WordPress database object
     */
    private function get_duplicate_media(): array {
        global $wpdb;
        
        $duplicates = $wpdb->get_results( "
            SELECT p1.ID, p1.guid, p1.post_title, p1.post_name
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
            ORDER BY p1.post_date ASC
        " );
        
        return $duplicates ? $duplicates : array();
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
            'message' => \__( 'Comments cleanup completed successfully', WPCA_TEXT_DOMAIN ),
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
                wp_delete_comment( $comment->comment_ID, true );
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
            wp_delete_comment( $comment_id, true );
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
            wp_delete_comment( $comment_id, true );
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
     * Run content cleanup
     *
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function run_content_cleanup( array $options = array() ): array {
        global $wpdb;
        
        $results = array(
            'success' => true,
            'message' => \__( 'Content cleanup completed successfully', WPCA_TEXT_DOMAIN ),
            'cleaned' => array()
        );
        
        // Set default options
        $default_options = array(
            'unused_shortcodes' => true,
            'empty_posts' => true,
            'duplicate_posts' => false
        );
        
        $options = $this->wp_parse_args( $options, $default_options );
        
        // Clean unused shortcodes
        if ( $options['unused_shortcodes'] ) {
            $results['cleaned']['unused_shortcodes'] = $this->cleanup_unused_shortcodes();
        }
        
        // Clean empty posts
        if ( $options['empty_posts'] ) {
            $deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE post_content = %s AND post_type = %s AND post_status = %s", '', 'post', 'publish' ) );
            $results['cleaned']['empty_posts'] = $deleted;
        }
        
        return $results;
    }
    
    /**
     * Clean unused shortcodes from posts
     *
     * This method finds posts containing shortcodes that are no longer registered
     * and removes the shortcode tags from the content.
     *
     * @global wpdb $wpdb WordPress database object
     * @return int Number of posts cleaned
     */
    public function cleanup_unused_shortcodes() {
        global $wpdb;
        
        $cleaned_count = 0;
        
        // Get all registered shortcodes
        $registered_shortcodes = array();
        if ( function_exists( '\shortcode_atts' ) ) {
            global $shortcode_tags;
            if ( isset( $shortcode_tags ) && is_array( $shortcode_tags ) ) {
                $registered_shortcodes = array_keys( $shortcode_tags );
            }
        }
        
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
            $cleaned_content = preg_replace( $all_shortcodes_pattern, '', $original_content );
            
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
    
    /**
     * Wrapper for _get_cron_array function
     *
     * @return array Cron events array
     */
    private function _get_cron_array() {
        if ( function_exists( '\_get_cron_array' ) ) {
            return \_get_cron_array();
        }
        return array();
    }
    
    /**
     * Wrapper for wp_unschedule_event function
     *
     * @param int $timestamp Timestamp
     * @param string $hook Hook name
     * @param array $args Hook arguments
     */
    private function wp_unschedule_event( $timestamp, $hook, $args = array() ) {
        if ( function_exists( '\wp_unschedule_event' ) ) {
            \wp_unschedule_event( $timestamp, $hook, $args );
        }
    }
    
    /**
     * Wrapper for wp_delete_attachment function
     *
     * @param int $post_id Post ID
     * @param bool $force_delete Force delete
     * @return mixed Deleted post or false
     */
    private function wp_delete_attachment( $post_id, $force_delete = false ) {
        if ( function_exists( '\wp_delete_attachment' ) ) {
            return \wp_delete_attachment( $post_id, $force_delete );
        }
        return false;
    }
    
    /**
     * Get registered shortcodes
     *
     * @return array Registered shortcodes
     */
    public function get_registered_shortcodes() {
        global $shortcode_tags;
        if ( isset( $shortcode_tags ) && is_array( $shortcode_tags ) ) {
            return array_keys( $shortcode_tags );
        }
        return array();
    }
    
    /**
     * Cleanup empty posts
     *
     * Provides functionality to clean empty posts according to the OpenSpec
     * admin-cleanup specification.
     *
     * @param array $options Cleanup options including:
     *                       - post_types: Array of post types to clean (default: all)
     *                       - post_statuses: Array of post statuses to clean (default: draft, publish)
     *                       - age_days: Only clean posts older than X days (default: 0 = all)
     * @return array Cleanup result with cleaned_count, posts_type, and message
     * @global $wpdb WordPress database object
     */
    public function cleanup_empty_posts( $options = array() ) {
        global $wpdb;
        
        $default_options = array(
            'post_types' => array( 'post', 'page' ),
            'post_statuses' => array( 'draft', 'publish' ),
            'age_days' => 0
        );
        
        $options = $this->wp_parse_args( $options, $default_options );
        
        // Build post type filter
        $post_types_placeholders = implode( ', ', array_fill( 0, count( $options['post_types'] ), '%s' ) );
        $post_statuses_placeholders = implode( ', ', array_fill( 0, count( $options['post_statuses'] ), '%s' ) );
        
        $query = $wpdb->prepare( "
            SELECT COUNT(*) 
            FROM {$wpdb->posts} 
            WHERE post_type IN ({$post_types_placeholders})
            AND post_status IN ({$post_statuses_placeholders})
            AND ( 
                post_content = '' 
                OR post_content LIKE '% %'
            )
        ", array_merge( $options['post_types'], $options['post_statuses'] ) );
        
        // Add age filter
        if ( $options['age_days'] > 0 ) {
            $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$options['age_days']} days" ) );
            $query .= $wpdb->prepare( " AND post_date < %s", $cutoff_date );
        }
        
        $count = $wpdb->get_var( $query );
        
        return array(
            'cleaned_count' => (int) $count,
            'posts_type' => 'empty_posts',
            'message' => sprintf( 
                \__( 'Found %d empty posts to clean', WPCA_TEXT_DOMAIN ), 
                $count 
            ),
        );
    }
    
    /**
     * Cleanup duplicate posts
     *
     * Provides functionality to clean duplicate posts according to the OpenSpec
     * admin-cleanup specification.
     *
     * @param array $options Cleanup options including:
     *                       - post_types: Array of post types to check (default: post, page)
     *                       - delete_method: 'keep_oldest' or 'keep_newest' (default: keep_oldest)
     *                       - compare_fields: Fields to compare for duplicates (default: title, content)
     * @return array Cleanup result with cleaned_count, duplicates_found, and message
     * @global $wpdb WordPress database object
     */
    public function cleanup_duplicate_posts( $options = array() ) {
        global $wpdb;
        
        $default_options = array(
            'post_types' => array( 'post', 'page' ),
            'delete_method' => 'keep_oldest',
            'compare_fields' => array( 'title', 'content' )
        );
        
        $options = $this->wp_parse_args( $options, $default_options );
        
        // Build post type filter
        $post_types_placeholders = implode( ', ', array_fill( 0, count( $options['post_types'] ), '%s' ) );
        
        // Find duplicates based on post_title and post_content
        $duplicates = $wpdb->get_results( $wpdb->prepare( "
            SELECT p1.ID, p1.post_title, p1.post_date
            FROM {$wpdb->posts} p1
            INNER JOIN {$wpdb->posts} p2 
                ON p1.post_title = p2.post_title 
                AND p1.post_content = p2.post_content
                AND p1.ID != p2.ID
            WHERE p1.post_type IN ({$post_types_placeholders})
            AND p1.post_status IN ('publish', 'draft', 'pending')
            ORDER BY p1.post_date ASC
        ", $options['post_types'] ) );
        
        if ( empty( $duplicates ) ) {
            return array(
                'cleaned_count' => 0,
                'duplicates_found' => 0,
                'type' => 'duplicate_posts',
                'message' => \__( 'No duplicate posts found', WPCA_TEXT_DOMAIN ),
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
                $end_index = count( $group );
                
                for ( $i = $start_index; $i < $end_index; $i++ ) {
                    wp_delete_post( $group[ $i ]->ID, true );
                    $deleted_count++;
                }
            }
        }
        
        return array(
            'cleaned_count' => $deleted_count,
            'duplicates_found' => count( $duplicates ),
            'type' => 'duplicate_posts',
            'message' => sprintf(
                \__( 'Cleaned %d duplicate posts from %d found', WPCA_TEXT_DOMAIN ),
                $deleted_count,
                count( $duplicates )
            ),
        );
    }
    
    /**
     * Get orphaned shortcodes
     *
     * Finds shortcodes that are registered but have no corresponding posts using them.
     *
     * @return array Array of orphaned shortcode names
     * @global $wpdb WordPress database object
     */
    public function get_orphaned_shortcodes() {
        global $wpdb;
        
        // Get all registered shortcodes
        $registered_shortcodes = $this->get_registered_shortcodes();
        
        if ( empty( $registered_shortcodes ) ) {
            return array();
        }
        
        $orphaned = array();
        
        foreach ( $registered_shortcodes as $shortcode ) {
            // Check if shortcode is used in any published content
            $usage_count = $wpdb->get_var( $wpdb->prepare( "
                SELECT COUNT(*) 
                FROM {$wpdb->posts} 
                WHERE post_status IN ('publish', 'draft', 'pending')
                AND post_content LIKE %s
            ", '%[' . $shortcode . '%' ) );
            
            if ( $usage_count === 0 ) {
                $orphaned[] = $shortcode;
            }
        }
        
        return $orphaned;
    }
}
