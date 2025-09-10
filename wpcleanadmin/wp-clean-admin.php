<?php
/**
 * Plugin Name: WP Clean Admin
 * Description: WordPress后台清理优化插件
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 */

// 安全检测
if (!defined('ABSPATH')) {
    exit; // 禁止直接访问
}

// 确保WordPress环境
if (!function_exists('add_action')) {
    die('This plugin requires WordPress');
}

// 定义插件常量
define('WP_CLEAN_ADMIN_VERSION', '1.0.0');
define('WP_CLEAN_ADMIN_FILE', __FILE__);
define('WP_CLEAN_ADMIN_PATH', plugin_dir_path(WP_CLEAN_ADMIN_FILE));

// 加载核心功能
require_once WP_CLEAN_ADMIN_PATH . 'includes/core.php';

// 添加插件设置链接
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    // 添加设置链接
    $settings_link = '<a href="' . admin_url('options-general.php?page=wp-clean-admin') . '">' . 
                     __('设置', 'wp-clean-admin') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});

// 添加插件元数据
add_filter('plugin_row_meta', function($links, $file) {
    if (plugin_basename(__FILE__) === $file) {
        $row_meta = array(
            'docs' => '<a href="' . esc_url('https://example.com/docs/') . '" target="_blank">' . 
                      __('文档', 'wp-clean-admin') . '</a>',
            'support' => '<a href="' . esc_url('https://example.com/support/') . '" target="_blank">' . 
                         __('支持', 'wp-clean-admin') . '</a>'
        );
        return array_merge($links, $row_meta);
    }
    return $links;
}, 10, 2);