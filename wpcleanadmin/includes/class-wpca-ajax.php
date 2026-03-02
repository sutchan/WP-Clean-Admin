<?php
/**
 * WPCleanAdmin Ajax Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include AJAX handler classes
if ( file_exists( dirname( __FILE__ ) . '/ajax/dashboard-ajax.php' ) ) {
    require_once dirname( __FILE__ ) . '/ajax/dashboard-ajax.php';
}
if ( file_exists( dirname( __FILE__ ) . '/ajax/cleanup-ajax.php' ) ) {
    require_once dirname( __FILE__ ) . '/ajax/cleanup-ajax.php';
}
if ( file_exists( dirname( __FILE__ ) . '/ajax/settings-ajax.php' ) ) {
    require_once dirname( __FILE__ ) . '/ajax/settings-ajax.php';
}
if ( file_exists( dirname( __FILE__ ) . '/ajax/user-roles-ajax.php' ) ) {
    require_once dirname( __FILE__ ) . '/ajax/user-roles-ajax.php';
}
if ( file_exists( dirname( __FILE__ ) . '/ajax/menu-manager-ajax.php' ) ) {
    require_once dirname( __FILE__ ) . '/ajax/menu-manager-ajax.php';
}
if ( file_exists( dirname( __FILE__ ) . '/ajax/performance-ajax.php' ) ) {
    require_once dirname( __FILE__ ) . '/ajax/performance-ajax.php';
}
if ( file_exists( dirname( __FILE__ ) . '/ajax/database-ajax.php' ) ) {
    require_once dirname( __FILE__ ) . '/ajax/database-ajax.php';
}
if ( file_exists( dirname( __FILE__ ) . '/ajax/resources-ajax.php' ) ) {
    require_once dirname( __FILE__ ) . '/ajax/resources-ajax.php';
}
if ( file_exists( dirname( __FILE__ ) . '/ajax/menu-customizer-ajax.php' ) ) {
    require_once dirname( __FILE__ ) . '/ajax/menu-customizer-ajax.php';
}
if ( file_exists( dirname( __FILE__ ) . '/ajax/reset-ajax.php' ) ) {
    require_once dirname( __FILE__ ) . '/ajax/reset-ajax.php';
}

// Declare WordPress functions for IDE compatibility
if ( ! function_exists( 'wp_verify_nonce' ) ) {
    function wp_verify_nonce() {}
}
if ( ! function_exists( 'wp_send_json_error' ) ) {
    function wp_send_json_error() {}
}
if ( ! function_exists( 'wp_send_json_success' ) ) {
    function wp_send_json_success() {}
}
if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can() {}
}
if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash() {}
}
if ( ! function_exists( 'add_query_arg' ) ) {
    function add_query_arg() {}
}
if ( ! function_exists( 'get_option' ) ) {
    function get_option() {}
}
if ( ! function_exists( 'update_option' ) ) {
    function update_option() {}
}
if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field() {}
}
if ( ! function_exists( '__' ) ) {
    function __() {}
}

/**
 * WPCleanAdmin AJAX Handler
 *
 * This class handles all AJAX requests for the WPCleanAdmin plugin.
 *
 * @package WPCleanAdmin
 * @subpackage AJAX
 * @since 1.7.15
 */

class AJAX {

    /**
     * Instance of this class.
     *
     * @since 1.7.15
     * @var AJAX
     */
    protected static $instance = null;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.7.15
     */
    private function __construct() {
        $this->register_ajax_hooks();
    }

    /**
     * Return an instance of this class.
     *
     * @since 1.7.15
     * @return AJAX A single instance of this class.
     */
    public static function getInstance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register all AJAX hooks.
     *
     * @since 1.7.15
     */
    private function register_ajax_hooks() {
        // Dashboard AJAX actions
        \add_action( 'wp_ajax_wpca_get_dashboard_stats', array( '\WPCleanAdmin\AJAX\Dashboard', 'get_dashboard_stats' ) );
        \add_action( 'wp_ajax_wpca_get_system_info', array( '\WPCleanAdmin\AJAX\Dashboard', 'get_system_info' ) );
        \add_action( 'wp_ajax_wpca_run_quick_action', array( '\WPCleanAdmin\AJAX\Dashboard', 'run_quick_action' ) );
        
        // Cleanup AJAX actions
        \add_action( 'wp_ajax_wpca_cleanup_database', array( '\WPCleanAdmin\AJAX\Cleanup', 'cleanup_database' ) );
        \add_action( 'wp_ajax_wpca_cleanup_media', array( '\WPCleanAdmin\AJAX\Cleanup', 'cleanup_media' ) );
        \add_action( 'wp_ajax_wpca_cleanup_comments', array( '\WPCleanAdmin\AJAX\Cleanup', 'cleanup_comments' ) );
        \add_action( 'wp_ajax_wpca_cleanup_content', array( '\WPCleanAdmin\AJAX\Cleanup', 'cleanup_content' ) );
        \add_action( 'wp_ajax_wpca_get_cleanup_stats', array( '\WPCleanAdmin\AJAX\Cleanup', 'get_cleanup_stats' ) );
        
        // Settings AJAX actions
        \add_action( 'wp_ajax_wpca_save_settings', array( '\WPCleanAdmin\AJAX\Settings', 'save_settings' ) );
        \add_action( 'wp_ajax_wpca_get_settings', array( '\WPCleanAdmin\AJAX\Settings', 'get_settings' ) );
        \add_action( 'wp_ajax_wpca_reset_settings', array( '\WPCleanAdmin\AJAX\Settings', 'reset_settings' ) );
        
        // User Roles AJAX actions
        \add_action( 'wp_ajax_wpca_get_user_roles', array( '\WPCleanAdmin\AJAX\User_Roles', 'get_user_roles' ) );
        \add_action( 'wp_ajax_wpca_update_role_capabilities', array( '\WPCleanAdmin\AJAX\User_Roles', 'update_role_capabilities' ) );
        \add_action( 'wp_ajax_wpca_create_role', array( '\WPCleanAdmin\AJAX\User_Roles', 'create_role' ) );
        \add_action( 'wp_ajax_wpca_delete_role', array( '\WPCleanAdmin\AJAX\User_Roles', 'delete_role' ) );
        \add_action( 'wp_ajax_wpca_duplicate_role', array( '\WPCleanAdmin\AJAX\User_Roles', 'duplicate_role' ) );
        
        // Menu Manager AJAX actions
        \add_action( 'wp_ajax_wpca_get_menu_items', array( '\WPCleanAdmin\AJAX\Menu_Manager', 'get_menu_items' ) );
        \add_action( 'wp_ajax_wpca_save_menu_items', array( '\WPCleanAdmin\AJAX\Menu_Manager', 'save_menu_items' ) );
        
        // Performance AJAX actions
        \add_action( 'wp_ajax_wpca_optimize_database', array( '\WPCleanAdmin\AJAX\Performance', 'optimize_database' ) );
        \add_action( 'wp_ajax_wpca_clear_cache', array( '\WPCleanAdmin\AJAX\Performance', 'clear_cache' ) );
        \add_action( 'wp_ajax_wpca_get_performance_stats', array( '\WPCleanAdmin\AJAX\Performance', 'get_performance_stats' ) );
        
        // Reset AJAX actions
        \add_action( 'wp_ajax_wpca_reset_settings', array( '\WPCleanAdmin\AJAX\Settings', 'reset_settings' ) );
        \add_action( 'wp_ajax_wpca_reset_plugin', array( '\WPCleanAdmin\AJAX\Reset', 'reset_plugin' ) );
        
        // Database AJAX actions
        \add_action( 'wp_ajax_wpca_get_database_info', array( '\WPCleanAdmin\AJAX\Database', 'get_database_info' ) );
        \add_action( 'wp_ajax_wpca_backup_database', array( '\WPCleanAdmin\AJAX\Database', 'backup_database' ) );
        \add_action( 'wp_ajax_wpca_restore_database', array( '\WPCleanAdmin\AJAX\Database', 'restore_database' ) );
        \add_action( 'wp_ajax_wpca_get_database_backups', array( '\WPCleanAdmin\AJAX\Database', 'get_database_backups' ) );
        \add_action( 'wp_ajax_wpca_delete_database_backup', array( '\WPCleanAdmin\AJAX\Database', 'delete_database_backup' ) );
        
        // Resources AJAX actions
        \add_action( 'wp_ajax_wpca_get_resources_stats', array( '\WPCleanAdmin\AJAX\Resources', 'get_resources_stats' ) );
        \add_action( 'wp_ajax_wpca_get_resource_details', array( '\WPCleanAdmin\AJAX\Resources', 'get_resource_details' ) );
        \add_action( 'wp_ajax_wpca_optimize_resources', array( '\WPCleanAdmin\AJAX\Resources', 'optimize_resources' ) );
        \add_action( 'wp_ajax_wpca_disable_resource', array( '\WPCleanAdmin\AJAX\Resources', 'disable_resource' ) );
        \add_action( 'wp_ajax_wpca_enable_resource', array( '\WPCleanAdmin\AJAX\Resources', 'enable_resource' ) );
        
        // Menu Customizer AJAX actions
        \add_action( 'wp_ajax_wpca_save_menu_customizer_settings', array( '\WPCleanAdmin\AJAX\Menu_Customizer', 'save_menu_customizer_settings' ) );
        \add_action( 'wp_ajax_wpca_get_menu_customizer_settings', array( '\WPCleanAdmin\AJAX\Menu_Customizer', 'get_menu_customizer_settings' ) );
        \add_action( 'wp_ajax_wpca_reset_menu_customizer_settings', array( '\WPCleanAdmin\AJAX\Menu_Customizer', 'reset_menu_customizer_settings' ) );
        
        // Database Settings AJAX actions
        \add_action( 'wp_ajax_wpca_save_database_settings', array( '\WPCleanAdmin\AJAX\Database', 'save_database_settings' ) );
        \add_action( 'wp_ajax_wpca_get_database_settings', array( '\WPCleanAdmin\AJAX\Database', 'get_database_settings' ) );
        \add_action( 'wp_ajax_wpca_reset_database_settings', array( '\WPCleanAdmin\AJAX\Database', 'reset_database_settings' ) );
        
        // Performance Settings AJAX actions
        \add_action( 'wp_ajax_wpca_save_performance_settings', array( '\WPCleanAdmin\AJAX\Performance', 'save_performance_settings' ) );
        \add_action( 'wp_ajax_wpca_get_performance_settings', array( '\WPCleanAdmin\AJAX\Performance', 'get_performance_settings' ) );
        \add_action( 'wp_ajax_wpca_reset_performance_settings', array( '\WPCleanAdmin\AJAX\Performance', 'reset_performance_settings' ) );
    }

}


