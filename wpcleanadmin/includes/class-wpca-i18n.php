<?php
/**
 * WP Clean Admin - 多语言支持类
 * 负责插件的国际化和本地化功能，加载语言文件和管理翻译
 * @package WP_Clean_Admin
 * @version 1.7.12
 * @since 1.4.2
 */

// 如果直接访问此文件，则中止
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(__FILE__))) . '/');
}

// 定义缺少的常量
if (!defined('WPCA_MAIN_FILE')) {
    define('WPCA_MAIN_FILE', dirname(dirname(__FILE__)) . '/wp-clean-admin.php');
}

// 提供 WordPress 核心函数的备用实现
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path( $file ) {
        return trailingslashit(dirname($file));
    }
}

if (!function_exists('trailingslashit')) {
    function trailingslashit( $value ) {
        return rtrim($value, '/\\') . '/';
    }
}

if (!function_exists('get_locale')) {
    function get_locale() {
        return 'en_US';
    }
}

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        return false;
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return 0;
    }
}

if (!function_exists('get_user_meta')) {
    function get_user_meta( $user_id, $key = '', $single = false ) {
        return $single ? '' : array();
    }
}

if (!function_exists('update_user_meta')) {
    function update_user_meta( $user_id, $key, $value ) {
        return false;
    }
}

if (!function_exists('check_ajax_referer')) {
    function check_ajax_referer( $action = -1, $query_arg = false, $die = true ) {
        return true;
    }
}

if (!function_exists('selected')) {
    function selected( $selected, $current, $echo = true ) {
        $result = '';
        if ( $selected === $current ) {
            $result = ' selected="selected"';
        }
        
        if ( $echo ) {
            echo $result;
        }
        
        return $result;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can( $capability ) {
        return false;
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce( $action = -1 ) {
        return 'dummy_nonce';
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field( $str ) {
        return filter_var($str, FILTER_SANITIZE_STRING);
    }
}

if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error( $data = null, $status_code = null ) {
        header( 'Content-Type: application/json; charset=utf-8' );
        echo json_encode( array( 'success' => false, 'data' => $data ) );
        if ( null !== $status_code ) {
            status_header( $status_code );
        }
        die;
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success( $data = null, $status_code = null ) {
        header( 'Content-Type: application/json; charset=utf-8' );
        echo json_encode( array( 'success' => true, 'data' => $data ) );
        if ( null !== $status_code ) {
            status_header( $status_code );
        }
        die;
    }
}

/**
 * 多语言支持类
 * 管理插件的国际化和本地化功能
 */
class WPCA_i18n {
    /**
     * 插件文本域
     * @var string
     */
    const TEXT_DOMAIN = 'wp-clean-admin';
    
    /**
     * 单例实例
     * @var WPCA_i18n
     */
    private static $instance = null;
    
    /**
     * 已加载的语言文件列表
     * @var array
     */
    private $loaded_languages = array();
    
    /**
     * 获取单例实例
     * @return WPCA_i18n
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        // 注册钩子
        $this->register_hooks();
    }
    
    /**
     * 注册多语言相关钩子
     */
    public function register_hooks() {
        if (function_exists('add_action')) {
            // 加载文本域
            add_action('plugins_loaded', array($this, 'load_textdomain'));
            
            // 添加 AJAX 动作用于切换语言
            add_action('wp_ajax_wpca_switch_language', array($this, 'ajax_switch_language'));
        }
    }
    
    /**
     * 加载插件文本域
     * @return bool 加载是否成功
     */
    public function load_textdomain() {
        if (!function_exists('load_plugin_textdomain') || !function_exists('plugin_dir_path')) {
            return false;
        }
        
        // 定义语言目录
        $languages_dir = plugin_dir_path(WPCA_MAIN_FILE) . 'wpcleanadmin/languages/';
        
        // 获取用户选择的语言（如果有）
        $user_language = $this->get_user_language();
        
        // 加载默认语言文件
        $result = load_plugin_textdomain(
            self::TEXT_DOMAIN,
            false,
            $languages_dir
        );
        
        // 记录加载状态
        $current_locale = get_locale();
        $this->loaded_languages[$current_locale] = $result;
        
        return $result;
    }
    
    /**
     * 获取用户选择的语言
     * @return string 用户语言代码