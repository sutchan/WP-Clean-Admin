<?php
/**
 * WPCleanAdmin Login Attempts
 *
 * 承载登录尝试限制与失败记录逻辑，从 Login 主类抽取。
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'get_transient' ) ) {
    function get_transient() {}
}
if ( ! function_exists( 'set_transient' ) ) {
    function set_transient() {}
}
if ( ! function_exists( 'add_action' ) ) {
    function add_action() {}
}
if ( ! function_exists( 'add_filter' ) ) {
    function add_filter() {}
}

/**
 * 登录尝试限制类
 */
class Login_Attempts {

    /**
     * Register login attempt restriction hooks
     */
    public function init(): void {
        if ( function_exists( 'add_filter' ) && function_exists( 'add_action' ) ) {
            \add_filter( 'authenticate', array( $this, 'check_login_attempts' ), 30, 3 );
            \add_action( 'wp_login_failed', array( $this, 'log_failed_login' ) );
        }
    }

    /**
     * Check login attempts
     *
     * @param object $user
     * @param string $username
     * @param string $password
     * @return object
     */
    public function check_login_attempts( $user, $username, $password ) {
        // Load settings
        $settings = \wpca_get_settings();

        // Get max login attempts
        $max_attempts = isset( $settings['login']['max_login_attempts'] ) ? intval( $settings['login']['max_login_attempts'] ) : 5;

        // Get lockout duration
        $lockout_duration = isset( $settings['login']['lockout_duration'] ) ? intval( $settings['login']['lockout_duration'] ) : 300;

        // Get user IP
        $user_ip = $_SERVER['REMOTE_ADDR'];

        // Get login attempts
        $login_attempts = ( function_exists( '\get_transient' ) ? \get_transient( 'wpca_login_attempts_' . $user_ip ) : 0 );

        // Check if user is locked out
        if ( $login_attempts >= $max_attempts ) {
            return new \WP_Error( 'too_many_attempts', \__( 'Too many login attempts. Please try again later.', \WPCA_TEXT_DOMAIN ) );
        }

        return $user;
    }

    /**
     * Log failed login attempts
     *
     * @param string $username
     */
    public function log_failed_login( $username ): void {
        // Get user IP
        $user_ip = $_SERVER['REMOTE_ADDR'];

        // Get login attempts
        $login_attempts = ( function_exists( '\get_transient' ) ? \get_transient( 'wpca_login_attempts_' . $user_ip ) : 0 );

        // Increment login attempts
        $login_attempts = $login_attempts ? $login_attempts + 1 : 1;

        // Set transient
        if ( function_exists( '\set_transient' ) ) {
            \set_transient( 'wpca_login_attempts_' . $user_ip, $login_attempts, 300 ); // 5 minutes
        }
    }
}
