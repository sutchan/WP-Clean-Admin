<?php
/**
 * WPCleanAdmin Security Headers
 *
 * 承载安全 HTTP 头发送逻辑，从 Core 主类抽取。
 *
 * @package WPCleanAdmin\Modules\Core\Classes
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

namespace WPCleanAdmin\Modules\Core\Classes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 安全头发送类
 */
class Security_Headers {

    /**
     * Send security HTTP headers
     */
    public function send_security_headers(): void {
        // X-Frame-Options: Prevent clickjacking
        if ( ! \headers_sent() ) {
            \header( 'X-Frame-Options: SAMEORIGIN' );
        }

        // X-XSS-Protection: Enable browser XSS filter
        if ( ! \headers_sent() ) {
            \header( 'X-XSS-Protection: 1; mode=block' );
        }

        // X-Content-Type-Options: Prevent MIME type sniffing
        if ( ! \headers_sent() ) {
            \header( 'X-Content-Type-Options: nosniff' );
        }

        // Referrer-Policy: Control referrer information
        if ( ! \headers_sent() ) {
            \header( 'Referrer-Policy: strict-origin-when-cross-origin' );
        }

        // Content-Security-Policy: Restrict resource loading (basic configuration)
        if ( ! \headers_sent() ) {
            \header( "Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';" );
        }
    }

    /**
     * Register security headers hook
     */
    public function register(): void {
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'send_headers', array( $this, 'send_security_headers' ) );
        }
    }
}
