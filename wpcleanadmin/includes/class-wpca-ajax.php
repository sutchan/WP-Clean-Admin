<?php
/**
 * WP Clean Admin - AJAX Handler Class
 * Handles AJAX requests for the plugin
 */

/**
 * AJAX handler class for WP Clean Admin
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
        if ( function_exists( 'add_action' ) ) {
            // Public AJAX actions (available to both logged in and non-logged in users)
            if ( function_exists( 'add_action' ) ) {
if ( function_exists( 'add_action' ) ) {
    add_action( 'wp_ajax_nopriv_wpca_get_public_data', array( $this, 'get_public_data' ) );
}
            }
            
            // Admin AJAX actions (only available to logged in users with proper permissions)
            if ( function_exists( 'add_action' ) ) {
if ( function_exists( 'add_action' ) ) {
if ( function_exists( 'add_action' ) ) {
    add_action( 'wp_ajax_wpca_toggle_menu', array( $this, 'toggle_menu' ) );
}
}
            }
            add_action( 'wp_ajax_wpca_update_menu_order', array( $this, 'update_menu_order' ) );
            add_action( 'wp_ajax_wpca_reset_settings', array( $this, 'reset_settings' ) );
            add_action( 'wp_ajax_wpca_save_settings', array( $this, 'save_settings' ) );
            add_action( 'wp_ajax_wpca_get_settings', array( $this, 'get_settings' ) );
            add_action( 'wp_ajax_wpca_update_dashboard_widgets', array( $this, 'update_dashboard_widgets' ) );
        }
    }
    
    /**
     * Validate AJAX request
     * @param string $action - The action name to validate
     * @return bool - True if valid, false otherwise
     */
    protected function validate_ajax_request( $action ) {
        // Check if it's an AJAX request
        if ( function_exists( 'wp_doing_ajax' ) && ! wp_doing_ajax() ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid request' ), 400 );
            }
            return false;
        }
        
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ( function_exists( 'wp_verify_nonce' ) && ! wp_verify_nonce( $_POST['nonce'], 'wpca_admin_nonce' ) ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid nonce' ), 403 );
            }
            return false;
        }
        
        // Check user capabilities
        if ( function_exists( 'current_user_can' ) && ! current_user_can( 'manage_options' ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Insufficient permissions' ), 403 );
            }
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
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Missing required parameters' ) );
            }
            return;
        }
        
        $slug = function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $_POST['slug'] ) : filter_var( $_POST['slug'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
        $state = intval( $_POST['state'] );
        
        // Get current settings
        $settings = function_exists( 'get_option' ) ? get_option( 'wpca_settings', array() ) : array();
        
        if ( ! isset( $settings['hidden_menus'] ) ) {
            $settings['hidden_menus'] = array();
        }
        
        // Update menu visibility
        if ( $state === 0 ) {
            // Hide menu
            if ( ! in_array( $slug, $settings['hidden_menus'] ) ) {
                $settings['hidden_menus'][] = $slug;
            }
        } else {
            // Show menu
            $key = array_search( $slug, $settings['hidden_menus'] );
            if ( $key !== false ) {
                unset( $settings['hidden_menus'][$key] );
                $settings['hidden_menus'] = array_values( $settings['hidden_menus'] );
            }
        }
        
        // Save updated settings
        if ( function_exists( 'update_option' ) ) {
            update_option( 'wpca_settings', $settings );
        }
        
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( array( 'message' => 'Menu visibility updated' ) );
        }
    }
    
    /**
     * Update menu order
     */
    public function update_menu_order() {
        if ( ! $this->validate_ajax_request( 'update_menu_order' ) ) {
            return;
        }
        
        if ( ! isset( $_POST['menu_order'] ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Missing menu order data' ) );
            }
            return;
        }
        
        // Get and sanitize menu order data
        $menu_order = ( function_exists( 'json_decode' ) && function_exists( 'stripslashes' ) ) ? 
            json_decode( stripslashes( $_POST['menu_order'] ), true ) : array();
        
        if ( ! is_array( $menu_order ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid menu order data format' ) );
            }
            return;
        }
        
        // Sanitize each menu slug
        $sanitized_order = array();
        foreach ( $menu_order as $slug ) {
            $sanitized_order[] = function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $slug ) : filter_var( $slug, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
        }
        
        // Get current settings
        $settings = function_exists( 'get_option' ) ? get_option( 'wpca_settings', array() ) : array();
        
        // Update menu order
        $settings['menu_order'] = $sanitized_order;
        
        // Save updated settings
        if ( function_exists( 'update_option' ) ) {
            update_option( 'wpca_settings', $settings );
        }
        
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( array( 'message' => 'Menu order updated' ) );
        }
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
        if ( function_exists( 'update_option' ) ) {
            update_option( 'wpca_settings', $default_settings );
        }
        
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( array( 'message' => 'Settings reset to default' ) );
        }
    }
    
    /**
     * Save plugin settings
     */
    public function save_settings() {
        if ( ! $this->validate_ajax_request( 'save_settings' ) ) {
            return;
        }
        
        if ( ! isset( $_POST['settings'] ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Missing settings data' ) );
            }
            return;
        }
        
        // Get and sanitize settings data
        $new_settings = ( function_exists( 'json_decode' ) && function_exists( 'stripslashes' ) ) ? 
            json_decode( stripslashes( $_POST['settings'] ), true ) : array();
        
        if ( ! is_array( $new_settings ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid settings data format' ) );
            }
            return;
        }
        
        // Get current settings to preserve any not included in the update
        $current_settings = function_exists( 'get_option' ) ? get_option( 'wpca_settings', array() ) : array();
        
        // Merge new settings with current settings
        $merged_settings = array_merge( $current_settings, $new_settings );
        
        // Save sanitized settings
        if ( function_exists( 'update_option' ) ) {
            update_option( 'wpca_settings', $merged_settings );
        }
        
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( array( 'message' => 'Settings saved successfully' ) );
        }
    }
    
    /**
     * Get current plugin settings
     */
    public function get_settings() {
        if ( ! $this->validate_ajax_request( 'get_settings' ) ) {
            return;
        }
        
        // Get current settings
        $settings = function_exists( 'get_option' ) ? get_option( 'wpca_settings', array() ) : array();
        
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( array( 'settings' => $settings ) );
        }
    }
    
    /**
     * Update dashboard widgets visibility
     */
    public function update_dashboard_widgets() {
        if ( ! $this->validate_ajax_request( 'update_dashboard_widgets' ) ) {
            return;
        }
        
        if ( ! isset( $_POST['widgets'] ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Missing widgets data' ) );
            }
            return;
        }
        
        // Get and sanitize widgets data
        $widgets = ( function_exists( 'json_decode' ) && function_exists( 'stripslashes' ) ) ? 
            json_decode( stripslashes( $_POST['widgets'] ), true ) : array();
        
        if ( ! is_array( $widgets ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid widgets data format' ) );
            }
            return;
        }
        
        // Sanitize each widget ID
        $sanitized_widgets = array();
        foreach ( $widgets as $widget_id => $is_visible ) {
            $sanitized_id = function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $widget_id ) : filter_var( $widget_id, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
            $sanitized_widgets[ $sanitized_id ] = (bool) $is_visible;
        }
        
        // Get current settings
        $settings = function_exists( 'get_option' ) ? get_option( 'wpca_settings', array() ) : array();
        
        // Update dashboard widgets settings
        $settings['dashboard_widgets'] = $sanitized_widgets;
        
        // Save updated settings
        if ( function_exists( 'update_option' ) ) {
            update_option( 'wpca_settings', $settings );
        }
        
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( array( 'message' => 'Dashboard widgets updated' ) );
        }
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
        if ( function_exists( 'get_option' ) ) {
            $settings = get_option( 'wpca_settings', array() );
            if ( isset( $settings['login_style'] ) && $settings['login_style'] !== 'default' ) {
                $public_data['has_custom_login'] = true;
            }
        }
        
        if ( function_exists( 'wp_send_json_success' ) ) {
            wp_send_json_success( array( 'data' => $public_data ) );
        }
    }
}