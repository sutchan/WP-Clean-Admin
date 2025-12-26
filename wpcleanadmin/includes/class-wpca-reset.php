<?php
/**
 * WPCleanAdmin Reset Class
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Reset class
 */
class Reset {
    
    /**
     * Singleton instance
     *
     * @var Reset
     */
    private static $instance;
    
    /**
     * Get singleton instance
     *
     * @return Reset
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize the reset module
     */
    public function init() {
        // Add reset hooks
        \add_action( 'wpca_reset_settings', array( $this, 'reset_settings' ) );
        \add_action( 'wpca_reset_plugin', array( $this, 'reset_plugin' ) );
    }
    
    /**
     * Reset plugin settings
     *
     * @return array Reset results
     */
    public function reset_settings() {
        $results = array(
            'success' => true,
            'message' => \__( 'Settings reset successfully', WPCA_TEXT_DOMAIN )
        );
        
        // Delete all plugin options
        $options = array(
            'wpca_settings',
            'wpca_menu_customizer_settings',
            'wpca_menu_items',
            'wpca_login_attempts_*'
        );
        
        foreach ( $options as $option ) {
            if ( strpos( $option, '*' ) !== false ) {
                // Delete all options matching the pattern
                global $wpdb;
                $pattern = str_replace( '*', '%', $option );
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $pattern ) );
            } else {
                // Delete specific option
                \delete_option( $option );
            }
        }
        
        return $results;
    }
    
    /**
     * Reset plugin completely
     *
     * @return array Reset results
     */
    public function reset_plugin() {
        $results = array(
            'success' => true,
            'message' => \__( 'Plugin reset successfully', WPCA_TEXT_DOMAIN )
        );
        
        // Reset settings
        $settings_result = $this->reset_settings();
        
        if ( ! $settings_result['success'] ) {
            $results['success'] = false;
            $results['message'] = $settings_result['message'];
            return $results;
        }
        
        // Remove scheduled events
        $events = array(
            'wpca_optimize_database',
            'wpca_clean_transients'
        );
        
        foreach ( $events as $event ) {
            $timestamp = ( function_exists( '\wp_next_scheduled' ) ? \wp_next_scheduled( $event ) : false );
            if ( $timestamp && function_exists( '\wp_unschedule_event' ) ) {
                \wp_unschedule_event( $timestamp, $event );
            }
        }
        
        // Remove custom roles
        $settings = wpca_get_settings();
        
        if ( isset( $settings['user_roles'] ) && isset( $settings['user_roles']['custom_roles'] ) ) {
            foreach ( $settings['user_roles']['custom_roles'] as $role_slug => $role_data ) {
                if ( function_exists( '\remove_role' ) ) {
                    \remove_role( $role_slug );
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Reset specific module settings
     *
     * @param string $module Module name
     * @return array Reset results
     */
    public function reset_module_settings( $module ) {
        $results = array(
            'success' => true,
            'message' => sprintf( \__( '%s module settings reset successfully', WPCA_TEXT_DOMAIN ), ucfirst( $module ) )
        );
        
        // Get all settings
        $settings = wpca_get_settings();
        
        // Reset specific module settings
        if ( isset( $settings[$module] ) ) {
            unset( $settings[$module] );
            \update_option( 'wpca_settings', $settings );
        } else {
            $results['success'] = false;
            $results['message'] = sprintf( \__( 'Invalid module: %s', WPCA_TEXT_DOMAIN ), $module );
        }
        
        return $results;
    }
    
    /**
     * Reset dashboard widgets
     *
     * @return array Reset results
     */
    public function reset_dashboard_widgets() {
        $results = array(
            'success' => true,
            'message' => \__( 'Dashboard widgets reset successfully', WPCA_TEXT_DOMAIN )
        );
        
        // Delete dashboard widget settings
        \delete_option( 'wp_user_dashboard_widgets' );
        \delete_option( 'dashboard_widget_options' );
        
        return $results;
    }
    
    /**
     * Reset admin menu
     *
     * @return array Reset results
     */
    public function reset_admin_menu() {
        $results = array(
            'success' => true,
            'message' => \__( 'Admin menu reset successfully', WPCA_TEXT_DOMAIN )
        );
        
        // Delete admin menu settings
        \delete_option( 'wpca_menu_items' );
        \delete_option( 'wpca_menu_customizer_settings' );
        
        return $results;
    }
    
    /**
     * Reset login settings
     *
     * @return array Reset results
     */
    public function reset_login_settings() {
        $results = array(
            'success' => true,
            'message' => \__( 'Login settings reset successfully', WPCA_TEXT_DOMAIN )
        );
        
        // Get all settings
        $settings = wpca_get_settings();
        
        // Reset login settings
        if ( isset( $settings['login'] ) ) {
            unset( $settings['login'] );
            \update_option( 'wpca_settings', $settings );
        }
        
        // Delete login attempt transients
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpca_login_attempts_%'" );
        
        return $results;
    }
    
    /**
     * Reset performance settings
     *
     * @return array Reset results
     */
    public function reset_performance_settings() {
        $results = array(
            'success' => true,
            'message' => \__( 'Performance settings reset successfully', WPCA_TEXT_DOMAIN )
        );
        
        // Get all settings
        $settings = wpca_get_settings();
        
        // Reset performance settings
        if ( isset( $settings['performance'] ) ) {
            unset( $settings['performance'] );
            \update_option( 'wpca_settings', $settings );
        }
        
        // Remove scheduled performance events
        $events = array(
            'wpca_optimize_database',
            'wpca_clean_transients'
        );
        
        foreach ( $events as $event ) {
            $timestamp = ( function_exists( '\wp_next_scheduled' ) ? \wp_next_scheduled( $event ) : false );
            if ( $timestamp && function_exists( '\wp_unschedule_event' ) ) {
                \wp_unschedule_event( $timestamp, $event );
            }
        }
        
        return $results;
    }
}