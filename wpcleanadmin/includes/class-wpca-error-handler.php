<?php
/**
 * WPCleanAdmin Error Handler
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @since 1.8.0
 */

namespace WPCleanAdmin;

/**
 * Error handler class
 */
class Error_Handler {
    /**
     * Singleton instance
     *
     * @var Error_Handler
     */
    private static $instance = null;
    
    /**
     * Log levels
     */
    const LOG_LEVELS = array(
        'debug' => 0,
        'info' => 1,
        'notice' => 2,
        'warning' => 3,
        'error' => 4,
        'critical' => 5
    );
    
    /**
     * Current log level
     *
     * @var string
     */
    private $log_level = 'notice';
    
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
        $this->load_log_level();
    }
    
    /**
     * Load log level from settings
     */
    private function load_log_level() {
        $settings = ( function_exists( 'get_option' ) ? \get_option( 'wpca_settings', array() ) : array() );
        if ( isset( $settings['general'] ) && isset( $settings['general']['log_level'] ) ) {
            $log_level = $settings['general']['log_level'];
            if ( isset( self::LOG_LEVELS[ $log_level ] ) ) {
                $this->log_level = $log_level;
            }
        }
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
        if ( $this->should_log( $log_level ) ) {
            $error = array(
                'type' => 'error',
                'level' => $log_level,
                'message' => $errstr,
                'file' => $errfile,
                'line' => $errline,
                'time' => \time()
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
            'type' => 'exception',
            'level' => 'error',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
            'time' => \time()
        );
        
        $this->log( $error );
        
        // Display error for debugging
        if ( \defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            echo '<pre>';
            echo 'Uncaught Exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine() . '\n';
            echo $exception->getTraceAsString();
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
     * Get log level from error number
     *
     * @param int $errno Error number
     * @return string
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
                // Handle E_STRICT if it exists (deprecated in PHP 5.4+, removed in PHP 7.0+)
                if ( defined( 'E_STRICT' ) && $errno === E_STRICT ) {
                    return 'info';
                }
                return 'debug';
        }
    }
    
    /**
     * Check if message should be logged
     *
     * @param string $log_level Log level
     * @return bool
     */
    public function should_log( string $log_level ): bool {
        $current_level = self::LOG_LEVELS[ $this->log_level ] ?? 2;
        $message_level = self::LOG_LEVELS[ $log_level ] ?? 0;
        
        return $message_level >= $current_level;
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
        $this->log_to_file( $error );
    }
    
    /**
     * Log to custom file
     *
     * @param array $error Error data
     */
    private function log_to_file( array $error ) {
        $log_dir = WPCA_PLUGIN_DIR . 'logs';
        
        // Create log directory if it doesn't exist
        if ( ! \is_dir( $log_dir ) ) {
            if ( function_exists( 'wp_mkdir_p' ) ) {
                \wp_mkdir_p( $log_dir );
            } else {
                // Fallback to mkdir if wp_mkdir_p is not available
                \mkdir( $log_dir, 0755, true );
            }
        }
        
        $log_file = $log_dir . '/wpca-' . \date( 'Y-m-d' ) . '.log';
        $message = sprintf(
            '[%s] [%s] %s: %s in %s:%d\n',
            \date( 'Y-m-d H:i:s' ),
            $error['type'],
            strtoupper( $error['level'] ),
            $error['message'],
            $error['file'],
            $error['line']
        );
        
        // Add trace if available
        if ( isset( $error['trace'] ) ) {
            $message .= 'Trace: ' . \print_r( $error['trace'], true ) . '\n';
        }
        
        // Write to log file
        \file_put_contents( $log_file, $message, FILE_APPEND );
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
            'type' => 'message',
            'level' => $level,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'time' => \time()
        );
        
        $this->log( $error );
    }
    
    /**
     * Set log level
     *
     * @param string $log_level Log level
     */
    public function set_log_level( string $log_level ) {
        if ( isset( self::LOG_LEVELS[ $log_level ] ) ) {
            $this->log_level = $log_level;
        }
    }
    
    /**
     * Get log level
     *
     * @return string
     */
    public function get_log_level(): string {
        return $this->log_level;
    }
}

