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

// Declare WordPress functions for IDE compatibility
if ( ! function_exists( 'plugin_dir_path' ) ) {
    function plugin_dir_path() {}
}
if ( ! function_exists( 'plugin_dir_url' ) ) {
    function plugin_dir_url() {}
}
if ( ! function_exists( 'load_plugin_textdomain' ) ) {
    function load_plugin_textdomain() {}
}
if ( ! function_exists( 'add_action' ) ) {
    function add_action() {}
}
if ( ! function_exists( 'deactivate_plugins' ) ) {
    function deactivate_plugins() {}
}
if ( ! function_exists( 'wp_die' ) ) {
    function wp_die() {}
}
if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__() {}
}
if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook() {}
}
if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook() {}
}
if ( ! function_exists( 'get_option' ) ) {
    function get_option() {}
}
if ( ! function_exists( 'update_option' ) ) {
    function update_option() {}
}
if ( ! function_exists( 'flush_rewrite_rules' ) ) {
    function flush_rewrite_rules() {}
}
if ( ! function_exists( 'admin_url' ) ) {
    function admin_url() {}
}
if ( ! function_exists( 'esc_url' ) ) {
    function esc_url() {}
}
if ( ! function_exists( 'esc_html' ) ) {
    function esc_html() {}
}
if ( ! function_exists( '__' ) ) {
    function __() {}
}
if ( ! function_exists( 'add_filter' ) ) {
    function add_filter() {}
}
if ( ! function_exists( 'plugin_basename' ) ) {
    function plugin_basename() {}
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
    try {
        // Load text domain for translations
        if ( function_exists( 'load_plugin_textdomain' ) && function_exists( 'plugin_basename' ) ) {
            $plugin_basename = plugin_basename( __FILE__ );
            if ( is_string( $plugin_basename ) ) {
                load_plugin_textdomain( WPCA_TEXT_DOMAIN, false, dirname( $plugin_basename ) . '/languages/' );
            }
        }
        
        // Initialize core class
        if ( class_exists( 'WPCleanAdmin\Modules\Core\Classes\Core' ) ) {
            WPCleanAdmin\Modules\Core\Classes\Core::getInstance();
        } else {
            // Fallback to legacy core class
            if ( class_exists( 'WPCleanAdmin\Core' ) ) {
                WPCleanAdmin\Core::getInstance();
            } else {
                // Log error if core class not found
                if ( function_exists( 'error_log' ) ) {
                    error_log( 'WP Clean Admin Error: Core class not found' );
                }
            }
        }
    } catch ( \Exception $e ) {
        // Log any exceptions during initialization
        if ( function_exists( 'error_log' ) ) {
            error_log( 'WP Clean Admin Error during initialization: ' . $e->getMessage() );
            error_log( 'WP Clean Admin Error trace: ' . $e->getTraceAsString() );
        }
    }
}

// Hook into WordPress initialization
if ( function_exists( 'add_action' ) ) {
    add_action( 'plugins_loaded', 'wpca_init' );
}

/**
 * 紧急停用插件函数
 * 
 * 当插件出现严重错误时，可手动调用此函数立即停用插件。
 * 注意：此函数默认未挂载到任何钩子，需手动触发。
 *
 * @since 1.8.0
 */
function wpca_emergency_deactivate() {
    // 获取当前插件的 basename
    if ( function_exists( 'plugin_basename' ) ) {
        $plugin_basename = plugin_basename( __FILE__ );

        // 如果 deactivate_plugins 函数可用，则执行停用
        if ( function_exists( 'deactivate_plugins' ) ) {
            deactivate_plugins( array( $plugin_basename ) );

            // 若当前请求为激活操作，则输出停用提示并终止
            if ( isset( $_GET['action'] ) && $_GET['action'] === 'activate' && function_exists( 'wp_die' ) && function_exists( 'esc_html__' ) ) {
                wp_die(
                    esc_html__(
                        'WP Clean Admin 插件因严重错误已被自动停用。请查看错误日志以获取更多信息。',
                        'wp-clean-admin'
                    )
                );
            }
        }
    }
}

// Emergency deactivation function - call manually when needed
// Note: This function is not hooked by default to avoid automatic deactivation

// Register activation hook
if ( function_exists( 'register_activation_hook' ) ) {
    register_activation_hook( __FILE__, function() {
        try {
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
                $current_settings = get_option( 'wpca_settings', array() );
                $current_settings = is_array( $current_settings ) ? $current_settings : array();
                $updated_settings = array_merge( $default_settings, $current_settings );
                update_option( 'wpca_settings', $updated_settings );
            }
            
            // Flush rewrite rules
            if ( function_exists( 'flush_rewrite_rules' ) ) {
                flush_rewrite_rules();
            }
        } catch ( \Exception $e ) {
            // Log error if activation fails
            if ( function_exists( 'error_log' ) ) {
                error_log( 'WP Clean Admin Activation Error: ' . $e->getMessage() );
                error_log( 'WP Clean Admin Activation Error Trace: ' . $e->getTraceAsString() );
            }
        }
    });
}

// Register deactivation hook
if ( function_exists( 'register_deactivation_hook' ) ) {
    register_deactivation_hook( __FILE__, function() {
        try {
            // Flush rewrite rules
            if ( function_exists( 'flush_rewrite_rules' ) ) {
                flush_rewrite_rules();
            }
        } catch ( \Exception $e ) {
            // Log error if deactivation fails
            if ( function_exists( 'error_log' ) ) {
                error_log( 'WP Clean Admin Deactivation Error: ' . $e->getMessage() );
                error_log( 'WP Clean Admin Deactivation Error Trace: ' . $e->getTraceAsString() );
            }
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
    if ( function_exists( '\admin_url' ) && function_exists( '\esc_url' ) && function_exists( '\esc_html' ) && function_exists( '\__' ) ) {
        $settings_link = array(
            '<a href="' . \esc_url( \admin_url( 'admin.php?page=wp-clean-admin' ) ) . '">' . \esc_html( \__( 'Settings', WPCA_TEXT_DOMAIN ) ) . '</a>'
        );
        return array_merge( $settings_link, $links );
    }
    return $links;
}

// Hook into plugin action links
if ( function_exists( '\add_filter' ) && function_exists( '\plugin_basename' ) ) {
    $plugin_basename = \plugin_basename( __FILE__ );
    if ( is_string( $plugin_basename ) ) {
        \add_filter( 'plugin_action_links_' . $plugin_basename, 'wpca_add_plugin_action_links' );
    }
}


