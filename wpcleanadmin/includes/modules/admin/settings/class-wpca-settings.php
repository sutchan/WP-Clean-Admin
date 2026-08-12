<?php
/**
 * WPCleanAdmin Settings Class
 *
 * @package WPCleanAdmin\Modules\Admin\Settings
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

require_once __DIR__ . '/class-wpca-settings-page.php';

namespace WPCleanAdmin\Modules\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Settings {
    
    /** @var self|null */
    private static $instance = null;
    
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    public function init() {
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
            \add_action( 'admin_init', array( $this, 'register_settings' ) );
            \add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        }
    }
    
    public function register_settings_page() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        if ( function_exists( 'add_options_page' ) ) {
            \add_options_page(
                \__( 'WP Clean Admin', $text_domain ),
                \__( 'Clean Admin', $text_domain ),
                'manage_options',
                'wp-clean-admin',
                array( $this, 'render_settings_page' )
            );
        }
    }
    
    public function register_settings() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        
        // Register settings sections
        $sections = array(
            'general' => array(
                'id' => 'wpca_general_settings',
                'title' => \__( 'General Settings', $text_domain ),
                'callback' => array( $this, 'render_general_settings_section' )
            ),
            'cleanup' => array(
                'id' => 'wpca_cleanup_settings',
                'title' => \__( 'Cleanup Settings', $text_domain ),
                'callback' => array( $this, 'render_cleanup_settings_section' )
            ),
            'performance' => array(
                'id' => 'wpca_performance_settings',
                'title' => \__( 'Performance Settings', $text_domain ),
                'callback' => array( $this, 'render_performance_settings_section' )
            ),
            'security' => array(
                'id' => 'wpca_security_settings',
                'title' => \__( 'Security Settings', $text_domain ),
                'callback' => array( $this, 'render_security_settings_section' )
            )
        );
        
        foreach ( $sections as $section ) {
            if ( function_exists( 'add_settings_section' ) ) {
                \add_settings_section(
                    $section['id'],
                    $section['title'],
                    $section['callback'],
                    'wp-clean-admin'
                );
            }
        }
        
        // Register settings fields
        $this->register_settings_fields();
        
        // Register setting
        if ( function_exists( 'register_setting' ) ) {
            \register_setting( 'wp-clean-admin', 'wpca_settings', array( $this, 'validate_settings' ) );
        }
    }
    
    private function register_settings_fields() {
        // Include settings fields
        if ( file_exists( dirname( __FILE__ ) . '/class-wpca-settings-fields.php' ) ) {
            require_once dirname( __FILE__ ) . '/class-wpca-settings-fields.php';
        }
        
        // Register fields using the fields class
        if ( class_exists( 'WPCleanAdmin\Modules\Admin\Settings\Settings_Fields' ) ) {
            $fields = \WPCleanAdmin\Modules\Admin\Settings\Settings_Fields::getInstance();
            $fields->register_fields();
        }
    }
    
    public function render_general_settings_section() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        echo '<p>' . \__( 'Configure general settings for WP Clean Admin plugin.', $text_domain ) . '</p>';
    }
    
    public function render_cleanup_settings_section() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        echo '<p>' . \__( 'Configure cleanup settings for WP Clean Admin plugin.', $text_domain ) . '</p>';
    }
    
    public function render_performance_settings_section() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        echo '<p>' . \__( 'Configure performance optimization settings for WP Clean Admin plugin.', $text_domain ) . '</p>';
    }
    
    public function render_security_settings_section() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        echo '<p>' . \__( 'Configure security settings for WP Clean Admin plugin.', $text_domain ) . '</p>';
    }
    
    public function render_settings_page() {
        $page = new Settings_Page();
        $page->render();
    }
    
    /**
     * 验证设置
     *
     * @param array|mixed $input 输入的设置数据
     * @return array|mixed 验证后的设置数据
     */
    public function validate_settings( $input ) {
        // Include validation
        if ( file_exists( dirname( __FILE__ ) . '/class-wpca-settings-validation.php' ) ) {
            require_once dirname( __FILE__ ) . '/class-wpca-settings-validation.php';
        }
        
        // Validate using the validation class
        if ( class_exists( 'WPCleanAdmin\Modules\Admin\Settings\Settings_Validation' ) ) {
            $validation = \WPCleanAdmin\Modules\Admin\Settings\Settings_Validation::getInstance();
            return $validation->validate( $input );
        }
        
        return $input;
    }
    
    public function enqueue_scripts( string $hook ) {
        if ( \strpos( $hook, 'wp-clean-admin' ) === false ) {
            return;
        }
        
        $plugin_url = defined( 'WPCA_PLUGIN_URL' ) ? WPCA_PLUGIN_URL : '';
        $plugin_version = defined( 'WPCA_VERSION' ) ? WPCA_VERSION : '1.8.0';
        
        if ( function_exists( 'wp_enqueue_style' ) ) {
            \wp_enqueue_style(
                'wpca-admin',
                $plugin_url . 'assets/css/wpca-admin.css',
                array(),
                $plugin_version
            );
        }
        
        if ( function_exists( 'wp_enqueue_script' ) ) {
            \wp_enqueue_script(
                'wpca-main',
                $plugin_url . 'assets/js/wpca-main.js',
                array( 'jquery' ),
                $plugin_version,
                true
            );
            
            // Enqueue settings-specific script from modular structure
            $settings_script_path = $plugin_url . 'includes/modules/admin/settings/assets/js/wpca-settings.js';
            \wp_enqueue_script(
                'wpca-settings',
                $settings_script_path,
                array( 'jquery', 'wpca-main' ),
                $plugin_version,
                true
            );
            
            // Localize script for translations
            if ( function_exists( 'wp_localize_script' ) ) {
                $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
                \wp_localize_script( 'wpca-settings', 'wpcaSettingsLocalize', array(
                    'savingText' => \__( 'Saving...', $text_domain ),
                    'successText' => \__( 'Settings saved successfully.', $text_domain ),
                    'errorText' => \__( 'Error saving settings.', $text_domain ),
                    'ajaxurl' => function_exists( 'admin_url' ) ? \admin_url( 'admin-ajax.php' ) : ''
                ) );
            }
        }
    }
}