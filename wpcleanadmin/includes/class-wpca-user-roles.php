<?php
/**
 * User Roles class for WP Clean Admin plugin
 *
 * @package WPCleanAdmin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

namespace WPCleanAdmin;

/**
 * User_Roles class
 */
class User_Roles {
    
    /**
     * Singleton instance
     *
     * @var User_Roles
     */
    private static $instance;
    
    /**
     * Get singleton instance
     *
     * @return User_Roles
     */
    public static function get_instance() {
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
     * Initialize the user roles module
     */
    public function init() {
        // Add user roles hooks
        add_action( 'init', array( $this, 'register_custom_roles' ) );
    }
    
    /**
     * Register custom roles
     */
    public function register_custom_roles() {
        // Load settings
        $settings = \wpca_get_settings();
        
        // Register custom roles based on settings
        if ( isset( $settings['user_roles'] ) && isset( $settings['user_roles']['custom_roles'] ) ) {
            foreach ( $settings['user_roles']['custom_roles'] as $role_slug => $role_data ) {
                // Register custom role
                \add_role( $role_slug, $role_data['name'], $role_data['capabilities'] );
            }
        }
    }
    
    /**
     * Get user roles
     *
     * @return array User roles
     */
    public function get_user_roles() {
        // Get all user roles
        global $wp_roles;
        
        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new \WP_Roles();
        }
        
        return $wp_roles->roles;
    }
    
    /**
     * Update role capabilities
     *
     * @param string $role_slug Role slug
     * @param array $capabilities Capabilities to update
     * @return bool Update result
     */
    public function update_role_capabilities( $role_slug, $capabilities ) {
        // Get role object
        $role = \get_role( $role_slug );
        
        if ( ! $role ) {
            return false;
        }
        
        // Update capabilities
        foreach ( $capabilities as $capability => $grant ) {
            if ( $grant ) {
                $role->add_cap( $capability );
            } else {
                $role->remove_cap( $capability );
            }
        }
        
        return true;
    }
    
    /**
     * Create new role
     *
     * @param string $role_slug Role slug
     * @param string $role_name Role name
     * @param array $capabilities Capabilities
     * @return array Create result
     */
    public function create_role( $role_slug, $role_name, $capabilities = array() ) {
        $result = array(
            'success' => false,
            'message' => \__( 'Failed to create role', WPCA_TEXT_DOMAIN )
        );
        
        // Check if role already exists
        if ( \get_role( $role_slug ) ) {
            $result['message'] = \__( 'Role already exists', WPCA_TEXT_DOMAIN );
            return $result;
        }
        
        // Create new role
        $role = \add_role( $role_slug, $role_name, $capabilities );
        
        if ( $role ) {
            $result['success'] = true;
            $result['message'] = \__( 'Role created successfully', WPCA_TEXT_DOMAIN );
        }
        
        return $result;
    }
    
    /**
     * Delete role
     *
     * @param string $role_slug Role slug
     * @return array Delete result
     */
    public function delete_role( $role_slug ) {
        $result = array(
            'success' => false,
            'message' => \__( 'Failed to delete role', WPCA_TEXT_DOMAIN )
        );
        
        // Check if role exists
        if ( ! \get_role( $role_slug ) ) {
            $result['message'] = \__( 'Role does not exist', WPCA_TEXT_DOMAIN );
            return $result;
        }
        
        // Delete role
        if ( \remove_role( $role_slug ) ) {
            $result['success'] = true;
            $result['message'] = \__( 'Role deleted successfully', WPCA_TEXT_DOMAIN );
        }
        
        return $result;
    }
    
    /**
     * Duplicate role
     *
     * @param string $role_slug Role slug to duplicate
     * @param string $new_role_name New role name
     * @param string $new_role_slug New role slug
     * @return array Duplicate result
     */
    public function duplicate_role( $role_slug, $new_role_name, $new_role_slug ) {
        $result = array(
            'success' => false,
            'message' => \__( 'Failed to duplicate role', WPCA_TEXT_DOMAIN )
        );
        
        // Check if source role exists
        $source_role = \get_role( $role_slug );
        if ( ! $source_role ) {
            $result['message'] = \__( 'Source role does not exist', WPCA_TEXT_DOMAIN );
            return $result;
        }
        
        // Check if new role already exists
        if ( \get_role( $new_role_slug ) ) {
            $result['message'] = \__( 'New role already exists', WPCA_TEXT_DOMAIN );
            return $result;
        }
        
        // Create new role with same capabilities
        $new_role = \add_role( $new_role_slug, $new_role_name, $source_role->capabilities );
        
        if ( $new_role ) {
            $result['success'] = true;
            $result['message'] = \__( 'Role duplicated successfully', WPCA_TEXT_DOMAIN );
        }
        
        return $result;
    }
    
    /**
     * Get role capabilities
     *
     * @param string $role_slug Role slug
     * @return array Role capabilities
     */
    public function get_role_capabilities( $role_slug ) {
        // Get role object
        $role = \get_role( $role_slug );
        
        if ( ! $role ) {
            return array();
        }
        
        return $role->capabilities;
    }
    
    /**
     * Reset role capabilities to default
     *
     * @param string $role_slug Role slug
     * @return bool Reset result
     */
    public function reset_role_capabilities( $role_slug ) {
        // Get default capabilities for the role
        $default_capabilities = $this->get_default_role_capabilities( $role_slug );
        
        if ( empty( $default_capabilities ) ) {
            return false;
        }
        
        // Update role capabilities
        return $this->update_role_capabilities( $role_slug, $default_capabilities );
    }
    
    /**
     * Get default role capabilities
     *
     * @param string $role_slug Role slug
     * @return array Default capabilities
     */
    private function get_default_role_capabilities( $role_slug ) {
        // Default capabilities for WordPress roles
        $default_capabilities = array(
            'administrator' => array(
                'switch_themes' => true,
                'edit_themes' => true,
                'activate_plugins' => true,
                'edit_plugins' => true,
                'edit_users' => true,
                'edit_files' => true,
                'manage_options' => true,
                'moderate_comments' => true,
                'manage_categories' => true,
                'manage_links' => true,
                'upload_files' => true,
                'import' => true,
                'unfiltered_html' => true,
                'edit_posts' => true,
                'edit_others_posts' => true,
                'edit_published_posts' => true,
                'publish_posts' => true,
                'edit_pages' => true,
                'read' => true,
                'level_10' => true,
                'level_9' => true,
                'level_8' => true,
                'level_7' => true,
                'level_6' => true,
                'level_5' => true,
                'level_4' => true,
                'level_3' => true,
                'level_2' => true,
                'level_1' => true,
                'level_0' => true,
                'edit_others_pages' => true,
                'edit_published_pages' => true,
                'publish_pages' => true,
                'delete_pages' => true,
                'delete_others_pages' => true,
                'delete_published_pages' => true,
                'delete_posts' => true,
                'delete_others_posts' => true,
                'delete_published_posts' => true,
                'delete_private_posts' => true,
                'edit_private_posts' => true,
                'read_private_posts' => true,
                'delete_private_pages' => true,
                'edit_private_pages' => true,
                'read_private_pages' => true,
                'delete_users' => true,
                'create_users' => true,
                'unfiltered_upload' => true,
                'edit_dashboard' => true,
                'update_plugins' => true,
                'delete_plugins' => true,
                'install_plugins' => true,
                'update_themes' => true,
                'install_themes' => true,
                'update_core' => true,
                'list_users' => true,
                'remove_users' => true,
                'promote_users' => true,
                'edit_theme_options' => true,
                'delete_themes' => true,
                'export' => true,
            ),
            'editor' => array(
                'moderate_comments' => true,
                'manage_categories' => true,
                'manage_links' => true,
                'upload_files' => true,
                'unfiltered_html' => true,
                'edit_posts' => true,
                'edit_others_posts' => true,
                'edit_published_posts' => true,
                'publish_posts' => true,
                'edit_pages' => true,
                'read' => true,
                'level_7' => true,
                'level_6' => true,
                'level_5' => true,
                'level_4' => true,
                'level_3' => true,
                'level_2' => true,
                'level_1' => true,
                'level_0' => true,
                'edit_others_pages' => true,
                'edit_published_pages' => true,
                'publish_pages' => true,
                'delete_pages' => true,
                'delete_others_pages' => true,
                'delete_published_pages' => true,
                'delete_posts' => true,
                'delete_others_posts' => true,
                'delete_published_posts' => true,
                'delete_private_posts' => true,
                'edit_private_posts' => true,
                'read_private_posts' => true,
                'delete_private_pages' => true,
                'edit_private_pages' => true,
                'read_private_pages' => true,
            ),
            'author' => array(
                'upload_files' => true,
                'edit_posts' => true,
                'edit_published_posts' => true,
                'publish_posts' => true,
                'read' => true,
                'level_2' => true,
                'level_1' => true,
                'level_0' => true,
                'delete_posts' => true,
                'delete_published_posts' => true,
            ),
            'contributor' => array(
                'edit_posts' => true,
                'read' => true,
                'level_1' => true,
                'level_0' => true,
                'delete_posts' => true,
            ),
            'subscriber' => array(
                'read' => true,
                'level_0' => true,
            ),
        );
        
        return isset( $default_capabilities[$role_slug] ) ? $default_capabilities[$role_slug] : array();
    }
}