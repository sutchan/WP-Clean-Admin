<?php
/**
 * 高保真原型：清理模块（数据库/媒体/评论/内容）
 *
 * @package WPCleanAdmin\Modules\Cleanup
 * @version 1.8.2
 */

namespace WPCleanAdmin\Modules\Cleanup;

use WPCleanAdmin\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Module_Cleanup extends Module_Base {

    protected function init() {
        $this->register_ajax( 'wpca_cleanup_run', array( $this, 'ajax_run' ) );
    }

    public function get_module_name() {
        return 'cleanup';
    }

    /**
     * 可清理项（高保真静态数据）
     *
     * @return array
     */
    public function get_tasks() {
        return array(
            array( 'id' => 'revisions', 'label' => '文章修订版本', 'count' => 1280, 'risk' => 'low' ),
            array( 'id' => 'drafts', 'label' => '自动草稿', 'count' => 43, 'risk' => 'low' ),
            array( 'id' => 'orphan_meta', 'label' => '孤立元数据', 'count' => 342, 'risk' => 'medium' ),
            array( 'id' => 'spam', 'label' => '垃圾评论', 'count' => 57, 'risk' => 'low' ),
            array( 'id' => 'trash', 'label' => '回收站内容', 'count' => 21, 'risk' => 'medium' ),
            array( 'id' => 'transients', 'label' => '过期临时选项', 'count' => 89, 'risk' => 'low' ),
        );
    }

    /**
     * AJAX：执行清理（原型和真实实现均返回受影响行数）
     */
    public function ajax_run() {
        $task = isset( $_POST['task'] ) ? sanitize_key( wp_unslash( $_POST['task'] ) ) : '';
        $tasks = $this->get_tasks();
        $matched = null;
        foreach ( $tasks as $t ) {
            if ( $t['id'] === $task ) {
                $matched = $t;
                break;
            }
        }

        if ( ! $matched ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => 'unknown_task' ) );
            }
            return;
        }

        // 原型：实际实现中此处用 $wpdb->delete(..., $wpdb->prepare(...))
        $affected = $matched['count'];

        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( array( 'task' => $task, 'affected' => $affected ) );
        }
    }
}
