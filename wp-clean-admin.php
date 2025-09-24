<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI:  https://example.com/wp-clean-admin/
 * Description: 简化和美化 WordPress 后台管理界面，提供更清爽的用户体验。
 * Version:     1.1.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * License:     GPL2+
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 *
 * @package WPCleanAdmin
 */

// 如果直接访问则退出
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('WPCA_VERSION', '1.1.0');
define('WPCA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPCA_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * WP Clean Admin 主类
 */
class WPCleanAdmin {

    /**
     * 插件实例
     *
     * @var WPCleanAdmin
     */
    private static $instance = null;

    /**
     * 设置对象
     *
     * @var WPCA_Settings
     */
    public $settings = null;

    /**
     * 获取插件实例
     *
     * @return WPCleanAdmin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 构造函数
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * 加载依赖文件
     */
    private function load_dependencies() {
        $files = [
            'class-wpca-settings.php',
            'class-wpca-menu-customizer.php',
            'class-wpca-ajax.php',
            'class-wpca-permissions.php',
            'class-wpca-user-roles.php'
        ];
        
        foreach ($files as $file) {
            $file = basename($file); // 确保文件名安全
            $path = plugin_dir_path(__FILE__) . 'includes/' . $file;
            if (file_exists($path)) {
                require_once $path;
            } else {
                error_log("[WP Clean Admin] File not found: $path");
            }
        }
    }

    /**
     * 初始化钩子
     */
    public function init_hooks() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * 插件初始化
     */
    public function init() {
        // 加载文本域
        load_plugin_textdomain('wp-clean-admin', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // 初始化设置
        $this->settings = new WPCA_Settings();
        
        // 后台管理功能
        new WPCA_Menu_Customizer();
        
        // 权限管理
        new WPCA_Permissions();
        
        // 用户角色管理
        new WPCA_User_Roles();
        
        // AJAX 处理
        new WPCA_Ajax();
    }

    /**
     * 插件激活时执行
     */
    public function activate() {
        // 设置默认选项
        if ($this->settings) {
            $this->settings->set_defaults();
        }
        
        // 延迟刷新重写规则
        add_action('init', function() {
            try {
                flush_rewrite_rules();
            } catch (Exception $e) {
                error_log('[WP Clean Admin] Failed to flush rewrite rules: ' . $e->getMessage());
            }
        });
    }

    /**
     * 插件停用时执行
     */
    public function deactivate() {
        // 停用时无需刷新重写规则
    }
}

/**
 * 获取插件主类实例
 *
 * @return WPCleanAdmin
 */
function wp_clean_admin() {
    return WPCleanAdmin::get_instance();
}

// 启动插件
wp_clean_admin();