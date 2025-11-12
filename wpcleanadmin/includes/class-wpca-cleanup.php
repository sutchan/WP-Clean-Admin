<?php
/**
 * WP Clean Admin Cleanup Class
 *
 * Handles various cleanup tasks in the WordPress admin area.
 *
 * @package WP_Clean_Admin
 * @version 1.7.11
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WPCA_Cleanup {

    /**
     * Constructor.
     */
    public function __construct() {
        // Enqueue cleanup specific styles/scripts.
        if (function_exists('add_action')) {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_cleanup_assets']);

            // Remove unnecessary admin features based on settings.
            add_action('admin_init', [$this, 'remove_unnecessary_features']);

            // Remove specific menu items for non-admin users.
            add_action('admin_menu', [$this, 'remove_menu_items_for_non_admins'], 999);
        }
    }

    /**
     * Enqueue cleanup-specific assets.
     */
    public function enqueue_cleanup_assets() {
        // Currently no specific assets for cleanup, but can be added here if needed.
    }

    /**
     * Remove unnecessary admin features.
     */
    public function remove_unnecessary_features() {
        if (class_exists('WPCA_Settings') && method_exists('WPCA_Settings', 'get_options')) {
            $options = WPCA_Settings::get_options();

            // Hide screen options tab
            if (isset($options['hide_screen_options']) && $options['hide_screen_options'] && function_exists('add_filter')) {
                add_filter('screen_options_show_screen', '__return_false');
            }

            // Hide help tab
            if (isset($options['hide_help_tab']) && $options['hide_help_tab'] && function_exists('add_filter')) {
                add_filter('contextual_help', [$this, 'remove_help_tab'], 999, 3);
            }

            // Remove WordPress logo from admin bar
            if (isset($options['hide_wp_logo']) && $options['hide_wp_logo'] && function_exists('add_action')) {
                add_action('admin_bar_menu', [$this, 'remove_wp_logo_from_admin_bar'], 999);
            }

            // Disable comments
            if (isset($options['disable_comments']) && $options['disable_comments']) {
                $this->disable_comments_globally();
            }

            // Remove update notices for non-admin users
            if (isset($options['hide_update_notices']) && $options['hide_update_notices'] && function_exists('add_action')) {
                add_action('admin_head', [$this, 'hide_update_notices_for_non_admins'], 1);
            }
            
            // Hide WordPress footer
            if (isset($options['hide_wpfooter']) && $options['hide_wpfooter'] && function_exists('add_action')) {
                add_action('admin_head', [$this, 'hide_wordpress_footer']);
            }
            
            // Hide frontend admin bar
            if (isset($options['hide_frontend_adminbar']) && $options['hide_frontend_adminbar'] && function_exists('add_filter')) {
                add_filter('show_admin_bar', '__return_false');
            }
        }
    }

    /**
     * Remove the help tab.
     */
    public function remove_help_tab($old_help, $screen_id, $screen) {
        if (isset($screen) && method_exists($screen, 'remove_help_tabs')) {
            $screen->remove_help_tabs();
        }
        return $old_help;
    }

    /**
     * Remove WordPress logo from admin bar.
     */
    public function remove_wp_logo_from_admin_bar($wp_admin_bar) {
        if (isset($wp_admin_bar) && method_exists($wp_admin_bar, 'remove_node')) {
            $wp_admin_bar->remove_node('wp-logo');
        }
    }

    /**
     * Disable comments globally.
     */
    private function disable_comments_globally() {
        // Redirect any user trying to access comments page
        if (function_exists('add_action')) {
            add_action('admin_init', function () {
                global $pagenow;
                if ($pagenow === 'edit-comments.php' && function_exists('wp_redirect') && function_exists('admin_url')) {
                    wp_redirect(admin_url());
                    exit;
                }
                // Remove comments metabox from dashboard
                if (function_exists('remove_meta_box')) {
                    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
                }
                // Disable comments on posts and pages
                if (function_exists('remove_post_type_support')) {
                    remove_post_type_support('post', 'comments');
                    remove_post_type_support('page', 'comments');
                }
            });

            // Close comments on the frontend
            if (function_exists('add_filter')) {
                add_filter('comments_open', '__return_false', 20, 2);
                add_filter('pings_open', '__return_false', 20, 2);

                // Hide existing comments
                add_filter('comments_array', '__return_empty_array', 10, 2);
            }

            // Remove comments link from admin menu
            if (function_exists('add_action')) {
                add_action('admin_menu', function () {
                    if (function_exists('remove_menu_page')) {
                        remove_menu_page('edit-comments.php');
                    }
                });
            }

            // Remove comments link from admin bar
            if (function_exists('add_action')) {
                add_action('wp_before_admin_bar_render', function () {
                    global $wp_admin_bar;
                    if (isset($wp_admin_bar) && method_exists($wp_admin_bar, 'remove_menu')) {
                        $wp_admin_bar->remove_menu('comments');
                    }
                });
            }
        }
    }

    /**
     * Remove specific menu items for non-admin users.
     */
    public function remove_menu_items_for_non_admins() {
        if (function_exists('current_user_can') && !current_user_can('manage_options')) {
            // Example: remove Tools menu for non-admin users
            // if (function_exists('remove_menu_page')) {
            //     remove_menu_page('tools.php');
            //     remove_submenu_page('options-general.php', 'options-writing.php');
            // }
        }
    }

    /**
     * Hide update notices for non-admin users.
     */
    public function hide_update_notices_for_non_admins() {
        if (function_exists('current_user_can') && !current_user_can('update_core') && function_exists('remove_action')) {
            remove_action('admin_notices', 'update_nag', 3);
            
            // Remove the WordPress SEO update notices if Yoast SEO is active
            if (function_exists('remove_action')) {
                remove_action('admin_notices', array('Yoast_Notification_Center', 'display_notifications'));
                remove_action('all_admin_notices', array('Yoast_Notification_Center', 'display_notifications'));
            }
        }
    }
    
    /**
     * Hide WordPress footer
     */
    public function hide_wordpress_footer() {
        ?>
        <style type="text/css">
            #wpfooter {
                display: none !important;
            }
        </style>
        <?php
    }
}
?>