<?php
/**
 * WPCleanAdmin User Roles Class
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

require_once __DIR__ . '/class-wpca-user-roles-data.php';

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Declare WordPress functions and classes for IDE compatibility
if ( ! function_exists( 'add_role' ) ) {
    function add_role() {}
}
if ( ! function_exists( 'get_role' ) ) {
    function get_role() {}
}
if ( ! function_exists( 'remove_role' ) ) {
    function remove_role() {}
}
if ( ! function_exists( 'add_action' ) ) {
    function add_action() {}
}

// Declare WP_Roles class for IDE compatibility
if ( ! class_exists( 'WP_Roles' ) ) {
    class WP_Roles {
        public $roles = array();
    }
}

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
     * 角色数据/操作处理器
     *
     * @var User_Roles_Data
     */
    private $data;

    /**
     * Get singleton instance
     *
     * @return User_Roles
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
        $this->data = new User_Roles_Data();
        $this->init();
    }

    /**
     * Initialize the user roles module
     */
    public function init() {
        // Add user roles hooks
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'init', array( $this, 'register_custom_roles' ) );
        }
    }

    /**
     * Register custom roles
     *
     * @uses wpca_get_settings() To retrieve plugin settings
     */
    public function register_custom_roles() {
        // Load settings
        $settings = \wpca_get_settings();

        // Register custom roles based on settings
        if ( isset( $settings['user_roles'] ) && isset( $settings['user_roles']['custom_roles'] ) ) {
            foreach ( $settings['user_roles']['custom_roles'] as $role_slug => $role_data ) {
                // Register custom role
                if ( function_exists( '\add_role' ) ) {
                    \add_role( $role_slug, $role_data['name'], $role_data['capabilities'] );
                }
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
     * @param array  $capabilities Capabilities to update
     * @return bool Update result
     */
    public function update_role_capabilities( string $role_slug, array $capabilities ): bool {
        // Get role object
        $role = ( function_exists( '\get_role' ) ? \get_role( $role_slug ) : false );

        if ( ! $role ) {
            return false;
        }

        // Update capabilities
        foreach ( $capabilities as $capability => $grant ) {
            if ( $grant ) {
                if ( method_exists( $role, 'add_cap' ) ) {
                    $role->add_cap( $capability );
                }
            } else {
                if ( method_exists( $role, 'remove_cap' ) ) {
                    $role->remove_cap( $capability );
                }
            }
        }

        return true;
    }

    /**
     * Get role capabilities
     *
     * @param string $role_slug Role slug
     * @return array Role capabilities
     */
    public function get_role_capabilities( $role_slug ) {
        // Get role object
        $role = ( function_exists( 'get_role' ) ? \get_role( $role_slug ) : false );

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
        $default_capabilities = $this->data->get_default_role_capabilities( $role_slug );

        if ( empty( $default_capabilities ) ) {
            return false;
        }

        // Update role capabilities
        return $this->update_role_capabilities( $role_slug, $default_capabilities );
    }

    /**
     * Create new role (delegated)
     *
     * @param string $role_slug
     * @param string $role_name
     * @param array  $capabilities
     * @return array
     */
    public function create_role( string $role_slug, string $role_name, array $capabilities = array() ): array {
        return $this->data->create_role( $role_slug, $role_name, $capabilities );
    }

    /**
     * Delete role (delegated)
     *
     * @param string $role_slug
     * @return array
     */
    public function delete_role( $role_slug ) {
        return $this->data->delete_role( $role_slug );
    }

    /**
     * Duplicate role (delegated)
     *
     * @param string $role_slug
     * @param string $new_role_name
     * @param string $new_role_slug
     * @return array
     */
    public function duplicate_role( $role_slug, $new_role_name, $new_role_slug ) {
        return $this->data->duplicate_role( $role_slug, $new_role_name, $new_role_slug );
    }
}
