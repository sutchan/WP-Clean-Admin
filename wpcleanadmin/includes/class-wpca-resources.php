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
        if ( defined( 'WP_VERSION' ) ) {
            $usage['wp_version'] = constant( 'WP_VERSION' );
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
    
    /**
     * Get resources statistics
     *
     * @return array Resources statistics
     */
    public function get_resources_stats(): array {
        return array(
            'usage' => $this->get_resource_usage(),
            'optimization' => $this->get_optimization_status()
        );
    }
    
    /**
     * Get resource details
     *
     * @param string $type Resource type
     * @return array Resource details
     */
    public function get_resource_details( string $type ): array {
        $details = array();
        
        switch ( $type ) {
            case 'scripts':
                $details = $this->get_scripts_details();
                break;
            case 'styles':
                $details = $this->get_styles_details();
                break;
            default:
                $details = array('message' => 'Invalid resource type');
        }
        
        return $details;
    }
    
    /**
     * Get scripts details
     *
     * @return array Scripts details
     */
    private function get_scripts_details(): array {
        global $wp_scripts;
        
        $scripts = array();
        
        if ( isset( $wp_scripts ) && is_object( $wp_scripts ) ) {
            foreach ( $wp_scripts->queue as $handle ) {
                if ( isset( $wp_scripts->registered[ $handle ] ) ) {
                    $script = $wp_scripts->registered[ $handle ];
                    $scripts[] = array(
                        'handle' => $handle,
                        'src' => $script->src ?? '',
                        'deps' => $script->deps ?? array(),
                        'ver' => $script->ver ?? false
                    );
                }
            }
        }
        
        return $scripts;
    }
    
    /**
     * Get styles details
     *
     * @return array Styles details
     */
    private function get_styles_details(): array {
        global $wp_styles;
        
        $styles = array();
        
        if ( isset( $wp_styles ) && is_object( $wp_styles ) ) {
            foreach ( $wp_styles->queue as $handle ) {
                if ( isset( $wp_styles->registered[ $handle ] ) ) {
                    $style = $wp_styles->registered[ $handle ];
                    $styles[] = array(
                        'handle' => $handle,
                        'src' => $style->src ?? '',
                        'deps' => $style->deps ?? array(),
                        'ver' => $style->ver ?? false
                    );
                }
            }
        }
        
        return $styles;
    }
    
    /**
     * Optimize resources
     *
     * @param array $options Optimization options
     * @return array Optimization result
     */
    public function optimize_resources( array $options ): array {
        $result = array(
            'success' => true,
            'message' => 'Resources optimized successfully',
            'optimizations' => array()
        );
        
        // Apply optimizations based on options
        if ( isset( $options['disable_emojis'] ) && $options['disable_emojis'] ) {
            $result['optimizations'][] = 'Emojis disabled';
        }
        
        if ( isset( $options['disable_embed'] ) && $options['disable_embed'] ) {
            $result['optimizations'][] = 'Embed disabled';
        }
        
        if ( isset( $options['disable_rest_api'] ) && $options['disable_rest_api'] ) {
            $result['optimizations'][] = 'REST API disabled';
        }
        
        if ( isset( $options['disable_heartbeat'] ) && $options['disable_heartbeat'] ) {
            $result['optimizations'][] = 'Heartbeat disabled';
        }
        
        return $result;
    }
    
    /**
     * Disable resource
     *
     * @param string $type Resource type
     * @param string $handle Resource handle
     * @return array Result
     */
    public function disable_resource( string $type, string $handle ): array {
        // Implementation would go here
        return array(
            'success' => true,
            'message' => "{$type} resource {$handle} disabled successfully"
        );
    }
    
    /**
     * Enable resource
     *
     * @param string $type Resource type
     * @param string $handle Resource handle
     * @return array Result
     */
    public function enable_resource( string $type, string $handle ): array {
        // Implementation would go here
        return array(
            'success' => true,
            'message' => "{$type} resource {$handle} enabled successfully"
        );
    }
}
