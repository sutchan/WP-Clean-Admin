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

/**
 * Core class for WP Clean Admin plugin
 *
 * @package WPCleanAdmin
 */

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
    private static ?Core $instance = null;
    
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
        require_once WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php';
        
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
        // Load settings module
        Settings::getInstance();
        
        // Load dashboard module
        Dashboard::getInstance();
        
        // Load database module
        Database::getInstance();
        
        // Load performance module
        Performance::getInstance();
        
        // Load menu manager module
        Menu_Manager::getInstance();
        
        // Load menu customizer module
        Menu_Customizer::getInstance();
        
        // Load permissions module
        Permissions::getInstance();
        
        // Load user roles module
        User_Roles::getInstance();
        
        // Load login module
        Login::getInstance();
        
        // Load cleanup module
        Cleanup::getInstance();
        
        // Load resources module
        Resources::getInstance();
        
        // Load reset module
        Reset::getInstance();
        
        // Load AJAX module
        AJAX::getInstance();
        
        // Load i18n module
        i18n::getInstance();
        
        // Load error handler module
        Error_Handler::getInstance();
        
        // Load cache module
        Cache::getInstance();
        
        // Load extension API module
        Extension_API::getInstance();
    }
    
    /**
     * Plugin activation callback
     */
    public function activate() {
        // Set default settings
        $this->set_default_settings();
        
        // Flush rewrite rules
        \flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation callback
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Set default plugin settings
     */
    private function set_default_settings(): void {
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
