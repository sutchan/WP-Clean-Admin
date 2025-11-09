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
    const CAP_MANAGE_PERFORMANCE = 'wpca_manage_performance';
    const CAP_VIEW_DATABASE_INFO = 'wpca_view_database_info';

    private $capabilities = array(
        self::CAP_VIEW_SETTINGS  => '查看设置',
        self::CAP_MANAGE_MENUS   => '管理菜单',
        self::CAP_MANAGE_PERFORMANCE => '管理性能',
        self::CAP_VIEW_DATABASE_INFO => '查看数据库信息',
        self::CAP_MANAGE_ALL     => '完全控制'
    );

    /**
     * 权限继承关系
     *
     * @var array
     */
    private $capability_hierarchy = array(
        'wpca_manage_all'   => array('wpca_manage_menus', 'wpca_manage_performance', 'wpca_view_database_info', 'wpca_view_settings'),
        'wpca_manage_menus' => array('wpca_view_settings'),
        'wpca_manage_performance' => array('wpca_view_database_info', 'wpca_view_settings'),
        'wpca_view_database_info' => array('wpca_view_settings')
    );

    /**
     * 构造函数
     */
    public function __construct() {
        if (function_exists('add_action')) {
            add_action('admin_init', array($this, 'register_capabilities'));
            add_action('wp_ajax_wpca_check_permission', array($this, 'ajax_check_permission'));
        }
        if (function_exists('add_filter')) {
            add_filter('wpca_localize_script_data', array($this, 'add_capabilities_to_js'));
        }
    }

    /**
     * 注册插件权限
     */
    public function register_capabilities() {
        if (!function_exists('get_role')) {
            return;
        }
        
        // 定义每个角色的权限配置
        $role_configs = array(
            'administrator' => array(
                'wpca_view_settings' => true,
                'wpca_manage_menus' => true,
                'wpca_manage_performance' => true,
                'wpca_view_database_info' => true,
                'wpca_manage_all' => true
            ),
            'editor' => array(
                'wpca_view_settings' => true,
                'wpca_manage_menus' => true,
                'wpca_manage_performance' => false,
                'wpca_view_database_info' => true,
                'wpca_manage_all' => false
            ),
            'author' => array(
                'wpca_view_settings' => true,
                'wpca_manage_menus' => false,
                'wpca_manage_performance' => false,
                'wpca_view_database_info' => false,
                'wpca_manage_all' => false
            )
        );
        
        // 为每个角色应用权限配置
        foreach ($role_configs as $role_name => $caps) {
            $role = get_role($role_name);
            if (!$role) continue;
            
            foreach ($caps as $cap => $grant) {
                if ($grant && method_exists($role, 'add_cap')) {
                    $role->add_cap($cap, true);
                } else if (!$grant) {
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
            if (class_exists('WP_Roles')) {
                $wp_roles = new WP_Roles();
            } else {
                return;
            }
        }
        
        if (isset($wp_roles->role_objects)) {
            foreach ($wp_roles->role_objects as $role) {
                foreach (array_keys($this->capabilities) as $cap) {
                    if (method_exists($role, 'remove_cap')) {
                        $role->remove_cap($cap);
                    }
                }
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
        // 参数类型验证
        if (!is_string($capability) || empty($capability)) {
            return false;
        }
        
        // 超级管理员检查
        if (is_multisite() && function_exists('is_super_admin') && is_super_admin()) {
            return true;
        }
        
        // 获取当前用户对象并验证
        if (!function_exists('wp_get_current_user')) {
            return false;
        }
        
        $user = wp_get_current_user();
        if (!is_object($user) || !isset($user->ID) || empty($user->ID)) {
            return false;
        }
        
        // 检查用户对象中的allcaps属性是否存在且为数组
        if (!isset($user->allcaps) || !is_array($user->allcaps)) {
            // 使用WordPress原生函数作为后备
            return function_exists('current_user_can') ? current_user_can($capability) : false;
        }
        
        // 管理员权限检查
        if (isset($user->allcaps['manage_options']) && $user->allcaps['manage_options']) {
            return true;
        }
        
        // 直接权限检查
        if (isset($user->allcaps[$capability]) && $user->allcaps[$capability]) {
            return true;
        }
        
        // 使用类的私有属性进行权限继承检查
        $instance = new self();
        $hierarchy = $instance->get_capability_hierarchy();
        
        // 确保hierarchy是有效的数组
        if (is_array($hierarchy)) {
            foreach ($hierarchy as $higher_cap => $lower_caps) {
                // 确保lower_caps是有效的数组
                if (is_array($lower_caps) && in_array($capability, $lower_caps) && 
                    isset($user->allcaps[$higher_cap]) && $user->allcaps[$higher_cap]) {
                    return true;
                }
            }
        }
        
        // WordPress原生函数检查作为最后后备
        if (function_exists('current_user_can')) {
            return current_user_can($capability);
        }
        
        return false;
    }
    
    /**
     * AJAX权限检查
     */
    public function ajax_check_permission() {
        // 检查是否为 AJAX 请求
        if (function_exists('wp_doing_ajax') && !wp_doing_ajax()) {
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => __('Invalid request', 'wp-clean-admin')
                ), 400);
            }
            return;
        }
        
        // 检查nonce - 注意: 使用静默模式以自定义错误消息
        if (function_exists('check_ajax_referer') && !check_ajax_referer('wpca_settings-options', false, false)) {
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => __('Security verification failed', 'wp-clean-admin')
                ), 403);
            }
            return;
        }
        
        // 基础安全检查 - 确保用户已登录
        if (function_exists('is_user_logged_in') && !is_user_logged_in()) {
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => __('User not logged in', 'wp-clean-admin')
                ), 401);
            }
            return;
        }
        
        // 检查参数
        if (!isset($_POST['capability']) || empty($_POST['capability'])) {
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => __('Missing permission parameter', 'wp-clean-admin')
                ), 400);
            }
            return;
        }
        
        $capability = function_exists('sanitize_text_field') ? sanitize_text_field($_POST['capability']) : filter_var($_POST['capability'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        
        // 验证权限名称是否有效
        $instance = new self();
        $valid_caps = array_keys($instance->get_capabilities());
        if (!in_array($capability, $valid_caps)) {
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => __('Invalid permission name', 'wp-clean-admin')
                ), 400);
            }
            return;
        }
        
        // 检查权限
        $has_permission = self::current_user_can($capability);
        
        if (function_exists('wp_send_json_success')) {
            wp_send_json_success(array(
                'has_permission' => $has_permission,
                'capability' => $capability,
                'timestamp' => function_exists('time') ? time() : 0
            ));
        }
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
        $data['can_manage_options'] = function_exists('current_user_can') ? current_user_can('manage_options') : false;
        
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
?>