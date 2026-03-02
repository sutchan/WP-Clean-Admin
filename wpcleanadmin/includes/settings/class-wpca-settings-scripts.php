<?php
/**
 * WPCleanAdmin Settings Scripts Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @update_date 2026-01-28
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */

namespace WPCleanAdmin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Settings_Scripts {
    
    /**
     * Enqueue scripts and styles
     *
     * @param string $hook Current admin page hook
     */
    public static function enqueue_scripts( $hook ) {
        if ( \strpos( $hook, 'wp-clean-admin' ) === false ) {
            return;
        }
        
        $plugin_url = defined( 'WPCA_PLUGIN_URL' ) ? WPCA_PLUGIN_URL : '';
        $plugin_version = defined( 'WPCA_VERSION' ) ? WPCA_VERSION : '1.8.0';
        
        if ( function_exists( 'wp_enqueue_style' ) ) {
            \wp_enqueue_style(
                'wpca-admin',
                $plugin_url . 'assets/css/wpca-admin.css',
                array(),
                $plugin_version
            );
        }
        
        if ( function_exists( 'wp_enqueue_script' ) ) {
            \wp_enqueue_script(
                'wpca-main',
                $plugin_url . 'assets/js/wpca-main.js',
                array( 'jquery' ),
                $plugin_version,
                true
            );
        }
    }
}
