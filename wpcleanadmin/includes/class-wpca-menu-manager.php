<?php
/**
 * WPCA Menu Manager
 * 
 * 管理WordPress管理菜单的显示、隐藏和权限控制
 * 
 * @package WPCleanAdmin
 * @version 1.0.0
 */

// 确保在 WordPress 环境中加载
if ( ! defined( 'ABSPATH' ) ) {
    exit; // 直接访问禁止
}

// 定义必要的WordPress函数模拟，确保在非WordPress环境中也能加载类定义
if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $function_to_add, $priority = 10, $accepted_args = 1 ) {}
}

if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can( $capability ) { return false; }
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) { return $default; }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option( $option, $value, $autoload = null ) { return false; }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) { return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) { return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) { return filter_var( $str, FILTER_SANITIZE_STRING ); }
}

if ( ! function_exists( 'wp_send_json_success' ) ) {
    function wp_send_json_success( $data = null, $status_code = null ) {
        wp_die( json_encode( array( 'success' => true, 'data' => $data ) ) );
    }
}

if ( ! function_exists( 'wp_send_json_error' ) ) {
    function wp_send_json_error( $data = null, $status_code = null ) {
        wp_die( json_encode( array( 'success' => false, 'data' => $data ) ) );
    }
}

if ( ! function_exists( 'wp_die' ) ) {
    function wp_die( $message = 'WordPress不再响应', $title = '', $args = array() ) {
        die( $message );
    }
}

if ( ! function_exists( 'wp_doing_ajax' ) ) {
    function wp_doing_ajax() { return defined( 'DOING_AJAX' ) && DOING_AJAX; }
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
    function wp_verify_nonce( $nonce, $action = -1 ) { return false; }
}

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) { return $text; }
}

/**
 * WPCA_Menu_Manager 类
 * 
 * 负责菜单项显示/隐藏功能的核心实现
 */
class WPCA_Menu_Manager {
    
    /**
     * 受保护的菜单项，不能被隐藏
     */
    const PROTECTED_MENUS = array(
        'index.php',      // Dashboard
        'users.php',      // Users
        'profile.php',    // Profile
        'wp-clean-admin'  // WPCleanAdmin 插件自身的菜单项
    );
    
    /**
     * 单例实例
     * 
     * @var WPCA_Menu_Manager
     */
    private static $instance;
    
    /**
     * 插件选项缓存
     * 
     * @var array
     */
    private $options_cache = null;
    
    /**
     * 隐藏的菜单项缓存
     * 
     * @var array
     */
    private $hidden_items_cache = null;
    
    /**
     * 获取单例实例
     * 
     * @return WPCA_Menu_Manager
     */
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     * 
     * 注册必要的钩子和初始化菜单管理功能
     */
    private function __construct() {
        // 注册管理菜单相关的钩子
        $this->register_hooks();
    }
    
    /**
     * 注册钩子
     * 
     * 注册所有与菜单管理相关的WordPress钩子
     */
    private function register_hooks() {
        // 确保 WordPress 函数可用
        if ( function_exists( 'add_action' ) ) {
            // 初始化菜单管理功能
            add_action('admin_init', array($this, 'initialize_menu_management'));
            
            // 添加菜单过滤钩子
            add_action('admin_menu', array($this, 'filter_menu_items'), 999);
            
            // 添加管理栏过滤钩子
            add_action('admin_bar_menu', array($this, 'filter_admin_bar_items'), 999);
            
            // 输出菜单隐藏的CSS
            add_action('admin_head', array($this, 'output_menu_css'));
            
            // 注册AJAX钩子
            add_action('wp_ajax_wpca_toggle_menu', array($this, 'handle_ajax_toggle_menu'));
            add_action('wp_ajax_wpca_get_hidden_menus', array($this, 'handle_ajax_get_hidden_menus'));
            add_action('wp_ajax_wpca_validate_menu_item', array($this, 'handle_ajax_validate_menu_item'));
        }
    }
    
    /**
     * 初始化菜单管理功能
     * 
     * 检查用户权限并初始化菜单管理功能
     */
    public function initialize_menu_management() {
        // 确保 WordPress 函数可用并检查用户权限
        if ( function_exists( 'current_user_can' ) && current_user_can('manage_options') ) {
            // 确保菜单选项数组存在
            $this->ensure_menu_options_exist();
        }
    }
    
    /**
     * 确保菜单选项数组存在
     * 
     * 检查并初始化menu_toggles选项数组
     */
    private function ensure_menu_options_exist() {
        $options = $this->get_plugin_options();
        
        // 确保menu_toggles数组存在
        if (!isset($options['menu_toggles']) || !is_array($options['menu_toggles'])) {
            $options['menu_toggles'] = array();
            // 确保 WordPress 函数可用
            if ( function_exists( 'update_option' ) ) {
                update_option('wpca_settings', $options);
            }
        }
    }
    
    /**
     * 获取插件选项
     * 
     * @return array 插件选项数组
     */
    private function get_plugin_options() {
        if ($this->options_cache === null) {
            // 确保 WordPress 函数可用
            $this->options_cache = function_exists( 'get_option' ) ? get_option('wpca_settings', array()) : array();
        }
        return $this->options_cache;
    }
    
    /**
     * 获取所有菜单项
     * 
     * @param bool $force_refresh 是否强制刷新缓存
     * @return array 所有菜单项数组
     */
    public function get_all_menu_items($force_refresh = false) {
        global $menu, $submenu;
        
        $all_items = array();
        
        // 获取顶级菜单
        if (is_array($menu)) {
            foreach ($menu as $item) {
                if (!empty($item) && isset($item[2])) {
                    $slug = $item[2];
                    $all_items[$slug] = array(
                        'type' => 'top',
                        'title' => isset($item[0]) ? $item[0] : '',
                        'slug' => $slug
                    );
                    
                    // 获取子菜单
                    if (isset($submenu[$slug]) && is_array($submenu[$slug])) {
                        foreach ($submenu[$slug] as $sub_item) {
                            if (!empty($sub_item) && isset($sub_item[2])) {
                                $sub_slug = $sub_item[2];
                                $combined_slug = $slug . '|' . $sub_slug;
                                $all_items[$combined_slug] = array(
                                    'type' => 'sub',
                                    'parent' => $slug,
                                    'title' => isset($sub_item[0]) ? $sub_item[0] : '',
                                    'slug' => $sub_slug,
                                    'combined_slug' => $combined_slug
                                );
                            }
                        }
                    }
                }
            }
        }
        
        return $all_items;
    }
    
    /**
     * 获取隐藏的菜单项
     * 
     * @return array 隐藏的菜单项数组
     */
    public function get_hidden_menu_items() {
        if ($this->hidden_items_cache === null) {
            $options = $this->get_plugin_options();
            $all_items = $this->get_all_menu_items();
            $this->hidden_items_cache = $this->calculate_hidden_items($options, $all_items);
        }
        return $this->hidden_items_cache;
    }
    
    /**
     * 计算隐藏的菜单项
     * 
     * @param array $options 插件选项
     * @param array $all_items 所有菜单项
     * @return array 隐藏的菜单项数组
     */
    private function calculate_hidden_items($options, $all_items) {
        $hidden_items = array();
        
        // 检查是否有menu_toggles数组
        if (!isset($options['menu_toggles']) || !is_array($options['menu_toggles'])) {
            return $hidden_items;
        }
        
        foreach ($options['menu_toggles'] as $slug => $state) {
            // 验证slug
            if (!is_string($slug) || empty($slug)) {
                continue;
            }
            
            // 检查菜单是否需要隐藏并且是有效的菜单项
            if ($state === 0 && isset($all_items[$slug])) {
                // 检查是否是受保护的菜单
                $slug_parts = explode('|', $slug);
                $main_slug = $slug_parts[0];
                
                if (!in_array($main_slug, self::PROTECTED_MENUS)) {
                    $hidden_items[] = $slug;
                }
            }
        }
        
        return $hidden_items;
    }
    
    /**
     * 过滤菜单项
     * 
     * 从管理菜单中移除隐藏的菜单项
     */
    public function filter_menu_items() {
        global $menu, $submenu;
        
        $hidden_items = $this->get_hidden_menu_items();
        
        if (empty($hidden_items)) {
            return;
        }
        
        foreach ($hidden_items as $slug) {
            // 处理子菜单
            if (strpos($slug, '|') !== false) {
                list($parent, $child) = explode('|', $slug);
                if (isset($submenu[$parent])) {
                    foreach ($submenu[$parent] as $key => $item) {
                        if (isset($item[2]) && $item[2] === $child) {
                            unset($submenu[$parent][$key]);
                        }
                    }
                    
                    // 如果子菜单数组为空，从数组中移除
                    if (empty($submenu[$parent])) {
                        unset($submenu[$parent]);
                    }
                }
            } 
            // 处理顶级菜单
            else {
                foreach ($menu as $key => $item) {
                    if (isset($item[2]) && $item[2] === $slug) {
                        unset($menu[$key]);
                    }
                }
            }
        }
    }
    
    /**
     * 过滤管理栏项目
     * 
     * 从管理栏中移除相关的隐藏菜单项
     * 
     * @param WP_Admin_Bar $wp_admin_bar 管理栏对象
     * @return WP_Admin_Bar 修改后的管理栏对象
     */
    public function filter_admin_bar_items($wp_admin_bar) {
        // 检查 $wp_admin_bar 是否是有效的对象
        if (!is_object($wp_admin_bar) || !method_exists($wp_admin_bar, 'get_nodes')) {
            return $wp_admin_bar;
        }
        
        $hidden_items = $this->get_hidden_menu_items();
        
        if (empty($hidden_items)) {
            return $wp_admin_bar;
        }
        
        // 获取所有管理栏节点
        $nodes = $wp_admin_bar->get_nodes();
        
        if (empty($nodes)) {
            return $wp_admin_bar;
        }
        
        foreach ($hidden_items as $slug) {
            // 生成对应的管理栏ID
            $admin_bar_ids = $this->generate_admin_bar_ids($slug);
            
            foreach ($admin_bar_ids as $id) {
                if ($wp_admin_bar->get_node($id)) {
                    $wp_admin_bar->remove_node($id);
                }
            }
        }
        
        return $wp_admin_bar;
    }
    
    /**
     * 生成管理栏ID
     * 
     * 根据菜单项slug生成可能的管理栏ID
     * 
     * @param string $slug 菜单项slug
     * @return array 可能的管理栏ID数组
     */
    private function generate_admin_bar_ids($slug) {
        $ids = array();
        
        // 处理子菜单
        if (strpos($slug, '|') !== false) {
            list($parent, $child) = explode('|', $slug);
            // 生成可能的子菜单管理栏ID
            $ids[] = 'edit-' . $parent . '-' . $child;
            $ids[] = $parent . '-' . $child;
        } 
        // 处理顶级菜单
        else {
            // 生成可能的顶级菜单管理栏ID
            $base_id = str_replace('.php', '', $slug);
            $ids[] = 'toplevel_page_' . $base_id;
            $ids[] = 'menu-' . $base_id;
            $ids[] = $base_id;
        }
        
        return $ids;
    }
    
    /**
     * 检查菜单是否受保护
     * 
     * @param string $slug 菜单项slug
     * @return bool 是否受保护
     */
    public function is_menu_protected($slug) {
        // 提取主菜单slug
        $slug_parts = explode('|', $slug);
        $main_slug = $slug_parts[0];
        
        return in_array($main_slug, self::PROTECTED_MENUS);
    }
    
    /**
     * 切换菜单项的可见性
     * 
     * @param string $slug 菜单项slug
     * @param int $state 状态 (0=隐藏, 1=显示)
     * @return array 操作结果
     */
    public function toggle_menu_item($slug, $state) {
        try {
            // 验证参数
            if (empty($slug) || !is_string($slug)) {
                throw new Exception((function_exists('__') ? __('Invalid menu slug', 'wp-clean-admin') : 'Invalid menu slug'), 400);
            }
            
            // 验证状态
            if (!in_array($state, array(0, 1))) {
                throw new Exception((function_exists('__') ? __('Invalid menu state', 'wp-clean-admin') : 'Invalid menu state'), 400);
            }
            
            // 检查菜单是否受保护
            if ($state === 0 && $this->is_menu_protected($slug)) {
                throw new Exception((function_exists('__') ? __('This menu item cannot be hidden', 'wp-clean-admin') : 'This menu item cannot be hidden'), 403);
            }
            
            // 获取并更新选项
            $options = $this->get_plugin_options();
            
            if (!isset($options['menu_toggles']) || !is_array($options['menu_toggles'])) {
                $options['menu_toggles'] = array();
            }
            
            $options['menu_toggles'][$slug] = $state;
            
            // 保存选项
            $updated = function_exists( 'update_option' ) ? update_option('wpca_settings', $options) : false;
            
            if (!$updated) {
                throw new Exception((function_exists('__') ? __('Failed to save menu settings', 'wp-clean-admin') : 'Failed to save menu settings'), 500);
            }
            
            // 清除缓存
            $this->clear_cache();
            
            return array(
                'success' => true,
                'message' => (function_exists('__') ? __('Menu settings updated successfully', 'wp-clean-admin') : 'Menu settings updated successfully'),
                'data' => array(
                    'slug' => $slug,
                    'state' => $state
                )
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage(),
                'code' => method_exists($e, 'getCode') ? $e->getCode() : 500
            );
        }
    }
    
    /**
     * 清除缓存
     * 
     * 清除内部缓存以确保数据更新
     */
    public function clear_cache() {
        $this->options_cache = null;
        $this->hidden_items_cache = null;
    }
    
    /**
     * 生成菜单隐藏CSS
     * 
     * @param array $hidden_items 隐藏的菜单项
     * @return string CSS样式
     */
    private function generate_menu_hide_css($hidden_items) {
        if (empty($hidden_items)) {
            return '';
        }
        
        $css = '';
        
        foreach ($hidden_items as $slug) {
            $selectors = $this->generate_css_selectors($slug);
            
            if (!empty($selectors)) {
                $css .= implode(',\n', $selectors) . ' {\n';
                $css .= '  display: none !important;\n';
                $css .= '  width: 0 !important;\n';
                $css .= '  height: 0 !important;\n';
                $css .= '  overflow: hidden !important;\n';
                $css .= '}\n';
            }
        }
        
        return $css;
    }
    
    /**
     * 生成CSS选择器
     * 
     * @param string $slug 菜单项slug
     * @return array CSS选择器数组
     */
    private function generate_css_selectors($slug) {
        $selectors = array();
        
        // 确保 WordPress 函数可用或使用备选函数
        $esc_attr_func = function_exists('esc_attr') ? 'esc_attr' : function($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); };
        
        // 处理子菜单
        if (strpos($slug, '|') !== false) {
            list($parent, $child) = explode('|', $slug);
            $parent_escaped = $esc_attr_func($parent);
            $child_escaped = $esc_attr_func($child);
            
            $selectors[] = '#adminmenu li.menu-top.toplevel_page_' . $parent_escaped . ' .wp-submenu li a[href$="' . $child_escaped . '"]';
            $selectors[] = '#adminmenu li.menu-top.menu-icon-' . $parent_escaped . ' .wp-submenu li a[href$="' . $child_escaped . '"]';
            $selectors[] = '#adminmenu li.menu-top#menu-' . $esc_attr_func(str_replace('.php', '', $parent)) . ' .wp-submenu li a[href$="' . $child_escaped . '"]';
        } 
        // 处理顶级菜单
        else {
            $slug_escaped = $esc_attr_func($slug);
            $selectors[] = '#adminmenu li.menu-top.toplevel_page_' . $slug_escaped;
            $selectors[] = '#adminmenu li.menu-top.menu-icon-' . $slug_escaped;
            $selectors[] = '#adminmenu li.menu-top#menu-' . $esc_attr_func(str_replace('.php', '', $slug));
        }
        
        return $selectors;
    }
    
    /**
     * 输出菜单CSS
     * 
     * 在管理界面头部输出菜单隐藏的CSS样式
     */
    public function output_menu_css() {
        $hidden_items = $this->get_hidden_menu_items();
        $css = $this->generate_menu_hide_css($hidden_items);
        
        if (!empty($css)) {
            echo '<style type="text/css" id="wpca-menu-manager-css">\n';
            echo "/* WP Clean Admin - Menu Manager CSS */\n";
            // 确保 WordPress 函数可用或使用备选函数
            echo function_exists('esc_html') ? esc_html($css) : htmlspecialchars($css, ENT_QUOTES, 'UTF-8');
            echo "\n</style>\n";
        }
    }
    
    /**
     * 处理AJAX切换菜单请求
     * 
     * 处理前端发送的菜单显示/隐藏切换请求
     */
    public function handle_ajax_toggle_menu() {
        try {
            // 验证AJAX请求
            $this->validate_ajax_request();
            
            // 获取参数
            $slug = function_exists('sanitize_text_field') ? sanitize_text_field($_POST['slug']) : (isset($_POST['slug']) ? filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_STRING) : '');
            $state = isset($_POST['state']) ? intval($_POST['state']) : 0;
            
            // 执行切换操作
            $result = $this->toggle_menu_item($slug, $state);
            
            if ($result['success']) {
                if (function_exists('wp_send_json_success')) {
                    wp_send_json_success($result);
                } else if (function_exists('wp_die')) {
                    wp_die(json_encode(array('success' => true, 'data' => $result)));
                }
            } else {
                $code = isset($result['code']) ? $result['code'] : 500;
                if (function_exists('wp_send_json_error')) {
                    wp_send_json_error($result, $code);
                } else if (function_exists('wp_die')) {
                    wp_die(json_encode(array('success' => false, 'data' => $result)), $code);
                }
            }
            
        } catch (Exception $e) {
            $code = method_exists($e, 'getCode') ? $e->getCode() : 500;
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => $e->getMessage(),
                    'code' => $code
                ), $code);
            } else if (function_exists('wp_die')) {
                wp_die(json_encode(array('success' => false, 'data' => array('message' => $e->getMessage(), 'code' => $code))), $code);
            }
        }
    }
    
    /**
     * 处理AJAX获取隐藏菜单请求
     * 
     * 返回当前隐藏的菜单项列表
     */
    public function handle_ajax_get_hidden_menus() {
        try {
            // 验证AJAX请求
            $this->validate_ajax_request();
            
            // 获取所有菜单项和隐藏的菜单项
            $all_items = $this->get_all_menu_items(true);
            $hidden_items = $this->get_hidden_menu_items();
            
            // 构建菜单状态数组
            $menu_states = array();
            foreach ($all_items as $slug => $item) {
                $menu_states[$slug] = array(
                    'title' => $item['title'],
                    'type' => $item['type'],
                    'hidden' => in_array($slug, $hidden_items),
                    'protected' => $this->is_menu_protected($slug)
                );
            }
            
            $response = array(
                'menu_states' => $menu_states,
                'hidden_count' => count($hidden_items)
            );
            
            if (function_exists('wp_send_json_success')) {
                wp_send_json_success($response);
            } else if (function_exists('wp_die')) {
                wp_die(json_encode(array('success' => true, 'data' => $response)));
            }
            
        } catch (Exception $e) {
            $code = method_exists($e, 'getCode') ? $e->getCode() : 500;
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => $e->getMessage(),
                    'code' => $code
                ), $code);
            } else if (function_exists('wp_die')) {
                wp_die(json_encode(array('success' => false, 'data' => array('message' => $e->getMessage(), 'code' => $code))), $code);
            }
        }
    }
    
    /**
     * 处理AJAX验证菜单项请求
     * 
     * 验证菜单项是否存在且可被隐藏
     */
    public function handle_ajax_validate_menu_item() {
        try {
            // 验证AJAX请求
            $this->validate_ajax_request();
            
            // 获取参数
            $slug = function_exists('sanitize_text_field') ? sanitize_text_field($_POST['slug']) : (isset($_POST['slug']) ? filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_STRING) : '');
            
            // 获取所有菜单项
            $all_items = $this->get_all_menu_items(true);
            
            // 验证菜单项
            $exists = isset($all_items[$slug]);
            $protected = $exists ? $this->is_menu_protected($slug) : false;
            
            $response = array(
                'slug' => $slug,
                'exists' => $exists,
                'protected' => $protected,
                'can_be_hidden' => $exists && !$protected,
                'menu_info' => $exists ? $all_items[$slug] : null
            );
            
            if (function_exists('wp_send_json_success')) {
                wp_send_json_success($response);
            } else if (function_exists('wp_die')) {
                wp_die(json_encode(array('success' => true, 'data' => $response)));
            }
            
        } catch (Exception $e) {
            $code = method_exists($e, 'getCode') ? $e->getCode() : 500;
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => $e->getMessage(),
                    'code' => $code
                ), $code);
            } else if (function_exists('wp_die')) {
                wp_die(json_encode(array('success' => false, 'data' => array('message' => $e->getMessage(), 'code' => $code))), $code);
            }
        }
    }
    
    /**
     * 验证AJAX请求
     * 
     * 验证AJAX请求的合法性和权限
     */
    private function validate_ajax_request() {
        // 检查是否是AJAX请求
        $is_ajax = function_exists('wp_doing_ajax') ? wp_doing_ajax() : (defined('DOING_AJAX') && DOING_AJAX);
        if (!$is_ajax) {
            throw new Exception((function_exists('__') ? __('Invalid request type', 'wp-clean-admin') : 'Invalid request type'), 400);
        }
        
        // 验证nonce
        $nonce_valid = isset($_POST['nonce']) && function_exists('wp_verify_nonce') && wp_verify_nonce($_POST['nonce'], 'wpca_admin_nonce');
        if (!$nonce_valid) {
            throw new Exception((function_exists('__') ? __('Security verification failed', 'wp-clean-admin') : 'Security verification failed'), 403);
        }
        
        // 检查用户权限
        $has_permission = function_exists('current_user_can') && current_user_can('manage_options');
        if (!$has_permission) {
            throw new Exception((function_exists('__') ? __('Insufficient permissions', 'wp-clean-admin') : 'Insufficient permissions'), 403);
        }
    }
    
    /**
     * 获取菜单统计信息
     * 
     * @return array 菜单统计信息
     */
    public function get_menu_statistics() {
        $all_items = $this->get_all_menu_items();
        $hidden_items = $this->get_hidden_menu_items();
        
        // 统计顶级菜单和子菜单
        $top_level_count = 0;
        $submenu_count = 0;
        $protected_count = 0;
        $hidden_top_level_count = 0;
        $hidden_submenu_count = 0;
        
        foreach ($all_items as $slug => $item) {
            if ($item['type'] === 'top') {
                $top_level_count++;
                if (in_array($slug, $hidden_items)) {
                    $hidden_top_level_count++;
                }
            } else {
                $submenu_count++;
                if (in_array($slug, $hidden_items)) {
                    $hidden_submenu_count++;
                }
            }
            
            if ($this->is_menu_protected($slug)) {
                $protected_count++;
            }
        }
        
        return array(
            'total' => count($all_items),
            'top_level' => $top_level_count,
            'submenu' => $submenu_count,
            'protected' => $protected_count,
            'hidden' => count($hidden_items),
            'hidden_top_level' => $hidden_top_level_count,
            'hidden_submenu' => $hidden_submenu_count,
            'visible' => count($all_items) - count($hidden_items)
        );
    }
}

// 初始化菜单管理器
define('WPCA_MENU_MANAGER_LOADED', true);

?>