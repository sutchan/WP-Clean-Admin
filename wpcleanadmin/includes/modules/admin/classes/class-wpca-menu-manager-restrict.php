<?php
/**
 * WPCleanAdmin Menu Manager Restrict
 *
 * 承载后台菜单的移除/清理/角色限制逻辑，从 Menu_Manager 主类抽取。
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

if ( ! function_exists( 'add_action' ) ) {
    function add_action() {}
}
if ( ! function_exists( 'remove_meta_box' ) ) {
    function remove_meta_box() {}
}
if ( ! function_exists( 'remove_action' ) ) {
    function remove_action() {}
}

/**
 * 菜单限制类
 */
class Menu_Manager_Restrict {

    /**
     * Remove dashboard widgets based on settings
     */
    public function remove_dashboard_widgets_by_settings(): void {
        // Load settings
        $settings = \wpca_get_settings();

        if ( isset( $settings['menu']['dashboard_widgets'] ) && function_exists( '\remove_meta_box' ) ) {
            $widgets_to_remove = $settings['menu']['dashboard_widgets'];

            foreach ( $widgets_to_remove as $widget_id => $remove ) {
                if ( $remove ) {
                    \remove_meta_box( $widget_id, 'dashboard', 'normal' );
                    \remove_meta_box( $widget_id, 'dashboard', 'side' );
                    \remove_meta_box( $widget_id, 'dashboard', 'column3' );
                    \remove_meta_box( $widget_id, 'dashboard', 'column4' );
                }
            }
        }
    }

    /**
     * Apply role-based menu restrictions
     */
    public function apply_role_based_menu_restrictions(): void {
        global $menu, $submenu;

        // Get current user
        if ( ! function_exists( '\wp_get_current_user' ) ) {
            return;
        }

        $current_user = \wp_get_current_user();
        if ( ! $current_user || ! isset( $current_user->roles ) ) {
            return;
        }

        $user_roles = $current_user->roles;

        // Load settings
        $settings = \wpca_get_settings();

        // Check if role-based menu restrictions are enabled
        if ( ! isset( $settings['menu']['role_based_restrictions'] ) || ! $settings['menu']['role_based_restrictions'] ) {
            return;
        }

        // Get role-based menu restrictions
        $role_restrictions = isset( $settings['menu']['role_menu_restrictions'] ) ? $settings['menu']['role_menu_restrictions'] : array();

        // Menu items to remove for current user
        $menu_items_to_remove = array();

        // Check each role restriction
        foreach ( $role_restrictions as $role => $restrictions ) {
            if ( in_array( $role, $user_roles, true ) && isset( $restrictions['menu_items'] ) ) {
                $menu_items_to_remove = array_merge( $menu_items_to_remove, array_keys( array_filter( $restrictions['menu_items'] ) ) );
            }
        }

        // Remove duplicates
        $menu_items_to_remove = array_unique( $menu_items_to_remove );

        // Remove top-level menu items
        foreach ( $menu as $key => $menu_item ) {
            if ( isset( $menu_item[2] ) && in_array( $menu_item[2], $menu_items_to_remove, true ) ) {
                unset( $menu[ $key ] );
            }
        }

        // Remove submenu items
        foreach ( $submenu as $parent_slug => $submenu_items ) {
            foreach ( $submenu_items as $key => $submenu_item ) {
                if ( isset( $submenu_item[2] ) && in_array( $submenu_item[2], $menu_items_to_remove, true ) ) {
                    unset( $submenu[ $parent_slug ][ $key ] );
                }
            }
        }
    }

    /**
     * Clean up admin menu (delegate for cleanup_admin_menu orchestration)
     *
     * @return array Cleanup result
     */
    public function cleanup(): array {
        $result = array(
            'success' => true,
            'message' => \__( 'Admin menu cleaned successfully', \WPCA_TEXT_DOMAIN )
        );

        // Load settings
        $settings = \wpca_get_settings();

        if ( isset( $settings['menu'] ) ) {
            // Remove dashboard widgets
            if ( isset( $settings['menu']['remove_dashboard_widgets'] ) && $settings['menu']['remove_dashboard_widgets'] ) {
                $this->remove_dashboard_widgets_by_settings();
            }

            // Apply role-based restrictions
            $this->apply_role_based_menu_restrictions();
        }

        return $result;
    }
}
