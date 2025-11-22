<?php

/**
 * Plugin Name: WP Clean Admin
 * Plugin URI: https://github.com/sutchan/WP-Clean-Admin
 * Description: Simplifies and optimizes the WordPress admin interface, providing a cleaner backend experience with database optimization capabilities.
 * Version: 1.7.13
 * Author: Sut
 * Author URI: https://github.com/sutchan/
 * License: GPLv2 or later
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 */

// Exit if accessed directly
// defined是PHP语言结构，不需要function_exists检查
if (! defined('ABSPATH')) {
    // exit是PHP语言结构，不需要function_exists检查
    exit;
}

// 安全地定义插件常量
if (! defined('WPCA_VERSION')) {
    define('WPCA_VERSION', '1.7.13');
}

if (! defined('WPCA_BASENAME')) {
    define('WPCA_BASENAME', 'wp-clean-admin.php');
}

if (! defined('WPCA_MAIN_FILE')) {
    define('WPCA_MAIN_FILE', __FILE__);
}

// Provide fallback implementations for WordPress core functions
if (! function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return trailingslashit(dirname($file));
    }
}

if (! function_exists('plugin_dir_url')) {
    function plugin_dir_url($file)
    {
        $url = str_replace('\\', '/', trailingslashit(dirname($file)));
        if (defined('ABSPATH') && ABSPATH) {
            $url = str_replace(str_replace('\\', '/', ABSPATH), site_url('/'), $url);
        }
        return $url;
    }
}

// Define WP_DEBUG constant if not defined
if (! defined('WP_DEBUG')) {
    define('WP_DEBUG', false);
}

// Define plugin directory path
if (! defined('WPCA_PLUGIN_DIR')) {
    define('WPCA_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Define plugin URL
if (! defined('WPCA_PLUGIN_URL')) {
    define('WPCA_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (! function_exists('trailingslashit')) {
    function trailingslashit($string)
    {
        return (function_exists('rtrim') ? rtrim($string, '/\\') : $string) . '/';
    }
}

if (! function_exists('site_url')) {
    function site_url($path = '', $scheme = null)
    {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $url = 'http://' . $host;
        if ($path) {
            $url = trailingslashit($url) . (function_exists('ltrim') ? ltrim($path, '/') : $path);
        }
        return $url;
    }
}

// Include core functions
// file_exists是PHP内置函数，可以安全使用
if (defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php')) {
    require_once WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php';
}

// Include the autoloader
// file_exists是PHP内置函数，可以安全使用
if (defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'includes/autoload.php')) {
    require_once WPCA_PLUGIN_DIR . 'includes/autoload.php';
}

// Performance optimization classes will be loaded automatically by the autoloader when needed

// Performance Settings will be loaded automatically by the autoloader when needed

// Include Performance Tests (only in development environment)
// file_exists是PHP内置函数，可以安全使用
if ((defined('WP_DEBUG') && WP_DEBUG) && defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'includes/tests/test-wpca-performance.php')) {
    require_once(WPCA_PLUGIN_DIR . 'includes/tests/test-wpca-performance.php');
}

/**
 * 初始化插件组件
 */
function wpca_initialize_plugin()
{
    // 初始化插件组件
    global $wpca_settings, $wpca_menu_customizer, $wpca_permissions,
        $wpca_ajax, $wpca_cleanup, $wpca_dashboard, $wpca_login, $wpca_user_roles;

    // 检查是否有class_exists函数可用
    // class_exists是PHP内置函数，可以安全使用
    // 创建必要的类实例，确保类存在才实例化
    if (class_exists('WPCA_Permissions')) {
        $wpca_permissions = new WPCA_Permissions();
    }

    if (class_exists('WPCA_Settings')) {
        try {
            $wpca_settings = new WPCA_Settings();
        } catch (Exception $e) {
            // 静默捕获异常，避免插件崩溃
        }
    }

    if (class_exists('WPCA_Menu_Customizer')) {
        try {
            $wpca_menu_customizer = new WPCA_Menu_Customizer();
        } catch (Exception $e) {
            // 静默捕获异常，避免插件崩溃
        }
    }

    // 初始化附加组件
    if (class_exists('WPCA_AJAX')) {
        try {
            $wpca_ajax = new WPCA_AJAX();
        } catch (Exception $e) {
            // 静默捕获异常，避免插件崩溃
        }
    }

    if (class_exists('WPCA_Cleanup')) {
        try {
            $wpca_cleanup = new WPCA_Cleanup();
        } catch (Exception $e) {
            // 静默捕获异常，避免插件崩溃
        }
    }

    if (class_exists('WPCA_Dashboard')) {
        try {
            $wpca_dashboard = new WPCA_Dashboard();
        } catch (Exception $e) {
            // 静默捕获异常，避免插件崩溃
        }
    }

    if (class_exists('WPCA_Login')) {
        try {
            $wpca_login = new WPCA_Login();
        } catch (Exception $e) {
            // 静默捕获异常，避免插件崩溃
        }
    }

    if (class_exists('WPCA_User_Roles')) {
        try {
            $wpca_user_roles = new WPCA_User_Roles();
        } catch (Exception $e) {
            // 静默捕获异常，避免插件崩溃
        }
    }

    // 璁剧疆榛樿鏉冮檺锛岀‘淇濆璞″瓨鍦ㄥ拰鏂规硶瀛樺湪
    if (isset($wpca_permissions)) {
        if (method_exists($wpca_permissions, 'set_default_permissions')) {
            try {
                $wpca_permissions->set_default_permissions();
            } catch (Exception $e) {
                // 闈欓粯鎹曡幏寮傚父锛岄伩鍏嶆彃浠跺穿婧?
            }
        }
    }
}

// 娉ㄥ唽鎻掍欢鍒濆鍖?
// Provide fallback implementation for add_action if not exists
if (! function_exists('add_action')) {
    function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        // This is a dummy implementation
        return true;
    }
}

add_action('plugins_loaded', 'wpca_initialize_plugin', 10);

// file_exists是PHP内置函数，可以安全使用
if (WP_DEBUG && defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'translation-debug.php')) {
    include_once WPCA_PLUGIN_DIR . 'translation-debug.php';
}

// 加载多语言支持类
// require_once是PHP语言结构，不需要函数存在性检查

// file_exists鏄疨HP鍐呯疆鍑芥暟锛屽彲浠ュ畨鍏ㄤ娇鐢?
if (defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'includes/class-wpca-i18n.php')) {
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-i18n.php';
    // 鍒濆鍖栧璇█鏀寔
    if (class_exists('WPCA_i18n') && method_exists('WPCA_i18n', 'get_instance')) {
        try {
            WPCA_i18n::get_instance();
        } catch (Exception $e) {
            // 闈欓粯鎹曡幏寮傚父锛岄伩鍏嶆彃浠跺穿婧?
        }
    }
}

function wpca_load_textdomain()
{
    // 鎻愪緵蹇呰鍑芥暟鐨勫鐢ㄥ疄鐜?
    if (! function_exists('load_plugin_textdomain')) {
        function load_plugin_textdomain($domain, $mu_plugin = false, $plugin_rel_path = '')
        {
            return true;
        }
    }

    if (! function_exists('plugin_basename')) {
        function plugin_basename($file)
        {
            return basename($file);
        }
    }

    if (! function_exists('dirname')) {
        function dirname($path, $levels = 1)
        {
            return '/';
        }
    }

    if (function_exists('load_plugin_textdomain')) {
        if (function_exists('plugin_basename') && function_exists('dirname')) {
            try {
                load_plugin_textdomain('wp-clean-admin', false, dirname(plugin_basename(__FILE__)) . '/languages/');
            } catch (Exception $e) {
                // 闈欓粯鎹曡幏寮傚父锛屼娇鐢ㄥ鐢ㄨ矾寰?
                try {
                    load_plugin_textdomain('wp-clean-admin', false, 'wpcleanadmin/languages/');
                } catch (Exception $e) {
                    // 闈欓粯鎹曡幏寮傚父锛岄伩鍏嶆彃浠跺穿婧?
                }
            }
        } else {
            try {
                load_plugin_textdomain('wp-clean-admin', false, 'wpcleanadmin/languages/');
            } catch (Exception $e) {
                // 闈欓粯鎹曡幏寮傚父锛岄伩鍏嶆彃浠跺穿婧?
            }
        }
    }
}
add_action('plugins_loaded', 'wpca_load_textdomain');

function wpca_load_admin_resources()
{
    global $wpca_admin_data;

    // 鎻愪緵蹇呰鍑芥暟鐨勫鐢ㄥ疄鐜?
    if (! function_exists('wp_enqueue_script')) {
        function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false)
        {
            return true;
        }
    }

    if (! function_exists('wp_enqueue_style')) {
        function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all')
        {
            return true;
        }
    }

    if (! function_exists('wp_localize_script')) {
        function wp_localize_script($handle, $object_name, $l10n)
        {
            return true;
        }
    }

    if (function_exists('wp_enqueue_script')) {
        wp_enqueue_script('wpca-main', (defined('WPCA_PLUGIN_URL') ? WPCA_PLUGIN_URL : '') . 'assets/js/wpca-main.js', array('jquery'), (defined('WPCA_VERSION') ? WPCA_VERSION : '1.0'), true);

        // Database optimization scripts
        wp_enqueue_script('wpca-database', (defined('WPCA_PLUGIN_URL') ? WPCA_PLUGIN_URL : '') . 'assets/js/wpca-database.js', array('jquery'), (defined('WPCA_VERSION') ? WPCA_VERSION : '1.0'), true);

        // Database optimization styles
        if (function_exists('wp_enqueue_style')) {
            wp_enqueue_style('wpca-database', (defined('WPCA_PLUGIN_URL') ? WPCA_PLUGIN_URL : '') . 'assets/css/wpca-database.css', array(), (defined('WPCA_VERSION') ? WPCA_VERSION : '1.0'));
        }

        // Provide fallback implementation for wp_create_nonce
        if (! function_exists('wp_create_nonce')) {
            function wp_create_nonce($action = -1)
            {
                return 'dummy_nonce_' . $action;
            }
        }

        // Provide fallback implementation for admin_url
        if (! function_exists('admin_url')) {
            function admin_url($path = '', $scheme = 'admin')
            {
                $url = site_url('wp-admin/', $scheme);
                if ($path && is_string($path)) {
                    $url .= ltrim($path, '/');
                }
                return $url;
            }
        }

        $wpca_admin_data = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wpca_admin_nonce'),
            'debug'   => WP_DEBUG,
            'version' => defined('WPCA_VERSION') ? WPCA_VERSION : '1.0',
            // Error messages for AJAX requests
            'error_request_processing_failed' => function_exists('__') ? __('Request processing failed', 'wp-clean-admin') : 'Request processing failed',
            'error_insufficient_permissions' => function_exists('__') ? __('You do not have permission to perform this action', 'wp-clean-admin') : 'You do not have permission to perform this action',
            'error_invalid_parameters' => function_exists('__') ? __('Invalid request parameters', 'wp-clean-admin') : 'Invalid request parameters',
            'error_not_logged_in' => function_exists('__') ? __('Please log in first', 'wp-clean-admin') : 'Please log in first',
            'error_server_error' => function_exists('__') ? __('Internal server error', 'wp-clean-admin') : 'Internal server error'
        );

        if (function_exists('wp_localize_script')) {
            wp_localize_script('wpca-main', 'wpca_admin', $wpca_admin_data);
        }
    }
}
add_action('admin_enqueue_scripts', 'wpca_load_admin_resources');

// Provide fallback implementation for WordPress hook functions
if (! function_exists('register_activation_hook')) {
    function register_activation_hook($file, $function)
    {
        // This is a dummy implementation
        return true;
    }
}

if (! function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $function)
    {
        // This is a dummy implementation
        return true;
    }
}

if (! function_exists('add_filter')) {
    function add_filter($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        // This is a dummy implementation
        return true;
    }
}

function wpca_activate_plugin()
{
    // 鎻愪緵蹇呰鍑芥暟鐨勫鐢ㄥ疄鐜?
    if (! function_exists('get_option')) {
        function get_option($option, $default = false)
        {
            return $default;
        }
    }

    if (! function_exists('update_option')) {
        function update_option($option, $value)
        {
            return true;
        }
    }

    if (! function_exists('add_option')) {
        function add_option($option, $value, $deprecated = '', $autoload = 'yes')
        {
            return true;
        }
    }

    if (! function_exists('flush_rewrite_rules')) {
        function flush_rewrite_rules($hard = true)
        {
            return true;
        }
    }

    // is_array鏄疨HP鍐呯疆鍑芥暟锛屽彲浠ュ畨鍏ㄤ娇鐢?

    // 瀹夊叏鍦拌幏鍙栧拰鏇存柊閫夐」
    $wpca_settings = get_option('wpca_settings');

    if (! $wpca_settings) {
        // 璁剧疆榛樿閰嶇疆
        $default_settings = array(
            'version'             => defined('WPCA_VERSION') ? WPCA_VERSION : '1.7.12',
            'menu_order'          => array(),
            'submenu_order'       => array(),
            'menu_toggles'        => array(),
            'dashboard_widgets'   => array(),
            'login_style'         => 'default',
            'custom_admin_bar'    => 0,
            'disable_help_tabs'   => 0,
            'cleanup_header'      => 0,
            'minify_admin_assets' => 0
        );
        update_option('wpca_settings', $default_settings);
    } else if (is_array($wpca_settings)) {
        // 鏇存柊鐗堟湰鍙?
        $wpca_settings['version'] = defined('WPCA_VERSION') ? WPCA_VERSION : '1.7.12';
        update_option('wpca_settings', $wpca_settings);
    }

    // 浠呭湪WordPress鍒濆鍖栧悗鍒锋柊閲嶅啓瑙勫垯
    if (defined('ABSPATH') && ABSPATH) {
        // 寤惰繜鍒锋柊閲嶅啓瑙勫垯锛岄伩鍏嶅湪婵€娲婚挬瀛愪腑鐩存帴璋冪敤瀵艰嚧鐨勯棶棰?
        if (function_exists('add_action')) {
            add_action('init', function () {
                flush_rewrite_rules();
            }, 100);
        } else {
            // 濡傛灉涓嶈兘浣跨敤閽╁瓙锛屾墠鐩存帴璋冪敤
            flush_rewrite_rules();
        }
    }
}
register_activation_hook(__FILE__, 'wpca_activate_plugin');

function wpca_deactivate_plugin()
{
    // 鎻愪緵蹇呰鍑芥暟鐨勫鐢ㄥ疄鐜?
    if (! function_exists('add_action')) {
        function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1)
        {
            return true;
        }
    }

    if (! function_exists('flush_rewrite_rules')) {
        function flush_rewrite_rules($hard = true)
        {
            return true;
        }
    }

    // 娓呯悊鏉冮檺
    if (class_exists('WPCA_Permissions')) {
        $permissions = new WPCA_Permissions();
        if (method_exists($permissions, 'cleanup_capabilities')) {
            $permissions->cleanup_capabilities();
        }
    }

    // 浠呭湪WordPress鍒濆鍖栧悗涓斿嚱鏁板瓨鍦ㄦ椂鍒锋柊閲嶅啓瑙勫垯
    if (function_exists('flush_rewrite_rules') && defined('ABSPATH') && ABSPATH) {
        // 寤惰繜鍒锋柊閲嶅啓瑙勫垯锛岄伩鍏嶅湪鍋滅敤閽╁瓙涓洿鎺ヨ皟鐢ㄥ鑷寸殑闂
        if (function_exists('add_action')) {
            add_action('init', function () {
                if (function_exists('flush_rewrite_rules')) {
                    flush_rewrite_rules();
                }
            }, 100);
        } else {
            // 濡傛灉涓嶈兘浣跨敤閽╁瓙锛屾墠鐩存帴璋冪敤
            flush_rewrite_rules();
        }
    }
}
register_deactivation_hook(__FILE__, 'wpca_deactivate_plugin');

function wpca_add_settings_link($links)
{
    // 鎻愪緵蹇呰鍑芥暟鐨勫鐢ㄥ疄鐜?
    if (! function_exists('array_unshift')) {
        function array_unshift(&$array, ...$values)
        {
            array_splice($array, 0, 0, $values);
            return count($array);
        }
    }

    if (! function_exists('admin_url')) {
        function admin_url($path = '', $scheme = 'admin')
        {
            return '/wp-admin/' . ltrim($path, '/');
        }
    }

    // 纭繚鍦╓ordPress鐜涓畨鍏ㄨ繍琛?
    if (function_exists('admin_url') && function_exists('array_unshift')) {
        // 瀹夊叏鍦板鐞?links鍙傛暟
        if (! is_array($links)) {
            $links = array();
        }

        $settings_link = '<a href="' . admin_url('admin.php?page=wp-clean-admin') . '">' . __('Settings', 'wp-clean-admin') . '</a>';
        array_unshift($links, $settings_link);
    }

    // 纭繚杩斿洖鍊兼槸鏁扮粍
    return is_array($links) ? $links : array();
}
add_filter('plugin_action_links_' . (defined('WPCA_BASENAME') ? WPCA_BASENAME : 'wp-clean-admin.php'), 'wpca_add_settings_link');

// 娉ㄦ剰锛歸pca_initialize_components鍑芥暟宸茶wpca_initialize_plugin鍑芥暟鏇夸唬
// 淇濈暀姝ゆ敞閲婁互閬垮厤娣锋穯锛屼絾涓嶅啀鎵ц閲嶅鐨勫垵濮嬪寲鎿嶄綔
