<?php
/**
 * WordPress Function Stubs for IDE Support
 *
 * @package WPCleanAdmin
 * @version 1.8.2
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */

if ( defined( 'ABSPATH' ) ) {
    return;
}

if ( ! defined( 'OBJECT' ) ) {
    define( 'OBJECT', 'OBJECT' );
}
if ( ! defined( 'OBJECT_K' ) ) {
    define( 'OBJECT_K', 'OBJECT_K' );
}
if ( ! defined( 'ARRAY_A' ) ) {
    define( 'ARRAY_A', 'ARRAY_A' );
}
if ( ! defined( 'ARRAY_N' ) ) {
    define( 'ARRAY_N', 'ARRAY_N' );
}

if ( ! defined( 'WP_DEBUG' ) ) {
    define( 'WP_DEBUG', false );
}
if ( ! defined( 'WP_DEBUG_LOG' ) ) {
    define( 'WP_DEBUG_LOG', false );
}
if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
    define( 'WP_DEBUG_DISPLAY', true );
}

if ( ! function_exists( 'get_plugin_data' ) ) {
    function get_plugin_data( $plugin_file ) {
        return array();
    }
}

if ( ! function_exists( 'wp_send_json' ) ) {
    function wp_send_json( $response ) {
    }
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
    function wp_verify_nonce( $nonce, $action = -1 ) {
        return false;
    }
}

if ( ! function_exists( 'add_options_page' ) ) {
    function add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $position = null ) {
        return false;
    }
}

if ( ! function_exists( 'checked' ) ) {
    function checked( $checked, $current = true, $echo = true ) {
        return '';
    }
}

if ( ! function_exists( 'get_admin_page_title' ) ) {
    function get_admin_page_title() {
        return '';
    }
}

if ( ! function_exists( 'settings_fields' ) ) {
    function settings_fields( $option_group ) {
    }
}

if ( ! function_exists( 'do_settings_sections' ) ) {
    function do_settings_sections( $page ) {
    }
}

if ( ! function_exists( 'submit_button' ) ) {
    function submit_button( $text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = array() ) {
        return '';
    }
}

if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can( $capability ) {
        return false;
    }
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        return $default;
    }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option( $option, $value ) {
        return false;
    }
}

if ( ! function_exists( 'delete_option' ) ) {
    function delete_option( $option ) {
        return false;
    }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
    }
}

if ( ! function_exists( 'register_setting' ) ) {
    function register_setting( $option_group, $option_name, $args = array() ) {
    }
}

if ( ! function_exists( 'add_settings_section' ) ) {
    function add_settings_section( $id, $title, $callback, $page ) {
    }
}

if ( ! function_exists( 'add_settings_field' ) ) {
    function add_settings_field( $id, $title, $callback, $page, $section = 'default', $args = array() ) {
    }
}

if ( ! function_exists( 'admin_url' ) ) {
    function admin_url( $path = '', $scheme = 'admin' ) {
        return '';
    }
}

if ( ! function_exists( 'home_url' ) ) {
    function home_url( $path = '', $scheme = null ) {
        return '';
    }
}

if ( ! function_exists( 'site_url' ) ) {
    function site_url( $path = '', $scheme = null ) {
        return '';
    }
}

if ( ! function_exists( 'add_query_arg' ) ) {
    function add_query_arg( $key, $value = '', $url = '' ) {
        return '';
    }
}

if ( ! function_exists( 'remove_query_arg' ) ) {
    function remove_query_arg( $key, $url = '' ) {
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
    }
}

if ( ! function_exists( '_x' ) ) {
    function _x( $text, $context, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( '_n' ) ) {
    function _n( $single, $plural, $number, $domain = 'default' ) {
        return $number === 1 ? $single : $plural;
    }
}

if ( ! function_exists( '_nx' ) ) {
    function _nx( $single, $plural, $number, $context, $domain = 'default' ) {
        return $number === 1 ? $single : $plural;
    }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        return '';
    }
}

if ( ! function_exists( 'sanitize_email' ) ) {
    function sanitize_email( $email ) {
        return '';
    }
}

if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( $url ) {
        return '';
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return '';
    }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return '';
    }
}

if ( ! function_exists( 'esc_js' ) ) {
    function esc_js( $text ) {
        return '';
    }
}

if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook( $file, $function ) {
    }
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook( $file, $function ) {
    }
}

if ( ! function_exists( 'load_plugin_textdomain' ) ) {
    function load_plugin_textdomain( $domain, $abs_rel_path = false, $plugin_rel_path = false ) {
        return false;
    }
}

if ( ! function_exists( 'plugin_basename' ) ) {
    function plugin_basename( $file ) {
        return '';
    }
}

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
    function is_plugin_active_for_network( $plugin ) {
        return false;
    }
}

if ( ! function_exists( 'is_plugin_active' ) ) {
    function is_plugin_active( $plugin ) {
        return false;
    }
}

if ( ! function_exists( 'wp_add_dashboard_widget' ) ) {
    function wp_add_dashboard_widget( $widget_id, $widget_name, $callback, $control_callback = null, $callback_args = null ) {
    }
}

if ( ! function_exists( 'remove_action' ) ) {
    function remove_action( $tag, $function_to_remove, $priority = 10 ) {
    }
}

if ( ! function_exists( 'remove_filter' ) ) {
    function remove_filter( $tag, $function_to_remove, $priority = 10 ) {
    }
}

if ( ! function_exists( 'remove_meta_box' ) ) {
    function remove_meta_box( $id, $screen, $context ) {
    }
}

if ( ! function_exists( 'wp_parse_args' ) ) {
    function wp_parse_args( $args, $defaults = array() ) {
        return array();
    }
}

if ( ! function_exists( 'wp_deregister_script' ) ) {
    function wp_deregister_script( $handle ) {
    }
}

if ( ! function_exists( 'wp_dequeue_script' ) ) {
    function wp_dequeue_script( $handle ) {
    }
}

if ( ! function_exists( 'wp_deregister_style' ) ) {
    function wp_deregister_style( $handle ) {
    }
}

if ( ! function_exists( 'wp_dequeue_style' ) ) {
    function wp_dequeue_style( $handle ) {
    }
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
    function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
    }
}

if ( ! function_exists( 'wp_mkdir_p' ) ) {
    function wp_mkdir_p( $path ) {
        return false;
    }
}

if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $tag, $value ) {
        return $value;
    }
}

if ( ! function_exists( 'do_action' ) ) {
    function do_action( $tag, ...$args ) {
    }
}

if ( ! function_exists( 'sanitize_key' ) ) {
    function sanitize_key( $key ) {
        return $key;
    }
}

if ( ! function_exists( 'current_time' ) ) {
    function current_time( $type = 'mysql', $gmt = false ) {
        return time();
    }
}

if ( ! function_exists( 'add_submenu_page' ) ) {
    function add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
        return $menu_slug;
    }
}

if ( ! function_exists( 'override_function' ) ) {
    function override_function( $function, $callback ) {
        return false;
    }
}

if ( ! function_exists( 'runkit_function_redefine' ) ) {
    function runkit_function_redefine( $function_name, $argument_list, $code ) {
        return false;
    }
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
    function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
    }
}

if ( ! function_exists( 'wp_localize_script' ) ) {
    function wp_localize_script( $handle, $object_name, $l10n ) {
    }
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
    function wp_create_nonce( $action = -1 ) {
        return '';
    }
}

if ( ! function_exists( 'size_format' ) ) {
    function size_format( $bytes, $decimals = 0 ) {
        return '';
    }
}

if ( ! function_exists( 'get_locale' ) ) {
    function get_locale() {
        return 'en_US';
    }
}

if ( ! function_exists( 'is_multisite' ) ) {
    function is_multisite() {
        return false;
    }
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
    function plugin_dir_path( $file ) {
        return dirname( $file ) . '/';
    }
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
    function plugin_dir_url( $file ) {
        return '';
    }
}

if ( ! function_exists( 'flush_rewrite_rules' ) ) {
    function flush_rewrite_rules( $hard = true ) {
    }
}

if ( ! function_exists( 'get_bloginfo' ) ) {
    function get_bloginfo( $show = '', $filter = 'raw' ) {
        return '';
    }
}

if ( ! function_exists( 'get_admin_bar' ) ) {
    function get_admin_bar() {
        return null;
    }
}

if ( ! class_exists( 'WP_Admin_Bar' ) ) {
    class WP_Admin_Bar {
        public function remove_node( $id ) {
        }
        
        public function get_node( $id ) {
            return null;
        }
        
        public function add_node( $node ) {
        }
        
        public function get_nodes() {
            return array();
        }
    }
}

if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        public function __construct( $code = '', $message = '', $data = '' ) {
        }
        
        public function get_error_code() {
            return '';
        }
        
        public function get_error_message( $code = '' ) {
            return '';
        }
        
        public function get_error_messages( $code = '' ) {
            return array();
        }
    }
}

if ( ! class_exists( 'wpdb' ) ) {
    class wpdb {
        public $db_version;
        
        public function db_version() {
            return '';
        }
        
        public function query( $query ) {
            return false;
        }
        
        public function get_results( $query, $output = OBJECT ) {
            return array();
        }
        
        public function get_row( $query, $output = OBJECT, $y = 0 ) {
            return null;
        }
        
        public function get_col( $query, $x = 0 ) {
            return array();
        }
        
        public function get_var( $query, $x = 0, $y = 0 ) {
            return null;
        }
        
        public function prepare( $query, ...$args ) {
            return $query;
        }
    }
}

if ( ! function_exists( 'is_user_logged_in' ) ) {
    function is_user_logged_in() {
        return false;
    }
}

if ( ! function_exists( 'wp_next_scheduled' ) ) {
    function wp_next_scheduled( $hook, $args = array() ) {
        return false;
    }
}

if ( ! function_exists( 'wp_schedule_event' ) ) {
    function wp_schedule_event( $timestamp, $recurrence, $hook, $args = array() ) {
        return false;
    }
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
    function timer_stop( $echo = 0, $precision = 3 ) {
        return 0.0;
    }
}

if ( ! function_exists( 'wp_remote_get' ) ) {
    function wp_remote_get( $url, $args = array() ) {
        return array();
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
    function wp_upload_dir( $time = null, $create = true ) {
        return array();
    }
}

if ( ! function_exists( 'includes_url' ) ) {
    function includes_url( $path = '' ) {
        return '';
    }
}

if ( ! function_exists( 'wp_cache_get' ) ) {
    function wp_cache_get( $key, $group = '', $force = false, &$found = null ) {
        return false;
    }
}

if ( ! function_exists( 'opcache_get_status' ) ) {
    function opcache_get_status( $force = false ) {
        return false;
    }
}

if ( ! function_exists( 'opcache_reset' ) ) {
    function opcache_reset() {
        return false;
    }
}

if ( ! function_exists( 'set_transient' ) ) {
    function set_transient( $transient, $value, $expiration = 0 ) {
        return false;
    }
}

if ( ! function_exists( 'get_transient' ) ) {
    function get_transient( $transient ) {
        return false;
    }
}

if ( ! function_exists( 'is_ssl' ) ) {
    function is_ssl() {
        return false;
    }
}

if ( ! function_exists( 'wp_kses_post' ) ) {
    function wp_kses_post( $data ) {
        return '';
    }
}

if ( ! function_exists( 'sanitize_html_class' ) ) {
    function sanitize_html_class( $class ) {
        return '';
    }
}

if ( ! function_exists( 'update_user_meta' ) ) {
    function update_user_meta( $user_id, $meta_key, $meta_value ) {
        return false;
    }
}

if ( ! function_exists( 'get_user_meta' ) ) {
    function get_user_meta( $user_id, $meta_key = '', $single = false ) {
        return false;
    }
}

if ( ! function_exists( 'get_userdata' ) ) {
    function get_userdata( $user_id ) {
        return false;
    }
}

if ( ! function_exists( 'wp_login_url' ) ) {
    function wp_login_url( $redirect = '', $force_reauth = false ) {
        return '';
    }
}

if ( ! function_exists( 'add_role' ) ) {
    function add_role( $role, $display_name, $capabilities = array() ) {
        return null;
    }
}

if ( ! function_exists( 'get_role' ) ) {
    function get_role( $role ) {
        return null;
    }
}

if ( ! function_exists( 'remove_role' ) ) {
    function remove_role( $role ) {
        return false;
    }
}

if ( ! function_exists( 'wp_delete_comment' ) ) {
    function wp_delete_comment( $comment_id, $force_delete = false ) {
        return false;
    }
}

if ( ! function_exists( '_get_cron_array' ) ) {
    function _get_cron_array() {
        return array();
    }
}

if ( ! function_exists( 'wp_unschedule_event' ) ) {
    function wp_unschedule_event( $timestamp, $hook, $args = array() ) {
        return false;
    }
}

if ( ! function_exists( 'wp_delete_attachment' ) ) {
    function wp_delete_attachment( $post_id, $force_delete = false ) {
        return false;
    }
}

if ( ! function_exists( 'wp_delete_post' ) ) {
    function wp_delete_post( $post_id, $force_delete = false ) {
        return false;
    }
}

if ( ! function_exists( 'wp_send_json_error' ) ) {
    function wp_send_json_error( $response = null, $status_code = null ) {
    }
}

if ( ! function_exists( 'wp_send_json_success' ) ) {
    function wp_send_json_success( $response = null ) {
    }
}

if ( ! function_exists( 'wp_count_posts' ) ) {
    function wp_count_posts( $type = 'post', $perm = '' ) {
        return (object) array();
    }
}

if ( ! function_exists( 'wp_count_comments' ) ) {
    function wp_count_comments() {
        return (object) array();
    }
}

if ( ! function_exists( 'count_users' ) ) {
    function count_users( $strategy = 'time' ) {
        return array();
    }
}

if ( ! function_exists( 'get_plugins' ) ) {
    function get_plugins( $plugin_folder = '' ) {
        return array();
    }
}

if ( ! function_exists( 'wp_get_themes' ) ) {
    function wp_get_themes( $args = array() ) {
        return array();
    }
}

if ( ! function_exists( 'wp_get_theme' ) ) {
    function wp_get_theme( $stylesheet = null, $theme_root = null ) {
        return (object) array();
    }
}

if ( ! class_exists( 'WP_Roles' ) ) {
    class WP_Roles {
        public function get_role( $role ) {
            return false;
        }
    }
}

if ( ! class_exists( 'WP_Theme' ) ) {
    class WP_Theme {
        public function parent() {
            return null;
        }
        
        public function get( $header ) {
            return '';
        }
    }
}

if ( ! class_exists( 'WPCA_Errors' ) ) {
    class WPCA_Errors {
        public function add( $code, $message ) {
        }
        
        public function get( $code = '' ) {
            return array();
        }
        
        public function has( $code = '' ) {
            return false;
        }
        
        public function get_error_messages( $code = '' ) {
            return array();
        }
    }
}