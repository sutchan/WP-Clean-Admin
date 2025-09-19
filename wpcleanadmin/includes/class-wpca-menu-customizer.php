<?php
/**
 * WP Clean Admin - Menu Customizer
 * 
 * @package WPCleanAdmin
 * @subpackage MenuCustomizer
 * @since 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Ensure we're in WordPress context
if (!defined('WPINC')) {
    die('This file must be loaded within WordPress environment');
}

// Ensure required WordPress functions are available
if (!function_exists('add_action')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Debug mode (disabled in production)
define('WPCA_DEBUG', false);

/**
 * Class WPCA_Menu_Customizer
 *
 * Handles WordPress admin menu customization functionality
 */
class WPCA_Menu_Customizer {
    public function __construct() {
        add_action('admin_menu', [$this, 'init_menu_customization']);
        add_action('wp_ajax_wpca_toggle_menu', [$this, 'ajax_toggle_menu']);
        add_action('wp_ajax_wpca_reset_menu', [$this, 'ajax_reset_menu']);
    }
    
    /**
     * Initialize menu customization
     */
    public function init_menu_customization() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get saved menu settings from wpca_settings option
        $options = get_option('wpca_settings', []);
        
        // 菜单排序功能
        if (!empty($options['menu_order'])) {
            add_filter('custom_menu_order', '__return_true');
            add_filter('menu_order', [$this, 'reorder_admin_menu']);
        }
        
        // 初始化菜单项显示控制功能
        add_action('admin_head', [$this, 'hide_menu_items']);
        
        // 加载脚本
        add_action('admin_enqueue_scripts', [$this, 'enqueue_menu_scripts']);
    }
    
    /**
     * Reorder admin menu items (both top-level and submenus)
     */
    public function reorder_admin_menu($menu_order) {
        // 获取一次选项并缓存
        static $options = null;
        if ($options === null) {
            $options = get_option('wpca_settings', []);
        }
        
        $custom_order = $options['menu_order'] ?? [];
        $submenu_order = $options['submenu_order'] ?? [];
        
        if (empty($custom_order) && empty($submenu_order)) {
            return $menu_order;
        }

        global $menu, $submenu;
        if (empty($menu)) {
            return $menu_order;
        }

        // 创建菜单项的原始副本
        $original_menu = $menu;
        $original_submenu = $submenu;

        // 先处理子菜单排序
        if (!empty($submenu) && !empty($submenu_order)) {
            $this->reorder_submenus($submenu, $submenu_order);
        }

        // 优化后的菜单重新排序
        $slug_to_order_map = $this->build_slug_mapping($original_menu);
        $new_order = $this->build_new_order($custom_order, $slug_to_order_map, $menu_order);
        
        // 确保不会重复添加菜单项
        $seen = [];
        $filtered_order = [];
        foreach ($new_order as $item) {
            if (!isset($seen[$item])) {
                $seen[$item] = true;
                $filtered_order[] = $item;
            }
        }
        
        return $filtered_order;
    }

    /**
     * Reorder submenus based on saved order
     */
    private function reorder_submenus(&$submenu, $submenu_order) {
        foreach ($submenu_order as $parent_slug => $ordered_slugs) {
            if (empty($submenu[$parent_slug]) || empty($ordered_slugs)) {
                continue;
            }

            $original = $submenu[$parent_slug];
            $ordered = [];
            $remaining = $original;
            $seen_slugs = [];

            foreach ($ordered_slugs as $sub_slug) {
                foreach ($remaining as $index => $item) {
                    if ($item[2] === $sub_slug && !isset($seen_slugs[$sub_slug])) {
                        $ordered[] = $item;
                        $seen_slugs[$sub_slug] = true;
                        unset($remaining[$index]);
                        break;
                    }
                }
            }

            // 合并时去重
            $merged = array_merge($ordered, $remaining);
            $unique_merged = [];
            $seen = [];
            foreach ($merged as $item) {
                $slug = $item[2];
                if (!isset($seen[$slug])) {
                    $seen[$slug] = true;
                    $unique_merged[] = $item;
                }
            }
            
            $submenu[$parent_slug] = $unique_merged;
        }
    }

    /**
     * Build mapping between menu slugs and their IDs
     */
    private function build_slug_mapping($menu) {
        $mapping = [];
        foreach ($menu as $item) {
            if (!empty($item[2])) {
                $slug = $this->get_menu_slug_from_item($item[2]);
                if ($slug) {
                    $mapping[$slug] = $item[2];
                }
            }
        }
        return $mapping;
    }

    /**
     * Build new menu order based on custom order and existing items
     */
    private function build_new_order($custom_order, $slug_mapping, $original_order) {
        $new_order = [];
        $processed = [];

        // Add items from custom order first
        foreach ($custom_order as $slug) {
            if (isset($slug_mapping[$slug])) {
                $new_order[] = $slug_mapping[$slug];
                $processed[$slug_mapping[$slug]] = true;
            }
        }

        // Add remaining items preserving original order
        foreach ($original_order as $item) {
            if (!isset($processed[$item])) {
                $new_order[] = $item;
            }
        }

        return $new_order;
    }
    
    /**
     * Get the menu slug from a menu item
     */
    private function get_menu_slug_from_item($menu_item) {
        // 核心菜单映射
        $menu_map = [
            'index.php' => 'dashboard',
            'edit.php' => 'posts',
            'upload.php' => 'media',
            'edit.php?post_type=page' => 'pages',
            'edit-comments.php' => 'comments',
            'themes.php' => 'themes.php',
            'plugins.php' => 'plugins.php',
            'users.php' => 'users.php',
            'tools.php' => 'tools.php',
            'options-general.php' => 'options-general.php'
        ];
        
        // 处理带查询参数的菜单项
        if (strpos($menu_item, '?') !== false) {
            $query = parse_url($menu_item, PHP_URL_QUERY);
            parse_str($query, $params);
            if (isset($params['page'])) {
                return $params['page'];
            }
            return strtok($menu_item, '?'); // 返回问号前的部分
        }
        
        // 处理核心菜单项
        if (isset($menu_map[$menu_item])) {
            return $menu_map[$menu_item];
        }
        
        // 处理第三方插件菜单
        return sanitize_key($menu_item);
    }
    
    /**
     * Get all admin menu items (both top-level and submenus)
     */
    public function get_all_menu_items() {
        global $menu, $submenu;
        $menu_items = [];
        
        // Get top-level menus
        if (!empty($menu)) {
            foreach ($menu as $item) {
                if (isset($item[2])) {
                    $slug = $this->get_menu_slug_from_item($item[2]);
                    if ($slug) {
                        $menu_items[$slug] = [
                            'title' => isset($item[0]) ? $item[0] : $slug,
                            'type' => 'top',
                            'parent' => ''
                        ];
                    }
                }
            }
        }
        
        // Get submenus
        if (!empty($submenu)) {
            foreach ($submenu as $parent_slug => $sub_items) {
                foreach ($sub_items as $sub_item) {
                    if (isset($sub_item[2])) {
                        $full_slug = $parent_slug . '|' . $sub_item[2];
                        $menu_items[$full_slug] = [
                            'title' => isset($sub_item[0]) ? $sub_item[0] : $sub_item[2],
                            'type' => 'sub',
                            'parent' => $parent_slug
                        ];
                    }
                }
            }
        }
        
        return $menu_items;
    }
    
    /**
     * Handle AJAX menu toggle requests
     */
    public function ajax_toggle_menu() {
        header('Content-Type: application/json');
        
        try {
            // 严格验证请求方法
            if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception(__('Invalid request method', 'wp-clean-admin'), 405);
            }

            // 严格验证nonce
            if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpca_menu_toggle')) {
                throw new Exception(__('Security check failed. Please refresh the page and try again.', 'wp-clean-admin'), 403);
            }

            // 验证用户权限
            if (!current_user_can('manage_options')) {
                throw new Exception(__('Unauthorized access', 'wp-clean-admin'), 403);
            }

            // 严格过滤输入参数
            $slug = isset($_POST['slug']) ? sanitize_key($_POST['slug']) : '';
            $state = isset($_POST['state']) ? (int)$_POST['state'] : null;
            
            if (empty($slug)) {
                throw new Exception(__('Missing menu slug', 'wp-clean-admin'));
            }
            
            if ($state === null) {
                throw new Exception(__('Missing state parameter', 'wp-clean-admin'));
            }

            // 获取当前设置
            $options = get_option('wpca_settings', []);
            
            // 初始化menu_toggles数组
            if (!isset($options['menu_toggles'])) {
                $options['menu_toggles'] = [];
            }
            
            // 验证菜单项是否存在并获取标准slug格式
            $all_items = $this->get_all_menu_items();
            $standard_slug = $this->get_menu_slug_from_item($slug);
            
            if (!isset($all_items[$standard_slug]) && !isset($all_items[$slug])) {
                throw new Exception(
                    sprintf(__('Menu item "%s" does not exist', 'wp-clean-admin'), $slug),
                    'invalid_menu_item'
                );
            }
            
            // 使用标准slug格式保存状态
            $save_slug = isset($all_items[$standard_slug]) ? $standard_slug : $slug;
            $options['menu_toggles'][$save_slug] = $state ? 1 : 0;
            
            // 清理无效的菜单项设置
            $options['menu_toggles'] = array_intersect_key(
                $options['menu_toggles'],
                $all_items
            );
            
            // 保存设置
            if (!update_option('wpca_settings', $options)) {
                throw new Exception(__('Failed to save settings', 'wp-clean-admin'), 'update_failed');
            }
            
            // 状态更新完成
            
            wp_send_json_success([
                'message' => __('Menu toggle updated', 'wp-clean-admin'),
                'data' => [
                    'slug' => $slug,
                    'state' => $state
                ]
            ], 200);
            
        } catch (Exception $e) {
            status_header(400);
            wp_send_json_error([
                'message' => $e->getMessage(),
                'data' => [
                    'slug' => $_REQUEST['slug'] ?? null,
                    'state' => $_REQUEST['state'] ?? null
                ]
            ], 400);
        }
    }

    /**
     * Reset menu settings to default
     */
    public function ajax_reset_menu() {
        check_ajax_referer('wpca_menu_toggle', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Unauthorized access', 'wp-clean-admin'),
                'code' => 'unauthorized'
            ], 403);
        }
        
        // 获取要重置的项目类型
        $reset_types = isset($_POST['reset_types']) ? (array)$_POST['reset_types'] : ['order', 'toggles'];
        $options = get_option('wpca_settings', []);
        
        // 批量重置设置
        $all_items = $this->get_all_menu_items();
        if (in_array('order', $reset_types)) {
            $options['menu_order'] = [];
            $options['submenu_order'] = [];
        }
        if (in_array('toggles', $reset_types)) {
            // 只保留实际存在的菜单项设置
            $options['menu_toggles'] = array_intersect_key(
                $options['menu_toggles'],
                $all_items
            );
        }
        
        // 保存设置
        if (!update_option('wpca_settings', $options)) {
            wp_send_json_error([
                'message' => __('Failed to reset settings', 'wp-clean-admin'),
                'code' => 'update_failed'
            ], 500);
        }
        
        // 返回重置结果
        wp_send_json_success([
            'message' => __('Menu settings reset successfully', 'wp-clean-admin'),
            'data' => [
                'reset_types' => $reset_types,
                'new_settings' => $options
            ]
        ]);
    }

    /**
     * Enqueue scripts for menu customization
     */
    public function enqueue_menu_scripts($hook) {
        // Only load on our settings page
        if (strpos($hook, 'settings_page_wp_clean_admin') === false && strpos($hook, 'options-general.php') === false) {
            return;
        }
        
        // Register and enqueue our admin script
        wp_register_script(
            'wpca-menu-script',
            plugins_url('assets/js/menu-sorting.js', dirname(__FILE__)),
            ['jquery', 'jquery-ui-sortable', 'wpca-core'],
            filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/js/menu-sorting.js'),
            true
        );
        
        wp_enqueue_script('wpca-menu-script');
        wp_enqueue_script('jquery-ui-sortable');
        
        // Localize script with translated strings
        wp_localize_script('wpca-admin-script', 'wpca_admin', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpca_menu_toggle'),
            'reset_options' => [
                'order' => __('Menu Order', 'wp-clean-admin'),
                'toggles' => __('Menu Visibility', 'wp-clean-admin')
            ],
            'reset_confirm' => __('Are you sure you want to reset the selected menu settings?', 'wp-clean-admin'),
            'resetting_text' => __('Resetting...', 'wp-clean-admin'),
            'reset_text' => __('Reset Selected', 'wp-clean-admin'),
            'reset_failed' => __('Reset failed. Please try again.', 'wp-clean-admin'),
            'select_reset_type' => __('Please select at least one setting type to reset', 'wp-clean-admin')
        ]);
    }
    
    /**
     * Hide menu items via CSS with optimized selectors
     */
    public function hide_menu_items() {
        $options = get_option('wpca_settings', []);
        $all_items = $this->get_all_menu_items();
        $hidden_items = [];
        
        // 确保只处理实际存在的菜单项
        foreach ($options['menu_toggles'] ?? [] as $slug => $visible) {
            if (!$visible && isset($all_items[$slug])) {
                $hidden_items[] = $slug;
            }
        }

        if (empty($hidden_items)) {
            return;
        }

        echo '<style id="wpca-menu-hide-css">';
        // 兼容所有WordPress版本的通用选择器
        echo 'li.wpca-hidden-menu, 
              li.wpca-hidden-menu > a, 
              li.wpca-hidden-menu .wp-submenu, 
              li.wpca-hidden-menu .wp-submenu-wrap,
              li.wpca-hidden-menu .wp-submenu-head,
              .folded li.wpca-hidden-menu .wp-submenu {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
                overflow: hidden !important;
                margin: 0 !important;
                padding: 0 !important;
                opacity: 0 !important;
                pointer-events: none !important;
              }';
        
        // 特定菜单项选择器（覆盖更多可能的DOM结构）
        $selectors = array_map(function($slug) {
            return "#toplevel_page_{$slug}, 
                    #menu-{$slug}, 
                    li#toplevel_page_{$slug}, 
                    li#menu-{$slug},
                    a[href*='{$slug}'].menu-top,
                    .wp-menu-open #toplevel_page_{$slug},
                    .wp-not-current-submenu #toplevel_page_{$slug}";
        }, $hidden_items);
        
        echo implode(', ', $selectors) . ' { 
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }';
        echo '</style>';

        // Move JS to external file and handle via wp_enqueue_script
        wp_add_inline_script('wpca-admin-script', sprintf(
            'var wpcaHiddenItems = %s;',
            json_encode($hidden_items)
        ));
    }
    
    /**
     * Sanitize menu settings
     */
    public function sanitize_menu_settings($input) {
        $output = [];
        
        if (!empty($input['order'])) {
            $output['order'] = array_map('sanitize_text_field', $input['order']);
        }
        
        if (!empty($input['menu_toggles'])) {
            $output['menu_toggles'] = array_map('boolval', $input['menu_toggles']);
        }
        
        return $output;
    }
}