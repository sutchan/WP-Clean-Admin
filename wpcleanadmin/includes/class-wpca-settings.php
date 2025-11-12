<?php
/**
 * WP Clean Admin Settings Class
 *
 * Handles the plugin settings page and manages all settings-related functionality.
 *
 * @package WP_Clean_Admin
 * @since 1.0.0
 * @version 1.7.11
 */

// defined是PHP语言结构，不需要function_exists检查
if (! defined( 'ABSPATH' ) ) {
    // exit是PHP语言结构，不需要function_exists检查
    exit;
}

/**
 * Class WPCA_Settings
 * Handles the plugin settings page.
 */
class WPCA_Settings {

    /**
     * Stores the plugin options.
     * @var array
     */
    private $options;
    
    /**
     * Cache for plugin options.
     * @var array|null
     */
    private static $cached_options = null;

    /**
     * WPCA_Settings constructor.
     */
    public function __construct() {
        if (function_exists('add_action')) {
            add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
            add_action( 'admin_init', array( $this, 'settings_init' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
            add_action( 'wp_ajax_wpca_save_tab_preference', array( $this, 'ajax_save_tab_preference' ) );
            add_action( 'wp_ajax_wpca_reset_tab_settings', array( $this, 'ajax_reset_settings' ) );
            add_action( 'wp_ajax_wpca_reset_menu_order', array( $this, 'ajax_reset_menu_order' ) );
        }
    }
    
    /**
     * Validate AJAX request with nonce and permission checks
     * @param string $action Optional. Action name
     * @return bool True if validation passed, false otherwise
     */
    private function validate_ajax_request($action = 'wpca_settings-options') {
        // 安全检查函数列表
        if (!function_exists('wp_send_json_error')) {
            return false;
        }
        
        // Check if it's an AJAX request
        if (function_exists('wp_doing_ajax') && !wp_doing_ajax()) {
            wp_send_json_error(array(
                'message' => __('Invalid request', 'wp-clean-admin')
            ), 400);
            return false;
        }

        // Validate nonce
        // isset是PHP语言结构，不需要function_exists检查
        if (!isset($_POST['nonce']) || function_exists('wp_verify_nonce') && !wp_verify_nonce($_POST['nonce'], 'wpca_admin_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security verification failed', 'wp-clean-admin')
            ), 403);
            return false;
        }

        // Check permissions
        if (!function_exists('current_user_can') || !current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('Insufficient permissions', 'wp-clean-admin')
            ), 403);
            return false;
        }
        
        // 注意：不再使用array_map直接过滤整个$_POST数组，这会破坏嵌套数组结构
        // 数据应该在各个处理函数中单独进行验证和清理
        
        return true;
    }

    /**
     * AJAX handler to save tab preference and menu state
     */
    public function ajax_save_tab_preference() {
        // 验证请求
        if (!$this->validate_ajax_request('wpca_admin_nonce')) {
            return;
        }
        
        // 安全函数检查
        if (!function_exists('sanitize_text_field') || !function_exists('wp_send_json_error') || !function_exists('wp_send_json_success')) {
            return;
        }
        
        // Get and validate data with strict type checking
        $allowed_tabs = ['tab-general', 'tab-visual-style', 'tab-menu', 'tab-login', 'tab-about'];
        $tab = 'tab-general'; // 默认值
        
        // isset是PHP语言结构，不需要function_exists检查
        // isset和is_string是PHP语言结构/内置函数，不需要function_exists检查
        if (isset($_POST['tab']) && is_string($_POST['tab']) && function_exists('in_array') && in_array($_POST['tab'], $allowed_tabs)) {
            $tab = sanitize_text_field($_POST['tab']);
        }
        
        // 获取现有选项
        $options = self::get_options();
        
        // 严格验证数据结构和内容
        $has_valid_data = false;
        
        // 安全地处理菜单顺序数据
        if (isset($_POST['menu_order']) && is_array($_POST['menu_order'])) {
            $safe_menu_order = array();
            foreach ($_POST['menu_order'] as $menu_item) {
                if (is_string($menu_item) && !empty($menu_item)) {
                    // 只允许字母、数字、短横线、下划线
                    if (preg_match('/^[a-zA-Z0-9_-]+$/', $menu_item)) {
                        $safe_menu_order[] = sanitize_text_field($menu_item);
                    }
                }
            }
            if (!empty($safe_menu_order)) {
                $options['menu_order'] = $safe_menu_order;
                $has_valid_data = true;
            }
        }
        
        // 安全地处理菜单开关数据
        if (isset($_POST['menu_toggles']) && is_array($_POST['menu_toggles'])) {
            $safe_menu_toggles = array();
            foreach ($_POST['menu_toggles'] as $key => $value) {
                // 验证键名格式
                if (is_string($key) && preg_match('/^[a-zA-Z0-9_-]+$/', $key)) {
                    $safe_key = sanitize_text_field($key);
                    // 确保值是布尔类型
                    $safe_menu_toggles[$safe_key] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                }
            }
            if (!empty($safe_menu_toggles)) {
                $options['menu_toggles'] = $safe_menu_toggles;
                $has_valid_data = true;
            }
        }
        
        // 总是更新当前选项卡
        $options['current_tab'] = $tab;
        
        // 如果没有有效数据，返回错误
        if (!$has_valid_data && isset($_POST['menu_order']) && isset($_POST['menu_toggles'])) {
            wp_send_json_error(array(
                'message' => __('Invalid data structure or content', 'wp-clean-admin')
            ), 400);
            return;
        }
        
        // 保存选项并响应
        if (function_exists('update_option')) {
            $update_result = update_option('wpca_settings', $options);
            
            if ($update_result) {
                self::$cached_options = null;
                wp_send_json_success(array(
                    'message' => __('Settings saved successfully', 'wp-clean-admin'),
                    'data' => array(
                        'menu_order' => $options['menu_order'] ?? array(),
                        'menu_toggles' => $options['menu_toggles'] ?? array()
                    )
                ));
            } else if (function_exists('wp_send_json_error')) {
                if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                    error_log('WP Clean Admin: Failed to save settings. Options: ' . print_r($options, true));
                }
                
                wp_send_json_error(array(
                    'message' => __('Failed to save settings', 'wp-clean-admin'),
                    'debug_info' => defined('WP_DEBUG') && WP_DEBUG ? 'Option update failed for wpca_settings' : null
                ), 500);
            }
        } else {
            // 如果不支持update_option，返回错误
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => __('Failed to save settings', 'wp-clean-admin')
                ), 500);
            }
        }
    }
    
    /**
     * Update options from POST data
     *
     * @param array &$options Reference to the options array
     * @param string $tab Current tab
     */
    private function update_options_from_post(&$options, $tab) {
        // 定义安全的文本清理函数
        $safe_sanitize_text_field = function_exists('sanitize_text_field') ? 'sanitize_text_field' : function($text) {
            return filter_var($text, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        };
        
        $options['current_tab'] = $safe_sanitize_text_field($tab);
        
        // Update menu order if provided
        if (isset($_POST['menu_order']) && is_array($_POST['menu_order'])) {
            // Validate each menu item against a whitelist of allowed menu slugs if possible
            if (class_exists('WPCA_Menu_Customizer')) {
                $menu_customizer = new WPCA_Menu_Customizer();
                $all_menu_items = $menu_customizer->get_all_menu_items();
                $allowed_slugs = array_keys($all_menu_items);
                
                $options['menu_order'] = array_filter(
                    array_map($safe_sanitize_text_field, $_POST['menu_order']),
                    function($slug) use ($allowed_slugs) {
                        return in_array($slug, $allowed_slugs);
                    }
                );
            } else {
                // Fallback if menu customizer class is not available
                $options['menu_order'] = array_filter(array_map($safe_sanitize_text_field, $_POST['menu_order']));
            }
        }
        
        // Update menu toggles if provided
        if (isset($_POST['menu_toggles']) && is_array($_POST['menu_toggles'])) {
            if (!isset($options['menu_toggles'])) {
                $options['menu_toggles'] = array();
            }
            
            foreach ($_POST['menu_toggles'] as $slug => $value) {
                $sanitized_slug = $safe_sanitize_text_field($slug);
                // Ensure toggle value is either 0 or 1
                $options['menu_toggles'][$sanitized_slug] = (int)($value ? 1 : 0);
            }
        }
        
        // Update main menu toggle state if provided
        if (isset($_POST['menu_toggle'])) {
            $options['menu_toggle'] = (int)($_POST['menu_toggle'] ? 1 : 0);
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts() {
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            $hook = $screen->id;
            
            // Only load scripts on the settings page
            if (strpos($hook, 'wp-clean-admin') === false) {
                return;
            }
            
            // Enqueue jQuery UI for sortable functionality
            if (function_exists('wp_script_is') && function_exists('wp_enqueue_script') && !wp_script_is('jquery-ui-sortable', 'enqueued')) {
                wp_enqueue_script('jquery-ui-sortable');
            }
            
            // Enqueue CSS files for the settings page
            if (function_exists('wp_enqueue_style')) {
                wp_enqueue_style( 'wpca-admin-style', WPCA_PLUGIN_URL . 'assets/css/wpca-admin.css', array(), WPCA_VERSION );
                wp_enqueue_style( 'wpca-settings-style', WPCA_PLUGIN_URL . 'assets/css/wpca-settings.css', array(), WPCA_VERSION );
                wp_enqueue_style( 'wpca-login-styles', WPCA_PLUGIN_URL . 'assets/css/wpca-login-styles.css', array(), WPCA_VERSION ); // Enqueue login styles here
            }
            
            // Enqueue module scripts in correct order - main first, then tabs, then settings
            // wpca-main.js is already loaded in wpca_load_admin_resources()
            if (function_exists('wp_enqueue_script')) {
                wp_enqueue_script( 'wpca-tabs', WPCA_PLUGIN_URL . 'assets/js/wpca-tabs.js', array('jquery', 'wpca-main'), WPCA_VERSION, true );
                wp_enqueue_script( 'wpca-permissions', WPCA_PLUGIN_URL . 'assets/js/wpca-permissions.js', array('jquery', 'wpca-main'), WPCA_VERSION, true );
                wp_enqueue_script( 'wpca-menu', WPCA_PLUGIN_URL . 'assets/js/wpca-menu.js', array('jquery', 'jquery-ui-sortable', 'wpca-main', 'wpca-permissions'), WPCA_VERSION, true );
                wp_enqueue_script( 'wpca-login', WPCA_PLUGIN_URL . 'assets/js/wpca-login.js', array('jquery', 'wpca-main'), WPCA_VERSION, true );
                wp_enqueue_script( 'wpca-reset', WPCA_PLUGIN_URL . 'assets/js/wpca-reset.js', array('jquery', 'wpca-main'), WPCA_VERSION, true );
                wp_enqueue_script( 'wpca-settings', WPCA_PLUGIN_URL . 'assets/js/wpca-settings.js', array(
                    'jquery', 
                    'jquery-ui-sortable',
                    'wpca-main',
                    'wpca-tabs',
                    'wpca-menu',
                    'wpca-login',
                    'wpca-reset'
                ), WPCA_VERSION, true );
            }
            
            // Localize scripts - add settings-specific data
            if (function_exists('wp_localize_script') && function_exists('admin_url') && function_exists('wp_create_nonce')) {
                wp_localize_script( 'wpca-main', 'wpca_admin', array_merge(
                    $GLOBALS['wpca_admin_data'] ?? array(),
                    array(
                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                        'nonce' => wp_create_nonce('wpca_ajax_request'),
                        'current_tab' => self::get_options()['current_tab'] ?? 'tab-general',
                        'reset_confirm' => __('Are you sure you want to reset all menu settings to default?', 'wp-clean-admin'),
                        'general_reset_confirm' => __('Are you sure you want to reset all general settings to default?', 'wp-clean-admin'),
                        'visual_reset_confirm' => __('Are you sure you want to reset all visual style settings to default?', 'wp-clean-admin'),
                        'login_reset_confirm' => __('Are you sure you want to reset all login page settings to default?', 'wp-clean-admin'),
                        'resetting_text' => __('Resetting...', 'wp-clean-admin'),
                        'reset_text' => __('Reset Defaults', 'wp-clean-admin'),
                        'reset_failed' => __('Reset failed. Please try again.', 'wp-clean-admin'),
                        'reset_successful_text' => __('successful', 'wp-clean-admin'),
                        'media_title' => __('Select or Upload Media', 'wp-clean-admin'),
                        'media_button' => __('Use this media', 'wp-clean-admin'),
                        'debug' => defined('WP_DEBUG') && WP_DEBUG,
                        // Error messages for AJAX requests
                        'error_request_processing_failed' => __('Request processing failed', 'wp-clean-admin'),
                        'error_insufficient_permissions' => __('You do not have permission to perform this action', 'wp-clean-admin'),
                        'error_invalid_parameters' => __('Invalid request parameters', 'wp-clean-admin'),
                        'error_not_logged_in' => __('Please log in first', 'wp-clean-admin'),
                        'error_server_error' => __('Internal server error', 'wp-clean-admin')
                    )
                ));
                
                // Localize login frontend configuration
                wp_localize_script( 'wpca-login', 'wpca_login_frontend', array(
                    'custom_styles' => '',
                    'auto_hide_form' => false,
                    'auto_hide_delay' => 3000,
                    'debug' => defined('WP_DEBUG') && WP_DEBUG
                ));
            }
        }
    }

    /**
     * Get plugin options, merged with defaults.
     *
     * @return array
     */
    public static function get_options() {
        if (self::$cached_options === null) {
            if (function_exists('wp_parse_args') && function_exists('get_option')) {
                self::$cached_options = wp_parse_args( get_option( 'wpca_settings', array() ), self::get_default_settings() );
            } else {
                self::$cached_options = self::get_default_settings();
            }
        }
        return self::$cached_options;
    }

    /**
     * Get default plugin settings.
     *
     * @return array
     */
    public static function get_default_settings() {
        return [
            // General settings
            'current_tab' => 'tab-general',
            'hide_wordpress_title' => 0,
            'hide_wpfooter' => 0,
            'hide_frontend_adminbar' => 0,
            'hide_dashboard_widgets' => [],
            
            // Menu settings
            'menu_toggle' => 1,
            'menu_visibility' => [],
            'hide_admin_menu_items' => [],
            'menu_order' => [],
            'submenu_order' => [],
            'menu_toggles' => [], // Ensure this is initialized
            
            // Visual style
            'theme_style' => 'default',
            'layout_density' => 'standard',
            'border_radius_style' => 'small',
            'shadow_style' => 'subtle',
            'primary_color' => '#4A90E2',
            'background_color' => '#F8F9FA',
            'text_color' => '#2D3748',
            'font_stack' => 'system',
            'font_size_base' => 'medium',
            'icon_style' => 'dashicons',
            
            // Admin bar
            'hide_admin_bar_items' => [],
            
            // Login page
            'login_elements' => [
                'language_switcher' => 1,
                'home_link' => 1,
                'register_link' => 1,
                'remember_me' => 1
            ],
            'login_style' => 'default',
            'login_logo' => '',
            'login_background' => '',
            'login_custom_css' => ''
        ];
    }

    /**
     * Add options page to the admin menu.
     */
    public function add_admin_menu() {
        // Check if the current user has the necessary permissions
        if (function_exists('add_options_page')) {
            $capability = (class_exists('WPCA_Permissions') && defined('WPCA_Permissions::CAP_VIEW_SETTINGS')) ? 
                          WPCA_Permissions::CAP_VIEW_SETTINGS : 'manage_options';
                           
            add_options_page(
                __( 'WP Clean Admin Settings', 'wp-clean-admin' ), // Page title
                __( 'WP Clean Admin', 'wp-clean-admin' ),         // Menu title
                $capability,                                      // Capability
                'wp-clean-admin',                                  // Menu slug - Changed from wp_clean_admin to wp-clean-admin to match URL
                array( $this, 'options_page' )                     // Function
            );
        }
    }

    /**
     * Initialize settings.
     */
    public function settings_init() {
        // 为所有WordPress设置函数添加存在性检查
        if (function_exists('register_setting')) {
            register_setting( 
                'wpca_settings', 
                'wpca_settings',
                array(
                    'sanitize_callback' => array($this, 'sanitize_settings')
                )
            );
        }

        // Main section
        if (function_exists('add_settings_section')) {
            add_settings_section(
                'wpca_settings_general_section',
                function_exists('__') ? __( 'General Settings', 'wp-clean-admin' ) : 'General Settings',
                array( $this, 'settings_section_callback' ),
                'wpca_settings'
            );
        }

        // Example setting field: Hide Dashboard Widgets
        if (function_exists('add_settings_field')) {
            add_settings_field(
                'wpca_hide_dashboard_widgets',
                function_exists('__') ? __( 'Hide Dashboard Widgets', 'wp-clean-admin' ) : 'Hide Dashboard Widgets',
                array( $this, 'hide_dashboard_widgets_render' ),
                'wpca_settings',
                'wpca_settings_general_section'
            );

            // Add Hide WordPress Title field
            add_settings_field(
                'wpca_hide_wordpress_title',
                function_exists('__') ? __( 'Hide WordPress Title', 'wp-clean-admin' ) : 'Hide WordPress Title',
                array( $this, 'hide_wordpress_title_render' ),
                'wpca_settings',
                'wpca_settings_general_section'
            );

            // Add Hide WordPress Footer field
            add_settings_field(
                'wpca_hide_wpfooter',
                function_exists('__') ? __( 'Hide WordPress Footer', 'wp-clean-admin' ) : 'Hide WordPress Footer',
                array( $this, 'hide_wpfooter_render' ),
                'wpca_settings',
                'wpca_settings_general_section'
            );

            // Add Hide Frontend Admin Bar field
            add_settings_field(
                'wpca_hide_frontend_adminbar',
                function_exists('__') ? __( 'Hide Frontend Admin Bar', 'wp-clean-admin' ) : 'Hide Frontend Admin Bar',
                array( $this, 'hide_frontend_adminbar_render' ),
                'wpca_settings',
                'wpca_settings_general_section'
            );

            // Admin Bar Customization field (moved to General section)
            add_settings_field(
                'wpca_hide_admin_bar_items',
                function_exists('__') ? __( 'Hide Admin Bar Items', 'wp-clean-admin' ) : 'Hide Admin Bar Items',
                array( $this, 'hide_admin_bar_items_render' ),
                'wpca_settings',
                'wpca_settings_general_section'
            );
        }

        // Visual Style section
        if (function_exists('add_settings_section')) {
            add_settings_section(
                'wpca_settings_visual_style_section',
                function_exists('__') ? __( 'Visual Style', 'wp-clean-admin' ) : 'Visual Style',
                array( $this, 'visual_style_section_callback' ),
                'wpca_settings_visual_style'
            );
        }

        // Visual Style fields
        if (function_exists('add_settings_field')) {
            add_settings_field(
                'wpca_theme_style',
                function_exists('__') ? __( 'Theme Style', 'wp-clean-admin' ) : 'Theme Style',
                array( $this, 'theme_style_render' ),
                'wpca_settings_visual_style',
                'wpca_settings_visual_style_section'
            );

            add_settings_field(
                'wpca_primary_color',
                function_exists('__') ? __( 'Primary Color', 'wp-clean-admin' ) : 'Primary Color',
                array( $this, 'primary_color_render' ),
                'wpca_settings_visual_style',
                'wpca_settings_visual_style_section'
            );

            add_settings_field(
                'wpca_background_color',
                function_exists('__') ? __( 'Background Color', 'wp-clean-admin' ) : 'Background Color',
                array( $this, 'background_color_render' ),
                'wpca_settings_visual_style',
                'wpca_settings_visual_style_section'
            );

            add_settings_field(
                'wpca_text_color',
                function_exists('__') ? __( 'Text Color', 'wp-clean-admin' ) : 'Text Color',
                array( $this, 'text_color_render' ),
                'wpca_settings_visual_style',
                'wpca_settings_visual_style_section'
            );

            add_settings_field(
                'wpca_shadow_style',
                function_exists('__') ? __( 'Shadow Style', 'wp-clean-admin' ) : 'Shadow Style',
                array( $this, 'shadow_style_render' ),
                'wpca_settings_visual_style',
                'wpca_settings_visual_style_section'
            );
            
            // Layout & Typography fields (moved to Visual Style)
            add_settings_field(
                'wpca_layout_density',
                function_exists('__') ? __( 'Layout Density', 'wp-clean-admin' ) : 'Layout Density',
                array( $this, 'layout_density_render' ),
                'wpca_settings_visual_style',
                'wpca_settings_visual_style_section'
            );

            add_settings_field(
                'wpca_border_radius_style',
                function_exists('__') ? __( 'Border Radius', 'wp-clean-admin' ) : 'Border Radius',
                array( $this, 'border_radius_style_render' ),
                'wpca_settings_visual_style',
                'wpca_settings_visual_style_section'
            );

            add_settings_field(
                'wpca_font_stack',
                function_exists('__') ? __( 'Font Stack', 'wp-clean-admin' ) : 'Font Stack',
                array( $this, 'font_stack_render' ),
                'wpca_settings_visual_style',
                'wpca_settings_visual_style_section'
            );

            add_settings_field(
                'wpca_font_size_base',
                function_exists('__') ? __( 'Font Size', 'wp-clean-admin' ) : 'Font Size',
                array( $this, 'font_size_base_render' ),
                'wpca_settings_visual_style',
                'wpca_settings_visual_style_section'
            );

            add_settings_field(
                'wpca_icon_style',
                function_exists('__') ? __( 'Icon Style', 'wp-clean-admin' ) : 'Icon Style',
                array( $this, 'icon_style_render' ),
                'wpca_settings_visual_style',
                'wpca_settings_visual_style_section'
            );

            // Menu Customization fields
            add_settings_field(
                'wpca_menu_order',
                function_exists('__') ? __( 'Menu Order', 'wp-clean-admin' ) : 'Menu Order', // This is just the label for the field
                array( $this, 'menu_order_render' ), // This render function will only output the sortable list and toggles
                'wpca_settings_menu',
                'wpca_settings_menu_section'
            );

            // Login Elements field (Moved from render_login_tab)
            add_settings_field(
                'wpca_login_elements',
                function_exists('__') ? __( 'Show/Hide Elements', 'wp-clean-admin' ) : 'Show/Hide Elements',
                array( $this, 'login_elements_render' ),
                'wpca_settings_login', // Use 'wpca_settings_login' as the page slug for this field
                'wpca_settings_login_elements_section'
            );
        }

        // Menu Customization section
        if (function_exists('add_settings_section')) {
            add_settings_section(
                'wpca_settings_menu_section',
                function_exists('__') ? __( 'Menu Customization', 'wp-clean-admin' ) : 'Menu Customization',
                array( $this, 'menu_section_callback' ), // This callback will now contain the header and reset button
                'wpca_settings_menu'
            );

            // Login Page Elements section (Moved from render_login_tab)
            add_settings_section(
                'wpca_settings_login_elements_section',
                function_exists('__') ? __( 'Login Page Elements', 'wp-clean-admin' ) : 'Login Page Elements',
                array( $this, 'login_elements_section_callback' ),
                'wpca_settings_login' // Use 'wpca_settings_login' as the page slug for this section
            );

            // About section
            add_settings_section(
                'wpca_settings_about_section',
                function_exists('__') ? __( 'About WP Clean Admin', 'wp-clean-admin' ) : 'About WP Clean Admin',
                array( $this, 'about_section_callback' ),
                'wpca_settings_about'
            );
        }
    }

    /**
     * Settings section callback.
     */
    public function settings_section_callback() {
        echo __( 'Configure the appearance and behavior of your admin dashboard.', 'wp-clean-admin' );
    }

    /**
     * Visual Style section callback.
     */
    public function visual_style_section_callback() {
        echo __( 'Customize the overall visual theme, colors, layout and typography.', 'wp-clean-admin' );
    }

    /**
     * Menu section callback.
     */
    public function menu_section_callback() {
        // Moved header, description from menu_order_render here
        echo '<div class="wpca-menu-order-header">';
        echo '<h3>';
        echo '<span class="dashicons dashicons-menu"></span>';
        echo __('Menu Order Customization', 'wp-clean-admin');
        echo '</h3>';
        echo '<p class="description">';
        echo __('Drag and drop menu items to reorder them. Use the toggle switches to show/hide items.', 'wp-clean-admin');
        echo '</p>';
        echo '</div>';
        echo '<p class="description">' . __( 'Select which admin menu items to hide and reorder them.', 'wp-clean-admin' ) . '</p>';
        // Reset button moved to bottom of tab content
    }

    /**
     * Render Menu Order field
     */
    public function menu_order_render() {
        $options = self::get_options(); // Use self::get_options() to retrieve options
        $menu_order = $options['menu_order'] ?? array();
        $submenu_order = $options['submenu_order'] ?? array();
        
        // Ensure menu_toggle is always set
        $options['menu_toggle'] = isset($options['menu_toggle']) ? (int)$options['menu_toggle'] : 1;
        
        echo '<div class="wpca-menu-toggle-wrapper">';
        echo '<label>';
        echo '<input type="checkbox" id="wpca-menu-toggle" name="wpca_settings[menu_toggle]" value="1" ' . checked( $options['menu_toggle'], 1, false ) . '>';
        echo __('Enable Menu Customization', 'wp-clean-admin');
        echo '</label>';
        echo '<p class="description">' . __('Toggle to show/hide and reorder admin menu items', 'wp-clean-admin') . '</p>';
        echo '</div>';
        
        echo '<ul id="wpca-menu-order-list" class="wpca-menu-sortable" style="' . (isset($options['menu_toggle']) && !$options['menu_toggle'] ? 'display:none;' : '') . '">';

        // Get all menu items including top-level and submenus
        $menu_customizer = new WPCA_Menu_Customizer();
        $all_menu_items = $menu_customizer->get_all_menu_items();
        
        // Group items by type (top-level or submenu)
        $top_level_items = [];
        $submenu_items = [];
        foreach ($all_menu_items as $slug => $item) {
            if ($item['type'] === 'top') {
                $top_level_items[$slug] = $item;
            } else {
                if (!isset($submenu_items[$item['parent']])) {
                    $submenu_items[$item['parent']] = [];
                }
                $submenu_items[$item['parent']][$slug] = $item;
            }
        }
        ?>
        <div class="wpca-menu-order-wrapper">
            <ul id="wpca-menu-order-list" class="wpca-menu-sortable">
                <?php 
                // Combine all menu items (top level and submenus) into a single flat list
                $all_items = [];
                
                // First add top level items in saved order
                foreach ($menu_order as $item_slug) {
                    if (isset($top_level_items[$item_slug])) {
                        $all_items[$item_slug] = $top_level_items[$item_slug];
                        
                        // Add submenu items in saved order
                        if (isset($submenu_items[$item_slug])) {
                            $parent_order = $submenu_order[$item_slug] ?? [];
                            foreach ($parent_order as $sub_slug) {
                                // Submenu slugs are typically parent_slug|sub_slug
                                if (isset($submenu_items[$item_slug][$item_slug.'|'.$sub_slug])) {
                                    $all_items[$item_slug.'|'.$sub_slug] = $submenu_items[$item_slug][$item_slug.'|'.$sub_slug];
                                }
                            }
                        }
                    }
                }
                
                // Then add remaining top level items not in saved order
                foreach ($top_level_items as $item_slug => $item) {
                    if (!in_array($item_slug, $menu_order)) {
                        $all_items[$item_slug] = $item;
                        
                        // Add submenu items not in saved order
                        if (isset($submenu_items[$item_slug])) {
                            foreach ($submenu_items[$item_slug] as $sub_slug => $sub_item) {
                                $all_items[$sub_slug] = $sub_item;
                            }
                        }
                    }
                }
                
                // Render items with hierarchy
                $render_menu_items = function($items, $level = 0) use (&$render_menu_items, $options) {
                    foreach ($items as $slug => $item) {
                        $is_submenu = $level > 0;
                        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                        
                        echo '<li data-menu-slug="'.esc_attr($slug).'" data-item-type="'.($is_submenu ? 'sub' : 'top').'"';
                        echo $is_submenu ? ' data-parent-slug="'.esc_attr($item['parent']).'"' : '';
                        echo $is_submenu ? ' class="submenu-item level-'.$level.'"' : '';
                        echo '>';
                        echo '<span class="dashicons dashicons-menu"></span> ';
                        // Clean up menu title - remove numbers and additional info
                        $clean_title = preg_replace([
                            '/\s*[\d]+\s*/',  // Remove numbers
                            '/pending comments/',    // Remove comment status
                            '/pending$/',         // Remove trailing status
                            '/items?$/'            // Remove counter
                        ], '', strip_tags($item['title']));
                        echo $indent . esc_html(trim($clean_title));
                        echo '<label class="wpca-menu-toggle-switch" data-menu-slug="'.esc_attr($slug).'">';
                        echo '<input type="checkbox" name="wpca_settings[menu_toggles]['.esc_attr($slug).']" value="1" ' 
                            . checked( isset($options['menu_toggles'][$slug]) ? $options['menu_toggles'][$slug] : 1, 1, false ) 
                            . ' data-menu-slug="'.esc_attr($slug).'">';
                        echo '<span class="wpca-toggle-slider"></span>';
                        echo '</label>';
                        echo '<input type="hidden" name="wpca_settings[menu_order][]" value="'.esc_attr($slug).'">';
                        echo '</li>';
                        
                        // Render children if exists
                        if (!empty($item['children'])) {
                            $render_menu_items($item['children'], $level + 1);
                        }
                    }
                };
                
                // Build hierarchical menu structure
                $hierarchical_items = [];
                foreach ($all_items as $slug => $item) {
                    if (empty($item['parent'])) {
                        $hierarchical_items[$slug] = $item;
                    } else {
                        if (!isset($hierarchical_items[$item['parent']]['children'])) {
                            $hierarchical_items[$item['parent']]['children'] = [];
                        }
                        $hierarchical_items[$item['parent']]['children'][$slug] = $item;
                    }
                }
                
                $render_menu_items($hierarchical_items);
                ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Render the Login Page tab content.
     */
    public function render_login_tab() {
        $options = self::get_options(); // Use self::get_options() to retrieve options
        $login_style = isset($options['login_style']) ? $options['login_style'] : 'default';
        $login_logo = isset($options['login_logo']) ? $options['login_logo'] : '';
        $login_background = isset($options['login_background']) ? $options['login_background'] : '';
        $login_custom_css = isset($options['login_custom_css']) ? $options['login_custom_css'] : '';
        
        // Removed add_settings_section and add_settings_field calls - they are now in settings_init()
        ?>
        <div class="wpca-login-container">
            <?php 
            // Display login page elements control section
            do_settings_sections('wpca_settings_login'); // This call is correct here
            ?>
            
            <div class="wpca-login-column wpca-login-options">
                <div class="wpca-login-section">
                    <h3><?php _e('Login Style', 'wp-clean-admin'); ?></h3>
                    <p class="description"><?php _e('Choose from predefined login styles or create your own custom style.', 'wp-clean-admin'); ?></p>
                    
                    <div class="wpca-login-styles-grid">
                        <!-- Default Style -->
                        <div class="wpca-login-style-item <?php echo $login_style === 'default' ? 'active' : ''; ?>">
                            <label>
                                <input type="radio" name="wpca_settings[login_style]" value="default" <?php checked($login_style, 'default'); ?>>
                                <div class="wpca-login-style-preview default-style">
                                    <div class="preview-image"></div>
                                    <div class="preview-title"><?php _e('Default', 'wp-clean-admin'); ?></div>
                                </div>
                                <div class="preview-badge">
                                    <span class="dashicons dashicons-yes"></span>
                                </div>
                                <div class="wpca-login-style-label"><?php _e('Default', 'wp-clean-admin'); ?></div>
                            </label>
                        </div>
                         
                        <!-- Modern Style -->
                        <div class="wpca-login-style-item <?php echo $login_style === 'modern' ? 'active' : ''; ?>">
                            <label>
                                <input type="radio" name="wpca_settings[login_style]" value="modern" <?php checked($login_style, 'modern'); ?>>
                                <div class="wpca-login-style-preview modern-style">
                                    <div class="preview-image"></div>
                                    <div class="preview-title"><?php _e('Modern', 'wp-clean-admin'); ?></div>
                                </div>
                                <div class="preview-badge">
                                    <span class="dashicons dashicons-yes"></span>
                                </div>
                                <div class="wpca-login-style-label"><?php _e('Modern', 'wp-clean-admin'); ?></div>
                            </label>
                        </div>
                         
                        <!-- Minimal Style -->
                        <div class="wpca-login-style-item <?php echo $login_style === 'minimal' ? 'active' : ''; ?>">
                            <label>
                                <input type="radio" name="wpca_settings[login_style]" value="minimal" <?php checked($login_style, 'minimal'); ?>>
                                <div class="wpca-login-style-preview minimal-style">
                                    <div class="preview-image"></div>
                                    <div class="preview-title"><?php _e('Minimal', 'wp-clean-admin'); ?></div>
                                </div>
                                <div class="preview-badge">
                                    <span class="dashicons dashicons-yes"></span>
                                </div>
                                <div class="wpca-login-style-label"><?php _e('Minimal', 'wp-clean-admin'); ?></div>
                            </label>
                        </div>
                         
                        <!-- Dark Style -->
                        <div class="wpca-login-style-item <?php echo $login_style === 'dark' ? 'active' : ''; ?>">
                            <label>
                                <input type="radio" name="wpca_settings[login_style]" value="dark" <?php checked($login_style, 'dark'); ?>>
                                <div class="wpca-login-style-preview dark-style">
                                    <div class="preview-image"></div>
                                    <div class="preview-title"><?php _e('Dark', 'wp-clean-admin'); ?></div>
                                </div>
                                <div class="preview-badge">
                                    <span class="dashicons dashicons-yes"></span>
                                </div>
                                <div class="wpca-login-style-label"><?php _e('Dark', 'wp-clean-admin'); ?></div>
                            </label>
                        </div>
                         
                        <!-- Gradient Style -->
                        <div class="wpca-login-style-item <?php echo $login_style === 'gradient' ? 'active' : ''; ?>">
                            <label>
                                <input type="radio" name="wpca_settings[login_style]" value="gradient" <?php checked($login_style, 'gradient'); ?>>
                                <div class="wpca-login-style-preview gradient-style">
                                    <div class="preview-image"></div>
                                    <div class="preview-title"><?php _e('Gradient', 'wp-clean-admin'); ?></div>
                                </div>
                                <div class="preview-badge">
                                    <span class="dashicons dashicons-yes"></span>
                                </div>
                                <div class="wpca-login-style-label"><?php _e('Gradient', 'wp-clean-admin'); ?></div>
                            </label>
                        </div>
                         
                        <!-- Glassmorphism Style -->
                        <div class="wpca-login-style-item <?php echo $login_style === 'glassmorphism' ? 'active' : ''; ?>">
                            <label>
                                <input type="radio" name="wpca_settings[login_style]" value="glassmorphism" <?php checked($login_style, 'glassmorphism'); ?>>
                                <div class="wpca-login-style-preview glassmorphism-style">
                                    <div class="preview-image"></div>
                                    <div class="preview-title"><?php _e('Glassmorphism', 'wp-clean-admin'); ?></div>
                                </div>
                                <div class="preview-badge">
                                    <span class="dashicons dashicons-yes"></span>
                                </div>
                                <div class="wpca-login-style-label"><?php _e('Glassmorphism', 'wp-clean-admin'); ?></div>
                            </label>
                        </div>
                         
                        <!-- Neumorphism Style -->
                        <div class="wpca-login-style-item <?php echo $login_style === 'neumorphism' ? 'active' : ''; ?>">
                            <label>
                                <input type="radio" name="wpca_settings[login_style]" value="neumorphism" <?php checked($login_style, 'neumorphism'); ?>>
                                <div class="wpca-login-style-preview neumorphism-style">
                                    <div class="preview-image"></div>
                                    <div class="preview-title"><?php _e('Neumorphism', 'wp-clean-admin'); ?></div>
                                </div>
                                <div class="preview-badge">
                                    <span class="dashicons dashicons-yes"></span>
                                </div>
                                <div class="wpca-login-style-label"><?php _e('Neumorphism', 'wp-clean-admin'); ?></div>
                            </label>
                        </div>
                         
                        <!-- Custom Style -->
                            <div class="wpca-login-style-item <?php echo $login_style === 'custom' ? 'active' : ''; ?>">
                                <label>
                                    <input type="radio" name="wpca_settings[login_style]" value="custom" <?php checked($login_style, 'custom'); ?>>
                                    <div class="wpca-login-style-preview custom-style">
                                        <div class="preview-image"></div>
                                        <div class="preview-title"><?php _e('Custom', 'wp-clean-admin'); ?></div>
                                    </div>
                                    <div class="preview-badge">
                                        <span class="dashicons dashicons-yes"></span>
                                    </div>
                                    <div class="wpca-login-style-label"><?php _e('Custom', 'wp-clean-admin'); ?></div>
                                </label>
                            </div>
                    </div>
                    
                    <div id="wpca-custom-login-options" class="wpca-login-section" style="<?php echo $login_style === 'custom' ? '' : 'display: none;'; ?>">
                        <h3><?php _e('Custom Login Options', 'wp-clean-admin'); ?></h3>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Custom Logo', 'wp-clean-admin'); ?></th>
                                <td>
                                    <div class="wpca-media-uploader">
                                        <input type="text" name="wpca_settings[login_logo]" value="<?php echo esc_attr($login_logo); ?>" class="regular-text wpca-upload-field" id="wpca-login-logo">
                                        <button type="button" class="button wpca-upload-button" data-target="wpca-login-logo"><?php _e('Choose Image', 'wp-clean-admin'); ?></button>
                                        <button type="button" class="button wpca-remove-button" data-target="wpca-login-logo"><?php _e('Remove', 'wp-clean-admin'); ?></button>
                                        <p class="description"><?php _e('Upload a custom logo for the login page. Recommended size: 320×80px.', 'wp-clean-admin'); ?></p>
                                        <div class="wpca-preview-image" id="wpca-login-logo-preview" style="<?php echo !empty($login_logo) ? '' : 'display: none;'; ?>">
                                            <img src="<?php echo esc_url($login_logo); ?>" alt="<?php _e('Login Logo Preview', 'wp-clean-admin'); ?>">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Background Image', 'wp-clean-admin'); ?></th>
                                <td>
                                    <div class="wpca-media-uploader">
                                        <input type="text" name="wpca_settings[login_background]" value="<?php echo esc_attr($login_background); ?>" class="regular-text wpca-upload-field" id="wpca-login-background">
                                        <button type="button" class="button wpca-upload-button" data-target="wpca-login-background"><?php _e('Choose Image', 'wp-clean-admin'); ?></button>
                                        <button type="button" class="button wpca-remove-button" data-target="wpca-login-background"><?php _e('Remove', 'wp-clean-admin'); ?></button>
                                        <p class="description"><?php _e('Upload a custom background image for the login page.', 'wp-clean-admin'); ?></p>
                                        <div class="wpca-preview-image" id="wpca-login-background-preview" style="<?php echo !empty($login_background) ? '' : 'display: none;'; ?>">
                                            <img src="<?php echo esc_url($login_background); ?>" alt="<?php _e('Background Preview', 'wp-clean-admin'); ?>">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Custom CSS', 'wp-clean-admin'); ?></th>
                                <td>
                                    <textarea name="wpca_settings[login_custom_css]" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($login_custom_css); ?></textarea>
                                    <p class="description"><?php _e('Add custom CSS to further customize the login page.', 'wp-clean-admin'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="wpca-login-column wpca-login-preview">
                <div class="wpca-login-preview-section">
                    <h3><?php _e('Live Preview', 'wp-clean-admin'); ?></h3>
                    <p class="description"><?php _e('Preview how your login page will look with the current settings.', 'wp-clean-admin'); ?></p>
                    <div class="wpca-login-preview-frame">
                        <div class="wpca-login-preview-content">
                            <div class="wpca-login-preview-logo"></div>
                            <div class="wpca-login-preview-form">
                                <div class="wpca-login-preview-input"></div>
                                <div class="wpca-login-preview-input"></div>
                                <div class="wpca-login-preview-button"></div>
                                <div class="wpca-login-preview-links">
                                    <div></div>
                                    <div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Login page functionality is now integrated into the main settings script, no extra loading needed -->
        </div>
        
        <?php
        // The script for login style change and media uploader initialization is moved to wpca-settings.js
    }

    /**
     * Render the About tab content.
     */
    public function render_about_tab() {
        ?>
        <div class="wpca-about-wrapper">
            <div class="wpca-about-header">
                <h2><?php _e('WP Clean Admin', 'wp-clean-admin'); ?></h2>
                <p class="wpca-version"><?php echo sprintf(__('Version %s', 'wp-clean-admin'), WPCA_VERSION); ?></p>
            </div>
            
            <div class="wpca-about-section">
                <h3><?php _e('Plugin Description', 'wp-clean-admin'); ?></h3>
                <p><?php _e('WP Clean Admin is a powerful WordPress plugin designed to streamline and customize your WordPress admin experience. It provides a cleaner, more efficient interface by allowing you to hide unnecessary elements, customize the visual appearance, and reorganize the admin menu to better suit your workflow.', 'wp-clean-admin'); ?></p>
            </div>
            
            <div class="wpca-about-section">
                <h3><?php _e('Key Features', 'wp-clean-admin'); ?></h3>
                <ul>
                    <li><?php _e('Hide unnecessary dashboard widgets', 'wp-clean-admin'); ?></li>
                    <li><?php _e('Customize admin menu by hiding or reordering items', 'wp-clean-admin'); ?></li>
                    <li><?php _e('Hide admin bar items for a cleaner top bar', 'wp-clean-admin'); ?></li>
                    <li><?php _e('Choose from multiple visual themes or create your own', 'wp-clean-admin'); ?></li>
                    <li><?php _e('Adjust layout density and typography settings', 'wp-clean-admin'); ?></li>
                    <li><?php _e('Customize colors, shadows, and border styles', 'wp-clean-admin'); ?></li>
                </ul>
            </div>
            
            <div class="wpca-about-section">
                <h3><?php _e('Author', 'wp-clean-admin'); ?></h3>
                <p><?php _e('Created by Sut', 'wp-clean-admin'); ?></p>
                <p><a href="https://github.com/sutchan/WP-Clean-Admin" target="_blank"><?php _e('Visit Plugin Website', 'wp-clean-admin'); ?></a> | 
                <a href="https://github.com/sutchan/WP-Clean-Admin" target="_blank"><?php _e('Documentation', 'wp-clean-admin'); ?></a> | 
                <a href="https://github.com/sutchan/WP-Clean-Admin" target="_blank"><?php _e('Support', 'wp-clean-admin'); ?></a></p>
            </div>
            
            <div class="wpca-about-section">
                <h3><?php _e('Rate & Review', 'wp-clean-admin'); ?></h3>
                <p><?php _e('If you find this plugin useful, please consider leaving a review on WordPress.org. Your feedback helps improve the plugin and reach more users.', 'wp-clean-admin'); ?></p>
                <p><a href="https://github.com/sutchan/WP-Clean-Admin" target="_blank" class="button button-primary"><?php _e('Rate on WordPress.org', 'wp-clean-admin'); ?></a></p>
            </div>
        </div>

        <?php
    }

    /**
     * Render Login Elements field.
     */
    public function login_elements_render() {
        $options = self::get_options(); // Use self::get_options() to retrieve options
        $login_elements = $options['login_elements'] ?? array(
            'language_switcher' => 1,
            'home_link' => 1,
            'register_link' => 1,
            'remember_me' => 1
        );
        ?>
        <fieldset>
            <label>
                <input type="checkbox" name="wpca_settings[login_elements][language_switcher]" value="1" <?php checked( $login_elements['language_switcher'], 1 ); ?>>
                <?php _e( 'Language Switcher', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[login_elements][home_link]" value="1" <?php checked( $login_elements['home_link'], 1 ); ?>>
                <?php _e( 'Back to Home Link', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[login_elements][register_link]" value="1" <?php checked( $login_elements['register_link'], 1 ); ?>>
                <?php _e( 'Register Link', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[login_elements][remember_me]" value="1" <?php checked( $login_elements['remember_me'], 1 ); ?>>
                <?php _e( 'Remember Me Checkbox', 'wp-clean-admin' ); ?>
            </label>
        </fieldset>
        <?php
    }

    /**
     * Login Elements section callback.
     */
    public function login_elements_section_callback() {
        echo __( 'Customize which elements appear on the login page.', 'wp-clean-admin' );
    }

    /**
     * Render Hide WordPress Title field
     */
    public function hide_wordpress_title_render() {
        $options = self::get_options(); // Use self::get_options() to retrieve options
        ?>
        <label>
            <input type="checkbox" name="wpca_settings[hide_wordpress_title]" value="1" <?php checked( $options['hide_wordpress_title'] ?? 0, 1 ); ?>>
            <?php _e( 'Hide "WordPress" in page titles', 'wp-clean-admin' ); ?>
        </label>
        <p class="description"><?php _e( 'When enabled, removes "WordPress" from admin page titles.', 'wp-clean-admin' ); ?></p>
        <?php
    }
    
    /**
     * Render the Hide Frontend Admin Bar field.
     */
    public function hide_frontend_adminbar_render() {
        $options = self::get_options();
        ?>
        <label>
            <input type="checkbox" name="wpca_settings[hide_frontend_adminbar]" value="1" <?php checked( $options['hide_frontend_adminbar'] ?? 0, 1 ); ?>>
            <?php _e( 'Hide admin bar on frontend', 'wp-clean-admin' ); ?>
        </label>
        <p class="description"><?php _e( 'When enabled, hides the WordPress admin bar for all users when viewing the website frontend.', 'wp-clean-admin' ); ?></p>
        <?php
    }
    
    /**
     * Render Hide WP Footer field
     */
    public function hide_wpfooter_render() {
        $options = self::get_options(); // Use self::get_options() to retrieve options
        ?>
        <label>
            <input type="checkbox" name="wpca_settings[hide_wpfooter]" value="1" <?php checked( $options['hide_wpfooter'] ?? 0, 1 ); ?>>
            <?php _e( 'Hide WordPress footer', 'wp-clean-admin' ); ?>
        </label>
        <p class="description"><?php _e( 'When enabled, removes the "Thank you for creating with WordPress" footer text.', 'wp-clean-admin' ); ?></p>
        <?php
    }

    public function hide_dashboard_widgets_render() {
        $options = self::get_options(); // Use self::get_options() to retrieve options
        $hidden_widgets = $options['hide_dashboard_widgets'] ?? [];
        
        // Get all core dashboard widgets
        $core_widgets = [
            'dashboard_primary' => __('WordPress News and Events', 'wp-clean-admin'),
            'dashboard_quick_press' => __('Quick Draft', 'wp-clean-admin'),
            'dashboard_right_now' => __('At a Glance', 'wp-clean-admin')
        ];
        
        echo '<fieldset>';
        foreach ($core_widgets as $widget_id => $title) {
            $checked = in_array($widget_id, $hidden_widgets) ? 'checked' : '';
            echo '<label><input type="checkbox" name="wpca_settings[hide_dashboard_widgets][]" 
                  value="'.esc_attr($widget_id).'" '.$checked.'> '.
                  esc_html($title).'</label><br>';
        }
        echo '</fieldset>';
        echo '<p class="description">'.__('Select dashboard widgets to hide', 'wp-clean-admin').'</p>';
    }

    public function hide_admin_bar_items_render() {
        $options = self::get_options(); // Use self::get_options() to retrieve options
        $admin_bar_items_to_hide = $options['hide_admin_bar_items'] ?? array();
        ?>
        <fieldset>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_bar_items][]" value="wp-logo" <?php checked( in_array( 'wp-logo', $admin_bar_items_to_hide ) ); ?>>
                <?php _e( 'WordPress Logo', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_bar_items][]" value="site-name" <?php checked( in_array( 'site-name', $admin_bar_items_to_hide ) ); ?>>
                <?php _e( 'Site Name', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_bar_items][]" value="updates" <?php checked( in_array( 'updates', $admin_bar_items_to_hide ) ); ?>>
                <?php _e( 'Updates', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_bar_items][]" value="comments" <?php checked( in_array( 'comments', $admin_bar_items_to_hide ) ); ?>>
                <?php _e( 'Comments', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_bar_items][]" value="new-content" <?php checked( in_array( 'new-content', $admin_bar_items_to_hide ) ); ?>>
                <?php _e( 'New Content', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_bar_items][]" value="my-account" <?php checked( in_array( 'my-account', $admin_bar_items_to_hide ) ); ?>>
                <?php _e( 'My Account', 'wp-clean-admin' ); ?>
            </label><br>
            <!-- Add more admin bar items as needed. Use the node ID. -->
        </fieldset>
        <?php
    }

    /**
     * Render Theme Style field
     */
    public function theme_style_render() {
        $options = self::get_options();
        $theme_style = isset($options['theme_style']) ? $options['theme_style'] : 'default';
        ?>
        <select name="wpca_settings[theme_style]">
            <option value="default" <?php selected($theme_style, 'default'); ?>><?php _e('Default', 'wp-clean-admin'); ?></option>
            <option value="modern" <?php selected($theme_style, 'modern'); ?>><?php _e('Modern', 'wp-clean-admin'); ?></option>
            <option value="minimal" <?php selected($theme_style, 'minimal'); ?>><?php _e('Minimal', 'wp-clean-admin'); ?></option>
            <option value="dark" <?php selected($theme_style, 'dark'); ?>><?php _e('Dark', 'wp-clean-admin'); ?></option>
            <option value="blue" <?php selected($theme_style, 'blue'); ?>><?php _e('Blue', 'wp-clean-admin'); ?></option>
            <option value="green" <?php selected($theme_style, 'green'); ?>><?php _e('Green', 'wp-clean-admin'); ?></option>
            <option value="purple" <?php selected($theme_style, 'purple'); ?>><?php _e('Purple', 'wp-clean-admin'); ?></option>
        </select>
        <p class="description"><?php _e('Select a theme style for the admin area.', 'wp-clean-admin'); ?></p>
        <?php
    }

    /**
     * Render Primary Color field
     */
    public function primary_color_render() {
        $options = self::get_options();
        $primary_color = isset($options['primary_color']) ? $options['primary_color'] : '#4A90E2';
        ?>
        <div class="wpca-color-picker-container">
            <div class="wpca-color-picker-preview" style="background-color: <?php echo esc_attr($primary_color); ?>"></div>
            <div class="wpca-color-picker-wrap">
                <input 
                    type="color" 
                    name="wpca_settings[primary_color]" 
                    value="<?php echo esc_attr($primary_color); ?>"
                    class="wpca-color-picker"
                    data-color-type="primary"
                >
                <div class="wpca-color-value"><?php echo esc_attr($primary_color); ?></div>
                <button type="button" class="wpca-color-preset-btn" data-preset="blue">#3b82f6</button>
                <button type="button" class="wpca-color-preset-btn" data-preset="indigo">#6366f1</button>
                <button type="button" class="wpca-color-preset-btn" data-preset="purple">#8b5cf6</button>
                <button type="button" class="wpca-color-preset-btn" data-preset="pink">#ec4899</button>
                <button type="button" class="wpca-color-preset-btn" data-preset="red">#ef4444</button>
                <button type="button" class="wpca-color-preset-btn" data-preset="green">#22c55e</button>
            </div>
            <p class="description"><?php _e('Select the primary color for links, buttons, and active states.', 'wp-clean-admin'); ?></p>
        </div>
        <?php
    }

    /**
     * Render Background Color field
     */
    public function background_color_render() {
        $options = self::get_options();
        $background_color = isset($options['background_color']) ? $options['background_color'] : '#F8F9FA';
        ?>
        <div class="wpca-color-picker-container">
            <div class="wpca-color-picker-preview" style="background-color: <?php echo esc_attr($background_color); ?>"></div>
            <div class="wpca-color-picker-wrap">
                <input 
                    type="color" 
                    name="wpca_settings[background_color]" 
                    value="<?php echo esc_attr($background_color); ?>"
                    class="wpca-color-picker"
                    data-color-type="background"
                >
                <div class="wpca-color-value"><?php echo esc_attr($background_color); ?></div>
                <button type="button" class="wpca-color-preset-btn" data-preset="light">#ffffff</button>
                <button type="button" class="wpca-color-preset-btn" data-preset="gray">#f9fafb</button>
                <button type="button" class="wpca-color-preset-btn" data-preset="dark-gray">#f3f4f6</button>
                <button type="button" class="wpca-color-preset-btn" data-preset="dark">#1f2937</button>
            </div>
            <p class="description"><?php _e('Select the background color for the admin interface.', 'wp-clean-admin'); ?></p>
        </div>
        <?php
    }

    /**
     * Render Text Color field
     */
    public function text_color_render() {
        $options = self::get_options();
        $text_color = isset($options['text_color']) ? $options['text_color'] : '#2D3748';
        ?>
        <div class="wpca-color-picker-container">
            <div class="wpca-color-picker-preview" style="background-color: <?php echo esc_attr($text_color); ?>"></div>
            <div class="wpca-color-picker-wrap">
                <input 
                    type="color" 
                    name="wpca_settings[text_color]" 
                    value="<?php echo esc_attr($text_color); ?>"
                    class="wpca-color-picker"
                    data-color-type="text"
                >
                <div class="wpca-color-value"><?php echo esc_attr($text_color); ?></div>
                <button type="button" class="wpca-color-preset-btn" data-preset="dark">#111827</button>
                <button type="button" class="wpca-color-preset-btn" data-preset="medium">#374151</button>
                <button type="button" class="wpca-color-preset-btn" data-preset="light">#4b5563</button>
                <button type="button" class="wpca-color-preset-btn" data-preset="white">#ffffff</button>
            </div>
            <p class="description"><?php _e('Select the text color for the admin interface.', 'wp-clean-admin'); ?></p>
        </div>
        <?php
    }

    /**
     * Render Shadow Style field
     */
    public function shadow_style_render() {
        $options = self::get_options();
        $shadow_style = isset($options['shadow_style']) ? $options['shadow_style'] : 'subtle';
        ?>
        <select name="wpca_settings[shadow_style]">
            <option value="none" <?php selected($shadow_style, 'none'); ?>><?php _e('None', 'wp-clean-admin'); ?></option>
            <option value="subtle" <?php selected($shadow_style, 'subtle'); ?>><?php _e('Subtle', 'wp-clean-admin'); ?></option>
            <option value="moderate" <?php selected($shadow_style, 'moderate'); ?>><?php _e('Moderate', 'wp-clean-admin'); ?></option>
            <option value="pronounced" <?php selected($shadow_style, 'pronounced'); ?>><?php _e('Pronounced', 'wp-clean-admin'); ?></option>
        </select>
        <p class="description"><?php _e('Select the shadow style for elements.', 'wp-clean-admin'); ?></p>
        <?php
    }

    /**
     * Render Layout Density field
     */
    public function layout_density_render() {
        $options = self::get_options();
        $layout_density = isset($options['layout_density']) ? $options['layout_density'] : 'standard';
        ?>
        <select name="wpca_settings[layout_density]">
            <option value="compact" <?php selected($layout_density, 'compact'); ?>><?php _e('Compact', 'wp-clean-admin'); ?></option>
            <option value="standard" <?php selected($layout_density, 'standard'); ?>><?php _e('Standard', 'wp-clean-admin'); ?></option>
            <option value="spacious" <?php selected($layout_density, 'spacious'); ?>><?php _e('Spacious', 'wp-clean-admin'); ?></option>
        </select>
        <p class="description"><?php _e('Select the layout density.', 'wp-clean-admin'); ?></p>
        <?php
    }

    /**
     * Render Border Radius Style field
     */
    public function border_radius_style_render() {
        $options = self::get_options();
        $border_radius_style = isset($options['border_radius_style']) ? $options['border_radius_style'] : 'small';
        ?>
        <select name="wpca_settings[border_radius_style]">
            <option value="none" <?php selected($border_radius_style, 'none'); ?>><?php _e('None', 'wp-clean-admin'); ?></option>
            <option value="small" <?php selected($border_radius_style, 'small'); ?>><?php _e('Small', 'wp-clean-admin'); ?></option>
            <option value="medium" <?php selected($border_radius_style, 'medium'); ?>><?php _e('Medium', 'wp-clean-admin'); ?></option>
            <option value="large" <?php selected($border_radius_style, 'large'); ?>><?php _e('Large', 'wp-clean-admin'); ?></option>
        </select>
        <p class="description"><?php _e('Select the border radius style for elements.', 'wp-clean-admin'); ?></p>
        <?php
    }

    /**
     * Render Font Stack field
     */
    public function font_stack_render() {
        $options = self::get_options();
        $font_stack = isset($options['font_stack']) ? $options['font_stack'] : 'system';
        ?>
        <select name="wpca_settings[font_stack]">
            <option value="system" <?php selected($font_stack, 'system'); ?>><?php _e('System Default', 'wp-clean-admin'); ?></option>
            <option value="sans-serif" <?php selected($font_stack, 'sans-serif'); ?>><?php _e('Sans Serif', 'wp-clean-admin'); ?></option>
            <option value="serif" <?php selected($font_stack, 'serif'); ?>><?php _e('Serif', 'wp-clean-admin'); ?></option>
            <option value="monospace" <?php selected($font_stack, 'monospace'); ?>><?php _e('Monospace', 'wp-clean-admin'); ?></option>
        </select>
        <p class="description"><?php _e('Select the font stack for the admin interface.', 'wp-clean-admin'); ?></p>
        <?php
    }

    /**
     * Render Font Size Base field
     */
    public function font_size_base_render() {
        $options = self::get_options();
        $font_size_base = isset($options['font_size_base']) ? $options['font_size_base'] : 'medium';
        ?>
        <select name="wpca_settings[font_size_base]">
            <option value="small" <?php selected($font_size_base, 'small'); ?>><?php _e('Small', 'wp-clean-admin'); ?></option>
            <option value="medium" <?php selected($font_size_base, 'medium'); ?>><?php _e('Medium', 'wp-clean-admin'); ?></option>
            <option value="large" <?php selected($font_size_base, 'large'); ?>><?php _e('Large', 'wp-clean-admin'); ?></option>
        </select>
        <p class="description"><?php _e('Select the base font size for the admin interface.', 'wp-clean-admin'); ?></p>
        <?php
    }

    /**
     * Render Icon Style field
     */
    public function icon_style_render() {
        $options = self::get_options();
        $icon_style = isset($options['icon_style']) ? $options['icon_style'] : 'dashicons';
        ?>
        <select name="wpca_settings[icon_style]">
            <option value="dashicons" <?php selected($icon_style, 'dashicons'); ?>><?php _e('WordPress Dashicons', 'wp-clean-admin'); ?></option>
            <option value="fontawesome" <?php selected($icon_style, 'fontawesome'); ?>><?php _e('Font Awesome', 'wp-clean-admin'); ?></option>
            <option value="none" <?php selected($icon_style, 'none'); ?>><?php _e('No Icons', 'wp-clean-admin'); ?></option>
        </select>
        <p class="description"><?php _e('Select the icon style for the admin interface.', 'wp-clean-admin'); ?></p>
        <?php
    }

    /**
     * Sanitize plugin settings before saving
     */
    public function sanitize_settings($input) {
        $output = array();
        
        // 定义一个安全的文本清理函数，如果WordPress函数不存在则使用自定义实现
        $safe_sanitize_text_field = function_exists('sanitize_text_field') ? 'sanitize_text_field' : function($text) {
            return filter_var($text, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        };
        
        // 定义一个安全的十六进制颜色清理函数
        $safe_sanitize_hex_color = function_exists('sanitize_hex_color') ? 'sanitize_hex_color' : function($color) {
            // 简单的十六进制颜色验证
            if (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                return $color;
            }
            return '#000000'; // 默认返回黑色
        };
        
        // 定义一个安全的URL清理函数
        $safe_esc_url_raw = function_exists('esc_url_raw') ? 'esc_url_raw' : function($url) {
            return filter_var($url, FILTER_SANITIZE_URL);
        };
        
        // 定义一个安全的标签清理函数
        $safe_wp_strip_all_tags = function_exists('wp_strip_all_tags') ? 'wp_strip_all_tags' : function($text) {
            return strip_tags($text);
        };
        
        // Validate and sanitize menu settings
        $output['menu_toggle'] = isset($input['menu_toggle']) ? (int)$input['menu_toggle'] : 0;

        // Validate login elements
        $output['login_elements'] = array(
            'language_switcher' => isset($input['login_elements']['language_switcher']) ? 1 : 0,
            'home_link' => isset($input['login_elements']['home_link']) ? 1 : 0,
            'register_link' => isset($input['login_elements']['register_link']) ? 1 : 0,
            'remember_me' => isset($input['login_elements']['remember_me']) ? 1 : 0
        );
        
        // Validate menu toggle states
        $output['menu_toggles'] = array();
        if (isset($input['menu_toggles']) && is_array($input['menu_toggles'])) {
            foreach ($input['menu_toggles'] as $slug => $value) {
                // 确保值只能是0或1，并进行适当的清理
                if (is_string($slug) && ($value === 0 || $value === 1)) {
                    $sanitized_slug = $safe_sanitize_text_field($slug);
                    $output['menu_toggles'][$sanitized_slug] = intval($value);
                }
            }
        }
        
        // Validate menu order with stricter checks
        $output['menu_order'] = array();
        if (isset($input['menu_order']) && is_array($input['menu_order'])) {
            // 过滤掉非字符串或空的菜单项
            $output['menu_order'] = array_filter(
                array_map($safe_sanitize_text_field, $input['menu_order']),
                function($item) {
                    return !empty($item);
                }
            );
        }
        
        // Validate submenu order
        $output['submenu_order'] = array();
        if (isset($input['submenu_order']) && is_array($input['submenu_order'])) {
            $output['submenu_order'] = array_map($safe_sanitize_text_field, $input['submenu_order']);
        }
        
        // Sanitize hide WordPress title setting
        $output['hide_wordpress_title'] = isset($input['hide_wordpress_title']) ? 1 : 0;
        
        // Sanitize hide wpfooter setting
        $output['hide_wpfooter'] = isset($input['hide_wpfooter']) ? 1 : 0;

        // Sanitize hide frontend admin bar setting
        $output['hide_frontend_adminbar'] = isset($input['hide_frontend_adminbar']) ? 1 : 0;

        // Sanitize other settings
        $output['current_tab'] = isset($input['current_tab']) ? $safe_sanitize_text_field($input['current_tab']) : 'tab-general';
        $output['hide_dashboard_widgets'] = isset($input['hide_dashboard_widgets']) ? 
            array_map($safe_sanitize_text_field, $input['hide_dashboard_widgets']) : 
            array();
        $output['theme_style'] = isset($input['theme_style']) ? $safe_sanitize_text_field($input['theme_style']) : 'default';
        $output['hide_admin_menu_items'] = isset($input['hide_admin_menu_items']) ? 
            array_map($safe_sanitize_text_field, $input['hide_admin_menu_items']) : 
            array();
        $output['hide_admin_bar_items'] = isset($input['hide_admin_bar_items']) ? 
            array_map($safe_sanitize_text_field, $input['hide_admin_bar_items']) : 
            array();
        $output['layout_density'] = isset($input['layout_density']) ? $safe_sanitize_text_field($input['layout_density']) : 'standard';
        $output['border_radius_style'] = isset($input['border_radius_style']) ? $safe_sanitize_text_field($input['border_radius_style']) : 'small';
        $output['shadow_style'] = isset($input['shadow_style']) ? $safe_sanitize_text_field($input['shadow_style']) : 'subtle';
        $output['primary_color'] = isset($input['primary_color']) ? $safe_sanitize_hex_color($input['primary_color']) : '#4A90E2';
        $output['background_color'] = isset($input['background_color']) ? $safe_sanitize_hex_color($input['background_color']) : '#F8F9FA';
        $output['text_color'] = isset($input['text_color']) ? $safe_sanitize_hex_color($input['text_color']) : '#2D3748';
        $output['font_stack'] = isset($input['font_stack']) ? $safe_sanitize_text_field($input['font_stack']) : 'system';
        $output['font_size_base'] = isset($input['font_size_base']) ? $safe_sanitize_text_field($input['font_size_base']) : 'medium';
        $output['icon_style'] = isset($input['icon_style']) ? $safe_sanitize_text_field($input['icon_style']) : 'dashicons';
        
        // Login page settings
        $output['login_style'] = isset($input['login_style']) ? $safe_sanitize_text_field($input['login_style']) : 'default';
        $output['login_logo'] = isset($input['login_logo']) ? $safe_esc_url_raw($input['login_logo']) : '';
        $output['login_background'] = isset($input['login_background']) ? $safe_esc_url_raw($input['login_background']) : '';
        $output['login_custom_css'] = isset($input['login_custom_css']) ? $safe_wp_strip_all_tags($input['login_custom_css']) : '';
        
        // Clear cache
        self::$cached_options = null;
        
        return $output;
    }

    /**
     * Render the options page.
     */
    public function options_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <div class="wpca-tabs">
                <div class="wpca-tab active" data-tab="tab-general"><?php _e('General', 'wp-clean-admin'); ?></div>
                <div class="wpca-tab" data-tab="tab-visual-style"><?php _e('Visual Style', 'wp-clean-admin'); ?></div>
                <div class="wpca-tab" data-tab="tab-menu"><?php _e('Menu Customization', 'wp-clean-admin'); ?></div>
                <div class="wpca-tab" data-tab="tab-login"><?php _e('Login Page', 'wp-clean-admin'); ?></div>
                <div class="wpca-tab" data-tab="tab-about"><?php _e('About', 'wp-clean-admin'); ?></div>
            </div>
            
            <form action="options.php" method="post" id="wpca-settings-form">
                <input type="hidden" id="wpca-current-tab" name="wpca_settings[current_tab]" value="<?php echo esc_attr(self::get_options()['current_tab'] ?? 'tab-general'); ?>">
                <?php
                settings_fields( 'wpca_settings' );
                
                // General tab content
                $active_tab = self::get_options()['current_tab'] ?? 'tab-general';
                echo '<div id="tab-general" class="wpca-tab-content ' . ($active_tab === 'tab-general' ? 'active' : '') . '">';
                do_settings_sections( 'wpca_settings' );
                echo '<div class="wpca-reset-section">';
                echo '<button type="button" id="wpca-reset-general" class="wpca-reset-button button-secondary">'.__('Reset to Defaults', 'wp-clean-admin').'</button>';
                echo '</div>';
                echo '</div>';
                
                // Menu Customization tab content
                echo '<div id="tab-menu" class="wpca-tab-content ' . ($active_tab === 'tab-menu' ? 'active' : '') . '">
';                do_settings_sections('wpca_settings_menu');
                echo '<div class="wpca-reset-section">
';                echo '<button type="button" id="wpca-reset-menu" class="wpca-reset-button button-secondary">'.__('Reset to Defaults', 'wp-clean-admin').'</button>
';                echo '<button type="button" id="wpca-reset-menu-order" class="wpca-reset-button button-secondary" style="margin-left: 10px;">
';                echo '<span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-right: 5px;"></span>
';                echo __('Reset to Default Order', 'wp-clean-admin');
                echo '</button>
';                echo '</div>
';                echo '</div>
';
                
                // Visual Style tab content
                echo '<div id="tab-visual-style" class="wpca-tab-content ' . ($active_tab === 'tab-visual-style' ? 'active' : '') . '">';
                do_settings_sections('wpca_settings_visual_style');
                echo '<div class="wpca-reset-section">';
                echo '<button type="button" id="wpca-reset-visual" class="wpca-reset-button button-secondary">'.__('Reset to Defaults', 'wp-clean-admin').'</button>';
                echo '</div>';
                echo '</div>';
                
                // Login Page tab content
                echo '<div id="tab-login" class="wpca-tab-content ' . ($active_tab === 'tab-login' ? 'active' : '') . '">';
                $this->render_login_tab();
                echo '<div class="wpca-reset-section">';
                echo '<button type="button" id="wpca-reset-login" class="wpca-reset-button button-secondary">'.__('Reset to Defaults', 'wp-clean-admin').'</button>';
                echo '</div>';
                echo '</div>';
                
                // About tab content
                echo '<div id="tab-about" class="wpca-tab-content ' . ($active_tab === 'tab-about' ? 'active' : '') . '">';
                $this->render_about_tab();
                echo '</div>';
                
                // Submit button for the form
                submit_button();
                
                // Hook for additional form elements (import/export)
                do_action('wpca_settings_after_form');
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * AJAX handler to reset settings for a specific tab
     */
    public function ajax_reset_settings() {
        // 验证请求
        if (!$this->validate_ajax_request('wpca_admin_nonce')) {
            return;
        }
        
        // 安全函数检查
        if (!function_exists('wp_send_json_error') || !function_exists('wp_send_json_success')) {
            return;
        }
        
        // 获取并验证tab参数
        $allowed_tabs = ['general', 'visual', 'login', 'menu'];
        $tab = 'general'; // 默认值
        
        if (isset($_POST['tab']) && is_string($_POST['tab']) && in_array($_POST['tab'], $allowed_tabs)) {
            if (function_exists('sanitize_text_field')) {
                $tab = sanitize_text_field($_POST['tab']);
            }
        } else {
            wp_send_json_error(array(
                'message' => __('Invalid tab specified', 'wp-clean-admin')
            ), 400);
            return;
        }
        
        // Get current options and default settings
        $options = self::get_options();
        $defaults = self::get_default_settings();
        
        // Reset specific tab settings
        switch ($tab) {
            case 'general':
                // Reset general settings
                $options['hide_wordpress_title'] = $defaults['hide_wordpress_title'];
                $options['hide_wpfooter'] = $defaults['hide_wpfooter'];
                $options['hide_frontend_adminbar'] = $defaults['hide_frontend_adminbar'];
                $options['hide_dashboard_widgets'] = $defaults['hide_dashboard_widgets'];
                break;
                
            case 'visual':
                // Reset visual style settings
                $options['theme_style'] = $defaults['theme_style'];
                $options['primary_color'] = $defaults['primary_color'];
                $options['background_color'] = $defaults['background_color'];
                $options['text_color'] = $defaults['text_color'];
                $options['layout_density'] = $defaults['layout_density'];
                $options['border_radius_style'] = $defaults['border_radius_style'];
                $options['shadow_style'] = $defaults['shadow_style'];
                $options['font_stack'] = $defaults['font_stack'];
                $options['font_size_base'] = $defaults['font_size_base'];
                $options['icon_style'] = $defaults['icon_style'];
                break;
                
            case 'login':
                // Reset login page settings
                $options['login_style'] = $defaults['login_style'];
                $options['login_logo'] = $defaults['login_logo'];
                $options['login_background'] = $defaults['login_background'];
                $options['login_custom_css'] = $defaults['login_custom_css'];
                $options['login_elements'] = $defaults['login_elements'];
                break;
            
            case 'menu':
                // Reset menu settings
                $options['menu_toggle'] = $defaults['menu_toggle'];
                $options['menu_toggles'] = $defaults['menu_toggles'];
                $options['menu_order'] = $defaults['menu_order'];
                $options['submenu_order'] = $defaults['submenu_order'];
                $options['hide_admin_menu_items'] = $defaults['hide_admin_menu_items'];
                $options['hide_admin_bar_items'] = $defaults['hide_admin_bar_items'];
                break;
        }
        
        // 保存选项并响应
        if (function_exists('update_option')) {
            $update_result = update_option('wpca_settings', $options);
            
            if ($update_result) {
                self::$cached_options = null;
                wp_send_json_success(array(
                    'message' => __('Settings reset successfully', 'wp-clean-admin'),
                    'tab' => $tab
                ));
            } else {
                // 保存失败的处理
                if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                    error_log('WP Clean Admin: Failed to reset settings. Options: ' . print_r($options, true));
                }
                
                wp_send_json_error(array(
                    'message' => __('Failed to reset settings', 'wp-clean-admin'),
                    'debug_info' => defined('WP_DEBUG') && WP_DEBUG ? 'Option update failed for wpca_settings' : null
                ), 500);
            }
        } else {
            // 如果update_option函数不存在
            wp_send_json_error(array(
                'message' => __('Failed to reset settings', 'wp-clean-admin')
            ), 500);
        }
    }
    
    /**
     * AJAX handler to reset menu order specifically
     */
    public function ajax_reset_menu_order() {
        // 验证请求
        if (!$this->validate_ajax_request('wpca_admin_nonce')) {
            return;
        }
        
        // 安全函数检查
        if (!function_exists('wp_send_json_error') || !function_exists('wp_send_json_success') || !function_exists('update_option')) {
            return;
        }
        
        try {
            // 获取当前选项和默认设置
            $options = self::get_options();
            $defaults = self::get_default_settings();
            
            // 仅重置菜单顺序相关设置
            $options['menu_order'] = $defaults['menu_order'];
            $options['submenu_order'] = $defaults['submenu_order'];
            
            // 保存选项并响应
            $update_result = update_option('wpca_settings', $options);
            
            if ($update_result) {
                self::$cached_options = null;
                wp_send_json_success(array(
                    'message' => __('Menu order reset successfully', 'wp-clean-admin')
                ));
            } else {
                // 保存失败的处理
                if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                    error_log('WP Clean Admin: Failed to reset menu order.');
                }
                
                wp_send_json_error(array(
                    'message' => __('Failed to reset menu order', 'wp-clean-admin')
                ), 500);
            }
        } catch (Exception $e) {
            // 异常处理
            if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                error_log('WP Clean Admin: Exception when resetting menu order: ' . $e->getMessage());
            }
            
            wp_send_json_error(array(
                'message' => __('An error occurred while resetting menu order', 'wp-clean-admin')
            ), 500);
        }
    }
}
?>