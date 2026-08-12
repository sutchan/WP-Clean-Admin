<?php
/**
 * WPCleanAdmin Error Config
 *
 * 承载错误日志级别的管理与判定，从 Error_Handler 主类抽取。
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
 * 错误日志配置类
 */
class Error_Config {

    use Error_Levels;

    /**
     * 当前日志级别
     *
     * @var string
     */
    private $log_level = 'notice';

    /**
     * 从设置加载日志级别
     */
    public function load_log_level(): void {
        $settings = ( function_exists( 'get_option' ) ? \get_option( 'wpca_settings', array() ) : array() );
        if ( isset( $settings['general'] ) && isset( $settings['general']['log_level'] ) ) {
            $log_level = $settings['general']['log_level'];
            if ( isset( self::LOG_LEVELS[ $log_level ] ) ) {
                $this->log_level = $log_level;
            }
        }
    }

    /**
     * 设置日志级别
     *
     * @param string $log_level
     */
    public function set_log_level( string $log_level ): void {
        if ( isset( self::LOG_LEVELS[ $log_level ] ) ) {
            $this->log_level = $log_level;
        }
    }

    /**
     * 获取当前日志级别
     *
     * @return string
     */
    public function get_log_level(): string {
        return $this->log_level;
    }

    /**
     * 判断某个级别是否应被记录
     *
     * @param string $log_level
     * @return bool
     */
    public function should_log( string $log_level ): bool {
        $current_level = self::LOG_LEVELS[ $this->log_level ] ?? 2;
        $message_level = self::LOG_LEVELS[ $log_level ] ?? 0;

        return $message_level >= $current_level;
    }
}
