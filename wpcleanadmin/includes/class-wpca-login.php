<?php
/**
 * WordPress Clean Admin - Login Manager
 * 
 * 自定义WordPress登录页面样式和行为的功能
 * 
 * @package WPCleanAdmin
 * @since 1.0.0
 * @version 1.7.13
 * @file wpcleanadmin/includes/class-wpca-login.php
 * @updated 2025-06-18
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Initialize login page customization functionality
if (!class_exists('WPCA_Login')) {
    function wpca_init_login_module() {
        return new WPCA_Login();
    }
    
    // Delay initialization until WordPress environment is fully loaded
    if (function_exists('add_action')) {
        add_action('init', 'wpca_init_login_module');
    }
}

/**
 * Class WPCA_Login
 * Manages WordPress login page customization functionality
 */
class WPCA_Login {
    /**
     * Plugin settings
     * @var array
     */
    private $options;

    /**
     * WPCA_Login constructor.
     * Initializes login page functionality and registers necessary hooks
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
        
        // 添加登录尝试限制相关钩子
        add_filter('authenticate', array($this, 'limit_login_attempts'), 30, 3);
        add_action('wp_login_failed', array($this, 'track_login_failure'));
        add_action('login_head', array($this, 'check_ip_blocked'));
    }

    /**
     * Loads necessary scripts and styles for the login page
     */
    public function enqueue_login_scripts() {
        // Only load resources on the login page
        if (function_exists('is_admin') && is_admin()) {
            return;
        }
        
        // Define plugin directory URL
        $plugin_url = function_exists('plugins_url') ? plugins_url('', WPCA_MAIN_FILE) : '';
        $assets_url = $plugin_url . '/wpcleanadmin/assets';
        
        // Load login page styles
        if (function_exists('wp_enqueue_style') && defined('WPCA_VERSION')) {
            wp_enqueue_style('wpca-login-styles', $assets_url . '/css/wpca-login-styles.css', array(), WPCA_VERSION);
        }
        
        // Load login page JavaScript
        if (function_exists('wp_enqueue_script') && defined('WPCA_VERSION')) {
            wp_enqueue_script('wpca-login-js', $assets_url . '/js/wpca-login.js', array('jquery'), WPCA_VERSION, true);
        }
        
        // Localize JavaScript configuration
        $login_config = $this->get_login_frontend_config();
        if (function_exists('wp_localize_script')) {
            wp_localize_script('wpca-login-js', 'wpca_login_frontend', $login_config);
        }
    }

    /**
     * Get login frontend configuration
     * @return array Frontend configuration parameters
     */
    private function get_login_frontend_config() {
        // Default configuration
        $config = array(
            'ajaxurl' => function_exists('admin_url') ? admin_url('admin-ajax.php') : '',
            'nonce' => function_exists('wp_create_nonce') ? wp_create_nonce('wpca_login_nonce') : '',
            'custom_styles' => '',
            'auto_hide_form' => false,
            'auto_hide_delay' => 3000,
            'debug' => defined('WP_DEBUG') && WP_DEBUG
        );
        
        // Get custom style from settings
        $login_style = isset($this->options['login_style']) && !empty($this->options['login_style']) ? $this->options['login_style'] : 'default';
        $custom_styles = $this->generate_custom_styles($login_style);
        
        if (!empty($custom_styles)) {
            $config['custom_styles'] = $custom_styles;
        }
        
        // Auto-hide form settings
        $config['auto_hide_form'] = isset($this->options['login_auto_hide_form']) && $this->options['login_auto_hide_form'];
        $config['auto_hide_delay'] = isset($this->options['login_auto_hide_delay']) && is_numeric($this->options['login_auto_hide_delay']) 
            ? intval($this->options['login_auto_hide_delay']) * 1000 // Convert to milliseconds
            : 3000;
        
        return $config;
    }

    /**
     * Generate custom login page styles
     * @param string $login_style Login style name
     * @return string Custom CSS styles
     */
    private function generate_custom_styles($login_style) {
        $custom_css = '';
        
        // Base styles
        $custom_css .= $this->get_base_login_styles();
        
        // Apply selected style preset
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
        
        // Add custom background
        if (isset($this->options['login_background_url']) && !empty($this->options['login_background_url'])) {
            $background_url = function_exists('esc_url') ? esc_url($this->options['login_background_url']) : filter_var($this->options['login_background_url'], FILTER_SANITIZE_URL);
            $custom_css .= "body.login { background-image: url('{$background_url}'); background-size: cover; background-repeat: no-repeat; }";
        }
        
        // Add custom logo
        if (isset($this->options['login_logo_url']) && !empty($this->options['login_logo_url'])) {
            $logo_url = function_exists('esc_url') ? esc_url($this->options['login_logo_url']) : filter_var($this->options['login_logo_url'], FILTER_SANITIZE_URL);
            $custom_css .= ".login h1 a { background-image: url('{$logo_url}') !important; }";
        }
        
        return $custom_css;
    }

    /**
     * Get base login styles
     * @return string CSS styles
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
     * Get modern style
     * @return string CSS styles
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
     * Get minimal style
     * @return string CSS styles
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
     * Get dark style
     * @return string CSS styles
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
     * Get gradient style
     * @return string CSS styles
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
     * Get glassmorphism style
     * @return string CSS styles
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
     * Get neumorphism style
     * @return string CSS styles
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
     * Custom login logo URL
     * @param string $url Default URL
     * @return string Custom URL
     */
    public function custom_login_logo_url($url) {
        return function_exists('home_url') ? home_url() : $url;
    }

    /**
     * Custom login logo title
     * @param string $title Default title
     * @return string Custom title
     */
    public function custom_login_logo_title($title) {
        return function_exists('get_bloginfo') ? get_bloginfo('name') : $title;
    }

    /**
     * Output custom styles to the login page header
     */
    public function output_custom_styles() {
        $login_style = isset($this->options['login_style']) && !empty($this->options['login_style']) ? $this->options['login_style'] : 'default';
        $custom_css = $this->generate_custom_styles($login_style);
        
        // Add custom CSS
        if (isset($this->options['login_custom_css']) && !empty($this->options['login_custom_css'])) {
            // Sanitize custom CSS input
            $sanitized_css = $this->sanitize_css($this->options['login_custom_css']);
            $custom_css .= $sanitized_css;
        }
        
        if (!empty($custom_css)) {
            echo "<style type=\"text/css\">{$custom_css}</style>
";
        }
    }
    
    /**
     * Sanitize CSS input to prevent injection attacks
     * @param string $css CSS to sanitize
     * @return string Sanitized CSS
     */
    private function sanitize_css($css) {
        // Remove potential JavaScript injections
        $css = preg_replace('/javascript:|data:text\/html/i', '', $css);
        // Remove potential XSS vectors
        $css = preg_replace('/expression\(|eval\(|on\w+\s*=/i', '', $css);
        return $css;
    }

    /**
     * Customize login page elements display/hide
     */
    public function customize_login_elements() {
        // Get login elements settings
        $login_elements = isset($this->options['login_elements']) && is_array($this->options['login_elements']) ? $this->options['login_elements'] : array(
            'language_switcher' => 1,
            'home_link' => 1,
            'register_link' => 1,
            'remember_me' => 1
        );
        
        // If language switcher needs to be hidden
        if (isset($login_elements['language_switcher']) && !$login_elements['language_switcher'] && function_exists('add_action')) {
            add_action('login_head', function() {
                echo "<style type=\"text/css\">#login .language-switcher { display: none; }</style>
";
            });
        }
        
        // If home link needs to be hidden
        if (isset($login_elements['home_link']) && !$login_elements['home_link'] && function_exists('add_action')) {
            add_action('login_head', function() {
                echo "<style type=\"text/css\">#backtoblog { display: none; }</style>
";
            });
        }
        
        // If register link needs to be hidden
        if (isset($login_elements['register_link']) && !$login_elements['register_link']) {
            if (function_exists('add_filter')) {
                add_filter('register', '__return_false');
            }
            if (function_exists('add_action')) {
                add_action('login_head', function() {
                    echo "<style type=\"text/css\">#nav { display: none; }</style>
";
                });
            }
        }
        
        // If remember me checkbox needs to be hidden
        if (isset($login_elements['remember_me']) && !$login_elements['remember_me'] && function_exists('add_action')) {
            add_action('login_head', function() {
                echo "<style type=\"text/css\">.login .forgetmenot { display: none; }</style>
";
            });
        }
    }
    
    /**
     * Add custom elements to the login form
     */
    public function add_login_form_elements() {
        // Custom login form elements can be added here
        // For example: welcome messages, company information, etc.
    }
    
    /**
     * Limit login attempts
     * 
     * Checks for too many failed login attempts and blocks if necessary
     * 
     * @param WP_User|WP_Error $user User object or error
     * @param string $username Username
     * @param string $password Password
     * @return WP_User|WP_Error
     */
    public function limit_login_attempts($user, $username, $password) {
        // If authentication already failed, don't interfere
        if (is_wp_error($user)) {
            return $user;
        }
        
        // Get IP address
        $ip = $this->get_client_ip();
        
        // Check if IP is blocked
        if ($this->is_ip_blocked($ip)) {
            $blocked_message = $this->get_blocked_message($ip);
            return new WP_Error('too_many_retries', $blocked_message);
        }
        
        return $user;
    }
    
    /**
     * Track login failures
     * 
     * Records failed login attempts in the database
     * 
     * @param string $username Username attempted
     */
    public function track_login_failure($username) {
        // Get IP address
        $ip = $this->get_client_ip();
        
        // Get current failure count and last attempt time
        $login_failures = get_transient('wpca_login_failures_' . $ip);
        
        if ($login_failures === false) {
            // First failure
            $login_failures = array(
                'count' => 1,
                'last_attempt' => time(),
                'username' => $username
            );
        } else {
            // Increment failure count
            $login_failures['count']++;
            $login_failures['last_attempt'] = time();
        }
        
        // Get max attempts and lockout duration from settings or use defaults
        $max_attempts = isset($this->options['login_max_attempts']) && is_numeric($this->options['login_max_attempts']) 
            ? intval($this->options['login_max_attempts']) : 5;
        $lockout_duration = isset($this->options['login_lockout_duration']) && is_numeric($this->options['login_lockout_duration']) 
            ? intval($this->options['login_lockout_duration']) : 15; // Default 15 minutes
        
        // If max attempts reached, block the IP
        if ($login_failures['count'] >= $max_attempts) {
            // Set block transient
            $lockout_duration_seconds = $lockout_duration * 60; // Convert to seconds
            set_transient('wpca_blocked_ip_' . $ip, $login_failures, $lockout_duration_seconds);
            
            // Log the block event
            if (class_exists('WPCA_Helpers') && method_exists('WPCA_Helpers', 'audit_log')) {
                WPCA_Helpers::audit_log(
                    'login_attempt_blocked',
                    'blocked',
                    array(
                        'ip' => $ip,
                        'username' => $username,
                        'attempts' => $login_failures['count'],
                        'lockout_duration' => $lockout_duration
                    )
                );
            }
        } else {
            // Save failure count
            set_transient('wpca_login_failures_' . $ip, $login_failures, 1800); // Save for 30 minutes
        }
    }
    
    /**
     * Check if IP is blocked
     * 
     * @param string $ip IP address
     * @return bool True if blocked, false otherwise
     */
    public function is_ip_blocked($ip) {
        $blocked = get_transient('wpca_blocked_ip_' . $ip);
        return $blocked !== false;
    }
    
    /**
     * Check if current IP is blocked and show message
     */
    public function check_ip_blocked() {
        $ip = $this->get_client_ip();
        
        if ($this->is_ip_blocked($ip)) {
            $blocked = get_transient('wpca_blocked_ip_' . $ip);
            $transient_info = get_transient('wpca_blocked_ip_' . $ip);
            
            if ($transient_info !== false) {
                $time_left = ceil((get_transient_timeout('wpca_blocked_ip_' . $ip) - time()) / 60);
                $blocked_message = sprintf(
                    __('Too many failed login attempts. Your IP has been temporarily blocked for %d more minutes.', 'wp-clean-admin'),
                    $time_left
                );
                
                echo '<div class="login_error">' . esc_html($blocked_message) . '</div>';
            }
        }
    }
    
    /**
     * Get blocked message with remaining time
     * 
     * @param string $ip IP address
     * @return string Blocked message
     */
    private function get_blocked_message($ip) {
        $blocked = get_transient('wpca_blocked_ip_' . $ip);
        $time_left = ceil((get_transient_timeout('wpca_blocked_ip_' . $ip) - time()) / 60);
        
        return sprintf(
            __('Too many failed login attempts. Your IP has been temporarily blocked for %d more minutes.', 'wp-clean-admin'),
            $time_left
        );
    }
    
    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    private function get_client_ip() {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Get the first IP in the comma-separated list
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return sanitize_text_field(trim($ips[0]));
        } else {
            return sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }
    }
    
    /**
     * Reset login attempts for a specific IP
     * 
     * @param string $ip IP address
     */
    public function reset_login_attempts($ip) {
        delete_transient('wpca_login_failures_' . $ip);
        delete_transient('wpca_blocked_ip_' . $ip);
    }

}
?>