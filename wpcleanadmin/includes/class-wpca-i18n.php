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
    function plugin_dir_path($file)
    {
        return trailingslashit(dirname($file));
    }
}

if (!function_exists('trailingslashit')) {
    function trailingslashit($value)
    {
        return rtrim($value, '/\\') . '/';
    }
}

if (!function_exists('get_locale')) {
    function get_locale()
    {
        return 'en_US';
    }
}

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in()
    {
        return false;
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id()
    {
        return 0;
    }
}

if (!function_exists('get_user_meta')) {
    function get_user_meta($user_id, $key = '', $single = false)
    {
        return $single ? '' : array();
    }
}

if (!function_exists('update_user_meta')) {
    function update_user_meta($user_id, $key, $value)
    {
        return false;
    }
}

if (!function_exists('check_ajax_referer')) {
    function check_ajax_referer($action = -1, $query_arg = false, $die = true)
    {
        return true;
    }
}

if (!function_exists('selected')) {
    function selected($selected, $current, $echo = true)
    {
        $result = '';
        if ($selected === $current) {
            $result = ' selected="selected"';
        }

        if ($echo) {
            echo $result;
        }

        return $result;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability)
    {
        return false;
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1)
    {
        return 'dummy_nonce';
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str)
    {
        return htmlspecialchars(strip_tags($str), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('status_header')) {
    /**
     * 设置 HTTP 状态码（WordPress 核心函数备用实现）
     * @param int $status_code HTTP 状态码
     */
    function status_header($status_code)
    {
        if (function_exists('http_response_code')) {
            http_response_code($status_code);
        } elseif (function_exists('header') && !headers_sent()) {
            $status_messages = array(
                200 => '200 OK',
                400 => '400 Bad Request',
                401 => '401 Unauthorized',
                403 => '403 Forbidden',
                404 => '404 Not Found',
                500 => '500 Internal Server Error'
            );
            $status_message = isset($status_messages[$status_code]) ? $status_messages[$status_code] : "$status_code Unknown Status";
            header("HTTP/1.1 $status_message");
        }
    }
}

if (!function_exists('wp_send_json_error')) {
    /**
     * 发送 JSON 错误响应
     * @param mixed $data 返回数据
     * @param int|null $status_code HTTP 状态码
     */
    function wp_send_json_error($data = null, $status_code = null)
    {
        // 确保输出为 JSON 格式
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        // 构建响应体
        $response = array('success' => false);
        if (null !== $data) {
            $response['data'] = $data;
        }

        // 输出 JSON
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // 设置 HTTP 状态码（仅在函数存在且状态码有效时调用）
        if (null !== $status_code) {
            status_header($status_code);
        }

        // 终止后续输出
        die;
    }
}

if (!function_exists('wp_send_json_success')) {
    /**
     * 发送 JSON 成功响应
     * @param mixed $data 返回数据
     * @param int|null $status_code HTTP 状态码
     */
    function wp_send_json_success($data = null, $status_code = null)
    {
        // 确保输出为 JSON 格式
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        // 构建响应体
        $response = array('success' => true);
        if (null !== $data) {
            $response['data'] = $data;
        }

        // 输出 JSON
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // 设置 HTTP 状态码
        if (null !== $status_code) {
            status_header($status_code);
        }

        // 终止后续输出
        die;
    }
}

/**
 * 多语言支持类
 * 管理插件的国际化和本地化功能
 */
class WPCA_i18n
{
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
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 构造函数
     */
    private function __construct()
    {
        // 注册钩子
        $this->register_hooks();
    }

    /**
     * 注册多语言相关钩子
     */
    public function register_hooks()
    {
        // 加载文本域
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // 添加 AJAX 动作用于切换语言
        add_action('wp_ajax_wpca_switch_language', array($this, 'ajax_switch_language'));
    }

    /**
     * 加载插件文本域
     * @return bool 加载是否成功
     */
    public function load_textdomain()
    {
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
     * @return string 用户语言代码（如 'en_US'）
     */
    private function get_user_language()
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return '';
        }

        $language = get_user_meta($user_id, 'wpca_language', true);
        return $language ? $language : '';
    }

    /**
     * AJAX 处理：切换语言
     */
    public function ajax_switch_language()
    {
        check_ajax_referer('wpca-switch-language', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(__('用户未登录', self::TEXT_DOMAIN));
        }

        $language = sanitize_text_field($_POST['language']);
        if (!$language) {
            wp_send_json_error(__('无效的语言参数', self::TEXT_DOMAIN));
        }

        $user_id = get_current_user_id();
        update_user_meta($user_id, 'wpca_language', $language);

        // 返回切换后的语言
        wp_send_json_success($language);
    }

    /**
     * 获取当前用户选择的语言
     * @return string 用户语言代码
     */
    public function get_current_user_language()
    {
        return $this->get_user_language();
    }
}
