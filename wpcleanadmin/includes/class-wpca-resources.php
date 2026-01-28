<?php
/**
 * WPCleanAdmin Resources Class
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
 * Resources class
 */
class Resources {
    
    /**
     * Singleton instance
     *
     * @var Resources
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     *
     * @return Resources
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
     * Initialize the resources module
     */
    public function init(): void {
        // Add resources hooks
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'wp_enqueue_scripts', array( $this, 'optimize_frontend_resources' ), 999 );
            \add_action( 'admin_enqueue_scripts', array( $this, 'optimize_admin_resources' ), 999 );
        }
    }
    
    /**
     * Optimize frontend resources
     */
    public function optimize_frontend_resources(): void {
        // Load settings
        $settings = \wpca_get_settings();
        
        // Apply frontend resource optimizations based on settings
        if ( isset( $settings['resources'] ) ) {
            // Disable emojis
            if ( isset( $settings['resources']['disable_emojis'] ) && $settings['resources']['disable_emojis'] ) {
                if ( function_exists( 'remove_action' ) && function_exists( 'remove_filter' ) ) {
                    \remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
                    \remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
                    \remove_action( 'wp_print_styles', 'print_emoji_styles' );
                    \remove_action( 'admin_print_styles', 'print_emoji_styles' );
                    \remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
                    \remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
                    \remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
                }
            }
            
            // Disable WordPress embed
            if ( isset( $settings['resources']['disable_embed'] ) && $settings['resources']['disable_embed'] ) {
                if ( function_exists( 'remove_action' ) ) {
                    \remove_action( 'wp_head', 'wp_oembed_add_host_js' );
                    \remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
                }
            }
            
            // Disable REST API
            if ( isset( $settings['resources']['disable_rest_api'] ) && $settings['resources']['disable_rest_api'] ) {
                if ( function_exists( 'add_filter' ) ) {
                    \add_filter( 'rest_authentication_errors', array( $this, 'disable_rest_api' ) );
                }
            }
            
            // Disable heartbeat
            if ( isset( $settings['resources']['disable_heartbeat'] ) && $settings['resources']['disable_heartbeat'] ) {
                if ( function_exists( 'wp_deregister_script' ) && function_exists( 'remove_action' ) ) {
                    \wp_deregister_script( 'heartbeat' );
                    \remove_action( 'admin_enqueue_scripts', 'wp_enqueue_heartbeat' );
                    \remove_action( 'wp_enqueue_scripts', 'wp_enqueue_heartbeat' );
                }
            }
        }
    }
    
    /**
     * Optimize admin resources
     */
    public function optimize_admin_resources(): void {
        // Load settings
        $settings = \wpca_get_settings();
        
        // Apply admin resource optimizations based on settings
        if ( isset( $settings['resources'] ) ) {
            // Remove admin toolbar
            if ( isset( $settings['resources']['remove_admin_toolbar'] ) && $settings['resources']['remove_admin_toolbar'] ) {
                if ( function_exists( 'add_filter' ) ) {
                    \add_filter( 'show_admin_bar', '__return_false' );
                }
            }
            
            // Remove dashboard widgets
            if ( isset( $settings['resources']['remove_dashboard_widgets'] ) && $settings['resources']['remove_dashboard_widgets'] ) {
                if ( function_exists( 'remove_meta_box' ) ) {
                    \remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
                    \remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
                    \remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
                    \remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
                    \remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
                    \remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
                    \remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
                    \remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
                }
            }
        }
    }
    
    /**
     * Disable REST API
     *
     * @param mixed $result Authentication result
     * @return mixed Modified authentication result
     */
    public function disable_rest_api( $result ) {
        if ( function_exists( 'is_user_logged_in' ) && ! \is_user_logged_in() ) {
            if ( class_exists( 'WP_Error' ) ) {
                return new \WP_Error( 'rest_not_logged_in', \__( 'REST API is disabled for non-authenticated users', \WPCA_TEXT_DOMAIN ), array( 'status' => 401 ) );
            }
        }
        return $result;
    }
    
    /**
     * Get resource usage statistics
     *
     * @return array Resource usage statistics
     */
    public function get_resource_usage(): array {
        $usage = array();
        
        // Get memory usage
        $usage['memory'] = array(
            'current' => \size_format( \memory_get_usage() ),
            'peak' => \size_format( \memory_get_peak_usage() ),
            'limit' => \ini_get( 'memory_limit' )
        );
        
        // Get PHP version
        $usage['php_version'] = PHP_VERSION;
        
        // Get WordPress version
        if ( defined( '\WP_VERSION' ) ) {
            $usage['wp_version'] = \WP_VERSION;
        }
        
        // Get active plugins count
        if ( function_exists( 'get_option' ) ) {
            $active_plugins = \get_option( 'active_plugins', array() );
            $usage['active_plugins_count'] = count( $active_plugins );
        }
        
        return $usage;
    }
    
    /**
     * Get resource optimization status
     *
     * @return array Resource optimization status
     */
    public function get_optimization_status(): array {
        $settings = \wpca_get_settings();
        
        return array(
            'emojis_disabled' => isset( $settings['resources']['disable_emojis'] ) ? $settings['resources']['disable_emojis'] : false,
            'embed_disabled' => isset( $settings['resources']['disable_embed'] ) ? $settings['resources']['disable_embed'] : false,
            'rest_api_disabled' => isset( $settings['resources']['disable_rest_api'] ) ? $settings['resources']['disable_rest_api'] : false,
            'heartbeat_disabled' => isset( $settings['resources']['disable_heartbeat'] ) ? $settings['resources']['disable_heartbeat'] : false,
            'admin_toolbar_removed' => isset( $settings['resources']['remove_admin_toolbar'] ) ? $settings['resources']['remove_admin_toolbar'] : false,
            'dashboard_widgets_removed' => isset( $settings['resources']['remove_dashboard_widgets'] ) ? $settings['resources']['remove_dashboard_widgets'] : false,
        );
    }
}
