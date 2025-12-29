<?php
/**
 * WPCleanAdmin Database Settings Class
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
 * Database_Settings class
 */
class Database_Settings {
    
    /**
     * Singleton instance
     *
     * @var Database_Settings
     */
    private static ?Database_Settings $instance = null;
    
    /**
     * Get singleton instance
     *
     * @return Database_Settings
     */
    public static function getInstance(): Database_Settings {
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
     * Initialize the database settings module
     */
    public function init(): void {
        // Register settings
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'admin_init', array( $this, 'register_settings' ) );
        }
    }
    
    /**
     * Register database settings
     */
    public function register_settings(): void {
        // Register database settings section
        if ( function_exists( 'add_settings_section' ) ) {
            \add_settings_section(
                'wpca_database_settings',
                \__( 'Database Settings', WPCA_TEXT_DOMAIN ),
                array( $this, 'settings_section_callback' ),
                'wpca_settings'
            );
        }
        
        // Register database optimization setting
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_optimize_database',
                \__( 'Optimize Database', WPCA_TEXT_DOMAIN ),
                array( $this, 'optimize_database_field_callback' ),
                'wpca_settings',
                'wpca_database_settings'
            );
            
            // Register clean transients setting
            \add_settings_field(
                'wpca_clean_transients',
                \__( 'Clean Transients', WPCA_TEXT_DOMAIN ),
                array( $this, 'clean_transients_field_callback' ),
                'wpca_settings',
                'wpca_database_settings'
            );
            
            // Register database backup setting
            \add_settings_field(
                'wpca_enable_backups',
                \__( 'Enable Database Backups', WPCA_TEXT_DOMAIN ),
                array( $this, 'enable_backups_field_callback' ),
                'wpca_settings',
                'wpca_database_settings'
            );
        }
        
        // Register settings
        if ( function_exists( 'register_setting' ) ) {
            \register_setting( 'wpca_settings', 'wpca_database_settings' );
        }
    }
    
    /**
     * Settings section callback
     */
    public function settings_section_callback(): void {
        echo '<p>' . \__( 'Configure database optimization and maintenance settings.', WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    /**
     * Optimize database field callback
     */
    public function optimize_database_field_callback(): void {
        $settings = $this->get_settings();
        $checked = isset( $settings['optimize_database'] ) && $settings['optimize_database'] ? 'checked' : '';
        echo '<input type="checkbox" name="wpca_database_settings[optimize_database]" id="wpca_optimize_database" value="1" ' . $checked . '>';
        echo '<label for="wpca_optimize_database">' . \__( 'Enable automatic database optimization', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Clean transients field callback
     */
    public function clean_transients_field_callback(): void {
        $settings = $this->get_settings();
        $checked = isset( $settings['clean_transients'] ) && $settings['clean_transients'] ? 'checked' : '';
        echo '<input type="checkbox" name="wpca_database_settings[clean_transients]" id="wpca_clean_transients" value="1" ' . $checked . '>';
        echo '<label for="wpca_clean_transients">' . \__( 'Clean expired transients automatically', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Enable backups field callback
     */
    public function enable_backups_field_callback(): void {
        $settings = $this->get_settings();
        $checked = isset( $settings['enable_backups'] ) && $settings['enable_backups'] ? 'checked' : '';
        echo '<input type="checkbox" name="wpca_database_settings[enable_backups]" id="wpca_enable_backups" value="1" ' . $checked . '>';
        echo '<label for="wpca_enable_backups">' . \__( 'Create database backups before optimization', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Get database settings
     *
     * @return array Database settings
     */
    public function get_settings(): array {
        $settings = ( function_exists( 'get_option' ) ? \get_option( 'wpca_database_settings', array() ) : array() );
        
        $default_settings = array(
            'optimize_database' => 1,
            'clean_transients' => 1,
            'enable_backups' => 0
        );
        
        return ( function_exists( '\wp_parse_args' ) ? \wp_parse_args( $settings, $default_settings ) : array_merge( $default_settings, $settings ) );
    }
    
    /**
     * Save database settings
     *
     * @param array $settings Settings to save
     * @return bool Save result
     */
    public function save_settings( array $settings ): bool {
        return ( function_exists( 'update_option' ) ? \update_option( 'wpca_database_settings', $settings ) : false );
    }
    
    /**
     * Reset database settings
     *
     * @return bool Reset result
     */
    public function reset_settings(): bool {
        return ( function_exists( 'delete_option' ) ? \delete_option( 'wpca_database_settings' ) : false );
    }
}