<?php
/**
 * WPCleanAdmin Settings Fields Class
 *
 * @package WPCleanAdmin\Modules\Admin\Settings
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */

namespace WPCleanAdmin\Modules\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include menu customization settings
if ( file_exists( dirname( __DIR__ ) . '/../settings/menu-customization.php' ) ) {
    require_once dirname( __DIR__ ) . '/../settings/menu-customization.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/../../../../settings/menu-customization.php' ) ) {
    require_once dirname( __FILE__ ) . '/../../../../settings/menu-customization.php';
}

class Settings_Fields {
    
    private static $instance = null;
    
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    
    public function register_fields() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        
        // Register general settings fields
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_clean_admin_bar',
                \__( 'Clean Admin Bar', $text_domain ),
                array( $this, 'render_clean_admin_bar_field' ),
                'wp-clean-admin',
                'wpca_general_settings'
            );
            
            \add_settings_field(
                'wpca_remove_wp_logo',
                \__( 'Remove WordPress Logo', $text_domain ),
                array( $this, 'render_remove_wp_logo_field' ),
                'wp-clean-admin',
                'wpca_general_settings'
            );
        }
        
        // Register cleanup settings fields
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_remove_dashboard_widgets',
                \__( 'Remove Dashboard Widgets', $text_domain ),
                array( $this, 'render_remove_dashboard_widgets_field' ),
                'wp-clean-admin',
                'wpca_cleanup_settings'
            );
            
            \add_settings_field(
                'wpca_simplify_admin_menu',
                \__( 'Simplify Admin Menu', $text_domain ),
                array( $this, 'render_simplify_admin_menu_field' ),
                'wp-clean-admin',
                'wpca_cleanup_settings'
            );
            
            \add_settings_field(
                'wpca_menu_customization',
                \__( 'Menu Customization', $text_domain ),
                array( $this, 'render_menu_customization_field' ),
                'wp-clean-admin',
                'wpca_cleanup_settings'
            );
        }
        
        // Register performance settings fields
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_optimize_database',
                \__( 'Optimize Database', $text_domain ),
                array( $this, 'render_optimize_database_field' ),
                'wp-clean-admin',
                'wpca_performance_settings'
            );
            
            \add_settings_field(
                'wpca_clean_transients',
                \__( 'Clean Transients', $text_domain ),
                array( $this, 'render_clean_transients_field' ),
                'wp-clean-admin',
                'wpca_performance_settings'
            );
        }
        
        // Register security settings fields
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_hide_wp_version',
                \__( 'Hide WordPress Version', $text_domain ),
                array( $this, 'render_hide_wp_version_field' ),
                'wp-clean-admin',
                'wpca_security_settings'
            );
            
            \add_settings_field(
                'wpca_disable_xmlrpc',
                \__( 'Disable XML-RPC', $text_domain ),
                array( $this, 'render_disable_xmlrpc_field' ),
                'wp-clean-admin',
                'wpca_security_settings'
            );
            
            \add_settings_field(
                'wpca_restrict_rest_api',
                \__( 'Restrict REST API Access', $text_domain ),
                array( $this, 'render_restrict_rest_api_field' ),
                'wp-clean-admin',
                'wpca_security_settings'
            );
            
            \add_settings_field(
                'wpca_restrict_admin_access',
                \__( 'Restrict Admin Access', $text_domain ),
                array( $this, 'render_restrict_admin_access_field' ),
                'wp-clean-admin',
                'wpca_security_settings'
            );
        }
    }
    
    public function render_clean_admin_bar_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $clean_admin_bar = isset( $settings['general']['clean_admin_bar'] ) ? $settings['general']['clean_admin_bar'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[general][clean_admin_bar]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $clean_admin_bar, 1, false ) : ( $clean_admin_bar ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_clean_admin_bar"> ' . \__( 'Remove unnecessary items from the admin bar.', $text_domain ) . '</label>';
    }
    
    public function render_remove_wp_logo_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $remove_wp_logo = isset( $settings['general']['remove_wp_logo'] ) ? $settings['general']['remove_wp_logo'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[general][remove_wp_logo]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $remove_wp_logo, 1, false ) : ( $remove_wp_logo ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_remove_wp_logo"> ' . \__( 'Remove WordPress logo from admin bar.', $text_domain ) . '</label>';
    }
    
    public function render_remove_dashboard_widgets_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $remove_dashboard_widgets = isset( $settings['menu']['remove_dashboard_widgets'] ) ? $settings['menu']['remove_dashboard_widgets'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[menu][remove_dashboard_widgets]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $remove_dashboard_widgets, 1, false ) : ( $remove_dashboard_widgets ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_remove_dashboard_widgets"> ' . \__( 'Remove unnecessary dashboard widgets.', $text_domain ) . '</label>';
    }
    
    public function render_simplify_admin_menu_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $simplify_admin_menu = isset( $settings['menu']['simplify_admin_menu'] ) ? $settings['menu']['simplify_admin_menu'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[menu][simplify_admin_menu]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $simplify_admin_menu, 1, false ) : ( $simplify_admin_menu ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_simplify_admin_menu"> ' . \__( 'Simplify admin menu by removing unnecessary items.', $text_domain ) . '</label>';
    }
    
    public function render_menu_customization_field() {
        // Call menu customization render function from separate file
        if ( class_exists( '\WPCleanAdmin\Settings\Menu_Customization' ) ) {
            \WPCleanAdmin\Settings\Menu_Customization::render_menu_customization_field();
        }
    }
    
    public function render_optimize_database_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $optimize_database = isset( $settings['performance']['optimize_database'] ) ? $settings['performance']['optimize_database'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[performance][optimize_database]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $optimize_database, 1, false ) : ( $optimize_database ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_optimize_database"> ' . \__( 'Automatically optimize database tables.', $text_domain ) . '</label>';
    }
    
    public function render_clean_transients_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $clean_transients = isset( $settings['performance']['clean_transients'] ) ? $settings['performance']['clean_transients'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[performance][clean_transients]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $clean_transients, 1, false ) : ( $clean_transients ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_clean_transients"> ' . \__( 'Automatically clean expired transients.', $text_domain ) . '</label>';
    }
    
    public function render_hide_wp_version_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $hide_wp_version = isset( $settings['security']['hide_wp_version'] ) ? $settings['security']['hide_wp_version'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[security][hide_wp_version]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $hide_wp_version, 1, false ) : ( $hide_wp_version ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_hide_wp_version"> ' . \__( 'Hide WordPress version information.', $text_domain ) . '</label>';
    }
    
    public function render_disable_xmlrpc_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $disable_xmlrpc = isset( $settings['security']['disable_xmlrpc'] ) ? $settings['security']['disable_xmlrpc'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[security][disable_xmlrpc]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $disable_xmlrpc, 1, false ) : ( $disable_xmlrpc ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_disable_xmlrpc"> ' . \__( 'Disable XML-RPC functionality.', $text_domain ) . '</label>';
    }
    
    public function render_restrict_rest_api_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $restrict_rest_api = isset( $settings['security']['restrict_rest_api'] ) ? $settings['security']['restrict_rest_api'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[security][restrict_rest_api]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $restrict_rest_api, 1, false ) : ( $restrict_rest_api ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_restrict_rest_api"> ' . \__( 'Restrict REST API access to authenticated users only.', $text_domain ) . '</label>';
    }
    
    public function render_restrict_admin_access_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $restrict_admin_access = isset( $settings['security']['restrict_admin_access'] ) ? $settings['security']['restrict_admin_access'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[security][restrict_admin_access]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $restrict_admin_access, 1, false ) : ( $restrict_admin_access ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_restrict_admin_access"> ' . \__( 'Restrict admin area access to users with proper permissions.', $text_domain ) . '</label>';
    }
}