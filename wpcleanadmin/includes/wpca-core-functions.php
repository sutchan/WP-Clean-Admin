<?php
/**
 * WPCleanAdmin Core Functions
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get plugin settings
 *
 * @param string $key Specific setting key to get
 * @param mixed $default Default value if setting not found
 * @return mixed Plugin settings or specific setting value
 */
function wpca_get_settings( $key = '', $default = false ) {
    $settings = function_exists( 'get_option' ) ? \get_option( 'wpca_settings', array() ) : array();
    
    if ( empty( $key ) ) {
        return $settings;
    }
    
    return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

/**
 * Update plugin settings
 *
 * @param array $settings Settings to update
 * @return bool Update result
 */
function wpca_update_settings( $settings ) {
    // Validate input is an array
    if ( ! is_array( $settings ) ) {
        return false;
    }
    
    // Filter settings to ensure they are valid
    $validated_settings = array();
    
    foreach ( $settings as $key => $value ) {
        // Sanitize key
        $sanitized_key = function_exists( 'sanitize_key' ) ? \sanitize_key( $key ) : $key;
        
        // Validate and sanitize value based on type
        if ( is_array( $value ) ) {
            // Recursively validate nested arrays
            $sanitized_value = wpca_sanitize_nested_array( $value );
        } elseif ( is_string( $value ) ) {
            $sanitized_value = function_exists( 'sanitize_text_field' ) ? \sanitize_text_field( $value ) : $value;
        } elseif ( is_numeric( $value ) ) {
            $sanitized_value = floatval( $value );
        } elseif ( is_bool( $value ) ) {
            $sanitized_value = $value;
        } else {
            // Skip invalid types
            continue;
        }
        
        $validated_settings[ $sanitized_key ] = $sanitized_value;
    }
    
    // Allow other plugins to modify settings before saving
    if ( function_exists( 'apply_filters' ) ) {
        $validated_settings = \apply_filters( 'wpca_update_settings', $validated_settings, $settings );
    }
    
    return function_exists( 'update_option' ) ? \update_option( 'wpca_settings', $validated_settings ) : false;
}

/**
 * Sanitize nested array recursively
 *
 * @param array $array Array to sanitize
 * @return array Sanitized array
 */
function wpca_sanitize_nested_array( $array ) {
    $sanitized = array();
    
    foreach ( $array as $key => $value ) {
        $sanitized_key = function_exists( 'sanitize_key' ) ? \sanitize_key( $key ) : $key;
        
        if ( is_array( $value ) ) {
            $sanitized[ $sanitized_key ] = wpca_sanitize_nested_array( $value );
        } elseif ( is_string( $value ) ) {
            $sanitized[ $sanitized_key ] = function_exists( 'sanitize_text_field' ) ? \sanitize_text_field( $value ) : $value;
        } elseif ( is_numeric( $value ) ) {
            $sanitized[ $sanitized_key ] = floatval( $value );
        } elseif ( is_bool( $value ) ) {
            $sanitized[ $sanitized_key ] = $value;
        }
    }
    
    return $sanitized;
}

/**
 * Get database settings
 *
 * @param string $key Specific setting key to get
 * @param mixed $default Default value if setting not found
 * @return mixed Database settings or specific setting value
 */
function wpca_get_database_settings( $key = '', $default = false ) {
    $settings = function_exists( 'get_option' ) ? \get_option( 'wpca_database_settings', array() ) : array();
    
    if ( empty( $key ) ) {
        return $settings;
    }
    
    return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

/**
 * Get performance settings
 *
 * @param string $key Specific setting key to get
 * @param mixed $default Default value if setting not found
 * @return mixed Performance settings or specific setting value
 */
function wpca_get_performance_settings( $key = '', $default = false ) {
    $settings = function_exists( 'get_option' ) ? \get_option( 'wpca_performance_settings', array() ) : array();
    
    if ( empty( $key ) ) {
        return $settings;
    }
    
    return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

/**
 * Check if user has access to WPCA features
 *
 * @return bool True if user has access, false otherwise
 */
function wpca_current_user_can() {
    return function_exists( 'current_user_can' ) ? \current_user_can( 'manage_options' ) : false;
}

/**
 * Get plugin URL
 *
 * @param string $path Path relative to plugin directory
 * @return string Full plugin URL
 */
function wpca_get_plugin_url( $path = '' ) {
    $url = rtrim(\WPCA_PLUGIN_URL, '/');
    return $url . '/' . ltrim($path, '/');
}

/**
 * Get plugin directory path
 *
 * @param string $path Path relative to plugin directory
 * @return string Full plugin directory path
 */
function wpca_get_plugin_dir( $path = '' ) {
    $dir = rtrim( \WPCA_PLUGIN_DIR, '/' );
    return $dir . '/' . ltrim( $path, '/' );
}

/**
 * Get assets URL
 *
 * @param string $asset_path Asset path relative to assets directory
 * @return string Full asset URL
 */
function wpca_get_asset_url( $asset_path = '' ) {
    return wpca_get_plugin_url( '/assets/' . ltrim( $asset_path, '/' ) );
}

/**
 * Get template path
 *
 * @param string $template_name Template name
 * @return string Full template path
 */
function wpca_get_template_path( $template_name ) {
    return wpca_get_plugin_dir( '/includes/templates/' . $template_name );
}

/**
 * Load template
 *
 * @param string $template_name Template name
 * @param array $args Arguments to pass to template
 */
function wpca_load_template( $template_name, $args = array() ) {
    $template_path = wpca_get_template_path( $template_name );
    
    if ( file_exists( $template_path ) ) {
        extract( $args );
        include $template_path;
    }
}

/**
 * Show admin notice
 *
 * @param string $message Notice message
 * @param string $type Notice type (success, error, warning, info)
 */
function wpca_admin_notice( $message, $type = 'info' ) {
    $allowed_types = array( 'success', 'error', 'warning', 'info' );
    $type = in_array( $type, $allowed_types ) ? $type : 'info';
    
    echo '<div class="notice notice-' . ( function_exists( 'esc_attr' ) ? \esc_attr( $type ) : htmlspecialchars( $type, ENT_QUOTES, 'UTF-8' ) ) . ' is-dismissible">';
    echo '<p>' . ( function_exists( 'esc_html' ) ? \esc_html( $message ) : htmlspecialchars( $message, ENT_QUOTES, 'UTF-8' ) ) . '</p>';
    echo '</div>';
}

/**
 * Get plugin version
 *
 * @return string Plugin version
 */
function wpca_get_version() {
    return \WPCA_VERSION;
}

/**
 * Check if plugin is in debug mode
 *
 * @return bool True if debug mode is enabled, false otherwise
 */
function wpca_is_debug() {
    return defined( 'WP_DEBUG' ) && WP_DEBUG;
}

/**
 * Log debug message
 *
 * @param mixed $message Message to log
 * @param string $context Context of the log
 */
function wpca_log( $message, $context = 'general' ) {
    if ( ! wpca_is_debug() ) {
        return;
    }
    
    $log_message = sprintf( '[WPCleanAdmin] [%s] %s', $context, $message );
    
    if ( is_array( $message ) || is_object( $message ) ) {
        $log_message = sprintf( '[WPCleanAdmin] [%s] %s', $context, print_r( $message, true ) );
    }
    
    error_log( $log_message );
}

/**
 * Sanitize array of data
 *
 * @param array $data Data to sanitize
 * @return array Sanitized data
 */
function wpca_sanitize_array( $data ) {
    if ( ! is_array( $data ) ) {
        return function_exists( 'sanitize_text_field' ) ? \sanitize_text_field( $data ) : $data;
    }
    
    foreach ( $data as &$value ) {
        if ( is_array( $value ) ) {
            $value = wpca_sanitize_array( $value );
        } else {
            $value = function_exists( 'sanitize_text_field' ) ? \sanitize_text_field( $value ) : $value;
        }
    }
    
    return $data;
}

/**
 * Get current tab from URL
 *
 * @param string $default Default tab if no tab is specified
 * @return string Current tab
 */
function wpca_get_current_tab( $default = 'dashboard' ) {
    return isset( $_GET['tab'] ) ? ( function_exists( 'sanitize_text_field' ) ? \sanitize_text_field( $_GET['tab'] ) : $_GET['tab'] ) : $default;
}

/**
 * Get settings page URL
 *
 * @param string $tab Tab to open
 * @return string Settings page URL
 */
function wpca_get_settings_url( $tab = '' ) {
    $url = function_exists( 'admin_url' ) ? \admin_url( 'admin.php?page=wp-clean-admin' ) : '';
    
    if ( ! empty( $tab ) ) {
        $url .= '&tab=' . $tab;
    }
    
    return $url;
}

/**
 * Check if current page is WPCA settings page
 *
 * @return bool True if current page is WPCA settings page, false otherwise
 */
function wpca_is_settings_page() {
    return isset( $_GET['page'] ) && $_GET['page'] === 'wp-clean-admin';
}

/**
 * Get plugin menu slug
 *
 * @return string Plugin menu slug
 */
function wpca_get_menu_slug() {
    return 'wp-clean-admin';
}

/**
 * Get plugin text domain
 *
 * @return string Plugin text domain
 */
function wpca_get_text_domain() {
    return \WPCA_TEXT_DOMAIN;
}

/**
 * Check if WordPress version is greater than or equal to specified version
 *
 * @param string $version Version to check against
 * @return bool True if WordPress version is greater than or equal to specified version, false otherwise
 */
function wpca_is_wp_version_gte( $version ) {
    global $wp_version;
    return version_compare( $wp_version, $version, '>=' );
}

/**
 * Get admin page title
 *
 * @param string $tab Current tab
 * @return string Admin page title
 */
function wpca_get_admin_page_title( $tab = '' ) {
    $translate = function_exists( '__' ) ? '\__' : function( $text ) { return $text; };
    
    $titles = array(
        'dashboard' => $translate( 'WP Clean Admin Dashboard', \WPCA_TEXT_DOMAIN ),
        'settings' => $translate( 'WP Clean Admin Settings', \WPCA_TEXT_DOMAIN ),
        'database' => $translate( 'Database Optimization', \WPCA_TEXT_DOMAIN ),
        'performance' => $translate( 'Performance Settings', \WPCA_TEXT_DOMAIN ),
        'menu' => $translate( 'Menu Management', \WPCA_TEXT_DOMAIN ),
        'permissions' => $translate( 'Permissions', \WPCA_TEXT_DOMAIN ),
        'login' => $translate( 'Login Customization', \WPCA_TEXT_DOMAIN ),
        'cleanup' => $translate( 'Cleanup', \WPCA_TEXT_DOMAIN ),
        'resources' => $translate( 'Resource Optimization', \WPCA_TEXT_DOMAIN ),
        'reset' => $translate( 'Reset Settings', \WPCA_TEXT_DOMAIN )
    );
    
    return isset( $titles[ $tab ] ) ? $titles[ $tab ] : $translate( 'WP Clean Admin', \WPCA_TEXT_DOMAIN );
}


