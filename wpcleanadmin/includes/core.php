<?php
/**
 * WP Clean Admin 核心功能
 */

// 确保在WordPress环境中运行
if (!defined('WP_CLEAN_ADMIN_PATH')) {
    die;
}

// 初始化插件
add_action('plugins_loaded', function() {
    // 加载多语言
    load_plugin_textdomain(
        'wp-clean-admin',
        false,
        dirname(plugin_basename(WP_CLEAN_ADMIN_FILE)) . '/languages/'
    );
    
    // 注册其他模块
    if (is_admin()) {
        // 加载样式和脚本
        add_action('admin_enqueue_scripts', function() {
            // 主样式文件
            wp_enqueue_style(
                'wp-clean-admin',
                plugins_url('assets/css/wp-clean-admin.css', WP_CLEAN_ADMIN_FILE),
                [],
                WP_CLEAN_ADMIN_VERSION
            );
            
            // 菜单切换样式
            wp_enqueue_style(
                'wp-clean-admin-menu-toggle',
                plugins_url('assets/css/menu-toggle.css', WP_CLEAN_ADMIN_FILE),
                ['wp-clean-admin'],
                WP_CLEAN_ADMIN_VERSION
            );
            
            // 菜单排序样式
            wp_enqueue_style(
                'wp-clean-admin-menu',
                plugins_url('assets/css/menu.css', WP_CLEAN_ADMIN_FILE),
                ['wp-clean-admin'],
                WP_CLEAN_ADMIN_VERSION
            );
            
            // 管理界面样式
            wp_enqueue_style(
                'wp-clean-admin-settings',
                plugins_url('assets/css/admin.css', WP_CLEAN_ADMIN_FILE),
                ['wp-clean-admin'],
                WP_CLEAN_ADMIN_VERSION
            );
            
            // 加载jQuery UI
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-tabs');
            
            // 加载管理界面脚本
            wp_enqueue_script(
                'wp-clean-admin-admin',
                plugins_url('assets/js/admin.js', WP_CLEAN_ADMIN_FILE),
                ['jquery', 'jquery-ui-sortable', 'jquery-ui-tabs'],
                WP_CLEAN_ADMIN_VERSION,
                true
            );
        });
        
        // 加载功能模块
        require_once __DIR__ . '/admin/settings-page.php';  // 设置页面模块
        require_once __DIR__ . '/admin/menu-manager.php';
        require_once __DIR__ . '/admin/performance.php';
    }
});