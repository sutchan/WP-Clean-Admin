<?php
/**
 * WPCleanAdmin Menu Manager Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
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
        $this->init();
    }
    
    /**
     * Initialize the menu manager module
     */
    public function init() {
        // Load settings
        $settings = wpca_get_settings();
        
        // Apply menu optimizations based on settings
        if ( isset( $settings['menu'] ) && function_exists( 'add_action' ) ) {
            // Remove dashboard widgets
            if ( isset( $settings['menu']['remove_dashboard_widgets'] ) && $settings['menu']['remove_dashboard_widgets'] ) {
                \add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
            }
            
            // Simplify admin menu
            if ( isset( $settings['menu']['simplify_admin_menu'] ) && $settings['menu']['simplify_admin_menu'] ) {
                \add_action( 'admin_menu', array( $this, 'simplify_admin_menu' ), 999 );
            }
            
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
            if ( isset( $menu_item[2] ) && in_array( $menu_item[2], $menu_items_to_remove ) ) {
                unset( $menu[$key] );
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
                if ( isset( $submenu_item[2] ) && in_array( $submenu_item[2], $post_submenu_to_remove ) ) {
                    unset( $submenu['edit.php'][$key] );
                }
            }
        }
    }
    
    /**
     * Clean admin bar
     *
     * @param WP_Admin_Bar $wp_admin_bar Admin bar object
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
     * Get menu items
     *
     * @return array Menu items
     */
    public function get_menu_items() {
        global $menu, $submenu;
        
        $menu_items = array();
        
        // Get top-level menu items
        foreach ( $menu as $key => $menu_item ) {
            if ( ! empty( $menu_item[0] ) && $menu_item[0] !== '-' ) {
                $menu_item_data = array(
                    'id' => $key,
                    'title' => $menu_item[0],
                    'slug' => $menu_item[2],
                    'capability' => $menu_item[1],
                    'icon' => $menu_item[6],
                    'position' => $menu_item[5],
                    'submenu' => array()
                );
                
                // Get submenu items
                if ( isset( $submenu[$menu_item[2]] ) ) {
                    foreach ( $submenu[$menu_item[2]] as $submenu_key => $submenu_item ) {
                        $menu_item_data['submenu'][] = array(
                            'id' => $submenu_key,
                            'title' => $submenu_item[0],
                            'slug' => $submenu_item[2],
                            'capability' => $submenu_item[1]
                        );
                    }
                }
                
                $menu_items[] = $menu_item_data;
            }
        }
        
        return $menu_items;
    }
    
    /**
     * Save menu items
     *
     * @param array $menu_items Menu items to save
     * @return array Save results
     */
    public function save_menu_items( $menu_items ) {
        $results = array(
            'success' => true,
            'message' => \__( 'Menu items saved successfully', WPCA_TEXT_DOMAIN )
        );
        
        // Save menu items to options
        if ( function_exists( 'update_option' ) ) {
            \update_option( 'wpca_menu_items', $menu_items );
        }
        
        return $results;
    }
    
    /**
     * Remove dashboard widgets based on settings
     */
    public function remove_dashboard_widgets_by_settings(): void {
        // Load settings
        $settings = wpca_get_settings();
        
        if ( isset( $settings['menu']['dashboard_widgets'] ) && function_exists( '\remove_meta_box' ) ) {
            $widgets_to_remove = $settings['menu']['dashboard_widgets'];
            
            foreach ( $widgets_to_remove as $widget_id => $remove ) {
                if ( $remove ) {
                    \remove_meta_box( $widget_id, 'dashboard', 'normal' );
                    \remove_meta_box( $widget_id, 'dashboard', 'side' );
                    \remove_meta_box( $widget_id, 'dashboard', 'column3' );
                    \remove_meta_box( $widget_id, 'dashboard', 'column4' );
                }
            }
        }
    }
    
    /**
     * Simplify admin menu based on settings
     */
    public function simplify_admin_menu_by_settings() {
        // Load settings
        $settings = wpca_get_settings();
        
        if ( isset( $settings['menu']['menu_items'] ) && function_exists( 'add_action' ) ) {
            $menu_items_to_remove = $settings['menu']['menu_items'];
            
            // Add action to remove menu items
            \add_action( 'admin_menu', function() use ( $menu_items_to_remove ) {
                global $menu, $submenu;
                
                // Remove top-level menu items
                foreach ( $menu as $key => $menu_item ) {
                    if ( isset( $menu_item[2] ) && isset( $menu_items_to_remove[$menu_item[2]] ) && $menu_items_to_remove[$menu_item[2]] ) {
                        unset( $menu[$key] );
                    }
                }
                
                // Remove submenu items
                foreach ( $submenu as $parent_slug => $submenu_items ) {
                    foreach ( $submenu_items as $key => $submenu_item ) {
                        if ( isset( $submenu_item[2] ) && isset( $menu_items_to_remove[$submenu_item[2]] ) && $menu_items_to_remove[$submenu_item[2]] ) {
                            unset( $submenu[$parent_slug][$key] );
                        }
                    }
                }
            }, 999 );
        }
    }
    
    /**
     * Apply role-based menu restrictions
     */
    public function apply_role_based_menu_restrictions() {
        global $menu, $submenu;
        
        // Get current user
        if ( ! function_exists( 'wp_get_current_user' ) ) {
            return;
        }
        
        $current_user = \wp_get_current_user();
        if ( ! $current_user || ! isset( $current_user->roles ) ) {
            return;
        }
        
        $user_roles = $current_user->roles;
        
        // Load settings
        $settings = wpca_get_settings();
        
        // Check if role-based menu restrictions are enabled
        if ( ! isset( $settings['menu']['role_based_restrictions'] ) || ! $settings['menu']['role_based_restrictions'] ) {
            return;
        }
        
        // Get role-based menu restrictions
        $role_restrictions = isset( $settings['menu']['role_menu_restrictions'] ) ? $settings['menu']['role_menu_restrictions'] : array();
        
        // Menu items to remove for current user
        $menu_items_to_remove = array();
        
        // Check each role restriction
        foreach ( $role_restrictions as $role => $restrictions ) {
            if ( in_array( $role, $user_roles ) && isset( $restrictions['menu_items'] ) ) {
                $menu_items_to_remove = array_merge( $menu_items_to_remove, array_keys( array_filter( $restrictions['menu_items'] ) ) );
            }
        }
        
        // Remove duplicates
        $menu_items_to_remove = array_unique( $menu_items_to_remove );
        
        // Remove top-level menu items
        foreach ( $menu as $key => $menu_item ) {
            if ( isset( $menu_item[2] ) && in_array( $menu_item[2], $menu_items_to_remove ) ) {
                unset( $menu[$key] );
            }
        }
        
        // Remove submenu items
        foreach ( $submenu as $parent_slug => $submenu_items ) {
            foreach ( $submenu_items as $key => $submenu_item ) {
                if ( isset( $submenu_item[2] ) && in_array( $submenu_item[2], $menu_items_to_remove ) ) {
                    unset( $submenu[$parent_slug][$key] );
                }
            }
        }
    }
    
    /**
     * Clean up admin menu
     */
    public function cleanup_admin_menu(): void {
        // Load settings
        $settings = wpca_get_settings();
        
        if ( isset( $settings['menu'] ) ) {
            // Remove dashboard widgets
            if ( isset( $settings['menu']['remove_dashboard_widgets'] ) && $settings['menu']['remove_dashboard_widgets'] ) {
                $this->remove_dashboard_widgets();
            }
            
            // Simplify admin menu
            if ( isset( $settings['menu']['simplify_admin_menu'] ) && $settings['menu']['simplify_admin_menu'] ) {
                $this->simplify_admin_menu();
            }
            
            // Clean admin bar
            if ( isset( $settings['general']['clean_admin_bar'] ) && $settings['general']['clean_admin_bar'] && function_exists( 'add_action' ) ) {
                \add_action( 'admin_bar_menu', array( $this, 'clean_admin_bar' ), 999 );
            }
        }
    }
}
