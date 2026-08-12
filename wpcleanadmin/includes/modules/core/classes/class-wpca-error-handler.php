<?php
/**
 * WPCleanAdmin Error Handler
 *
 * @package WPCleanAdmin\Modules\Core\Classes
 * @version  1.8.4
 * @author Sut
 * @since 1.8.0
 */

require_once __DIR__ . '/class-wpca-error-levels.php';
require_once __DIR__ . '/class-wpca-file-logger.php';
require_once __DIR__ . '/class-wpca-error-config.php';

namespace WPCleanAdmin\Modules\Core\Classes;

use WPCleanAdmin\Modules\Core\Classes\Error_Levels;
use WPCleanAdmin\Modules\Core\Classes\File_Logger;
use WPCleanAdmin\Modules\Core\Classes\Error_Config;

/**
 * Error handler class
 */
class Error_Handler {
    use Error_Levels;

    /**
     * Singleton instance
     *
     * @var Error_Handler
     */
    private static $instance = null;

    /**
     * 文件日志器
     *
     * @var File_Logger
     */
    private $file_logger;

    /**
     * 错误日志配置器
     *
     * @var Error_Config
     */
    private $config;

    /**
     * Get singleton instance
     *
     * @return Error_Handler
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->file_logger = new File_Logger();
        $this->config      = new Error_Config();
        $this->init();
    }

    /**
     * Initialize error handler
     */
    public function init() {
        // Set error handler
        \set_error_handler( array( $this, 'error_handler' ) );

        // Set exception handler
        \set_exception_handler( array( $this, 'exception_handler' ) );

        // Set shutdown function
        \register_shutdown_function( array( $this, 'shutdown_function' ) );

        // Get log level from settings
        $this->config->load_log_level();
    }

    /**
     * Custom error handler
     *
     * @param int $errno Error number
     * @param string $errstr Error message
     * @param string $errfile Error file
     * @param int $errline Error line
     * @return bool
     */
    public function error_handler( int $errno, string $errstr, string $errfile, int $errline ): bool {
        // Convert error number to log level
        $log_level = $this->get_log_level_from_errorno( $errno );

        // Check if error should be logged
        if ( $this->config->should_log( $log_level ) ) {
            $error = array(
                'type'    => 'error',
                'level'   => $log_level,
                'message' => $errstr,
                'file'    => $errfile,
                'line'    => $errline,
                'time'    => \time()
            );

            $this->log( $error );
        }

        // Return false to let PHP handle the error normally
        return false;
    }

    /**
     * Custom exception handler
     *
     * @param \Throwable $exception Exception object
     */
    public function exception_handler( \Throwable $exception ) {
        $error = array(
            'type'    => 'exception',
            'level'   => 'error',
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTrace(),
            'time'    => \time()
        );

        $this->log( $error );

        // Display error for debugging
        if ( \defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            echo '<pre>';
            echo \esc_html( 'Uncaught Exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine() . '\n' );
            echo \esc_html( $exception->getTraceAsString() );
            echo '</pre>';
        }
    }

    /**
     * Shutdown function
     */
    public function shutdown_function() {
        $error = \error_get_last();
        if ( $error !== null ) {
            $this->error_handler( $error['type'], $error['message'], $error['file'], $error['line'] );
        }
    }

    /**
     * Log error
     *
     * @param array $error Error data
     */
    public function log( array $error ) {
        // Log to WordPress debug log
        if ( \defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            $message = sprintf(
                '[WP Clean Admin] %s: %s in %s:%d',
                strtoupper( $error['level'] ),
                $error['message'],
                $error['file'],
                $error['line']
            );

            if ( function_exists( 'error_log' ) ) {
                \error_log( $message );
            }
        }

        // Log to custom log file
        $this->file_logger->log_to_file( $error );
    }

    /**
     * Log message
     *
     * @param string $message Message
     * @param string $level Log level
     * @param string $file File name
     * @param int $line Line number
     */
    public function log_message( string $message, string $level = 'info', string $file = '', int $line = 0 ) {
        if ( empty( $file ) || $line === 0 ) {
            $backtrace = \debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 1 );
            if ( isset( $backtrace[0] ) ) {
                $file = $backtrace[0]['file'] ?? '';
                $line = $backtrace[0]['line'] ?? 0;
            }
        }

        $error = array(
            'type'    => 'message',
            'level'   => $level,
            'message' => $message,
            'file'    => $file,
            'line'    => $line,
            'time'    => \time()
        );

        $this->log( $error );
    }

    /**
     * Set log level
     *
     * @param string $log_level
     */
    public function set_log_level( string $log_level ) {
        $this->config->set_log_level( $log_level );
    }

    /**
     * Get log level
     *
     * @return string
     */
    public function get_log_level(): string {
        return $this->config->get_log_level();
    }
}

