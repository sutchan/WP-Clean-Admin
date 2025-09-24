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



/**
 * 权限管理类
 */
class WPCA_Permissions {

    /**
     * 插件权限列表
     *
     * @var array
     */
    const CAP_VIEW_SETTINGS = 'wpca_view_settings';
    const CAP_MANAGE_MENUS = 'wpca_manage_menus';
    const CAP_MANAGE_ALL = 'wpca_manage_all';

    private $capabilities = array(
        self::CAP_VIEW_SETTINGS  => '查看设置',
        self::CAP_MANAGE_MENUS   => '管理菜单',
        self::CAP_MANAGE_ALL     => '完全控制'
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
        // 定义每个角色的权限配置
        $role_configs = array(
            'administrator' => array(
                'wpca_view_settings' => true,
                'wpca_manage_menus' => true,
                'wpca_manage_all' => true
            ),
            'editor' => array(
                'wpca_view_settings' => true,
                'wpca_manage_menus' => true,
                'wpca_manage_all' => false
            ),
            'author' => array(
                'wpca_view_settings' => true,
                'wpca_manage_menus' => false,
                'wpca_manage_all' => false
            )
        );
        
        // 为每个角色应用权限配置
        foreach ($role_configs as $role_name => $caps) {
            $role = get_role($role_name);
            if (!$role) continue;
            
            foreach ($caps as $cap => $grant) {
                if ($grant) {
                    $role->add_cap($cap, true);
                } else {
                    // 安全地移除权限（防止PHP警告）
                    if (method_exists($role, 'remove_cap')) {
                        $role->remove_cap($cap);
                    }
                }
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
        // 简化版本，避免递归调用和复杂的权限检查
        
        // 管理员始终有权限
        if (function_exists('is_super_admin') && is_super_admin()) {
            return true;
        }
        
        // 获取当前用户
        $user = null;
        if (function_exists('wp_get_current_user')) {
            $user = wp_get_current_user();
        }
        
        // 如果用户未登录，返回 false
        if (!$user || !$user->ID) {
            return false;
        }
        
        // 管理员权限检查
        if (isset($user->allcaps['manage_options']) && $user->allcaps['manage_options']) {
            return true;
        }
        
        // 直接检查指定权限
        if (isset($user->allcaps[$capability]) && $user->allcaps[$capability]) {
            return true;
        }
        
        // 简化的权限继承检查
        $higher_caps = array(
            'wpca_manage_all' => array('wpca_manage_menus', 'wpca_view_settings'),
            'wpca_manage_menus' => array('wpca_view_settings')
        );
        
        // 检查是否有更高级权限
        foreach ($higher_caps as $higher_cap => $lower_caps) {
            if (in_array($capability, $lower_caps) && 
                isset($user->allcaps[$higher_cap]) && 
                $user->allcaps[$higher_cap]) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * AJAX权限检查
     */
    public function ajax_check_permission() {
        // 检查是否为 AJAX 请求
        if (!wp_doing_ajax()) {
            wp_send_json_error(array(
                'message' => __('非法请求', 'wp-clean-admin')
            ), 400);
        }
        
        // 检查nonce - 注意: 使用静默模式以自定义错误消息
        if (!check_ajax_referer('wpca_settings-options', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('安全验证失败', 'wp-clean-admin')
            ), 403);
        }
        
        // 基础安全检查 - 确保用户已登录
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('用户未登录', 'wp-clean-admin')
            ), 401);
        }
        
        // 检查参数
        if (!isset($_POST['capability']) || empty($_POST['capability'])) {
            wp_send_json_error(array(
                'message' => __('缺少权限参数', 'wp-clean-admin')
            ), 400);
        }
        
        $capability = sanitize_text_field($_POST['capability']);
        
        // 验证权限名称是否有效
        $instance = new self();
        $valid_caps = array_keys($instance->get_capabilities());
        if (!in_array($capability, $valid_caps)) {
            wp_send_json_error(array(
                'message' => __('无效的权限名称', 'wp-clean-admin')
            ), 400);
        }
        
        // 检查权限
        $has_permission = self::current_user_can($capability);
        
        wp_send_json_success(array(
            'has_permission' => $has_permission,
            'capability' => $capability,
            'timestamp' => time()
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