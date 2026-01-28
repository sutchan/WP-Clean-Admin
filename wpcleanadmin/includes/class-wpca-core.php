<?php
/**
 * WPCleanAdmin Core Class
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
 * Core class
 */
class Core {
    
    /**
     * Singleton instance
     *
     * @var Core
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     *
     * @return Core
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
     * Initialize the plugin
     */
    public function init() {
        // Load core functions
        require_once \WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php';
        
        // Add security headers
        $this->add_security_headers();
        
        // Initialize modules
        $this->init_modules();
    }
    
    /**
     * Add security HTTP headers
     */
    private function add_security_headers() {
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'send_headers', array( $this, 'send_security_headers' ) );
        }
    }
    
    /**
     * Send security HTTP headers
     */
    public function send_security_headers() {
        // X-Frame-Options: Prevent clickjacking
        if ( ! \headers_sent() ) {
            \header( 'X-Frame-Options: SAMEORIGIN' );
        }
        
        // X-XSS-Protection: Enable browser XSS filter
        if ( ! \headers_sent() ) {
            \header( 'X-XSS-Protection: 1; mode=block' );
        }
        
        // X-Content-Type-Options: Prevent MIME type sniffing
        if ( ! \headers_sent() ) {
            \header( 'X-Content-Type-Options: nosniff' );
        }
        
        // Referrer-Policy: Control referrer information
        if ( ! \headers_sent() ) {
            \header( 'Referrer-Policy: strict-origin-when-cross-origin' );
        }
        
        // Content-Security-Policy: Restrict resource loading (basic configuration)
        if ( ! \headers_sent() ) {
            \header( "Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';" );
        }
    }
    
    /**
     * Initialize all plugin modules
     */
    private function init_modules() {
        // Module initialization order matters - load core modules first
        $modules = array(
            'Settings',
            'Dashboard',
            'Database',
            'Performance',
            'Menu_Manager',
            'Menu_Customizer',
            'Permissions',
            'User_Roles',
            'Login',
            'Cleanup',
            'Resources',
            'Reset',
            'AJAX',
            'i18n',
            'Error_Handler',
            'Cache',
            'Extension_API'
        );
        
        foreach ( $modules as $module ) {
            $class_name = 'WPCleanAdmin\\' . $module;
            
            try {
                if ( class_exists( $class_name ) ) {
                    // Check if getInstance method exists
                    if ( method_exists( $class_name, 'getInstance' ) ) {
                        $class_name::getInstance();
                    }
                }
            } catch ( \Exception $e ) {
                // Silently catch exceptions during module initialization
                // This prevents the entire plugin from failing if one module has an issue
                // Log error for debugging if needed
                if ( function_exists( 'error_log' ) ) {
                    \error_log( 'WPCA Module Init Error: ' . $e->getMessage() );
                }
            }
        }
    }
    
    /**
     * Plugin activation callback
     */
    public function activate() {
        // Set default settings
        $this->set_default_settings();
        
        // Flush rewrite rules
        if ( function_exists( 'flush_rewrite_rules' ) ) {
            \flush_rewrite_rules();
        }
    }
    
    /**
     * Plugin deactivation callback
     */
    public function deactivate() {
        // Flush rewrite rules
        if ( function_exists( 'flush_rewrite_rules' ) ) {
            \flush_rewrite_rules();
        }
    }
    
    /**
     * Set default plugin settings
     */
    private function set_default_settings() {
        $default_settings = array(
            'general' => array(
                'clean_admin_bar' => 1,
                'clean_dashboard' => 1,
                'remove_wp_logo' => 1,
            ),
            'performance' => array(
                'optimize_database' => 1,
                'clean_transients' => 1,
                'disable_emojis' => 1,
            ),
            'menu' => array(
                'remove_dashboard_widgets' => 1,
                'simplify_admin_menu' => 1,
            ),
        );
        
        // Update settings if they don't exist
        $current_settings = ( function_exists( 'get_option' ) ? \get_option( 'wpca_settings', array() ) : array() );
        $updated_settings = ( function_exists( 'wp_parse_args' ) ? \wp_parse_args( $current_settings, $default_settings ) : array_merge( $default_settings, $current_settings ) );
        
        if ( function_exists( 'update_option' ) ) {
            \update_option( 'wpca_settings', $updated_settings );
        }
    }
}

