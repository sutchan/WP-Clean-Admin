<?php
// includes/wpca-core-functions.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Apply core modifications based on settings.
 */


/**
 * Enqueue admin scripts and styles.
 */
function wpca_enqueue_admin_assets() {
    wp_enqueue_style( 'wpca-admin-style', WPCA_PLUGIN_URL . 'assets/css/wp-clean-admin.css', array(), WPCA_VERSION );
    wp_enqueue_script( 'wpca-admin-script', WPCA_PLUGIN_URL . 'assets/js/wpca-settings.js', array( 'jquery', 'jquery-ui-sortable' ), WPCA_VERSION, true );

    // Enqueue WordPress color picker script and style
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );

    // Initialize color picker
    add_action( 'admin_footer', function() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('.wpca-color-picker').wpColorPicker();
            });
        </script>
        <?php
    });
}
add_action( 'admin_enqueue_scripts', 'wpca_enqueue_admin_assets' );


/**
 * Remove dashboard widgets based on settings.
 */
function wpca_remove_dashboard_widgets() {
    $options = WPCA_Settings::get_options();
    $widgets_to_hide = $options['hide_dashboard_widgets'] ?? array();

    if ( in_array( 'dashboard_right_now', $widgets_to_hide ) ) {
        remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
    }
    if ( in_array( 'dashboard_activity', $widgets_to_hide ) ) {
        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
    }
    if ( in_array( 'dashboard_quick_press', $widgets_to_hide ) ) {
        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
    }
    if ( in_array( 'dashboard_primary', $widgets_to_hide ) ) {
        remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
    }
    if ( in_array( 'dashboard_site_health', $widgets_to_hide ) ) {
        remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
    }
    if ( in_array( 'dashboard_at_glance', $widgets_to_hide ) ) {
        remove_meta_box( 'dashboard_at_glance', 'dashboard', 'normal' );
    }
    // Add more widget removals here based on settings
}
add_action( 'wp_dashboard_setup', 'wpca_remove_dashboard_widgets' );


/**
 * Apply admin body classes for styling based on settings.
 *
 * @param $classes
 *
 * @return string
 */
function wpca_admin_body_class( $classes ) {
    $options = WPCA_Settings::get_options();
    $theme_style = $options['theme_style'] ?? 'default';
    $layout_density = $options['layout_density'] ?? 'standard';
    $border_radius_style = $options['border_radius_style'] ?? 'small';
    $shadow_style = $options['shadow_style'] ?? 'subtle';
    $font_size_base = $options['font_size_base'] ?? 'medium';

    if ( $theme_style && $theme_style !== 'default' ) {
        $classes .= ' wpca-theme-' . esc_attr( $theme_style );
    }
    if ( $layout_density && $layout_density !== 'standard' ) {
        $classes .= ' wpca-layout-' . esc_attr( $layout_density );
    }
    if ( $border_radius_style && $border_radius_style !== 'small' ) {
        $classes .= ' wpca-radius-' . esc_attr( $border_radius_style );
    }
    if ( $shadow_style && $shadow_style !== 'subtle' ) {
        $classes .= ' wpca-shadow-' . esc_attr( $shadow_style );
    }
    if ( $font_size_base && $font_size_base !== 'medium' ) {
        $classes .= ' wpca-font-size-' . esc_attr( $font_size_base );
    }

    return $classes;
}
add_filter( 'admin_body_class', 'wpca_admin_body_class' );


/**
 * Apply custom styles based on settings.
 */
function wpca_apply_custom_styles() {
    $options = WPCA_Settings::get_options();
    $theme_style = $options['theme_style'] ?? 'default';
    $primary_color = esc_attr( $options['primary_color'] );
    $background_color = esc_attr( $options['background_color'] );
    $text_color = esc_attr( $options['text_color'] );
    $font_stack = esc_attr( $options['font_stack'] );

    $system_font_stack = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';

    $custom_css = '';

    // Define CSS variables based on settings
    $custom_css .= "
        :root {
            --wpca-primary-color: {$primary_color};
            --wpca-background-color: {$background_color};
            --wpca-text-color: {$text_color};
            --wpca-border-radius-small: 4px; /* Unified 4px */
            --wpca-border-radius-large: 8px; /* Unified 8px */
            --wpca-shadow-subtle: 0 1px 3px rgba(0,0,0,0.08); /* Subtle shadow */
            --wpca-spacing-compact: 8px;
            --wpca-spacing-standard: 16px;
            --wpca-spacing-spacious: 24px;
            --wpca-font-size-small: 13px;
            --wpca-font-size-medium: 14px;
            --wpca-font-size-large: 15px;
        }
    ";

    // Apply theme-specific variables if not 'custom'
    if ( 'default' === $theme_style ) {
        $custom_css .= "
            :root {
                --wpca-primary-color: #4A90E2;
                --wpca-background-color: #F8F9FA;
                --wpca-text-color: #2D3748;
            }
        ";
    } elseif ( 'light_blue_gray' === $theme_style ) {
        $custom_css .= "
            :root {
                --wpca-primary-color: #607D8B; /* Blue Gray */
                --wpca-background-color: #ECEFF1; /* Lighter Blue Gray */
                --wpca-text-color: #37474F; /* Darker Blue Gray */
            }
        ";
    } elseif ( 'mint' === $theme_style ) {
        $custom_css .= "
            :root {
                --wpca-primary-color: #4CAF50;
                --wpca-background-color: #E8F5E9;
                --wpca-text-color: #2E7D32;
            }
        ";
    } elseif ( 'dark' === $theme_style ) {
        $custom_css .= "
            :root {
                --wpca-primary-color: #0073aa; /* WordPress Blue for contrast */
                --wpca-background-color: #212121;
                --wpca-text-color: #E0E0E0;
            }
        ";
    }

    // Apply font stack
    if ( 'system' === $font_stack ) {
        $custom_css .= "
            body.wp-admin, #adminmenu, #adminmenumain, .wp-core-ui, .wp-admin.wp-core-ui .button, .wp-admin.wp-core-ui .button-primary, .wp-admin.wp-core-ui .button-secondary {
                font-family: {$system_font_stack};
            }
        ";
    }
    // Future: Google Fonts loading logic here

    if ( ! empty( $custom_css ) ) {
        echo '<style type="text/css" id="wpca-custom-styles">' . $custom_css . '</style>';
    }
}
add_action( 'admin_head', 'wpca_apply_custom_styles' );


/**
 * Remove admin menu items based on settings.
 */
function wpca_remove_admin_menu_items() {
    $options = WPCA_Settings::get_options();
    $menu_items_to_hide = $options['hide_admin_menu_items'] ?? array();

    if ( in_array( 'dashboard', $menu_items_to_hide ) ) {
        remove_menu_page( 'index.php' ); // Dashboard
    }
    if ( in_array( 'posts', $menu_items_to_hide ) ) {
        remove_menu_page( 'edit.php' ); // Posts
    }
    if ( in_array( 'media', $menu_items_to_hide ) ) {
        remove_menu_page( 'upload.php' ); // Media
    }
    if ( in_array( 'pages', $menu_items_to_hide ) ) {
        remove_menu_page( 'edit.php?post_type=page' ); // Pages
    }
    if ( in_array( 'comments', $menu_items_to_hide ) ) {
        remove_menu_page( 'edit-comments.php' ); // Comments
    }
    if ( in_array( 'themes.php', $menu_items_to_hide ) ) {
        remove_menu_page( 'themes.php' ); // Appearance
    }
    if ( in_array( 'plugins.php', $menu_items_to_hide ) ) {
        remove_menu_page( 'plugins.php' ); // Plugins
    }
    if ( in_array( 'users.php', $menu_items_to_hide ) ) {
        remove_menu_page( 'users.php' ); // Users
    }
    if ( in_array( 'tools.php', $menu_items_to_hide ) ) {
        remove_menu_page( 'tools.php' ); // Tools
    }
    if ( in_array( 'options-general.php', $menu_items_to_hide ) ) {
        remove_menu_page( 'options-general.php' ); // Settings
    }
    // Add more menu removals here based on settings
}
add_action( 'admin_menu', 'wpca_remove_admin_menu_items', 999 ); // High priority to ensure it runs after other plugins add menus


/**
 * Remove admin bar items based on settings.
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function wpca_remove_admin_bar_items( $wp_admin_bar ) {
    $options = WPCA_Settings::get_options();
    $admin_bar_items_to_hide = $options['hide_admin_bar_items'] ?? array();

    if ( in_array( 'wp-logo', $admin_bar_items_to_hide ) ) {
        $wp_admin_bar->remove_node( 'wp-logo' );
    }
    if ( in_array( 'site-name', $admin_bar_items_to_hide ) ) {
        $wp_admin_bar->remove_node( 'site-name' );
    }
    if ( in_array( 'updates', $admin_bar_items_to_hide ) ) {
        $wp_admin_bar->remove_node( 'updates' );
    }
    if ( in_array( 'comments', $admin_bar_items_to_hide ) ) {
        $wp_admin_bar->remove_node( 'comments' );
    }
    if ( in_array( 'new-content', $admin_bar_items_to_hide ) ) {
        $wp_admin_bar->remove_node( 'new-content' );
    }
    if ( in_array( 'my-account', $admin_bar_items_to_hide ) ) {
        $wp_admin_bar->remove_node( 'my-account' );
    }
    // Add more admin bar item removals here based on settings
}
add_action( 'admin_bar_menu', 'wpca_remove_admin_bar_items', 999 ); // High priority


// Future core functions for hiding menus, applying styles, etc., will go here.