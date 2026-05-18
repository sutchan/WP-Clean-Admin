<?php
/**
 * WPCleanAdmin Diagnostics Settings Fields Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.8.0
 */

namespace WPCleanAdmin\Settings\Fields;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Diagnostics_Settings_Fields {
    
    public static function render_enable_diagnostics_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $enable_diagnostics = isset( $settings['diagnostics']['enable_diagnostics'] ) ? $settings['diagnostics']['enable_diagnostics'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[diagnostics][enable_diagnostics]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $enable_diagnostics, 1, false ) : ( $enable_diagnostics ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_enable_diagnostics"> ' . \__( 'Enable site health diagnostics.', $text_domain ) . '</label>';
    }
    
    public static function render_auto_run_diagnostics_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $auto_run_diagnostics = isset( $settings['diagnostics']['auto_run_diagnostics'] ) ? $settings['diagnostics']['auto_run_diagnostics'] : 0;
        
        echo '<input type="checkbox" name="wpca_settings[diagnostics][auto_run_diagnostics]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $auto_run_diagnostics, 1, false ) : ( $auto_run_diagnostics ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_auto_run_diagnostics"> ' . \__( 'Run diagnostics automatically on admin dashboard load.', $text_domain ) . '</label>';
    }
    
    public static function render_show_warnings_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $show_warnings = isset( $settings['diagnostics']['show_warnings'] ) ? $settings['diagnostics']['show_warnings'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[diagnostics][show_warnings]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $show_warnings, 1, false ) : ( $show_warnings ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_show_warnings"> ' . \__( 'Show warning notifications for detected issues.', $text_domain ) . '</label>';
    }
    
    public static function render_severity_filter_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $severity_filter = isset( $settings['diagnostics']['severity_filter'] ) ? $settings['diagnostics']['severity_filter'] : 'all';
        
        $options = array(
            'all' => __( 'All Severities', $text_domain ),
            'critical' => __( 'Critical Only', $text_domain ),
            'high' => __( 'High & Critical', $text_domain ),
            'medium' => __( 'Medium & Higher', $text_domain )
        );
        
        echo '<select name="wpca_settings[diagnostics][severity_filter]" id="wpca_severity_filter">';
        foreach ( $options as $value => $label ) {
            $selected = ( function_exists( 'selected' ) ? \selected( $severity_filter, $value, false ) : ( $severity_filter === $value ? 'selected="selected"' : '' ) );
            echo '<option value="' . \esc_attr( $value ) . '" ' . $selected . '>' . \esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }
}