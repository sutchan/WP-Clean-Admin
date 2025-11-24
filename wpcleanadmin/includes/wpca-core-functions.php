<?php
/**
 * WPCleanAdmin 核心功能函数
 *
 * @package WPCleanAdmin
 * @version 1.7.13
 * @file wpcleanadmin/includes/wpca-core-functions.php
 * @updated 2025-06-18
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 加载WordPress函数存根（仅在非WordPress环境中）
require_once __DIR__ . '/wpca-wordpress-stubs.php';

/**
 * 移除仪表板小部件
 */
function wpca_remove_dashboard_widgets() {
    // 检查WordPress核心函数是否存在
    if (!function_exists('remove_meta_box') || !function_exists('is_admin')) {
        return;
    }
    
    // 检查是否在管理界面
    if (!is_admin()) {
        return;
    }
    
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $hidden_widgets = isset($options['hide_dashboard_widgets']) ? $options['hide_dashboard_widgets'] : array();
            
            // 移除选中的仪表板小部件
            foreach ($hidden_widgets as $widget) {
                if (function_exists('sanitize_key')) {
                    $widget_id = sanitize_key($widget);
                    remove_meta_box($widget_id, 'dashboard', 'normal');
                    remove_meta_box($widget_id, 'dashboard', 'side');
                }
            }
        }
    }
}

// 钩子到wp_dashboard_setup
if (function_exists('add_action')) {
    add_action('wp_dashboard_setup', 'wpca_remove_dashboard_widgets');
}

/**
 * 添加自定义CSS类到管理界面body标签
 */
function wpca_admin_body_class($classes) {
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $theme_style = isset($options['theme_style']) ? $options['theme_style'] : 'default';
            $layout_density = isset($options['layout_density']) ? $options['layout_density'] : 'standard';
            
            // 添加主题样式类
            if (function_exists('sanitize_html_class')) {
                $classes .= ' wpca-theme-' . sanitize_html_class($theme_style);
                $classes .= ' wpca-density-' . sanitize_html_class($layout_density);
            }
        }
    }
    
    return $classes;
}

// 钩子到admin_body_class
if (function_exists('add_filter')) {
    add_filter('admin_body_class', 'wpca_admin_body_class');
}

/**
 * 应用自定义样式
 */
function wpca_apply_custom_styles() {
    // 检查是否在管理界面
    if (!function_exists('is_admin') || !is_admin()) {
        return;
    }
    
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $primary_color = isset($options['primary_color']) ? $options['primary_color'] : '#4A90E2';
            $background_color = isset($options['background_color']) ? $options['background_color'] : '#F8F9FA';
            $text_color = isset($options['text_color']) ? $options['text_color'] : '#2D3748';
            $border_radius_style = isset($options['border_radius_style']) ? $options['border_radius_style'] : 'small';
            $shadow_style = isset($options['shadow_style']) ? $options['shadow_style'] : 'subtle';
            
            // 定义CSS变量
            $custom_css = "
            :root {
                --wpca-primary-color: {$primary_color};
                --wpca-background-color: {$background_color};
                --wpca-text-color: {$text_color};
            }";
            
            // 根据选择的边框半径样式添加CSS
            $border_radius_values = array(
                'none' => '0',
                'small' => '4px',
                'medium' => '8px',
                'large' => '12px',
                'extra_large' => '16px'
            );
            
            $border_radius = isset($border_radius_values[$border_radius_style]) ? $border_radius_values[$border_radius_style] : '4px';
            
            $custom_css .= "
            .wp-admin .wpca-theme-enhanced * {
                border-radius: {$border_radius} !important;
            }";
            
            // 根据选择的阴影样式添加CSS
            $shadow_values = array(
                'none' => 'none',
                'subtle' => '0 1px 3px rgba(0, 0, 0, 0.1)',
                'medium' => '0 4px 6px rgba(0, 0, 0, 0.1)',
                'large' => '0 10px 15px rgba(0, 0, 0, 0.1)'
            );
            
            $shadow = isset($shadow_values[$shadow_style]) ? $shadow_values[$shadow_style] : '0 1px 3px rgba(0, 0, 0, 0.1)';
            
            $custom_css .= "
            .wp-admin .wpca-theme-enhanced .postbox,
            .wp-admin .wpca-theme-enhanced .card,
            .wp-admin .wpca-theme-enhanced .wrap > h1,
            .wp-admin .wpca-theme-enhanced .nav-tab-wrapper {
                box-shadow: {$shadow} !important;
            }";
            
            // 应用自定义样式
            if (function_exists('wp_add_inline_style')) {
                wp_add_inline_style('wp-admin', $custom_css);
            }
        }
    }
}

// 钩子到admin_enqueue_scripts
if (function_exists('add_action')) {
    add_action('admin_enqueue_scripts', 'wpca_apply_custom_styles');
}

/**
 * 移除管理栏项目
 */
function wpca_remove_admin_bar_items() {
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $hidden_items = isset($options['hide_admin_bar_items']) ? $options['hide_admin_bar_items'] : array();
            
            // 移除选中的管理栏项目
            foreach ($hidden_items as $item) {
                // 使用WordPress全局变量$wp_admin_bar来移除菜单项
                global $wp_admin_bar;
                if (is_object($wp_admin_bar)) {
                    $wp_admin_bar->remove_menu($item);
                }
            }
        }
    }
}

// 钩子到wp_before_admin_bar_render
if (function_exists('add_action')) {
    add_action('wp_before_admin_bar_render', 'wpca_remove_admin_bar_items');
}

/**
 * 从管理页面标题中移除"WordPress"
 */
function wpca_remove_wordpress_from_title($admin_title) {
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $hide_wordpress_title = isset($options['hide_wordpress_title']) ? $options['hide_wordpress_title'] : 0;
            
            if ($hide_wordpress_title) {
                // 检查WordPress核心函数是否存在
                if (function_exists('sanitize_key')) {
                    $admin_title = sanitize_key($admin_title);
                }
                return str_replace('WordPress &#8212; ', '', $admin_title);
            }
        }
    }
    
    return $admin_title;
}

// 钩子到admin_title
if (function_exists('add_filter')) {
    add_filter('admin_title', 'wpca_remove_wordpress_from_title');
}

/**
 * 为旧版WordPress提供的标题修改功能
 */
function wpca_remove_wordpress_from_wp_title($title) {
    // 检查是否在管理界面
    if (!function_exists('is_admin') || !is_admin()) {
        return $title;
    }
    
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $hide_wordpress_title = isset($options['hide_wordpress_title']) ? $options['hide_wordpress_title'] : 0;
            
            if ($hide_wordpress_title) {
                // 检查WordPress核心函数是否存在
                if (function_exists('sanitize_key')) {
                    $title = sanitize_key($title);
                }
                return str_replace('WordPress &#8212; ', '', $title);
            }
        }
    }
    
    return $title;
}

// 钩子到wp_title
if (function_exists('add_filter')) {
    add_filter('wp_title', 'wpca_remove_wordpress_from_wp_title');
}

/**
 * 隐藏WordPress页脚
 */
function wpca_hide_wp_footer() {
    // 检查是否在管理界面
    if (!function_exists('is_admin') || !is_admin()) {
        return;
    }
    
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $hide_wpfooter = isset($options['hide_wpfooter']) ? $options['hide_wpfooter'] : 0;
            
            if ($hide_wpfooter) {
                // 添加自定义CSS隐藏页脚
                $custom_css = "#wpfooter { display: none !important; }";
                
                // 应用自定义样式
                if (function_exists('wp_add_inline_style')) {
                    wp_add_inline_style('wp-admin', $custom_css);
                }
            }
        }
    }
}

// 钩子到admin_enqueue_scripts
if (function_exists('add_action')) {
    add_action('admin_enqueue_scripts', 'wpca_hide_wp_footer');
}

/**
 * 隐藏前端管理栏
 */
function wpca_hide_frontend_admin_bar() {
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $hide_frontend_adminbar = isset($options['hide_frontend_adminbar']) ? $options['hide_frontend_adminbar'] : 0;
            
            if ($hide_frontend_adminbar) {
                // 检查WordPress核心函数是否存在
                if (function_exists('show_admin_bar')) {
                    show_admin_bar(false);
                }
            }
        }
    }
}

// 钩子到init
if (function_exists('add_action')) {
    add_action('init', 'wpca_hide_frontend_admin_bar');
}

/**
 * 自定义登录页面样式
 */
function wpca_custom_login_styles() {
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $login_style = isset($options['login_style']) ? $options['login_style'] : 'default';
            $login_logo = isset($options['login_logo']) ? $options['login_logo'] : '';
            $login_background = isset($options['login_background']) ? $options['login_background'] : '';
            $login_custom_css = isset($options['login_custom_css']) ? $options['login_custom_css'] : '';
            
            $custom_css = "";
            
            // 应用背景图片
            if (!empty($login_background)) {
                $custom_css .= "body.login { background-image: url('{$login_background}') !important; background-size: cover !important; background-position: center !important; }";
            }
            
            // 应用自定义logo
            if (!empty($login_logo)) {
                $custom_css .= "#login h1 a { background-image: url('{$login_logo}') !important; background-size: contain !important; width: 100% !important; }";
            }
            
            // 应用自定义CSS
            if (!empty($login_custom_css)) {
                $custom_css .= $login_custom_css;
            }
            
            // 应用自定义样式
            if (function_exists('wp_add_inline_style')) {
                wp_add_inline_style('login', $custom_css);
            }
        }
    }
}

// 钩子到login_enqueue_scripts
if (function_exists('add_action')) {
    add_action('login_enqueue_scripts', 'wpca_custom_login_styles');
}

/**
 * 隐藏登录页面元素
 */
function wpca_hide_login_elements() {
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $login_elements = isset($options['login_elements']) ? $options['login_elements'] : array();
        
            $custom_css = "";
            
            // 隐藏语言切换器
            if (isset($login_elements['language_switcher']) && !$login_elements['language_switcher']) {
                $custom_css .= ".language-switcher { display: none !important; }";
            }
            
            // 隐藏首页链接
            if (isset($login_elements['home_link']) && !$login_elements['home_link']) {
                $custom_css .= "#backtoblog { display: none !important; }";
            }
            
            // 隐藏注册链接
            if (isset($login_elements['register_link']) && !$login_elements['register_link']) {
                $custom_css .= "#nav a[href*='wp-login.php?action=register'] { display: none !important; }";
            }
            
            // 隐藏记住我
            if (isset($login_elements['remember_me']) && !$login_elements['remember_me']) {
                $custom_css .= ".forgetmenot { display: none !important; }";
            }
            
            // 应用自定义样式
            if (!empty($custom_css) && function_exists('wp_add_inline_style')) {
                wp_add_inline_style('login', $custom_css);
            }
        }
    }
}

// 钩子到login_enqueue_scripts
if (function_exists('add_action')) {
    add_action('login_enqueue_scripts', 'wpca_hide_login_elements');
}

/**
 * 自定义登录页面标题
 */
function wpca_custom_login_title($login_title) {
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $hide_wordpress_title = isset($options['hide_wordpress_title']) ? $options['hide_wordpress_title'] : 0;
            
            if ($hide_wordpress_title) {
                // 检查WordPress核心函数是否存在
                if (function_exists('sanitize_key')) {
                    $login_title = sanitize_key($login_title);
                }
                return str_replace('WordPress &#8212; ', '', $login_title);
            }
        }
    }
    
    return $login_title;
}

// 钩子到login_title
if (function_exists('add_filter')) {
    add_filter('login_title', 'wpca_custom_login_title');
}

/**
 * 自定义登录页面链接URL
 */
function wpca_custom_login_headerurl($url) {
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $hide_wordpress_title = isset($options['hide_wordpress_title']) ? $options['hide_wordpress_title'] : 0;
            
            if ($hide_wordpress_title) {
                // 检查WordPress核心函数是否存在
                if (function_exists('home_url')) {
                    return home_url();
                }
            }
        }
    }
    
    return $url;
}

// 钩子到login_headerurl
if (function_exists('add_filter')) {
    add_filter('login_headerurl', 'wpca_custom_login_headerurl');
}

/**
 * 自定义登录页面链接标题
 */
function wpca_custom_login_headertitle($title) {
    // 检查WPCA_Settings类是否存在
    if (class_exists('WPCA_Settings')) {
        // 检查get_options方法是否存在
        if (method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();
            $hide_wordpress_title = isset($options['hide_wordpress_title']) ? $options['hide_wordpress_title'] : 0;
            
            if ($hide_wordpress_title) {
                // 检查WordPress核心函数是否存在
                if (function_exists('get_bloginfo')) {
                    return get_bloginfo('name', 'display');
                }
            }
        }
    }
    
    return $title;
}

// 钩子到login_headertitle
if (function_exists('add_filter')) {
    add_filter('login_headertitle', 'wpca_custom_login_headertitle');
}
?>