<?php
/**
 * 测试引导文件
 */

// 定义WordPress测试环境
define('WP_TESTS_DIR', '/path/to/wordpress/tests/phpunit');
define('WP_ROOT_DIR', '/path/to/wordpress');

// 加载WordPress测试框架
require_once WP_TESTS_DIR . '/includes/functions.php';

// 设置插件常量
define('WP_CLEAN_ADMIN_FILE', dirname(__DIR__) . '/wpcleanadmin/wp-clean-admin.php');

// 加载WordPress
tests_add_filter('muplugins_loaded', function() {
    require WP_CLEAN_ADMIN_FILE;
});

// 启动WordPress测试环境
require WP_TESTS_DIR . '/includes/bootstrap.php';