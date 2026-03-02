<?php
/**
 * WPCleanAdmin Security Settings Fields Class
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

class Security_Settings_Fields {
    
    /**
     * Render hide WordPress version field
     */
    public static function render_hide_wp_version_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $hide_wp_version = isset( $settings['security']['hide_wp_version'] ) ? $settings['security']['hide_wp_version'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[security][hide_wp_version]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $hide_wp_version, 1, false ) : ( $hide_wp_version ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_hide_wp_version"> ' . \__( 'Hide WordPress version information.', $text_domain ) . '</label>';
    }
    
    /**
     * Render disable XML-RPC field
     */
    public static function render_disable_xmlrpc_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $disable_xmlrpc = isset( $settings['security']['disable_xmlrpc'] ) ? $settings['security']['disable_xmlrpc'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[security][disable_xmlrpc]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $disable_xmlrpc, 1, false ) : ( $disable_xmlrpc ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_disable_xmlrpc"> ' . \__( 'Disable XML-RPC functionality.', $text_domain ) . '</label>';
    }
    
    /**
     * Render restrict REST API field
     */
    public static function render_restrict_rest_api_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $restrict_rest_api = isset( $settings['security']['restrict_rest_api'] ) ? $settings['security']['restrict_rest_api'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[security][restrict_rest_api]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $restrict_rest_api, 1, false ) : ( $restrict_rest_api ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_restrict_rest_api"> ' . \__( 'Restrict REST API access to authenticated users only.', $text_domain ) . '</label>';
    }
    
    /**
     * Render restrict admin access field
     */
    public static function render_restrict_admin_access_field() {
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
