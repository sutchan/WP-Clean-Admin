<?php
/**
 * WPCleanAdmin Performance Settings Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin;

/**
 * Performance Settings class for WP Clean Admin plugin
 *
 * @package WPCleanAdmin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Performance_Settings class
 */
class Performance_Settings {
    
    /**
     * Singleton instance
     *
     * @var Performance_Settings
     */
    private static ?Performance_Settings $instance = null;
    
    /**
     * Get singleton instance
     *
     * @return Performance_Settings
     */
    public static function getInstance(): Performance_Settings {
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
     * Initialize the performance settings module
     */
    public function init(): void {
        // Register settings
        \add_action( 'admin_init', array( $this, 'register_settings' ) );
    }
    
    /**
     * Register performance settings
     */
    public function register_settings(): void {
        // Register performance settings section
        \add_settings_section(
            'wpca_performance_settings',
            \__( 'Performance Settings', WPCA_TEXT_DOMAIN ),
            array( $this, 'settings_section_callback' ),
            'wpca_settings'
        );
        
        // Register disable emojis setting
        \add_settings_field(
            'wpca_disable_emojis',
            \__( 'Disable Emojis', WPCA_TEXT_DOMAIN ),
            array( $this, 'disable_emojis_field_callback' ),
            'wpca_settings',
            'wpca_performance_settings'
        );
        
        // Register disable embeds setting
        \add_settings_field(
            'wpca_disable_embeds',
            \__( 'Disable Embeds', WPCA_TEXT_DOMAIN ),
            array( $this, 'disable_embeds_field_callback' ),
            'wpca_settings',
            'wpca_performance_settings'
        );
        
        // Register disable XML-RPC setting
        \add_settings_field(
            'wpca_disable_xmlrpc',
            \__( 'Disable XML-RPC', WPCA_TEXT_DOMAIN ),
            array( $this, 'disable_xmlrpc_field_callback' ),
            'wpca_settings',
            'wpca_performance_settings'
        );
        
        // Register disable REST API setting
        \add_settings_field(
            'wpca_disable_rest_api',
            \__( 'Disable REST API', WPCA_TEXT_DOMAIN ),
            array( $this, 'disable_rest_api_field_callback' ),
            'wpca_settings',
            'wpca_performance_settings'
        );
        
        // Register disable jQuery migrate setting
        \add_settings_field(
            'wpca_disable_jquery_migrate',
            \__( 'Disable jQuery Migrate', WPCA_TEXT_DOMAIN ),
            array( $this, 'disable_jquery_migrate_field_callback' ),
            'wpca_settings',
            'wpca_performance_settings'
        );
        
        // Register lazy load images setting
        \add_settings_field(
            'wpca_lazy_load_images',
            \__( 'Lazy Load Images', WPCA_TEXT_DOMAIN ),
            array( $this, 'lazy_load_images_field_callback' ),
            'wpca_settings',
            'wpca_performance_settings'
        );
        
        // Register settings
        \register_setting( 'wpca_settings', 'wpca_performance_settings' );
    }
    
    /**
     * Settings section callback
     */
    public function settings_section_callback(): void {
        echo '<p>' . \__( 'Configure performance optimization settings to improve your website speed.', WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    /**
     * Disable emojis field callback
     */
    public function disable_emojis_field_callback(): void {
        $settings = $this->get_settings();
        $checked = isset( $settings['disable_emojis'] ) && $settings['disable_emojis'] ? 'checked' : '';
        echo '<input type="checkbox" name="wpca_performance_settings[disable_emojis]" id="wpca_disable_emojis" value="1" ' . $checked . '>';
        echo '<label for="wpca_disable_emojis">' . \__( 'Disable WordPress emojis', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Disable embeds field callback
     */
    public function disable_embeds_field_callback(): void {
        $settings = $this->get_settings();
        $checked = isset( $settings['disable_embeds'] ) && $settings['disable_embeds'] ? 'checked' : '';
        echo '<input type="checkbox" name="wpca_performance_settings[disable_embeds]" id="wpca_disable_embeds" value="1" ' . $checked . '>';
        echo '<label for="wpca_disable_embeds">' . \__( 'Disable WordPress embeds', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Disable XML-RPC field callback
     */
    public function disable_xmlrpc_field_callback(): void {
        $settings = $this->get_settings();
        $checked = isset( $settings['disable_xmlrpc'] ) && $settings['disable_xmlrpc'] ? 'checked' : '';
        echo '<input type="checkbox" name="wpca_performance_settings[disable_xmlrpc]" id="wpca_disable_xmlrpc" value="1" ' . $checked . '>';
        echo '<label for="wpca_disable_xmlrpc">' . \__( 'Disable XML-RPC', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Disable REST API field callback
     */
    public function disable_rest_api_field_callback(): void {
        $settings = $this->get_settings();
        $checked = isset( $settings['disable_rest_api'] ) && $settings['disable_rest_api'] ? 'checked' : '';
        echo '<input type="checkbox" name="wpca_performance_settings[disable_rest_api]" id="wpca_disable_rest_api" value="1" ' . $checked . '>';
        echo '<label for="wpca_disable_rest_api">' . \__( 'Disable REST API', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Disable jQuery migrate field callback
     */
    public function disable_jquery_migrate_field_callback(): void {
        $settings = $this->get_settings();
        $checked = isset( $settings['disable_jquery_migrate'] ) && $settings['disable_jquery_migrate'] ? 'checked' : '';
        echo '<input type="checkbox" name="wpca_performance_settings[disable_jquery_migrate]" id="wpca_disable_jquery_migrate" value="1" ' . $checked . '>';
        echo '<label for="wpca_disable_jquery_migrate">' . \__( 'Disable jQuery Migrate', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Lazy load images field callback
     */
    public function lazy_load_images_field_callback(): void {
        $settings = $this->get_settings();
        $checked = isset( $settings['lazy_load_images'] ) && $settings['lazy_load_images'] ? 'checked' : '';
        echo '<input type="checkbox" name="wpca_performance_settings[lazy_load_images]" id="wpca_lazy_load_images" value="1" ' . $checked . '>';
        echo '<label for="wpca_lazy_load_images">' . \__( 'Enable lazy loading for images', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Get performance settings
     *
     * @return array Performance settings
     */
    public function get_settings(): array {
        $settings = \get_option( 'wpca_performance_settings', array() );
        
        $default_settings = array(
            'disable_emojis' => 1,
            'disable_embeds' => 0,
            'disable_xmlrpc' => 0,
            'disable_rest_api' => 0,
            'disable_jquery_migrate' => 1,
            'lazy_load_images' => 1
        );
        
        return function_exists( '\wp_parse_args' ) ? \wp_parse_args( $settings, $default_settings ) : array_merge( $default_settings, $settings );
    }
    
    /**
     * Save performance settings
     *
     * @param array $settings Settings to save
     * @return bool Save result
     */
    public function save_settings( array $settings ): bool {
        return \update_option( 'wpca_performance_settings', $settings );
    }
    
    /**
     * Reset performance settings
     *
     * @return bool Reset result
     */
    public function reset_settings(): bool {
        return \delete_option( 'wpca_performance_settings' );
    }
}
