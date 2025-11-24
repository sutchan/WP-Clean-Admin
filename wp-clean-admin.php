<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI: https://github.com/sutchan/WP-Clean-Admin
 * Description: Simplifies and optimizes the WordPress admin interface, providing a cleaner backend experience.
 * Version: 1.7.13
 * Author: Sut
 * Author URI: https://github.com/sutchan/
 * License: GPLv2 or later
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 * 
 * @file wp-clean-admin.php
 * @version 1.7.13
 * @updated 2025-06-18
 */

// 确保安全运行，即使在WordPress环境未完全加载时
// Exit if accessed directly with proper function_exists check
if ( ! defined( 'ABSPATH' ) && ! function_exists( 'add_action' ) ) {
    // 定义一个简单的ABSPATH常量作为备用
    if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', dirname( __FILE__ ) . '/' );
    }
    // 如果没有WordPress环境，只加载必要的文件或提供基础功能
    // 但不要尝试注册钩子或执行WordPress特定的操作
}

// 安全地定义插件常量
if ( ! defined( 'WPCA_VERSION' ) ) {
	define( 'WPCA_VERSION', '1.7.13' );
}

if ( ! defined( 'WPCA_MAIN_FILE' ) ) {
	define( 'WPCA_MAIN_FILE', __FILE__ );
}

// 调试模式设置
if (!defined('WPCA_DEBUG')) {
    define('WPCA_DEBUG', (defined('WP_DEBUG') && WP_DEBUG));
}

// 审计日志设置
// 启用审计日志功能 (true/false)
if (!defined('WPCA_ENABLE_AUDIT_LOGS')) {
    define('WPCA_ENABLE_AUDIT_LOGS', true);
}

// 是否将审计日志保存到数据库 (true/false)
if (!defined('WPCA_SAVE_AUDIT_TO_DB')) {
    define('WPCA_SAVE_AUDIT_TO_DB', true);
}

// 审计日志保留时间（天）
if (!defined('WPCA_AUDIT_LOG_RETENTION_DAYS')) {
    define('WPCA_AUDIT_LOG_RETENTION_DAYS', 30);
}

// 安全地包含主插件文件
// 确保dirname函数存在且文件可访问
if ( function_exists( 'dirname' ) ) {
    $main_plugin_file = dirname( __FILE__ ) . '/wpcleanadmin/wp-clean-admin.php';
    if ( file_exists( $main_plugin_file ) ) {
        require_once $main_plugin_file;
    }
}
?>