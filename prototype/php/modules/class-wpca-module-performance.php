<?php
/**
 * 高保真原型：性能优化模块
 *
 * @package WPCleanAdmin\Modules\Performance
 * @version 1.8.2
 */

namespace WPCleanAdmin\Modules\Performance;

use WPCleanAdmin\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Module_Performance extends Module_Base {

    protected function init() {
        $this->register_ajax( 'wpca_perf_toggle', array( $this, 'ajax_toggle' ) );
    }

    public function get_module_name() {
        return 'performance';
    }

    /**
     * 优化开关（高保真静态数据）
     *
     * @return array
     */
    public function get_options() {
        return array(
            array( 'id' => 'disable_emojis', 'label' => '禁用 Emoji 脚本', 'on' => true ),
            array( 'id' => 'disable_embeds', 'label' => '禁用 oEmbed', 'on' => true ),
            array( 'id' => 'defer_scripts', 'label' => '延迟加载脚本', 'on' => false ),
            array( 'id' => 'lazy_images', 'label' => '图片懒加载', 'on' => true ),
            array( 'id' => 'minify_html', 'label' => '压缩 HTML 输出', 'on' => false ),
        );
    }

    /**
     * AJAX：切换优化项
     */
    public function ajax_toggle() {
        $id  = isset( $_POST['option'] ) ? sanitize_key( wp_unslash( $_POST['option'] ) ) : '';
        $val = isset( $_POST['value'] ) ? (bool) $_POST['value'] : false;

        if ( ! $id ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => 'invalid_option' ) );
            }
            return;
        }

        if ( function_exists( '\update_option' ) ) {
            \update_option( 'wpca_perf_' . $id, $val );
        }

        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( array( 'option' => $id, 'value' => $val ) );
        }
    }
}
