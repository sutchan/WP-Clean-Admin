<?php
/**
 * WPCleanAdmin User Roles Data
 *
 * 承载角色默认能力清单与角色 CRUD 操作，从 User_Roles 主类抽取。
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
 * 用户角色数据/操作类
 */
class User_Roles_Data {

    /**
     * Get default role capabilities
     *
     * @param string $role_slug Role slug
     * @return array Default capabilities
     */
    public function get_default_role_capabilities( string $role_slug ): array {
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

        return isset( $default_capabilities[ $role_slug ] ) ? $default_capabilities[ $role_slug ] : array();
    }

    /**
     * Create new role
     *
     * @param string $role_slug
     * @param string $role_name
     * @param array  $capabilities
     * @return array Create result
     */
    public function create_role( string $role_slug, string $role_name, array $capabilities = array() ): array {
        $result = array(
            'success' => false,
            'message' => \__( 'Failed to create role', \WPCA_TEXT_DOMAIN )
        );

        // Check if role already exists
        if ( function_exists( 'get_role' ) && \get_role( $role_slug ) ) {
            $result['message'] = \__( 'Role already exists', \WPCA_TEXT_DOMAIN );
            return $result;
        }

        // Create new role
        $role = ( function_exists( 'add_role' ) ? \add_role( $role_slug, $role_name, $capabilities ) : false );

        if ( $role ) {
            $result['success'] = true;
            $result['message'] = \__( 'Role created successfully', \WPCA_TEXT_DOMAIN );
        }

        return $result;
    }

    /**
     * Delete role
     *
     * @param string $role_slug
     * @return array Delete result
     */
    public function delete_role( $role_slug ) {
        $result = array(
            'success' => false,
            'message' => \__( 'Failed to delete role', \WPCA_TEXT_DOMAIN )
        );

        // Check if role exists
        if ( ! ( function_exists( 'get_role' ) && \get_role( $role_slug ) ) ) {
            $result['message'] = \__( 'Role does not exist', \WPCA_TEXT_DOMAIN );
            return $result;
        }

        // Delete role
        if ( function_exists( '\remove_role' ) && \remove_role( $role_slug ) ) {
            $result['success'] = true;
            $result['message'] = \__( 'Role deleted successfully', \WPCA_TEXT_DOMAIN );
        }

        return $result;
    }

    /**
     * Duplicate role
     *
     * @param string $role_slug
     * @param string $new_role_name
     * @param string $new_role_slug
     * @return array Duplicate result
     */
    public function duplicate_role( $role_slug, $new_role_name, $new_role_slug ) {
        $result = array(
            'success' => false,
            'message' => \__( 'Failed to duplicate role', \WPCA_TEXT_DOMAIN )
        );

        // Check if source role exists
        $source_role = ( function_exists( 'get_role' ) ? \get_role( $role_slug ) : false );
        if ( ! $source_role ) {
            $result['message'] = \__( 'Source role does not exist', \WPCA_TEXT_DOMAIN );
            return $result;
        }

        // Check if new role already exists
        if ( function_exists( 'get_role' ) && \get_role( $new_role_slug ) ) {
            $result['message'] = \__( 'New role already exists', \WPCA_TEXT_DOMAIN );
            return $result;
        }

        // Create new role with same capabilities
        $new_role = ( function_exists( 'add_role' ) ? \add_role( $new_role_slug, $new_role_name, $source_role->capabilities ) : false );

        if ( $new_role ) {
            $result['success'] = true;
            $result['message'] = \__( 'Role duplicated successfully', \WPCA_TEXT_DOMAIN );
        }

        return $result;
    }
}
