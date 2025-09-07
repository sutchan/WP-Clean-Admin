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
     * Reorder admin menu items
     */
    public function reorder_admin_menu($menu_order) {
        global $menu;
        
        // Get settings from wpca_settings option
        $options = get_option('wpca_settings', []);
        $custom_order = isset($options['menu_order']) ? $options['menu_order'] : [];
        
        if (empty($custom_order) || empty($menu)) {
            return $menu_order;
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
        
        return null;
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
        $menu_settings = get_option($this->menu_settings_key, []);
        if (empty($menu_settings['hidden_items'])) {
            return;
        }
        
        echo '<style>';
        foreach ($menu_settings['hidden_items'] as $menu_slug) {
            echo "#toplevel_page_{$menu_slug}, #menu-{$menu_slug} { display: none !important; }";
        }
        echo '</style>';
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