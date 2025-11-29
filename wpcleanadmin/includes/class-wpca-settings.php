<?php

/**
 * Settings management for WPCleanAdmin plugin
 * 
 * @file wpcleanadmin/includes/class-wpca-settings.php
 * @version 1.7.15
 * @updated 2025-11-28
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * WPCA_Settings class
 * Manages plugin settings and configuration
 */
class WPCA_Settings {
    
    /**
     * Settings array
     * 
     * @var array
     */
    private $settings;
    
    /**
     * Default settings
     * 
     * @var array
     */
    private $default_settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->default_settings = $this->get_default_settings();
        $this->settings = $this->load_settings();
        $this->init();
    }
    
    /**
     * Initialize the settings class
     */
    private function init() {
        // Register hooks
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Get default settings
     * 
     * @return array Default settings array
     */
    private function get_default_settings() {
        return array(
            'version'             => WPCA_VERSION,
            'menu_order'          => array(),
            'submenu_order'       => array(),
            'menu_toggles'        => array(),
            'dashboard_widgets'   => array(),
            'login_style'         => 'default',
            'custom_admin_bar'    => 0,
            'disable_help_tabs'   => 0,
            'cleanup_header'      => 0,
            'minify_admin_assets' => 0
        );
    }
    
    /**
     * Load settings from database
     * 
     * @return array Settings array
     */
    private function load_settings() {
        $settings = get_option('wpca_settings');
        
        // If settings don't exist, use defaults
        if (! $settings || ! is_array($settings)) {
            $settings = $this->default_settings;
            $this->save_settings($settings);
        } else {
            // Merge with defaults to ensure all keys exist
            $settings = array_merge($this->default_settings, $settings);
            
            // Update version if needed
            if (isset($settings['version']) && $settings['version'] !== WPCA_VERSION) {
                $settings['version'] = WPCA_VERSION;
                $this->save_settings($settings);
            }
        }
        
        return $settings;
    }
    
    /**
     * Save settings to database
     * 
     * @param array $settings Settings array to save
     * @return bool True if settings were saved successfully
     */
    private function save_settings($settings) {
        return update_option('wpca_settings', $settings);
    }
    
    /**
     * Get a specific setting
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Setting value
     */
    public function get_setting($key, $default = null) {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }
    
    /**
     * Set a specific setting
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool True if setting was saved successfully
     */
    public function set_setting($key, $value) {
        $this->settings[$key] = $value;
        return $this->save_settings($this->settings);
    }
    
    /**
     * Get all settings
     * 
     * @return array All settings
     */
    public function get_all_settings() {
        return $this->settings;
    }
    
    /**
     * Reset settings to defaults
     * 
     * @return bool True if settings were reset successfully
     */
    public function reset_settings() {
        $this->settings = $this->default_settings;
        return $this->save_settings($this->settings);
    }
    
    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_menu_page(
            __('WP Clean Admin', 'wp-clean-admin'),
            __('Clean Admin', 'wp-clean-admin'),
            'manage_options',
            'wp-clean-admin',
            array($this, 'render_settings_page'),
            'dashicons-admin-generic',
            20
        );
        
        // Add submenu pages for different settings sections
        add_submenu_page(
            'wp-clean-admin',
            __('General Settings', 'wp-clean-admin'),
            __('General', 'wp-clean-admin'),
            'manage_options',
            'wp-clean-admin',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'wp-clean-admin',
            __('Menu Settings', 'wp-clean-admin'),
            __('Menu', 'wp-clean-admin'),
            'manage_options',
            'wp-clean-admin-menu',
            array($this, 'render_menu_settings_page')
        );
        
        add_submenu_page(
            'wp-clean-admin',
            __('Dashboard Settings', 'wp-clean-admin'),
            __('Dashboard', 'wp-clean-admin'),
            'manage_options',
            'wp-clean-admin-dashboard',
            array($this, 'render_dashboard_settings_page')
        );
        
        add_submenu_page(
            'wp-clean-admin',
            __('Login Settings', 'wp-clean-admin'),
            __('Login', 'wp-clean-admin'),
            'manage_options',
            'wp-clean-admin-login',
            array($this, 'render_login_settings_page')
        );
    }
    
    /**
     * Register settings with WordPress
     */
    public function register_settings() {
        register_setting(
            'wpca_settings_group',
            'wpca_settings',
            array($this, 'validate_settings')
        );
        
        // Add settings sections
        add_settings_section(
            'wpca_general_section',
            __('General Settings', 'wp-clean-admin'),
            array($this, 'render_general_section'),
            'wp-clean-admin'
        );
        
        // Add settings fields
        add_settings_field(
            'wpca_cleanup_header',
            __('Cleanup Header', 'wp-clean-admin'),
            array($this, 'render_cleanup_header_field'),
            'wp-clean-admin',
            'wpca_general_section'
        );
        
        add_settings_field(
            'wpca_disable_help_tabs',
            __('Disable Help Tabs', 'wp-clean-admin'),
            array($this, 'render_disable_help_tabs_field'),
            'wp-clean-admin',
            'wpca_general_section'
        );
        
        add_settings_field(
            'wpca_custom_admin_bar',
            __('Custom Admin Bar', 'wp-clean-admin'),
            array($this, 'render_custom_admin_bar_field'),
            'wp-clean-admin',
            'wpca_general_section'
        );
        
        add_settings_field(
            'wpca_minify_admin_assets',
            __('Minify Admin Assets', 'wp-clean-admin'),
            array($this, 'render_minify_admin_assets_field'),
            'wp-clean-admin',
            'wpca_general_section'
        );
    }
    
    /**
     * Validate settings before saving
     * 
     * @param array $input Input settings array
     * @return array Validated settings array
     */
    public function validate_settings($input) {
        $validated = array();
        
        // Validate each setting
        $validated['version'] = WPCA_VERSION;
        
        // Menu order
        $validated['menu_order'] = isset($input['menu_order']) && is_array($input['menu_order']) ? $input['menu_order'] : array();
        
        // Submenu order
        $validated['submenu_order'] = isset($input['submenu_order']) && is_array($input['submenu_order']) ? $input['submenu_order'] : array();
        
        // Menu toggles
        $validated['menu_toggles'] = isset($input['menu_toggles']) && is_array($input['menu_toggles']) ? $input['menu_toggles'] : array();
        
        // Dashboard widgets
        $validated['dashboard_widgets'] = isset($input['dashboard_widgets']) && is_array($input['dashboard_widgets']) ? $input['dashboard_widgets'] : array();
        
        // Login style
        $validated['login_style'] = isset($input['login_style']) && is_string($input['login_style']) ? sanitize_text_field($input['login_style']) : 'default';
        
        // Boolean settings
        $validated['custom_admin_bar'] = isset($input['custom_admin_bar']) ? (int) $input['custom_admin_bar'] : 0;
        $validated['disable_help_tabs'] = isset($input['disable_help_tabs']) ? (int) $input['disable_help_tabs'] : 0;
        $validated['cleanup_header'] = isset($input['cleanup_header']) ? (int) $input['cleanup_header'] : 0;
        $validated['minify_admin_assets'] = isset($input['minify_admin_assets']) ? (int) $input['minify_admin_assets'] : 0;
        
        return $validated;
    }
    
    /**
     * Render general settings section
     */
    public function render_general_section() {
        echo '<p>' . __('Configure general settings for WP Clean Admin.', 'wp-clean-admin') . '</p>';
    }
    
    /**
     * Render cleanup header field
     */
    public function render_cleanup_header_field() {
        $value = $this->get_setting('cleanup_header');
        echo '<input type="checkbox" name="wpca_settings[cleanup_header]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_settings[cleanup_header]"> ' . __('Clean up WordPress header by removing unnecessary meta tags and links.', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render disable help tabs field
     */
    public function render_disable_help_tabs_field() {
        $value = $this->get_setting('disable_help_tabs');
        echo '<input type="checkbox" name="wpca_settings[disable_help_tabs]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_settings[disable_help_tabs]"> ' . __('Disable help tabs in the WordPress admin interface.', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render custom admin bar field
     */
    public function render_custom_admin_bar_field() {
        $value = $this->get_setting('custom_admin_bar');
        echo '<input type="checkbox" name="wpca_settings[custom_admin_bar]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_settings[custom_admin_bar]"> ' . __('Enable custom admin bar modifications.', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render minify admin assets field
     */
    public function render_minify_admin_assets_field() {
        $value = $this->get_setting('minify_admin_assets');
        echo '<input type="checkbox" name="wpca_settings[minify_admin_assets]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_settings[minify_admin_assets]"> ' . __('Minify admin CSS and JavaScript files for improved performance.', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render main settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('WP Clean Admin Settings', 'wp-clean-admin'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wpca_settings_group');
                do_settings_sections('wp-clean-admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render menu settings page
     */
    public function render_menu_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Menu Settings', 'wp-clean-admin'); ?></h1>
            <p><?php _e('Menu customization features will be available soon.', 'wp-clean-admin'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Render dashboard settings page
     */
    public function render_dashboard_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Dashboard Settings', 'wp-clean-admin'); ?></h1>
            <p><?php _e('Dashboard customization features will be available soon.', 'wp-clean-admin'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Render login settings page
     */
    public function render_login_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Login Settings', 'wp-clean-admin'); ?></h1>
            <p><?php _e('Login customization features will be available soon.', 'wp-clean-admin'); ?></p>
        </div>
        <?php
    }
}