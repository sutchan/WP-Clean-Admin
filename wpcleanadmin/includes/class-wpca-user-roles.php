<?php
/**
 * User Roles Management Class
 *
 * Handles role-based permissions and settings filtering for non-administrator users.
 *
 * @package WPCleanAdmin
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WPCA_User_Roles
 *
 * Manages user roles and capabilities for the WP Clean Admin plugin.
 */
class WPCA_User_Roles {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'init'));
    }

    /**
     * Initialize role management
     */
    public function init() {
        // Add capabilities to default roles
        $this->add_capabilities_to_roles();
        
        // Filter settings for non-admin users
        add_filter('option_wpca_settings', array($this, 'filter_settings_for_roles'));
    }
    
    /**
     * Add capabilities to default roles
     */
    private function add_capabilities_to_roles() {
        $roles = array('editor', 'author', 'contributor');
        
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                $role->add_cap('wpca_view_settings');
                $role->add_cap('wpca_manage_menus');
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
        // Only filter for users without manage_options capability
        if (current_user_can('manage_options')) {
            return $settings;
        }
        
        // 避免使用 WPCA_Permissions::current_user_can 以防止递归调用
        // 直接检查用户是否有特定权限
        $user = wp_get_current_user();
        if ($user && isset($user->allcaps['wpca_manage_all']) && $user->allcaps['wpca_manage_all']) {
            return $settings;
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