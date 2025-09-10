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
        add_action('admin_menu', [$this, 'init_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'modify_admin_menu'], 999);
    }
    
    public function register_settings() {
        register_setting('wp-clean-admin', 'wpca_hidden_menus', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_menu_settings'],
            'default' => []
        ]);
        
        register_setting('wp-clean-admin', 'wpca_hidden_submenus', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_menu_settings'],
            'default' => []
        ]);

        register_setting('wp-clean-admin', 'wpca_menu_order', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_menu_settings'],
            'default' => []
        ]);
    }
    
    public function sanitize_menu_settings($input) {
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
    
    public function init_menu() {
        // 添加设置页面
        add_options_page(
            __('WP Clean Admin Settings', 'wp-clean-admin'),
            __('Clean Admin', 'wp-clean-admin'),
            'manage_options',
            'wp-clean-admin',
            [$this, 'settings_page']
        );
    }
    
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // 保存设置
        if (isset($_POST['submit'])) {
            check_admin_referer('wp-clean-admin-settings');
            
            $allowed_scripts = isset($_POST['allowed_scripts']) ? 
                array_map('sanitize_text_field', $_POST['allowed_scripts']) : [];
            $allowed_styles = isset($_POST['allowed_styles']) ? 
                array_map('sanitize_text_field', $_POST['allowed_styles']) : [];
                
            update_option('wpca_allowed_scripts', $allowed_scripts);
            update_option('wpca_allowed_styles', $allowed_styles);
            
            echo '<div class="notice notice-success"><p>' . 
                esc_html__('Settings saved.', 'wp-clean-admin') . '</p></div>';
        }
        
        // 获取当前设置
        $allowed_scripts = get_option('wpca_allowed_scripts', ['jquery', 'wp-api', 'wp-util']);
        $allowed_styles = get_option('wpca_allowed_styles', ['admin-bar', 'common']);
        
        // 显示设置表单
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('WP Clean Admin Settings', 'wp-clean-admin') . '</h1>';
        
        echo '<form method="post">';
        wp_nonce_field('wp-clean-admin-settings');
        
        echo '<h2>' . esc_html__('Performance Optimization', 'wp-clean-admin') . '</h2>';
        echo '<table class="form-table">';
        
        // 脚本设置
        echo '<tr><th scope="row">' . esc_html__('Allowed Scripts', 'wp-clean-admin') . '</th><td>';
        echo '<fieldset>';
        echo '<label><input type="checkbox" name="allowed_scripts[]" value="jquery" ' . 
            checked(in_array('jquery', $allowed_scripts), true, false) . '> jQuery</label><br>';
        echo '<label><input type="checkbox" name="allowed_scripts[]" value="wp-api" ' . 
            checked(in_array('wp-api', $allowed_scripts), true, false) . '> WP REST API</label><br>';
        echo '<label><input type="checkbox" name="allowed_scripts[]" value="wp-util" ' . 
            checked(in_array('wp-util', $allowed_scripts), true, false) . '> WP Utils</label>';
        echo '</fieldset></td></tr>';
        
        // 样式设置
        echo '<tr><th scope="row">' . esc_html__('Allowed Styles', 'wp-clean-admin') . '</th><td>';
        echo '<fieldset>';
        echo '<label><input type="checkbox" name="allowed_styles[]" value="admin-bar" ' . 
            checked(in_array('admin-bar', $allowed_styles), true, false) . '> Admin Bar</label><br>';
        echo '<label><input type="checkbox" name="allowed_styles[]" value="common" ' . 
            checked(in_array('common', $allowed_styles), true, false) . '> Common Styles</label>';
        echo '</fieldset></td></tr>';
        
        // 菜单排序设置
        echo '<tr><th scope=\"row\">' . esc_html__('Menu Order', 'wp-clean-admin') . '</th><td>';
        echo '<div id=\"menu-sortable\" style=\"max-height:400px;overflow-y:auto;border:1px solid #ddd;padding:10px;\">';
        
        global $menu;
        $menu_order = get_option('wpca_menu_order', array_column($menu, 2));
        
        // 按当前顺序或默认顺序显示菜单
        foreach ($menu_order as $slug) {
            foreach ($menu as $item) {
                if ($item[2] === $slug && !empty($item[0])) {
                    echo '<div class=\"menu-item\" data-slug=\"' . esc_attr($slug) . '\" style=\"padding:8px;margin:4px 0;background:#f5f5f5;cursor:move;\">';
                    echo '<input type=\"hidden\" name=\"wpca_menu_order[]\" value=\"' . esc_attr($slug) . '\">';
                    echo esc_html(strip_tags($item[0]));
                    echo '</div>';
                    break;
                }
            }
        }
        
        echo '</div>';
        echo '<p class=\"description\">' . esc_html__('Drag to reorder menu items', 'wp-clean-admin') . '</p>';
        echo '</td></tr>';

        // 添加拖拽排序JS
        echo '<script>
        jQuery(document).ready(function($) {
            if (typeof $.fn.sortable !== "undefined") {
                $("#menu-sortable").sortable({
                    placeholder: "ui-state-highlight",
                    update: function(event, ui) {
                        // 更新隐藏字段的顺序
                        var newOrder = [];
                        $("#menu-sortable .menu-item").each(function() {
                            newOrder.push($(this).data("slug"));
                        });
                    }
                });
            } else {
                console.warn("jQuery UI Sortable is not available");
            }
        });
        </script>';

        // 主菜单管理设置
        echo '<tr><th scope="row">' . esc_html__('Hidden Main Menus', 'wp-clean-admin') . '</th><td>';
        echo '<fieldset>';
        
        global $menu;
        $hidden_menus = get_option('wpca_hidden_menus', []);
        
        foreach ($menu as $menu_item) {
            if (!empty($menu_item[0]) && !empty($menu_item[2])) {
                $menu_name = strip_tags($menu_item[0]);
                $menu_slug = $menu_item[2];
                
                echo '<label><input type="checkbox" name="wpca_hidden_menus[]" value="' . 
                    esc_attr($menu_slug) . '" ' . 
                    checked(in_array($menu_slug, $hidden_menus), true, false) . '> ' . 
                    esc_html($menu_name) . '</label><br>';
            }
        }
        
        echo '</fieldset></td></tr>';
        
        // 子菜单管理设置
        echo '<tr><th scope="row">' . esc_html__('Hidden Submenus', 'wp-clean-admin') . '</th><td>';
        echo '<fieldset>';
        
        global $submenu;
        $hidden_submenus = get_option('wpca_hidden_submenus', []);
        
        foreach ($submenu as $parent_slug => $submenu_items) {
            if (!empty($submenu_items)) {
                foreach ($submenu_items as $submenu_item) {
                    if (!empty($submenu_item[0]) && !empty($submenu_item[2])) {
                        $submenu_name = strip_tags($submenu_item[0]);
                        $submenu_slug = $submenu_item[2];
                        $value = $parent_slug . '|' . $submenu_slug;
                        
                        echo '<label><input type="checkbox" name="wpca_hidden_submenus[]" value="' . 
                            esc_attr($value) . '" ' . 
                            checked(in_array($value, $hidden_submenus), true, false) . '> ' . 
                            esc_html($parent_slug . ' → ' . $submenu_name) . '</label><br>';
                    }
                }
            }
        }
        
        echo '</fieldset></td></tr>';
        
        echo '</table>';
        submit_button();
        echo '</form>';
        echo '</div>';
    }
}

// 初始化菜单管理模块
WP_Clean_Admin_Menu::get_instance();