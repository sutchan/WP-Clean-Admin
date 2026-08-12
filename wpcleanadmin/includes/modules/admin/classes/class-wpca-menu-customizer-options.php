<?php
/**
 * WPCleanAdmin Menu Customizer Options
 *
 * 承载菜单定制设置的读写与分组 CRUD，从 Menu_Customizer 主类抽取。
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
if ( ! function_exists( 'delete_option' ) ) {
    function delete_option() {}
}
if ( ! function_exists( 'get_option' ) ) {
    function get_option() {}
}
if ( ! function_exists( 'wp_parse_args' ) ) {
    function wp_parse_args() {}
}

/**
 * 菜单定制设置操作类
 */
class Menu_Customizer_Options {

    const OPTION_KEY = 'wpca_menu_customizer_settings';

    /**
     * Get menu customizer settings
     *
     * @return array
     */
    public function get_settings(): array {
        $settings = ( function_exists( '\get_option' ) ? \get_option( self::OPTION_KEY, array() ) : array() );

        $default_settings = array(
            'enabled'        => false,
            'menu_items'     => array(),
            'submenu_items'  => array(),
            'admin_bar_items' => array()
        );

        return ( function_exists( '\wp_parse_args' ) ? \wp_parse_args( $settings, $default_settings ) : array_merge( $default_settings, $settings ) );
    }

    /**
     * Save menu customizer settings
     *
     * @param array $settings
     * @return bool
     */
    public function save_settings( array $settings ): bool {
        return ( function_exists( '\update_option' ) ? \update_option( self::OPTION_KEY, $settings ) : false );
    }

    /**
     * Reset menu customizer settings
     *
     * @return bool
     */
    public function reset_settings(): bool {
        return ( function_exists( '\delete_option' ) ? \delete_option( self::OPTION_KEY ) : false );
    }

    /**
     * Create a new menu group
     *
     * @param string $group_id
     * @param string $group_name
     * @param array  $menu_items
     * @return bool
     */
    public function create_menu_group( $group_id, $group_name, $menu_items = array() ): bool {
        if ( empty( $group_id ) || empty( $group_name ) ) {
            return false;
        }

        $settings = $this->get_settings();

        if ( ! isset( $settings['menu_groups'] ) ) {
            $settings['menu_groups'] = array();
        }

        $settings['menu_groups'][ $group_id ] = array(
            'name'       => $group_name,
            'menu_items' => $menu_items,
            'enabled'    => true,
            'created_at' => date( 'Y-m-d H:i:s' )
        );

        return $this->save_settings( $settings );
    }

    /**
     * Delete a menu group
     *
     * @param string $group_id
     * @return bool
     */
    public function delete_menu_group( $group_id ): bool {
        if ( empty( $group_id ) ) {
            return false;
        }

        $settings = $this->get_settings();

        if ( isset( $settings['menu_groups'][ $group_id ] ) ) {
            unset( $settings['menu_groups'][ $group_id ] );
            return $this->save_settings( $settings );
        }

        return false;
    }

    /**
     * Update menu group settings
     *
     * @param string $group_id
     * @param array  $group_settings
     * @return bool
     */
    public function update_menu_group( $group_id, $group_settings ): bool {
        if ( empty( $group_id ) || empty( $group_settings ) ) {
            return false;
        }

        $settings = $this->get_settings();

        if ( isset( $settings['menu_groups'][ $group_id ] ) ) {
            $settings['menu_groups'][ $group_id ] = array_merge(
                $settings['menu_groups'][ $group_id ],
                $group_settings
            );
            return $this->save_settings( $settings );
        }

        return false;
    }

    /**
     * Get menu group settings
     *
     * @return array
     */
    public function get_menu_groups(): array {
        $settings = $this->get_settings();
        return isset( $settings['menu_groups'] ) ? $settings['menu_groups'] : array();
    }
}
