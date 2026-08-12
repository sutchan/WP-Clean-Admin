<?php
/**
 * WPCleanAdmin Menu Customizer Render
 *
 * 承载 admin bar 定制与结构导出/导入逻辑，从 Menu_Customizer 主类抽取。
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

if ( ! function_exists( 'wp_get_current_user' ) ) {
    function wp_get_current_user() {}
}
if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode() {}
}
if ( ! function_exists( 'json_decode' ) ) {
    function json_decode() {}
}
if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field() {}
}

/**
 * 菜单渲染/导出类
 */
class Menu_Customizer_Render {

    /**
     * Customize admin bar
     *
     * @global array $wp_admin_bar
     */
    public function customize_admin_bar(): void {
        global $wp_admin_bar;

        $settings = ( function_exists( '\wpca_get_settings' ) ? \wpca_get_settings() : array() );

        if ( ! isset( $wp_admin_bar ) || ! is_object( $wp_admin_bar ) ) {
            return;
        }

        // Customize admin bar items based on settings
        if ( isset( $settings['admin_bar_items'] ) ) {
            foreach ( $settings['admin_bar_items'] as $item_id => $item_settings ) {
                // Hide admin bar item
                if ( isset( $item_settings['hidden'] ) && $item_settings['hidden'] ) {
                    if ( method_exists( $wp_admin_bar, 'remove_node' ) ) {
                        $wp_admin_bar->remove_node( $item_id );
                    }
                }

                // Customize admin bar item title
                if ( isset( $item_settings['title'] ) && ! empty( $item_settings['title'] ) ) {
                    if ( method_exists( $wp_admin_bar, 'add_node' ) ) {
                        $wp_admin_bar->add_node(
                            array(
                                'id'    => $item_id,
                                'title' => $item_settings['title']
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * Get admin menu structure
     *
     * @return array
     * @global array $menu
     * @global array $submenu
     */
    public function get_admin_menu_structure(): array {
        global $menu, $submenu;

        $structure = array();

        if ( ! isset( $menu ) ) {
            return $structure;
        }

        foreach ( $menu as $menu_item ) {
            if ( empty( $menu_item[0] ) || $menu_item[0] === '-' ) {
                continue;
            }

            $menu_slug = $menu_item[2];
            $sub_items = array();

            if ( isset( $submenu[ $menu_slug ] ) ) {
                foreach ( $submenu[ $menu_slug ] as $submenu_item ) {
                    $sub_items[] = array(
                        'title' => $submenu_item[0],
                        'slug'  => $submenu_item[2]
                    );
                }
            }

            $structure[] = array(
                'title'    => $menu_item[0],
                'slug'     => $menu_slug,
                'icon'     => $menu_item[6],
                'position' => $menu_item[5],
                'submenu'  => $sub_items
            );
        }

        return $structure;
    }

    /**
     * Get admin bar structure
     *
     * @return array
     * @global array $wp_admin_bar
     */
    public function get_admin_bar_structure(): array {
        global $wp_admin_bar;

        $structure = array();

        if ( ! isset( $wp_admin_bar ) || ! is_object( $wp_admin_bar ) || ! isset( $wp_admin_bar->nodes ) ) {
            return $structure;
        }

        foreach ( $wp_admin_bar->nodes as $node ) {
            $structure[] = array(
                'id'     => $node->id,
                'title'  => isset( $node->title ) ? $node->title : '',
                'parent' => isset( $node->parent ) ? $node->parent : '',
                'href'   => isset( $node->href ) ? $node->href : ''
            );
        }

        return $structure;
    }

    /**
     * Export menu customizer settings
     *
     * @return array
     */
    public function export_settings(): array {
        $settings = ( function_exists( '\wpca_get_settings' ) ? \wpca_get_settings() : array() );

        // Build the export data
        $export_data = array(
            'menu_items'      => isset( $settings['menu_items'] ) ? $settings['menu_items'] : array(),
            'submenu_items'   => isset( $settings['submenu_items'] ) ? $settings['submenu_items'] : array(),
            'admin_bar_items' => isset( $settings['admin_bar_items'] ) ? $settings['admin_bar_items'] : array(),
            'menu_order'      => isset( $settings['menu_order'] ) ? $settings['menu_order'] : array(),
            'menu_groups'     => isset( $settings['menu_groups'] ) ? $settings['menu_groups'] : array()
        );

        return $export_data;
    }

    /**
     * Import menu customizer settings
     *
     * @param array $import_data
     * @return bool
     */
    public function import_settings( array $import_data ): bool {
        // Validate import data
        if ( empty( $import_data ) || ! is_array( $import_data ) ) {
            return false;
        }

        $current_settings = ( function_exists( '\wpca_get_settings' ) ? \wpca_get_settings() : array() );

        // Merge imported settings
        $merged_settings = array_merge( $current_settings, $import_data );

        // Save settings
        return ( function_exists( '\update_option' ) ? \update_option( 'wpca_menu_customizer_settings', $merged_settings ) : false );
    }
}
