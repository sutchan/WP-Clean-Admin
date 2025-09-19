<?php
/**
 * WP Clean Admin - 权限管理类
 *
 * 实现细粒度的用户权限管理，支持权限注册、检查和继承
 *
 * @package WP_Clean_Admin
 * @since 1.1.0
 */

// 如果直接访问此文件，则中止
if (!defined('ABSPATH')) {
    exit;
}

// 确保WordPress核心函数可用
if (!function_exists('add_action')) {
    return;
}

/**
 * WordPress函数声明，用于IDE代码提示
 * @codingStandardsIgnoreStart
 */
if (!function_exists('add_action') && !function_exists('current_user_can')) {
    function add_action() {}
    function add_filter() {}
    function current_user_can() {}
    function get_role() {}
    function check_ajax_referer() {}
    function wp_send_json_error() {}
    function wp_send_json_success() {}
    function sanitize_text_field() {}
    class WP_Roles {}
}
/** @codingStandardsIgnoreEnd */

/**
 * 权限管理类
 */
class WPCA_Permissions {

    /**
     * 插件权限列表
     *
     * @var array
     */
    private $capabilities = array(
        'wpca_view_settings'  => '查看设置',
        'wpca_manage_menus'   => '管理菜单',
        'wpca_manage_all'     => '完全控制'
    );

    /**
     * 权限继承关系
     *
     * @var array
     */
    private $capability_hierarchy = array(
        'wpca_manage_all'   => array('wpca_manage_menus', 'wpca_view_settings'),
        'wpca_manage_menus' => array('wpca_view_settings')
    );

    /**
     * 构造函数
     */
    public function __construct() {
        add_action('admin_init', array($this, 'register_capabilities'));
        add_action('wp_ajax_wpca_check_permission', array($this, 'ajax_check_permission'));
        add_filter('wpca_localize_script_data', array($this, 'add_capabilities_to_js'));
    }

    /**
     * 注册插件权限
     */
    public function register_capabilities() {
        $roles = array('administrator', 'editor', 'author');
        
        // 默认只给管理员完全权限
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if (!$role) continue;
            
            if ($role_name == 'administrator') {
                $role->add_cap('wpca_view_settings', true);
                $role->add_cap('wpca_manage_menus', true);
                $role->add_cap('wpca_manage_all', true);
            } else if ($role_name == 'editor') {
                $role->add_cap('wpca_view_settings', true);
                $role->add_cap('wpca_manage_menus', true);
                $role->remove_cap('wpca_manage_all');
            } else {
                $role->add_cap('wpca_view_settings', true);
                $role->remove_cap('wpca_manage_menus');
                $role->remove_cap('wpca_manage_all');
            }
        }
    }

    /**
     * 设置默认权限
     */
    public function set_default_permissions() {
        // 在插件激活时调用
        $this->register_capabilities();
    }

    /**
     * 清理插件权限
     */
    public function cleanup_capabilities() {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        
        foreach ($wp_roles->role_objects as $role) {
            foreach (array_keys($this->capabilities) as $cap) {
                $role->remove_cap($cap);
            }
        }
    }

    /**
     * 检查当前用户是否有指定权限
     *
     * @param string $capability 权限名称
     * @return bool 是否有权限
     */
    public static function current_user_can($capability) {
        // 管理员始终拥有所有权限
        if (current_user_can('administrator')) {
            return true;
        }
        
        // 检查直接权限
        if (current_user_can($capability)) {
            return true;
        }
        
        // 检查更高级权限
        $instance = new self();
        $hierarchy = $instance->capability_hierarchy;
        
        foreach ($hierarchy as $higher_cap => $lower_caps) {
            if (in_array($capability, $lower_caps) && current_user_can($higher_cap)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * AJAX权限检查
     */
    public function ajax_check_permission() {
        // 检查nonce
        check_ajax_referer('wpca_settings-options', 'nonce');
        
        // 检查参数
        if (!isset($_POST['capability'])) {
            wp_send_json_error('缺少权限参数');
        }
        
        $capability = sanitize_text_field($_POST['capability']);
        
        // 检查权限
        $has_permission = self::current_user_can($capability);
        
        wp_send_json_success(array(
            'has_permission' => $has_permission,
            'capability' => $capability
        ));
    }

    /**
     * 添加用户权限到JavaScript
     *
     * @param array $data 现有数据
     * @return array 添加权限后的数据
     */
    public function add_capabilities_to_js($data) {
        $user_capabilities = array();
        
        foreach (array_keys($this->capabilities) as $cap) {
            $user_capabilities[$cap] = self::current_user_can($cap);
        }
        
        $data['user_capabilities'] = $user_capabilities;
        $data['can_manage_options'] = current_user_can('manage_options');
        
        return $data;
    }

    /**
     * 获取所有权限
     *
     * @return array 权限列表
     */
    public function get_capabilities() {
        return $this->capabilities;
    }

    /**
     * 获取权限继承关系
     *
     * @return array 权限继承关系
     */
    public function get_capability_hierarchy() {
        return $this->capability_hierarchy;
    }
}