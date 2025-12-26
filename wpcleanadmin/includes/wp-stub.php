<?php
/**
 * WordPress Core Function Stubs for Intelephense
 *
 * This file provides stub declarations for WordPress core functions
 * to enable proper IDE auto-completion and error checking.
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 */

namespace {
    if ( ! function_exists( 'wp_kses_post' ) ) {
        function wp_kses_post( $string ) {
            return $string;
        }
    }

    if ( ! function_exists( 'sanitize_html_class' ) ) {
        function sanitize_html_class( $class, $fallback = '' ) {
            return $fallback;
        }
    }

    if ( ! function_exists( 'get_transient' ) ) {
        function get_transient( $transient ) {
            return false;
        }
    }

    if ( ! function_exists( 'set_transient' ) ) {
        function set_transient( $transient, $value, $expiration = 0 ) {
            return false;
        }
    }

    if ( ! function_exists( 'update_user_meta' ) ) {
        function update_user_meta( $user_id, $meta_key, $meta_value, $prev_value = '' ) {
            return false;
        }
    }

    if ( ! function_exists( 'get_user_meta' ) ) {
        function get_user_meta( $user_id, $key = '', $single = false ) {
            return false;
        }
    }

    if ( ! function_exists( 'get_userdata' ) ) {
        function get_userdata( $user_id ) {
            return false;
        }
    }

    if ( ! function_exists( 'wp_verify_nonce' ) ) {
        function wp_verify_nonce( $nonce, $action = -1 ) {
            return false;
        }
    }

    if ( ! function_exists( 'wp_login_url' ) ) {
        function wp_login_url( $redirect = '', $force_reauth = false ) {
            return '';
        }
    }

    if ( ! function_exists( 'get_names' ) ) {
        function get_names() {
            return array();
        }
    }

    if ( ! class_exists( 'WP_Error' ) ) {
        class WP_Error {
            public $code;
            public $message;
            public $data;

            public function __construct( $code, $message, $data = null ) {
                $this->code = $code;
                $this->message = $message;
                $this->data = $data;
            }
        }
    }
}
