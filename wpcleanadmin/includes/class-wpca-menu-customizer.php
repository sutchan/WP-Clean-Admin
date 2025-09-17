<?php
/**
 * WP Clean Admin - Menu Customizer
 * 
 * Provides functionality to customize WordPress admin menu
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load WordPress environment
if (!defined('WPINC')) {
    require_once(dirname(__FILE__) . '/../../../wp-load.php');
}

// Debug mode (disabled in production)
define('WPCA_DEBUG', false);

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
        // Early return if no custom ordering needed
        $options = get_option('wpca_settings', []);
        $custom_order = $options['menu_order'] ?? [];
        $submenu_order = $options['submenu_order'] ?? [];
        
        if (empty($custom_order) && empty($submenu_order)) {
            return $menu_order;
        }

        global $menu, $submenu;
        if (empty($menu)) {
            return $menu_order;
        }

        // Process submenu ordering first
        if (!empty($submenu) && !empty($submenu_order)) {
            $this->reorder_submenus($submenu, $submenu_order);
        }

        // Optimized menu reordering
        $slug_to_order_map = $this->build_slug_mapping($menu);
        $new_order = $this->build_new_order($custom_order, $slug_to_order_map, $menu_order);
        
        return $new_order;
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

            foreach ($ordered_slugs as $sub_slug) {
                foreach ($remaining as $index => $item) {
                    if ($item[2] === $sub_slug) {
                        $ordered[] = $item;
                        unset($remaining[$index]);
                        break;
                    }
                }
            }

            $submenu[$parent_slug] = array_merge($ordered, $remaining);
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
        // Common menu slugs and their corresponding menu items
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
        
        if (isset($menu_map[$menu_item])) {
            return $menu_map[$menu_item];
        }
        
        // For third-party plugins, use the menu item as-is
        return $menu_item;
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
            // 验证请求方法
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception(__('Invalid request method', 'wp-clean-admin'));
            }

            // 验证nonce
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if (!wp_verify_nonce($nonce, 'wpca_menu_toggle')) {
                throw new Exception(__('Security check failed. Please refresh the page and try again.', 'wp-clean-admin'));
            }

            // 验证用户权限
            if (!current_user_can('manage_options')) {
                throw new Exception(__('Unauthorized access', 'wp-clean-admin'));
            }

            // 验证必要参数
            $slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
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
            
            // 更新状态并确保值为0或1
            $options['menu_toggles'][$slug] = $state ? 1 : 0;
            
            // 保存设置
            if (!update_option('wpca_settings', $options)) {
                throw new Exception(__('Failed to save settings', 'wp-clean-admin'));
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
            wp_send_json_error(__('Unauthorized access', 'wp-clean-admin'));
        }
        
        $options = get_option('wpca_settings', []);
        
        // Reset all menu-related settings
        $options['menu_order'] = [];
        $options['submenu_order'] = [];
        $options['menu_toggles'] = [];
        
        update_option('wpca_settings', $options);
        
        wp_send_json_success([
            'message' => __('Menu settings reset to default', 'wp-clean-admin')
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
        
        wp_enqueue_script('jquery-ui-sortable');
        wp_localize_script('wpca-admin-script', 'wpca_admin', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpca_menu_toggle'),
            'reset_confirm' => __('Are you sure you want to reset all menu settings to default?', 'wp-clean-admin'),
            'resetting_text' => __('Resetting...', 'wp-clean-admin'),
            'reset_text' => __('Reset Defaults', 'wp-clean-admin'),
            'reset_failed' => __('Reset failed. Please try again.', 'wp-clean-admin')
        ]);
    }
    
    /**
     * Hide menu items via CSS with optimized selectors
     */
    public function hide_menu_items() {
        $options = get_option('wpca_settings', []);
        $hidden_items = array_keys(array_filter($options['menu_toggles'] ?? [], function($visible) {
            return !$visible;
        }));

        if (empty($hidden_items)) {
            return;
        }

        echo '<style id="wpca-menu-hide-css">';
        // Base styles for all hidden menus
        echo '.wpca-hidden-menu, 
              .wpca-hidden-menu .wp-submenu, 
              .wpca-hidden-menu .wp-submenu-wrap {
                display: none !important;
                height: 0 !important;
                overflow: hidden !important;
                transition: all 0.3s ease !important;
              }';
        
        // Specific menu items
        $selectors = array_map(function($slug) {
            return "#toplevel_page_{$slug}, #menu-{$slug}";
        }, $hidden_items);
        
        echo implode(', ', $selectors) . ' { 
            display: none !important; 
            height: 0 !important;
            overflow: hidden !important;
        }';
        echo '</style>';

        // Add JS to handle dynamic menu items
        add_action('admin_footer', function() use ($hidden_items) {
            ?>
            <script>
            jQuery(document).ready(function($) {
                // Apply hidden class to specified menu items
                var hiddenItems = <?php echo json_encode($hidden_items); ?>;
                hiddenItems.forEach(function(slug) {
                    $('#toplevel_page_' + slug).addClass('wpca-hidden-menu');
                    $('#menu-' + slug).addClass('wpca-hidden-menu');
                });

                // Handle dynamically added menu items
                $(document).on('menu-added', function(e, menuId) {
                    if (hiddenItems.includes(menuId.replace('toplevel_page_', ''))) {
                        $('#' + menuId).addClass('wpca-hidden-menu');
                    }
                });
            });
            </script>
            <?php
        });
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