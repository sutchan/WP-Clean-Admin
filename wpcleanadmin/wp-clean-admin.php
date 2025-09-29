<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI: https://github.com/sutchan/WP-Clean-Admin
 * Description: Simplifies and optimizes the WordPress admin interface, providing a cleaner backend experience.
 * Version: 1.2.2
 * Author: Sut
 * Author URI: https://github.com/sutchan/
 * License: GPL v2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
if ( ! defined( 'WPCA_VERSION' ) ) {
	define( 'WPCA_VERSION', '1.2.2' );
}

if ( ! defined( 'WPCA_BASENAME' ) ) {
	define( 'WPCA_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'WPCA_PLUGIN_DIR' ) ) {
	// 简化为直接使用plugin_dir_path，现代WordPress版本都支持
	define( 'WPCA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WPCA_PLUGIN_URL' ) ) {
	// 简化为直接使用plugin_dir_url，现代WordPress版本都支持
	define( 'WPCA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// 移除了旧版WordPress的后备函数实现，现代WordPress版本已内置这些函数

/**
 * Load plugin translation files
 * Ensures proper Chinese display and adds debugging information
 */
function wpca_load_textdomain() {
    // Define language directory path
    $language_dir = WPCA_PLUGIN_DIR . 'languages/';
    
    // Get current language
    $current_locale = get_locale();
    $mo_file = $language_dir . 'wp-clean-admin-' . $current_locale . '.mo';
    
    // Add debugging information
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('WPCA: Current locale is ' . $current_locale);
        error_log('WPCA: Checking MO file at ' . $mo_file);
        error_log('WPCA: MO file exists? ' . (file_exists($mo_file) ? 'Yes' : 'No'));
        error_log('WPCA: Language directory exists? ' . (is_dir($language_dir) ? 'Yes' : 'No'));
    }
    
    // Force UTF-8 encoding for Chinese display
    if (function_exists('mb_internal_encoding')) {
        mb_internal_encoding('UTF-8');
    }
    
    // Load plugin translation files with additional checks
    $loaded = load_plugin_textdomain(
        'wp-clean-admin', // Text domain
        false,            // Do not use WordPress default language directory
        $language_dir     // Custom language directory
    );
    
    // Log loading result
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('WPCA: Translation loaded? ' . ($loaded ? 'Yes' : 'No'));
        
        // Test a translation string
        $test_translation = __('WP Clean Admin', 'wp-clean-admin');
        error_log('WPCA: Test translation result: ' . $test_translation);
    }
    
    return $loaded;
}

// Register translation loading function on init hook
// Using init instead of plugins_loaded to comply with WordPress 6.7.0+ translation loading best practices
add_action( 'init', 'wpca_load_textdomain', 5 );



// Include core files
function wpca_include_core_files() {
    require_once WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php';
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-settings.php';
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-menu-customizer.php';
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-ajax.php';
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-cleanup.php';
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-permissions.php';
}
// Direct call to ensure files are loaded
wpca_include_core_files();



function wpca_load_admin_resources() {
    global $wpca_admin_data;
    
    wp_enqueue_script( 'wpca-main', WPCA_PLUGIN_URL . 'assets/js/wpca-main.js', array( 'jquery' ), WPCA_VERSION, true );
    
    $wpca_admin_data = array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'wpca_admin_nonce' ),
        'debug'   => defined( 'WP_DEBUG' ) && WP_DEBUG,
        'version' => WPCA_VERSION,
        // Error messages for AJAX requests (translated)
        'error_initialization_failed' => __( 'WP Clean Admin initialization failed:', 'wp-clean-admin' ),
        'error_request_processing_failed' => __( 'Request processing failed', 'wp-clean-admin' ),
        'error_insufficient_permissions' => __( 'You do not have permission to perform this action', 'wp-clean-admin' ),
        'error_invalid_parameters' => __( 'Invalid request parameters', 'wp-clean-admin' ),
        'error_not_logged_in' => __( 'Please log in first', 'wp-clean-admin' ),
        'error_server_error' => __( 'Internal server error', 'wp-clean-admin' ),
        'error_js_settings_missing' => __( 'Unable to load necessary JavaScript settings. Please refresh the page and try again.', 'wp-clean-admin' ),
        'plugin_name' => __( 'WP Clean Admin', 'wp-clean-admin' ),
        // Menu management messages
        'menu_config_failed' => __( 'Menu management configuration failed to load', 'wp-clean-admin' ),
        'menu_jquery_ui_required' => __( 'Menu management requires jQuery UI Sortable, please ensure it is loaded', 'wp-clean-admin' ),
        'menu_slug_missing' => __( 'Menu slug is missing, please refresh the page and try again', 'wp-clean-admin' ),
        'settings_saved' => __( 'Settings saved', 'wp-clean-admin' ),
        'unknown_error' => __( 'An unknown error occurred', 'wp-clean-admin' ),
        'insufficient_permissions' => __( 'Insufficient permissions or security verification failed. The page will refresh in 2 seconds.', 'wp-clean-admin' ),
        'network_connection_error' => __( 'Network connection error, please check your network and try again.', 'wp-clean-admin' ),
        'request_failed_format' => __( 'Request failed, server returned error code: %d', 'wp-clean-admin' ),
        'menu_initialization_failed' => __( 'Menu management initialization failed', 'wp-clean-admin' ),
        // Settings management messages
        'media_unavailable' => __( 'WordPress media uploader not available.', 'wp-clean-admin' ),
        'reset_failed' => __( 'Reset failed. Please try again.', 'wp-clean-admin' ),
        // Login management messages
        'media_uploader_not_available' => __( 'WordPress media uploader is not available', 'wp-clean-admin' ),
        'use_this_media' => __( 'Use this media', 'wp-clean-admin' ),
        'show_login_form' => __( 'Show Login Form', 'wp-clean-admin' )
    );
    
    wp_localize_script( 'wpca-main', 'wpca_admin', $wpca_admin_data );
}
add_action( 'admin_enqueue_scripts', 'wpca_load_admin_resources' );

function wpca_activate_plugin() {
    if ( ! get_option( 'wpca_settings' ) ) {
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
        update_option( 'wpca_settings', $default_settings );
    } else {
        $settings = get_option( 'wpca_settings' );
        $settings['version'] = WPCA_VERSION;
        update_option( 'wpca_settings', $settings );
    }
    
    flush_rewrite_rules();
    
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-permissions.php';
    if ( class_exists( 'WPCA_Permissions' ) ) {
        $permissions = new WPCA_Permissions();
        $permissions->set_default_permissions();
    }
}
register_activation_hook( __FILE__, 'wpca_activate_plugin' );

function wpca_deactivate_plugin() {
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-permissions.php';
    if ( class_exists( 'WPCA_Permissions' ) ) {
        $permissions = new WPCA_Permissions();
        $permissions->cleanup_capabilities();
    }
    
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wpca_deactivate_plugin' );

function wpca_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=wp-clean-admin">'.__( '设置', 'wp-clean-admin' ).'</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . WPCA_BASENAME, 'wpca_add_settings_link' );

/**
 * Initialize plugin components
 */
function wpca_initialize_components() {
    if ( class_exists( 'WPCA_Settings' ) ) {
        new WPCA_Settings();
    }
    
    if ( class_exists( 'WPCA_Menu_Customizer' ) ) {
        new WPCA_Menu_Customizer();
    }
    
    if ( class_exists( 'WPCA_Permissions' ) ) {
        new WPCA_Permissions();
    }
    
    if ( class_exists( 'WPCA_Cleanup' ) ) {
        new WPCA_Cleanup();
    }
}

add_action( 'plugins_loaded', 'wpca_initialize_components', 20 );