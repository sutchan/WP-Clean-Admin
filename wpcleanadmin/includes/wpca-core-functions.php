<?php
/**
 * WP Clean Admin - Core Functions
 * 
 * @package WPCleanAdmin
 * @subpackage Core
 * @since 1.0.0
 */

/**
 * This file contains core functions that modify the WordPress admin area based on plugin settings.
 * It handles non-settings-page related functionality, such as dashboard widget management, admin body classes,
 * and other admin interface customizations.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// No utility class required - directly using WordPress native functions


/**
 * Remove dashboard widgets based on settings.
 *
 * Action: wp_dashboard_setup
 */
function wpca_remove_dashboard_widgets() {
    // Check if WPCA_Settings class exists and has get_options method
    if (!class_exists('WPCA_Settings') || !method_exists('WPCA_Settings', 'get_options')) {
        return;
    }
    
    $options = WPCA_Settings::get_options();
    $widgets_to_hide = $options['hide_dashboard_widgets'] ?? [];

    $widget_map = [
        'dashboard_activity'    => ['dashboard', 'normal'],
        'dashboard_at_glance'   => ['dashboard', 'normal'], // Fixed ID to match settings page
        'dashboard_quick_press' => ['dashboard', 'side'],
        'dashboard_primary'     => ['dashboard', 'side'],
        'dashboard_site_health' => ['dashboard', 'normal'],
    ];

    foreach ($widgets_to_hide as $widget_id) {
        // Add additional security check
        if (isset($widget_map[$widget_id]) && is_string($widget_id)) {
            remove_meta_box(sanitize_key($widget_id), $widget_map[$widget_id][0], $widget_map[$widget_id][1]);
        }
    }
}
add_action('wp_dashboard_setup', 'wpca_remove_dashboard_widgets', 999);


/**
 * Add custom CSS classes to the admin body tag for theme styling.
 *
 * Filter: admin_body_class
 *
 * @param string $classes Space-separated string of classes.
 * @return string Modified string of classes.
 */
function wpca_admin_body_class($classes) {
    // Check if WPCA_Settings class exists and has get_options method
    if (!class_exists('WPCA_Settings') || !method_exists('WPCA_Settings', 'get_options')) {
        return $classes;
    }
    
    $options = WPCA_Settings::get_options();
    
    // An array makes it easier to manage classes.
    $custom_classes = [];

    // Add classes based on settings, only if they are not the default.
    if (!empty($options['theme_style']) && 'default' !== $options['theme_style']) {
        $custom_classes[] = 'wpca-theme-' . esc_attr($options['theme_style']);
    }
    if (!empty($options['layout_density']) && 'standard' !== $options['layout_density']) {
        $custom_classes[] = 'wpca-layout-' . esc_attr($options['layout_density']);
    }
    if (!empty($options['border_radius_style']) && 'small' !== $options['border_radius_style']) {
        $custom_classes[] = 'wpca-radius-' . esc_attr($options['border_radius_style']);
    }

    if (!empty($custom_classes)) {
        $classes .= ' ' . implode(' ', $custom_classes);
    }
    
    return $classes;
}
add_filter('admin_body_class', 'wpca_admin_body_class');


/**
 * Generate and enqueue custom styles based on plugin settings.
 * This uses CSS variables for modern, flexible styling.
 *
 * Action: admin_enqueue_scripts
 */
function wpca_apply_custom_styles() {
    // This style needs to be applied on all admin pages, so no page check here.
    wp_enqueue_style('wpca-admin-style', WPCA_PLUGIN_URL . 'assets/css/wpca-admin.css', [], WPCA_VERSION);

    // Check if WPCA_Settings class exists and has get_options method
    if (!class_exists('WPCA_Settings') || !method_exists('WPCA_Settings', 'get_options')) {
        return;
    }
    
    $options = WPCA_Settings::get_options();

    // Start with values from settings (for 'custom' theme).
    $primary_color    = $options['primary_color'];
    $background_color = $options['background_color'];
    $text_color       = $options['text_color'];
    
    // Override colors for predefined themes.
    switch ($options['theme_style']) {
        case 'dark':
            $primary_color    = '#00a0d2'; // Brighter blue for contrast.
            $background_color = '#1e1e1e';
            $text_color       = '#e0e0e0';
            break;
        // Add other themes here if needed.
    }

    // Sanitize colors before output with fallback values
    $primary_color    = sanitize_hex_color($primary_color) ?: '#0073aa';
    $background_color = sanitize_hex_color($background_color) ?: '#ffffff';
    $text_color       = sanitize_hex_color($text_color) ?: '#333333';

    // Using a HEREDOC for clean multiline CSS.
    $custom_css = <<<CSS
    :root {
        --wpca-primary-color: {$primary_color};
        --wpca-background-color: {$background_color};
        --wpca-text-color: {$text_color};
    }
    CSS;

    // Use the recommended WordPress function to add inline styles.
    // This is safer and better for dependency management.
    wp_add_inline_style('wpca-admin-style', $custom_css);
}
add_action('admin_enqueue_scripts', 'wpca_apply_custom_styles');


/**
 * Remove admin bar items based on settings.
 *
 * Action: admin_bar_menu
 *
 * @param WP_Admin_Bar $wp_admin_bar The admin bar object.
 */
function wpca_remove_admin_bar_items($wp_admin_bar) {
    // Check if WPCA_Settings class exists and has get_options method
    if (!class_exists('WPCA_Settings') || !method_exists('WPCA_Settings', 'get_options')) {
        return;
    }
    
    $options = WPCA_Settings::get_options();
    $items_to_hide = $options['hide_admin_bar_items'] ?? [];

    foreach ($items_to_hide as $node_id) {
        if (is_string($node_id) && isset($wp_admin_bar) && method_exists($wp_admin_bar, 'remove_node')) {
            $wp_admin_bar->remove_node(sanitize_key($node_id));
        }
    }
}
add_action('admin_bar_menu', 'wpca_remove_admin_bar_items', 999);


// [REMOVED] The function 'wpca_enqueue_admin_assets' was removed because its functionality
// has been merged into 'WPCA_Settings::enqueue_admin_scripts' for settings-page-only assets,
// and 'wpca_apply_custom_styles' for global assets.

// [REMOVED] The function 'wpca_remove_admin_menu_items' was removed to resolve a
// conflict with the more advanced menu customization feature in the 'WPCA_Menu_Customizer' class.
// Menu visibility is now handled exclusively by CSS via the Menu Customization tab.


/**
 * Remove "WordPress" from admin page titles.
 *
 * Filter: document_title_parts
 *
 * @param array $title_parts The document title parts.
 * @return array Modified document title parts.
 */
function wpca_remove_wordpress_from_title($title_parts) {
    // Check if WPCA_Settings class exists and has get_options method
    if (!class_exists('WPCA_Settings') || !method_exists('WPCA_Settings', 'get_options')) {
        return $title_parts;
    }
    
    $options = WPCA_Settings::get_options();
    
    // Check if we're in the admin area and if the setting is enabled
    if (is_admin() && isset($options['hide_wordpress_title']) && $options['hide_wordpress_title']) {
        // WordPress typically appends "WordPress" to the site name in admin titles
        // We'll check each part and remove any that contains "WordPress"
        foreach ($title_parts as $key => $part) {
            if (stripos($part, 'WordPress') !== false) {
                unset($title_parts[$key]);
            }
        }
        
        // Re-index the array to avoid gaps
        $title_parts = array_values($title_parts);
    }
    
    return $title_parts;
}
add_filter('document_title_parts', 'wpca_remove_wordpress_from_title', 999);


/**
 * Remove "WordPress" from admin page titles for older WordPress versions
 * that still use wp_title filter.
 *
 * Filter: wp_title
 *
 * @param string $title Page title.
 * @param string $sep Title separator.
 * @param string $seplocation Location of the separator (left or right).
 * @return string Modified page title.
 */
function wpca_remove_wordpress_from_wp_title($title, $sep, $seplocation) {
    // Check if WPCA_Settings class exists and has get_options method
    if (!class_exists('WPCA_Settings') || !method_exists('WPCA_Settings', 'get_options')) {
        return $title;
    }
    
    $options = WPCA_Settings::get_options();
    
    // Check if we're in the admin area and if the setting is enabled
    if (is_admin() && isset($options['hide_wordpress_title']) && $options['hide_wordpress_title']) {
        // Remove any instance of "WordPress" from the title
        $title = str_ireplace('WordPress', '', $title);
        // Remove any extra separators
        $title = str_replace(array("$sep  ", "  $sep"), '', $title);
        // Trim whitespace
        $title = trim($title);
    }
    
    return $title;
}
add_filter('wp_title', 'wpca_remove_wordpress_from_wp_title', 999, 3);


/**
 * Remove "WordPress" from admin page titles for newer WordPress versions
 * that use wp_get_document_title filter.
 *
 * Filter: wp_get_document_title
 *
 * @param string $title The document title.
 * @return string Modified document title.
 */
function wpca_remove_wordpress_from_document_title($title) {
    // Check if WPCA_Settings class exists and has get_options method
    if (!class_exists('WPCA_Settings') || !method_exists('WPCA_Settings', 'get_options')) {
        return $title;
    }
    
    $options = WPCA_Settings::get_options();
    
    // Check if we're in the admin area and if the setting is enabled
    if (is_admin() && isset($options['hide_wordpress_title']) && $options['hide_wordpress_title']) {
        // Remove any instance of "WordPress" from the title
        $title = str_ireplace('WordPress', '', $title);
        // Trim whitespace
        $title = trim($title);
    }
    
    return $title;
}
add_filter('wp_get_document_title', 'wpca_remove_wordpress_from_document_title', 999);