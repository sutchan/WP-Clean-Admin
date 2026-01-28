<?php
/**
 * WPCleanAdmin Performance Class
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
 * Performance class
 */
class Performance {
    
    /**
     * Singleton instance
     *
     * @var Performance
     */
    private static $instance = null;
    
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
    public function init(): void {
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
     *
     * @uses remove_action() To remove emoji detection actions
     * @uses remove_action() To remove emoji styles
     * @return void
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
    public function disable_xmlrpc(): void {
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
     * @param mixed $result Authentication result
     * @return mixed Modified authentication result
     */
    public function disable_rest_api_authentication( $result ) {
        if ( function_exists( 'is_user_logged_in' ) && ! is_user_logged_in() ) {
            if ( class_exists( 'WP_Error' ) ) {
                return new \WP_Error( 'rest_not_logged_in', \__( 'REST API is disabled for non-authenticated users', WPCA_TEXT_DOMAIN ), array( 'status' => 401 ) );
            }
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
        if ( function_exists( 'wp_next_scheduled' ) && function_exists( 'wp_schedule_event' ) ) {
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
        if ( function_exists( 'wp_next_scheduled' ) && function_exists( 'wp_schedule_event' ) ) {
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
        if ( function_exists( 'wp_cache_flush' ) ) {
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
        $stats['query_count'] = function_exists( 'get_num_queries' ) ? \get_num_queries() : 0;
        
        // Get page load time
        $stats['load_time'] = function_exists( 'timer_stop' ) ? \timer_stop( 0, 3 ) . 's' : '0.000s';
        
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
        // Check if minification is enabled
        $settings = wpca_get_settings();
        if ( ! isset( $settings['performance']['minify_css'] ) || ! $settings['performance']['minify_css'] ) {
            return $tag;
        }
        
        // Extract CSS URL from tag
        if ( preg_match( '/href=["\']([^"\']+\.css[^"\']*)["\']/', $tag, $matches ) ) {
            $css_url = $matches[1];
            $minified_url = $this->get_minified_css_url( $css_url );
            
            if ( $minified_url ) {
                $tag = str_replace( $css_url, $minified_url, $tag );
            }
        }
        
        return $tag;
    }
    
    /**
     * Get minified CSS URL
     *
     * @param string $original_url Original CSS URL
     * @return string Minified CSS URL or original if not found
     */
    private function get_minified_css_url( $original_url ) {
        $parsed_url = parse_url( $original_url );
        
        if ( ! isset( $parsed_url['path'] ) ) {
            return $original_url;
        }
        
        $path = $parsed_url['path'];
        $dirname = dirname( $path );
        $basename = basename( $path );
        $minified_basename = preg_replace( '/\.css$/i', '.min.css', $basename );
        $minified_path = $dirname . '/' . $minified_basename;
        
        // Check if minified file exists
        $document_root = isset( $_SERVER['DOCUMENT_ROOT'] ) ? $_SERVER['DOCUMENT_ROOT'] : ABSPATH;
        $full_path = $document_root . ltrim( $minified_path, '/' );
        
        if ( file_exists( $full_path ) ) {
            // Build the full URL
            $site_url = function_exists( '\site_url' ) ? \site_url() : get_option( 'siteurl' );
            $minified_url = $site_url . $minified_path;
            
            // Preserve query string
            if ( isset( $parsed_url['query'] ) ) {
                $minified_url .= '?' . $parsed_url['query'];
            }
            
            return $minified_url;
        }
        
        return $original_url;
    }
    
    /**
     * Minify CSS content
     *
     * @param string $css CSS content
     * @return string Minified CSS content
     */
    public function minify_css_content( $css ) {
        if ( empty( $css ) ) {
            return $css;
        }
        
        // Remove comments
        $css = preg_replace( '/\/\*[\s\S]*?\*\//', '', $css );
        
        // Remove whitespace
        $css = preg_replace( '/\s+/', ' ', $css );
        
        // Remove space around special characters
        $css = preg_replace( '/\s*([{}:;,>+~])\s*/', '$1', $css );
        
        // Remove last semicolon before }
        $css = preg_replace( '/;}/', '}', $css );
        
        // Trim
        $css = trim( $css );
        
        return $css;
    }
    
    /**
     * Minify JS
     *
     * @param string $tag JS tag
     * @return string Modified JS tag
     */
    public function minify_js( $tag ) {
        // Check if minification is enabled
        $settings = wpca_get_settings();
        if ( ! isset( $settings['performance']['minify_js'] ) || ! $settings['performance']['minify_js'] ) {
            return $tag;
        }
        
        // Extract JS URL from tag
        if ( preg_match( '/src=["\']([^"\']+\.js[^"\']*)["\']/', $tag, $matches ) ) {
            $js_url = $matches[1];
            $minified_url = $this->get_minified_js_url( $js_url );
            
            if ( $minified_url ) {
                $tag = str_replace( $js_url, $minified_url, $tag );
            }
        }
        
        return $tag;
    }
    
    /**
     * Get minified JS URL
     *
     * @param string $original_url Original JS URL
     * @return string Minified JS URL or original if not found
     */
    private function get_minified_js_url( $original_url ) {
        $parsed_url = parse_url( $original_url );
        
        if ( ! isset( $parsed_url['path'] ) ) {
            return $original_url;
        }
        
        $path = $parsed_url['path'];
        $dirname = dirname( $path );
        $basename = basename( $path );
        $minified_basename = preg_replace( '/\.js$/i', '.min.js', $basename );
        $minified_path = $dirname . '/' . $minified_basename;
        
        // Check if minified file exists
        $document_root = isset( $_SERVER['DOCUMENT_ROOT'] ) ? $_SERVER['DOCUMENT_ROOT'] : ABSPATH;
        $full_path = $document_root . ltrim( $minified_path, '/' );
        
        if ( file_exists( $full_path ) ) {
            // Build the full URL
            $site_url = function_exists( '\site_url' ) ? \site_url() : get_option( 'siteurl' );
            $minified_url = $site_url . $minified_path;
            
            // Preserve query string
            if ( isset( $parsed_url['query'] ) ) {
                $minified_url .= '?' . $parsed_url['query'];
            }
            
            return $minified_url;
        }
        
        return $original_url;
    }
    
    /**
     * Minify JS content
     *
     * Basic JS minification - removes comments and extra whitespace
     * For production use, consider using a library like JSMin or Terser
     *
     * @param string $js JS content
     * @return string Minified JS content
     */
    public function minify_js_content( $js ) {
        if ( empty( $js ) ) {
            return $js;
        }
        
        // Remove single-line comments (but not http:// URLs)
        $js = preg_replace( '/\/\/(?![a-zA-Z]+:\/\/)(.*?)[\r\n]/', '$1', $js );
        
        // Remove multi-line comments
        $js = preg_replace( '/\/\*[\s\S]*?\*\//', '', $js );
        
        // Remove extra whitespace
        $js = preg_replace( '/\s+/', ' ', $js );
        
        // Remove space around operators
        $js = preg_replace( '/\s*([{}();,.=!<>+\-*\/&|?%:~])\s*/', '$1', $js );
        
        // Trim
        $js = trim( $js );
        
        return $js;
    }
    
    /**
     * Combine CSS files
     *
     * @param string $uri CSS URI
     * @return string Modified CSS URI
     */
    public function combine_css( $uri ) {
        // Check if combination is enabled
        $settings = wpca_get_settings();
        if ( ! isset( $settings['performance']['combine_css'] ) || ! $settings['performance']['combine_css'] ) {
            return $uri;
        }
        
        return $uri;
    }
    
    /**
     * Combine multiple CSS files into one
     *
     * @param array $css_urls Array of CSS URLs to combine
     * @return string|null Combined CSS file URL or null on failure
     */
    public function combine_css_files( $css_urls = array() ) {
        if ( empty( $css_urls ) || ! is_array( $css_urls ) ) {
            return null;
        }
        
        $combined_content = '';
        $content_hash = '';
        
        foreach ( $css_urls as $url ) {
            if ( function_exists( 'wp_remote_get' ) && function_exists( 'is_wp_error' ) && function_exists( 'wp_remote_retrieve_response_code' ) && function_exists( 'wp_remote_retrieve_body' ) ) {
                $response = \wp_remote_get( $url );
                if ( ! \is_wp_error( $response ) && \wp_remote_retrieve_response_code( $response ) === 200 ) {
                    $content = \wp_remote_retrieve_body( $response );
                    // Minify the content first
                    $content = $this->minify_css_content( $content );
                    $combined_content .= $content . "\n";
                    $content_hash = md5( $content_hash . $content );
                }
            }
        }
        
        if ( empty( $combined_content ) ) {
            return null;
        }
        
        // Create combined filename
        $hash = md5( implode( ',', $css_urls ) );
        $combined_filename = 'wpca-combined-' . $hash . '.css';
        if ( function_exists( 'wp_upload_dir' ) ) {
            $upload_dir = \wp_upload_dir();
            $combined_dir = $upload_dir['basedir'] . '/wpca-cache';
            
            // Create directory if it doesn't exist
            if ( ! file_exists( $combined_dir ) ) {
                if ( function_exists( 'wp_mkdir_p' ) ) {
                    \wp_mkdir_p( $combined_dir );
                }
            }
        } else {
            return null;
        }
        
        $combined_path = $combined_dir . '/' . $combined_filename;
        
        // Write combined content to file
        $result = file_put_contents( $combined_path, $combined_content );
        
        if ( $result !== false ) {
            return $upload_dir['baseurl'] . '/wpca-cache/' . $combined_filename;
        }
        
        return null;
    }
    
    /**
     * Combine JS files
     *
     * @param string $uri JS URI
     * @return string Modified JS URI
     */
    public function combine_js( $uri ) {
        // Check if combination is enabled
        $settings = wpca_get_settings();
        if ( ! isset( $settings['performance']['combine_js'] ) || ! $settings['performance']['combine_js'] ) {
            return $uri;
        }
        
        return $uri;
    }
    
    /**
     * Combine multiple JS files into one
     *
     * @param array $js_urls Array of JS URLs to combine
     * @return string|null Combined JS file URL or null on failure
     */
    public function combine_js_files( $js_urls = array() ) {
        if ( empty( $js_urls ) || ! is_array( $js_urls ) ) {
            return null;
        }
        
        $combined_content = '';
        
        foreach ( $js_urls as $url ) {
            if ( function_exists( 'wp_remote_get' ) && function_exists( 'is_wp_error' ) && function_exists( 'wp_remote_retrieve_response_code' ) && function_exists( 'wp_remote_retrieve_body' ) ) {
                $response = \wp_remote_get( $url );
                if ( ! \is_wp_error( $response ) && \wp_remote_retrieve_response_code( $response ) === 200 ) {
                    $content = \wp_remote_retrieve_body( $response );
                    // Add semicolon if needed between files
                    if ( ! empty( $combined_content ) ) {
                        $content = ';' . trim( $content );
                    }
                    $combined_content .= $content . "\n";
                }
            }
        }
        
        if ( empty( $combined_content ) ) {
            return null;
        }
        
        // Create combined filename
        $hash = md5( implode( ',', $js_urls ) );
        $combined_filename = 'wpca-combined-' . $hash . '.js';
        if ( function_exists( 'wp_upload_dir' ) ) {
            $upload_dir = \wp_upload_dir();
            $combined_dir = $upload_dir['basedir'] . '/wpca-cache';
            
            // Create directory if it doesn't exist
            if ( ! file_exists( $combined_dir ) ) {
                if ( function_exists( 'wp_mkdir_p' ) ) {
                    \wp_mkdir_p( $combined_dir );
                }
            }
        } else {
            return null;
        }
        
        $combined_path = $combined_dir . '/' . $combined_filename;
        
        // Write combined content to file
        $result = file_put_contents( $combined_path, $combined_content );
        
        if ( $result !== false ) {
            return $upload_dir['baseurl'] . '/wpca-cache/' . $combined_filename;
        }
        
        return null;
    }
    
    /**
     * Resource preloading for admin pages
     *
     * Preloads critical resources to improve page load performance
     * Uses WordPress resource hints (preload, prefetch, prerender)
     *
     * @uses \add_filter() To add resource hints filter
     * @return void
     */
    public function enable_resource_preloading() {
        \add_filter( 'wp_resource_hints', array( $this, 'add_resource_hints' ), 10, 2 );
    }
    
    /**
     * Add resource hints for performance optimization
     *
     * @param array $hints Resource hints array
     * @param string $type Resource type (dns-prefetch, preconnect, preload, prerender)
     * @return array Modified resource hints
     */
    public function add_resource_hints( $hints, $type ) {
        $settings = wpca_get_settings();
        
        if ( ! isset( $settings['performance']['resource_preloading'] ) || ! $settings['performance']['resource_preloading'] ) {
            return $hints;
        }
        
        // Preload critical admin assets
        $preload_resources = array();
        if ( function_exists( 'includes_url' ) ) {
            $preload_resources = array(
                'admin-css' => array(
                    'href' => \includes_url( 'css/common.css' ),
                    'as' => 'style',
                    'crossorigin' => false,
                ),
                'admin-js' => array(
                    'href' => \includes_url( 'js/common.js' ),
                    'as' => 'script',
                    'crossorigin' => false,
                ),
            );
        }
        
        foreach ( $preload_resources as $id => $resource ) {
            $hints[] = array(
                'href' => $resource['href'],
                'as' => $resource['as'],
                'id' => $id,
                'crossorigin' => $resource['crossorigin'] ? 'anonymous' : null,
            );
        }
        
        return $hints;
    }
    
    /**
     * Preload specific resource
     *
     * @param string $url Resource URL to preload
     * @param string $as Resource type (style, script, font, image, etc.)
     * @param string $media Optional media attribute for styles
     * @return string Link tag for preloading
     */
    public function preload_resource( $url, $as = 'script', $media = '' ) {
        if ( empty( $url ) ) {
            return '';
        }
        
        $link_tag = '<link rel="preload" href="' . \esc_url( $url ) . '" as="' . \esc_attr( $as ) . '"';
        
        if ( ! empty( $media ) && $as === 'style' ) {
            $link_tag .= ' media="' . \esc_attr( $media ) . '"';
        }
        
        $link_tag .= ' />';
        
        return $link_tag;
    }
    
    /**
     * Add DNS prefetch for external resources
     *
     * @param array $domains Array of domains to prefetch
     * @return void
     */
    public function add_dns_prefetch( $domains = array() ) {
        if ( empty( $domains ) || ! is_array( $domains ) ) {
            return;
        }
        
        \add_filter( 'wp_resource_hints', function( $hints ) use ( $domains ) {
            foreach ( $domains as $domain ) {
                $hints[] = array(
                    'href' => $domain,
                    'as' => 'script',
                    'rel' => 'dns-prefetch',
                );
            }
            return $hints;
        }, 10, 1 );
    }
    
    /**
     * Add preconnect for external resources
     *
     * @param array $domains Array of domains to preconnect
     * @return void
     */
    public function add_preconnect( $domains = array() ) {
        if ( empty( $domains ) || ! is_array( $domains ) ) {
            return;
        }
        
        \add_filter( 'wp_resource_hints', function( $hints ) use ( $domains ) {
            foreach ( $domains as $domain ) {
                $hints[] = array(
                    'href' => $domain,
                    'as' => 'script',
                    'rel' => 'preconnect',
                );
            }
            return $hints;
        }, 10, 1 );
    }
    
    /**
     * Prerender specified URL
     *
     * @param string $url URL to prerender
     * @return string Link tag for prerendering
     */
    public function prerender_url( $url ) {
        if ( empty( $url ) ) {
            return '';
        }
        
        return '<link rel="prerender" href="' . \esc_url( $url ) . '" />';
    }
    
    /**
     * Prefetch specified URL
     *
     * @param string $url URL to prefetch
     * @return string Link tag for prefetching
     */
    public function prefetch_url( $url ) {
        if ( empty( $url ) ) {
            return '';
        }
        
        return '<link rel="prefetch" href="' . \esc_url( $url ) . '" />';
    }
    
    /**
     * Get preloading status
     *
     * @return array Preloading status information
     */
    public function get_preloading_status() {
        $settings = wpca_get_settings();
        
        return array(
            'enabled' => isset( $settings['performance']['resource_preloading'] ) ? $settings['performance']['resource_preloading'] : false,
            'preload_count' => 0,
            'dns_prefetch_count' => 0,
            'preconnect_count' => 0,
        );
    }
}
