<?php
/**
 * WordPress Core Function Stubs for Intelephense
 *
 * This file provides stub declarations for WordPress core functions
 * to enable proper IDE auto-completion and error checking.
 *
 * These stubs are only loaded when WordPress is not available,
 * ensuring no conflicts with the actual WordPress functions.
 *
 * @package WPCleanAdmin
 */

namespace {
    if ( ! function_exists( 'add_menu_page' ) ) {
        function add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback = '', $icon_url = '', $position = null ) {}
    }

    if ( ! function_exists( 'add_submenu_page' ) ) {
        function add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback = '' ) {}
    }

    if ( ! function_exists( 'checked' ) ) {
        function checked( $checked, $current = true, $echo = true ) {}
    }

    if ( ! function_exists( 'wp_roles' ) ) {
        function wp_roles() {
            return new WP_Roles();
        }
    }

    if ( ! function_exists( 'get_admin_page_title' ) ) {
        function get_admin_page_title() {
            return '';
        }
    }

    if ( ! function_exists( 'settings_fields' ) ) {
        function settings_fields( $option_group ) {}
    }

    if ( ! function_exists( 'do_settings_sections' ) ) {
        function do_settings_sections( $page ) {}
    }

    if ( ! function_exists( 'submit_button' ) ) {
        function submit_button( $text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null ) {}
    }

    if ( ! function_exists( 'wp_enqueue_style' ) ) {
        function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {}
    }

    if ( ! function_exists( 'wp_enqueue_script' ) ) {
        function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {}
    }

    if ( ! function_exists( 'esc_html' ) ) {
        function esc_html( $text ) {
            return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
        }
    }

    if ( ! function_exists( 'esc_attr' ) ) {
        function esc_attr( $text ) {
            return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
        }
    }

    if ( ! function_exists( 'esc_url' ) ) {
        function esc_url( $url, $protocols = null, $_context = 'display' ) {
            return '';
        }
    }

    if ( ! function_exists( '__' ) ) {
        function __( $text, $domain = 'default' ) {
            return $text;
        }
    }

    if ( ! function_exists( '_e' ) ) {
        function _e( $text, $domain = 'default' ) {
            echo $text;
        }
    }

    if ( ! function_exists( 'add_query_arg' ) ) {
        function add_query_arg() {
            return '';
        }
    }

    if ( ! function_exists( 'delete_option' ) ) {
        function delete_option( $option ) {
            return false;
        }
    }

    if ( ! function_exists( 'update_option' ) ) {
        function update_option( $option, $value ) {
            return false;
        }
    }

    if ( ! function_exists( 'get_option' ) ) {
        function get_option( $option, $default = false ) {
            return $default;
        }
    }

    if ( ! function_exists( 'add_action' ) ) {
        function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {}
    }

    if ( ! function_exists( 'add_filter' ) ) {
        function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {}
    }

    if ( ! function_exists( 'remove_action' ) ) {
        function remove_action( $tag, $function_to_remove, $priority = 10 ) {}
    }

    if ( ! function_exists( 'remove_filter' ) ) {
        function remove_filter( $tag, $function_to_remove, $priority = 10 ) {}
    }

    if ( ! function_exists( 'wp_create_nonce' ) ) {
        function wp_create_nonce( $action = -1 ) {
            return '';
        }
    }

    if ( ! function_exists( 'wp_get_current_user' ) ) {
        function wp_get_current_user() {
            return new WP_User();
        }
    }

    if ( ! function_exists( 'get_current_user_id' ) ) {
        function get_current_user_id() {
            return 0;
        }
    }

    if ( ! function_exists( 'get_user_by' ) ) {
        function get_user_by( $field, $value ) {
            return false;
        }
    }

    if ( ! function_exists( 'user_can' ) ) {
        function user_can( $user, $capability ) {
            return false;
        }
    }

    if ( ! function_exists( 'is_user_logged_in' ) ) {
        function is_user_logged_in() {
            return false;
        }
    }

    if ( ! function_exists( 'wp_redirect' ) ) {
        function wp_redirect( $location, $status = 302, $x_redirect_by = 'WordPress' ) {
            return false;
        }
    }

    if ( ! function_exists( 'add_role' ) ) {
        function add_role( $role_name, $display_name, $capabilities = array() ) {
            return null;
        }
    }

    if ( ! function_exists( 'get_role' ) ) {
        function get_role( $role ) {
            return null;
        }
    }

    if ( ! function_exists( 'wp_mkdir_p' ) ) {
        function wp_mkdir_p( $target ) {
            return false;
        }
    }

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

    if ( ! function_exists( 'wp_parse_args' ) ) {
        function wp_parse_args( $args, $defaults = array() ) {
            return $defaults;
        }
    }

    if ( ! function_exists( '_get_cron_array' ) ) {
        function _get_cron_array() {
            return array();
        }
    }

    if ( ! function_exists( 'wp_unschedule_event' ) ) {
        function wp_unschedule_event( $timestamp, $hook, $args = array() ) {}
    }

    if ( ! function_exists( 'wp_schedule_event' ) ) {
        function wp_schedule_event( $timestamp, $recurrence, $hook, $args = array() ) {}
    }

    if ( ! function_exists( 'wp_delete_attachment' ) ) {
        function wp_delete_attachment( $post_id, $force_delete = false ) {
            return false;
        }
    }

    if ( ! function_exists( 'wp_next_scheduled' ) ) {
        function wp_next_scheduled( $hook, $args = array() ) {
            return false;
        }
    }

    if ( ! function_exists( 'remove_role' ) ) {
        function remove_role( $role ) {}
    }

    if ( ! function_exists( 'wp_cache_flush' ) ) {
        function wp_cache_flush() {
            return false;
        }
    }

    if ( ! function_exists( 'get_num_queries' ) ) {
        function get_num_queries() {
            return 0;
        }
    }

    if ( ! function_exists( 'timer_stop' ) ) {
        function timer_stop( $display = 0, $precision = 3 ) {
            return '0.000';
        }
    }

    if ( ! function_exists( 'wpca_get_settings' ) ) {
        function wpca_get_settings() {
            return array();
        }
    }

    if ( ! function_exists( 'wp_remote_get' ) ) {
        function wp_remote_get( $url, $args = array() ) {
            return null;
        }
    }

    if ( ! function_exists( 'is_wp_error' ) ) {
        function is_wp_error( $thing ) {
            return false;
        }
    }

    if ( ! function_exists( 'wp_remote_retrieve_response_code' ) ) {
        function wp_remote_retrieve_response_code( $response ) {
            return 200;
        }
    }

    if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
        function wp_remote_retrieve_body( $response ) {
            return '';
        }
    }

    if ( ! function_exists( 'wp_upload_dir' ) ) {
        function wp_upload_dir( $time = null ) {
            return array(
                'path'    => '',
                'url'     => '',
                'subdir'  => '',
                'basedir' => '',
                'baseurl' => '',
                'error'   => false,
            );
        }
    }

    if ( ! function_exists( 'apply_filters' ) ) {
        function apply_filters( $tag, $value ) {
            return $value;
        }
    }

    if ( ! function_exists( 'do_action' ) ) {
        function do_action( $tag, ...$arg ) {}
    }

    if ( ! function_exists( 'sanitize_key' ) ) {
        function sanitize_key( $key ) {
            return '';
        }
    }

    if ( ! function_exists( 'current_time' ) ) {
        function current_time( $type, $gmt = 0 ) {
            return '';
        }
    }

    if ( ! function_exists( 'wp_send_json' ) ) {
        function wp_send_json( $data, $status_code = null ) {}
    }

    if ( ! function_exists( 'includes_url' ) ) {
        function includes_url( $path = '', $scheme = 'admin' ) {
            return '';
        }
    }

    if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', '' );
    }

    if ( ! function_exists( 'remove_meta_box' ) ) {
        function remove_meta_box( $id, $screen, $context ) {}
    }

    if ( ! function_exists( 'wp_deregister_script' ) ) {
        function wp_deregister_script( $handle ) {}
    }

    if ( ! function_exists( 'wp_dequeue_script' ) ) {
        function wp_dequeue_script( $handle ) {}
    }

    if ( ! function_exists( 'wp_deregister_style' ) ) {
        function wp_deregister_style( $handle ) {}
    }

    if ( ! function_exists( 'wp_dequeue_style' ) ) {
        function wp_dequeue_style( $handle ) {}
    }

    if ( ! function_exists( 'wp_die' ) ) {
        function wp_die( $message = '', $title = '', $args = array() ) {}
    }

    if ( ! function_exists( 'wp_send_json_error' ) ) {
        function wp_send_json_error( $data = null, $status_code = null ) {}
    }

    if ( ! function_exists( 'wp_send_json_success' ) ) {
        function wp_send_json_success( $data = null, $status_code = null ) {}
    }

    if ( ! function_exists( 'wp_unslash' ) ) {
        function wp_unslash( $value ) {
            return $value;
        }
    }

    if ( ! function_exists( 'wp_delete_comment' ) ) {
        function wp_delete_comment( $comment_id, $force_delete = false ) {
            return false;
        }
    }

    if ( ! function_exists( 'wp_delete_post' ) ) {
        function wp_delete_post( $post_id, $force_delete = false ) {
            return null;
        }
    }

    if ( ! function_exists( 'delete_transient' ) ) {
        function delete_transient( $transient ) {
            return true;
        }
    }

    if ( ! function_exists( 'get_post_meta' ) ) {
        function get_post_meta( $post_id, $key = '', $single = false ) {
            return $single ? null : array();
        }
    }

    if ( ! function_exists( 'delete_post_meta' ) ) {
        function delete_post_meta( $post_id, $meta_key, $meta_value = '' ) {
            return true;
        }
    }

    if ( ! function_exists( 'update_post_meta' ) ) {
        function update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = '' ) {
            return true;
        }
    }

    if ( ! function_exists( 'wp_slash' ) ) {
        function wp_slash( $value ) {
            return $value;
        }
    }

    if ( ! function_exists( 'wp_json_encode' ) ) {
        function wp_json_encode( $data, $options = 0, $depth = 512 ) {
            return json_encode( $data, $options, $depth );
        }
    }

    if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
        define( 'ELEMENTOR_VERSION', '' );
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

    if ( ! class_exists( 'WP_Roles' ) ) {
        class WP_Roles {
            public function get_names() {
                return array();
            }
        }
    }

    if ( ! class_exists( 'WP_User' ) ) {
        class WP_User {
            public $ID = 0;
        }
    }
}
