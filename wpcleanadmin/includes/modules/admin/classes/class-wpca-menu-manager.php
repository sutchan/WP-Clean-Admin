<?php
/**
 * WPCleanAdmin Menu Manager Class
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

require_once __DIR__ . '/class-wpca-menu-manager-data.php';
require_once __DIR__ . '/class-wpca-menu-manager-restrict.php';

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Declare WordPress functions for IDE compatibility
if ( ! function_exists( 'wp_get_current_user' ) ) {
    function wp_get_current_user() {}
}
if ( ! function_exists( 'add_action' ) ) {
    function add_action() {}
}
if ( ! function_exists( 'remove_meta_box' ) ) {
    function remove_meta_box() {}
}
if ( ! function_exists( 'remove_action' ) ) {
    function remove_action() {}
}
if ( ! function_exists( 'update_option' ) ) {
    function update_option() {}
}

/**
 * Menu_Manager class
 */
class Menu_Manager {

    /**
     * Singleton instance
     *
     * @var Menu_Manager
     */
    private static $instance = null;

    /**
     * 菜单数据处理器
     *
     * @var Menu_Manager_Data
     */
    private $data;

    /**
     * 菜单限制处理器
     *
     * @var Menu_Manager_Restrict
     */
    private $restrict;

    /**
     * Get singleton instance
     *
     * @return Menu_Manager
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->data     = new Menu_Manager_Data();
        $this->restrict = new Menu_Manager_Restrict();
        $this->init();
    }

    /**
     * Initialize the menu manager module
     */
    public function init() {
        // Load settings
        $settings = \wpca_get_settings();

        // Apply menu optimizations based on settings
        if ( isset( $settings['menu'] ) && function_exists( 'add_action' ) ) {
            // Remove dashboard widgets
            if ( isset( $settings['menu']['remove_dashboard_widgets'] ) && $settings['menu']['remove_dashboard_widgets'] ) {
                \add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
            }

            // Apply menu customizations
            $this->simplify_admin_menu_by_settings();

            // Clean admin bar
            if ( isset( $settings['general']['clean_admin_bar'] ) && $settings['general']['clean_admin_bar'] ) {
                \add_action( 'admin_bar_menu', array( $this, 'clean_admin_bar' ), 999 );
            }

            // Apply role-based menu restrictions
            \add_action( 'admin_menu', array( $this, 'apply_role_based_menu_restrictions' ), 999 );
        }
    }

    /**
     * Remove dashboard widgets
     */
    public function remove_dashboard_widgets() {
        // Remove default WordPress dashboard widgets
        if ( function_exists( '\remove_meta_box' ) ) {
            \remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
            \remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
            \remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
            \remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
            \remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
            \remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
            \remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
            \remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
        }

        // Remove WordPress welcome panel
        if ( function_exists( '\remove_action' ) ) {
            \remove_action( 'welcome_panel', 'wp_welcome_panel' );
        }
    }

    /**
     * Simplify admin menu
     */
    public function simplify_admin_menu() {
        global $menu, $submenu;

        // Menu items to remove
        $menu_items_to_remove = array(
            'edit-comments.php', // Comments
            'edit.php?post_type=page', // Pages
            'upload.php', // Media
            'themes.php', // Appearance
            'plugins.php', // Plugins
            'users.php', // Users
            'tools.php', // Tools
            'options-general.php', // Settings
        );

        // Remove menu items
        foreach ( $menu as $key => $menu_item ) {
            if ( isset( $menu_item[2] ) && in_array( $menu_item[2], $menu_items_to_remove, true ) ) {
                unset( $menu[ $key ] );
            }
        }

        // Remove submenu items
        if ( isset( $submenu['edit.php'] ) ) {
            // Remove Posts submenu items
            $post_submenu_to_remove = array(
                'edit.php?post_type=post', // All Posts
                'post-new.php', // Add New
                'edit-tags.php?taxonomy=category', // Categories
                'edit-tags.php?taxonomy=post_tag', // Tags
            );

            foreach ( $submenu['edit.php'] as $key => $submenu_item ) {
                if ( isset( $submenu_item[2] ) && in_array( $submenu_item[2], $post_submenu_to_remove, true ) ) {
                    unset( $submenu['edit.php'][ $key ] );
                }
            }
        }
    }

    /**
     * Clean admin bar
     *
     * @param \WP_Admin_Bar $wp_admin_bar Admin bar object
     */
    public function clean_admin_bar( \WP_Admin_Bar $wp_admin_bar ): void {
        // Remove default WordPress admin bar items
        $wp_admin_bar->remove_node( 'wp-logo' );
        $wp_admin_bar->remove_node( 'about' );
        $wp_admin_bar->remove_node( 'wporg' );
        $wp_admin_bar->remove_node( 'documentation' );
        $wp_admin_bar->remove_node( 'support-forums' );
        $wp_admin_bar->remove_node( 'feedback' );
        $wp_admin_bar->remove_node( 'site-name' );
        $wp_admin_bar->remove_node( 'view-site' );
        $wp_admin_bar->remove_node( 'comments' );
        $wp_admin_bar->remove_node( 'new-content' );
        $wp_admin_bar->remove_node( 'w3tc' ); // W3 Total Cache
        $wp_admin_bar->remove_node( 'wpseo-menu' ); // Yoast SEO
    }

    /**
     * Get menu items (delegated to data handler)
     *
     * @return array
     */
    public function get_menu_items() {
        return $this->data->get_menu_items();
    }

    /**
     * Save menu items (delegated to data handler)
     *
     * @param array $menu_items
     * @return array
     */
    public function save_menu_items( $menu_items ) {
        return $this->data->save_menu_items( $menu_items );
    }

    /**
     * Remove dashboard widgets based on settings (delegated)
     */
    public function remove_dashboard_widgets_by_settings(): void {
        $this->restrict->remove_dashboard_widgets_by_settings();
    }

    /**
     * Apply role-based menu restrictions (delegated)
     */
    public function apply_role_based_menu_restrictions() {
        $this->restrict->apply_role_based_menu_restrictions();
    }

    /**
     * Simplify admin menu based on settings
     */
    public function simplify_admin_menu_by_settings() {
        // Load settings
        $settings = \wpca_get_settings();

        if ( isset( $settings['menu'] ) && function_exists( 'add_action' ) ) {
            // Add action to customize menu
            \add_action(
                'admin_menu',
                function () use ( $settings ) {
                    global $menu, $submenu;

                    // Apply menu order if specified
                    if ( isset( $settings['menu']['menu_order'] ) && is_array( $settings['menu']['menu_order'] ) ) {
                        $this->data->reorder_menu( $menu, $settings['menu']['menu_order'] );
                    }

                    // Remove menu items if specified
                    if ( isset( $settings['menu']['menu_items'] ) ) {
                        $menu_items_to_remove = $settings['menu']['menu_items'];

                        // Remove top-level menu items
                        foreach ( $menu as $key => $menu_item ) {
                            if ( isset( $menu_item[2] ) && isset( $menu_items_to_remove[ $menu_item[2] ] ) && $menu_items_to_remove[ $menu_item[2] ] ) {
                                unset( $menu[ $key ] );
                            }
                        }

                        // Remove submenu items
                        foreach ( $submenu as $parent_slug => $submenu_items ) {
                            foreach ( $submenu_items as $key => $submenu_item ) {
                                if ( isset( $submenu_item[2] ) && isset( $menu_items_to_remove[ $submenu_item[2] ] ) && $menu_items_to_remove[ $submenu_item[2] ] ) {
                                    unset( $submenu[ $parent_slug ][ $key ] );
                                }
                            }
                        }
                    }
                },
                999
            );
        }
    }

    /**
     * Clean up admin menu (delegated to restrict handler)
     */
    public function cleanup_admin_menu(): void {
        $this->restrict->cleanup();
    }
}
