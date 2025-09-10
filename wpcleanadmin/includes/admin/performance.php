<?php
/**
 * 性能优化模块
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Clean_Admin_Performance {
    private static $instance;
    
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'optimize_assets']);
    }
    
    public function optimize_assets() {
        global $wp_styles, $wp_scripts;
        
        // 只禁用特定的脚本，保留核心功能
        // wp_deregister_script('jquery-ui-core'); // 保留，菜单排序需要
        // wp_deregister_script('jquery-ui-tabs'); // 保留，可能被其他功能使用
        
        // 保留核心WordPress样式，只处理冲突的样式
        // wp_deregister_style('dashicons'); // 保留，WordPress图标需要
        // wp_deregister_style('wp-admin'); // 保留，核心后台样式
        
        // 确保不加载不存在的admin.css（处理不同可能的注册名称）
        wp_deregister_style('admin.css');
        wp_dequeue_style('admin.css');
        wp_deregister_style('admin');
        wp_dequeue_style('admin');
        wp_deregister_style('inc/admin/css/admin.css');
        wp_dequeue_style('inc/admin/css/admin.css');
        
        // 获取用户设置的白名单
        $allowed_scripts = get_option('wpca_allowed_scripts', ['jquery', 'wp-api', 'wp-util']);
        $allowed_styles = get_option('wpca_allowed_styles', ['admin-bar', 'common']);
        
        // 添加核心样式到白名单，确保后台正常显示
        $core_styles = ['wp-admin', 'dashicons', 'common', 'forms', 'admin-menu', 'dashboard', 
                       'list-tables', 'edit', 'revisions', 'media', 'themes', 'about', 'nav-menus',
                       'widgets', 'site-icon', 'l10n', 'wp-auth-check'];
        $allowed_styles = array_merge($allowed_styles, $core_styles);
        
        // 添加核心脚本到白名单
        $core_scripts = ['jquery', 'jquery-core', 'jquery-migrate', 'jquery-ui-core', 
                        'jquery-ui-sortable', 'wp-util', 'wp-api', 'admin-bar'];
        $allowed_scripts = array_merge($allowed_scripts, $core_scripts);
        
        // 只清理非核心脚本
        foreach ($wp_scripts->registered as $handle => $script) {
            // 跳过核心脚本和白名单脚本
            if (!in_array($handle, $allowed_scripts) && 
                !strpos($handle, 'wp-') === 0 && 
                !strpos($handle, 'admin-') === 0) {
                wp_dequeue_script($handle);
            }
        }
        
        // 只清理非核心样式
        foreach ($wp_styles->registered as $handle => $style) {
            // 跳过核心样式和白名单样式
            if (!in_array($handle, $allowed_styles) && 
                !strpos($handle, 'wp-') === 0 && 
                !strpos($handle, 'admin-') === 0) {
                wp_dequeue_style($handle);
            }
        }
    }
}

// 初始化性能优化模块
WP_Clean_Admin_Performance::get_instance();