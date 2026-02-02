<?php
/**
 * WordPress Function Stubs for IDE Support
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
// ABSPATH check removed to ensure stub functions are always available for IDE

// WordPress core constants for database query results
define( 'OBJECT', 'OBJECT' );
define( 'OBJECT_K', 'OBJECT_K' );
define( 'ARRAY_A', 'ARRAY_A' );
define( 'ARRAY_N', 'ARRAY_N' );

// WordPress debug constants
if ( ! defined( 'WP_DEBUG' ) ) {
    define( 'WP_DEBUG', false );
}
if ( ! defined( 'WP_DEBUG_LOG' ) ) {
    define( 'WP_DEBUG_LOG', false );
}
if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
    define( 'WP_DEBUG_DISPLAY', true );
}

/**
 * WordPress core functions stubs
 * These are just declarations for IDE support, not actual implementations
 */

/**
 * Get plugin data
 *
 * @param string $plugin_file Plugin file path
 * @return array Plugin data
 */
function get_plugin_data( $plugin_file ) {
    return array();
}

/**
 * Send JSON response and exit
 *
 * @param mixed $response Response data
 */
function wp_send_json( $response ) {
}

/**
 * Verify nonce
 *
 * @param string $nonce Nonce to verify
 * @param string $action Nonce action
 * @return bool True if nonce is valid, false otherwise
 */
function wp_verify_nonce( $nonce, $action = -1 ) {
    return false;
}

/**
 * Add options page
 *
 * @param string $page_title Page title
 * @param string $menu_title Menu title
 * @param string $capability Capability required
 * @param string $menu_slug Menu slug
 * @param callable $function Function to call
 * @param int $position Menu position
 * @return string|false Hook name or false
 */
function add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $position = null ) {
    return false;
}

/**
 * Output HTML for checked attribute
 *
 * @param mixed $checked Checked value
 * @param mixed $current Current value
 * @param bool $echo Whether to echo
 * @return string Checked attribute or empty string
 */
function checked( $checked, $current = true, $echo = true ) {
    return '';
}

/**
 * Get admin page title
 *
 * @return string Admin page title
 */
function get_admin_page_title() {
    return '';
}

/**
 * Output settings fields
 *
 * @param string $option_group Option group
 */
function settings_fields( $option_group ) {
}

/**
 * Output settings sections
 *
 * @param string $page Page slug
 */
function do_settings_sections( $page ) {
}

/**
 * Output submit button
 *
 * @param string $text Button text
 * @param string $type Button type
 * @param string $name Button name
 * @param bool $wrap Whether to wrap
 * @param array $other_attributes Other attributes
 * @return string Submit button HTML
 */
function submit_button( $text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = array() ) {
    return '';
}

/**
 * Check if current user has capability
 *
 * @param string $capability Capability to check
 * @return bool True if user has capability, false otherwise
 */
function current_user_can( $capability ) {
    return false;
}

/**
 * Get option value
 *
 * @param string $option Option name
 * @param mixed $default Default value if option not found
 * @return mixed Option value
 */
function get_option( $option, $default = false ) {
    return $default;
}

/**
 * Update option value
 *
 * @param string $option Option name
 * @param mixed $value Option value
 * @return bool True on success, false on failure
 */
function update_option( $option, $value ) {
    return false;
}

/**
 * Delete option
 *
 * @param string $option Option name
 * @return bool True on success, false on failure
 */
function delete_option( $option ) {
    return false;
}

/**
 * Add action hook
 *
 * @param string $tag Action tag
 * @param callable $function_to_add Function to add
 * @param int $priority Priority
 * @param int $accepted_args Number of accepted arguments
 */
function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
}

/**
 * Add filter hook
 *
 * @param string $tag Filter tag
 * @param callable $function_to_add Function to add
 * @param int $priority Priority
 * @param int $accepted_args Number of accepted arguments
 */
function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
}

/**
 * Register settings
 *
 * @param string $option_group Option group
 * @param string $option_name Option name
 * @param array $args Arguments
 */
function register_setting( $option_group, $option_name, $args = array() ) {
}

/**
 * Add settings section
 *
 * @param string $id Section ID
 * @param string $title Section title
 * @param callable $callback Callback function
 * @param string $page Page slug
 */
function add_settings_section( $id, $title, $callback, $page ) {
}

/**
 * Add settings field
 *
 * @param string $id Field ID
 * @param string $title Field title
 * @param callable $callback Callback function
 * @param string $page Page slug
 * @param string $section Section ID
 * @param array $args Arguments
 */
function add_settings_field( $id, $title, $callback, $page, $section = 'default', $args = array() ) {
}

/**
 * Get admin URL
 *
 * @param string $path Path relative to admin directory
 * @param string $scheme URL scheme
 * @return string Admin URL
 */
function admin_url( $path = '', $scheme = 'admin' ) {
    return '';
}

/**
 * Get home URL
 *
 * @param int $blog_id Blog ID
 * @param string $path Path relative to home directory
 * @param string $scheme URL scheme
 * @return string Home URL
 */
function home_url( $path = '', $scheme = null ) {
    return '';
}

/**
 * Get site URL
 *
 * @param int $blog_id Blog ID
 * @param string $path Path relative to site directory
 * @param string $scheme URL scheme
 * @return string Site URL
 */
function site_url( $path = '', $scheme = null ) {
    return '';
}

/**
 * Add query argument to URL
 *
 * @param string|array $key Query key or array of key-value pairs
 * @param string $value Query value
 * @param string $url URL to add query argument to
 * @return string URL with query argument
 */
function add_query_arg( $key, $value = '', $url = '' ) {
    return '';
}

/**
 * Remove query argument from URL
 *
 * @param string|array $key Query key or array of keys to remove
 * @param string $url URL to remove query argument from
 * @return string URL without query argument
 */
function remove_query_arg( $key, $url = '' ) {
    return '';
}

/**
 * Translate text
 *
 * @param string $text Text to translate
 * @param string $domain Text domain
 * @return string Translated text
 */
function __( $text, $domain = 'default' ) {
    return $text;
}

/**
 * Translate and echo text
 *
 * @param string $text Text to translate
 * @param string $domain Text domain
 */
function _e( $text, $domain = 'default' ) {
}

/**
 * Translate text with context
 *
 * @param string $text Text to translate
 * @param string $context Context
 * @param string $domain Text domain
 * @return string Translated text
 */
function _x( $text, $context, $domain = 'default' ) {
    return $text;
}

/**
 * Translate plural text
 *
 * @param string $single Single form
 * @param string $plural Plural form
 * @param int $number Number
 * @param string $domain Text domain
 * @return string Translated text
 */
function _n( $single, $plural, $number, $domain = 'default' ) {
    return $number === 1 ? $single : $plural;
}

/**
 * Translate plural text with context
 *
 * @param string $single Single form
 * @param string $plural Plural form
 * @param int $number Number
 * @param string $context Context
 * @param string $domain Text domain
 * @return string Translated text
 */
function _nx( $single, $plural, $number, $context, $domain = 'default' ) {
    return $number === 1 ? $single : $plural;
}

/**
 * Sanitize text field
 *
 * @param string $str String to sanitize
 * @return string Sanitized string
 */
function sanitize_text_field( $str ) {
    return '';
}

/**
 * Sanitize email
 *
 * @param string $email Email to sanitize
 * @return string Sanitized email
 */
function sanitize_email( $email ) {
    return '';
}

/**
 * Sanitize URL
 *
 * @param string $url URL to sanitize
 * @return string Sanitized URL
 */
function esc_url( $url ) {
    return '';
}

/**
 * Sanitize HTML
 *
 * @param string $text Text to sanitize
 * @return string Sanitized HTML
 */
function esc_html( $text ) {
    return '';
}

/**
 * Sanitize attribute
 *
 * @param string $text Text to sanitize
 * @return string Sanitized attribute
 */
function esc_attr( $text ) {
    return '';
}

/**
 * Sanitize JavaScript
 *
 * @param string $text Text to sanitize
 * @return string Sanitized JavaScript
 */
function esc_js( $text ) {
    return '';
}

/**
 * Register activation hook
 *
 * @param string $file Plugin file
 * @param callable $function Function to call on activation
 */
function register_activation_hook( $file, $function ) {
}

/**
 * Register deactivation hook
 *
 * @param string $file Plugin file
 * @param callable $function Function to call on deactivation
 */
function register_deactivation_hook( $file, $function ) {
}

/**
 * Load plugin textdomain
 *
 * @param string $domain Text domain
 * @param string $abs_rel_path Absolute path to languages directory
 * @param string $plugin_rel_path Relative path to languages directory
 * @return bool True on success, false on failure
 */
function load_plugin_textdomain( $domain, $abs_rel_path = false, $plugin_rel_path = false ) {
    return false;
}

/**
 * Get plugin basename
 *
 * @param string $file Plugin file path
 * @return string Plugin basename
 */
function plugin_basename( $file ) {
    return '';
}

/**
 * Check if plugin is active for network
 *
 * @param string $plugin Plugin basename
 * @return bool True if plugin is network activated, false otherwise
 */
function is_plugin_active_for_network( $plugin ) {
    return false;
}

/**
 * Check if plugin is active
 *
 * @param string $plugin Plugin basename
 * @return bool True if plugin is activated, false otherwise
 */
function is_plugin_active( $plugin ) {
    return false;
}

/**
 * Add dashboard widget
 *
 * @param string $widget_id Widget ID
 * @param string $widget_name Widget name
 * @param callable $callback Callback function
 * @param string $control_callback Control callback
 * @param array $callback_args Callback arguments
 */
function wp_add_dashboard_widget( $widget_id, $widget_name, $callback, $control_callback = null, $callback_args = null ) {
}

/**
 * Remove action hook
 *
 * @param string $tag Action tag
 * @param callable $function_to_remove Function to remove
 * @param int $priority Priority
 */
function remove_action( $tag, $function_to_remove, $priority = 10 ) {
}

/**
 * Remove filter hook
 *
 * @param string $tag Filter tag
 * @param callable $function_to_remove Function to remove
 * @param int $priority Priority
 */
function remove_filter( $tag, $function_to_remove, $priority = 10 ) {
}

/**
 * Remove meta box
 *
 * @param string $id Meta box ID
 * @param string|array $screen Screen(s)
 * @param string $context Context
 */
function remove_meta_box( $id, $screen, $context ) {
}

/**
 * Parse arguments
 *
 * @param array $args Arguments to parse
 * @param array $defaults Default arguments
 * @return array Parsed arguments
 */
function wp_parse_args( $args, $defaults = array() ) {
    return array();
}

/**
 * Deregister script
 *
 * @param string $handle Script handle
 */
function wp_deregister_script( $handle ) {
}

/**
 * Dequeue script
 *
 * @param string $handle Script handle
 */
function wp_dequeue_script( $handle ) {
}

/**
 * Deregister style
 *
 * @param string $handle Style handle
 */
function wp_deregister_style( $handle ) {
}

/**
 * Dequeue style
 *
 * @param string $handle Style handle
 */
function wp_dequeue_style( $handle ) {
}

/**
 * Enqueue style
 *
 * @param string $handle Style handle
 * @param string $src Style source URL
 * @param array $deps Dependencies
 * @param string|bool|null $ver Version
 * @param string $media Media type
 */
function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
}

/**
 * Create directory recursively
 *
 * @param string $path Directory path to create
 * @return bool True on success, false on failure
 */
function wp_mkdir_p( $path ) {
    return false;
}

/**
 * Apply filters to a value
 *
 * @param string $tag Filter tag
 * @param mixed $value Value to filter
 * @return mixed Filtered value
 */
function apply_filters( $tag, $value ) {
    return $value;
}

/**
 * Execute action hooks
 *
 * @param string $tag Action tag
 * @param mixed $arg,... Additional arguments
 */
function do_action( $tag, ...$args ) {
}

/**
 * Sanitize key
 *
 * @param string $key Key to sanitize
 * @return string Sanitized key
 */
function sanitize_key( $key ) {
    return $key;
}

/**
 * Get current time
 *
 * @param string $type Type of time to get
 * @param bool $gmt Whether to use GMT time
 * @return string|int Current time
 */
function current_time( $type = 'mysql', $gmt = false ) {
    return time();
}

/**
 * Add submenu page
 *
 * @param string $parent_slug Parent menu slug
 * @param string $page_title Page title
 * @param string $menu_title Menu title
 * @param string $capability Capability required
 * @param string $menu_slug Menu slug
 * @param callable $function Function to render page
 * @return string Hook suffix
 */
function add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
    return $menu_slug;
}

/**
 * Override function
 *
 * @param string $function Function name
 * @param callable $callback Callback function
 * @return bool True on success, false on failure
 */
function override_function( $function, $callback ) {
    return false;
}

/**
 * Redefine function
 *
 * @param string $function_name Function name
 * @param string $argument_list Argument list
 * @param string $code Function code
 * @return bool True on success, false on failure
 */
function runkit_function_redefine( $function_name, $argument_list, $code ) {
    return false;
}

/**
 * Enqueue script
 *
 * @param string $handle Script handle
 * @param string $src Script source URL
 * @param array $deps Dependencies
 * @param string|bool|null $ver Version
 * @param bool $in_footer Whether to enqueue in footer
 */
function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
}

/**
 * Localize script
 *
 * @param string $handle Script handle
 * @param string $object_name JavaScript object name
 * @param array $l10n Localization data
 */
function wp_localize_script( $handle, $object_name, $l10n ) {
}

/**
 * Create nonce
 *
 * @param string $action Action name
 * @return string Nonce value
 */
function wp_create_nonce( $action = -1 ) {
    return '';
}

/**
 * Format bytes to human readable size
 *
 * @param int $bytes Bytes to format
 * @param int $decimals Number of decimals
 * @return string Formatted size
 */
function size_format( $bytes, $decimals = 0 ) {
    return '';
}

/**
 * Get locale
 *
 * @return string Locale
 */
function get_locale() {
    return 'en_US';
}

/**
 * Check if multisite is enabled
 *
 * @return bool True if multisite is enabled, false otherwise
 */
function is_multisite() {
    return false;
}

/**
 * Get plugin directory path
 *
 * @param string $file Plugin file path
 * @return string Plugin directory path
 */
function plugin_dir_path( $file ) {
    return dirname( $file ) . '/';
}

/**
 * Get plugin directory URL
 *
 * @param string $file Plugin file path
 * @return string Plugin directory URL
 */
function plugin_dir_url( $file ) {
    return '';
}

/**
 * Flush rewrite rules
 *
 * @param bool $hard Whether to flush hard
 */
function flush_rewrite_rules( $hard = true ) {
}

/**
 * Get WordPress version
 *
 * @return string WordPress version
 */
function get_bloginfo( $show = '', $filter = 'raw' ) {
    return '';
}

/**
 * Get admin bar object
 *
 * @return WP_Admin_Bar Admin bar object
 */
function get_admin_bar() {
    return null;
}

/**
 * WP_Admin_Bar class for IDE support
 */
class WP_Admin_Bar {
    /**
     * Remove node from admin bar
     *
     * @param string $id Node ID
     */
    public function remove_node( $id ) {
    }
    
    /**
     * Get node from admin bar
     *
     * @param string $id Node ID
     * @return object Node object
     */
    public function get_node( $id ) {
        return null;
    }
    
    /**
     * Add node to admin bar
     *
     * @param array $node Node data
     */
    public function add_node( $node ) {
    }
    
    /**
     * Get all nodes from admin bar
     *
     * @return array All nodes
     */
    public function get_nodes() {
        return array();
    }
}

/**
 * WP_Error class for IDE support
 */
class WP_Error {
    /**
     * Constructor
     *
     * @param string $code Error code
     * @param string $message Error message
     * @param mixed $data Error data
     */
    public function __construct( $code = '', $message = '', $data = '' ) {
    }
    
    /**
     * Get error code
     *
     * @return string Error code
     */
    public function get_error_code() {
        return '';
    }
    
    /**
     * Get error message
     *
     * @param string $code Error code
     * @return string Error message
     */
    public function get_error_message( $code = '' ) {
        return '';
    }
    
    /**
     * Get all error messages
     *
     * @param string $code Error code
     * @return array Error messages
     */
    public function get_error_messages( $code = '' ) {
        return array();
    }
}

/**
 * WPDB class for IDE support
 */
class wpdb {
    /**
     * Database version
     *
     * @var string
     */
    public $db_version;
    
    /**
     * Get database version
     *
     * @return string Database version
     */
    public function db_version() {
        return '';
    }
    
    /**
     * Query database
     *
     * @param string $query SQL query
     * @return int|false Number of rows affected or false on error
     */
    public function query( $query ) {
        return false;
    }
    
    /**
     * Get results from database
     *
     * @param string $query SQL query
     * @param string $output Output type
     * @return array|object|null Results
     */
    public function get_results( $query, $output = OBJECT ) {
        return array();
    }
    
    /**
     * Get row from database
     *
     * @param string $query SQL query
     * @param string $output Output type
     * @param int $y Row number
     * @return object|array|null Row
     */
    public function get_row( $query, $output = OBJECT, $y = 0 ) {
        return null;
    }
    
    /**
     * Get column from database
     *
     * @param string $query SQL query
     * @param int $x Column number
     * @return array Column
     */
    public function get_col( $query, $x = 0 ) {
        return array();
    }
    
    /**
     * Get var from database
     *
     * @param string $query SQL query
     * @param int $x Column number
     * @param int $y Row number
     * @return string|null Var
     */
    public function get_var( $query, $x = 0, $y = 0 ) {
        return null;
    }
}

/**
 * Check if user is logged in
 *
 * @return bool True if user is logged in, false otherwise
 */
function is_user_logged_in() {
    return false;
}

/**
 * Check if next scheduled event exists
 *
 * @param string $hook Hook name
 * @param array $args Arguments
 * @return int|false Timestamp if event exists, false otherwise
 */
function wp_next_scheduled( $hook, $args = array() ) {
    return false;
}

/**
 * Schedule event
 *
 * @param int $timestamp Timestamp
 * @param string $recurrence Recurrence
 * @param string $hook Hook name
 * @param array $args Arguments
 * @return bool True on success, false on failure
 */
function wp_schedule_event( $timestamp, $recurrence, $hook, $args = array() ) {
    return false;
}

/**
 * Flush object cache
 *
 * @return bool True on success, false on failure
 */
function wp_cache_flush() {
    return false;
}

/**
 * Get number of queries
 *
 * @return int Number of queries
 */
function get_num_queries() {
    return 0;
}

/**
 * Stop timer
 *
 * @param int $echo Whether to echo
 * @param int $precision Precision
 * @return float Page load time
 */
function timer_stop( $echo = 0, $precision = 3 ) {
    return 0.0;
}

/**
 * Make HTTP request
 *
 * @param string $url URL
 * @param array $args Arguments
 * @return array|WP_Error Response or error
 */
function wp_remote_get( $url, $args = array() ) {
    return array();
}

/**
 * Check if variable is WP_Error
 *
 * @param mixed $thing Variable to check
 * @return bool True if WP_Error, false otherwise
 */
function is_wp_error( $thing ) {
    return false;
}

/**
 * Get response code from HTTP response
 *
 * @param array|WP_Error $response Response
 * @return int Response code
 */
function wp_remote_retrieve_response_code( $response ) {
    return 200;
}

/**
 * Get body from HTTP response
 *
 * @param array|WP_Error $response Response
 * @return string Response body
 */
function wp_remote_retrieve_body( $response ) {
    return '';
}

/**
 * Get upload directory
 *
 * @param string $time Time
 * @param bool $create Whether to create directory
 * @return array Upload directory info
 */
function wp_upload_dir( $time = null, $create = true ) {
    return array();
}

/**
 * Get includes URL
 *
 * @param string $path Path
 * @return string Includes URL
 */
function includes_url( $path = '' ) {
    return '';
}

/**
 * Get cache value
 *
 * @param int|string $key Cache key
 * @param string $group Cache group
 * @param bool $force Whether to force refresh
 * @param bool $found Whether found
 * @return mixed Cache value
 */
function wp_cache_get( $key, $group = '', $force = false, &$found = null ) {
    return false;
}

/**
 * Check OPcache status
 *
 * @param bool $force Whether to force refresh
 * @return array|false OPcache status
 */
function opcache_get_status( $force = false ) {
    return false;
}

/**
 * Reset OPcache
 *
 * @return bool True on success, false on failure
 */
function opcache_reset() {
    return false;
}

/**
 * Set transient
 *
 * @param string $transient Transient name
 * @param mixed $value Transient value
 * @param int $expiration Expiration in seconds
 * @return bool True on success, false on failure
 */
function set_transient( $transient, $value, $expiration = 0 ) {
    return false;
}

/**
 * Get transient
 *
 * @param string $transient Transient name
 * @return mixed Transient value or false if not set
 */
function get_transient( $transient ) {
    return false;
}

/**
 * Check if SSL is being used
 *
 * @return bool True if SSL is being used, false otherwise
 */
function is_ssl() {
    return false;
}

/**
 * Sanitize HTML content for allowed HTML tags
 *
 * @param string $data Content to sanitize
 * @return string Sanitized content
 */
function wp_kses_post( $data ) {
    return '';
}

/**
 * Sanitize HTML class name
 *
 * @param string $class Class name to sanitize
 * @return string Sanitized class name
 */
function sanitize_html_class( $class ) {
    return '';
}

/**
 * Update user meta
 *
 * @param int $user_id User ID
 * @param string $meta_key Meta key
 * @param mixed $meta_value Meta value
 * @return bool True on success, false on failure
 */
function update_user_meta( $user_id, $meta_key, $meta_value ) {
    return false;
}

/**
 * Get user meta
 *
 * @param int $user_id User ID
 * @param string $meta_key Meta key
 * @param bool $single Whether to return single value
 * @return mixed User meta value
 */
function get_user_meta( $user_id, $meta_key = '', $single = false ) {
    return false;
}

/**
 * Get user data
 *
 * @param int $user_id User ID
 * @return object|false User data object or false if not found
 */
function get_userdata( $user_id ) {
    return false;
}

/**
 * Get login URL
 *
 * @param string $redirect Redirect URL
 * @param bool $force_reauth Whether to force reauthentication
 * @return string Login URL
 */
function wp_login_url( $redirect = '', $force_reauth = false ) {
    return '';
}

/**
 * Add user role
 *
 * @param string $role Role name
 * @param string $display_name Display name
 * @param array $capabilities Capabilities
 * @return object|null Role object or null if role already exists
 */
function add_role( $role, $display_name, $capabilities = array() ) {
    return null;
}

/**
 * Get user role
 *
 * @param string $role Role name
 * @return object|null Role object or null if not found
 */
function get_role( $role ) {
    return null;
}

/**
 * Remove user role
 *
 * @param string $role Role name
 * @return bool True on success, false on failure
 */
function remove_role( $role ) {
    return false;
}

/**
 * Delete comment
 *
 * @param int $comment_id Comment ID
 * @param bool $force_delete Force delete
 * @return bool True on success, false on failure
 */
function wp_delete_comment( $comment_id, $force_delete = false ) {
    return false;
}

/**
 * Get cron array
 *
 * @return array Cron events array
 */
function _get_cron_array() {
    return array();
}

/**
 * Unschedule event
 *
 * @param int $timestamp Timestamp
 * @param string $hook Hook name
 * @param array $args Arguments
 * @return bool True on success, false on failure
 */
function wp_unschedule_event( $timestamp, $hook, $args = array() ) {
    return false;
}

/**
 * Delete attachment
 *
 * @param int $post_id Post ID
 * @param bool $force_delete Force delete
 * @return mixed Deleted post or false
 */
function wp_delete_attachment( $post_id, $force_delete = false ) {
    return false;
}

/**
 * Delete post
 *
 * @param int $post_id Post ID
 * @param bool $force_delete Force delete
 * @return mixed Deleted post or false
 */
function wp_delete_post( $post_id, $force_delete = false ) {
    return false;
}

/**
 * Send JSON error response and exit
 *
 * @param mixed $response Response data
 * @param int $status_code HTTP status code
 */
function wp_send_json_error( $response = null, $status_code = null ) {
}

/**
 * Send JSON success response and exit
 *
 * @param mixed $response Response data
 */
function wp_send_json_success( $response = null ) {
}

/**
 * Count posts of a specific post type
 *
 * @param string $type Post type
 * @param string $perm Permission
 * @return object Post counts
 */
function wp_count_posts( $type = 'post', $perm = '' ) {
    return (object) array();
}

/**
 * Count comments
 *
 * @return object Comment counts
 */
function wp_count_comments() {
    return (object) array();
}

/**
 * Count users
 *
 * @param string $strategy Counting strategy
 * @return array User counts
 */
function count_users( $strategy = 'time' ) {
    return array();
}

/**
 * Get all plugins
 *
 * @return array Plugins
 */
function get_plugins( $plugin_folder = '' ) {
    return array();
}

/**
 * Get all themes
 *
 * @param array $args Arguments
 * @return array Themes
 */
function wp_get_themes( $args = array() ) {
    return array();
}

/**
 * Get theme data
 *
 * @param string $stylesheet Stylesheet name
 * @param string $theme_root Theme root
 * @return object Theme data
 */
function wp_get_theme( $stylesheet = null, $theme_root = null ) {
    return (object) array();
}

/**
 * WP_Roles class for IDE support
 */
class WP_Roles {
    /**
     * Get role
     *
     * @param string $role Role name
     * @return object|false Role object or false if not found
     */
    public function get_role( $role ) {
        return false;
    }
}

/**
 * WPCA_Errors class for IDE support
 */
class WPCA_Errors {
    /**
     * Add error
     *
     * @param string $code Error code
     * @param string $message Error message
     */
    public function add( $code, $message ) {
    }
    
    /**
     * Get errors
     *
     * @param string $code Error code
     * @return array Errors
     */
    public function get( $code = '' ) {
        return array();
    }
    
    /**
     * Check if error exists
     *
     * @param string $code Error code
     * @return bool True if error exists, false otherwise
     */
    public function has( $code = '' ) {
        return false;
    }
    
    /**
     * Get error messages
     *
     * @param string $code Error code
     * @return array Error messages
     */
    public function get_error_messages( $code = '' ) {
        return array();
    }
}

