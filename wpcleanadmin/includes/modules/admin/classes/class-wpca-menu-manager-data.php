<?php
/**
 * WPCleanAdmin Menu Manager Data
 *
 * 承载菜单项的读取/保存/排序等纯数据逻辑，从 Menu_Manager 主类抽取。
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

if ( ! function_exists( 'update_option' ) ) {
    function update_option() {}
}

/**
 * 菜单数据类
 */
class Menu_Manager_Data {

    /**
     * Get menu items
     *
     * @return array Menu items
     */
    public function get_menu_items(): array {
        global $menu, $submenu;

        $menu_items = array();

        // Get top-level menu items
        foreach ( $menu as $key => $menu_item ) {
            if ( ! empty( $menu_item[0] ) && $menu_item[0] !== '-' ) {
                $menu_item_data = array(
                    'id'         => $key,
                    'title'      => $menu_item[0],
                    'slug'       => $menu_item[2],
                    'capability' => $menu_item[1],
                    'icon'       => $menu_item[6],
                    'position'   => $menu_item[5],
                    'submenu'    => array()
                );

                // Get submenu items
                if ( isset( $submenu[ $menu_item[2] ] ) ) {
                    foreach ( $submenu[ $menu_item[2] ] as $submenu_key => $submenu_item ) {
                        $menu_item_data['submenu'][] = array(
                            'id'         => $submenu_key,
                            'title'      => $submenu_item[0],
                            'slug'       => $submenu_item[2],
                            'capability' => $submenu_item[1]
                        );
                    }
                }

                $menu_items[] = $menu_item_data;
            }
        }

        return $menu_items;
    }

    /**
     * Save menu items
     *
     * @param array $menu_items Menu items to save
     * @return array Save results
     */
    public function save_menu_items( $menu_items ): array {
        $results = array(
            'success' => true,
            'message' => \__( 'Menu items saved successfully', \WPCA_TEXT_DOMAIN )
        );

        // Save menu items to options
        if ( function_exists( '\update_option' ) ) {
            \update_option( 'wpca_menu_items', $menu_items );
        }

        return $results;
    }

    /**
     * Reorder menu items based on specified order
     *
     * @param array &$menu Menu array reference
     * @param array $menu_order Desired menu order
     */
    public function reorder_menu( array &$menu, array $menu_order ): void {
        $new_menu   = array();
        $menu_index = 5;

        // Add menu items in the specified order
        foreach ( $menu_order as $menu_slug ) {
            foreach ( $menu as $key => $menu_item ) {
                if ( isset( $menu_item[2] ) && $menu_item[2] === $menu_slug ) {
                    $menu_item[5] = $menu_index;
                    $new_menu[ $key ] = $menu_item;
                    $menu_index += 5;
                    break;
                }
            }
        }

        // Add remaining menu items
        foreach ( $menu as $key => $menu_item ) {
            $menu_slug = isset( $menu_item[2] ) ? $menu_item[2] : '';
            if ( ! in_array( $menu_slug, $menu_order, true ) ) {
                $menu_item[5] = $menu_index;
                $new_menu[ $key ] = $menu_item;
                $menu_index += 5;
            }
        }

        // Replace original menu with reordered menu
        $menu = $new_menu;
    }
}
