<?php
/**
 * WP Clean Admin - Menu Customizer
 * 
 * @package WPCleanAdmin
 * @subpackage MenuCustomizer
 * @since 1.0.0
 * @version 1.7.11
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
        // AJAX钩子已移至WPCA_Ajax类统一管理，避免重复注册
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
        $user = wp_get_current_user();
        
        // Administrators always have permissions
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check custom permissions
        if (isset($user->allcaps['wpca_customize_admin']) && $user->allcaps['wpca_customize_admin']) {
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
            $this->options_cache = get_option(WPCA_SETTINGS_KEY, array());
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
        return update_option('wpca_settings', $this->options_cache);
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
        // Log to WordPress error log
        error_log(sprintf('[WPCA Menu Customizer] %s: %s', $code, $message));
        
        // Only log to debug.log in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(print_r($context, true));
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
        
        // Only log errors in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
            error_log('WP Clean Admin - Menu Customizer Error (' . $code . '): ' . $message);
            
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && isset($context['trace'])) {
                error_log('WP Clean Admin - Stack Trace: ' . $context['trace']);
            }
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
        // 已委托给WPCA_Menu_Manager处理，此方法保留以保持兼容性
        // Menu_Manager会通过自己注册的hooks处理菜单隐藏
    }
    
    // Menu item show/hide related methods have been moved to WPCA_Menu_Manager class
    // The following methods are no longer needed but kept for compatibility
    
    /**
     * Handle AJAX menu toggle
     * 
     * 委托给Menu_Manager处理菜单显示/隐藏操作
     */
    public function handle_ajax_toggle_menu() {
        try {
            // Validate request
            $this->validate_ajax_request('wpca_admin_nonce');
            
            // Get and validate parameters
            $slug = isset($_POST['slug']) ? (function_exists('sanitize_text_field') ? sanitize_text_field($_POST['slug']) : filter_var($_POST['slug'], FILTER_SANITIZE_STRING)) : '';
            $state = isset($_POST['state']) ? intval($_POST['state']) : 0;
            
            // 委托给菜单管理器处理
            if ($this->menu_manager) {
                $result = $this->menu_manager->toggle_menu_item($slug, $state);
                
                if ($result['success']) {
                    // Send success response
                    if (function_exists('wp_send_json_success')) {
                        wp_send_json_success($result);
                    } else {
                        echo json_encode(array('success' => true, 'data' => $result['data']));
                        wp_die();
                    }
                } else {
                    throw new Exception($result['message'], $result['code']);
                }
            } else {
                // Fallback processing, keeping original logic for compatibility
                // Enhanced input validation
                if (empty($slug)) {
                    throw new Exception(__('Invalid menu slug', 'wp-clean-admin'), 400);
                }
                
                // Validate slug format (prevent injection attacks)
                if (!preg_match('/^[a-zA-Z0-9_\-|\.]+$/', $slug)) {
                    throw new Exception(__('Invalid menu slug format', 'wp-clean-admin'), 'invalid_slug_format');
                }
                
                // Validate state is boolean (0 or 1)
                if (!in_array($state, array(0, 1))) {
                    throw new Exception(__('Invalid menu state', 'wp-clean-admin'), 'invalid_state');
                }
                
                // Get current options
                $options = $this->get_plugin_options();
                if (!is_array($options)) {
                    throw new Exception(__('Failed to retrieve settings', 'wp-clean-admin'), 'settings_retrieval_failed');
                }
                
                // Ensure menu_toggles array exists
                if (!isset($options['menu_toggles']) || !is_array($options['menu_toggles'])) {
                    $options['menu_toggles'] = array();
                }
                
                // Check if this menu can be hidden (if toggling to hidden state)
                if ($state === 0) {
                    $slug_parts = explode('|', $slug);
                    $main_slug = $slug_parts[0]; // Get the main slug part
                    if (in_array($main_slug, self::PROTECTED_MENUS)) {
                        throw new Exception(__('This menu item cannot be hidden', 'wp-clean-admin'), 'menu_protected');
                    }
                }
                
                // Update the toggle state
                $options['menu_toggles'][$slug] = $state;
                
                // Save updated options with error handling
                $updated = function_exists('update_option') ? update_option('wpca_settings', $options) : false;
                
                if (!$updated) {
                    throw new Exception(__('Failed to save menu settings', 'wp-clean-admin'), 500);
                }
                
                // Clear all caches
                $this->clear_menu_cache();
                $this->options_cache = null;
                
                // Log successful update if in debug mode
                if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                    error_log('WP Clean Admin - Menu item "' . $slug . '" visibility toggled to ' . ($state ? 'visible' : 'hidden'));
                }
                
                // Send success response
                if (function_exists('wp_send_json_success')) {
                    wp_send_json_success(array(
                        'message' => __('Menu settings updated successfully', 'wp-clean-admin'),
                        'data' => array(
                            'slug' => $slug,
                            'state' => $state
                        )
                    ));
                } else {
                echo wp_json_encode(array('success' => true, 'data' => array('slug' => $slug, 'state' => $state)));
                wp_die();
            }
            
        } catch (Exception $e) {
            $code = method_exists($e, 'getCode') && $e->getCode() ? $e->getCode() : 'ajax_toggle_menu_failed';
            
            // Log error with context
            $this->log_error($code, $e, array(
                'menu_slug' => isset($_POST['slug']) ? (function_exists('sanitize_text_field') ? sanitize_text_field($_POST['slug']) : 'unknown') : 'unknown',
                'state' => isset($_POST['state']) ? intval($_POST['state']) : null
            ));
            
            // Send error response with standardized format
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'code' => $code,
                    'message' => $e->getMessage()
                ), $e->getCode() ?: 400);
            } else {
                echo json_encode(array(
                    'error' => $e->getMessage(),
                    'code' => $code
                ));
                wp_die();
            }
        }
    }

    /**
     * Handle AJAX reset menu
     */
    public function handle_ajax_reset_menu() {
        try {
            $this->validate_ajax_request('wpca_admin_nonce');
            
            $reset_types = $this->get_reset_types_from_request();
            $valid_types = array('order', 'toggles');
            $reset_types = (function_exists('array_intersect')) ? array_intersect($reset_types, $valid_types) : array_filter($reset_types, function($type) use ($valid_types) { return in_array($type, $valid_types); });
            
            if (empty($reset_types)) {
                throw new Exception(__('Please select at least one valid setting type to reset', 'wp-clean-admin'), 400);
            }
            
            $options = $this->get_plugin_options();
            $options = $this->reset_settings_by_types($options, $reset_types);

            $updated = function_exists('update_option') ? update_option('wpca_settings', $options) : false;
            
            if (!$updated) {
                throw new Exception(__('Failed to reset settings', 'wp-clean-admin'), 500);
            }
            
            $this->clear_menu_cache();
            
            if (function_exists('wp_send_json_success')) {
                wp_send_json_success(array(
                    'message' => __('Menu settings reset successfully', 'wp-clean-admin'),
                    'data' => array(
                        'reset_types' => $reset_types,
                        'new_settings' => $options
                    )
                ));
            } else {
                echo json_encode(array('success' => true, 'data' => array('reset_types' => $reset_types, 'new_settings' => $options)));
                wp_die();
            }
        } catch (Exception $e) {
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error($e->getMessage(), $e->getCode() ?: 400);
            } else {
                echo json_encode(array('error' => $e->getMessage()));
                wp_die();
            }
        }
    }
    
    /**
     * Get reset types from request
     * 
     * @return array Reset types
     */
    private function get_reset_types_from_request() {
        $reset_types = array();
        if (isset($_POST['reset_types'])) {
            $reset_types = is_array($_POST['reset_types']) ? $_POST['reset_types'] : array($_POST['reset_types']);
            // Check if sanitize_key function exists
            if (function_exists('sanitize_key')) {
                $reset_types = array_map('sanitize_key', $reset_types);
            } else {
                // Define safe text sanitization function
                $safe_sanitize_text_field = function_exists('sanitize_text_field') ? 'sanitize_text_field' : function($text) {
                    return filter_var($text, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
                };
                
                $reset_types = array_map(function($type) use ($safe_sanitize_text_field) {
                    return $safe_sanitize_text_field(strtolower($type));
                }, $reset_types);
            }
        } else {
            $reset_types = array('order', 'toggles');
        }
        return $reset_types;
    }
    
    /**
     * Reset settings by types
     * 
     * @param array $options Options
     * @param array $reset_types Reset types
     * @return array Updated options
     */
    private function reset_settings_by_types($options, $reset_types) {
        if (in_array('order', $reset_types)) {
            $options['menu_order'] = array();
            $options['submenu_order'] = array();
        }
        
        if (in_array('toggles', $reset_types)) {
            // Get all menu items for validation
            $all_items = $this->get_all_menu_items(true); // Force refresh
            
            // Only keep settings for menu items that actually exist
            $options['menu_toggles'] = array_intersect_key(
                $options['menu_toggles'] ?? array(),
                $all_items
            );
        }
        
        return $options;
    }
    
    /**
     * Handle AJAX reset menu order
     */
    public function handle_ajax_reset_menu_order() {
        try {
            $this->validate_ajax_request('wpca_admin_nonce');
            
            $options = $this->get_plugin_options();
            $options['menu_order'] = array();
            $options['submenu_order'] = array();

            $updated = function_exists('update_option') ? update_option('wpca_settings', $options) : false;
            
            if (!$updated) {
                throw new Exception(__('Failed to reset menu order', 'wp-clean-admin'), 500);
            }
            
            $this->clear_menu_cache();
            
            if (function_exists('wp_send_json_success')) {
                wp_send_json_success(array(
                    'message' => __('Menu order has been reset to default', 'wp-clean-admin')
                ));
            } else {
                echo json_encode(array('success' => true, 'message' => __('Menu order has been reset to default', 'wp-clean-admin')));
                wp_die();
            }
            
        } catch (Exception $e) {
            $this->log_error('ajax_reset_menu_order_failed', $e);
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => $e->getMessage(),
                    'code' => $e->getCode() ?: 500
                ), $e->getCode() ?: 500);
            } else {
                echo json_encode(array('error' => $e->getMessage(), 'code' => $e->getCode() ?: 500));
                wp_die();
            }
        }
    }
    
    /**
     * Load menu scripts and styles
     */
    public function load_menu_scripts() {
        // Only load on admin pages
        if (!is_admin() || !function_exists('wp_enqueue_script') || !function_exists('wp_enqueue_style')) {
            return;
        }

        // Get current screen to determine if we should load scripts
        $current_screen = get_current_screen();
        
        // Only load on specific admin pages
        if ($current_screen && isset($current_screen->id) && (strpos($current_screen->id, 'settings_page_wp-clean-admin') !== false || $current_screen->id === 'index.php')) {
            // Register and enqueue scripts
            if (function_exists('wp_register_script')) {
                wp_register_script(
                    'wpca-menu-customizer',
                    WPCA_PLUGIN_URL . 'assets/js/wpca-menu.js',
                    array('jquery', 'jquery-ui-sortable', 'wpca-core'),
                    WPCA_VERSION,
                    true
                );
            }
            
            // Localize script with menu data only if script is enqueued
            if (function_exists('wp_localize_script')) {
                $ajaxUrl = function_exists('admin_url') ? admin_url('admin-ajax.php') : site_url('/wp-admin/admin-ajax.php');
                $nonce = function_exists('wp_create_nonce') ? wp_create_nonce('wpca_admin_nonce') : '';
                
                wp_localize_script('wpca-menu-customizer', 'wpcaMenuData', array(
                    'ajaxUrl' => $ajaxUrl,
                    'nonce' => $nonce,
                    'i18n' => array(
                        'confirmReset' => __('Are you sure you want to reset all menu settings?', 'wp-clean-admin'),
                        'success' => __('Settings updated successfully', 'wp-clean-admin'),
                        'error' => __('An error occurred', 'wp-clean-admin')
                    )
                ));
            }
            
            // Enqueue the script
            wp_enqueue_script('wpca-menu-customizer');
            
            // Enqueue styles
            wp_enqueue_style(
                'wpca-menu-customizer',
                WPCA_PLUGIN_URL . 'assets/css/wpca-admin.css',
                array(),
                WPCA_VERSION
            );
        }
    }
    
    /**
     * Validate AJAX request
     * 
     * @param string $action The action name to validate
     * @return bool True if the request is valid, false otherwise
     */
    private function validate_ajax_request($action) {
        // Check if this is an AJAX request
        if (!function_exists('wp_doing_ajax') || !wp_doing_ajax()) {
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array('message' => 'Invalid request'), 400);
            }
            return false;
        }

        // Verify nonce
        if (!isset($_POST['nonce']) || !function_exists('wp_verify_nonce') || !wp_verify_nonce($_POST['nonce'], 'wpca_admin_nonce')) {
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array('message' => 'Invalid nonce'), 403);
            }
            return false;
        }

        // Check user capabilities
        if (!function_exists('current_user_can') || !current_user_can('manage_options')) {
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array('message' => 'Insufficient permissions'), 403);
            }
            return false;
        }

        return true;
    }
}
?>