<?php
/**
 * WPCleanAdmin Content Cleanup Class
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
 * Content Cleanup class
 */
class Content_Cleanup {
    
    /**
     * Singleton instance
     *
     * @var Content_Cleanup
     */
    private static $instance = null;
    
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
    private function __construct() {}
    
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
                OR post_content LIKE '%%'
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
                __( 'Found %d empty posts to clean', WPCA_TEXT_DOMAIN ), 
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
                'message' => __( 'No duplicate posts found', WPCA_TEXT_DOMAIN ),
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
                    \wp_delete_post( $group[ $i ]->ID, true );
                    $deleted_count++;
                }
            }
        }
        
        return array(
            'cleaned_count' => $deleted_count,
            'duplicates_found' => count( $duplicates ),
            'type' => 'duplicate_posts',
            'message' => sprintf(
                __( 'Cleaned %d duplicate posts from %d found', WPCA_TEXT_DOMAIN ),
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
