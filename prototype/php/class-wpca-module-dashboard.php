<?php
/**
 * 示例模块：Dashboard（原型用法示范）
 *
 * 演示如何继承 Module_Base 并通过 AJAX 网关注册 handler。
 * 新功能模块应以本文件为模板。
 *
 * @package WPCleanAdmin\Modules\Admin
 * @version 1.8.2
 */

namespace WPCleanAdmin\Modules\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use WPCleanAdmin\Modules\Module_Base;

/**
 * Dashboard 模块
 */
class Module_Dashboard extends Module_Base {

    /**
     * 初始化：注册菜单与 AJAX
     *
     * @return void
     */
    protected function init() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );

        // 统一经网关注册，自动获得 nonce + capability 校验
        $this->register_ajax( 'wpca_dashboard_save', array( $this, 'ajax_save' ) );
    }

    /**
     * 模块名
     *
     * @return string
     */
    public function get_module_name() {
        return 'dashboard';
    }

    /**
     * 注册后台菜单
     *
     * @return void
     */
    public function register_menu() {
        add_menu_page(
            __( 'WP Clean Admin', 'wp-clean-admin' ),
            __( 'WP Clean Admin', 'wp-clean-admin' ),
            'manage_options',
            'wp-clean-admin',
            array( $this, 'render_page' )
        );
    }

    /**
     * 渲染设置页（加载 UI 原型）
     *
     * @return void
     */
    public function render_page() {
        include WPCA_PLUGIN_DIR . 'assets/prototype/ui/settings-page.html';
    }

    /**
     * AJAX：保存设置（示范 sanitize 基线）
     *
     * @return void
     */
    public function ajax_save() {
        $raw = isset( $_POST['settings'] )
            ? ( function_exists( '\wp_unslash' ) ? \wp_unslash( $_POST['settings'] ) : $_POST['settings'] )
            : array();

        $settings = $this->sanitize_settings( $raw );

        if ( function_exists( '\update_option' ) ) {
            \update_option( 'wpca_settings', $settings );
        }

        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( array( 'saved' => true ) );
        }
    }
}
