<?php
/**
 * Cleanup class for WP Clean Admin plugin
 *
 * @package WPCleanAdmin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

namespace WPCleanAdmin;

/**
 * Cleanup class
 */
class Cleanup {
    
    /**
     * Singleton instance
     *
     * @var Cleanup
     */
    private static $instance;
    
    /**
     * Get singleton instance
     *
     * @return Cleanup
     */
    public static function get_instance() {
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
    public function init() {
        // Add cleanup hooks
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
    public function get_cleanup_stats() {
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
    private function get_orphaned_media_count() {
        global $wpdb;
        
        // Use a single query to get orphaned media count
        // This is more efficient than looping through all media files and checking each one individually
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
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function run_database_cleanup( $options = array() ) {
        global $wpdb;
        
        $results = array(
            'success' => true,
            'message' => __( 'Database cleanup completed successfully', WPCA_TEXT_DOMAIN ),
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
        
        $options = ( function_exists( 'wp_parse_args' ) ? \wp_parse_args( $options, $default_options ) : array_merge( $default_options, $options ) );
        
        // Clean transients
        if ( $options['transients'] ) {
            $deleted = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_timeout_%' AND option_value < " . time() );
            $deleted += $wpdb->query( "DELETE t1 FROM {$wpdb->options} t1 INNER JOIN {$wpdb->options} t2 ON t1.option_name = CONCAT( '_transient_', SUBSTRING( t2.option_name, 19 ) ) WHERE t2.option_name LIKE '%_transient_timeout_%'" );
            $results['cleaned']['transients'] = $deleted;
        }
        
        // Clean orphaned postmeta
        if ( $options['orphaned_postmeta'] ) {
            $deleted = $wpdb->query( "DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE p.ID IS NULL" );
            $results['cleaned']['orphaned_postmeta'] = $deleted;
        }
        
        // Clean orphaned termmeta
        if ( $options['orphaned_termmeta'] ) {
            $deleted = $wpdb->query( "DELETE tm FROM {$wpdb->termmeta} tm LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id WHERE t.term_id IS NULL" );
            $results['cleaned']['orphaned_termmeta'] = $deleted;
        }
        
        // Clean orphaned relationships
        if ( $options['orphaned_relationships'] ) {
            $deleted = $wpdb->query( "DELETE tr FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID WHERE p.ID IS NULL" );
            $results['cleaned']['orphaned_relationships'] = $deleted;
        }
        
        // Clean expired crons
        if ( $options['expired_crons'] ) {
            $crons = _get_cron_array();
            $now = time();
            $deleted = 0;
            
            foreach ( $crons as $timestamp => $cronhooks ) {
                if ( $timestamp < $now ) {
                    foreach ( $cronhooks as $hook => $events ) {
                        foreach ( $events as $sig => $data ) {
                            wp_unschedule_event( $timestamp, $hook, $data['args'] );
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
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function run_media_cleanup( $options = array() ) {
        global $wpdb;
        
        $results = array(
            'success' => true,
            'message' => __( 'Media cleanup completed successfully', WPCA_TEXT_DOMAIN ),
            'cleaned' => array()
        );
        
        // Set default options
        $default_options = array(
            'orphaned_media' => true,
            'unused_media' => true,
            'duplicate_media' => false
        );
        
        $options = wp_parse_args( $options, $default_options );
        
        // Clean orphaned media
        if ( $options['orphaned_media'] ) {
            // Get all orphaned media files in a single query
            // This is more efficient than looping through all media files and checking each one individually
            $orphaned_media = $wpdb->get_results( "
                SELECT p.ID, p.guid 
                FROM {$wpdb->posts} p 
                LEFT JOIN {$wpdb->postmeta} pm ON pm.meta_value LIKE CONCAT('%', p.ID, '%') 
                WHERE p.post_type = 'attachment' 
                AND pm.meta_id IS NULL
            " );
            
            $deleted = 0;
            
            foreach ( $orphaned_media as $media ) {
                // Delete media file
                wp_delete_attachment( $media->ID, true );
                $deleted++;
            }
            
            $results['cleaned']['orphaned_media'] = $deleted;
        }
        
        return $results;
    }
    
    /**
     * Run comments cleanup
     *
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function run_comments_cleanup( $options = array() ) {
        global $wpdb;
        
        $results = array(
            'success' => true,
            'message' => __( 'Comments cleanup completed successfully', WPCA_TEXT_DOMAIN ),
            'cleaned' => array()
        );
        
        // Set default options
        $default_options = array(
            'spam_comments' => true,
            'trash_comments' => true,
            'unapproved_comments' => false,
            'old_comments' => false
        );
        
        $options = wp_parse_args( $options, $default_options );
        
        // Clean spam comments
        if ( $options['spam_comments'] ) {
            $deleted = $wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );
            $results['cleaned']['spam_comments'] = $deleted;
        }
        
        // Clean trash comments
        if ( $options['trash_comments'] ) {
            $deleted = $wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_approved = 'trash'" );
            $results['cleaned']['trash_comments'] = $deleted;
        }
        
        // Clean unapproved comments
        if ( $options['unapproved_comments'] ) {
            $deleted = $wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_approved = '0'" );
            $results['cleaned']['unapproved_comments'] = $deleted;
        }
        
        // Clean old comments
        if ( $options['old_comments'] ) {
            $old_days = isset( $options['old_days'] ) ? intval( $options['old_days'] ) : 365;
            $old_date = date( 'Y-m-d H:i:s', strtotime( "-{$old_days} days" ) );
            $deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->comments} WHERE comment_date < %s", $old_date ) );
            $results['cleaned']['old_comments'] = $deleted;
        }
        
        return $results;
    }
    
    /**
     * Run content cleanup
     *
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function run_content_cleanup( $options = array() ) {
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
        
        $options = wp_parse_args( $options, $default_options );
        
        // Clean unused shortcodes
        if ( $options['unused_shortcodes'] ) {
            // This is a placeholder for actual shortcode cleanup
            $results['cleaned']['unused_shortcodes'] = 0;
        }
        
        // Clean empty posts
        if ( $options['empty_posts'] ) {
            $deleted = $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_content = '' AND post_type = 'post' AND post_status = 'publish'" );
            $results['cleaned']['empty_posts'] = $deleted;
        }
        
        return $results;
    }
}