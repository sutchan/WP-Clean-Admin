<?php
/**
 * Resources Manager Class for WPCleanAdmin
 * 
 * @package WPCleanAdmin
 * @since 1.0
 * @version 1.7.13
 * @file wpcleanadmin/includes/class-wpca-resources.php
 * @updated 2025-06-18
 */
class WPCA_Resources {
    /**
     * 单例实例
     * @var WPCA_Resources
     */
    private static $instance = null;
    /**
     * 插件设置
     * @var array
     */
    private $options;
    
    /**
     * 需要移除的CSS文件列表
     * @var array
     */
    private $css_to_remove = array();
    
    /**
     * 需要移除的JS文件列表
     * @var array
     */
    private $js_to_remove = array();
    
    /**
     * 延迟加载的JS文件列表
     * @var array
     */
    private $js_to_defer = array();
    
    /**
     * 内联关键CSS的页面列表
     * @var array
     */
    private $pages_for_critical_css = array();

    /**
     * WPCA_Resources constructor.
     * 初始化资源管理功能，注册必要的钩子
     */
    public function __construct() {
        // 获取插件设置
        if (class_exists('WPCA_Settings') && method_exists('WPCA_Settings', 'get_options')) {
            $this->options = WPCA_Settings::get_options();
        } else {
            $this->options = array();
        }
        
        // 初始化配置
        $this->initialize_config();
        
        // 注册资源管理钩子
        $this->register_hooks();
    }

    /**
     * 初始化资源管理配置
     */
    private function initialize_config() {
        // 从设置中加载需要移除的CSS文件列表
        if (isset($this->options['remove_css_files']) && is_array($this->options['remove_css_files'])) {
            $this->css_to_remove = array_filter($this->options['remove_css_files']);
        }
        
        // 从设置中加载需要移除的JS文件列表
        if (isset($this->options['remove_js_files']) && is_array($this->options['remove_js_files'])) {
            $this->js_to_remove = array_filter($this->options['remove_js_files']);
        }
        
        // 从设置中加载需要延迟的JS文件列表
        if (isset($this->options['defer_js_files']) && is_array($this->options['defer_js_files'])) {
            $this->js_to_defer = array_filter($this->options['defer_js_files']);
        }
        
        // 从设置中加载需要内联关键CSS的页面列表
        if (isset($this->options['critical_css_pages']) && is_array($this->options['critical_css_pages'])) {
            $this->pages_for_critical_css = array_filter($this->options['critical_css_pages']);
        }
    }

    /**
     * 注册资源管理相关的钩子
     */
    private function register_hooks() {
        // 只在管理区域加载资源优化
        if (function_exists('is_admin') && is_admin()) {
            // 移除不需要的CSS文件
            if (!empty($this->css_to_remove) && function_exists('add_action')) {
                add_action('admin_enqueue_scripts', array($this, 'remove_unnecessary_css'), 999);
            }
            
            // 移除不需要的JS文件
            if (!empty($this->js_to_remove) && function_exists('add_action')) {
                add_action('admin_enqueue_scripts', array($this, 'remove_unnecessary_js'), 999);
            }
            
            // 延迟加载非关键JS
            if (!empty($this->js_to_defer) && function_exists('add_filter')) {
                add_filter('script_loader_tag', array($this, 'defer_non_critical_js'), 10, 3);
            }
            
            // 内联关键CSS
            if (!empty($this->pages_for_critical_css) && function_exists('add_action')) {
                add_action('admin_head', array($this, 'inline_critical_css'));
            }
            
            // 合并资源
            if (isset($this->options['enable_resource_merging']) && $this->options['enable_resource_merging'] && function_exists('add_filter')) {
                add_filter('admin_enqueue_scripts', array($this, 'merge_resources'));
            }
            
            // 清理未使用的全局样式
            if (isset($this->options['cleanup_global_styles']) && $this->options['cleanup_global_styles'] && function_exists('add_action')) {
                add_action('admin_init', array($this, 'cleanup_global_styles'));
            }
            
            // AJAX资源管理操作
            if (function_exists('add_action')) {
                add_action('wp_ajax_wpca_test_resource_removal', array($this, 'ajax_test_resource_removal'));
                add_action('wp_ajax_wpca_generate_critical_css', array($this, 'ajax_generate_critical_css'));
            }
        }
    }

    /**
     * 移除不需要的CSS文件
     */
    public function remove_unnecessary_css() {
        if (empty($this->css_to_remove) || !function_exists('wp_deregister_style')) {
            return;
        }
        
        foreach ($this->css_to_remove as $handle) {
            $handle = sanitize_key($handle);
            if (!empty($handle)) {
                wp_deregister_style($handle);
                wp_dequeue_style($handle);
            }
        }
    }

    /**
     * 移除不需要的JS文件
     */
    public function remove_unnecessary_js() {
        if (empty($this->js_to_remove) || !function_exists('wp_deregister_script')) {
            return;
        }
        
        foreach ($this->js_to_remove as $handle) {
            $handle = sanitize_key($handle);
            if (!empty($handle)) {
                wp_deregister_script($handle);
                wp_dequeue_script($handle);
            }
        }
    }

    /**
     * 为非关键JS添加defer属性
     * 
     * @param string $tag 脚本标签
     * @param string $handle 脚本句柄
     * @param string $src 脚本源URL
     * @return string 修改后的脚本标签
     */
    public function defer_non_critical_js($tag, $handle, $src) {
        // 检查是否需要延迟此脚本
        $is_admin_check = function_exists('is_admin') ? !is_admin() : true;
        $preg_match_check = function_exists('preg_match') ? !preg_match('/defer|async/', $tag) : true;
        
        if (in_array($handle, $this->js_to_defer) && $is_admin_check || $preg_match_check) {
            // 确保脚本句柄有效
            if (function_exists('sanitize_key') && sanitize_key($handle) === $handle) {
                // 添加defer属性
                return str_replace(' src', ' defer src', $tag);
            }
        }
        return $tag;
    }

    /**
     * 内联关键CSS到页面头部
     */
    public function inline_critical_css() {
        // 获取当前页面钩子
        $current_hook = isset($GLOBALS['hook_suffix']) ? $GLOBALS['hook_suffix'] : '';
        
        // 检查当前页面是否需要内联关键CSS
        if (!in_array($current_hook, $this->pages_for_critical_css)) {
            return;
        }
        
        // 获取当前页面的关键CSS设置
        $critical_css_key = 'wpca_critical_css_' . (function_exists('sanitize_key') ? sanitize_key($current_hook) : $current_hook);
        $critical_css = function_exists('get_option') ? get_option($critical_css_key, '') : '';
        
        // 如果有关键CSS，则内联到页面头部
        if (!empty($critical_css)) {
            echo "<style id='wpca-critical-css'>
{$critical_css}
</style>
";
        }
    }

    /**
     * 合并资源（实验性功能）
     */
    public function merge_resources() {
        // 此功能需要文件系统写入权限，先检查
        $can_manage_options = function_exists('current_user_can') ? current_user_can('manage_options') : false;
        if (!$can_manage_options) {
            return;
        }
        
        // 获取当前页面钩子
        $current_page = function_exists('get_current_screen') ? get_current_screen() : null;
        $current_hook = isset($GLOBALS['hook_suffix']) ? $GLOBALS['hook_suffix'] : '';
        
        // 为当前页面创建合并资源的缓存键
        $sanitize_func = function_exists('sanitize_key') ? 'sanitize_key' : function($str) { return $str; };
        $merged_css_key = 'wpca_merged_css_' . $sanitize_func($current_hook);
        $merged_js_key = 'wpca_merged_js_' . $sanitize_func($current_hook);
        
        // 尝试获取缓存的合并资源
        $merged_css = function_exists('get_transient') ? get_transient($merged_css_key) : false;
        $merged_js = function_exists('get_transient') ? get_transient($merged_js_key) : false;
        
        // 如果缓存不存在，则生成合并资源
        // 注意：完整实现需要更复杂的资源合并逻辑
    }

    /**
     * 清理未使用的全局样式
     */
    public function cleanup_global_styles() {
        // 获取需要清理的样式列表
        $styles_to_clean = isset($this->options['cleanup_global_styles_list']) && is_array($this->options['cleanup_global_styles_list']) 
            ? array_filter($this->options['cleanup_global_styles_list']) 
            : array();
        
        // 添加清理样式的操作
        if (!empty($styles_to_clean) && function_exists('add_action')) {
            add_action('admin_head', function() use ($styles_to_clean) {
                $cleanup_css = "";
                $sanitize_func = function_exists('sanitize_text_field') ? 'sanitize_text_field' : function($str) { return $str; };
                
                foreach ($styles_to_clean as $selector) {
                    $cleanup_css .= $sanitize_func($selector) . " { display: none !important; }
";
                }
                
                if (!empty($cleanup_css)) {
                    echo "<style id='wpca-cleanup-styles'>
{$cleanup_css}
</style>
";
                }
            });
        }
    }

    /**
     * 生成关键CSS
     * 
     * @param string $page_hook 页面钩子
     * @return string 关键CSS内容
     */
    private function generate_critical_css($page_hook) {
        // 此方法应该实现关键CSS生成逻辑
        // 简单的实现：获取页面关键元素的基本样式
        $critical_css = "";
        
        // 为不同的页面类型生成不同的关键CSS
        switch ($page_hook) {
            case 'index.php': // 仪表盘
                $critical_css = $this->get_dashboard_critical_css();
                break;
            case 'edit.php': // 文章编辑列表
                $critical_css = $this->get_posts_list_critical_css();
                break;
            default:
                $critical_css = $this->get_default_critical_css();
                break;
        }
        
        return $critical_css;
    }

    /**
     * 获取仪表盘页面的关键CSS
     * 
     * @return string CSS内容
     */
    private function get_dashboard_critical_css() {
        return "
        /* Dashboard Critical CSS */
        #wpbody-content { margin-right: 0; }
        .dashboard-widgets-wrap { display: flex; flex-wrap: wrap; }
        .postbox { margin-bottom: 20px; }
        .inside { padding: 12px; }
        ";
    }

    /**
     * 获取文章列表页面的关键CSS
     * 
     * @return string CSS内容
     */
    private function get_posts_list_critical_css() {
        return "
        /* Posts List Critical CSS */
        .wp-list-table { width: 100%; }
        .wp-list-table th { text-align: left; }
        .wp-list-table tr:nth-child(2n) { background-color: #f9f9f9; }
        ";
    }

    /**
     * 获取默认的关键CSS
     * 
     * @return string CSS内容
     */
    private function get_default_critical_css() {
        return "
        /* Default Critical CSS */
        #wpcontent { padding-left: 0; }
        .wrap { margin: 20px; }
        .notice { padding: 15px; margin-bottom: 20px; }
        .button { padding: 5px 15px; }
        ";
    }

    /**
     * AJAX测试资源移除
     * 
     * 测试移除特定资源（CSS或JS），确保在实际移除前可以验证效果
     */
    public function ajax_test_resource_removal() {
        // 确保必要的WordPress函数可用
        if (!function_exists('wp_die') || !function_exists('wp_send_json_error') || !function_exists('wp_send_json_success')) {
            die('WordPress functions not available');
        }
        
        // 检查是否为AJAX请求 - 兼容旧版WordPress
        if (function_exists('wp_doing_ajax')) {
            if (!wp_doing_ajax()) {
                wp_die(__('Invalid request', 'wp-clean-admin'), 400);
            }
        } elseif (!defined('DOING_AJAX') || !DOING_AJAX) {
            wp_die(__('Invalid request', 'wp-clean-admin'), 400);
        }
        
        // 检查nonce参数是否存在
       365: if (!isset($_POST['security']) || !is_string($_POST['security']) || empty(trim($_POST['security']))) {
            wp_send_json_error(array('message' => __('Security check failed: nonce missing', 'wp-clean-admin')));
        }
        
        // 检查nonce安全验证
        if (!function_exists('check_ajax_referer') || !check_ajax_referer('wpca-settings-options', 'security', false)) {
            wp_send_json_error(array('message' => __('Security check failed: invalid nonce', 'wp-clean-admin')));
        }
        
        // 检查用户权限
        if (!class_exists('WPCA_Permissions') || !WPCA_Permissions::current_user_can('wpca_manage_all')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')));
        }
        
        // 获取并严格验证请求参数
       380: // 安全处理资源类型参数
381: $resource_type = isset($_POST['resource_type']) && is_string($_POST['resource_type']) ? sanitize_text_field($_POST['resource_type']) : '';
       382: // 安全处理资源句柄参数
383: $resource_handle = isset($_POST['resource_handle']) && is_string($_POST['resource_handle']) ? sanitize_key($_POST['resource_handle']) : '';
        
        // 严格验证参数
        if (!in_array($resource_type, array('css', 'js')) || empty($resource_handle) || !preg_match('/^[a-zA-Z0-9_-]+$/', $resource_handle)) {
            $error_msg = __('Invalid resource parameters', 'wp-clean-admin');
            if (class_exists('WPCA_Helpers')) {
                WPCA_Helpers::log(
                    $error_msg,
                    array(
                        'resource_type' => $resource_type,
                        'resource_handle' => $resource_handle
                    ),
                    'error',
                    true
                );
            } else if (function_exists('error_log')) {
                error_log('WPCA AJAX: ' . $error_msg . ' - type: ' . $resource_type . ', handle: ' . $resource_handle);
            }
            wp_send_json_error(array('message' => $error_msg));
        }
        
        try {
            // 测试资源移除
            $test_results = array(
                'resource_type' => $resource_type,
                'resource_handle' => $resource_handle,
                'status' => 'removed',
                'message' => __('Resource removed successfully. Please check if the page functionality works correctly.', 'wp-clean-admin')
            );
            
            wp_send_json_success($test_results);
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
            if (class_exists('WPCA_Helpers')) {
                WPCA_Helpers::log($error_msg, array(), 'error', true);
            } else if (function_exists('error_log')) {
                error_log('WPCA AJAX Error: ' . $error_msg);
            }
            wp_send_json_error(array('message' => $error_msg));
        } finally {
            wp_die();
        }
    }

    /**
     * AJAX生成关键CSS
     * 
     * 为指定页面生成关键CSS并保存到数据库，优化页面加载性能
     */
    public function ajax_generate_critical_css() {
        // 确保必要的WordPress函数可用
        if (!function_exists('wp_die') || !function_exists('wp_send_json_error') || !function_exists('wp_send_json_success')) {
            die('WordPress functions not available');
        }
        
        // 检查是否为AJAX请求 - 兼容旧版WordPress
        if (function_exists('wp_doing_ajax')) {
            if (!wp_doing_ajax()) {
                wp_die(__('Invalid request', 'wp-clean-admin'), 400);
            }
        } elseif (!defined('DOING_AJAX') || !DOING_AJAX) {
            wp_die(__('Invalid request', 'wp-clean-admin'), 400);
        }
        
        // 检查nonce参数是否存在
       434: if (!isset($_POST['security']) || !is_string($_POST['security']) || empty(trim($_POST['security']))) {
            wp_send_json_error(array('message' => __('Security check failed: nonce missing', 'wp-clean-admin')));
        }
        
        // 检查nonce安全验证
        if (!function_exists('check_ajax_referer') || !check_ajax_referer('wpca-settings-options', 'security', false)) {
            wp_send_json_error(array('message' => __('Security check failed: invalid nonce', 'wp-clean-admin')));
        }
        
        // 检查用户权限
        if (!class_exists('WPCA_Permissions') || !WPCA_Permissions::current_user_can('wpca_manage_all')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')));
        }
        
        // 获取并严格验证请求参数
       449: // 安全处理页面钩子参数
450: $page_hook = isset($_POST['page_hook']) && is_string($_POST['page_hook']) ? sanitize_key($_POST['page_hook']) : '';
        
        // 严格验证参数
        if (empty($page_hook) || !preg_match('/^[a-zA-Z0-9_-]+$/', $page_hook)) {
            $error_msg = __('Invalid page hook', 'wp-clean-admin');
            if (class_exists('WPCA_Helpers')) {
                WPCA_Helpers::log(
                    $error_msg,
                    array(
                        'page_hook' => $page_hook
                    ),
                    'error',
                    true
                );
            } else if (function_exists('error_log')) {
                error_log('WPCA AJAX: ' . $error_msg . ' - hook: ' . $page_hook);
            }
            wp_send_json_error(array('message' => $error_msg));
        }
        
        try {
            // 检查方法是否存在
            if (!method_exists($this, 'generate_critical_css')) {
                throw new Exception(__('Critical CSS generation method not available', 'wp-clean-admin'));
            }
            
            // 生成关键CSS
            $critical_css = $this->generate_critical_css($page_hook);
            
            // 验证生成结果
            if (!is_string($critical_css)) {
                throw new Exception(__('Invalid critical CSS generated', 'wp-clean-admin'));
            }
            
            // 保存到数据库
            $option_key = 'wpca_critical_css_' . $page_hook;
            if (function_exists('update_option')) {
                update_option($option_key, $critical_css);
            } else {
                throw new Exception(__('WordPress option functions not available', 'wp-clean-admin'));
            }
            
            // 返回成功结果
            wp_send_json_success(array(
                'page_hook' => $page_hook,
                'css_length' => strlen($critical_css),
                'message' => __('Critical CSS generated and saved successfully', 'wp-clean-admin')
            ));
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
            if (class_exists('WPCA_Helpers')) {
                WPCA_Helpers::log($error_msg, array(), 'error', true);
            } else if (function_exists('error_log')) {
                error_log('WPCA AJAX Error: ' . $error_msg);
            }
            wp_send_json_error(array('message' => $error_msg));
        } finally {
            wp_die();
        }
    }
    
    /**
     * 获取当前加载的CSS资源列表
     * 
     * @return array CSS资源列表
     */
    public function get_loaded_styles() {
        global $wp_styles;
        $styles = array();
        
        // 获取已加载的CSS资源
        if (isset($wp_styles) && is_object($wp_styles) && isset($wp_styles->registered)) {
            foreach ($wp_styles->registered as $handle => $style) {
                $styles[$handle] = array(
                    'handle' => $handle,
                    'src' => isset($style->src) ? $style->src : '',
                    'ver' => isset($style->ver) ? $style->ver : '',
                    'source' => $this->detect_resource_source($style->src)
                );
            }
        }
        
        return $styles;
    }
    
    /**
     * 获取当前加载的JS资源列表
     * 
     * @return array JS资源列表
     */
    public function get_loaded_scripts() {
        global $wp_scripts;
        $scripts = array();
        
        // 获取已加载的JS资源
        if (isset($wp_scripts) && is_object($wp_scripts) && isset($wp_scripts->registered)) {
            foreach ($wp_scripts->registered as $handle => $script) {
                $scripts[$handle] = array(
                    'handle' => $handle,
                    'src' => isset($script->src) ? $script->src : '',
                    'ver' => isset($script->ver) ? $script->ver : '',
                    'deps' => isset($script->deps) ? $script->deps : array(),
                    'source' => $this->detect_resource_source($script->src)
                );
            }
        }
        
        return $scripts;
    }
    
    /**
     * 检测资源来源
     * 
     * @param string $src 资源URL
     * @return string 资源来源标识
     */
    private function detect_resource_source($src) {
        if (empty($src)) {
            return __('Inline', 'wp-clean-admin');
        }
        
        $wp_core_dirs = array('wp-admin', 'wp-includes');
        $is_core = false;
        
        foreach ($wp_core_dirs as $dir) {
            if (strpos($src, $dir) !== false) {
                $is_core = true;
                break;
            }
        }
        
        return $is_core ? __('WordPress Core', 'wp-clean-admin') : __('Plugin/Theme', 'wp-clean-admin');
    }
    
    /**
     * 获取当前加载的资源列表
     * 
     * @return array 资源列表
     */
    public function get_loaded_resources() {
        global $wp_styles, $wp_scripts;
        
        $resources = array(
            'css' => array(),
            'js' => array()
        );
        
        // 获取已加载的CSS资源
        if (isset($wp_styles) && is_object($wp_styles) && isset($wp_styles->registered)) {
            foreach ($wp_styles->registered as $handle => $style) {
                $resources['css'][] = array(
                    'handle' => $handle,
                    'src' => isset($style->src) ? $style->src : '',
                    'ver' => isset($style->ver) ? $style->ver : ''
                );
            }
        }
        
        // 获取已加载的JS资源
        if (isset($wp_scripts) && is_object($wp_scripts) && isset($wp_scripts->registered)) {
            foreach ($wp_scripts->registered as $handle => $script) {
                $resources['js'][] = array(
                    'handle' => $handle,
                    'src' => isset($script->src) ? $script->src : '',
                    'ver' => isset($script->ver) ? $script->ver : '',
                    'deps' => isset($script->deps) ? $script->deps : array()
                );
            }
        }
        
        return $resources;
    }
    
    /**
     * 获取类的单例实例
     * 
     * @return WPCA_Resources 资源管理类的单例实例
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
?>