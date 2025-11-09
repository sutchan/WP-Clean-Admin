<?php
/**
 * WP Clean Admin Login Class
 *
 * Handles all login page-related modifications and settings.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// 初始化登录页面自定义功能
if (class_exists('WPCA_Login')) {
    function wpca_init_login_module() {
        return new WPCA_Login();
    }
    
    // 延迟初始化直到WordPress环境完全加载
    if (function_exists('add_action')) {
        add_action('init', 'wpca_init_login_module');
    }
}

/**
 * Class WPCA_Login
 * 管理WordPress登录页面的自定义功能
 */
class WPCA_Login {
    /**
     * 插件设置
     * @var array
     */
    private $options;

    /**
     * WPCA_Login constructor.
     * 初始化登录页面功能，注册必要的钩子
     */
    public function __construct() {
        // 获取插件设置
        if (class_exists('WPCA_Settings') && method_exists('WPCA_Settings', 'get_options')) {
            $this->options = WPCA_Settings::get_options();
        } else {
            $this->options = array();
        }
        
        // 注册登录页面钩子
        add_action('login_enqueue_scripts', array($this, 'enqueue_login_scripts'));
        add_filter('login_headerurl', array($this, 'custom_login_logo_url'));
        add_filter('login_headertitle', array($this, 'custom_login_logo_title'));
        add_action('login_head', array($this, 'output_custom_styles'));
        add_action('login_init', array($this, 'customize_login_elements'));
        
        // 添加登录表单自定义钩子
        add_action('login_form_top', array($this, 'add_login_form_elements'));
    }

    /**
     * 加载登录页面必要的脚本和样式
     */
    public function enqueue_login_scripts() {
        // 只在登录页面加载资源
        if (is_admin()) {
            return;
        }
        
        // 定义插件目录URL
        $plugin_url = plugins_url('', WPCA_PLUGIN_FILE);
        $assets_url = $plugin_url . '/wpcleanadmin/assets';
        
        // 加载登录页面样式
        wp_enqueue_style('wpca-login-styles', $assets_url . '/css/wpca-login-styles.css', array(), WPCA_VERSION);
        
        // 加载登录页面JavaScript
        wp_enqueue_script('wpca-login-js', $assets_url . '/js/wpca-login.js', array('jquery'), WPCA_VERSION, true);
        
        // 本地化JavaScript配置
        $login_config = $this->get_login_frontend_config();
        wp_localize_script('wpca-login-js', 'wpca_login_frontend', $login_config);
    }

    /**
     * 获取登录前端配置
     * @return array 前端配置参数
     */
    private function get_login_frontend_config() {
        // 默认配置
        $config = array(
            'custom_styles' => '',
            'auto_hide_form' => false,
            'auto_hide_delay' => 3000,
            'debug' => defined('WP_DEBUG') && WP_DEBUG
        );
        
        // 从设置中获取自定义样式
        $login_style = $this->options['login_style'] ?? 'default';
        $custom_styles = $this->generate_custom_styles($login_style);
        
        if (!empty($custom_styles)) {
            $config['custom_styles'] = $custom_styles;
        }
        
        // 自动隐藏表单设置
        $config['auto_hide_form'] = isset($this->options['login_auto_hide_form']) && $this->options['login_auto_hide_form'];
        $config['auto_hide_delay'] = isset($this->options['login_auto_hide_delay']) && is_numeric($this->options['login_auto_hide_delay']) 
            ? intval($this->options['login_auto_hide_delay']) * 1000 // 转换为毫秒
            : 3000;
        
        return $config;
    }

    /**
     * 生成自定义登录页面样式
     * @param string $login_style 登录样式名称
     * @return string 自定义CSS样式
     */
    private function generate_custom_styles($login_style) {
        $custom_css = '';
        
        // 基础样式
        $custom_css .= $this->get_base_login_styles();
        
        // 应用所选样式预设
        switch ($login_style) {
            case 'modern':
                $custom_css .= $this->get_modern_style();
                break;
            case 'minimal':
                $custom_css .= $this->get_minimal_style();
                break;
            case 'dark':
                $custom_css .= $this->get_dark_style();
                break;
            case 'gradient':
                $custom_css .= $this->get_gradient_style();
                break;
            case 'glassmorphism':
                $custom_css .= $this->get_glassmorphism_style();
                break;
            case 'neumorphism':
                $custom_css .= $this->get_neumorphism_style();
                break;
            case 'custom':
                // 自定义样式不应用预设
                break;
            case 'default':
            default:
                // 默认样式不需要额外CSS
                break;
        }
        
        // 添加自定义背景
        if (isset($this->options['login_background_url']) && !empty($this->options['login_background_url'])) {
            $background_url = esc_url($this->options['login_background_url']);
            $custom_css .= "body.login { background-image: url('{$background_url}'); background-size: cover; background-repeat: no-repeat; }";
        }
        
        // 添加自定义Logo
        if (isset($this->options['login_logo_url']) && !empty($this->options['login_logo_url'])) {
            $logo_url = esc_url($this->options['login_logo_url']);
            $custom_css .= ".login h1 a { background-image: url('{$logo_url}') !important; }";
        }
        
        return $custom_css;
    }

    /**
     * 获取基础登录样式
     * @return string CSS样式
     */
    private function get_base_login_styles() {
        return "
        /* WP Clean Admin - Base Login Styles */
        body.login { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login form { border-radius: 8px; }
        .login form .input, .login input[type=text], .login input[type=password] { border-radius: 4px; }
        .login .button-primary { border-radius: 4px; }
        ";
    }

    /**
     * 获取现代样式
     * @return string CSS样式
     */
    private function get_modern_style() {
        return "
        /* Modern Style */
        .login form { background: #fff; box-shadow: 0 6px 12px rgba(0,0,0,0.1); border: 1px solid #e0e0e0; border-radius: 12px; }
        .login .button-primary { background-color: #2271b1; box-shadow: none; text-shadow: none; }
        .login .button-primary:hover { background-color: #1e6298; }
        ";
    }

    /**
     * 获取极简样式
     * @return string CSS样式
     */
    private function get_minimal_style() {
        return "
        /* Minimal Style */
        .login form { background: #fff; box-shadow: none; border: 1px solid #e0e0e0; }
        .login .button-primary { background-color: #2271b1; box-shadow: none; text-shadow: none; }
        .login .button-primary:hover { background-color: #1e6298; }
        ";
    }

    /**
     * 获取暗黑样式
     * @return string CSS样式
     */
    private function get_dark_style() {
        return "
        /* Dark Style */
        body.login { background-color: #222; color: #fff; }
        .login h1 a { background-color: #333; border-radius: 4px; }
        .login form { background: #333; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
        .login form .input, .login input[type=text], .login input[type=password] { background-color: #444; color: #fff; border-color: #555; }
        .login .button-primary { background-color: #2271b1; box-shadow: none; text-shadow: none; }
        .login .button-primary:hover { background-color: #1e6298; }
        .login #nav, .login #backtoblog { color: #ccc; }
        .login #nav a, .login #backtoblog a { color: #fff; }
        ";
    }

    /**
     * 获取渐变样式
     * @return string CSS样式
     */
    private function get_gradient_style() {
        return "
        /* Gradient Style */
        body.login { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .login form { background: rgba(255,255,255,0.9); border-radius: 12px; box-shadow: 0 6px 30px rgba(0, 0, 0, 0.1); border: 1px solid rgba(255, 255, 255, 0.3); }
        .login .button-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: none; text-shadow: none; border: none; }
        .login .button-primary:hover { opacity: 0.9; }
        ";
    }

    /**
     * 获取玻璃拟态样式
     * @return string CSS样式
     */
    private function get_glassmorphism_style() {
        return "
        /* Glassmorphism Style */
        body.login { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        .login form { background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1); border-radius: 12px; }
        .login .button-primary { background-color: rgba(34, 113, 177, 0.8); box-shadow: none; text-shadow: none; }
        .login .button-primary:hover { background-color: rgba(34, 113, 177, 1); }
        ";
    }

    /**
     * 获取新拟态样式
     * @return string CSS样式
     */
    private function get_neumorphism_style() {
        return "
        /* Neumorphism Style */
        body.login { background-color: #ecf0f3; }
        .login form {
            background: #ecf0f3;
            box-shadow: 10px 10px 20px #d1d9e6, -10px -10px 20px #ffffff;
            border: none;
            border-radius: 16px;
            padding: 30px;
        }
        .login form .input, .login input[type=text], .login input[type=password] {
            background: #ecf0f3;
            box-shadow: inset 5px 5px 10px #d1d9e6, inset -5px -5px 10px #ffffff;
            border: none;
            padding: 12px 15px;
            border-radius: 8px;
        }
        .login .button-primary {
            background: #2271b1;
            box-shadow: 5px 5px 10px #d1d9e6, -5px -5px 10px #ffffff;
            text-shadow: none;
            border: none;
            transition: all 0.3s ease;
        }
        .login .button-primary:hover {
            background: #1e6298;
            box-shadow: 3px 3px 6px #d1d9e6, -3px -3px 6px #ffffff;
        }
        .login .button-primary:active {
            box-shadow: inset 5px 5px 10px #d1d9e6, inset -5px -5px 10px #ffffff;
        }
        ";
    }

    /**
     * 自定义登录Logo URL
     * @param string $url 默认URL
     * @return string 自定义URL
     */
    public function custom_login_logo_url($url) {
        return home_url();
    }

    /**
     * 自定义登录Logo标题
     * @param string $title 默认标题
     * @return string 自定义标题
     */
    public function custom_login_logo_title($title) {
        return get_bloginfo('name');
    }

    /**
     * 输出自定义样式到登录页面头部
     */
    public function output_custom_styles() {
        $login_style = $this->options['login_style'] ?? 'default';
        $custom_css = $this->generate_custom_styles($login_style);
        
        // 添加自定义CSS
        if (isset($this->options['login_custom_css']) && !empty($this->options['login_custom_css'])) {
            $custom_css .= $this->options['login_custom_css'];
        }
        
        if (!empty($custom_css)) {
            echo "<style type=\"text/css\">{$custom_css}</style>\n";
        }
    }

    /**
     * 自定义登录页面元素显示/隐藏
     */
    public function customize_login_elements() {
        // 获取登录元素设置
        $login_elements = $this->options['login_elements'] ?? array(
            'language_switcher' => 1,
            'home_link' => 1,
            'register_link' => 1,
            'remember_me' => 1
        );
        
        // 如果需要隐藏语言切换器
        if (isset($login_elements['language_switcher']) && !$login_elements['language_switcher']) {
            add_action('login_head', function() {
                echo "<style type=\"text/css\">#login .language-switcher { display: none; }</style>\n";
            });
        }
        
        // 如果需要隐藏返回首页链接
        if (isset($login_elements['home_link']) && !$login_elements['home_link']) {
            add_action('login_head', function() {
                echo "<style type=\"text/css\">#backtoblog { display: none; }</style>\n";
            });
        }
        
        // 如果需要隐藏注册链接
        if (isset($login_elements['register_link']) && !$login_elements['register_link']) {
            add_filter('register', '__return_false');
            add_action('login_head', function() {
                echo "<style type=\"text/css\">#nav { display: none; }</style>\n";
            });
        }
        
        // 如果需要隐藏记住我复选框
        if (isset($login_elements['remember_me']) && !$login_elements['remember_me']) {
            add_action('login_head', function() {
                echo "<style type=\"text/css\">.login .forgetmenot { display: none; }</style>\n";
            });
        }
    }
    
    /**
     * 向登录表单添加自定义元素
     */
    public function add_login_form_elements() {
        // 可以在这里添加任何自定义的登录表单元素
        // 例如欢迎消息、公司信息等
    }
    
    /**
     * 获取登录页面前端配置
     * @return array 配置数组
     */
    public function get_login_frontend_config() {
        return array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpca_login_frontend'),
            'login_style' => $this->options['login_style'] ?? 'default',
            'has_logo' => !empty($this->options['login_logo_url']),
            'has_background' => !empty($this->options['login_background_url'])
        );
    }
}
?>