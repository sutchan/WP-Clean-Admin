<?php
/**
 * WP Clean Admin - Menu Customizer
 * 
 * Provides functionality to customize WordPress admin menu
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPCA_Menu_Customizer {
    public function __construct() {
        add_action('admin_menu', [$this, 'init_menu_customization']);
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
        global $menu, $submenu;
        
        // Get settings from wpca_settings option
        $options = get_option('wpca_settings', []);
        
        // 总开关不再影响菜单排序功能
        // 菜单排序将始终保持激活状态
        
        $custom_order = isset($options['menu_order']) ? $options['menu_order'] : [];
        $submenu_order = isset($options['submenu_order']) ? $options['submenu_order'] : [];
        
        if ((empty($custom_order) && empty($submenu_order)) || empty($menu)) {
            return $menu_order;
        }
        
        // Process submenu ordering
        if (!empty($submenu) && !empty($submenu_order)) {
            foreach ($submenu_order as $parent_slug => $ordered_slugs) {
                if (isset($submenu[$parent_slug])) {
                    $original_submenu = $submenu[$parent_slug];
                    $new_submenu = [];
                    
                    // Reorder based on saved order
                    foreach ($ordered_slugs as $sub_slug) {
                        foreach ($original_submenu as $index => $sub_item) {
                            if ($sub_item[2] === $sub_slug) {
                                $new_submenu[] = $sub_item;
                                unset($original_submenu[$index]);
                                break;
                            }
                        }
                    }
                    
                    // Add any remaining items
                    $submenu[$parent_slug] = array_merge($new_submenu, $original_submenu);
                }
            }
        }
        
        // Debug - log information for troubleshooting
        error_log(__('WP Clean Admin - Custom Menu Order:', 'wp-clean-admin') . ' ' . print_r($custom_order, true));
        error_log(__('WP Clean Admin - Original Menu Order:', 'wp-clean-admin') . ' ' . print_r($menu_order, true));
        error_log(__('WP Clean Admin - Global Menu:', 'wp-clean-admin') . ' ' . print_r($menu, true));
        
        // Create a mapping between menu slugs and their actual menu_order values
        $slug_to_order_map = [];
        foreach ($menu as $position => $item) {
            if (isset($item[2])) {
                $slug = $this->get_menu_slug_from_item($item[2]);
                if ($slug) {
                    $slug_to_order_map[$slug] = $item[2];
                }
            }
        }
        
        error_log(__('WP Clean Admin - Slug to Order Map:', 'wp-clean-admin') . ' ' . print_r($slug_to_order_map, true));
        
        // Create new order based on saved settings
        $new_order = [];
        foreach ($custom_order as $menu_slug) {
            if (isset($slug_to_order_map[$menu_slug])) {
                $new_order[] = $slug_to_order_map[$menu_slug];
            }
        }
        
        // Add any menu items that weren't in the saved order
        foreach ($menu_order as $item) {
            if (!in_array($item, $new_order)) {
                $new_order[] = $item;
            }
        }
        
        error_log(__('WP Clean Admin - New Menu Order:', 'wp-clean-admin') . ' ' . print_r($new_order, true));
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
     * Enqueue scripts for menu customization
     */
    public function enqueue_menu_scripts($hook) {
        // Only load on our settings page
        if (strpos($hook, 'settings_page_wp_clean_admin') === false && strpos($hook, 'options-general.php') === false) {
            return;
        }
        
        wp_enqueue_script('jquery-ui-sortable');
        
        // Add inline script for saving menu order
        add_action('admin_footer', function() {
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                if ($('.wpca-menu-sortable').length) {
                    $('.wpca-menu-sortable').sortable({
                        update: function(event, ui) {
                            var menuOrder = [];
                            $('.wpca-menu-sortable li').each(function() {
                                menuOrder.push($(this).data('menu-slug'));
                            });
                            
                            // 添加翻译字符串，但不影响功能
                            // <?php _e('Menu items reordered', 'wp-clean-admin'); ?>
                            
                            // Update hidden field with new order
                            $('#wpca_menu_order').val(JSON.stringify(menuOrder));
                        }
                    });
                }
            });
            </script>
            <?php
        });
    }
    
    /**
     * Hide menu items via CSS
     */
    public function hide_menu_items() {
        // 获取设置
        $options = get_option('wpca_settings', []);
        
        // 检查是否有需要隐藏的菜单项
        if (empty($options['menu_toggles'])) {
            return;
        }
        
        echo '<style>';
        foreach ($options['menu_toggles'] as $menu_slug => $is_visible) {
            // wpca-toggle-slider 开关直接控制菜单项显示/隐藏
            if (!$is_visible) {
                echo "#toplevel_page_{$menu_slug}, #menu-{$menu_slug} { display: none !important; }";
            }
        }
        echo '</style>';
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