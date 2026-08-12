<?php
/**
 * WPCleanAdmin Menu Customizer Tree
 *
 * 承载菜单结构构建、排序与分组逻辑，从 Menu_Customizer 主类抽取。
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

if ( ! function_exists( 'sanitize_html_class' ) ) {
    function sanitize_html_class() {}
}

/**
 * 菜单结构构建类
 */
class Menu_Customizer_Tree {

    /**
     * Customize admin menu (hide/title/icon/position + order + groups)
     *
     * @global array $menu
     * @global array $submenu
     */
    public function customize_admin_menu(): void {
        global $menu, $submenu;

        $settings = ( function_exists( '\wpca_get_settings' ) ? \wpca_get_settings() : array() );

        // Customize menu items based on settings
        if ( isset( $settings['menu_items'] ) ) {
            foreach ( $settings['menu_items'] as $menu_slug => $menu_settings ) {
                // Hide menu item
                if ( isset( $menu_settings['hidden'] ) && $menu_settings['hidden'] ) {
                    foreach ( $menu as $key => $menu_item ) {
                        if ( isset( $menu_item[2] ) && $menu_item[2] === $menu_slug ) {
                            unset( $menu[ $key ] );
                            break;
                        }
                    }
                }

                // Customize menu title
                if ( isset( $menu_settings['title'] ) && ! empty( $menu_settings['title'] ) ) {
                    foreach ( $menu as $key => $menu_item ) {
                        if ( isset( $menu_item[2] ) && $menu_item[2] === $menu_slug ) {
                            $menu[ $key ][0] = $menu_settings['title'];
                            break;
                        }
                    }
                }

                // Customize menu icon
                if ( isset( $menu_settings['icon'] ) && ! empty( $menu_settings['icon'] ) ) {
                    foreach ( $menu as $key => $menu_item ) {
                        if ( isset( $menu_item[2] ) && $menu_item[2] === $menu_slug ) {
                            $menu[ $key ][6] = $menu_settings['icon'];
                            break;
                        }
                    }
                }

                // Customize menu position
                if ( isset( $menu_settings['position'] ) && is_numeric( $menu_settings['position'] ) ) {
                    foreach ( $menu as $key => $menu_item ) {
                        if ( isset( $menu_item[2] ) && $menu_item[2] === $menu_slug ) {
                            $menu[ $key ][5] = $menu_settings['position'];
                            break;
                        }
                    }
                }
            }
        }

        // Customize submenu items
        if ( isset( $settings['submenu_items'] ) ) {
            foreach ( $settings['submenu_items'] as $parent_slug => $submenu_items ) {
                if ( isset( $submenu[ $parent_slug ] ) ) {
                    foreach ( $submenu_items as $submenu_slug => $submenu_settings ) {
                        // Hide submenu item
                        if ( isset( $submenu_settings['hidden'] ) && $submenu_settings['hidden'] ) {
                            foreach ( $submenu[ $parent_slug ] as $key => $submenu_item ) {
                                if ( isset( $submenu_item[2] ) && $submenu_item[2] === $submenu_slug ) {
                                    unset( $submenu[ $parent_slug ][ $key ] );
                                    break;
                                }
                            }
                        }

                        // Customize submenu title
                        if ( isset( $submenu_settings['title'] ) && ! empty( $submenu_settings['title'] ) ) {
                            foreach ( $submenu[ $parent_slug ] as $key => $submenu_item ) {
                                if ( isset( $submenu_item[2] ) && $submenu_item[2] === $submenu_slug ) {
                                    $submenu[ $parent_slug ][ $key ][0] = $submenu_settings['title'];
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Apply menu order
        if ( isset( $settings['menu_order'] ) && ! empty( $settings['menu_order'] ) ) {
            $this->apply_menu_order( $menu, $settings['menu_order'] );
        }

        // Apply menu groups
        if ( isset( $settings['menu_groups'] ) && ! empty( $settings['menu_groups'] ) ) {
            $this->apply_menu_groups( $menu, $settings['menu_groups'] );
        }
    }

    /**
     * Apply menu order to admin menu
     *
     * @param array $menu
     * @param array $menu_order
     */
    public function apply_menu_order( array &$menu, array $menu_order ): void {
        $new_menu         = array();
        $remaining_menu   = $menu;

        foreach ( $menu_order as $menu_slug ) {
            foreach ( $remaining_menu as $key => $menu_item ) {
                if ( isset( $menu_item[2] ) && $menu_item[2] === $menu_slug ) {
                    $new_menu[] = $menu_item;
                    unset( $remaining_menu[ $key ] );
                    break;
                }
            }
        }

        foreach ( $remaining_menu as $menu_item ) {
            $new_menu[] = $menu_item;
        }

        $menu = $new_menu;
    }

    /**
     * Apply menu groups to admin menu
     *
     * @param array $menu
     * @param array $menu_groups
     */
    public function apply_menu_groups( array &$menu, array $menu_groups ): void {
        if ( empty( $menu_groups ) || ! is_array( $menu_groups ) ) {
            return;
        }

        foreach ( $menu_groups as $group_id => $group_settings ) {
            if ( ! isset( $group_settings['enabled'] ) || ! $group_settings['enabled'] ) {
                continue;
            }

            $group_name      = isset( $group_settings['name'] ) ? $group_settings['name'] : __( 'Custom Group', WPCA_TEXT_DOMAIN );
            $group_menu_items = isset( $group_settings['menu_items'] ) ? $group_settings['menu_items'] : array();

            if ( empty( $group_menu_items ) ) {
                continue;
            }

            $grouped_items    = array();
            $remaining_items  = array();

            foreach ( $menu as $menu_item ) {
                $menu_slug = isset( $menu_item[2] ) ? $menu_item[2] : '';

                if ( in_array( $menu_slug, $group_menu_items, true ) ) {
                    $grouped_items[] = $menu_item;
                } else {
                    $remaining_items[] = $menu_item;
                }
            }

            if ( ! empty( $grouped_items ) ) {
                $insert_position = count( $remaining_items );
                foreach ( $remaining_items as $index => $item ) {
                    $item_slug = isset( $item[2] ) ? $item[2] : '';
                    if ( in_array( $item_slug, $group_menu_items, true ) ) {
                        $insert_position = $index;
                        break;
                    }
                }

                $separator_item = array(
                    '',
                    'read',
                    'separator-' . $group_id,
                    '',
                    'wpca-menu-group wpca-menu-group-' . ( function_exists( '\sanitize_html_class' ) ? \sanitize_html_class( $group_id ) : $group_id ),
                    $insert_position
                );

                $new_menu   = array_slice( $remaining_items, 0, $insert_position );
                $new_menu[] = $separator_item;
                $new_menu   = array_merge( $new_menu, array_slice( $remaining_items, $insert_position ) );
                $menu       = $new_menu;
            }
        }
    }
}
