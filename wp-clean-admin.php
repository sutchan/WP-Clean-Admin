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
 */

// 确保安全运行，即使在WordPress环境未完全加载时
// Exit if accessed directly with proper function_exists check
if ( ! defined( 'ABSPATH' ) && ! function_exists( 'add_action' ) ) {
    // 定义一个简单的ABSPATH常量作为备用
    if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', dirname( __FILE__ ) . '/' );
    }
    // 如果没有WordPress环境，只加载必要的文件或提供基础功能
    // 但不要尝试注册钩子或执行WordPress特定的操作}

// 安全地定义插件常量if ( ! defined( 'WPCA_VERSION' ) ) {
	define( 'WPCA_VERSION', '1.7.13' );
}

if ( ! defined( 'WPCA_MAIN_FILE' ) ) {
	define( 'WPCA_MAIN_FILE', __FILE__ );
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