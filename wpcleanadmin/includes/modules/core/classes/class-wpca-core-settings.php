<?php
/**
 * WPCleanAdmin Core Settings
 *
 * 承载插件默认设置的合并与写入，从 Core 主类抽取。
 *
 * @package WPCleanAdmin\Modules\Core\Classes
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

namespace WPCleanAdmin\Modules\Core\Classes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 默认设置管理类
 */
class Core_Settings {

    /**
     * 获取默认设置结构
     *
     * @return array
     */
    public function get_default_settings(): array {
        return array(
            'general'     => array(
                'clean_admin_bar' => 1,
                'clean_dashboard' => 1,
                'remove_wp_logo'  => 1,
            ),
            'performance' => array(
                'optimize_database' => 1,
                'clean_transients'  => 1,
                'disable_emojis'    => 1,
            ),
            'menu'        => array(
                'remove_dashboard_widgets' => 1,
                'simplify_admin_menu'      => 1,
            ),
        );
    }

    /**
     * 合并当前设置与默认设置并写回
     */
    public function set_default_settings(): void {
        $default_settings = $this->get_default_settings();

        $current_settings = ( function_exists( 'get_option' ) ? \get_option( 'wpca_settings', array() ) : array() );
        $updated_settings = ( function_exists( 'wp_parse_args' ) ? \wp_parse_args( $current_settings, $default_settings ) : array_merge( $default_settings, $current_settings ) );

        if ( function_exists( 'update_option' ) ) {
            \update_option( 'wpca_settings', $updated_settings );
        }
    }
}
