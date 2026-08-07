<?php
/**
 * PHPUnit bootstrap file
 *
 * 在 CLI 环境下加载插件 autoloader（含 WordPress 函数 stub），
 * 使模块类可在非 WordPress 运行时被实例化与测试。
 *
 * @package WPCleanAdmin
 */

$plugin_root = dirname( __DIR__ );

// 加载 PSR-4 autoloader（无 ABSPATH 时会自动定义 WP 函数 stub）
require_once $plugin_root . '/includes/autoload.php';
