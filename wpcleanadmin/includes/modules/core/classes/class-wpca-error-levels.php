<?php
/**
 * WPCleanAdmin Error Levels
 *
 * 承载日志级别定义与 PHP 错误号到日志级别的映射，
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
 * 错误级别映射 trait
 */
trait Error_Levels {

    /**
     * 日志级别
     */
    const LOG_LEVELS = array(
        'debug'    => 0,
        'info'     => 1,
        'notice'   => 2,
        'warning'  => 3,
        'error'    => 4,
        'critical' => 5,
    );

    /**
     * 将 PHP 错误号转换为日志级别
     *
     * @param int $errno 错误号
     * @return string 日志级别
     */
    private function get_log_level_from_errorno( int $errno ): string {
        switch ( $errno ) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return 'error';
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return 'warning';
            case E_NOTICE:
            case E_USER_NOTICE:
                return 'notice';
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return 'info';
            default:
                if ( defined( 'E_STRICT' ) && $errno === E_STRICT ) {
                    return 'info';
                }
                return 'debug';
        }
    }
}
