<?php

/**
 * Plugin Name: WP Clean Admin
 * Plugin URI: https://github.com/sutchan/WP-Clean-Admin
 * Description: Simplifies and optimizes the WordPress admin interface, providing a cleaner backend experience with database optimization capabilities.
 * Version: 1.7.15
 * Author: Sut
 * Author URI: https://github.com/sutchan/
 * License: GPLv2 or later
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 * 
 * @file wpcleanadmin/wp-clean-admin.php
 * @version 1.7.15
 * @updated 2025-06-18
 */

// Exit if accessed directly
// defined閺勭枿HP鐠囶叀鈻堢紒鎾寸€敍灞肩瑝闂団偓鐟曚公unction_exists濡偓閺?if (! defined('ABSPATH')) {
    // exit閺勭枿HP鐠囶叀鈻堢紒鎾寸€敍灞肩瑝闂団偓鐟曚公unction_exists濡偓閺?    exit;
}

// 鐎瑰鍙忛崷鏉跨暰娑斿褰冩禒璺虹埗闁?if (! defined('WPCA_VERSION')) {
    define('WPCA_VERSION', '1.7.15');
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
        // 鐎瑰鍙忔径鍕倞娑撶粯婧€閸氬秴寮弫?        $host = isset($_SERVER['HTTP_HOST']) && is_string($_SERVER['HTTP_HOST']) ? 
            (function_exists('sanitize_text_field') ? sanitize_text_field($_SERVER['HTTP_HOST']) : filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_STRING)) : 'localhost';
        $url = 'http://' . $host;
        if ($path) {
            $url = trailingslashit($url) . (function_exists('ltrim') ? ltrim($path, '/') : $path);
        }
        return $url;
    }
}

// Include core functions
// file_exists閺勭枿HP閸愬懐鐤嗛崙鑺ユ殶閿涘苯褰叉禒銉ョ暔閸忋劋濞囬悽?if (defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php')) {
    require_once WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php';
}

// Include helper class for logging functionality
if (defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'includes/class-wpca-helpers.php')) {
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-helpers.php';
}

// Include the autoloader
// file_exists閺勭枿HP閸愬懐鐤嗛崙鑺ユ殶閿涘苯褰叉禒銉ョ暔閸忋劋濞囬悽?if (defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'includes/autoload.php')) {
    require_once WPCA_PLUGIN_DIR . 'includes/autoload.php';
}

// Include input validator class
if (defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'includes/class-wpca-input-validator.php')) {
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-input-validator.php';
}

// Include security audit class
if (defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'includes/class-wpca-security-audit.php')) {
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-security-audit.php';
}

// Performance optimization classes will be loaded automatically by the autoloader when needed

// Performance Settings will be loaded automatically by the autoloader when needed

// Include Performance Tests (only in development environment)
// file_exists閺勭枿HP閸愬懐鐤嗛崙鑺ユ殶閿涘苯褰叉禒銉ョ暔閸忋劋濞囬悽?if ((defined('WP_DEBUG') && WP_DEBUG) && defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'includes/tests/test-wpca-performance.php')) {
    require_once(WPCA_PLUGIN_DIR . 'includes/tests/test-wpca-performance.php');
}

/**
 * 閸掓繂顫愰崠鏍ㄥ絻娴犲墎绮嶆禒? */
function wpca_initialize_plugin()
{
    // 閸掓繂顫愰崠鏍ㄥ絻娴犲墎绮嶆禒?    global $wpca_settings, $wpca_menu_customizer, $wpca_permissions,
        $wpca_ajax, $wpca_cleanup, $wpca_dashboard, $wpca_login, $wpca_user_roles, $wpca_security_audit,
        $wpca_database, $wpca_database_settings;

    // 濡偓閺屻儲妲搁崥锔芥箒class_exists閸戣姤鏆熼崣顖滄暏
    // class_exists閺勭枿HP閸愬懐鐤嗛崙鑺ユ殶閿涘苯褰叉禒銉ョ暔閸忋劋濞囬悽?    // 閸掓稑缂撹箛鍛邦洣閻ㄥ嫮琚€圭偘绶ラ敍宀€鈥樻穱婵堣鐎涙ê婀幍宥呯杽娓氬瀵?    if (class_exists('WPCA_Permissions')) {
        $wpca_permissions = new WPCA_Permissions();
    }

    if (class_exists('WPCA_Settings')) {
        try {
            $wpca_settings = new WPCA_Settings();
        } catch (Exception $e) {
            // 闂堟瑩绮幑鏇″箯瀵倸鐖堕敍宀勪缉閸忓秵褰冩禒璺虹┛濠?        }
    }

    if (class_exists('WPCA_Menu_Customizer')) {
        try {
            $wpca_menu_customizer = new WPCA_Menu_Customizer();
        } catch (Exception $e) {
            // 闂堟瑩绮幑鏇″箯瀵倸鐖堕敍宀勪缉閸忓秵褰冩禒璺虹┛濠?        }
    }

    // 閸掓繂顫愰崠鏍閸旂姷绮嶆禒?    if (class_exists('WPCA_AJAX')) {
        try {
            $wpca_ajax = new WPCA_AJAX();
        } catch (Exception $e) {
            // 闂堟瑩绮幑鏇″箯瀵倸鐖堕敍宀勪缉閸忓秵褰冩禒璺虹┛濠?        }
    }

    if (class_exists('WPCA_Cleanup')) {
        try {
            $wpca_cleanup = new WPCA_Cleanup();
        } catch (Exception $e) {
            // 闂堟瑩绮幑鏇″箯瀵倸鐖堕敍宀勪缉閸忓秵褰冩禒璺虹┛濠?        }
    }

    if (class_exists('WPCA_Dashboard')) {
        try {
            $wpca_dashboard = new WPCA_Dashboard();
        } catch (Exception $e) {
            // 闂堟瑩绮幑鏇″箯瀵倸鐖堕敍宀勪缉閸忓秵褰冩禒璺虹┛濠?        }
    }

    if (class_exists('WPCA_Login')) {
        try {
            $wpca_login = new WPCA_Login();
        } catch (Exception $e) {
            // 闂堟瑩绮幑鏇″箯瀵倸鐖堕敍宀勪缉閸忓秵褰冩禒璺虹┛濠?        }
    }

    if (class_exists('WPCA_User_Roles')) {
        try {
            $wpca_user_roles = new WPCA_User_Roles();
        } catch (Exception $e) {
            // 闂堟瑩绮幑鏇″箯瀵倸鐖堕敍宀勪缉閸忓秵褰冩禒璺虹┛濠?        }
    }

    // 閸掓繂顫愰崠鏍х暔閸忋劌顓哥拋锛勭矋娴?    if (class_exists('WPCA_Security_Audit')) {
        try {
            $wpca_security_audit = new WPCA_Security_Audit();
        } catch (Exception $e) {
            // 闂堟瑩绮幑鏇″箯瀵倸鐖堕敍宀勪缉閸忓秵褰冩禒璺虹┛濠?        }
    }

    // 閸掓繂顫愰崠鏍х安閺佹澘绨辩紓鏍枱缂佸嫪娆?    if (class_exists('WPCA_Database')) {
        try {
            $wpca_database = WPCA_Database::get_instance();
        } catch (Exception $e) {
            // 闂堟瑩绮幑鏇″箯瀵倸鐖堕敍宀勪缉閸忓秵褰冩禒璺虹┛濠?        }
    }

    if (class_exists('WPCA_Database_Settings')) {
        try {
            $wpca_database_settings = new WPCA_Database_Settings();
        } catch (Exception $e) {
            // 闂堟瑩绮幑鏇″箯瀵倸鐖堕敍宀勪缉閸忓秵褰冩禒璺虹┛濠?        }
    }

    // 閻犱礁澧介悿鍡橆渶濡鍚囬柡澶婂暣濡炬椽鏁嶅畝鈧垾妯荤┍濠靛棭鍤犻悹鐐┾偓宕囨憼闁革负鍔岄幏浼村棘鐟欏嫮銆婇悗娑櫭﹢?    if (isset($wpca_permissions)) {
        if (method_exists($wpca_permissions, 'set_default_permissions')) {
            try {
                $wpca_permissions->set_default_permissions();
            } catch (Exception $e) {
                // 闂傚牊鐟╃划顖炲箲閺団€崇鐎殿喖鍊搁悥鍫曟晬瀹€鍕級闁稿繐绉佃ぐ鍐╃鐠鸿櫣鈹涙繝?
            }
        }
    }
}

// 婵炲鍔岄崬浠嬪箵閹哄秵顐介柛鎺撶箓椤劙宕?
// Provide fallback implementation for add_action if not exists
if (! function_exists('add_action')) {
    function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        // This is a dummy implementation
        return true;
    }
}

add_action('plugins_loaded', 'wpca_initialize_plugin', 10);

// file_exists閺勭枿HP閸愬懐鐤嗛崙鑺ユ殶閿涘苯褰叉禒銉ョ暔閸忋劋濞囬悽?if (WP_DEBUG && defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'translation-debug.php')) {
    include_once WPCA_PLUGIN_DIR . 'translation-debug.php';
}

// 閸旂姾娴囨径姘愁嚔鐟封偓閺€顖涘瘮缁?// require_once閺勭枿HP鐠囶叀鈻堢紒鎾寸€敍灞肩瑝闂団偓鐟曚礁鍤遍弫鏉跨摠閸︺劍鈧勵梾閺?
// file_exists闁哄嫮鏋縃P闁告劕鎳愰悿鍡涘礄閼恒儲娈堕柨娑樿嫰瑜板弶绂掗妷銉ф殧闁稿繈鍔嬫繛鍥偨?
if (defined('WPCA_PLUGIN_DIR') && file_exists(WPCA_PLUGIN_DIR . 'includes/class-wpca-i18n.php')) {
    require_once WPCA_PLUGIN_DIR . 'includes/class-wpca-i18n.php';
    // 闁告帗绻傞～鎰板礌閺嵮屾▼閻犲浂鍙€閳诲牓寮ㄩ娑樼槷
    if (class_exists('WPCA_i18n') && method_exists('WPCA_i18n', 'get_instance')) {
        try {
            WPCA_i18n::get_instance();
        } catch (Exception $e) {
            // 闂傚牊鐟╃划顖炲箲閺団€崇鐎殿喖鍊搁悥鍫曟晬瀹€鍕級闁稿繐绉佃ぐ鍐╃鐠鸿櫣鈹涙繝?
        }
    }
}

function wpca_load_textdomain()
{
    // 闁圭粯鍔掔欢浣冪疀閸涢偊娲ｉ柛鎴ｅГ閺嗙喖鎯冮崟顐У闁活潿鍔岄悿鍕偝?
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
                // 闂傚牊鐟╃划顖炲箲閺団€崇鐎殿喖鍊搁悥鍫曟晬鐏炵厧鈻忛柣顫妼椤︻剟鎮介妸銊х唴鐎?
                try {
                    load_plugin_textdomain('wp-clean-admin', false, 'wpcleanadmin/languages/');
                } catch (Exception $e) {
                    // 闂傚牊鐟╃划顖炲箲閺団€崇鐎殿喖鍊搁悥鍫曟晬瀹€鍕級闁稿繐绉佃ぐ鍐╃鐠鸿櫣鈹涙繝?
                }
            }
        } else {
            try {
                load_plugin_textdomain('wp-clean-admin', false, 'wpcleanadmin/languages/');
            } catch (Exception $e) {
                // 闂傚牊鐟╃划顖炲箲閺団€崇鐎殿喖鍊搁悥鍫曟晬瀹€鍕級闁稿繐绉佃ぐ鍐╃鐠鸿櫣鈹涙繝?
            }
        }
    }
}
add_action('plugins_loaded', 'wpca_load_textdomain');

function wpca_load_admin_resources()
{
    global $wpca_admin_data;

    // 闁圭粯鍔掔欢浣冪疀閸涢偊娲ｉ柛鎴ｅГ閺嗙喖鎯冮崟顐У闁活潿鍔岄悿鍕偝?
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
    // 闁圭粯鍔掔欢浣冪疀閸涢偊娲ｉ柛鎴ｅГ閺嗙喖鎯冮崟顐У闁活潿鍔岄悿鍕偝?
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

    // is_array闁哄嫮鏋縃P闁告劕鎳愰悿鍡涘礄閼恒儲娈堕柨娑樿嫰瑜板弶绂掗妷銉ф殧闁稿繈鍔嬫繛鍥偨?

    // 閻庣懓顦崣蹇涘捶閹峰苯绠柛娆愮墪閹蜂即寮寸€涙ɑ鐓€闂侇偄顦甸妴?    $wpca_settings = get_option('wpca_settings');

    if (! $wpca_settings) {
        // 閻犱礁澧介悿鍡橆渶濡鍚囬梺鏉跨Ф閻?        $default_settings = array(
            'version'             => defined('WPCA_VERSION') ? WPCA_VERSION : '1.7.15',
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
        // 闁哄洤鐡ㄩ弻濠囨偋閸喐鎷遍柛?
        $wpca_settings['version'] = defined('WPCA_VERSION') ? WPCA_VERSION : '1.7.15';
        update_option('wpca_settings', $wpca_settings);
    }

    // 濞寸姴鎳庡﹢鐚坥rdPress闁告帗绻傞～鎰板礌閺嵮勫€甸柛鎺楁敱閺屽﹪鏌屽鍛櫢閻熸瑥瀚崹?    if (defined('ABSPATH') && ABSPATH) {
        // 鐎点倖鍎肩换婊堝礆闁垮鐓€闂佹彃绉撮崯鎾舵喆閸曨偄鐏熼柨娑樼焸娴尖晠宕楀鍛含婵犵鍋撴繛鎻掝煼閹割剛鈧稒鍔掗懙鎴︽儎鐎涙ê澶嶉悹瀣暟閺併倗鈧絻澹堥崵褔鎯冮崟顖涳紪濡?
        if (function_exists('add_action')) {
            add_action('init', function () {
                flush_rewrite_rules();
            }, 100);
        } else {
            // 濠碘€冲€归悘澶嬬▔瀹ュ牆鍘村ù锝堟硶閺併倝鏌﹂埡浣烘憤闁挎稑鏈晶鐘绘儎鐎涙ê澶嶉悹瀣暟閺?            flush_rewrite_rules();
        }
    }
}
register_activation_hook(__FILE__, 'wpca_activate_plugin');

function wpca_deactivate_plugin()
{
    // 闁圭粯鍔掔欢浣冪疀閸涢偊娲ｉ柛鎴ｅГ閺嗙喖鎯冮崟顐У闁活潿鍔岄悿鍕偝?
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

    // 婵炴挸鎳愰幃濠囧级閸愵喗顎?    if (class_exists('WPCA_Permissions')) {
        $permissions = new WPCA_Permissions();
        if (method_exists($permissions, 'cleanup_capabilities')) {
            $permissions->cleanup_capabilities();
        }
    }

    // 濞寸姴鎳庡﹢鐚坥rdPress闁告帗绻傞～鎰板礌閺嵮勫€靛☉鎾存煥閸ら亶寮弶璺ㄦ憼闁革负鍔嶅鍌炲礆闁垮鐓€闂佹彃绉撮崯鎾舵喆閸曨偄鐏?    if (function_exists('flush_rewrite_rules') && defined('ABSPATH') && ABSPATH) {
        // 鐎点倖鍎肩换婊堝礆闁垮鐓€闂佹彃绉撮崯鎾舵喆閸曨偄鐏熼柨娑樼焸娴尖晠宕楀鍛含闁稿绮庨弫銈夋煢閳轰胶鎽嶅☉鎿冨幘濞插潡骞掗妷銊ф闁活潿鍔岄閬嶆嚊鐎靛憡鐣遍梻鍌ゅ櫍椤?        if (function_exists('add_action')) {
            add_action('init', function () {
                if (function_exists('flush_rewrite_rules')) {
                    flush_rewrite_rules();
                }
            }, 100);
        } else {
            // 濠碘€冲€归悘澶嬬▔瀹ュ牆鍘村ù锝堟硶閺併倝鏌﹂埡浣烘憤闁挎稑鏈晶鐘绘儎鐎涙ê澶嶉悹瀣暟閺?            flush_rewrite_rules();
        }
    }
}
register_deactivation_hook(__FILE__, 'wpca_deactivate_plugin');

function wpca_add_settings_link($links)
{
    // 闁圭粯鍔掔欢浣冪疀閸涢偊娲ｉ柛鎴ｅГ閺嗙喖鎯冮崟顐У闁活潿鍔岄悿鍕偝?
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

    // 缁绢収鍠曠换姘跺捶閳烘悐rdPress闁绘粠鍨伴。銊︾▔椤撶偟鏆旈柛蹇嬪姀缁诲秶鎮?
    if (function_exists('admin_url') && function_exists('array_unshift')) {
        // 閻庣懓顦崣蹇涘捶閺夋妲遍柣?links闁告瑥鍊归弳?        if (! is_array($links)) {
            $links = array();
        }

        $settings_link = '<a href="' . admin_url('admin.php?page=wp-clean-admin') . '">' . __('Settings', 'wp-clean-admin') . '</a>';
        array_unshift($links, $settings_link);
    }

    // 缁绢収鍠曠换姘交閺傛寧绀€闁稿﹤鍚嬪Σ鎼佸极閹殿喚鐭?    return is_array($links) ? $links : array();
}
add_filter('plugin_action_links_' . (defined('WPCA_BASENAME') ? WPCA_BASENAME : 'wp-clean-admin.php'), 'wpca_add_settings_link');

// 婵炲鍔嶉崜浼存晬鏀粹偓pca_initialize_components闁告垼濮ら弳鐔奉啅閼奸娼秝pca_initialize_plugin闁告垼濮ら弳鐔煎即婢剁鏁?// 濞ｅ洦绻勯弳鈧慨婵勫€栭弫鐐烘煂婵犱椒绨伴梺顒€鐏濋崢銈吳庨柨瀣攱闁挎稑濂旂徊鐐▔瀹ュ懎鏅欓柟绗涘棭鏀介梺鎻掔Т椤︽煡鎯冮崟顐㈢仴濠殿喖顑呯€垫煡骞欏鍕▕