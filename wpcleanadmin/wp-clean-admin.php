<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI:  https://github.com/sutchan/WP-Clean-Admin
 * Description: Simplify and customize your WordPress admin dashboard with a flat, minimal, and fresh UI.
 * Version:     1.0.0
 * Author:      sutchan
 * Author URI:  https://github.com/sutchan
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
if ( ! defined( 'WPCA_VERSION' ) ) {
    define( 'WPCA_VERSION', '1.0.0' );
}
if ( ! defined( 'WPCA_PLUGIN_DIR' ) ) {
    define( 'WPCA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WPCA_PLUGIN_URL' ) ) {
    define( 'WPCA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Define additional constants for new features
if ( ! defined( 'WPCA_RESPONSIVE_BREAKPOINT' ) ) {
    define( 'WPCA_RESPONSIVE_BREAKPOINT', '782px' ); // WordPress admin breakpoint
}

if ( ! defined( 'WPCA_EXPORT_KEY' ) ) {
    define( 'WPCA_EXPORT_KEY', 'wpca_settings_export' );
}

/**
 * Load plugin textdomain.
 */
function wpca_load_textdomain() {
    load_plugin_textdomain( 'wp-clean-admin', false, basename( WPCA_PLUGIN_DIR ) . '/languages' );
}
add_action( 'plugins_loaded', 'wpca_load_textdomain' );

// Include core files
require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-settings.php';
require_once WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php';
require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-export-import.php'; // New export/import feature
require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-user-roles.php'; // User role permissions
require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-menu-customizer.php'; // Menu customization

/**
 * Initialize the plugin.
 */
function wpca_run_plugin() {
    // Initialize core components
    $settings = new WPCA_Settings();
    
    // Only load advanced features for admin users
    if (current_user_can('manage_options')) {
        new WPCA_Export_Import();
        new WPCA_User_Roles();
        new WPCA_Menu_Customizer();
        
        // Add admin menu
        add_action('admin_menu', 'wpca_add_admin_menu');
    }

    // Core functions are hooked directly in wpca-core-functions.php
    
    /**
     * Add admin menu item
     */
    function wpca_add_admin_menu() {
        add_menu_page(
            __('WP Clean Admin Settings', 'wp-clean-admin'),
            __('Clean Admin', 'wp-clean-admin'),
            'manage_options',
            'wp-clean-admin',
            'wpca_render_settings_page',
            'dashicons-admin-appearance',
            80
        );
    }
    
    /**
     * Render settings page
     */
    function wpca_render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('WP Clean Admin Settings', 'wp-clean-admin'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wpca_settings_group');
                do_settings_sections('wp-clean-admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    // Add responsive design support
    add_action('admin_head', function() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    });

    // Add theme presets
    add_filter('wpca_theme_presets', function($presets) {
        $presets['modern'] = [
            'primary_color' => '#3a86ff',
            'background_color' => '#f8f9fa',
            'text_color' => '#212529'
        ];
        $presets['dark'] = [
            'primary_color' => '#6c757d',
            'background_color' => '#212529',
            'text_color' => '#f8f9fa'
        ];
        return $presets;
    });
}
add_action( 'plugins_loaded', 'wpca_run_plugin' );

/**
 * Activation hook.
 * Set default options if they don't exist.
 */
function wpca_activate() {
    if ( ! get_option( 'wpca_settings' ) ) {
        update_option( 'wpca_settings', WPCA_Settings::get_default_settings() );
    }
}
register_activation_hook( __FILE__, 'wpca_activate' );

/**
 * Add settings link to plugin actions
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wpca_add_settings_link');
function wpca_add_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=wp_clean_admin">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
