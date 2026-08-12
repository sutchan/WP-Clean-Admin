<?php
/**
 * WPCleanAdmin Login Class
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

require_once __DIR__ . '/class-wpca-login-captcha.php';
require_once __DIR__ . '/class-wpca-login-two-factor.php';
require_once __DIR__ . '/class-wpca-login-style.php';
require_once __DIR__ . '/class-wpca-login-attempts.php';

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Declare WordPress functions for IDE compatibility
if ( ! function_exists( 'wpca_get_settings' ) ) {
    function wpca_get_settings() {}
}
if ( ! function_exists( 'add_action' ) ) {
    function add_action() {}
}
if ( ! function_exists( 'add_filter' ) ) {
    function add_filter() {}
}
if ( ! function_exists( 'set_transient' ) ) {
    function set_transient() {}
}
if ( ! function_exists( 'get_transient' ) ) {
    function get_transient() {}
}

/**
 * Login class
 */
class Login {

    /**
     * Singleton instance
     *
     * @var Login
     */
    private static $instance;

    /**
     * CAPTCHA 处理器
     *
     * @var Login_Captcha
     */
    private $captcha;

    /**
     * 双因素认证处理器
     *
     * @var Login_TwoFactor
     */
    private $two_factor;

    /**
     * 登录页样式处理器
     *
     * @var Login_Style
     */
    private $style;

    /**
     * 登录尝试限制处理器
     *
     * @var Login_Attempts
     */
    private $attempts;

    /**
     * Get singleton instance
     *
     * @return Login
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->captcha     = new Login_Captcha();
        $this->two_factor  = new Login_TwoFactor();
        $this->style       = new Login_Style();
        $this->attempts    = new Login_Attempts();
        $this->init();
    }

    /**
     * Initialize the login module
     */
    public function init() {
        // Handle CAPTCHA image request
        if ( isset( $_GET['wpca_captcha'] ) && $_GET['wpca_captcha'] === '1' ) {
            $this->captcha->generate_captcha_image();
        }

        // Add login hooks
        if ( function_exists( 'add_action' ) && function_exists( 'add_filter' ) ) {
            \add_action( 'login_enqueue_scripts', array( $this, 'enqueue_login_scripts' ) );
            \add_filter( 'login_headerurl', array( $this, 'filter_login_header_url' ) );
            \add_filter( 'login_headertitle', array( $this, 'filter_login_header_title' ) );
            \add_action( 'login_footer', array( $this, 'add_login_footer_content' ) );
            \add_filter( 'login_body_class', array( $this, 'filter_login_body_class' ) );

            // Register sub-module hooks
            $this->two_factor->init();
            $this->captcha->init();
            $this->attempts->init();
        }
    }

    /**
     * Enqueue login scripts and styles (delegated)
     */
    public function enqueue_login_scripts() {
        $this->style->enqueue_login_scripts();
    }

    /**
     * Filter login header URL (delegated)
     *
     * @param string $url
     * @return string
     */
    public function filter_login_header_url( $url ) {
        return $this->style->filter_login_header_url( $url );
    }

    /**
     * Filter login header title (delegated)
     *
     * @param string $title
     * @return string
     */
    public function filter_login_header_title( string $title ): string {
        return $this->style->filter_login_header_title( $title );
    }

    /**
     * Add login footer content (delegated)
     */
    public function add_login_footer_content() {
        $this->style->add_login_footer_content();
    }

    /**
     * Filter login body class (delegated)
     *
     * @param array $classes
     * @return array
     */
    public function filter_login_body_class( $classes ) {
        return $this->style->filter_login_body_class( $classes );
    }

    /**
     * Customize login page (delegated)
     */
    public function customize_login_page(): void {
        $this->style->customize_login_page();
    }

    /**
     * Add custom login logo (delegated)
     */
    public function add_custom_login_logo(): void {
        $this->style->add_custom_login_logo();
    }

    /**
     * Add custom login background (delegated)
     */
    public function add_custom_login_background() {
        $this->style->add_custom_login_background();
    }

    /**
     * Restrict login attempts (delegated)
     */
    public function restrict_login_attempts(): void {
        $this->attempts->init();
    }

    /**
     * Check login attempts (delegated)
     *
     * @param object $user
     * @param string $username
     * @param string $password
     * @return object
     */
    public function check_login_attempts( $user, $username, $password ) {
        return $this->attempts->check_login_attempts( $user, $username, $password );
    }

    /**
     * Log failed login (delegated)
     *
     * @param string $username
     */
    public function log_failed_login( $username ) {
        $this->attempts->log_failed_login( $username );
    }

    /**
     * Initialize CAPTCHA (delegated)
     */
    public function init_captcha() {
        $this->captcha->init();
    }

    /**
     * Generate CAPTCHA image (delegated)
     */
    public function generate_captcha_image() {
        $this->captcha->generate_captcha_image();
    }

    /**
     * Render CAPTCHA form (delegated)
     */
    public function render_captcha() {
        $this->captcha->render_captcha();
    }

    /**
     * Verify CAPTCHA (delegated)
     *
     * @param object $user
     * @param string $username
     * @param string $password
     * @return object
     */
    public function verify_captcha( $user, $username, $password ) {
        return $this->captcha->verify_captcha( $user, $username, $password );
    }

    /**
     * Initialize two-factor auth (delegated)
     */
    public function init_two_factor_auth() {
        $this->two_factor->init();
    }

    /**
     * Generate two-factor secret (delegated)
     *
     * @param int $user_id
     */
    public function generate_two_factor_secret( $user_id ) {
        $this->two_factor->generate_two_factor_secret( $user_id );
    }

    /**
     * Update two-factor secret (delegated)
     *
     * @param int $user_id
     */
    public function update_two_factor_secret( $user_id ) {
        $this->two_factor->update_two_factor_secret( $user_id );
    }

    /**
     * Create random secret (delegated)
     *
     * @return string
     */
    public function create_random_secret() {
        return $this->two_factor->create_random_secret();
    }

    /**
     * Get QR code URL (delegated)
     *
     * @param int $user_id
     * @return string
     */
    public function get_qr_code_url( $user_id ) {
        return $this->two_factor->get_qr_code_url( $user_id );
    }

    /**
     * Render two-factor form (delegated)
     */
    public function render_two_factor_form() {
        $this->two_factor->render_two_factor_form();
    }

    /**
     * Check two-factor auth (delegated)
     *
     * @param object $user
     * @param string $password
     * @return object
     */
    public function check_two_factor_auth( $user, $password ) {
        return $this->two_factor->check_two_factor_auth( $user, $password );
    }

    /**
     * Handle two-factor failure (delegated)
     *
     * @param string $username
     */
    public function handle_two_factor_failure( $username ) {
        $this->two_factor->handle_two_factor_failure( $username );
    }
}
