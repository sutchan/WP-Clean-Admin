<?php
/**
 * WPCleanAdmin Resources Class
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
 * Resources class
 */
class Resources {
    
    /**
     * Singleton instance
     *
     * @var Resources
     */
    private static $instance;
    
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
    public function init() {
        // Add resources hooks
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'wp_enqueue_scripts', array( $this, 'optimize_frontend_resources' ), 999 );
            \add_action( 'admin_enqueue_scripts', array( $this, 'optimize_admin_resources' ), 999 );
        }
    }
    
    /**
     * Optimize frontend resources
     */
    public function optimize_frontend_resources() {
        // Load settings
        $settings = wpca_get_settings();
        
        // Apply frontend resource optimizations based on settings
        if ( isset( $settings['resources'] ) ) {
            // Disable emojis
            if ( isset( $settings['resources']['disable_emojis'] ) && $settings['resources']['disable_emojis'] ) {
                if ( function_exists( 'remove_action' ) && function_exists( 'remove_filter' ) ) {
                    \remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
                    \remove_action( 'wp_print_styles', 'print_emoji_styles' );
                    \remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
                    \remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
                    \remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
                }
            }
            
            // Disable WordPress embeds
            if ( isset( $settings['resources']['disable_embeds'] ) && $settings['resources']['disable_embeds'] ) {
                if ( function_exists( 'remove_action' ) ) {
                    \remove_action( 'wp_head', 'wp_oembed_add_host_js' );
                    \remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
                }
            }
            
            // Disable WordPress version
            if ( isset( $settings['resources']['disable_version'] ) && $settings['resources']['disable_version'] ) {
                if ( function_exists( 'remove_action' ) ) {
                    \remove_action( 'wp_head', 'wp_generator' );
                }
            }
            
            // Disable RSS feeds
            if ( isset( $settings['resources']['disable_rss'] ) && $settings['resources']['disable_rss'] ) {
                if ( function_exists( 'remove_action' ) ) {
                    \remove_action( 'wp_head', 'feed_links', 2 );
                    \remove_action( 'wp_head', 'feed_links_extra', 3 );
                }
            }
            
            // Disable REST API
            if ( isset( $settings['resources']['disable_rest_api'] ) && $settings['resources']['disable_rest_api'] ) {
                if ( function_exists( 'remove_action' ) ) {
                    \remove_action( 'wp_head', 'rest_output_link_wp_head' );
                }
            }
        }
    }
    
    /**
     * Optimize admin resources
     */
    public function optimize_admin_resources() {
        // Load settings
        $settings = wpca_get_settings();
        
        // Apply admin resource optimizations based on settings
        if ( isset( $settings['resources'] ) && isset( $settings['resources']['optimize_admin_resources'] ) && $settings['resources']['optimize_admin_resources'] ) {
            // Remove unnecessary admin scripts and styles
            if ( function_exists( 'add_action' ) ) {
                \add_action( 'admin_init', array( $this, 'remove_unnecessary_admin_resources' ) );
            }
        }
    }
    
    /**
     * Remove unnecessary admin resources
     */
    public function remove_unnecessary_admin_resources() {
        // Remove WordPress welcome panel
        if ( function_exists( 'remove_action' ) ) {
            \remove_action( 'welcome_panel', 'wp_welcome_panel' );
        }
        
        // Remove unnecessary dashboard widgets
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
    
    /**
     * Get resources statistics
     *
     * @return array Resources statistics
     */
    public function get_resources_stats() {
        $stats = array();
        
        // Get enqueued scripts count
        global $wp_scripts;
        $stats['enqueued_scripts'] = count( $wp_scripts->queue );
        
        // Get enqueued styles count
        global $wp_styles;
        $stats['enqueued_styles'] = count( $wp_styles->queue );
        
        // Get total scripts count
        $stats['total_scripts'] = count( $wp_scripts->registered );
        
        // Get total styles count
        $stats['total_styles'] = count( $wp_styles->registered );
        
        return $stats;
    }
    
    /**
     * Get resource details
     *
     * @param string $type Resource type (scripts or styles)
     * @return array Resource details
     */
    public function get_resource_details( $type = 'scripts' ) {
        $details = array();
        
        if ( $type === 'scripts' ) {
            // Get script details
            global $wp_scripts;
            
            foreach ( $wp_scripts->registered as $handle => $script ) {
                $details[] = array(
                    'handle' => $handle,
                    'src' => $script->src,
                    'deps' => $script->deps,
                    'version' => $script->ver,
                    'in_footer' => $script->args,
                    'enqueued' => in_array( $handle, $wp_scripts->queue )
                );
            }
        } else if ( $type === 'styles' ) {
            // Get style details
            global $wp_styles;
            
            foreach ( $wp_styles->registered as $handle => $style ) {
                $details[] = array(
                    'handle' => $handle,
                    'src' => $style->src,
                    'deps' => $style->deps,
                    'version' => $style->ver,
                    'media' => $style->args,
                    'enqueued' => in_array( $handle, $wp_styles->queue )
                );
            }
        }
        
        return $details;
    }
    
    /**
     * Optimize resources
     *
     * @param array $options Optimization options
     * @return array Optimization results
     */
    public function optimize_resources( $options = array() ) {
        $results = array(
            'success' => true,
            'message' => \__( 'Resources optimized successfully', WPCA_TEXT_DOMAIN ),
            'optimized' => array()
        );
        
        // Set default options
        $default_options = array(
            'minify' => true,
            'combine' => false,
            'defer' => false,
            'async' => false
        );
        
        $options = ( function_exists( 'wp_parse_args' ) ? \wp_parse_args( $options, $default_options ) : array_merge( $default_options, $options ) );
        
        // Apply resource optimizations based on options
        if ( $options['minify'] ) {
            // Add minification hooks
            if ( function_exists( 'add_filter' ) ) {
                \add_filter( 'style_loader_tag', array( $this, 'minify_css' ) );
                \add_filter( 'script_loader_tag', array( $this, 'minify_js' ) );
                $results['optimized']['minify'] = true;
            }
        }
        
        if ( $options['defer'] ) {
            // Add defer attribute to scripts
            if ( function_exists( 'add_filter' ) ) {
                \add_filter( 'script_loader_tag', array( $this, 'add_defer_attribute' ), 10, 2 );
                $results['optimized']['defer'] = true;
            }
        }
        
        if ( $options['async'] ) {
            // Add async attribute to scripts
            if ( function_exists( 'add_filter' ) ) {
                \add_filter( 'script_loader_tag', array( $this, 'add_async_attribute' ), 10, 2 );
                $results['optimized']['async'] = true;
            }
        }
        
        return $results;
    }
    
    /**
     * Minify CSS
     *
     * @param string $tag CSS tag
     * @return string Modified tag
     */
    public function minify_css( $tag ) {
        // This is a placeholder for actual CSS minification
        return $tag;
    }
    
    /**
     * Minify JS
     *
     * @param string $tag JS tag
     * @return string Modified tag
     */
    public function minify_js( $tag ) {
        // This is a placeholder for actual JS minification
        return $tag;
    }
    
    /**
     * Add defer attribute to scripts
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @return string Modified tag
     */
    public function add_defer_attribute( $tag, $handle ) {
        // Add defer attribute to all scripts except jQuery
        if ( 'jquery' !== $handle ) {
            return str_replace( ' src', ' defer src', $tag );
        }
        return $tag;
    }
    
    /**
     * Add async attribute to scripts
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @return string Modified tag
     */
    public function add_async_attribute( $tag, $handle ) {
        // Add async attribute to all scripts except jQuery
        if ( 'jquery' !== $handle ) {
            return str_replace( ' src', ' async src', $tag );
        }
        return $tag;
    }
    
    /**
     * Disable resource
     *
     * @param string $type Resource type (scripts or styles)
     * @param string $handle Resource handle
     * @return array Disable result
     */
    public function disable_resource( $type, $handle ) {
        $results = array(
            'success' => true,
            'message' => \__( 'Resource disabled successfully', WPCA_TEXT_DOMAIN )
        );
        
        // Disable resource based on type
        if ( $type === 'scripts' ) {
            if ( function_exists( 'wp_deregister_script' ) && function_exists( 'wp_dequeue_script' ) ) {
                \wp_deregister_script( $handle );
                \wp_dequeue_script( $handle );
            }
        } else if ( $type === 'styles' ) {
            if ( function_exists( 'wp_deregister_style' ) && function_exists( 'wp_dequeue_style' ) ) {
                \wp_deregister_style( $handle );
                \wp_dequeue_style( $handle );
            }
        } else {
            $results['success'] = false;
            $results['message'] = \__( 'Invalid resource type', WPCA_TEXT_DOMAIN );
        }
        
        return $results;
    }
    
    /**
     * Enable resource
     *
     * @param string $type Resource type (scripts or styles)
     * @param string $handle Resource handle
     * @return array Enable result
     */
    public function enable_resource( $type, $handle ) {
        $results = array(
            'success' => true,
            'message' => \__( 'Resource enabled successfully', WPCA_TEXT_DOMAIN )
        );
        
        // Enable resource based on type
        if ( $type === 'scripts' ) {
            if ( function_exists( 'wp_enqueue_script' ) ) {
                \wp_enqueue_script( $handle );
            }
        } else if ( $type === 'styles' ) {
            if ( function_exists( 'wp_enqueue_style' ) ) {
                \wp_enqueue_style( $handle );
            }
        } else {
            $results['success'] = false;
            $results['message'] = \__( 'Invalid resource type', WPCA_TEXT_DOMAIN );
        }
        
        return $results;
    }
}