<?php
/**
 * WP Clean Admin Core Functions
 *
 * This file contains core functions that modify the WordPress admin area based on plugin settings.
 * It handles non-settings-page related functionality.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


/**
 * Remove dashboard widgets based on settings.
 *
 * Action: wp_dashboard_setup
 */
function wpca_remove_dashboard_widgets() {
    $options = WPCA_Settings::get_options();
    $widgets_to_hide = $options['hide_dashboard_widgets'] ?? [];

    $widget_map = [
        'dashboard_activity'    => ['dashboard', 'normal'],
        'dashboard_at_glance'   => ['dashboard', 'normal'], // 修正 ID，与设置页面保持一致
        'dashboard_quick_press' => ['dashboard', 'side'],
        'dashboard_primary'     => ['dashboard', 'side'],
        'dashboard_site_health' => ['dashboard', 'normal'],
    ];

    foreach ($widgets_to_hide as $widget_id) {
        // 添加额外的安全检查
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

    // Sanitize colors before output with fallback values.
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
    $options = WPCA_Settings::get_options();
    $items_to_hide = $options['hide_admin_bar_items'] ?? [];

    foreach ($items_to_hide as $node_id) {
        if (is_string($node_id)) {
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