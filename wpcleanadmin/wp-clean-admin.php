<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI: https://github.com/sutchan/WP-Clean-Admin
 * Description: A comprehensive WordPress admin cleanup and optimization plugin
 * Version: 1.8.0
 * Author: Sut
 * Author URI: https://github.com/sutchan
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 * Network: true
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'WPCA_VERSION', '1.8.0' );
define( 'WPCA_PLUGIN_DIR', ( function_exists( 'plugin_dir_path' ) ? plugin_dir_path( __FILE__ ) : dirname( __FILE__ ) . '/' ) );
define( 'WPCA_PLUGIN_URL', ( function_exists( 'plugin_dir_url' ) ? plugin_dir_url( __FILE__ ) : '' ) );
define( 'WPCA_TEXT_DOMAIN', 'wp-clean-admin' );

// Load autoloader
$autoloader_path = WPCA_PLUGIN_DIR . 'includes/autoload.php';
if ( file_exists( $autoloader_path ) ) {
    require_once $autoloader_path;
} else {
    // Log error if autoloader not found
    if ( function_exists( 'error_log' ) ) {
        error_log( 'WP Clean Admin Error: Autoloader file not found at ' . $autoloader_path );
    }
}

// Fallback autoloader if main autoloader fails
spl_autoload_register( function( $class ) {
    // Check if the class belongs to our namespace
    if ( strpos( $class, 'WPCleanAdmin\\' ) !== 0 ) {
        return;
    }
    
    // Remove namespace prefix
    $class_name = str_replace( 'WPCleanAdmin\\', '', $class );
    
    // Convert camelCase to kebab-case and replace underscores with hyphens
    $file_path = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $class_name ) );
    $file_path = str_replace( '_', '-', $file_path );
    
    // Build full file path
    $file = __DIR__ . '/includes/class-wpca-' . $file_path . '.php';
    
    // Check if file exists and include it
    if ( file_exists( $file ) ) {
        require_once $file;
    }
});

/**
 * Initialize the WP Clean Admin plugin
 *
 * This function loads the plugin text domain and initializes the core class.
 * It's hooked to the 'plugins_loaded' action.
 *
 * @since 1.7.15
 */
function wpca_init() {
    // Load text domain for translations
    if ( function_exists( 'load_plugin_textdomain' ) && function_exists( 'plugin_basename' ) ) {
        load_plugin_textdomain( WPCA_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
    
    // Initialize core class
    if ( class_exists( 'WPCleanAdmin\Core' ) ) {
        WPCleanAdmin\Core::getInstance();
    } else {
        // Log error if core class not found
        if ( function_exists( 'error_log' ) ) {
            error_log( 'WP Clean Admin Error: Core class not found' );
        }
    }
}

// Hook into WordPress initialization
if ( function_exists( 'add_action' ) ) {
    add_action( 'plugins_loaded', 'wpca_init' );
}

// Register activation hook
if ( function_exists( 'register_activation_hook' ) ) {
    register_activation_hook( __FILE__, function() {
        // Set default settings directly
        $default_settings = array(
            'general' => array(
                'clean_admin_bar' => 1,
                'clean_dashboard' => 1,
                'remove_wp_logo' => 1,
            ),
            'performance' => array(
                'optimize_database' => 1,
                'clean_transients' => 1,
                'disable_emojis' => 1,
            ),
            'menu' => array(
                'remove_dashboard_widgets' => 1,
                'simplify_admin_menu' => 1,
            ),
        );
        
        // Update settings if they don't exist
        if ( function_exists( 'get_option' ) && function_exists( 'update_option' ) ) {
            try {
                $current_settings = get_option( 'wpca_settings', array() );
                $updated_settings = array_merge( $default_settings, $current_settings );
                update_option( 'wpca_settings', $updated_settings );
            } catch ( Exception $e ) {
                // Log error if settings update fails
                if ( function_exists( 'error_log' ) ) {
                    error_log( 'WP Clean Admin Error: Failed to update settings: ' . $e->getMessage() );
                }
            }
        }
        
        // Flush rewrite rules
        if ( function_exists( 'flush_rewrite_rules' ) ) {
            try {
                flush_rewrite_rules();
            } catch ( Exception $e ) {
                // Log error if rewrite rules flush fails
                if ( function_exists( 'error_log' ) ) {
                    error_log( 'WP Clean Admin Error: Failed to flush rewrite rules: ' . $e->getMessage() );
                }
            }
        }
    });
}

// Register deactivation hook
if ( function_exists( 'register_deactivation_hook' ) ) {
    register_deactivation_hook( __FILE__, function() {
        // Flush rewrite rules
        if ( function_exists( 'flush_rewrite_rules' ) ) {
            flush_rewrite_rules();
        }
    });
}

/**
 * Add settings link to plugin management page
 *
 * @param array $links Existing plugin action links
 * @return array Modified plugin action links with settings link
 * @since 1.8.0
 */
function wpca_add_plugin_action_links( $links ) {
    if ( function_exists( 'admin_url' ) && function_exists( 'esc_url' ) && function_exists( 'esc_html' ) && function_exists( '__' ) ) {
        $settings_link = array(
            '<a href="' . esc_url( admin_url( 'admin.php?page=wp-clean-admin' ) ) . '">' . esc_html( __( 'Settings', WPCA_TEXT_DOMAIN ) ) . '</a>'
        );
        return array_merge( $settings_link, $links );
    }
    return $links;
}

// Hook into plugin action links
if ( function_exists( 'add_filter' ) ) {
    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wpca_add_plugin_action_links' );
}


