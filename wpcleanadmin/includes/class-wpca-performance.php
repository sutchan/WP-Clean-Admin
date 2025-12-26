<?php
/**
 * WPCleanAdmin Performance Class
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Performance class
 */
class Performance {
    
    /**
     * Singleton instance
     *
     * @var Performance
     */
    private static $instance;
    
    /**
     * Get singleton instance
     *
     * @return Performance
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
     * Initialize the performance module
     */
    public function init() {
        // Load settings
        $settings = wpca_get_settings();
        
        // Apply performance optimizations based on settings
        if ( isset( $settings['performance'] ) ) {
            // Disable emojis
            if ( isset( $settings['performance']['disable_emojis'] ) && $settings['performance']['disable_emojis'] ) {
                $this->disable_emojis();
            }
            
            // Disable XML-RPC
            if ( isset( $settings['performance']['disable_xmlrpc'] ) && $settings['performance']['disable_xmlrpc'] ) {
                $this->disable_xmlrpc();
            }
            
            // Disable REST API
            if ( isset( $settings['performance']['disable_rest_api'] ) && $settings['performance']['disable_rest_api'] ) {
                $this->disable_rest_api();
            }
            
            // Disable heartbeat
            if ( isset( $settings['performance']['disable_heartbeat'] ) && $settings['performance']['disable_heartbeat'] ) {
                $this->disable_heartbeat();
            }
            
            // Optimize database
            if ( isset( $settings['performance']['optimize_database'] ) && $settings['performance']['optimize_database'] ) {
                $this->optimize_database();
            }
            
            // Clean transients
            if ( isset( $settings['performance']['clean_transients'] ) && $settings['performance']['clean_transients'] ) {
                $this->clean_transients();
            }
        }
        
        // Add performance hooks
        \add_action( 'wpca_clear_cache', array( $this, 'clear_cache' ) );
    }
    
    /**
     * Disable WordPress emojis
     */
    public function disable_emojis() {
        // Remove emoji actions
        if ( function_exists( 'remove_action' ) ) {
            remove_action( 'admin_print_styles', 'print_emoji_styles' );
            remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
            remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
            remove_action( 'wp_print_styles', 'print_emoji_styles' );
        }
        
        if ( function_exists( 'remove_filter' ) ) {
            remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
            remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
            remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        }
        
        // Disable emoji TinyMCE plugin
        \add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );
    }
    
    /**
     * Disable emojis in TinyMCE
     *
     * @param array $plugins TinyMCE plugins
     * @return array Modified TinyMCE plugins
     */
    public function disable_emojis_tinymce( $plugins ) {
        if ( is_array( $plugins ) ) {
            return array_diff( $plugins, array( 'wpemoji' ) );
        }
        return $plugins;
    }
    
    /**
     * Disable XML-RPC
     */
    public function disable_xmlrpc() {
        // Disable XML-RPC methods
        \add_filter( 'xmlrpc_enabled', '__return_false' );
        \add_filter( 'xmlrpc_methods', '__return_empty_array' );
        
        // Remove XML-RPC headers
        if ( function_exists( 'remove_action' ) ) {
            remove_action( 'wp_head', 'rsd_link' );
            remove_action( 'wp_head', 'wlwmanifest_link' );
        }
    }
    
    /**
     * Disable REST API
     */
    public function disable_rest_api() {
        // Disable REST API for non-authenticated users
        \add_filter( 'rest_authentication_errors', array( $this, 'disable_rest_api_authentication' ) );
    }
    
    /**
     * Disable REST API authentication
     *
     * @param WP_Error|bool $result Authentication result
     * @return WP_Error|bool Modified authentication result
     */
    public function disable_rest_api_authentication( $result ) {
        if ( function_exists( 'is_user_logged_in' ) && ! is_user_logged_in() ) {
            return new \WP_Error( 'rest_not_logged_in', \__( 'REST API is disabled for non-authenticated users', WPCA_TEXT_DOMAIN ), array( 'status' => 401 ) );
        }
        return $result;
    }
    
    /**
     * Disable heartbeat
     */
    public function disable_heartbeat() {
        // Remove heartbeat actions
        if ( function_exists( 'remove_action' ) ) {
            remove_action( 'admin_enqueue_scripts', 'wp_enqueue_heartbeat' );
            remove_action( 'wp_enqueue_scripts', 'wp_enqueue_heartbeat' );
        }
    }
    
    /**
     * Optimize database
     */
    public function optimize_database() {
        // Schedule database optimization
        if ( function_exists( '\wp_next_scheduled' ) && function_exists( '\wp_schedule_event' ) ) {
            if ( ! \wp_next_scheduled( 'wpca_optimize_database' ) ) {
                \wp_schedule_event( time(), 'weekly', 'wpca_optimize_database' );
            }
        }
    }
    
    /**
     * Clean transients
     */
    public function clean_transients() {
        // Schedule transient cleanup
        if ( function_exists( '\wp_next_scheduled' ) && function_exists( '\wp_schedule_event' ) ) {
            if ( ! \wp_next_scheduled( 'wpca_clean_transients' ) ) {
                \wp_schedule_event( time(), 'daily', 'wpca_clean_transients' );
            }
        }
        
        // Add transient cleanup hook
        \add_action( 'wpca_clean_transients', array( $this, 'run_transient_cleanup' ) );
    }
    
    /**
     * Run transient cleanup
     */
    public function run_transient_cleanup() {
        global $wpdb;
        
        // Delete expired transients
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d", '%_transient_timeout_%', time() ) );
        $wpdb->query( $wpdb->prepare( "DELETE t1 FROM {$wpdb->options} t1 INNER JOIN {$wpdb->options} t2 ON t1.option_name = CONCAT( '_transient_', SUBSTRING( t2.option_name, 19 ) ) WHERE t2.option_name LIKE %s", '%_transient_timeout_%' ) );
    }
    
    /**
     * Clear cache
     *
     * @return array Cache clearing results
     */
    public function clear_cache() {
        $results = array(
            'success' => true,
            'message' => \__( 'Cache cleared successfully', WPCA_TEXT_DOMAIN ),
            'caches' => array()
        );
        
        // Clear WordPress object cache
        if ( function_exists( '\wp_cache_flush' ) ) {
            \wp_cache_flush();
            $results['caches'][] = array(
                'name' => \__( 'WordPress Object Cache', WPCA_TEXT_DOMAIN ),
                'cleared' => true
            );
        }
        
        // Clear transients
        $this->run_transient_cleanup();
        $results['caches'][] = array(
            'name' => \__( 'Transients', WPCA_TEXT_DOMAIN ),
            'cleared' => true
        );
        
        // Clear opcode cache if available
        if ( function_exists( '\opcache_reset' ) ) {
            \opcache_reset();
            $results['caches'][] = array(
                'name' => \__( 'OPcache', WPCA_TEXT_DOMAIN ),
                'cleared' => true
            );
        }
        
        return $results;
    }
    
    /**
     * Get performance statistics
     *
     * @return array Performance statistics
     */
    public function get_performance_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Get PHP memory usage
        $stats['memory_usage'] = array(
            'current' => \size_format( \memory_get_usage() ),
            'peak' => \size_format( \memory_get_peak_usage() ),
            'limit' => \ini_get( 'memory_limit' )
        );
        
        // Get database query count
        $stats['query_count'] = function_exists( '\get_num_queries' ) ? \get_num_queries() : 0;
        
        // Get page load time
        $stats['load_time'] = function_exists( '\timer_stop' ) ? \timer_stop( 0, 3 ) . 's' : '0.000s';
        
        // Get transients count
        $stats['transients_count'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%transient%'" );
        
        // Get cache status
        $stats['cache_status'] = array(
            'object_cache' => function_exists( '\wp_cache_get' ) ? \__( 'Enabled', WPCA_TEXT_DOMAIN ) : \__( 'Disabled', WPCA_TEXT_DOMAIN ),
            'opcache' => function_exists( '\opcache_get_status' ) ? \__( 'Enabled', WPCA_TEXT_DOMAIN ) : \__( 'Disabled', WPCA_TEXT_DOMAIN )
        );
        
        return $stats;
    }
    
    /**
     * Optimize resources
     */
    public function optimize_resources() {
        // Load settings
        $settings = wpca_get_settings();
        
        if ( isset( $settings['performance'] ) ) {
            // Minify CSS and JS
            if ( isset( $settings['performance']['minify_resources'] ) && $settings['performance']['minify_resources'] ) {
                \add_filter( 'style_loader_tag', array( $this, 'minify_css' ) );
                \add_filter( 'script_loader_tag', array( $this, 'minify_js' ) );
            }
            
            // Combine CSS and JS
            if ( isset( $settings['performance']['combine_resources'] ) && $settings['performance']['combine_resources'] ) {
                \add_filter( 'stylesheet_uri', array( $this, 'combine_css' ) );
                \add_filter( 'script_uri', array( $this, 'combine_js' ) );
            }
        }
    }
    
    /**
     * Minify CSS
     *
     * @param string $tag CSS tag
     * @return string Modified CSS tag
     */
    public function minify_css( $tag ) {
        // This is a placeholder for actual CSS minification
        return $tag;
    }
    
    /**
     * Minify JS
     *
     * @param string $tag JS tag
     * @return string Modified JS tag
     */
    public function minify_js( $tag ) {
        // This is a placeholder for actual JS minification
        return $tag;
    }
    
    /**
     * Combine CSS
     *
     * @param string $uri CSS URI
     * @return string Modified CSS URI
     */
    public function combine_css( $uri ) {
        // This is a placeholder for actual CSS combination
        return $uri;
    }
    
    /**
     * Combine JS
     *
     * @param string $uri JS URI
     * @return string Modified JS URI
     */
    public function combine_js( $uri ) {
        // This is a placeholder for actual JS combination
        return $uri;
    }
}