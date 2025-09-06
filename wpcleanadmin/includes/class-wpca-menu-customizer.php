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

        // Get saved menu settings
        $menu_settings = get_option($this->menu_settings_key, []);
        
        // Apply menu customizations
        if (!empty($menu_settings['order'])) {
            add_filter('custom_menu_order', [$this, 'reorder_admin_menu']);
            add_filter('menu_order', [$this, 'reorder_admin_menu']);
        }
        
        if (!empty($menu_settings['hidden_items'])) {
            add_action('admin_head', [$this, 'hide_menu_items']);
        }
    }
    
    /**
     * Reorder admin menu items
     */
    public function reorder_admin_menu($menu_order) {
        $menu_settings = get_option($this->menu_settings_key, []);
        if (empty($menu_settings['order'])) {
            return $menu_order;
        }
        
        $new_order = [];
        foreach ($menu_settings['order'] as $menu_slug) {
            if (isset($GLOBALS['menu'][$menu_slug])) {
                $new_order[] = $menu_slug;
            }
        }
        
        return $new_order;
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