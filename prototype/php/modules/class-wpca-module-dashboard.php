<?php
/**
 * 高保真原型：仪表盘模块
 *
 * 提供体检总览数据与一键体检 AJAX。
 *
 * @package WPCleanAdmin\Modules\Admin
 * @version 1.8.3
 */

namespace WPCleanAdmin\Modules\Admin;

use WPCleanAdmin\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Module_Dashboard extends Module_Base {

    protected function init() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        $this->register_ajax( 'wpca_dashboard_scan', array( $this, 'ajax_scan' ) );
    }

    public function get_module_name() {
        return 'dashboard';
    }

    public function register_menu() {
        add_menu_page(
            __( 'WP Clean Admin', 'wp-clean-admin' ),
            __( 'WP Clean Admin', 'wp-clean-admin' ),
            'manage_options',
            'wp-clean-admin',
            array( $this, 'render_page' )
        );
    }

    public function render_page() {
        $tpl = dirname( __DIR__, 3 ) . '/ui/index.html';
        if ( file_exists( $tpl ) ) {
            include $tpl;
        } else {
            echo '<p>原型模板未找到：' . esc_html( $tpl ) . '</p>';
        }
    }

    /**
     * 体检指标（高保真静态数据）
     *
     * @return array
     */
    public function get_health_metrics() {
        return array(
            'revisions'   => array( 'label' => '冗余修订版本', 'count' => 1280, 'level' => 'warning' ),
            'orphan_meta' => array( 'label' => '孤立元数据包', 'count' => 342, 'level' => 'warning' ),
            'spam'        => array( 'label' => '垃圾评论', 'count' => 57, 'level' => 'danger' ),
            'transients'  => array( 'label' => '过期临时选项', 'count' => 89, 'level' => 'info' ),
            'db_size'     => array( 'label' => '数据库体积', 'count' => '48.2 MB', 'level' => 'info' ),
        );
    }

    /**
     * AJAX：一键体检
     */
    public function ajax_scan() {
        $metrics = $this->get_health_metrics();
        $total   = array_sum( array_map( function ( $m ) {
            return is_numeric( $m['count'] ) ? $m['count'] : 0;
        }, $metrics ) );

        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( array( 'metrics' => $metrics, 'total_optimizable' => $total ) );
        }
    }
}
