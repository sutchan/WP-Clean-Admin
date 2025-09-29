<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI: https://github.com/sutchan/WP-Clean-Admin
 * Description: Simplifies and optimizes the WordPress admin interface, providing a cleaner backend experience.
 * Version: 1.1.1
 * Author: Sut
 * Author URI: https://github.com/sutchan/
 * License: GPLv2 or later
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
if ( ! defined( 'WPCA_VERSION' ) ) {
	define( 'WPCA_VERSION', '1.1.1' );
}

if ( ! defined( 'WPCA_BASENAME' ) ) {
	define( 'WPCA_BASENAME', 'wp-clean-admin.php' );
}

if ( ! defined( 'WPCA_PLUGIN_DIR' ) ) {
	if ( function_exists( 'plugin_dir_path' ) ) {
		define( 'WPCA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	} else {
		define( 'WPCA_PLUGIN_DIR', trailingslashit( dirname( __FILE__ ) ) );
	}
}

if ( ! defined( 'WPCA_PLUGIN_URL' ) ) {
	if ( function_exists( 'plugin_dir_url' ) ) {
		define( 'WPCA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	} else {
		$plugin_url = str_replace( '\\', '/', trailingslashit( dirname( __FILE__ ) ) );
        if ( defined( 'ABSPATH' ) && ABSPATH ) {
            $plugin_url = str_replace( str_replace( '\\', '/', ABSPATH ), site_url( '/' ), $plugin_url );
        }
		define( 'WPCA_PLUGIN_URL', $plugin_url );
	}
}

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $string ) {
		return rtrim( $string, '/\\' ) . '/';
	}
}

if ( ! function_exists( 'site_url' ) ) {
	function site_url( $path = '', $scheme = null ) {
		$url = 'http://' . $_SERVER['HTTP_HOST'];
		if ( $path ) {
			$url = trailingslashit( $url ) . ltrim( $path, '/' );
		}
		return $url;
	}
}

if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	include_once WPCA_PLUGIN_DIR . 'translation-debug.php';
}

function wpca_load_textdomain() {
    if ( function_exists( 'load_plugin_textdomain' ) ) {
        if ( function_exists( 'plugin_basename' ) ) {
            load_plugin_textdomain( 'wp-clean-admin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        } else {
            load_plugin_textdomain( 'wp-clean-admin', false, 'wpcleanadmin/languages/' );
        }
    }
}
if ( function_exists( 'add_action' ) ) {
    add_action( 'plugins_loaded', 'wpca_load_textdomain' );
}

function wpca_load_admin_resources() {
    global $wpca_admin_data;
    
    if ( function_exists( 'wp_enqueue_script' ) ) {
        wp_enqueue_script( 'wpca-main', WPCA_PLUGIN_URL . 'assets/js/wpca-main.js', array( 'jquery' ), WPCA_VERSION, true );
        
        $wpca_admin_data = array(
            'ajaxurl' => function_exists( 'admin_url' ) ? admin_url( 'admin-ajax.php' ) : '/wp-admin/admin-ajax.php',
            'nonce'   => function_exists( 'wp_create_nonce' ) ? wp_create_nonce( 'wpca_admin_nonce' ) : 'dummy_nonce',
            'debug'   => defined( 'WP_DEBUG' ) && WP_DEBUG,
            'version' => WPCA_VERSION,
            // Error messages for AJAX requests
            'error_request_processing_failed' => __('Request processing failed', 'wp-clean-admin'),
            'error_insufficient_permissions' => __('You do not have permission to perform this action', 'wp-clean-admin'),
            'error_invalid_parameters' => __('Invalid request parameters', 'wp-clean-admin'),
            'error_not_logged_in' => __('Please log in first', 'wp-clean-admin'),
            'error_server_error' => __('Internal server error', 'wp-clean-admin')
        );
        
        if ( function_exists( 'wp_localize_script' ) ) {
            wp_localize_script( 'wpca-main', 'wpca_admin', $wpca_admin_data );
        }
    }
}
if ( function_exists( 'add_action' ) ) {
    add_action( 'admin_enqueue_scripts', 'wpca_load_admin_resources' );
}

function wpca_include_core_files() {
    require_once WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php';
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-settings.php';
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-menu-customizer.php';
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-ajax.php';
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-dashboard.php';
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-login.php';
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-cleanup.php';
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-permissions.php';
}
if ( function_exists( 'add_action' ) ) {
    add_action( 'plugins_loaded', 'wpca_include_core_files' );
}

function wpca_activate_plugin() {
    if ( function_exists( 'get_option' ) && ! get_option( 'wpca_settings' ) ) {
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
        if ( function_exists( 'update_option' ) ) {
            update_option( 'wpca_settings', $default_settings );
        }
    } else if ( function_exists( 'get_option' ) && function_exists( 'update_option' ) ) {
        $settings = get_option( 'wpca_settings' );
        $settings['version'] = WPCA_VERSION;
        update_option( 'wpca_settings', $settings );
    }
    
    if ( function_exists( 'flush_rewrite_rules' ) ) {
        flush_rewrite_rules();
    }
    
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-permissions.php';
    if ( class_exists( 'WPCA_Permissions' ) ) {
        $permissions = new WPCA_Permissions();
        if ( method_exists( $permissions, 'set_default_permissions' ) ) {
            $permissions->set_default_permissions();
        }
    }
}
if ( function_exists( 'register_activation_hook' ) ) {
    register_activation_hook( __FILE__, 'wpca_activate_plugin' );
}

function wpca_deactivate_plugin() {
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-permissions.php';
    if ( class_exists( 'WPCA_Permissions' ) ) {
        $permissions = new WPCA_Permissions();
        if ( method_exists( $permissions, 'cleanup_capabilities' ) ) {
            $permissions->cleanup_capabilities();
        }
    }
    
    if ( function_exists( 'flush_rewrite_rules' ) ) {
        flush_rewrite_rules();
    }
}
if ( function_exists( 'register_deactivation_hook' ) ) {
    register_deactivation_hook( __FILE__, 'wpca_deactivate_plugin' );
}

function wpca_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=wp-clean-admin">Settings</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
if ( function_exists( 'add_filter' ) ) {
    add_filter( 'plugin_action_links_' . WPCA_BASENAME, 'wpca_add_settings_link' );
}

/**
 * Initialize plugin components
 * 初始化插件组件
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
}

if ( function_exists( 'add_action' ) ) {
    add_action( 'plugins_loaded', 'wpca_initialize_components', 20 );
}