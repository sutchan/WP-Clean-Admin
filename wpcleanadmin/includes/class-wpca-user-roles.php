<?php
/**
 * User Roles Management Class
 *
 * Handles role-based permissions and settings filtering for non-administrator users.
 *
 * @package WPCleanAdmin
 * @version 1.7.13
 * @file wpcleanadmin/includes/class-wpca-user-roles.php
 * @updated 2025-06-18
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WPCA_User_Roles class
 * Handles user role management and capabilities
 * 
 * @package WPCleanAdmin
 * @since 1.0.0
 * @version 1.7.13
 */
class WPCA_User_Roles {
    
    /**
     * Constructor
     */
    public function __construct() {
        if (function_exists('add_action')) {
            add_action('admin_init', array($this, 'init'));
        }
    }

    /**
     * Initialize role management
     */
    public function init() {
        // Add capabilities to default roles
        if (method_exists($this, 'add_capabilities_to_roles')) {
            $this->add_capabilities_to_roles();
        }
        
        // Filter settings for non-admin users
        if (function_exists('add_filter')) {
            add_filter('option_wpca_settings', array($this, 'filter_settings_for_roles'));
        }
    }
    
    /**
     * Add capabilities to default roles
     */
    private function add_capabilities_to_roles() {
        $roles = array('editor', 'author', 'contributor');
        
        if (function_exists('get_role')) {
            foreach ($roles as $role_name) {
                $role = get_role($role_name);
                if ($role && method_exists($role, 'add_cap')) {
                    $role->add_cap('wpca_view_settings');
                    $role->add_cap('wpca_manage_menus');
                }
            }
        }
    }

    /**
     * Filter settings for non-administrator users
     *
     * @param array $settings The settings array.
     * @return array Filtered settings.
     */
    public function filter_settings_for_roles($settings) {
        // 确保 $settings 是一个数组
        if (!is_array($settings)) {
            return array();
        }
        
        // Only filter for users without manage_options capability
        if (function_exists('current_user_can') && current_user_can('manage_options')) {
            return $settings;
        }
        
        // 避免使用 WPCA_Permissions::current_user_can 以防止递归调用
        // 直接检查用户是否拥有特定权限
        if (function_exists('wp_get_current_user')) {
            $user = wp_get_current_user();
            if ($user && is_object($user) && isset($user->allcaps) && is_array($user->allcaps) && isset($user->allcaps['wpca_manage_all']) && $user->allcaps['wpca_manage_all']) {
                return $settings;
            }
        }
        
        // Remove sensitive settings for non-admins
        $sensitive_settings = array(
            'menu_toggles',
            'menu_order',
            'menu_toggle',
            'hide_admin_bar_items'
        );
        
        foreach ($sensitive_settings as $setting) {
            if (isset($settings[$setting])) {
                unset($settings[$setting]);
            }
        }
        
        return $settings;
    }
}
?>