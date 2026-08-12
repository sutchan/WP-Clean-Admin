<?php
/**
 * WPCleanAdmin Permissions Restrict
 *
 * 承载后台访问与特定页面的限制/重定向逻辑，从 Permissions 主类抽取。
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

/**
 * 权限限制类（后台访问/页面重定向）
 */
class Permissions_Restrict {

    /**
     * Restrict access to admin pages
     */
    public function restrict_admin_access(): void {
        // Load settings
        $settings = \wpca_get_settings();

        // Check if admin access restriction is enabled
        if ( isset( $settings['permissions']['restrict_admin_access'] ) && $settings['permissions']['restrict_admin_access'] ) {
            // Check if user has access to admin area
            if ( ! ( function_exists( 'current_user_can' ) && \current_user_can( 'manage_options' ) ) ) {
                // Redirect non-administrators to front-end
                if ( function_exists( 'wp_redirect' ) && function_exists( 'home_url' ) ) {
                    \wp_redirect( \home_url() );
                    exit;
                }
            }
        }
    }

    /**
     * Restrict access to specific admin pages
     */
    public function restrict_specific_admin_pages(): void {
        // Load settings
        $settings = \wpca_get_settings();

        // Check if specific admin page restriction is enabled
        if ( isset( $settings['permissions']['restrict_specific_pages'] ) && $settings['permissions']['restrict_specific_pages'] ) {
            // Get current admin page
            $current_page = isset( $_GET['page'] ) ? ( function_exists( 'sanitize_text_field' ) ? \sanitize_text_field( $_GET['page'] ) : $_GET['page'] ) : '';

            // Check if current page is restricted
            if ( isset( $settings['permissions']['restricted_pages'] ) && in_array( $current_page, $settings['permissions']['restricted_pages'], true ) ) {
                // Check if user has access to restricted page
                if ( ! ( function_exists( 'current_user_can' ) && \current_user_can( 'manage_options' ) ) ) {
                    // Redirect to admin dashboard
                    if ( function_exists( 'wp_redirect' ) && function_exists( 'admin_url' ) ) {
                        \wp_redirect( \admin_url() );
                        exit;
                    }
                }
            }
        }
    }
}
