<?php
/**
 * WPCleanAdmin Cleanup Settings Fields Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @update_date 2026-01-28
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */

namespace WPCleanAdmin\Settings\Fields;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cleanup_Settings_Fields {
    
    /**
     * Render remove dashboard widgets field
     */
    public static function render_remove_dashboard_widgets_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $remove_dashboard_widgets = isset( $settings['menu']['remove_dashboard_widgets'] ) ? $settings['menu']['remove_dashboard_widgets'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[menu][remove_dashboard_widgets]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $remove_dashboard_widgets, 1, false ) : ( $remove_dashboard_widgets ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_remove_dashboard_widgets"> ' . \__( 'Remove unnecessary dashboard widgets.', $text_domain ) . '</label>';
    }
    
    /**
     * Render simplify admin menu field
     */
    public static function render_simplify_admin_menu_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $simplify_admin_menu = isset( $settings['menu']['simplify_admin_menu'] ) ? $settings['menu']['simplify_admin_menu'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[menu][simplify_admin_menu]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $simplify_admin_menu, 1, false ) : ( $simplify_admin_menu ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_simplify_admin_menu"> ' . \__( 'Simplify admin menu by removing unnecessary items.', $text_domain ) . '</label>';
    }
    
    /**
     * Render menu customization field
     */
    public static function render_menu_customization_field() {
        // Call menu customization render function from separate file
        if ( class_exists( '\WPCleanAdmin\Settings\Menu_Customization' ) ) {
            \WPCleanAdmin\Settings\Menu_Customization::render_menu_customization_field();
        }
    }
}
