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
    private $menu_settings_key = 'wpca_menu_settings';
    
    public function __construct() {
        add_action('admin_menu', [$this, 'init_menu_customization']);
        add_action('admin_init', [$this, 'register_menu_settings']);
        
        // Add AJAX handlers
        add_action('wp_ajax_wpca_toggle_menu_item', [$this, 'ajax_toggle_menu_item']);
        add_action('wp_ajax_wpca_get_menu_state', [$this, 'ajax_get_menu_state']);
        add_action('wp_ajax_wpca_toggle_submenu', [$this, 'ajax_toggle_submenu']);
        add_action('wp_ajax_wpca_save_menu_order', [$this, 'ajax_save_menu_order']);
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
        
        // Apply menu customizations
        if (!empty($options['menu_order'])) {
            add_filter('custom_menu_order', '__return_true'); // Enable custom menu order
            add_filter('menu_order', [$this, 'reorder_admin_menu']);
        }
        
        // For backward compatibility, also check the old settings format
        $menu_settings = get_option($this->menu_settings_key, []);
        if (!empty($menu_settings['hidden_items'])) {
            add_action('admin_head', [$this, 'hide_menu_items']);
        }
        
        // Add JavaScript for drag and drop functionality
        add_action('admin_enqueue_scripts', [$this, 'enqueue_menu_scripts']);
    }
    
    /**
     * Reorder admin menu items (both top-level and submenus)
     */
    public function reorder_admin_menu($menu_order) {
        global $menu, $submenu;
        
        // Get settings from wpca_settings option
        $options = get_option('wpca_settings', []);
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
                            // Skip hidden menu items
                            if (isset($options['menu_hidden_items']) && 
                                in_array($parent_slug.'|'.$sub_slug, $options['menu_hidden_items'])) {
                                unset($original_submenu[$index]);
                                continue;
                            }
                            
                            if ($sub_item[2] === $sub_slug) {
                                $new_submenu[] = $sub_item;
                                unset($original_submenu[$index]);
                                break;
                            }
                        }
                    }
                    
                    // Add any remaining items (excluding hidden ones)
                    foreach ($original_submenu as $index => $sub_item) {
                        if (isset($options['menu_hidden_items']) && 
                            in_array($parent_slug.'|'.$sub_item[2], $options['menu_hidden_items'])) {
                            continue;
                        }
                        $new_submenu[] = $sub_item;
                    }
                    
                    $submenu[$parent_slug] = $new_submenu;
                }
            }
        }
        
        // Debug - log information for troubleshooting
        error_log('WP Clean Admin - Custom Menu Order: ' . print_r($custom_order, true));
        error_log('WP Clean Admin - Original Menu Order: ' . print_r($menu_order, true));
        error_log('WP Clean Admin - Global Menu: ' . print_r($menu, true));
        
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
        
        error_log('WP Clean Admin - Slug to Order Map: ' . print_r($slug_to_order_map, true));
        
        // Create new order based on saved settings
        $new_order = [];
        foreach ($custom_order as $menu_slug) {
            // Skip hidden menu items
            if (isset($options['menu_hidden_items']) && in_array($menu_slug, $options['menu_hidden_items'])) {
                continue;
            }
            
            if (isset($slug_to_order_map[$menu_slug])) {
                $new_order[] = $slug_to_order_map[$menu_slug];
            }
        }
        
        // Add any menu items that weren't in the saved order (excluding hidden ones)
        foreach ($menu_order as $item) {
            $menu_slug = $this->get_menu_slug_from_item($item);
            if (isset($options['menu_hidden_items']) && in_array($menu_slug, $options['menu_hidden_items'])) {
                continue;
            }
            
            if (!in_array($item, $new_order)) {
                $new_order[] = $item;
            }
        }
        
        error_log('WP Clean Admin - New Menu Order: ' . print_r($new_order, true));
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
                            'parent' => '',
                            'icon' => isset($item[6]) ? $item[6] : 'dashicons-admin-generic'
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
                            'parent' => $parent_slug,
                            'icon' => 'dashicons-arrow-right'
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
        wp_enqueue_script('wpca-menu-customizer', WPCA_PLUGIN_URL . 'assets/js/wpca-menu-customizer.js', 
                         array('jquery', 'jquery-ui-sortable'), WPCA_VERSION, true);
        
        // Pass menu data to JavaScript
        $menu_items = $this->get_all_menu_items();
        $options = get_option('wpca_settings', []);
        $submenu_states = isset($options['submenu_states']) ? $options['submenu_states'] : [];
        
        wp_localize_script('wpca-menu-customizer', 'wpcaMenuData', array(
            'menuItems' => $menu_items,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpca_menu_order_nonce'),
            'locale' => get_locale(),
            'submenuStates' => $submenu_states
        ));
    }
    
    /**
     * Hide menu items via CSS
     */
    public function hide_menu_items() {
        // Get settings from wpca_settings option
        $options = get_option('wpca_settings', []);
        if (empty($options['menu_hidden_items'])) {
            return;
        }
        
        echo '<style>';
        foreach ($options['menu_hidden_items'] as $menu_slug) {
            echo "#toplevel_page_{$menu_slug}, #menu-{$menu_slug} { display: none !important; }";
        }
        echo '</style>';
    }
    
    /**
     * AJAX handler to toggle menu item visibility
     */
    public function ajax_toggle_menu_item() {
        check_ajax_referer('wpca_menu_order_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $menu_slug = isset($_POST['menu_slug']) ? sanitize_text_field($_POST['menu_slug']) : '';
        $hidden = isset($_POST['hidden']) ? (bool)$_POST['hidden'] : false;
        
        if (empty($menu_slug)) {
            wp_send_json_error(['message' => 'Invalid menu slug']);
        }
        
        // Get current settings
        $options = get_option('wpca_settings', []);
        $hidden_items = isset($options['menu_hidden_items']) ? $options['menu_hidden_items'] : [];
        
        // Update hidden items array
        if ($hidden) {
            if (!in_array($menu_slug, $hidden_items)) {
                $hidden_items[] = $menu_slug;
            }
        } else {
            $hidden_items = array_diff($hidden_items, [$menu_slug]);
        }
        
        // Save updated settings
        $options['menu_hidden_items'] = array_values($hidden_items);
        
        // Ensure menu order is updated when items are hidden/shown
        if (isset($options['menu_order'])) {
            if ($hidden) {
                // Remove from menu order if hidden
                $options['menu_order'] = array_diff($options['menu_order'], [$menu_slug]);
            } else {
                // Add back to menu order if shown (at the end)
                if (!in_array($menu_slug, $options['menu_order'])) {
                    $options['menu_order'][] = $menu_slug;
                }
            }
        }
        
        update_option('wpca_settings', $options);
        
        // Force refresh of admin menu
        global $menu, $submenu;
        $menu = $submenu = array();
        
        // Return localized status text
        $status_text = '';
        if ($hidden) {
            $status_text = function_exists('get_locale') && get_locale() === 'zh_CN' ? 
                '(已隐藏)' : 
                '(Hidden)';
        }
            
        wp_send_json_success([
            'menu_slug' => $menu_slug,
            'hidden' => $hidden,
            'hidden_items' => $hidden_items,
            'status_text' => $status_text,
            'refreshed' => true
        ]);
    }
    
    /**
     * AJAX handler to get current menu state
     */
    public function ajax_get_menu_state() {
        check_ajax_referer('wpca_menu_order_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $options = get_option('wpca_settings', []);
        $hidden_items = isset($options['menu_hidden_items']) ? $options['menu_hidden_items'] : [];
        
        wp_send_json_success([
            'hidden_items' => $hidden_items
        ]);
    }
    
    /**
     * AJAX handler to toggle submenu visibility
     */
    public function ajax_toggle_submenu() {
        check_ajax_referer('wpca_menu_order_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $menu_slug = isset($_POST['menu_slug']) ? sanitize_text_field($_POST['menu_slug']) : '';
        $expanded = isset($_POST['expanded']) ? (bool)$_POST['expanded'] : false;
        
        if (empty($menu_slug)) {
            wp_send_json_error(['message' => 'Invalid menu slug']);
        }
        
        // Get current settings
        $options = get_option('wpca_settings', []);
        $submenu_states = isset($options['submenu_states']) ? $options['submenu_states'] : [];
        
        // Update submenu state
        $submenu_states[$menu_slug] = $expanded;
        
        // Save updated settings
        $options['submenu_states'] = $submenu_states;
        update_option('wpca_settings', $options);
        
        wp_send_json_success([
            'menu_slug' => $menu_slug,
            'expanded' => $expanded
        ]);
    }

    /**
     * AJAX handler to save menu order
     */
    public function ajax_save_menu_order() {
        check_ajax_referer('wpca_menu_order_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $menu_order = isset($_POST['menu_order']) ? json_decode(stripslashes($_POST['menu_order']), true) : [];
        $submenu_order = isset($_POST['submenu_order']) ? json_decode(stripslashes($_POST['submenu_order']), true) : [];
        
        if (empty($menu_order)) {
            wp_send_json_error(['message' => 'Invalid menu order data']);
        }
        
        // Get current settings
        $options = get_option('wpca_settings', []);
        
        // Update menu order settings
        $options['menu_order'] = $menu_order;
        $options['submenu_order'] = $submenu_order;
        
        // Save updated settings
        update_option('wpca_settings', $options);
        
        wp_send_json_success([
            'message' => 'Menu order saved successfully',
            'menu_order' => $menu_order,
            'submenu_order' => $submenu_order
        ]);
    }
    
    /**
     * Register menu settings
     */
    public function register_menu_settings() {
        register_setting(
            'wpca_menu_group',
            $this->menu_settings_key,
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_menu_settings'],
                'default' => []
            ]
        );
    }
    
    /**
     * Sanitize menu settings
     */
    public function sanitize_menu_settings($input) {
        $output = [];
        
        if (!empty($input['order'])) {
            $output['order'] = array_map('sanitize_text_field', $input['order']);
        }
        
        if (!empty($input['hidden_items'])) {
            $output['hidden_items'] = array_map('sanitize_text_field', $input['hidden_items']);
        }
        
        return $output;
    }
}