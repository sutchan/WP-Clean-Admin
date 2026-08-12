<?php
/**
 * WPCleanAdmin Permissions Class
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

require_once __DIR__ . '/class-wpca-permissions-checker.php';
require_once __DIR__ . '/class-wpca-permissions-restrict.php';

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Declare WordPress functions for IDE compatibility
if ( ! function_exists( 'get_current_user_id' ) ) {
    function get_current_user_id() {}
}
if ( ! function_exists( 'get_user_by' ) ) {
    function get_user_by() {}
}
if ( ! function_exists( 'user_can' ) ) {
    function user_can() {}
}
if ( ! function_exists( 'wp_redirect' ) ) {
    function wp_redirect() {}
}

/**
 * Permissions class
 */
class Permissions {

    /**
     * Singleton instance
     *
     * @var Permissions
     */
    private static $instance;

    /**
     * 功能/用户权限计算
     *
     * @var Permissions_Checker
     */
    private $checker;

    /**
     * 后台访问/页面限制
     *
     * @var Permissions_Restrict
     */
    private $restrict;

    /**
     * Get singleton instance
     *
     * @return Permissions
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
        $this->checker  = new Permissions_Checker();
        $this->restrict = new Permissions_Restrict();
        $this->init();
    }

    /**
     * Initialize the permissions module
     */
    public function init() {
        // Add permissions hooks
        if ( function_exists( 'add_filter' ) ) {
            \add_filter( 'user_has_cap', array( $this, 'filter_user_capabilities' ), 10, 3 );
        }
    }

    /**
     * Filter user capabilities
     *
     * @param array $allcaps All capabilities assigned to the user
     * @return array Modified capabilities
     * @uses wpca_get_settings() To retrieve plugin settings
     */
    public function filter_user_capabilities( $allcaps ) {
        // Load settings
        $settings = \wpca_get_settings();

        // Apply permission filters based on settings
        if ( isset( $settings['permissions'] ) ) {
            // Restrict access to certain features
            if ( isset( $settings['permissions']['restrict_features'] ) && $settings['permissions']['restrict_features'] ) {
                // Restrict access to specific capabilities
                $restricted_caps = array(
                    'manage_options',
                    'edit_theme_options',
                    'install_plugins',
                    'update_plugins',
                    'delete_plugins',
                    'install_themes',
                    'update_themes',
                    'delete_themes',
                    'import',
                    'export'
                );

                // Remove restricted capabilities for non-administrators
                if ( ! isset( $allcaps['administrator'] ) || ! $allcaps['administrator'] ) {
                    foreach ( $restricted_caps as $cap ) {
                        if ( isset( $allcaps[ $cap ] ) ) {
                            unset( $allcaps[ $cap ] );
                        }
                    }
                }
            }
        }

        return $allcaps;
    }

    /**
     * Check if user has permission to access a feature (delegated)
     *
     * @param string $feature
     * @param int    $user_id
     * @return bool
     */
    public function has_feature_permission( string $feature, ?int $user_id = null ): bool {
        return $this->checker->has_feature_permission( $feature, $user_id );
    }

    /**
     * Get user permissions (delegated)
     *
     * @param int $user_id
     * @return array
     */
    public function get_user_permissions( $user_id = null ) {
        return $this->checker->get_user_permissions( $user_id );
    }

    /**
     * Restrict access to admin pages (delegated)
     */
    public function restrict_admin_access(): void {
        $this->restrict->restrict_admin_access();
    }

    /**
     * Restrict access to specific admin pages (delegated)
     */
    public function restrict_specific_admin_pages(): void {
        $this->restrict->restrict_specific_admin_pages();
    }
}
