<?php
/**
 * WPCleanAdmin General Settings Fields Class
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

class General_Settings_Fields {
    
    /**
     * Render clean admin bar field
     */
    public static function render_clean_admin_bar_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $clean_admin_bar = isset( $settings['general']['clean_admin_bar'] ) ? $settings['general']['clean_admin_bar'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[general][clean_admin_bar]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $clean_admin_bar, 1, false ) : ( $clean_admin_bar ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_clean_admin_bar"> ' . \__( 'Remove unnecessary items from the admin bar.', $text_domain ) . '</label>';
    }
    
    /**
     * Render remove WordPress logo field
     */
    public static function render_remove_wp_logo_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $remove_wp_logo = isset( $settings['general']['remove_wp_logo'] ) ? $settings['general']['remove_wp_logo'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[general][remove_wp_logo]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $remove_wp_logo, 1, false ) : ( $remove_wp_logo ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_remove_wp_logo"> ' . \__( 'Remove WordPress logo from admin bar.', $text_domain ) . '</label>';
    }
}
