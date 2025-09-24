<?php
/**
 * WP Clean Admin - Menu Customizer
 * 
 * @package WPCleanAdmin
 * @subpackage MenuCustomizer
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

// Ensure we're in WordPress context
if (!defined('WPINC')) {
    die('This file must be loaded within WordPress environment');
}

// Ensure required WordPress functions are available
if (!function_exists('add_action')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
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
        add_action('admin_menu', array($this, 'initialize_menu_customization'));
        add_action('wp_ajax_wpca_toggle_menu', array($this, 'handle_ajax_toggle_menu'));
        add_action('wp_ajax_wpca_reset_menu', array($this, 'handle_ajax_reset_menu'));
        add_action('wp_ajax_wpca_reset_menu_order', array($this, 'handle_ajax_reset_menu_order'));
        
        // Add error logging hook
        add_action('wpca_menu_customization_error', array($this, 'log_customization_error'), 10, 3);
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
                do_action(
                    'wpca_menu_customization_error', 
                    'invalid_options_format', 
                    __('Menu settings format is invalid, has been reset', 'wp-clean-admin'), 
                    array()
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
        // 直接检查用户权限，避免递归调用
        $user = wp_get_current_user();
        
        // 管理员始终有权限
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // 检查自定义权限
        if (isset($user->allcaps['wpca_customize_admin']) && $user->allcaps['wpca_customize_admin']) {
            return true;
        }
        
        // 检查更高级权限
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
                    return sanitize_key($params['page']);
                }
                return sanitize_key(strtok($menu_item, '?')); // Return part before question mark
            }
            
            // Handle core menu items
            if (isset($menu_map[$menu_item])) {
                return $menu_map[$menu_item];
            }
            
            // Handle third-party plugin menus
            return sanitize_key($menu_item);
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
     * Log error from exception
     * 
     * @param string $code Error code
     * @param Exception $exception Exception object
     * @param array $context Additional context
     */
    private function log_error($code, $exception, $context = array()) {
        $context['trace'] = $exception->getTraceAsString();
        $this->log_customization_error($code, $exception->getMessage(), $context);
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
        
        foreach ($menu as $item) {
            if (is_array($item) && isset($item[2])) {
                $slug = $item[2];
                $menu_items[$slug] = array(
                    'title' => isset($item[0]) ? wp_strip_all_tags($item[0]) : $slug,
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
        
        foreach ($submenu as $parent_slug => $sub_items) {
            foreach ($sub_items as $sub_item) {
                if (is_array($sub_item) && isset($sub_item[2])) {
                    $full_slug = $parent_slug . '|' . $sub_item[2];
                    $menu_items[$full_slug] = array(
                        'title' => isset($sub_item[0]) ? wp_strip_all_tags($sub_item[0]) : $sub_item[2],
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
     */
    public function handle_menu_item_visibility() {
        try {
            $options = $this->get_plugin_options();
            $all_items = $this->get_all_menu_items();
            $hidden_items = $this->get_hidden_menu_items($options, $all_items);

            if (empty($hidden_items)) {
                return;
            }

            // Generate CSS for hiding menu items
            $css = $this->generate_menu_hide_css($hidden_items);
            
            // Output CSS safely
            $this->output_menu_css($css);
        } catch (Exception $e) {
            $this->log_error('menu_visibility_failed', $e);
        }
    }
    
    /**
     * Get hidden menu items
     * 
     * @param array $options Plugin options
     * @param array $all_items All menu items
     * @return array Hidden menu items
     */
    private function get_hidden_menu_items($options, $all_items) {
        $hidden_items = array();
        
        if (!isset($options['menu_toggles']) || !is_array($options['menu_toggles'])) {
            return $hidden_items;
        }
        
        foreach ($options['menu_toggles'] as $slug => $state) {
            if ($state === 0 && isset($all_items[$slug]) && !in_array($slug, self::PROTECTED_MENUS)) {
                $hidden_items[] = $slug;
            }
        }
        
        return $hidden_items;
    }
    
    /**
     * Generate menu hide CSS
     * 
     * @param array $hidden_items Hidden menu items
     * @return string CSS
     */
    private function generate_menu_hide_css($hidden_items) {
        try {
            if (empty($hidden_items)) {
                return '';
            }
            
            $css = $this->get_base_hide_css();
            $selectors = $this->generate_menu_selectors($hidden_items);
            
            if (!empty($selectors)) {
                $css .= "\n" . implode(",\n", $selectors) . $this->get_hide_css_rules();
            }
            
            return $css;
        } catch (Exception $e) {
            $this->log_error('css_generation_failed', $e);
            return '';
        }
    }
    
    /**
     * Get base hide CSS
     * 
     * @return string Base CSS
     */
    private function get_base_hide_css() {
        return 'li.wpca-hidden-menu, 
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
    }
    
    /**
     * Get hide CSS rules
     * 
     * @return string CSS rules
     */
    private function get_hide_css_rules() {
        return ' { 
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }';
    }
    
    /**
     * Generate menu selectors
     * 
     * @param array $hidden_items Hidden menu items
     * @return array CSS selectors
     */
    private function generate_menu_selectors($hidden_items) {
        $selectors = array();
        
        foreach ($hidden_items as $slug) {
            // Handle top-level menus
            if (strpos($slug, '|') === false) {
                $selectors[] = "#adminmenu li.menu-top.toplevel_page_" . esc_attr($slug);
                $selectors[] = "#adminmenu li.menu-top.menu-icon-" . esc_attr($slug);
                $selectors[] = "#adminmenu li.menu-top#menu-" . esc_attr(str_replace('.php', '', $slug));
                
                // Direct match for core menus
                if (strpos($slug, '.php') !== false) {
                    $selectors[] = "#adminmenu li.menu-top#" . esc_attr(str_replace('.php', '', $slug));
                }
            } 
            // Handle submenus
            else {
                list($parent, $child) = explode('|', $slug);
                $selectors[] = "#adminmenu li.menu-top.toplevel_page_" . esc_attr($parent) . " .wp-submenu li a[href$='" . esc_attr($child) . "']";
                $selectors[] = "#adminmenu li.menu-top.menu-icon-" . esc_attr($parent) . " .wp-submenu li a[href$='" . esc_attr($child) . "']";
                $selectors[] = "#adminmenu li.menu-top#menu-" . esc_attr(str_replace('.php', '', $parent)) . " .wp-submenu li a[href$='" . esc_attr($child) . "']";
                
                // Direct match for core submenus
                if (strpos($parent, '.php') !== false) {
                    $selectors[] = "#adminmenu li.menu-top#" . esc_attr(str_replace('.php', '', $parent)) . " .wp-submenu li a[href$='" . esc_attr($child) . "']";
                }
            }
        }
        
        return $selectors;
    }
    
    /**
     * Output menu CSS
     * 
     * @param string $css CSS to output
     */
    private function output_menu_css($css) {
        if (empty($css)) {
            return;
        }
        
        echo '<style type="text/css" id="wpca-menu-customizer-css">' . "\n";
        echo "/* WP Clean Admin - Menu Customizer CSS */\n";
        echo esc_html($css);
        echo "\n</style>\n";
    }
    
    /**
     * Load menu scripts
     * 
     * @param string $hook Current admin page hook
     */

    
    /**
     * Validate AJAX request permissions
     * 
     * @param string $nonce_action Nonce action name
     * @param string $permission Permission name
     * @throws Exception If validation fails
     */
    private function validate_ajax_request($nonce_action = 'wpca_menu_toggle', $permission = 'manage_options') {
        if (!wp_doing_ajax()) {
            throw new Exception(__('Invalid AJAX request', 'wp-clean-admin'), 400);
        }
        
        if (!check_ajax_referer($nonce_action, 'nonce', false)) {
            throw new Exception(__('Invalid security nonce', 'wp-clean-admin'), 403);
        }
        
        if (!current_user_can($permission) && !current_user_can('wpca_manage_all')) {
            throw new Exception(__('You do not have sufficient permissions', 'wp-clean-admin'), 403);
        }
    }
    
    /**
     * Ensure menu toggles array exists in options
     * 
     * @param array &$options Options array reference
     */
    private function ensure_menu_toggles_exist(&$options) {
        if (!is_array($options)) {
            $options = array();
        }
        if (!isset($options['menu_toggles']) || !is_array($options['menu_toggles'])) {
            $options['menu_toggles'] = array();
        }
    }
    
    /**
     * Validate menu item exists
     * 
     * @param array $all_items All menu items
     * @param string $standard_slug Standard slug
     * @param string $original_slug Original slug
     * @return string Valid slug to use
     * @throws Exception If menu item doesn't exist
     */
    private function validate_menu_item_exists($all_items, $standard_slug, $original_slug) {
        if (!isset($all_items[$standard_slug]) && !isset($all_items[$original_slug])) {
            throw new Exception(
                sprintf(__('Menu item "%s" does not exist', 'wp-clean-admin'), $original_slug),
                400
            );
        }
        
        // Use standard slug format if it exists, otherwise use original
        $save_slug = isset($all_items[$standard_slug]) ? $standard_slug : $original_slug;

        // Final validation
        if (!isset($all_items[$save_slug])) {
            throw new Exception(
                sprintf(__('Menu item "%s" does not exist', 'wp-clean-admin'), $save_slug),
                400
            );
        }
        
        return $save_slug;
    }
    
    /**
     * Clean invalid menu toggles
     * 
     * @param array $menu_toggles Menu toggles
     * @param array $all_items All menu items
     * @return array Cleaned menu toggles
     */
    private function clean_invalid_menu_toggles($menu_toggles, $all_items) {
        return array_filter(
            $menu_toggles,
            function ($key) use ($all_items) {
                return isset($all_items[$key]);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
    
    /**
     * Handle AJAX reset menu
     */
    public function handle_ajax_reset_menu() {
        try {
            $this->validate_ajax_request('wpca_menu_toggle', 'manage_options');
            
            $reset_types = $this->get_reset_types_from_request();
            $valid_types = array('order', 'toggles');
            $reset_types = array_intersect($reset_types, $valid_types);
            
            if (empty($reset_types)) {
                throw new Exception(__('Please select at least one valid setting type to reset', 'wp-clean-admin'), 400);
            }
            
            $options = $this->get_plugin_options();
            $options = $this->reset_settings_by_types($options, $reset_types);

            $updated = update_option('wpca_settings', $options);
            
            if (!$updated) {
                throw new Exception(__('Failed to reset settings', 'wp-clean-admin'), 500);
            }
            
            $this->clear_menu_cache();
            
            wp_send_json_success(array(
                'message' => __('Menu settings reset successfully', 'wp-clean-admin'),
                'data' => array(
                    'reset_types' => $reset_types,
                    'new_settings' => $options
                )
            ));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage(), $e->getCode() ?: 400);
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
            $reset_types = array_map('sanitize_key', $reset_types);
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
            $this->validate_ajax_request('wpca_menu_toggle', 'manage_options');
            
            $options = $this->get_plugin_options();
            $options['menu_order'] = array();
            $options['submenu_order'] = array();

            $updated = update_option('wpca_settings', $options);
            
            if (!$updated) {
                throw new Exception(__('Failed to reset menu order', 'wp-clean-admin'), 500);
            }
            
            $this->clear_menu_cache();
            
            wp_send_json_success(array(
                'message' => __('Menu order has been reset to default', 'wp-clean-admin')
            ));
            
        } catch (Exception $e) {
            $this->log_error('ajax_reset_menu_order_failed', $e);
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Load menu scripts
     * 
     * @param string $hook Current admin page hook
     */
    public function load_menu_scripts($hook) {
        try {
            // Only load on settings page - using correct page slug
            if (strpos($hook, 'settings_page_wp_clean_admin') === false) {
                return;
            }
            
            // Register and enqueue scripts
            wp_register_script(
                'wpca-menu-customizer',
                WPCA_PLUGIN_URL . 'assets/js/menu-management.js',
                array('jquery', 'jquery-ui-sortable', 'wpca-core'),
                WPCA_VERSION,
                true
            );
            
            // Localize script with menu data
            wp_localize_script('wpca-menu-customizer', 'wpcaMenuData', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wpca_menu_toggle'),
                'i18n' => array(
                    'confirmReset' => __('Are you sure you want to reset all menu settings?', 'wp-clean-admin'),
                    'success' => __('Settings updated successfully', 'wp-clean-admin'),
                    'error' => __('An error occurred', 'wp-clean-admin')
                )
            ));
            
            wp_enqueue_script('wpca-menu-customizer');
            
            // Enqueue styles
            wp_enqueue_style(
                'wpca-menu-customizer',
                WPCA_PLUGIN_URL . 'assets/css/wpca-admin.css',
                array(),
                WPCA_VERSION
            );
        } catch (Exception $e) {
            error_log('[WPCA] Menu script loading error: ' . $e->getMessage());
        }
    }
}