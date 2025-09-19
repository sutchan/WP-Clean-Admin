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
require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-permissions.php'; // 权限管理系统
require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-user-roles.php'; // User role permissions
require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-menu-customizer.php'; // Menu customization

/**
 * Initialize the plugin.
 */
function wpca_run_plugin() {
    // Initialize core components
    $settings = new WPCA_Settings();
    
    // 初始化权限管理系统（对所有用户都可用）
    $permissions = new WPCA_Permissions();
    
    // 根据用户权限加载高级功能
    if (WPCA_Permissions::current_user_can('wpca_manage_all') || current_user_can('manage_options')) {
        new WPCA_User_Roles();
        new WPCA_Menu_Customizer();
        
        // Admin menu removed as per requirements
        // add_action('admin_menu', 'wpca_add_admin_menu');
    }

    // Core functions are hooked directly in wpca-core-functions.php
    


    // Add responsive design support
    add_action('admin_head', function() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    });

    // Add admin page assets
    add_action('admin_enqueue_scripts', function() {
        // Only load on our plugin pages
        $screen = get_current_screen();
        if (strpos($screen->id, 'wp_clean_admin') === false) return;
        
        // Enqueue color picker and its style
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Enqueue our admin scripts
        wp_enqueue_script(
            'wpca-settings',
            WPCA_PLUGIN_URL . 'assets/js/wpca-settings.js',
            array('jquery', 'wp-color-picker'),
            WPCA_VERSION,
            true
        );
    });

    // Add login page styling and element controls
    add_action('login_enqueue_scripts', function() {
        // Get settings
        $options = get_option('wpca_settings');
        $login_style = isset($options['login_style']) ? $options['login_style'] : 'default';
        
        // Enqueue our login styles
        wp_enqueue_style(
            'wpca-login-styles',
            WPCA_PLUGIN_URL . 'assets/css/wpca-admin.css',
            array(),
            WPCA_VERSION
        );
        
        // Enqueue login module
        wp_enqueue_script(
            'wpca-login',
            WPCA_PLUGIN_URL . 'assets/js/login-page.js',
            array('jquery', 'wpca-core'),
            WPCA_VERSION,
            true
        );

        // Localize script with login style data
        wp_localize_script('wpca-core', 'wpcaLoginVars', [
            'loginStyle' => $login_style,
            'loginLogo' => isset($options['login_logo']) ? $options['login_logo'] : '',
            'loginBackground' => isset($options['login_background']) ? $options['login_background'] : '',
            'elementControls' => [
                'show_language_switcher' => isset($options['show_language_switcher']) ? $options['show_language_switcher'] : '1',
                'show_back_to_site' => isset($options['show_back_to_site']) ? $options['show_back_to_site'] : '1',
                'show_remember_me' => isset($options['show_remember_me']) ? $options['show_remember_me'] : '1',
                'show_login_form' => isset($options['show_login_form']) ? $options['show_login_form'] : '1'
            ]
        ]);
        
        // Add login style class via inline CSS
        wp_add_inline_style('wpca-login-styles', 
            "body.login { background-color: inherit !important; }
            body.login.wpca-login-$login_style { background-color: inherit !important; }"
        );
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

    // Modify admin page titles if hide_wordpress_title is enabled
    add_filter('admin_title', function($admin_title) {
        $options = get_option('wpca_settings');
        if (isset($options['hide_wordpress_title']) && $options['hide_wordpress_title']) {
            return str_replace('WordPress', '', $admin_title);
        }
        return $admin_title;
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
