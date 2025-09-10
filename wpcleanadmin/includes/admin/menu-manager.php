<?php
/**
 * 菜单管理模块
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Clean_Admin_Menu {
    private static $instance;
    
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'modify_admin_menu'], 999);
    }
    
    public function register_settings() {
        register_setting('wpca_menu', 'wpca_hidden_menus', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_array'],
            'default' => []
        ]);
        
        register_setting('wpca_menu', 'wpca_hidden_submenus', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_array'],
            'default' => []
        ]);

        register_setting('wpca_menu', 'wpca_menu_order', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_array'],
            'default' => []
        ]);
    }
    
    public function sanitize_array($input) {
        return is_array($input) ? $input : [];
    }
    
    public function modify_admin_menu() {
        $hidden_menus = get_option('wpca_hidden_menus', []);
        $hidden_submenus = get_option('wpca_hidden_submenus', []);
        $menu_order = get_option('wpca_menu_order', []);
        
        // 隐藏主菜单
        foreach ($hidden_menus as $menu_slug) {
            remove_menu_page($menu_slug);
        }
        
        // 隐藏子菜单
        foreach ($hidden_submenus as $submenu) {
            list($parent_slug, $child_slug) = explode('|', $submenu);
            remove_submenu_page($parent_slug, $child_slug);
        }

        // 重新排序菜单
        if (!empty($menu_order)) {
            global $menu;
            $ordered_menu = [];
            foreach ($menu_order as $slug) {
                foreach ($menu as $item) {
                    if ($item[2] === $slug) {
                        $ordered_menu[] = $item;
                        break;
                    }
                }
            }
            $menu = $ordered_menu;
        }
    }
}

// 初始化菜单管理模块
WP_Clean_Admin_Menu::get_instance();