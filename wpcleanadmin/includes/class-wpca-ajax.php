<?php
/**
 * WPCleanAdmin AJAX Handler
 *
 * This class handles all AJAX requests for the WPCleanAdmin plugin.
 *
 * @package WPCleanAdmin
 * @subpackage AJAX
 * @since 1.7.15
 */

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
    public static function get_instance() {
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
        \add_action( 'wp_ajax_wpca_get_dashboard_stats', array( $this, 'get_dashboard_stats' ) );
        \add_action( 'wp_ajax_wpca_get_system_info', array( $this, 'get_system_info' ) );
        \add_action( 'wp_ajax_wpca_run_quick_action', array( $this, 'run_quick_action' ) );
        
        // Cleanup AJAX actions
        \add_action( 'wp_ajax_wpca_cleanup_database', array( $this, 'cleanup_database' ) );
        \add_action( 'wp_ajax_wpca_cleanup_media', array( $this, 'cleanup_media' ) );
        \add_action( 'wp_ajax_wpca_cleanup_comments', array( $this, 'cleanup_comments' ) );
        \add_action( 'wp_ajax_wpca_cleanup_content', array( $this, 'cleanup_content' ) );
        \add_action( 'wp_ajax_wpca_get_cleanup_stats', array( $this, 'get_cleanup_stats' ) );
        
        // Settings AJAX actions
        \add_action( 'wp_ajax_wpca_save_settings', array( $this, 'save_settings' ) );
        \add_action( 'wp_ajax_wpca_get_settings', array( $this, 'get_settings' ) );
        \add_action( 'wp_ajax_wpca_reset_settings', array( $this, 'reset_settings' ) );
        
        // User Roles AJAX actions
        \add_action( 'wp_ajax_wpca_get_user_roles', array( $this, 'get_user_roles' ) );
        \add_action( 'wp_ajax_wpca_update_role_capabilities', array( $this, 'update_role_capabilities' ) );
        \add_action( 'wp_ajax_wpca_create_role', array( $this, 'create_role' ) );
        \add_action( 'wp_ajax_wpca_delete_role', array( $this, 'delete_role' ) );
        \add_action( 'wp_ajax_wpca_duplicate_role', array( $this, 'duplicate_role' ) );
        
        // Menu Manager AJAX actions
        \add_action( 'wp_ajax_wpca_get_menu_items', array( $this, 'get_menu_items' ) );
        \add_action( 'wp_ajax_wpca_save_menu_items', array( $this, 'save_menu_items' ) );
        
        // Performance AJAX actions
        \add_action( 'wp_ajax_wpca_optimize_database', array( $this, 'optimize_database' ) );
        \add_action( 'wp_ajax_wpca_clear_cache', array( $this, 'clear_cache' ) );
        \add_action( 'wp_ajax_wpca_get_performance_stats', array( $this, 'get_performance_stats' ) );
        
        // Reset AJAX actions
        \add_action( 'wp_ajax_wpca_reset_settings', array( $this, 'reset_settings' ) );
        \add_action( 'wp_ajax_wpca_reset_plugin', array( $this, 'reset_plugin' ) );
        
        // Database AJAX actions
        \add_action( 'wp_ajax_wpca_get_database_info', array( $this, 'get_database_info' ) );
        \add_action( 'wp_ajax_wpca_backup_database', array( $this, 'backup_database' ) );
        \add_action( 'wp_ajax_wpca_restore_database', array( $this, 'restore_database' ) );
        \add_action( 'wp_ajax_wpca_get_database_backups', array( $this, 'get_database_backups' ) );
        \add_action( 'wp_ajax_wpca_delete_database_backup', array( $this, 'delete_database_backup' ) );
        
        // Resources AJAX actions
        \add_action( 'wp_ajax_wpca_get_resources_stats', array( $this, 'get_resources_stats' ) );
        \add_action( 'wp_ajax_wpca_get_resource_details', array( $this, 'get_resource_details' ) );
        \add_action( 'wp_ajax_wpca_optimize_resources', array( $this, 'optimize_resources' ) );
        \add_action( 'wp_ajax_wpca_disable_resource', array( $this, 'disable_resource' ) );
        \add_action( 'wp_ajax_wpca_enable_resource', array( $this, 'enable_resource' ) );
        
        // Menu Customizer AJAX actions
        \add_action( 'wp_ajax_wpca_save_menu_customizer_settings', array( $this, 'save_menu_customizer_settings' ) );
        \add_action( 'wp_ajax_wpca_get_menu_customizer_settings', array( $this, 'get_menu_customizer_settings' ) );
        \add_action( 'wp_ajax_wpca_reset_menu_customizer_settings', array( $this, 'reset_menu_customizer_settings' ) );
        
        // Database Settings AJAX actions
        \add_action( 'wp_ajax_wpca_save_database_settings', array( $this, 'save_database_settings' ) );
        \add_action( 'wp_ajax_wpca_get_database_settings', array( $this, 'get_database_settings' ) );
        \add_action( 'wp_ajax_wpca_reset_database_settings', array( $this, 'reset_database_settings' ) );
        
        // Performance Settings AJAX actions
        \add_action( 'wp_ajax_wpca_save_performance_settings', array( $this, 'save_performance_settings' ) );
        \add_action( 'wp_ajax_wpca_get_performance_settings', array( $this, 'get_performance_settings' ) );
        \add_action( 'wp_ajax_wpca_reset_performance_settings', array( $this, 'reset_performance_settings' ) );
    }

    /**
     * Verify AJAX nonce and check user capabilities.
     *
     * @since 1.7.15
     * @param string $action The AJAX action name.
     * @return bool True if the nonce is valid and user has capabilities, false otherwise.
     */
    private function verify_ajax_request( $action ) {
        if ( ! function_exists( '\wp_verify_nonce' ) || ! \wp_verify_nonce( $_POST['_wpnonce'], 'wpca_ajax_nonce' ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( \__( 'Invalid nonce', WPCA_TEXT_DOMAIN ) );
            }
            return false;
        }
        
        if ( ! function_exists( '\current_user_can' ) || ! \current_user_can( 'manage_options' ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( \__( 'Insufficient permissions', WPCA_TEXT_DOMAIN ) );
            }
            return false;
        }
        
        return true;
    }

    /**
     * Get dashboard statistics.
     *
     * @since 1.7.15
     */
    public function get_dashboard_stats() {
        if ( ! $this->verify_ajax_request( 'wpca_get_dashboard_stats' ) ) {
            return;
        }
        
        $dashboard = new Dashboard();
        $stats = $dashboard->get_dashboard_stats();
        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( $stats );
        }
    }

    /**
     * Get system information.
     *
     * @since 1.7.15
     */
    public function get_system_info() {
        if ( ! $this->verify_ajax_request( 'wpca_get_system_info' ) ) {
            return;
        }
        
        $dashboard = new Dashboard();
        $system_info = $dashboard->get_system_info();
        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( $system_info );
        }
    }

    /**
     * Run quick action.
     *
     * @since 1.7.15
     */
    public function run_quick_action() {
        if ( ! $this->verify_ajax_request( 'wpca_run_quick_action' ) ) {
            return;
        }
        
        $action = isset( $_POST['action_name'] ) ? sanitize_text_field( $_POST['action_name'] ) : '';
        
        $dashboard = new Dashboard();
        $result = $dashboard->run_quick_action( $action );
        
        if ( $result['success'] ) {
            if ( function_exists( '\wp_send_json_success' ) ) {
                \wp_send_json_success( $result );
            }
        } else {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( $result['message'] );
            }
        }
    }

    /**
     * Cleanup database.
     *
     * @since 1.7.15
     */
    public function cleanup_database() {
        if ( ! $this->verify_ajax_request( 'wpca_cleanup_database' ) ) {
            return;
        }
        
        $options = isset( $_POST['options'] ) ? ( function_exists( '\wp_unslash' ) ? \wp_unslash( $_POST['options'] ) : $_POST['options'] ) : array();
        
        $cleanup = new Cleanup();
        $result = $cleanup->run_database_cleanup( $options );
        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( $result );
        }
    }

    /**
     * Cleanup media.
     *
     * @since 1.7.15
     */
    public function cleanup_media() {
        if ( ! $this->verify_ajax_request( 'wpca_cleanup_media' ) ) {
            return;
        }
        
        $options = isset( $_POST['options'] ) ? wp_unslash( $_POST['options'] ) : array();
        
        $cleanup = new Cleanup();
        $result = $cleanup->run_media_cleanup( $options );
        wp_send_json_success( $result );
    }

    /**
     * Cleanup comments.
     *
     * @since 1.7.15
     */
    public function cleanup_comments() {
        if ( ! $this->verify_ajax_request( 'wpca_cleanup_comments' ) ) {
            return;
        }
        
        $options = isset( $_POST['options'] ) ? wp_unslash( $_POST['options'] ) : array();
        
        $cleanup = new Cleanup();
        $result = $cleanup->run_comments_cleanup( $options );
        wp_send_json_success( $result );
    }

    /**
     * Cleanup content.
     *
     * @since 1.7.15
     */
    public function cleanup_content() {
        if ( ! $this->verify_ajax_request( 'wpca_cleanup_content' ) ) {
            return;
        }
        
        $options = isset( $_POST['options'] ) ? wp_unslash( $_POST['options'] ) : array();
        
        $cleanup = new Cleanup();
        $result = $cleanup->run_content_cleanup( $options );
        wp_send_json_success( $result );
    }

    /**
     * Get cleanup statistics.
     *
     * @since 1.7.15
     */
    public function get_cleanup_stats() {
        if ( ! $this->verify_ajax_request( 'wpca_get_cleanup_stats' ) ) {
            return;
        }
        
        $cleanup = new Cleanup();
        $stats = $cleanup->get_cleanup_stats();
        wp_send_json_success( $stats );
    }

    /**
     * Save settings.
     *
     * @since 1.7.15
     */
    public function save_settings() {
        if ( ! $this->verify_ajax_request( 'wpca_save_settings' ) ) {
            return;
        }
        
        $settings = isset( $_POST['settings'] ) ? ( function_exists( '\wp_unslash' ) ? \wp_unslash( $_POST['settings'] ) : $_POST['settings'] ) : array();
        
        $settings_manager = new Settings();
        $result = wpca_update_settings( $settings );
        
        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Settings saved successfully', WPCA_TEXT_DOMAIN ) ) );
        } else {
            wp_send_json_error( __( 'Failed to save settings', WPCA_TEXT_DOMAIN ) );
        }
    }

    /**
     * Get settings.
     *
     * @since 1.7.15
     */
    public function get_settings() {
        if ( ! $this->verify_ajax_request( 'wpca_get_settings' ) ) {
            return;
        }
        
        $settings = wpca_get_settings();
        wp_send_json_success( $settings );
    }

    /**
     * Reset settings.
     *
     * @since 1.7.15
     */
    public function reset_settings() {
        if ( ! $this->verify_ajax_request( 'wpca_reset_settings' ) ) {
            return;
        }
        
        $reset = new Reset();
        $result = $reset->reset_settings();
        
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    /**
     * Get user roles.
     *
     * @since 1.7.15
     */
    public function get_user_roles() {
        if ( ! $this->verify_ajax_request( 'wpca_get_user_roles' ) ) {
            return;
        }
        
        $user_roles = new User_Roles();
        $roles = $user_roles->get_user_roles();
        wp_send_json_success( $roles );
    }

    /**
     * Update role capabilities.
     *
     * @since 1.7.15
     */
    public function update_role_capabilities() {
        if ( ! $this->verify_ajax_request( 'wpca_update_role_capabilities' ) ) {
            return;
        }
        
        $role_slug = isset( $_POST['role_slug'] ) ? sanitize_text_field( $_POST['role_slug'] ) : '';
        $capabilities = isset( $_POST['capabilities'] ) ? ( function_exists( '\wp_unslash' ) ? \wp_unslash( $_POST['capabilities'] ) : $_POST['capabilities'] ) : array();
        
        $user_roles = new User_Roles();
        $result = $user_roles->update_role_capabilities( $role_slug, $capabilities );
        wp_send_json_success( $result );
    }

    /**
     * Create new role.
     *
     * @since 1.7.15
     */
    public function create_role() {
        if ( ! $this->verify_ajax_request( 'wpca_create_role' ) ) {
            return;
        }
        
        $role_name = isset( $_POST['role_name'] ) ? sanitize_text_field( $_POST['role_name'] ) : '';
        $role_slug = isset( $_POST['role_slug'] ) ? sanitize_text_field( $_POST['role_slug'] ) : '';
        $capabilities = isset( $_POST['capabilities'] ) ? wp_unslash( $_POST['capabilities'] ) : array();
        
        $user_roles = new User_Roles();
        $result = $user_roles->create_role( $role_slug, $role_name, $capabilities );
        
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    /**
     * Delete role.
     *
     * @since 1.7.15
     */
    public function delete_role() {
        if ( ! $this->verify_ajax_request( 'wpca_delete_role' ) ) {
            return;
        }
        
        $role_slug = isset( $_POST['role_slug'] ) ? sanitize_text_field( $_POST['role_slug'] ) : '';
        
        $user_roles = new User_Roles();
        $result = $user_roles->delete_role( $role_slug );
        
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    /**
     * Duplicate role.
     *
     * @since 1.7.15
     */
    public function duplicate_role() {
        if ( ! $this->verify_ajax_request( 'wpca_duplicate_role' ) ) {
            return;
        }
        
        $role_slug = isset( $_POST['role_slug'] ) ? sanitize_text_field( $_POST['role_slug'] ) : '';
        $new_role_name = isset( $_POST['new_role_name'] ) ? sanitize_text_field( $_POST['new_role_name'] ) : '';
        $new_role_slug = isset( $_POST['new_role_slug'] ) ? sanitize_text_field( $_POST['new_role_slug'] ) : '';
        
        $user_roles = new User_Roles();
        $result = $user_roles->duplicate_role( $role_slug, $new_role_name, $new_role_slug );
        
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    /**
     * Get menu items.
     *
     * @since 1.7.15
     */
    public function get_menu_items() {
        if ( ! $this->verify_ajax_request( 'wpca_get_menu_items' ) ) {
            return;
        }
        
        $menu_manager = new Menu_Manager();
        $menu_items = $menu_manager->get_menu_items();
        wp_send_json_success( $menu_items );
    }

    /**
     * Save menu items.
     *
     * @since 1.7.15
     */
    public function save_menu_items() {
        if ( ! $this->verify_ajax_request( 'wpca_save_menu_items' ) ) {
            return;
        }
        
        $menu_items = isset( $_POST['menu_items'] ) ? ( function_exists( '\wp_unslash' ) ? \wp_unslash( $_POST['menu_items'] ) : $_POST['menu_items'] ) : array();
        
        $menu_manager = new Menu_Manager();
        $result = $menu_manager->save_menu_items( $menu_items );
        
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    /**
     * Optimize database.
     *
     * @since 1.7.15
     */
    public function optimize_database() {
        if ( ! $this->verify_ajax_request( 'wpca_optimize_database' ) ) {
            return;
        }
        
        $database = new Database();
        $result = $database->optimize_database();
        wp_send_json_success( $result );
    }

    /**
     * Clear cache.
     *
     * @since 1.7.15
     */
    public function clear_cache() {
        if ( ! $this->verify_ajax_request( 'wpca_clear_cache' ) ) {
            return;
        }
        
        $performance = new Performance();
        $result = $performance->clear_cache();
        wp_send_json_success( $result );
    }

    /**
     * Get performance statistics.
     *
     * @since 1.7.15
     */
    public function get_performance_stats() {
        if ( ! $this->verify_ajax_request( 'wpca_get_performance_stats' ) ) {
            return;
        }
        
        $performance = new Performance();
        $stats = $performance->get_performance_stats();
        wp_send_json_success( $stats );
    }

    /**
     * Reset plugin.
     *
     * @since 1.7.15
     */
    public function reset_plugin() {
        if ( ! $this->verify_ajax_request( 'wpca_reset_plugin' ) ) {
            return;
        }
        
        $reset = new Reset();
        $result = $reset->reset_plugin();
        
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    /**
     * Get database information.
     *
     * @since 1.7.15
     */
    public function get_database_info() {
        if ( ! $this->verify_ajax_request( 'wpca_get_database_info' ) ) {
            return;
        }
        
        $database = new Database();
        $info = $database->get_database_info();
        wp_send_json_success( $info );
    }

    /**
     * Backup database.
     *
     * @since 1.7.15
     */
    public function backup_database() {
        if ( ! $this->verify_ajax_request( 'wpca_backup_database' ) ) {
            return;
        }
        
        $options = isset( $_POST['options'] ) ? wp_unslash( $_POST['options'] ) : array();
        
        $database = new Database();
        $result = $database->backup_database( $options );
        
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    /**
     * Restore database.
     *
     * @since 1.7.15
     */
    public function restore_database() {
        if ( ! $this->verify_ajax_request( 'wpca_restore_database' ) ) {
            return;
        }
        
        $backup_file = isset( $_POST['backup_file'] ) ? sanitize_text_field( $_POST['backup_file'] ) : '';
        
        $database = new Database();
        $result = $database->restore_database( $backup_file );
        
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    /**
     * Get database backups.
     *
     * @since 1.7.15
     */
    public function get_database_backups() {
        if ( ! $this->verify_ajax_request( 'wpca_get_database_backups' ) ) {
            return;
        }
        
        $database = new Database();
        $backups = $database->get_database_backups();
        wp_send_json_success( $backups );
    }

    /**
     * Delete database backup.
     *
     * @since 1.7.15
     */
    public function delete_database_backup() {
        if ( ! $this->verify_ajax_request( 'wpca_delete_database_backup' ) ) {
            return;
        }
        
        $backup_file = isset( $_POST['backup_file'] ) ? sanitize_text_field( $_POST['backup_file'] ) : '';
        
        $database = new Database();
        $result = $database->delete_database_backup( $backup_file );
        
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    /**
     * Get resources statistics.
     *
     * @since 1.7.15
     */
    public function get_resources_stats() {
        if ( ! $this->verify_ajax_request( 'wpca_get_resources_stats' ) ) {
            return;
        }
        
        $resources = new Resources();
        $stats = $resources->get_resources_stats();
        wp_send_json_success( $stats );
    }

    /**
     * Get resource details.
     *
     * @since 1.7.15
     */
    public function get_resource_details() {
        if ( ! $this->verify_ajax_request( 'wpca_get_resource_details' ) ) {
            return;
        }
        
        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
        
        $resources = new Resources();
        $details = $resources->get_resource_details( $type );
        wp_send_json_success( $details );
    }

    /**
     * Optimize resources.
     *
     * @since 1.7.15
     */
    public function optimize_resources() {
        if ( ! $this->verify_ajax_request( 'wpca_optimize_resources' ) ) {
            return;
        }
        
        $options = isset( $_POST['options'] ) ? wp_unslash( $_POST['options'] ) : array();
        
        $resources = new Resources();
        $result = $resources->optimize_resources( $options );
        wp_send_json_success( $result );
    }

    /**
     * Disable resource.
     *
     * @since 1.7.15
     */
    public function disable_resource() {
        if ( ! $this->verify_ajax_request( 'wpca_disable_resource' ) ) {
            return;
        }
        
        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
        $handle = isset( $_POST['handle'] ) ? sanitize_text_field( $_POST['handle'] ) : '';
        
        $resources = new Resources();
        $result = $resources->disable_resource( $type, $handle );
        wp_send_json_success( $result );
    }

    /**
     * Enable resource.
     *
     * @since 1.7.15
     */
    public function enable_resource() {
        if ( ! $this->verify_ajax_request( 'wpca_enable_resource' ) ) {
            return;
        }
        
        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
        $handle = isset( $_POST['handle'] ) ? sanitize_text_field( $_POST['handle'] ) : '';
        
        $resources = new Resources();
        $result = $resources->enable_resource( $type, $handle );
        wp_send_json_success( $result );
    }

    /**
     * Save menu customizer settings.
     *
     * @since 1.7.15
     */
    public function save_menu_customizer_settings() {
        if ( ! $this->verify_ajax_request( 'wpca_save_menu_customizer_settings' ) ) {
            return;
        }
        
        $settings = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
        
        $menu_customizer = new Menu_Customizer();
        $result = $menu_customizer->save_settings( $settings );
        
        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Menu customizer settings saved successfully', WPCA_TEXT_DOMAIN ) ) );
        } else {
            wp_send_json_error( __( 'Failed to save menu customizer settings', WPCA_TEXT_DOMAIN ) );
        }
    }

    /**
     * Get menu customizer settings.
     *
     * @since 1.7.15
     */
    public function get_menu_customizer_settings() {
        if ( ! $this->verify_ajax_request( 'wpca_get_menu_customizer_settings' ) ) {
            return;
        }
        
        $menu_customizer = new Menu_Customizer();
        $settings = $menu_customizer->get_settings();
        wp_send_json_success( $settings );
    }

    /**
     * Reset menu customizer settings.
     *
     * @since 1.7.15
     */
    public function reset_menu_customizer_settings() {
        if ( ! $this->verify_ajax_request( 'wpca_reset_menu_customizer_settings' ) ) {
            return;
        }
        
        $menu_customizer = new Menu_Customizer();
        $result = $menu_customizer->reset_settings();
        
        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Menu customizer settings reset to default', WPCA_TEXT_DOMAIN ) ) );
        } else {
            wp_send_json_error( __( 'Failed to reset menu customizer settings', WPCA_TEXT_DOMAIN ) );
        }
    }

    /**
     * Save database settings.
     *
     * @since 1.7.15
     */
    public function save_database_settings() {
        if ( ! $this->verify_ajax_request( 'wpca_save_database_settings' ) ) {
            return;
        }
        
        $settings = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
        
        $database_settings = new Database_Settings();
        $result = $database_settings->save_settings( $settings );
        
        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Database settings saved successfully', WPCA_TEXT_DOMAIN ) ) );
        } else {
            wp_send_json_error( __( 'Failed to save database settings', WPCA_TEXT_DOMAIN ) );
        }
    }

    /**
     * Get database settings.
     *
     * @since 1.7.15
     */
    public function get_database_settings() {
        if ( ! $this->verify_ajax_request( 'wpca_get_database_settings' ) ) {
            return;
        }
        
        $database_settings = new Database_Settings();
        $settings = $database_settings->get_settings();
        wp_send_json_success( $settings );
    }

    /**
     * Reset database settings.
     *
     * @since 1.7.15
     */
    public function reset_database_settings() {
        if ( ! $this->verify_ajax_request( 'wpca_reset_database_settings' ) ) {
            return;
        }
        
        $database_settings = new Database_Settings();
        $result = $database_settings->reset_settings();
        
        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Database settings reset to default', WPCA_TEXT_DOMAIN ) ) );
        } else {
            wp_send_json_error( __( 'Failed to reset database settings', WPCA_TEXT_DOMAIN ) );
        }
    }

    /**
     * Save performance settings.
     *
     * @since 1.7.15
     */
    public function save_performance_settings() {
        if ( ! $this->verify_ajax_request( 'wpca_save_performance_settings' ) ) {
            return;
        }
        
        $settings = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
        
        $performance_settings = new Performance_Settings();
        $result = $performance_settings->save_settings( $settings );
        
        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Performance settings saved successfully', WPCA_TEXT_DOMAIN ) ) );
        } else {
            wp_send_json_error( __( 'Failed to save performance settings', WPCA_TEXT_DOMAIN ) );
        }
    }

    /**
     * Get performance settings.
     *
     * @since 1.7.15
     */
    public function get_performance_settings() {
        if ( ! $this->verify_ajax_request( 'wpca_get_performance_settings' ) ) {
            return;
        }
        
        $performance_settings = new Performance_Settings();
        $settings = $performance_settings->get_settings();
        wp_send_json_success( $settings );
    }

    /**
     * Reset performance settings.
     *
     * @since 1.7.15
     */
    public function reset_performance_settings() {
        if ( ! $this->verify_ajax_request( 'wpca_reset_performance_settings' ) ) {
            return;
        }
        
        $performance_settings = new Performance_Settings();
        $result = $performance_settings->reset_settings();
        
        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Performance settings reset to default', WPCA_TEXT_DOMAIN ) ) );
        } else {
            wp_send_json_error( __( 'Failed to reset performance settings', WPCA_TEXT_DOMAIN ) );
        }
    }
}