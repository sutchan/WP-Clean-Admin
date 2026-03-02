<?php
/**
 * WPCleanAdmin Database Cleanup Class
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
 * Database Cleanup class
 */
class Database_Cleanup {
    
    /**
     * Singleton instance
     *
     * @var Database_Cleanup
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     *
     * @return Database_Cleanup
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
        if ( function_exists( '_get_cron_array' ) ) {
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
        if ( function_exists( 'wp_unschedule_event' ) ) {
            \wp_unschedule_event( $timestamp, $hook, $args );
        }
    }
}
