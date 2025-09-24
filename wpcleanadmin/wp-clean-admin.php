<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI: https://github.com/sutchan/WP-Clean-Admin
 * Description: 简化并优化WordPress管理界面，提供更清爽的后台体验
 * Version: 1.0.0
 * Author: Sut
 * Author URI: https://github.com/sutchan
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// 安全检查：如果直接访问此文件，则中止
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('WPCA_VERSION', '1.0.0');
define('WPCA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPCA_PLUGIN_FILE', __FILE__);
define('WPCA_PLUGIN_URL', plugin_dir_url(__FILE__)); // 修复常量定义
define('WPCA_SETTINGS_KEY', 'wpca_settings');

/**
 * 加载插件文本域用于翻译
 */
function wpca_load_textdomain() {
    load_plugin_textdomain('wp-clean-admin', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'wpca_load_textdomain');

/**
 * 加载管理界面脚本和样式
 */
function wpca_enqueue_admin_assets($hook) {
    // 只在管理页面加载
    if (!is_admin()) {
        return;
    }
    
    // 核心管理样式（全局）
    wp_enqueue_style('wpca-admin-styles', WPCA_PLUGIN_URL . 'assets/css/wpca-admin.css', array(), WPCA_VERSION);
    wp_enqueue_script('wpca-core', WPCA_PLUGIN_URL . 'assets/js/wpca-core.js', array('jquery'), WPCA_VERSION, true);
    
    // 设置页面专用资源
    if (strpos($hook, 'settings_page_wp_clean_admin') !== false) {
        wp_enqueue_style('wpca-settings-style', WPCA_PLUGIN_URL . 'assets/css/wpca-settings.css', array(), WPCA_VERSION);
        wp_enqueue_script('wpca-settings', WPCA_PLUGIN_URL . 'assets/js/wpca-settings.js', array('jquery', 'wpca-core'), WPCA_VERSION, true);
    }
    
    // 仪表盘页面专用资源
    if (function_exists('get_current_screen') && get_current_screen() && get_current_screen()->id === 'dashboard') {
        wp_enqueue_script('wpca-dashboard', WPCA_PLUGIN_URL . 'assets/js/wpca-dashboard.js', array('jquery', 'wpca-core'), WPCA_VERSION, true);
    }
}
add_action('admin_enqueue_scripts', 'wpca_enqueue_admin_assets');

// Include core files
require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-permissions.php'; // 权限管理系统
require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-settings.php';
require_once WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php';
require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-user-roles.php'; // User role permissions
require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-menu-customizer.php'; // Menu customization

/**
 * 初始化插件
 */
function wpca_run_plugin() {
    try {
        // 1. 初始化设置
        if (class_exists('WPCA_Settings')) {
            $settings = new WPCA_Settings();
        }
        
        // 2. 初始化权限管理系统（对所有用户都可用）
        if (class_exists('WPCA_Permissions')) {
            $permissions = new WPCA_Permissions();
        }
        
        // 3. 只有管理员才能初始化这些组件
        if (current_user_can('manage_options')) {
            // 用户角色管理已经通过自动加载初始化
            // 菜单自定义已经通过自动加载初始化
        }
    } catch (Exception $e) {
        // 记录错误但不中断执行
        error_log(sprintf('[WP Clean Admin] 初始化错误: %s', $e->getMessage()));
        
        // 在管理界面显示错误通知（仅对管理员）
        if (is_admin() && current_user_can('manage_options')) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>';
                echo sprintf('[WP Clean Admin] 初始化错误: %s', esc_html($e->getMessage()));
                echo '</p></div>';
            });
        }
    }
}

/**
 * 插件激活钩子
 */
function wpca_activate() {
    try {
        // 确保设置存在
        $current_version = get_option('wpca_plugin_version', '0.0.0');

        // 比较版本号
        if (version_compare($current_version, WPCA_VERSION, '<')) {
           // 如果设置不存在，创建默认设置
            $default_settings = get_option(WPCA_SETTINGS_KEY, array());

            if (empty($default_settings)) {
                $default_settings = array(
                    'general' => array(
                        'clean_dashboard' => 1,
                        'remove_screen_options' => 0,
                        'remove_help_tabs' => 0,
                    ),
                    'menu_toggles' => array(),
                    'menu_order' => array(),
                    'submenu_order' => array(),
                );

                update_option(WPCA_SETTINGS_KEY, $default_settings);
            }
            
            // 刷新重写规则
            flush_rewrite_rules();

            // 更新插件版本
            update_option('wpca_plugin_version', WPCA_VERSION);

        }
    } catch (Exception $e) {
        error_log(sprintf('[WP Clean Admin] 激活错误: %s', $e->getMessage()));
    }
}
register_activation_hook(__FILE__, 'wpca_activate');

/**
 * 插件卸载钩子
 */
function wpca_uninstall() {
    // 清理插件设置
    delete_option(WPCA_SETTINGS_KEY);
    delete_option('wpca_plugin_version'); // 删除版本选项

    // 获取角色并删除插件添加的权限
    if (class_exists('WP_Roles')) {
        $wp_roles = new WP_Roles();
        foreach ($wp_roles->roles as $role_name => $role_data) {
            $role = get_role($role_name);
            if ($role) {
                $role->remove_cap('wpca_view_settings');
                $role->remove_cap('wpca_manage_menus');
                $role->remove_cap('wpca_manage_all');
            }
        }
    }
}
register_uninstall_hook(__FILE__, 'wpca_uninstall');

/**
 * 添加设置链接到插件页面
 */
function wpca_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=wp_clean_admin') . '">' . __('设置', 'wp-clean-admin') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wpca_add_settings_link');

// 在所有文件加载后初始化插件
add_action('plugins_loaded', 'wpca_run_plugin');