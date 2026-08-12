<?php
/**
 * WPCleanAdmin File Logger
 *
 * 负责将错误记录写入插件自定义日志文件，
 * 从 Error_Handler 中抽取，保持单一职责。
 *
 * @package WPCleanAdmin\Modules\Core\Classes
 * @version  1.8.4
 * @author Sut
 * @since 1.8.0
 */

namespace WPCleanAdmin\Modules\Core\Classes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 文件日志记录器
 *
 * 处理日志目录创建与按日期滚动的日志写入。
 */
class File_Logger {

    /**
     * 写入错误到插件自定义日志文件
     *
     * @param array $error 错误数据（type/level/message/file/line/trace）
     */
    public function log_to_file( array $error ): void {
        $plugin_dir = defined( 'WPCA_PLUGIN_DIR' ) ? WPCA_PLUGIN_DIR : dirname( __DIR__, 2 ) . '/';
        $log_dir    = $plugin_dir . 'logs';

        // 创建日志目录（优先使用 WordPress 辅助函数）
        if ( ! \is_dir( $log_dir ) ) {
            if ( function_exists( 'wp_mkdir_p' ) ) {
                \wp_mkdir_p( $log_dir );
            } else {
                \mkdir( $log_dir, 0755, true );
            }
        }

        $log_file = $log_dir . '/wpca-' . \date( 'Y-m-d' ) . '.log';
        $message  = sprintf(
            "[%s] [%s] %s: %s in %s:%d\n",
            \date( 'Y-m-d H:i:s' ),
            $error['type'],
            strtoupper( $error['level'] ),
            $error['message'],
            $error['file'],
            $error['line']
        );

        // 追加异常堆栈信息
        if ( isset( $error['trace'] ) ) {
            $message .= 'Trace: ' . \print_r( $error['trace'], true ) . "\n";
        }

        \file_put_contents( $log_file, $message, FILE_APPEND );
    }
}
