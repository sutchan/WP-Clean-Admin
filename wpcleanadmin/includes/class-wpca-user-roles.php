<?php
if (!defined('ABSPATH')) exit;

class WPCA_User_Roles {
    public function __construct() {
        add_action('admin_init', array($this, 'init'));
    }

    public function init() {
        // Add role capabilities
        $roles = array('editor', 'author', 'contributor');
        foreach ($roles as $role) {
            $role = get_role($role);
            if ($role) {
                $role->add_cap('wpca_customize_admin');
            }
        }
        
        // Only allow administrators to access settings
        if (!current_user_can('manage_options')) {
            add_filter('wpca_get_settings', array($this, 'filter_settings_for_roles'));
        }
    }

    public function filter_settings_for_roles($settings) {
        // Remove sensitive settings for non-admins
        unset($settings['hide_admin_menu_items']);
        unset($settings['hide_admin_bar_items']);
        return $settings;
    }
}