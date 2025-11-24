<?php
/**
 * WP Clean Admin - Menu Customizer
 * 
 * @package WPCleanAdmin
 * @subpackage MenuCustomizer
 * @since 1.0.0
 * @version 1.7.13
 * @file wpcleanadmin/includes/class-wpca-menu-customizer.php
 * @updated 2025-06-18
 */

defined('ABSPATH') || exit;

// Ensure we're in WordPress context
if (!defined('WPINC')) {
    die('This file must be loaded within WordPress environment');
}

// Ensure required WordPress functions are available
if (!function_exists('add_action')) {
if (defined('ABSPATH')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
}

// Include menu manager class
if (!class_exists('WPCA_Menu_Manager') && defined('WPCA_PLUGIN_DIR')) {
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-menu-manager.php';
}

/**
 * Class WPCA_Menu_Customizer
 *
 * Handles WordPress admin menu customization functionality
 */
class WPCA_Menu_Customizer {
    /**
     * Protected menus that cannot be hidden for security reasons
     * 
     * @var array
     */
    const PROTECTED_MENUS = array(
        'index.php',      // Dashboard
        'users.php',      // Users
        'profile.php',    // Profile
        'plugins.php',    // Plugins
        'options-general.php' // Settings
    );
    
    /**
     * Menu manager instance
     * 
     * @var WPCA_Menu_Manager|null
     */
    private $menu_manager = null;
    
    /**
     * Cache for menu items to improve performance
     * 
     * @var array|null
     */
    private $menu_items_cache = null;
    
    /**
     * Cache for plugin options
     * 
     * @var array|null
     */
    private $options_cache = null;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize menu manager if available
        if (class_exists('WPCA_Menu_Manager')) {
            $this->menu_manager = WPCA_Menu_Manager::get_instance();
        }
        
        if (function_exists('add_action')) {
            add_action('admin_menu', array($this, 'initialize_menu_customization'));
            
            // Add error logging hook
            add_action('wpca_menu_customization_error', array($this, 'log_customization_error'), 10, 3);
        }
        // AJAX 钩子已移至 WPCA_Ajax 类统一管理，避免重复注册
    }
    
    /**
     * Initialize menu customization
     */
    public function initialize_menu_customization() {
        try {
            // Extended permission check, supporting non-admin users
            if (!$this->current_user_has_menu_permissions()) {
                return;
            }

            // Get saved menu settings
            $options = $this->get_plugin_options();
            
            // Validate options format
            if (!is_array($options)) {
                $this->reset_plugin_options();
                $this->log_error(
                    'invalid_options_format', 
                    __('Menu settings format is invalid, has been reset', 'wp-clean-admin'), 
                    array('action' => 'options_reset')
                );
            }
            
            // Menu ordering functionality
            if (!empty($options['menu_order']) && is_array($options['menu_order'])) {
                add_filter('custom_menu_order', '__return_true');
                add_filter('menu_order', array($this, 'handle_admin_menu_reordering'));
            }
            
            // Initialize menu item display control
            add_action('admin_head', array($this, 'handle_menu_item_visibility'));
            
            // Only load scripts for users with full management permissions
            if (current_user_can('manage_options')) {
                add_action('admin_enqueue_scripts', array($this, 'load_menu_scripts'));
            }
        } catch (Exception $e) {
            // Catch and log all exceptions
            $this->log_error('initialization_failed', $e);
        }
    }
    
    /**
     * Check if current user has menu customization permissions
     * 
     * @return bool True if user has permissions
     */
    private function current_user_has_menu_permissions() {
        // Directly check user permissions to avoid recursive calls
        $user = null;
        if (function_exists('wp_get_current_user')) {
            $user = wp_get_current_user();
        }
        
        // Administrators always have permissions
        if (function_exists('current_user_can') && current_user_can('manage_options')) {
            return true;
        }
        
        // Check custom permissions
        if ($user && is_object($user) && isset($user->allcaps) && is_array($user->allcaps) && isset($user->allcaps['wpca_customize_admin']) && $user->allcaps['wpca_customize_admin']) {
            return true;
        }
        
        // Check higher-level permissions
        if (isset($user->allcaps['wpca_manage_all']) && $user->allcaps['wpca_manage_all']) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get plugin options with caching
     * 
     * @return array Plugin options
     */
    private function get_plugin_options() {
        if ($this->options_cache === null) {
            if (!defined('WPCA_SETTINGS_KEY')) {
                define('WPCA_SETTINGS_KEY', 'wpca_settings');
            }
            $this->options_cache = function_exists('get_option') ? get_option(WPCA_SETTINGS_KEY, array()) : array();
        }
        return $this->options_cache;
    }
    
    /**
     * Reset plugin options to default
     * 
     * @return bool True if options were reset successfully
     */
    private function reset_plugin_options() {
        $this->options_cache = array();
        return function_exists('update_option') ? update_option('wpca_settings', $this->options_cache) : false;
    }
    
    /**
     * Handle admin menu reordering
     * 
     * @param array $menu_order Original menu order
     * @return array Reordered menu
     */
    public function handle_admin_menu_reordering($menu_order) {
        try {
            $options = $this->get_plugin_options();
            
            if ($this->should_skip_reordering($options)) {
                return $menu_order;
            }

            global $menu, $submenu;
            if (empty($menu) || !is_array($menu)) {
                return $menu_order;
            }

            // Create original menu copy
            $original_menu = $menu;
            $original_submenu = $submenu;

            // Process submenu ordering first
            $this->process_submenu_ordering($submenu, $options['submenu_order'] ?? array());

            // Process main menu ordering
            return $this->process_main_menu_ordering(
                $original_menu, 
                $options['menu_order'] ?? array(), 
                $menu_order
            );
        } catch (Exception $e) {
            $this->log_error('reorder_failed', $e);
            return $menu_order;
        }
    }
    
    /**
     * Check if menu reordering should be skipped
     * 
     * @param array $options Plugin options
     * @return bool True if reordering should be skipped
     */
    private function should_skip_reordering($options) {
        $custom_order = $options['menu_order'] ?? array();
        $submenu_order = $options['submenu_order'] ?? array();
        
        return empty($custom_order) && empty($submenu_order);
    }
    
    /**
     * Process submenu ordering
     * 
     * @param array $submenu Submenu array reference
     * @param array $submenu_order Submenu order configuration
     */
    private function process_submenu_ordering(&$submenu, $submenu_order) {
        try {
            if (empty($submenu) || empty($submenu_order) || !is_array($submenu_order)) {
                return;
            }
            
            foreach ($submenu_order as $parent_slug => $ordered_slugs) {
                if (empty($submenu[$parent_slug]) || empty($ordered_slugs) || !is_array($ordered_slugs)) {
                    continue;
                }

                $this->reorder_specific_submenu($submenu[$parent_slug], $ordered_slugs);
            }
        } catch (Exception $e) {
            $this->log_error('submenu_reorder_failed', $e);
        }
    }
    
    /**
     * Reorder a specific submenu
     * 
     * @param array $submenu_items Submenu items reference
     * @param array $ordered_slugs Ordered slugs
     */
    private function reorder_specific_submenu(&$submenu_items, $ordered_slugs) {
        $original = $submenu_items;
        $ordered = array();
        $remaining = $original;
        $seen_slugs = array();

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

        // Merge and remove duplicates
        $merged = array_unique(array_merge($ordered, $remaining), SORT_REGULAR);
        $submenu_items = array_values($merged);
    }
    
    /**
     * Process main menu ordering
     * 
     * @param array $menu Original menu
     * @param array $custom_order Custom order
     * @param array $original_order Original order
     * @return array New menu order
     */
    private function process_main_menu_ordering($menu, $custom_order, $original_order) {
        $slug_to_order_map = $this->build_slug_mapping($menu);
        $new_order = $this->build_new_order($custom_order, $slug_to_order_map, $original_order);
        
        // Remove duplicates
        return $this->remove_duplicate_menu_items($new_order);
    }
    
    /**
     * Remove duplicate menu items
     * 
     * @param array $menu_items Menu items
     * @return array Deduplicated menu items
     */
    private function remove_duplicate_menu_items($menu_items) {
        $seen = array();
        $filtered_order = array();
        
        foreach ($menu_items as $item) {
            if (!isset($seen[$item])) {
                $seen[$item] = true;
                $filtered_order[] = $item;
            }
        }
        
        return $filtered_order;
    }

    /**
     * Build slug mapping from menu items
     * 
     * @param array $menu Menu array
     * @return array Slug to ID mapping
     */
    private function build_slug_mapping($menu) {
        $mapping = array();
        try {
            foreach ($menu as $item) {
                if (!empty($item[2])) {
                    $slug = $this->get_menu_slug_from_item($item[2]);
                    if ($slug) {
                        $mapping[$slug] = $item[2];
                    }
                }
            }
        } catch (Exception $e) {
            $this->log_error('slug_mapping_failed', $e);
        }
        return $mapping;
    }

    /**
     * Build new menu order based on custom order and existing items
     * 
     * @param array $custom_order Custom order
     * @param array $slug_mapping Slug mapping
     * @param array $original_order Original order
     * @return array New menu order
     */
    private function build_new_order($custom_order, $slug_mapping, $original_order) {
        $new_order = array();
        $processed = array();

        try {
            // First add items from custom order
            if (is_array($custom_order)) {
                foreach ($custom_order as $slug) {
                    if (isset($slug_mapping[$slug])) {
                        $new_order[] = $slug_mapping[$slug];
                        $processed[$slug_mapping[$slug]] = true;
                    }
                }
            }

            // Add remaining items preserving original order
            if (is_array($original_order)) {
                foreach ($original_order as $item) {
                    if (!isset($processed[$item])) {
                        $new_order[] = $item;
                    }
                }
            }
        } catch (Exception $e) {
            $this->log_error('new_order_build_failed', $e);
            return $original_order;
        }

        return $new_order;
    }
    
    /**
     * Get standardized menu slug from menu item
     * 
     * @param string $menu_item Menu item path
     * @return string Standardized slug
     */
    private function get_menu_slug_from_item($menu_item) {
        try {
            // Safety check
            if (!is_string($menu_item)) {
                return '';
            }
            
            // Define safe key sanitization function
                $safe_sanitize_key = function_exists('sanitize_key') ? 'sanitize_key' : function($key) {
                // Simple key sanitization implementation
                $key = strtolower($key);
                $key = preg_replace('/[^a-z0-9_\-]/', '', $key);
                return $key;
            };
            
            // Core menu mapping
            $menu_map = array(
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
            );
            
            // Handle menu items with query parameters
            if (strpos($menu_item, '?') !== false) {
                $query = parse_url($menu_item, PHP_URL_QUERY);
                parse_str($query, $params);
                if (isset($params['page'])) {
                    return $safe_sanitize_key($params['page']);
                }
                return $safe_sanitize_key(strtok($menu_item, '?')); // Return part before question mark
            }
            
            // Handle core menu items
            if (isset($menu_map[$menu_item])) {
                return $menu_map[$menu_item];
            }
            
            // Handle third-party plugin menus
            return $safe_sanitize_key($menu_item);
        } catch (Exception $e) {
            $this->log_error('slug_extraction_failed', $e);
            return '';
        }
    }
    
    /**
     * Log customization error
     * 
     * @param string $code Error code
     * @param string $message Error message
     * @param array $context Error context
     */
    public function log_customization_error($code, $message, $context = array()) {
        if (class_exists('WPCA_Helpers')) {
            // 添加错误代码到上下文
            $context['error_code'] = $code;
            
            // 记录详细错误日志
            WPCA_Helpers::log(
                $message,
                $context,
                'error',
                (defined('WP_DEBUG') && WP_DEBUG)
            );
        }
    }
    
    /**
     * Log an error with WordPress error logger
     * 
     * @param string $code Error code
     * @param Exception|string $error Exception object or error message
     * @param array $context Additional context
     */
    private function log_error($code, $error, $context = array()) {
        // Get the message
        $message = is_object($error) && method_exists($error, 'getMessage') ? $error->getMessage() : (string)$error;
        
        // Add trace to context if available
        if (is_object($error) && method_exists($error, 'getTraceAsString')) {
            $context['trace'] = $error->getTraceAsString();
        }
        
        // 添加错误代码到上下文
        $context['error_code'] = $code;
        
        // 使用新的日志记录方法
        if (class_exists('WPCA_Helpers')) {
            WPCA_Helpers::log(
                $message,
                $context,
                'error',
                (defined('WP_DEBUG') && WP_DEBUG)
            );
        }
        
        // Also trigger the error action hook for external handling
        if (function_exists('do_action')) {
            do_action('wpca_menu_customization_error', $code, $message, $context);
        }
    }
    
    /**
     * Get all admin menu items (top level and submenus)
     * 
     * @param bool $force_refresh Force refresh cache
     * @return array All menu items
     */
    public function get_all_menu_items($force_refresh = false) {
        // Return cached items if available
        if ($this->menu_items_cache !== null && !$force_refresh) {
            return $this->menu_items_cache;
        }
        
        global $menu, $submenu;
        $menu_items = array();
        
        try {
            // Get top level menus
            if (!empty($menu) && is_array($menu)) {
                $menu_items = array_merge($menu_items, $this->extract_top_level_menus($menu));
            }
            
            // Get submenus
            if (!empty($submenu) && is_array($submenu)) {
                $menu_items = array_merge($menu_items, $this->extract_submenus($submenu));
            }
        } catch (Exception $e) {
            $this->log_error('menu_items_fetch_failed', $e);
        }
        
        // Cache the result
        $this->menu_items_cache = $menu_items;
        
        return $menu_items;
    }
    
    /**
     * Extract top level menus
     * 
     * @param array $menu Menu array
     * @return array Top level menu items
     */
    private function extract_top_level_menus($menu) {
        $menu_items = array();
        
        // Define safe tag stripping function
        $safe_wp_strip_all_tags = function_exists('wp_strip_all_tags') ? 'wp_strip_all_tags' : function($text) {
            return strip_tags($text);
        };
        
        foreach ($menu as $item) {
            if (is_array($item) && isset($item[2])) {
                $slug = $item[2];
                $menu_items[$slug] = array(
                    'title' => isset($item[0]) ? $safe_wp_strip_all_tags($item[0]) : $slug,
                    'type' => 'top',
                    'parent' => '',
                    'url' => isset($item[2]) ? $item[2] : '',
                    'id' => isset($item[5]) ? $item[5] : '',
                    'icon' => isset($item[6]) ? $item[6] : '',
                );
            }
        }
        
        return $menu_items;
    }
    
    /**
     * Extract submenus
     * 
     * @param array $submenu Submenu array
     * @return array Submenu items
     */
    private function extract_submenus($submenu) {
        $menu_items = array();
        
        // Define safe tag stripping function
        $safe_wp_strip_all_tags = function_exists('wp_strip_all_tags') ? 'wp_strip_all_tags' : function($text) {
            return strip_tags($text);
        };
        
        foreach ($submenu as $parent_slug => $sub_items) {
            foreach ($sub_items as $sub_item) {
                if (is_array($sub_item) && isset($sub_item[2])) {
                    $full_slug = $parent_slug . '|' . $sub_item[2];
                    $menu_items[$full_slug] = array(
                        'title' => isset($sub_item[0]) ? $safe_wp_strip_all_tags($sub_item[0]) : $sub_item[2],
                        'type' => 'sub',
                        'parent' => $parent_slug,
                        'url' => isset($sub_item[2]) ? $sub_item[2] : '',
                        'id' => '',
                        'icon' => '',
                    );
                }
            }
        }
        
        return $menu_items;
    }
    
    /**
     * Clear menu items cache
     */
    public function clear_menu_cache() {
        $this->menu_items_cache = null;
        $this->options_cache = null;
    }
    
    /**
     * Handle menu item visibility
     * 
     * Delegated to Menu_Manager for menu hiding functionality
     */
    public function handle_menu_item_visibility() {
        // 已委托给 WPCA_Menu_Manager 处理，此方法保留以保持兼容性
        // Menu_Manager 会通过自己注册的 hooks 处理菜单隐藏
    }
    
    // Menu item show/hide related methods have been moved to WPCA_Menu_Manager class
    // The following methods are no longer needed but kept for compatibility
    
    /**
     * Handle AJAX menu toggle
     * 
     * 委托给 Menu_Manager 处理菜单显示/隐藏操作
     */
    public function handle_ajax_toggle_menu() {
        try {
            // Validate request
            $this->validate_ajax_request('wpca_admin_nonce');
            
            // Get and validate parameters