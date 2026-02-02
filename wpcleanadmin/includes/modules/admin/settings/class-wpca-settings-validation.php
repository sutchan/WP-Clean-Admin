<?php
/**
 * WPCleanAdmin Settings Validation Class
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

class Settings_Validation {
    
    private static $instance = null;
    
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    
    public function validate( $input ) {
        $validated = array();
        
        // Validate general settings
        if ( isset( $input['general'] ) ) {
            $validated['general'] = array();
            
            // Validate clean admin bar setting
            $validated['general']['clean_admin_bar'] = isset( $input['general']['clean_admin_bar'] ) ? 1 : 0;
            
            // Validate remove WordPress logo setting
            $validated['general']['remove_wp_logo'] = isset( $input['general']['remove_wp_logo'] ) ? 1 : 0;
        }
        
        // Validate menu settings
        if ( isset( $input['menu'] ) ) {
            $validated['menu'] = array();
            
            // Validate remove dashboard widgets setting
            $validated['menu']['remove_dashboard_widgets'] = isset( $input['menu']['remove_dashboard_widgets'] ) ? 1 : 0;
            
            // Validate simplify admin menu setting
            $validated['menu']['simplify_admin_menu'] = isset( $input['menu']['simplify_admin_menu'] ) ? 1 : 0;
            
            // Validate menu items setting
            if ( isset( $input['menu']['menu_items'] ) ) {
                $validated['menu']['menu_items'] = $input['menu']['menu_items'];
            }
            
            // Validate menu order setting
            if ( isset( $input['menu']['menu_order'] ) ) {
                $validated['menu']['menu_order'] = $input['menu']['menu_order'];
            }
        }
        
        // Validate performance settings
        if ( isset( $input['performance'] ) ) {
            $validated['performance'] = array();
            
            // Validate optimize database setting
            $validated['performance']['optimize_database'] = isset( $input['performance']['optimize_database'] ) ? 1 : 0;
            
            // Validate clean transients setting
            $validated['performance']['clean_transients'] = isset( $input['performance']['clean_transients'] ) ? 1 : 0;
        }
        
        // Validate security settings
        if ( isset( $input['security'] ) ) {
            $validated['security'] = array();
            
            // Validate hide WordPress version setting
            $validated['security']['hide_wp_version'] = isset( $input['security']['hide_wp_version'] ) ? 1 : 0;
            
            // Validate disable XML-RPC setting
            $validated['security']['disable_xmlrpc'] = isset( $input['security']['disable_xmlrpc'] ) ? 1 : 0;
            
            // Validate restrict REST API setting
            $validated['security']['restrict_rest_api'] = isset( $input['security']['restrict_rest_api'] ) ? 1 : 0;
            
            // Validate restrict admin access setting
            $validated['security']['restrict_admin_access'] = isset( $input['security']['restrict_admin_access'] ) ? 1 : 0;
        }
        
        return $validated;
    }
}