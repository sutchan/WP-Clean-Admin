<?php
/**
 * WPCA_Ajax Class
 * 
 * Handles AJAX functionality for the WPCleanAdmin plugin.
 * 
 * @package    WPCleanAdmin
 * @subpackage Includes
 * @author     Sut
 * @file       wpcleanadmin/includes/class-wpca-ajax.php
 * @version    1.7.13
 * @updated    2025-06-18
 * @license    GPL-2.0+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AJAX handler class for WP Clean Admin
 */
class WPCA_Ajax {
    
    /**
     * Validate image file type
     * 
     * Checks if the provided URL points to an allowed image file type
     * for security reasons.
     * 
     * @param string $url The URL to validate
     * @return bool True if valid, false otherwise
     */
    private function validate_image_file_type( $url ) {
        // Allowed image file extensions
        $allowed_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );
        
        // Get file extension from URL
        $file_parts = explode( '.', $url );
        if ( count( $file_parts ) < 2 ) {
            return false;
        }
        
        $extension = strtolower( end( $file_parts ) );
        
        // Basic extension check
        if ( ! in_array( $extension, $allowed_extensions ) ) {
            return false;
        }
        
        // Advanced check: get MIME type if possible (for local files)
        if ( strpos( $url, site_url() ) === 0 ) {
            // For local files, try to get the actual file path and check MIME type
            $relative_path = parse_url( $url, PHP_URL_PATH );
            $file_path = ABSPATH . ltrim( $relative_path, '/' );
            
            if ( file_exists( $file_path ) ) {
                $finfo = finfo_open( FILEINFO_MIME_TYPE );
                if ( $finfo ) {
                    $mime_type = finfo_file( $finfo, $file_path );
                    finfo_close( $finfo );
                    
                    // Check if MIME type matches allowed image types
                    $allowed_mime_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
                    if ( ! in_array( $mime_type, $allowed_mime_types ) ) {
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
    
    /**
     * Save login page settings
     * 
     * Handles AJAX request to save login page customization settings
     * and sanitizes all input data for security.
     */
    public function save_login_settings() {
        // 验证请求
        if ( ! $this->validate_ajax_request( 'wpca_save_login_settings' ) ) {
            return;
        }
        
        // 获取并验证设置数据
        if ( ! isset( $_POST['login_settings'] ) || ! is_array( $_POST['login_settings'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid data structure', 'wp-clean-admin' ) ), 400 );
            return;
        }
        
        // 确保使用 isset 检查并在访问 $_POST 数据前进行过滤
            if ( function_exists( 'sanitize_text_field' ) ) {
               46: // 安全处理登录设置参数
47: $login_settings = isset( $_POST['login_settings'] ) && is_array( $_POST['login_settings'] ) ? 
48:     array_map( function( $value ) {
49:         return is_string( $value ) && function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $value ) : $value;
50:     }, $_POST['login_settings'] ) : array();
            } else {
                $login_settings = array();
            }
        $sanitized_settings = array();
        
        // 清理设置数据
        if ( isset( $login_settings['login_style'] ) ) {
            $sanitized_settings['login_style'] = sanitize_text_field( $login_settings['login_style'] );
        }
        
        if ( isset( $login_settings['login_logo_url'] ) ) {
            $logo_url = $login_settings['login_logo_url'];
            
            // Validate file type for logo URL
            if ( ! $this->validate_image_file_type( $logo_url ) ) {
                wp_send_json_error( array( 'message' => __( 'Invalid image file type. Only JPG, PNG, GIF, and WebP files are allowed.', 'wp-clean-admin' ) ) );
                return;
            }
            
            $sanitized_settings['login_logo_url'] = esc_url_raw( $logo_url );
        }
        
        if ( isset( $login_settings['login_background_url'] ) ) {
            $sanitized_settings['login_background_url'] = esc_url_raw( $login_settings['login_background_url'] );
        }
        
        if ( isset( $login_settings['login_custom_css'] ) ) {
            $sanitized_settings['login_custom_css'] = wp_kses_post( $login_settings['login_custom_css'] );
        }
        
        if ( isset( $login_settings['login_elements'] ) && is_array( $login_settings['login_elements'] ) ) {
            $sanitized_settings['login_elements'] = array_map( 'intval', $login_settings['login_elements'] );
        }
        
        // 获取现有选项并合并
        if ( class_exists( 'WPCA_Settings' ) && method_exists( 'WPCA_Settings', 'get_options' ) ) {
            $options = WPCA_Settings::get_options();
            $updated_options = array_merge( $options, $sanitized_settings );
            
            // 保存更新后的设置
            if ( method_exists( 'WPCA_Settings', 'update_options' ) ) {
                if ( WPCA_Settings::update_options( $updated_options ) ) {
                    wp_send_json_success( array( 'message' => __( 'Login settings saved successfully', 'wp-clean-admin' ) ) );
                } else {
                    wp_send_json_error( array( 'message' => __( 'Failed to save login settings', 'wp-clean-admin' ) ) );
                }
            } else {
                wp_send_json_error( array( 'message' => __( 'Settings update method not available', 'wp-clean-admin' ) ) );
            }
        } else {
            wp_send_json_error( array( 'message' => __( 'Settings class not available', 'wp-clean-admin' ) ) );
        }
    }
    
    /**
     * Get login page settings
     * 
     * Retrieves login page customization settings via AJAX
     * and ensures proper error handling.
     */
    public function get_login_settings() {
        // 验证请求
        if ( ! $this->validate_ajax_request( 'wpca_get_login_settings' ) ) {
            return;
        }
        
        // 获取设置
        if ( class_exists( 'WPCA_Settings' ) && method_exists( 'WPCA_Settings', 'get_options' ) ) {
            $options = WPCA_Settings::get_options();
            
            // 提取登录相关设置
            $login_settings = array(
                'login_style' => $options['login_style'] ?? 'default',
                'login_logo_url' => $options['login_logo_url'] ?? '',
                'login_background_url' => $options['login_background_url'] ?? '',
                'login_custom_css' => $options['login_custom_css'] ?? '',
                'login_elements' => $options['login_elements'] ?? array(
                    'language_switcher' => 1,
                    'home_link' => 1,
                    'register_link' => 1,
                    'remember_me' => 1
                )
            );
            
            wp_send_json_success( array( 'login_settings' => $login_settings ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Settings class not available', 'wp-clean-admin' ) ) );
        }
    }
    
    /**
     * Reset login page settings
     * 
     * Handles AJAX request to reset login page customization
     * settings to default values.
     */
    public function reset_login_settings() {
        // 验证请求
        if ( ! $this->validate_ajax_request( 'wpca_reset_login_settings' ) ) {
            return;
        }
        
        // 获取现有选项并移除登录相关设置
        if ( class_exists( 'WPCA_Settings' ) && method_exists( 'WPCA_Settings', 'get_options' ) ) {
            $options = WPCA_Settings::get_options();
            
            // 删除登录相关设置
            $login_settings_keys = array(
                'login_style',
                'login_logo_url',
                'login_background_url',
                'login_custom_css',
                'login_elements'
            );
            
            foreach ( $login_settings_keys as $key ) {
                if ( isset( $options[ $key ] ) ) {
                    unset( $options[ $key ] );
                }
            }
            
            // 保存更新后的设置
            if ( method_exists( 'WPCA_Settings', 'update_options' ) ) {
                if ( WPCA_Settings::update_options( $options ) ) {
                    wp_send_json_success( array( 'message' => __( 'Login settings reset successfully', 'wp-clean-admin' ) ) );
                } else {
                    wp_send_json_error( array( 'message' => __( 'Failed to reset login settings', 'wp-clean-admin' ) ) );
                }
            } else {
                wp_send_json_error( array( 'message' => __( 'Settings update method not available', 'wp-clean-admin' ) ) );
            }
        } else {
            wp_send_json_error( array( 'message' => __( 'Settings class not available', 'wp-clean-admin' ) ) );
        }
    }
    
    /**
     * Constructor
     * 
     * Initializes the AJAX handler and sets up hooks
     */
    public function __construct() {
        // Initialize hooks
        $this->init_hooks();
    }
    
    /**
     * Initialize AJAX hooks
     */
    public function init_hooks() {
        if ( function_exists( 'add_action' ) ) {
            // Public AJAX actions (available to both logged in and non-logged in users)
            add_action( 'wp_ajax_nopriv_wpca_get_public_data', array( $this, 'get_public_data' ) );
            
            // Admin AJAX actions (only available to logged in users with proper permissions)
            add_action( 'wp_ajax_wpca_toggle_menu', array( $this, 'toggle_menu' ) );
            add_action( 'wp_ajax_wpca_update_menu_order', array( $this, 'update_menu_order' ) );
            add_action( 'wp_ajax_wpca_reset_menu', array( $this, 'reset_menu' ) ); // 添加重置菜单方法
            add_action( 'wp_ajax_wpca_reset_menu_order', array( $this, 'reset_menu_order' ) ); // 添加重置菜单顺序方法
            add_action( 'wp_ajax_wpca_reset_settings', array( $this, 'reset_settings' ) );
            add_action( 'wp_ajax_wpca_save_settings', array( $this, 'save_settings' ) );
            add_action( 'wp_ajax_wpca_get_settings', array( $this, 'get_settings' ) );
            add_action( 'wp_ajax_wpca_update_dashboard_widgets', array( $this, 'update_dashboard_widgets' ) );
            // 登录页面自定义相关 AJAX 操作