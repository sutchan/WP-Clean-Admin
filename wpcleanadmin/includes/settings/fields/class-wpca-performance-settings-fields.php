<?php
/**
 * WPCleanAdmin Performance Settings Fields Class
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

class Performance_Settings_Fields {
    
    /**
     * Render optimize database field
     */
    public static function render_optimize_database_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $optimize_database = isset( $settings['performance']['optimize_database'] ) ? $settings['performance']['optimize_database'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[performance][optimize_database]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $optimize_database, 1, false ) : ( $optimize_database ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_optimize_database"> ' . \__( 'Automatically optimize database tables.', $text_domain ) . '</label>';
    }
    
    /**
     * Render clean transients field
     */
    public static function render_clean_transients_field() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $clean_transients = isset( $settings['performance']['clean_transients'] ) ? $settings['performance']['clean_transients'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[performance][clean_transients]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $clean_transients, 1, false ) : ( $clean_transients ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_clean_transients"> ' . \__( 'Automatically clean expired transients.', $text_domain ) . '</label>';
    }
}
