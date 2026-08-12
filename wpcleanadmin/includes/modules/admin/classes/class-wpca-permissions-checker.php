<?php
/**
 * WPCleanAdmin Permissions Checker
 *
 * 承载功能权限与用户权限的计算逻辑，从 Permissions 主类抽取。
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 权限计算类（功能/用户权限）
 */
class Permissions_Checker {

    /**
     * Check if user has permission to access a feature
     *
     * @param string $feature Feature name
     * @param int    $user_id User ID
     * @return bool Permission result
     */
    public function has_feature_permission( string $feature, ?int $user_id = null ): bool {
        // Get user ID if not provided
        if ( $user_id === null ) {
            $user_id = ( function_exists( 'get_current_user_id' ) ? \get_current_user_id() : 0 );
        }

        // Get user object
        $user = ( function_exists( 'get_user_by' ) ? \get_user_by( 'id', $user_id ) : false );
        if ( ! $user ) {
            return false;
        }

        // Load settings
        $settings = \wpca_get_settings();

        // Check if feature is restricted
        if ( isset( $settings['permissions']['feature_restrictions'] ) && isset( $settings['permissions']['feature_restrictions'][ $feature ] ) ) {
            $restriction = $settings['permissions']['feature_restrictions'][ $feature ];

            // Check if user has required role
            if ( isset( $restriction['roles'] ) && ! empty( $restriction['roles'] ) ) {
                $user_roles = $user->roles;
                $has_role   = array_intersect( $user_roles, $restriction['roles'] );

                if ( empty( $has_role ) ) {
                    return false;
                }
            }

            // Check if user has required capability
            if ( isset( $restriction['capability'] ) && ! empty( $restriction['capability'] ) ) {
                if ( ! ( function_exists( 'user_can' ) && \user_can( $user_id, $restriction['capability'] ) ) ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get user permissions
     *
     * @param int $user_id User ID
     * @return array User permissions
     */
    public function get_user_permissions( $user_id = null ) {
        // Get user ID if not provided
        if ( $user_id === null ) {
            $user_id = ( function_exists( 'get_current_user_id' ) ? \get_current_user_id() : 0 );
        }

        // Get user object
        $user = ( function_exists( 'get_user_by' ) ? \get_user_by( 'id', $user_id ) : false );
        if ( ! $user ) {
            return array();
        }

        // Get user capabilities
        $capabilities = $user->allcaps;

        // Get user roles
        $roles = $user->roles;

        // Load settings
        $settings = \wpca_get_settings();

        // Get feature permissions
        $feature_permissions = array();

        if ( isset( $settings['permissions']['feature_restrictions'] ) ) {
            foreach ( $settings['permissions']['feature_restrictions'] as $feature => $restriction ) {
                $feature_permissions[ $feature ] = $this->has_feature_permission( $feature, $user_id );
            }
        }

        return array(
            'capabilities'        => $capabilities,
            'roles'               => $roles,
            'feature_permissions' => $feature_permissions,
        );
    }
}
