<?php
/**
 * WordPress 函数存根
 * 用于IDE识别WordPress函数，避免未定义函数错误
 * 
 * @package WPCleanAdmin
 * @version 1.7.13
 * @file wpcleanadmin/includes/wpca-wordpress-stubs.php
 * @updated 2025-06-18
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// 检查是否在WordPress环境中，如果不在，则定义函数存根
if (!function_exists('is_admin')) {
    /**
     * 检查是否在管理界面
     *
     * @return bool
     */
    function is_admin() {
        return false;
    }
}

if (!function_exists('add_action')) {
    /**
     * 添加动作钩子
     *
     * @param string $tag 钩子名称
     * @param callable $function_to_add 回调函数
     * @param int $priority 优先级
     * @param int $accepted_args 接受的参数数量
     * @return bool
     */
    function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('add_filter')) {
    /**
     * 添加过滤器钩子
     *
     * @param string $tag 钩子名称
     * @param callable $function_to_add 回调函数
     * @param int $priority 优先级
     * @param int $accepted_args 接受的参数数量
     * @return bool
     */
    function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('remove_meta_box')) {
    /**
     * 移除仪表板小部件
     *
     * @param string $id 小部件ID
     * @param string $screen 屏幕ID
     * @param string $context 上下文
     * @return void
     */
    function remove_meta_box($id, $screen, $context) {
        // 存根函数
    }
}

if (!function_exists('sanitize_key')) {
    /**
     * 清理键名
     *
     * @param string $key 键名
     * @return string
     */
    function sanitize_key($key) {
        return $key;
    }
}

if (!function_exists('sanitize_html_class')) {
    /**
     * 清理HTML类名
     *
     * @param string $class 类名
     * @param string $fallback 备用类名
     * @return string
     */
    function sanitize_html_class($class, $fallback = '') {
        return $class;
    }
}

if (!function_exists('wp_add_inline_style')) {
    /**
     * 添加内联样式
     *
     * @param string $handle 样式句柄
     * @param string $inline_style 内联样式
     * @return bool
     */
    function wp_add_inline_style($handle, $inline_style) {
        return true;
    }
}

if (!function_exists('show_admin_bar')) {
    /**
     * 显示/隐藏管理栏
     *
     * @param bool $show 是否显示
     * @return void
     */
    function show_admin_bar($show) {
        // 存根函数
    }
}

if (!function_exists('home_url')) {
    /**
     * 获取首页URL
     *
     * @param string $path 路径
     * @param string $scheme 协议
     * @return string
     */
    function home_url($path = '', $scheme = null) {
        return 'http://example.com';
    }
}

if (!function_exists('get_bloginfo')) {
    /**
     * 获取博客信息
     *
     * @param string $show 信息类型
     * @param string $filter 过滤器
     * @return string
     */
    function get_bloginfo($show = '', $filter = 'raw') {
        return 'WordPress';
    }
}

if (!function_exists('wp_parse_args')) {
    /**
     * 解析参数
     *
     * @param array|string $args 参数
     * @param array $defaults 默认值
     * @return array
     */
    function wp_parse_args($args, $defaults = '') {
        if (is_object($args)) {
            $r = get_object_vars($args);
        } elseif (is_array($args)) {
            $r =& $args;
        } else {
            wp_parse_str($args, $r);
        }

        if (is_array($defaults)) {
            return array_merge($defaults, $r);
        }
        
        return $r;
    }
}

if (!function_exists('wp_parse_str')) {
    /**
     * 解析字符串为数组
     *
     * @param string $input_string 输入字符串
     * @param array $result 结果数组
     * @return array
     */
    function wp_parse_str($input_string, &$result) {
        parse_str($input_string, $result);
        
        if (get_magic_quotes_gpc()) {
            $result = stripslashes_deep($result);
        }
        
        return $result;
    }
}

if (!function_exists('get_magic_quotes_gpc')) {
    /**
     * 获取magic_quotes_gpc设置
     *
     * @return int
     */
    function get_magic_quotes_gpc() {
        return 0;
    }
}

if (!function_exists('stripslashes_deep')) {
    /**
     * 递归去除斜杠
     *
     * @param mixed $value 值
     * @return mixed
     */
    function stripslashes_deep($value) {
        if (is_array($value)) {
            $value = array_map('stripslashes_deep', $value);
        } elseif (is_object($value)) {
            $vars = get_object_vars($value);
            foreach ($vars as $key => $data) {
                $value->{$key} = stripslashes_deep($data);
            }
        } elseif (is_string($value)) {
            $value = stripslashes($value);
        }
        
        return $value;
    }
}

if (!function_exists('get_option')) {
    /**
     * 获取选项
     *
     * @param string $option 选项名称
     * @param mixed $default 默认值
     * @return mixed
     */
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('add_menu_page')) {
    /**
     * 添加菜单页面
     *
     * @param string $page_title 页面标题
     * @param string $menu_title 菜单标题
     * @param string $capability 权限
     * @param string $menu_slug 菜单slug
     * @param callable $function 回调函数
     * @param string $icon_url 图标URL
     * @param int $position 位置
     * @return string
     */
    function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null) {
        return $menu_slug;
    }
}

if (!function_exists('add_submenu_page')) {
    /**
     * 添加子菜单页面
     *
     * @param string $parent_slug 父菜单slug
     * @param string $page_title 页面标题
     * @param string $menu_title 菜单标题
     * @param string $capability 权限
     * @param string $menu_slug 菜单slug
     * @param callable $function 回调函数
     * @return string
     */
    function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '') {
        return $menu_slug;
    }
}

if (!function_exists('register_setting')) {
    /**
     * 注册设置
     *
     * @param string $option_group 选项组
     * @param string $option_name 选项名称
     * @param array $args 参数
     * @return void
     */
    function register_setting($option_group, $option_name, $args = array()) {
        // 存根函数
    }
}

if (!function_exists('settings_fields')) {
    /**
     * 输出设置字段
     *
     * @param string $option_group 选项组
     * @return void
     */
    function settings_fields($option_group) {
        // 存根函数
    }
}

if (!function_exists('do_settings_sections')) {
    /**
     * 输出设置部分
     *
     * @param string $page 页面
     * @return void
     */
    function do_settings_sections($page) {
        // 存根函数
    }
}

if (!function_exists('submit_button')) {
    /**
     * 输出提交按钮
     *
     * @param string $text 按钮文本
     * @param string $type 按钮类型
     * @param string $name 按钮名称
     * @param bool $wrap 是否包装
     * @param array|string $other_attributes 其他属性
     * @return void
     */
    function submit_button($text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null) {
        // 存根函数
    }
}

if (!function_exists('wp_nonce_field')) {
    /**
     * 输出nonce字段
     *
     * @param int|string $action 动作
     * @param string $name 名称
     * @param bool $referer 是否包含referer字段
     * @param bool $echo 是否输出
     * @return string
     */
    function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {
        return '<input type="hidden" name="' . $name . '" value="nonce" />';
    }
}

if (!function_exists('wp_verify_nonce')) {
    /**
     * 验证nonce
     *
     * @param string $nonce nonce值
     * @param string|int $action 动作
     * @return bool|int
     */
    function wp_verify_nonce($nonce, $action = -1) {
        return 1;
    }
}

if (!function_exists('check_admin_referer')) {
    /**
     * 检查管理员引用
     *
     * @param int|string $action 动作
     * @param string $query_arg 查询参数
     * @return bool|int
     */
    function check_admin_referer($action = -1, $query_arg = '_wpnonce') {
        return 1;
    }
}

if (!function_exists('wp_die')) {
    /**
     * WordPress die函数
     *
     * @param string $message 消息
     * @param string $title 标题
     * @param array $args 参数
     * @return void
     */
    function wp_die($message = '', $title = '', $args = array()) {
        die($message);
    }
}

if (!function_exists('current_user_can')) {
    /**
     * 检查当前用户权限
     *
     * @param string $capability 权限
     * @return bool
     */
    function current_user_can($capability) {
        return true;
    }
}

if (!function_exists('is_user_logged_in')) {
    /**
     * 检查用户是否登录
     *
     * @return bool
     */
    function is_user_logged_in() {
        return true;
    }
}

if (!function_exists('wp_get_current_user')) {
    /**
     * 获取当前用户
     *
     * @return WP_User
     */
    function wp_get_current_user() {
        return new stdClass();
    }
}

if (!function_exists('wp_redirect')) {
    /**
     * 重定向
     *
     * @param string $location URL
     * @param int $status 状态码
     * @return bool
     */
    function wp_redirect($location, $status = 302) {
        header('Location: ' . $location, true, $status);
        return true;
    }
}

if (!function_exists('esc_html')) {
    /**
     * 转义HTML
     *
     * @param string $text 文本
     * @return string
     */
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    /**
     * 转义属性
     *
     * @param string $text 文本
     * @return string
     */
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    /**
     * 转义URL
     *
     * @param string $url URL
     * @param array $protocols 协议
     * @param string $_context 上下文
     * @return string
     */
    function esc_url($url, $protocols = null, $_context = 'display') {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('sanitize_text_field')) {
    /**
     * 清理文本字段
     *
     * @param string $str 字符串
     * @return string
     */
    function sanitize_text_field($str) {
        return trim(strip_tags($str));
    }
}

if (!function_exists('wp_enqueue_script')) {
    /**
     * 加载脚本
     *
     * @param string $handle 句柄
     * @param string $src 源
     * @param array $deps 依赖
     * @param string|bool|null $ver 版本
     * @param bool $in_footer 是否在页脚
     * @return void
     */
    function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {
        // 存根函数
    }
}

if (!function_exists('wp_enqueue_style')) {
    /**
     * 加载样式
     *
     * @param string $handle 句柄
     * @param string $src 源
     * @param array $deps 依赖
     * @param string|bool|null $ver 版本
     * @param string $media 媒体
     * @return void
     */
    function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {
        // 存根函数
    }
}

if (!function_exists('plugins_url')) {
    /**
     * 获取插件URL
     *
     * @param string $path 路径
     * @param string $plugin 插件
     * @return string
     */
    function plugins_url($path = '', $plugin = '') {
        return 'http://example.com/wp-content/plugins' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('admin_url')) {
    /**
     * 获取管理URL
     *
     * @param string $path 路径
     * @param string $scheme 协议
     * @return string
     */
    function admin_url($path = '', $scheme = 'admin') {
        return 'http://example.com/wp-admin/' . ltrim($path, '/');
    }
}

if (!function_exists('get_admin_url')) {
    /**
     * 获取管理URL
     *
     * @param int $blog_id 博客ID
     * @param string $path 路径
     * @param string $scheme 协议
     * @return string
     */
    function get_admin_url($blog_id = null, $path = '', $scheme = 'admin') {
        return 'http://example.com/wp-admin/' . ltrim($path, '/');
    }
}

if (!function_exists('wp_upload_dir')) {
    /**
     * 获取上传目录
     *
     * @param string $time 时间
     * @param bool $create_dir 是否创建目录
     * @param bool $skip_cache 是否跳过缓存
     * @return array
     */
    function wp_upload_dir($time = null, $create_dir = true, $skip_cache = false) {
        return array(
            'path' => 'wp-content/uploads',
            'url' => 'http://example.com/wp-content/uploads',
            'subdir' => '',
            'basedir' => 'wp-content/uploads',
            'baseurl' => 'http://example.com/wp-content/uploads',
            'error' => false,
        );
    }
}

if (!function_exists('wp_get_attachment_url')) {
    /**
     * 获取附件URL
     *
     * @param int $attachment_id 附件ID
     * @return string|false
     */
    function wp_get_attachment_url($attachment_id) {
        return 'http://example.com/wp-content/uploads/attachment.jpg';
    }
}

if (!function_exists('wp_get_attachment_image_src')) {
    /**
     * 获取附件图片源
     *
     * @param int $attachment_id 附件ID
     * @param string|array $size 尺寸
     * @param bool $icon 是否使用图标
     * @return array|false
     */
    function wp_get_attachment_image_src($attachment_id, $size = 'thumbnail', $icon = false) {
        return array(
            'http://example.com/wp-content/uploads/attachment.jpg',
            300,
            200,
            false
        );
    }
}

if (!function_exists('get_post_meta')) {
    /**
     * 获取文章元数据
     *
     * @param int $post_id 文章ID
     * @param string $key 键
     * @param bool $single 是否单个
     * @return mixed
     */
    function get_post_meta($post_id, $key = '', $single = false) {
        return $single ? '' : array();
    }
}

if (!function_exists('update_post_meta')) {
    /**
     * 更新文章元数据
     *
     * @param int $post_id 文章ID
     * @param string $meta_key 键
     * @param mixed $meta_value 值
     * @param mixed $prev_value 之前的值
     * @return int|bool
     */
    function update_post_meta($post_id, $meta_key, $meta_value, $prev_value = '') {
        return true;
    }
}

if (!function_exists('add_post_meta')) {
    /**
     * 添加文章元数据
     *
     * @param int $post_id 文章ID
     * @param string $meta_key 键
     * @param mixed $meta_value 值
     * @param bool $unique 是否唯一
     * @return int|bool
     */
    function add_post_meta($post_id, $meta_key, $meta_value, $unique = false) {
        return true;
    }
}

if (!function_exists('delete_post_meta')) {
    /**
     * 删除文章元数据
     *
     * @param int $post_id 文章ID
     * @param string $meta_key 键
     * @param mixed $meta_value 值
     * @return bool
     */
    function delete_post_meta($post_id, $meta_key, $meta_value = '') {
        return true;
    }
}

if (!function_exists('get_option')) {
    /**
     * 获取选项
     *
     * @param string $option 选项名称
     * @param mixed $default 默认值
     * @return mixed
     */
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('update_option')) {
    /**
     * 更新选项
     *
     * @param string $option 选项名称
     * @param mixed $value 值
     * @param string|bool $autoload 是否自动加载
     * @return bool
     */
    function update_option($option, $value, $autoload = null) {
        return true;
    }
}

if (!function_exists('add_option')) {
    /**
     * 添加选项
     *
     * @param string $option 选项名称
     * @param mixed $value 值
     * @param string $deprecated 已弃用
     * @param string|bool $autoload 是否自动加载
     * @return bool
     */
    function add_option($option, $value, $deprecated = '', $autoload = 'yes') {
        return true;
    }
}

if (!function_exists('delete_option')) {
    /**
     * 删除选项
     *
     * @param string $option 选项名称
     * @return bool
     */
    function delete_option($option) {
        return true;
    }
}

// WPCA_Settings 类存根（如果未定义）
if (!class_exists('WPCA_Settings')) {
    /**
     * WPCA_Settings 类存根
     */
    class WPCA_Settings {
        /**
         * 获取选项
         *
         * @return array
         */
        public static function get_options() {
            return array();
        }
        
        /**
         * 获取默认设置
         *
         * @return array
         */
        public static function get_default_settings() {
            return array();
        }
    }
}

// WP_Admin_Bar 类存根（如果未定义）
if (!class_exists('WP_Admin_Bar')) {
    /**
     * WP_Admin_Bar 类存根
     */
    class WP_Admin_Bar {
        /**
         * 移除菜单
         *
         * @param string $id ID
         * @return void
         */
        public function remove_menu($id) {
            // 存根函数
        }
    }
}

// 全局变量存根
if (!isset($GLOBALS['wp_admin_bar'])) {
    $GLOBALS['wp_admin_bar'] = new WP_Admin_Bar();
}

// 国际化函数存根
if (!function_exists('__')) {
    /**
     * 翻译函数
     *
     * @param string $text 文本
     * @param string $domain 文本域
     * @return string
     */
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('_e')) {
    /**
     * 翻译并输出函数
     *
     * @param string $text 文本
     * @param string $domain 文本域
     * @return void
     */
    function _e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('_x')) {
    /**
     * 带上下文的翻译函数
     *
     * @param string $text 文本
     * @param string $context 上下文
     * @param string $domain 文本域
     * @return string
     */
    function _x($text, $context, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('esc_html__')) {
    /**
     * 翻译并转义HTML
     *
     * @param string $text 文本
     * @param string $domain 文本域
     * @return string
     */
    function esc_html__($text, $domain = 'default') {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html_e')) {
    /**
     * 翻译并转义HTML后输出
     *
     * @param string $text 文本
     * @param string $domain 文本域
     * @return void
     */
    function esc_html_e($text, $domain = 'default') {
        echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html_x')) {
    /**
     * 带上下文的翻译并转义HTML
     *
     * @param string $text 文本
     * @param string $context 上下文
     * @param string $domain 文本域
     * @return string
     */
    function esc_html_x($text, $context, $domain = 'default') {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

// 表单辅助函数存根
if (!function_exists('checked')) {
    /**
     * 输出checked属性
     *
     * @param mixed $checked 检查值
     * @param mixed $current 当前值
     * @param bool $echo 是否输出
     * @return string
     */
    function checked($checked, $current = true, $echo = true) {
        $result = (checked) === $current ? ' checked="checked"' : '';
        if ($echo) {
            echo $result;
        }
        return $result;
    }
}

if (!function_exists('selected')) {
    /**
     * 输出selected属性
     *
     * @param mixed $selected 选择值
     * @param mixed $current 当前值
     * @param bool $echo 是否输出
     * @return string
     */
    function selected($selected, $current = true, $echo = true) {
        $result = (selected) === $current ? ' selected="selected"' : '';
        if ($echo) {
            echo $result;
        }
        return $result;
    }
}

if (!function_exists('disabled')) {
    /**
     * 输出disabled属性
     *
     * @param mixed $disabled 禁用值
     * @param mixed $current 当前值
     * @param bool $echo 是否输出
     * @return string
     */
    function disabled($disabled, $current = true, $echo = true) {
        $result = (disabled) === $current ? ' disabled="disabled"' : '';
        if ($echo) {
            echo $result;
        }
        return $result;
    }
}

// 常量定义存根
if (!defined('WPCA_URL')) {
    define('WPCA_URL', 'http://example.com/wp-content/plugins/wp-clean-admin/');
}

if (!defined('WPCA_PATH')) {
    define('WPCA_PATH', dirname(__FILE__) . '/');
}

if (!defined('WPCA_VERSION')) {
    define('WPCA_VERSION', '1.7.13');
}
?>