<?php
/**
 * WP Clean Admin - AJAX Handler Class
 * 
 * @package WPCleanAdmin
 * @subpackage AJAX
 * @since 1.0.0
 */

/**
 * AJAX handler class for WP Clean Admin
 * 
 * Handles all AJAX requests for the plugin, including menu toggling, 
 * settings management, dashboard widget updates, and more.
 */
class WPCA_Ajax {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize hooks
        $this->init_hooks();
    }
    
    /**
     * Initialize AJAX hooks
     */
    public function init_hooks() {
        // Public AJAX actions (available to both logged in and non-logged in users)
        add_action( 'wp_ajax_nopriv_wpca_get_public_data', array( $this, 'get_public_data' ) );
        
        // Admin AJAX actions (only available to logged in users with proper permissions)
        add_action( 'wp_ajax_wpca_toggle_menu', array( $this, 'toggle_menu' ) );
        add_action( 'wp_ajax_wpca_update_menu_order', array( $this, 'update_menu_order' ) );
        add_action( 'wp_ajax_wpca_reset_settings', array( $this, 'reset_settings' ) );
        add_action( 'wp_ajax_wpca_save_settings', array( $this, 'save_settings' ) );
        add_action( 'wp_ajax_wpca_get_settings', array( $this, 'get_settings' ) );
        add_action( 'wp_ajax_wpca_update_dashboard_widgets', array( $this, 'update_dashboard_widgets' ) );
    }
    
    /**
     * Validate AJAX request
     * @param string $action - The action name to validate
     * @return bool - True if valid, false otherwise
     */
    protected function validate_ajax_request( $action ) {
        // Directly use WordPress native functions for validation
        if (!function_exists('wp_doing_ajax') || !wp_doing_ajax() ||
            !function_exists('check_ajax_referer') || !check_ajax_referer('wpca_admin_nonce', 'nonce', false) ||
            !function_exists('current_user_can') || !current_user_can('manage_options')) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Toggle menu visibility
     */
    public function toggle_menu() {
        if ( ! $this->validate_ajax_request( 'toggle_menu' ) ) {
            return;
        }
        
        if ( ! isset( $_POST['slug'] ) || ! isset( $_POST['state'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Missing required parameters', 'wp-clean-admin' ) ) );
            return;
        }
        
        $slug = sanitize_text_field( $_POST['slug'] );
        $state = intval( $_POST['state'] );
        
        // Get current settings
        $settings = get_option( 'wpca_settings', array() );
        
        // Ensure menu_toggles array exists
        if ( ! isset( $settings['menu_toggles'] ) || ! is_array( $settings['menu_toggles'] ) ) {
            $settings['menu_toggles'] = array();
        }
        
        // Update menu visibility
        $settings['menu_toggles'][$slug] = $state;
        
        // Save updated settings
        update_option( 'wpca_settings', $settings );
        
        wp_send_json_success( array( 'message' => __( 'Menu visibility updated', 'wp-clean-admin' ) ) );
    }
    
    /**
     * Update menu order
     */
    public function update_menu_order() {
        if ( ! $this->validate_ajax_request( 'update_menu_order' ) ) {
            return;
        }
        
        if ( ! isset( $_POST['menu_order'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Missing menu order data', 'wp-clean-admin' ) ) );
            return;
        }
        
        // Get and sanitize menu order data
        $menu_order = json_decode( stripslashes( $_POST['menu_order'] ), true );
        
        if ( ! is_array( $menu_order ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid menu order data format', 'wp-clean-admin' ) ) );
            return;
        }
        
        // Sanitize each menu slug
        $sanitized_order = array();
        foreach ( $menu_order as $slug ) {
            $sanitized_order[] = sanitize_text_field( $slug );
        }
        
        // Get current settings
        $settings = get_option( 'wpca_settings', array() );
        
        // Ensure settings is an array
        if ( ! is_array( $settings ) ) {
            $settings = array();
        }
        
        // Update menu order
        $settings['menu_order'] = $sanitized_order;
        
        // Save updated settings
        update_option( 'wpca_settings', $settings );
        
        wp_send_json_success( array( 'message' => __( 'Menu order updated', 'wp-clean-admin' ) ) );
    }
    
    /**
     * Reset settings to default
     */
    public function reset_settings() {
        if ( ! $this->validate_ajax_request( 'reset_settings' ) ) {
            return;
        }
        
        // Define default settings
        $default_settings = array(
            'version'             => WPCA_VERSION,
            'menu_order'          => array(),
            'menu_toggles'        => array(),
            'submenu_order'       => array(),
            'hidden_menus'        => array(),
            'dashboard_widgets'   => array(),
            'login_style'         => 'default',
            'custom_admin_bar'    => 0,
            'disable_help_tabs'   => 0,
            'cleanup_header'      => 0,
            'minify_admin_assets' => 0
        );
        
        // Reset settings
        update_option( 'wpca_settings', $default_settings );
        
        wp_send_json_success( array( 'message' => __( 'Settings reset to default', 'wp-clean-admin' ) ) );
    }
    
    /**
     * Save plugin settings
     */
    public function save_settings() {
        if ( ! $this->validate_ajax_request( 'save_settings' ) ) {
            return;
        }
        
        if ( ! isset( $_POST['settings'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Missing settings data', 'wp-clean-admin' ) ) );
            return;
        }
        
        // Get and sanitize settings data
        $new_settings = json_decode( stripslashes( $_POST['settings'] ), true );
        
        if ( ! is_array( $new_settings ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid settings data format', 'wp-clean-admin' ) ) );
            return;
        }
        
        // Get current settings to preserve any not included in the update
        $current_settings = get_option( 'wpca_settings', array() );
        
        // Ensure current settings is an array
        if ( ! is_array( $current_settings ) ) {
            $current_settings = array();
        }
        
        // Merge new settings with current settings
        $merged_settings = array_merge( $current_settings, $new_settings );
        
        // Ensure menu_toggles array exists if it's being set
        if ( isset( $merged_settings['menu_toggles'] ) && ! is_array( $merged_settings['menu_toggles'] ) ) {
            $merged_settings['menu_toggles'] = array();
        }
        
        // Save sanitized settings
        update_option( 'wpca_settings', $merged_settings );
        
        wp_send_json_success( array( 'message' => __( 'Settings saved successfully', 'wp-clean-admin' ) ) );
    }
    
    /**
     * Get current plugin settings
     */
    public function get_settings() {
        if ( ! $this->validate_ajax_request( 'get_settings' ) ) {
            return;
        }
        
        // Get current settings
        $settings = get_option( 'wpca_settings', array() );
        
        wp_send_json_success( array( 'settings' => $settings ) );
    }
    
    /**
     * Update dashboard widgets visibility
     */
    public function update_dashboard_widgets() {
        if ( ! $this->validate_ajax_request( 'update_dashboard_widgets' ) ) {
            return;
        }
        
        if ( ! isset( $_POST['widgets'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Missing widgets data', 'wp-clean-admin' ) ) );
            return;
        }
        
        // Get and sanitize widgets data
        $widgets = json_decode( stripslashes( $_POST['widgets'] ), true );
        
        if ( ! is_array( $widgets ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid widgets data format', 'wp-clean-admin' ) ) );
            return;
        }
        
        // Sanitize each widget ID
        $sanitized_widgets = array();
        foreach ( $widgets as $widget_id => $is_visible ) {
            $sanitized_id = sanitize_text_field( $widget_id );
            $sanitized_widgets[ $sanitized_id ] = (bool) $is_visible;
        }
        
        // Get current settings
        $settings = get_option( 'wpca_settings', array() );
        
        // Update dashboard widgets settings
        $settings['dashboard_widgets'] = $sanitized_widgets;
        
        // Save updated settings
        update_option( 'wpca_settings', $settings );
        
        wp_send_json_success( array( 'message' => __( 'Dashboard widgets updated', 'wp-clean-admin' ) ) );
    }
    
    /**
     * Get public data (available to non-logged in users)
     */
    public function get_public_data() {
        // This method doesn't require nonce verification since it's accessible to non-logged in users
        // Only return public data that's safe to expose
        
        $public_data = array(
            'version' => WPCA_VERSION,
            'has_custom_login' => false
        );
        
        // Check if there are custom login settings
        $settings = get_option( 'wpca_settings', array() );
        if ( isset( $settings['login_style'] ) && $settings['login_style'] !== 'default' ) {
            $public_data['has_custom_login'] = true;
        }
        
        wp_send_json_success( array( 'data' => $public_data ) );
    }
}