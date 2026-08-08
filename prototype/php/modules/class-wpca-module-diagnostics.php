<?php
/**
 * 高保真原型：诊断模块（环境检测 + 报告）
 *
 * @package WPCleanAdmin\Modules\Diagnostics
 * @version 1.8.3
 */

namespace WPCleanAdmin\Modules\Diagnostics;

use WPCleanAdmin\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Module_Diagnostics extends Module_Base {

    protected function init() {
        $this->register_ajax( 'wpca_diag_run', array( $this, 'ajax_run' ) );
    }

    public function get_module_name() {
        return 'diagnostics';
    }

    /**
     * 环境检测项（高保真静态数据）
     *
     * @return array
     */
    public function get_checks() {
        return array(
            array( 'label' => 'PHP 版本', 'value' => '8.1.2', 'status' => 'success', 'advice' => '' ),
            array( 'label' => 'WordPress 版本', 'value' => '6.4.1', 'status' => 'success', 'advice' => '' ),
            array( 'label' => 'MySQL 版本', 'value' => '5.7.4', 'status' => 'warning', 'advice' => '建议升级到 8.0+' ),
            array( 'label' => 'HTTPS', 'value' => '已启用', 'status' => 'success', 'advice' => '' ),
            array( 'label' => 'WP_DEBUG', 'value' => '开启', 'status' => 'danger', 'advice' => '生产环境应关闭' ),
            array( 'label' => '对象缓存', 'value' => '未启用', 'status' => 'warning', 'advice' => '建议启用 Redis/Memcached' ),
        );
    }

    /**
     * AJAX：运行诊断
     */
    public function ajax_run() {
        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( array( 'checks' => $this->get_checks() ) );
        }
    }
}
