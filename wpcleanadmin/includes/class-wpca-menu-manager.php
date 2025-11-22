<?php
/**
 * WPCA Menu Manager
 * 
 * Manages the display, hiding, and permission control of WordPress admin menus
 * 
 * @package WPCleanAdmin
 * @version 1.7.12
 */

// Ensure file is loaded within WordPress environment
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
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
        if ( function_exists( 'wp_json_encode' ) ) {
            wp_die( wp_json_encode( array( 'success' => true, 'data' => $data ) ) );
        } else {
            wp_die( json_encode( array( 'success' => true, 'data' => $data ) ) );
        }
    }
}

if ( ! function_exists( 'wp_send_json_error' ) ) {
    function wp_send_json_error( $data = null, $status_code = null ) {
        if ( function_exists( 'wp_json_encode' ) ) {
            wp_die( wp_json_encode( array( 'success' => false, 'data' => $data ) ) );
        } else {
            wp_die( json_encode( array( 'success' => false, 'data' => $data ) ) );
        }
    }
}

if ( ! function_exists( 'wp_die' ) ) {
    function wp_die( $message = 'WordPress no longer responding', $title = '', $args = array() ) {
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
 * WPCA_Menu_Manager class
 * 
 * Core implementation for menu item show/hide functionality
 */
class WPCA_Menu_Manager {
    
    /**
     * Protected menu items that cannot be hidden
     */
    const PROTECTED_MENUS = array(
        'index.php',      // Dashboard
        'users.php',      // Users
        'profile.php',    // Profile
        'wp-clean-admin'  // WPCleanAdmin 鎻掍欢鑷韩鐨勮彍鍗曢」
    );
    
    /**
     * 鍗曚緥瀹炰緥
     * 
     * @var WPCA_Menu_Manager
     */
    private static $instance;
    
    /**
     * 鎻掍欢閫夐」缂撳瓨
     * 
     * @var array
     */
    private $options_cache = null;
    
    /**
     * 闅愯棌鐨勮彍鍗曢」缂撳瓨
     * 
     * @var array
     */
    private $hidden_items_cache = null;
    
    /**
     * 鑾峰彇鍗曚緥瀹炰緥
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
     * 鏋勯€犲嚱鏁?
     * 
     * 注册必要的钩子和初始化菜单管理功能
     */
    private function __construct() {
        // 注册管理菜单相关的钩子
        $this->register_hooks();
    }
    
    /**
     * 娉ㄥ唽閽╁瓙
     * 
     * 娉ㄥ唽鎵€鏈変笌鑿滃崟绠＄悊鐩稿叧鐨刉ordPress閽╁瓙
     */
    private function register_hooks() {
        // 纭繚 WordPress 鍑芥暟鍙敤
        if ( function_exists( 'add_action' ) ) {
            // 初始化菜单管理功能
            add_action('admin_init', array($this, 'initialize_menu_management'));
            
            // 娣诲姞鑿滃崟杩囨护閽╁瓙
            add_action('admin_menu', array($this, 'filter_menu_items'), 999);
            
            // 添加管理栏过滤钩子
            add_action('admin_bar_menu', array($this, 'filter_admin_bar_items'), 999);
            
            // 杈撳嚭鑿滃崟闅愯棌鐨凜SS
            add_action('admin_head', array($this, 'output_menu_css'));
            
            // 娉ㄥ唽AJAX閽╁瓙
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
            // Initialize menu management functionality
                $this->ensure_menu_options_exist();
        }
    }
    
    /**
     * 纭繚鑿滃崟閫夐」鏁扮粍瀛樺湪
     * 
     * 妫€鏌ュ苟鍒濆鍖杕enu_toggles閫夐」鏁扮粍
     */
    private function ensure_menu_options_exist() {
        $options = $this->get_plugin_options();
        
        // 纭繚menu_toggles鏁扮粍瀛樺湪
        if (!isset($options['menu_toggles']) || !is_array($options['menu_toggles'])) {
            $options['menu_toggles'] = array();
            // 纭繚 WordPress 鍑芥暟鍙敤
            if ( function_exists( 'update_option' ) ) {
                update_option('wpca_settings', $options);
            }
        }
    }
    
    /**
     * 鑾峰彇鎻掍欢閫夐」
     * 
     * @return array 鎻掍欢閫夐」鏁扮粍
     */
    private function get_plugin_options() {
        if ($this->options_cache === null) {
            // 纭繚 WordPress 鍑芥暟鍙敤
            $this->options_cache = function_exists( 'get_option' ) ? get_option('wpca_settings', array()) : array();
        }
        return $this->options_cache;
    }
    
    /**
     * 鑾峰彇鎵€鏈夎彍鍗曢」
     * 
     * @param bool $force_refresh 鏄惁寮哄埗鍒锋柊缂撳瓨
     * @return array 鎵€鏈夎彍鍗曢」鏁扮粍
     */
    public function get_all_menu_items($force_refresh = false) {
        global $menu, $submenu;
        
        $all_items = array();
        
        // 鑾峰彇椤剁骇鑿滃崟
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
     * 鑾峰彇闅愯棌鐨勮彍鍗曢」
     * 
     * @return array 闅愯棌鐨勮彍鍗曢」鏁扮粍
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
     * 璁＄畻闅愯棌鐨勮彍鍗曢」
     * 
     * @param array $options 鎻掍欢閫夐」
     * @param array $all_items 鎵€鏈夎彍鍗曢」
     * @return array 闅愯棌鐨勮彍鍗曢」鏁扮粍
     */
    private function calculate_hidden_items($options, $all_items) {
        $hidden_items = array();
        
        // 妫€鏌ユ槸鍚︽湁menu_toggles鏁扮粍
        if (!isset($options['menu_toggles']) || !is_array($options['menu_toggles'])) {
            return $hidden_items;
        }
        
        foreach ($options['menu_toggles'] as $slug => $state) {
            // 楠岃瘉slug
            if (!is_string($slug) || empty($slug)) {
                continue;
            }
            
            // 妫€鏌ヨ彍鍗曟槸鍚﹂渶瑕侀殣钘忓苟涓旀槸鏈夋晥鐨勮彍鍗曢」
            if ($state === 0 && isset($all_items[$slug])) {
                // 妫€鏌ユ槸鍚︽槸鍙椾繚鎶ょ殑鑿滃崟
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
     * 杩囨护鑿滃崟椤?
     * 
     * 从管理菜单中移除隐藏的菜单项
            if (strpos($slug, '|') !== false) {
                list($parent, $child) = explode('|', $slug);
                if (isset($submenu[$parent])) {
                    foreach ($submenu[$parent] as $key => $item) {
                        if (isset($item[2]) && $item[2] === $child) {
                            unset($submenu[$parent][$key]);
                        }
                    }
                    
                    // 濡傛灉瀛愯彍鍗曟暟缁勪负绌猴紝浠庢暟缁勪腑绉婚櫎
                    if (empty($submenu[$parent])) {
                        unset($submenu[$parent]);
                    }
                }
            } 
            // 澶勭悊椤剁骇鑿滃崟
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
        // 妫€鏌?$wp_admin_bar 鏄惁鏄湁鏁堢殑瀵硅薄
        if (!is_object($wp_admin_bar) || !method_exists($wp_admin_bar, 'get_nodes')) {
            return $wp_admin_bar;
        }
        
        $hidden_items = $this->get_hidden_menu_items();
        
        if (empty($hidden_items)) {
            return $wp_admin_bar;
        }
        
        // 鑾峰彇鎵€鏈夌鐞嗘爮鑺傜偣
        $nodes = $wp_admin_bar->get_nodes();
        
        if (empty($nodes)) {
            return $wp_admin_bar;
        }
        
        foreach ($hidden_items as $slug) {
            // 鐢熸垚瀵瑰簲鐨勭鐞嗘爮ID
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
     * 鐢熸垚绠＄悊鏍廔D
     * 
     * 鏍规嵁鑿滃崟椤箂lug鐢熸垚鍙兘鐨勭鐞嗘爮ID
     * 
     * @param string $slug 鑿滃崟椤箂lug
     * @return array 鍙兘鐨勭鐞嗘爮ID鏁扮粍
     */
    private function generate_admin_bar_ids($slug) {
        $ids = array();
        
        // 处理子菜单
        if (strpos($slug, '|') !== false) {
            list($parent, $child) = explode('|', $slug);
            // 鐢熸垚鍙兘鐨勫瓙鑿滃崟绠＄悊鏍廔D
            $ids[] = 'edit-' . $parent . '-' . $child;
            $ids[] = $parent . '-' . $child;
        } 
        // 澶勭悊椤剁骇鑿滃崟
        else {
            // 鐢熸垚鍙兘鐨勯《绾ц彍鍗曠鐞嗘爮ID
            $base_id = str_replace('.php', '', $slug);
            $ids[] = 'toplevel_page_' . $base_id;
            $ids[] = 'menu-' . $base_id;
            $ids[] = $base_id;
        }
        
        return $ids;
    }
    
    /**
     * 妫€鏌ヨ彍鍗曟槸鍚﹀彈淇濇姢
     * 
     * @param string $slug 菜单项slug
     * @return bool 是否受保护
     */
    public function is_menu_protected($slug) {
        // 鎻愬彇涓昏彍鍗晄lug
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
            if (!in_array($state, array(0, 1))) {
                throw new Exception((function_exists('__') ? __('Invalid menu state', 'wp-clean-admin') : 'Invalid menu state'), 400);
            }
            
            // 妫€鏌ヨ彍鍗曟槸鍚﹀彈淇濇姢
            if ($state === 0 && $this->is_menu_protected($slug)) {
                throw new Exception((function_exists('__') ? __('This menu item cannot be hidden', 'wp-clean-admin') : 'This menu item cannot be hidden'), 403);
            }
            
            // 鑾峰彇骞舵洿鏂伴€夐」
            $options = $this->get_plugin_options();
            
            if (!isset($options['menu_toggles']) || !is_array($options['menu_toggles'])) {
                $options['menu_toggles'] = array();
            }
            
            $options['menu_toggles'][$slug] = $state;
            
            // 淇濆瓨閫夐」
            $updated = function_exists( 'update_option' ) ? update_option('wpca_settings', $options) : false;
            
            if (!$updated) {
                throw new Exception((function_exists('__') ? __('Failed to save menu settings', 'wp-clean-admin') : 'Failed to save menu settings'), 500);
            }
            
            // 娓呴櫎缂撳瓨
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
     * 娓呴櫎缂撳瓨
     * 
     * 清除内部缓存以确保数据更新
     */
    public function clear_cache() {
        $this->options_cache = null;
        $this->hidden_items_cache = null;
    }
    
    /**
     * 鐢熸垚鑿滃崟闅愯棌CSS
     * 
     * @param array $hidden_items 闅愯棌鐨勮彍鍗曢」
     * @return string CSS鏍峰紡
     */
    private function generate_menu_hide_css($hidden_items) {
        if (empty($hidden_items)) {
            return '';
        }
        
        $css = '';
        
        foreach ($hidden_items as $slug) {
            $selectors = $this->generate_css_selectors($slug);
            
            if (!empty($selectors)) {
                $css .= implode(',
', $selectors) . ' {
';
                $css .= '  display: none !important;
';
                $css .= '  width: 0 !important;
';
                $css .= '  height: 0 !important;
';
                $css .= '  overflow: hidden !important;
';
                $css .= '}
';
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
        
        // 澶勭悊瀛愯彍鍗?
        if (strpos($slug, '|') !== false) {
            list($parent, $child) = explode('|', $slug);
            $parent_escaped = $esc_attr_func($parent);
            $child_escaped = $esc_attr_func($child);
            
            $selectors[] = '#adminmenu li.menu-top.toplevel_page_' . $parent_escaped . ' .wp-submenu li a[href$="' . $child_escaped . '"]';
            $selectors[] = '#adminmenu li.menu-top.menu-icon-' . $parent_escaped . ' .wp-submenu li a[href$="' . $child_escaped . '"]';
            $selectors[] = '#adminmenu li.menu-top#menu-' . $esc_attr_func(str_replace('.php', '', $parent)) . ' .wp-submenu li a[href$="' . $child_escaped . '"]';
        } 
        // 澶勭悊椤剁骇鑿滃崟
        else {
            $slug_escaped = $esc_attr_func($slug);
            $selectors[] = '#adminmenu li.menu-top.toplevel_page_' . $slug_escaped;
            $selectors[] = '#adminmenu li.menu-top.menu-icon-' . $slug_escaped;
            $selectors[] = '#adminmenu li.menu-top#menu-' . $esc_attr_func(str_replace('.php', '', $slug));
        }
        
        return $selectors;
    }
    
    /**
     * 杈撳嚭鑿滃崟CSS
     * 
     * 鍦ㄧ鐞嗙晫闈㈠ご閮ㄨ緭鍑鸿彍鍗曢殣钘忕殑CSS鏍峰紡
     */
    public function output_menu_css() {
        $hidden_items = $this->get_hidden_menu_items();
        $css = $this->generate_menu_hide_css($hidden_items);
        
        if (!empty($css)) {
            echo '<style type="text/css" id="wpca-menu-manager-css">
';
            echo "/* WP Clean Admin - Menu Manager CSS */
";
            // 纭繚 WordPress 鍑芥暟鍙敤鎴栦娇鐢ㄥ閫夊嚱鏁?
            echo function_exists('esc_html') ? esc_html($css) : htmlspecialchars($css, ENT_QUOTES, 'UTF-8');
            echo "
</style>
";
        }
    }
    
    /**
     * 澶勭悊AJAX鍒囨崲鑿滃崟璇锋眰
     * 
     * 澶勭悊鍓嶇鍙戦€佺殑鑿滃崟鏄剧ず/闅愯棌鍒囨崲璇锋眰
     */
    public function handle_ajax_toggle_menu() {
        try {
            // 楠岃瘉AJAX璇锋眰
            $this->validate_ajax_request();
            
            // 鑾峰彇鍙傛暟
              // isset是PHP语言结构，不需要function_exists检查
              $slug = function_exists('sanitize_text_field') ? sanitize_text_field($_POST['slug']) : (isset($_POST['slug']) ? filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_STRING) : '');
            $state = isset($_POST['state']) ? intval($_POST['state']) : 0;
            
            // 鎵ц鍒囨崲鎿嶄綔
            $result = $this->toggle_menu_item($slug, $state);
            
            if ($result['success']) {
                if (function_exists('wp_send_json_success')) {
                    wp_send_json_success($result);
                } else if (function_exists('wp_die')) {
                    wp_die(wp_json_encode(array('success' => true, 'data' => $result)));
                }
            } else {
                $code = isset($result['code']) ? $result['code'] : 500;
                if (function_exists('wp_send_json_error')) {
                    wp_send_json_error($result, $code);
                } else if (function_exists('wp_die')) {
                    wp_die(wp_json_encode(array('success' => false, 'data' => $result)), $code);
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
                wp_die(wp_json_encode(array('success' => false, 'data' => array('message' => $e->getMessage(), 'code' => $code))), $code);
            }
        }
    }
    
    /**
     * 澶勭悊AJAX鑾峰彇闅愯棌鑿滃崟璇锋眰
     * 
     * 杩斿洖褰撳墠闅愯棌鐨勮彍鍗曢」鍒楄〃
     */
    public function handle_ajax_get_hidden_menus() {
        try {
            // 楠岃瘉AJAX璇锋眰
            $this->validate_ajax_request();
            
            // 鑾峰彇鎵€鏈夎彍鍗曢」鍜岄殣钘忕殑鑿滃崟椤?
            $all_items = $this->get_all_menu_items(true);
            $hidden_items = $this->get_hidden_menu_items();
            
            // 鏋勫缓鑿滃崟鐘舵€佹暟缁?
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
                wp_die(wp_json_encode(array('success' => true, 'data' => $response)));
            }
            
        } catch (Exception $e) {
            $code = method_exists($e, 'getCode') ? $e->getCode() : 500;
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => $e->getMessage(),
                    'code' => $code
                ), $code);
            } else if (function_exists('wp_die')) {
                wp_die(wp_json_encode(array('success' => false, 'data' => array('message' => $e->getMessage(), 'code' => $code))), $code);
            }
        }
    }
    
    /**
     * 澶勭悊AJAX楠岃瘉鑿滃崟椤硅姹?
     * 
     * 楠岃瘉鑿滃崟椤规槸鍚﹀瓨鍦ㄤ笖鍙闅愯棌
     */
    public function handle_ajax_validate_menu_item() {
        try {
            // 楠岃瘉AJAX璇锋眰
            $this->validate_ajax_request();
            
            // 鑾峰彇鍙傛暟
            $slug = function_exists('sanitize_text_field') ? sanitize_text_field($_POST['slug']) : (isset($_POST['slug']) ? filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_STRING) : '');
            
            // 鑾峰彇鎵€鏈夎彍鍗曢」
            $all_items = $this->get_all_menu_items(true);
            
            // 楠岃瘉鑿滃崟椤?
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
                wp_die(wp_json_encode(array('success' => true, 'data' => $response)));
            }
            
        } catch (Exception $e) {
            $code = method_exists($e, 'getCode') ? $e->getCode() : 500;
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => $e->getMessage(),
                    'code' => $code
                ), $code);
            } else if (function_exists('wp_die')) {
                wp_die(wp_json_encode(array('success' => false, 'data' => array('message' => $e->getMessage(), 'code' => $code))), $code);
            }
        }
    }
    
    /**
     * 楠岃瘉AJAX璇锋眰
     * 
     * 楠岃瘉AJAX璇锋眰鐨勫悎娉曟€у拰鏉冮檺
     */
    private function validate_ajax_request() {
        // 妫€鏌ユ槸鍚︽槸AJAX璇锋眰
        // defined鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?
        $is_ajax = function_exists('wp_doing_ajax') ? wp_doing_ajax() : (defined('DOING_AJAX') && DOING_AJAX);
        if (!$is_ajax) {
            throw new Exception((function_exists('__') ? __('Invalid request type', 'wp-clean-admin') : 'Invalid request type'), 400);
        }
        
        // 楠岃瘉nonce
        $nonce_valid = isset($_POST['nonce']) && function_exists('wp_verify_nonce') && wp_verify_nonce($_POST['nonce'], 'wpca_admin_nonce');
        if (!$nonce_valid) {
            throw new Exception((function_exists('__') ? __('Security verification failed', 'wp-clean-admin') : 'Security verification failed'), 403);
        }
        
        // 妫€鏌ョ敤鎴锋潈闄?
        $has_permission = function_exists('current_user_can') && current_user_can('manage_options');
        if (!$has_permission) {
            throw new Exception((function_exists('__') ? __('Insufficient permissions', 'wp-clean-admin') : 'Insufficient permissions'), 403);
        }
    }
    
    /**
     * 鑾峰彇鑿滃崟缁熻淇℃伅
     * 
     * @return array 鑿滃崟缁熻淇℃伅
     */
    public function get_menu_statistics() {
        $all_items = $this->get_all_menu_items();
        $hidden_items = $this->get_hidden_menu_items();
        
        // 缁熻椤剁骇鑿滃崟鍜屽瓙鑿滃崟
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

// 鍒濆鍖栬彍鍗曠鐞嗗櫒
define('WPCA_MENU_MANAGER_LOADED', true);

?>