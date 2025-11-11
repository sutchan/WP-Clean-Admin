<?php
/**
 * WP Clean Admin - 多语言支持类
 *
 * 负责插件的国际化和本地化功能，加载语言文件和管理翻译
 *
 * @package WP_Clean_Admin
 * @since 1.4.2
 */

// 如果直接访问此文件，则中止
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(__FILE__))) . '/');
}

// 定义缺失的常量
if (!defined('WPCA_PLUGIN_FILE')) {
    define('WPCA_PLUGIN_FILE', dirname(dirname(__FILE__)) . '/wp-clean-admin.php');
}

// 提供WordPress核心函数的备用实现
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
            
            // 添加AJAX动作用于切换语言
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
        $languages_dir = plugin_dir_path(WPCA_PLUGIN_FILE) . 'wpcleanadmin/languages/';
        
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
     */
    public function get_user_language() {
        // 检查用户元数据中的语言设置
        if (is_user_logged_in() && function_exists('get_user_meta')) {
            $user_id = get_current_user_id();
            $user_lang = get_user_meta($user_id, 'wpca_user_language', true);
            if (!empty($user_lang)) {
                return $user_lang;
            }
        }
        
        // 检查插件设置中的默认语言
        if (class_exists('WPCA_Settings') && method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            if (isset($options['default_language']) && !empty($options['default_language'])) {
                return $options['default_language'];
            }
        }
        
        // 返回WordPress默认语言
        return get_locale();
    }
    
    /**
     * 设置用户语言
     * @param string $language 语言代码
     * @param int $user_id 用户ID
     * @return bool 设置是否成功
     */
    public function set_user_language($language, $user_id = null) {
        if (!function_exists('update_user_meta')) {
            return false;
        }
        
        if (null === $user_id) {
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
            } else {
                return false;
            }
        }
        
        return update_user_meta($user_id, 'wpca_user_language', $language);
    }
    
    /**
     * 获取可用的语言列表
     * @return array 语言代码和名称的映射
     */
    public function get_available_languages() {
        return array(
            'en_US' => __('English (US)', 'wp-clean-admin'),
            'zh_CN' => __('Chinese (Simplified)', 'wp-clean-admin')
        );
    }
    
    /**
     * 验证语言代码是否有效
     * @param string $language_code 语言代码
     * @return bool 是否有效
     */
    public function is_valid_language($language_code) {
        $available_languages = $this->get_available_languages();
        return array_key_exists($language_code, $available_languages);
    }
    
    /**
     * AJAX处理函数：切换用户语言
     */
    public function ajax_switch_language() {
        // 验证请求
        if (!function_exists('check_ajax_referer')) {
            wp_send_json_error(array('message' => __('Invalid request', 'wp-clean-admin')));
            return;
        }
        
        check_ajax_referer('wpca_switch_language_nonce', 'nonce');
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')));
            return;
        }
        
        // 获取并验证语言参数
        if (isset($_POST['language'])) {
            $language = sanitize_text_field($_POST['language']);
            
            if ($this->is_valid_language($language)) {
                if ($this->set_user_language($language)) {
                    wp_send_json_success(array(
                        'message' => __('Language switched successfully', 'wp-clean-admin'),
                        'language' => $language
                    ));
                } else {
                    wp_send_json_error(array('message' => __('Failed to switch language', 'wp-clean-admin')));
                }
            } else {
                wp_send_json_error(array('message' => __('Invalid language code', 'wp-clean-admin')));
            }
        } else {
            wp_send_json_error(array('message' => __('Missing language parameter', 'wp-clean-admin')));
        }
    }
    
    /**
     * 生成语言切换器HTML
     * @return string HTML代码
     */
    public function get_language_switcher_html() {
        $available_languages = $this->get_available_languages();
        $current_language = $this->get_user_language();
        $html = '';
        
        if (count($available_languages) > 1) {
            $html .= '<div class="wpca-language-switcher">';
            $html .= '<label for="wpca-language-select">' . __('Language:', 'wp-clean-admin') . '</label>';
            $html .= '<select id="wpca-language-select" name="wpca_language">';
            
            foreach ($available_languages as $code => $name) {
                $selected = selected($code, $current_language, false);
                $html .= '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($name) . '</option>';
            }
            
            $html .= '</select>';
            $html .= '<input type="hidden" id="wpca-language-nonce" value="' . wp_create_nonce('wpca_switch_language_nonce') . '" />';
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * 获取语言切换器JavaScript
     * @return string JavaScript代码
     */
    public function get_language_switcher_js() {
        $js = '';
        $available_languages = $this->get_available_languages();
        
        if (count($available_languages) > 1) {
            $js .= "
            <script type=\"text/javascript\">
            jQuery(document).ready(function($) {
                $('#wpca-language-select').on('change', function() {
                    var language = $(this).val();
                    var nonce = $('#wpca-language-nonce').val();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wpca_switch_language',
                            language: language,
                            nonce: nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert(response.data.message || '" . __('An error occurred', 'wp-clean-admin') . "');
                            }
                        },
                        error: function() {
                            alert('" . __('AJAX request failed', 'wp-clean-admin') . "');
                        }
                    });
                });
            });
            </script>
            ";
        }
        
        return $js;
    }
}

// 初始化多语言模块
if (class_exists('WPCA_i18n')) {
    WPCA_i18n::get_instance();
}
?>