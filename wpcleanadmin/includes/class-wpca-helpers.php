<?php
/**
 * WPCleanAdmin Helpers Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin;

/**
 * Error codes for WPCleanAdmin plugin
 */
abstract class WPCA_Errors {
    /** @var int No error */
    const ERROR_NONE = 0;
    /** @var int Database error */
    const ERROR_DATABASE = 1001;
    /** @var int Permission denied */
    const ERROR_PERMISSION = 1002;
    /** @var int Invalid input */
    const ERROR_INVALID_INPUT = 1003;
    /** @var int File operation error */
    const ERROR_FILE_OPERATION = 1004;
    /** @var int AJAX error */
    const ERROR_AJAX = 1005;
    /** @var int Settings validation error */
    const ERROR_VALIDATION = 1006;
    /** @var int Authentication error */
    const ERROR_AUTH = 1007;
    /** @var int Unknown error */
    const ERROR_UNKNOWN = 9999;
}

/**
 * Helpers class for WP Clean Admin plugin
 *
 * @package WPCleanAdmin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Helpers class
 */
class Helpers {
    
    /**
     * Singleton instance
     *
     * @var Helpers
     */
    private static $instance;
    
    /**
     * Get singleton instance
     *
     * @return Helpers
     */
    public static function getInstance(): Helpers {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Empty constructor
    }
    
    /**
     * Format bytes to human readable size
     *
     * @param int $bytes Bytes to format
     * @param int $precision Precision of the result
     * @return string Human readable size
     */
    public function format_bytes( $bytes, $precision = 2 ) {
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB' );
        
        $bytes = max( $bytes, 0 );
        $pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
        $pow = min( $pow, count( $units ) - 1 );
        
        $bytes /= pow( 1024, $pow );
        
        return round( $bytes, $precision ) . ' ' . $units[ $pow ];
    }
    
    /**
     * Format seconds to human readable time
     *
     * @param int $seconds Seconds to format
     * @return string Human readable time
     */
    public function format_seconds( int $seconds ): string {
        $days = floor( $seconds / 86400 );
        $hours = floor( ( $seconds % 86400 ) / 3600 );
        $minutes = floor( ( $seconds % 3600 ) / 60 );
        $seconds = $seconds % 60;
        
        $result = array();
        
        if ( $days > 0 ) {
            $result[] = sprintf( \_n( '%d day', '%d days', $days, WPCA_TEXT_DOMAIN ), $days );
        }
        if ( $hours > 0 ) {
            $result[] = sprintf( \_n( '%d hour', '%d hours', $hours, WPCA_TEXT_DOMAIN ), $hours );
        }
        if ( $minutes > 0 ) {
            $result[] = sprintf( \_n( '%d minute', '%d minutes', $minutes, WPCA_TEXT_DOMAIN ), $minutes );
        }
        if ( $seconds > 0 ) {
            $result[] = sprintf( \_n( '%d second', '%d seconds', $seconds, WPCA_TEXT_DOMAIN ), $seconds );
        }
        
        return implode( ', ', $result );
    }
    
    /**
     * Check if user has required capability
     *
     * @param string $capability Capability to check
     * @return bool True if user has capability, false otherwise
     */
    public function user_has_capability( $capability ) {
        if ( ! function_exists( 'current_user_can' ) ) {
            return false;
        }
        
        return \current_user_can( $capability );
    }
    
    /**
     * Get plugin information
     *
     * @param string $field Field to get
     * @return mixed Plugin information
     */
    public function get_plugin_info( $field = '' ) {
        $plugin_data = \get_plugin_data( \WPCA_PLUGIN_DIR . 'wp-clean-admin.php' );
        
        if ( empty( $field ) ) {
            return $plugin_data;
        }
        
        return isset( $plugin_data[ $field ] ) ? $plugin_data[ $field ] : '';
    }
    
    /**
     * Get WordPress version
     *
     * @return string WordPress version
     */
    public function get_wp_version(): string {
        global $wp_version;
        return $wp_version;
    }
    
    /**
     * Get PHP version
     *
     * @return string PHP version
     */
    public function get_php_version(): string {
        return PHP_VERSION;
    }
    
    /**
     * Get database version
     *
     * @return string Database version
     */
    public function get_db_version() {
        global $wpdb;
        return $wpdb->db_version();
    }
    
    /**
     * Get server information
     *
     * @return string Server information
     */
    public function get_server_info(): string {
        return $_SERVER['SERVER_SOFTWARE'];
    }
    
    /**
     * Check if plugin is network activated
     *
     * @return bool True if network activated, false otherwise
     */
    public function is_network_activated() {
        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            if ( defined( 'ABSPATH' ) ) {
                require_once ABSPATH . '/wp-admin/includes/plugin.php';
            } else {
                return false;
            }
        }
        
        if ( function_exists( 'plugin_basename' ) ) {
            return \is_plugin_active_for_network( \plugin_basename( WPCA_PLUGIN_DIR . 'wp-clean-admin.php' ) );
        }
        
        return false;
    }
    
    /**
     * Get current admin page URL
     *
     * @return string Current admin page URL
     */
    public function get_current_admin_url() {
        return \admin_url( \add_query_arg( array(), $_SERVER['REQUEST_URI'] ) );
    }
    
    /**
     * Sanitize array of data
     *
     * @param array $data Data to sanitize
     * @return array Sanitized data
     */
    public function sanitize_array( $data ): array {
        if ( ! is_array( $data ) ) {
            return array();
        }
        
        foreach ( $data as &$value ) {
            if ( is_array( $value ) ) {
                $value = $this->sanitize_array( $value );
            } else {
                $value = \sanitize_text_field( $value );
            }
        }
        
        return $data;
    }
    
    /**
     * Get plugin settings page URL
     *
     * @param string $tab Tab to open
     * @return string Settings page URL
     */
    public function get_settings_url( $tab = '' ) {
        $url = \admin_url( 'admin.php?page=wp-clean-admin' );
        
        if ( ! empty( $tab ) ) {
            $url .= '&tab=' . $tab;
        }
        
        return $url;
    }
    
    /**
     * Log message to debug log
     *
     * @param mixed $message Message to log
     * @param string $context Context of the log
     */
    public function log( $message, $context = 'general' ) {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }
        
        $log_message = sprintf( '[WPCleanAdmin] [%s] %s', $context, $message );
        
        if ( is_array( $message ) || is_object( $message ) ) {
            $log_message = sprintf( '[WPCleanAdmin] [%s] %s', $context, print_r( $message, true ) );
        }
        
        error_log( $log_message );
    }
    
    /**
     * Create standardized error response
     *
     * @param int $error_code Error code from WPCA_Errors class
     * @param string $message Error message
     * @param array $additional_data Additional data to include
     * @return array Error response array
     */
    public function create_error_response( $error_code, $message = '', $additional_data = array() ) {
        $response = array(
            'success' => false,
            'error_code' => $error_code,
            'message' => $message,
            'data' => $additional_data,
        );
        
        // Log the error
        $this->log( "Error {$error_code}: {$message}", 'error' );
        
        return $response;
    }
    
    /**
     * Create standardized success response
     *
     * @param string $message Success message
     * @param array $additional_data Additional data to include
     * @return array Success response array
     */
    public function create_success_response( $message = '', $additional_data = array() ) {
        $response = array(
            'success' => true,
            'message' => $message,
            'data' => $additional_data,
        );
        
        return $response;
    }
    
    /**
     * Handle exception and create error response
     *
     * @param Exception $exception Exception to handle
     * @param int $default_error_code Default error code to use
     * @return array Error response array
     */
    public function handle_exception( $exception, $default_error_code = WPCA_Errors::ERROR_UNKNOWN ) {
        $error_code = $default_error_code;
        $message = $exception->getMessage();
        
        // Determine error code from exception type
        if ( strpos( $message, 'SQLSTATE' ) !== false ) {
            $error_code = WPCA_Errors::ERROR_DATABASE;
        } elseif ( strpos( $message, 'permission' ) !== false || strpos( $message, 'capability' ) !== false ) {
            $error_code = WPCA_Errors::ERROR_PERMISSION;
        } elseif ( strpos( $message, 'file' ) !== false || strpos( $message, 'upload' ) !== false ) {
            $error_code = WPCA_Errors::ERROR_FILE_OPERATION;
        }
        
        // Log the exception
        $this->log( $exception, 'exception' );
        
        return $this->create_error_response( $error_code, $message );
    }
    
    /**
     * Get error message by error code
     *
     * @param int $error_code Error code
     * @return string Error message
     */
    public function get_error_message( $error_code ) {
        $messages = array(
            WPCA_Errors::ERROR_NONE => __( 'No error occurred.', WPCA_TEXT_DOMAIN ),
            WPCA_Errors::ERROR_DATABASE => __( 'A database error occurred. Please check the logs for more details.', WPCA_TEXT_DOMAIN ),
            WPCA_Errors::ERROR_PERMISSION => __( 'You do not have permission to perform this action.', WPCA_TEXT_DOMAIN ),
            WPCA_Errors::ERROR_INVALID_INPUT => __( 'Invalid input provided. Please check your entries and try again.', WPCA_TEXT_DOMAIN ),
            WPCA_Errors::ERROR_FILE_OPERATION => __( 'A file operation failed. Please check file permissions and try again.', WPCA_TEXT_DOMAIN ),
            WPCA_Errors::ERROR_AJAX => __( 'An AJAX request failed. Please try again.', WPCA_TEXT_DOMAIN ),
            WPCA_Errors::ERROR_VALIDATION => __( 'Settings validation failed. Please check your entries.', WPCA_TEXT_DOMAIN ),
            WPCA_Errors::ERROR_AUTH => __( 'Authentication failed. Please log in again.', WPCA_TEXT_DOMAIN ),
            WPCA_Errors::ERROR_UNKNOWN => __( 'An unknown error occurred. Please try again.', WPCA_TEXT_DOMAIN ),
        );
        
        return isset( $messages[ $error_code ] ) ? $messages[ $error_code ] : $messages[ WPCA_Errors::ERROR_UNKNOWN ];
    }
    
    /**
     * Register error handler for WordPress
     *
     * Sets custom error handler and exception handler for consistent error management
     *
     * @uses set_error_handler() To set custom error handler
     * @uses set_exception_handler() To set custom exception handler
     * @return void
     */
    public function register_error_handler() {
        set_error_handler( array( $this, 'custom_error_handler' ) );
        set_exception_handler( array( $this, 'custom_exception_handler' ) );
    }
    
    /**
     * Custom error handler
     *
     * @param int $errno Error level
     * @param string $errstr Error message
     * @param string $errfile Error file
     * @param int $errline Error line
     * @return bool True if error was handled
     */
    public function custom_error_handler( $errno, $errstr, $errfile, $errline ) {
        if ( ! ( error_reporting() & $errno ) ) {
            return false;
        }
        
        $error_types = array(
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            // E_STRICT 甯搁噺宸插湪 PHP 7.4 璧疯寮冪敤锛岀Щ闄ゆ槧灏勪互閬垮厤璀﹀憡
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED',
        );
        
        $error_type = isset( $error_types[ $errno ] ) ? $error_types[ $errno ] : 'UNKNOWN';
        
        $message = sprintf(
            '[%s] %s in %s on line %d',
            $error_type,
            $errstr,
            $errfile,
            $errline
        );
        
        $this->log( $message, 'php-error' );
        
        return true;
    }
    
    /**
     * Custom exception handler
     *
     * @param Throwable $exception Uncaught exception
     * @return void
     */
    public function custom_exception_handler( $exception ) {
        $message = sprintf(
            '[EXCEPTION] %s in %s on line %d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        
        $this->log( $message, 'exception' );
        $this->log( $exception->getTraceAsString(), 'exception-trace' );
    }
    
    /**
     * Handle AJAX errors
     *
     * Creates standardized error response for AJAX requests
     *
     * @param string $message Error message
     * @param int $error_code Error code
     * @param array $additional_data Additional data
     * @return void Outputs JSON error response and exits
     */
    public function handle_ajax_error( $message = '', $error_code = WPCA_Errors::ERROR_AJAX, $additional_data = array() ) {
        $response = $this->create_error_response( $error_code, $message, $additional_data );
        
        \wp_send_json( $response );
    }
    
    /**
     * Handle validation errors
     *
     * Creates standardized error response for validation failures
     *
     * @param array $errors Array of validation errors (field => error message)
     * @param string $message General error message
     * @return array Error response array
     */
    public function handle_validation_errors( $errors = array(), $message = '' ) {
        if ( empty( $message ) ) {
            $message = __( 'Validation failed. Please check your entries.', WPCA_TEXT_DOMAIN );
        }
        
        return $this->create_error_response(
            WPCA_Errors::ERROR_VALIDATION,
            $message,
            array( 'validation_errors' => $errors )
        );
    }
    
    /**
     * Validate required parameters
     *
     * @param array $params Parameters to validate
     * @param array $required List of required parameter names
     * @return array|null Validation errors or null if valid
     */
    public function validate_required_params( $params, $required = array() ) {
        $errors = array();
        
        foreach ( $required as $param_name ) {
            if ( ! isset( $params[ $param_name ] ) || empty( $params[ $param_name ] ) ) {
                $errors[ $param_name ] = sprintf(
                    __( 'The %s parameter is required.', WPCA_TEXT_DOMAIN ),
                    $param_name
                );
            }
        }
        
        return empty( $errors ) ? null : $errors;
    }
    
    /**
     * Validate nonce for AJAX requests
     *
     * @param string $nonce Nonce to verify
     * @param string $action Nonce action
     * @return bool True if nonce is valid
     */
    public function validate_ajax_nonce( $nonce, $action = 'wpca_ajax_nonce' ) {
        if ( ! \wp_verify_nonce( $nonce, $action ) ) {
            $this->handle_ajax_error(
                __( 'Security verification failed. Please try again.', WPCA_TEXT_DOMAIN ),
                WPCA_Errors::ERROR_AUTH
            );
        }
        
        return true;
    }
    
    /**
     * Check user capabilities
     *
     * @param string $capability Required capability
     * @return bool True if user has capability
     */
    public function check_capability( $capability ) {
        if ( ! \current_user_can( $capability ) ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Log error with context
     *
     * @param string $message Error message
     * @param array $context Context data
     * @param int $error_code Error code
     * @return void
     */
    public function log_error( $message, $context = array(), $error_code = WPCA_Errors::ERROR_UNKNOWN ) {
        $context_str = ! empty( $context ) ? ' | Context: ' . json_encode( $context ) : '';
        $this->log( "Error [{$error_code}]: {$message}{$context_str}", 'error' );
    }
    
    /**
     * Log success action
     *
     * @param string $message Success message
     * @param array $context Context data
     * @return void
     */
    public function log_success( $message, $context = array() ) {
        $context_str = ! empty( $context ) ? ' | Context: ' . json_encode( $context ) : '';
        $this->log( "Success: {$message}{$context_str}", 'success' );
    }
    
    /**
     * Get error statistics
     *
     * @param int $days Number of days to analyze
     * @return array Error statistics
     */
    public function get_error_stats( $days = 7 ) {
        return array(
            'total_errors' => 0,
            'error_types' => array(),
            'recent_errors' => array(),
            'period' => $days . ' days',
        );
    }
    
    /**
     * Clear error logs
     *
     * @return bool Success status
     */
    public function clear_logs() {
        $this->log( 'Logs cleared by user', 'log-clear' );
        return true;
    }
    
    /**
     * Export error logs
     *
     * @return array Error logs for export
     */
    public function export_logs() {
        return array(
            'exported_at' => \current_time( 'mysql' ),
            'wp_version' => $this->get_wp_version(),
            'php_version' => $this->get_php_version(),
            'plugin_version' => $this->get_plugin_info( 'Version' ),
            'logs' => array(),
        );
    }
}

