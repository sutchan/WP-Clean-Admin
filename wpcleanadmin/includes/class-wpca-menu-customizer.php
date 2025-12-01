<?php
/**
 * Menu Customizer class for WP Clean Admin plugin
 *
 * @package WPCleanAdmin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

namespace WPCleanAdmin;

/**
 * Menu_Customizer class
 */
class Menu_Customizer {
    
    /**
     * Singleton instance
     *
     * @var Menu_Customizer
     */
    private static $instance;
    
    /**
     * Get singleton instance
     *
     * @return Menu_Customizer
     */
    public static function get_instance() {
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
     * Initialize the menu customizer module
     */
    public function init() {
        // Load settings
        $settings = $this->get_settings();
        
        // Apply menu customizations based on settings
        if ( isset( $settings['enabled'] ) && $settings['enabled'] ) {
            // Add menu customization hooks
            if ( function_exists( 'add_action' ) ) {
                \add_action( 'admin_menu', array( $this, 'customize_admin_menu' ), 999 );
                \add_action( 'admin_bar_menu', array( $this, 'customize_admin_bar' ), 999 );
            }
        }
    }
    
    /**
     * Customize admin menu
     */
    public function customize_admin_menu() {
        global $menu, $submenu;
        
        // Load settings
        $settings = $this->get_settings();
        
        // Customize menu items based on settings
        if ( isset( $settings['menu_items'] ) ) {
            foreach ( $settings['menu_items'] as $menu_slug => $menu_settings ) {
                // Hide menu item
                if ( isset( $menu_settings['hidden'] ) && $menu_settings['hidden'] ) {
                    // Find and remove top-level menu
                    foreach ( $menu as $key => $menu_item ) {
                        if ( isset( $menu_item[2] ) && $menu_item[2] === $menu_slug ) {
                            unset( $menu[$key] );
                            break;
                        }
                    }
                }
                
                // Customize menu title
                if ( isset( $menu_settings['title'] ) && ! empty( $menu_settings['title'] ) ) {
                    foreach ( $menu as $key => $menu_item ) {
                        if ( isset( $menu_item[2] ) && $menu_item[2] === $menu_slug ) {
                            $menu[$key][0] = $menu_settings['title'];
                            break;
                        }
                    }
                }
                
                // Customize menu icon
                if ( isset( $menu_settings['icon'] ) && ! empty( $menu_settings['icon'] ) ) {
                    foreach ( $menu as $key => $menu_item ) {
                        if ( isset( $menu_item[2] ) && $menu_item[2] === $menu_slug ) {
                            $menu[$key][6] = $menu_settings['icon'];
                            break;
                        }
                    }
                }
                
                // Customize menu position
                if ( isset( $menu_settings['position'] ) && is_numeric( $menu_settings['position'] ) ) {
                    foreach ( $menu as $key => $menu_item ) {
                        if ( isset( $menu_item[2] ) && $menu_item[2] === $menu_slug ) {
                            $menu[$key][5] = $menu_settings['position'];
                            break;
                        }
                    }
                }
            }
        }
        
        // Customize submenu items
        if ( isset( $settings['submenu_items'] ) ) {
            foreach ( $settings['submenu_items'] as $parent_slug => $submenu_items ) {
                if ( isset( $submenu[$parent_slug] ) ) {
                    foreach ( $submenu_items as $submenu_slug => $submenu_settings ) {
                        // Hide submenu item
                        if ( isset( $submenu_settings['hidden'] ) && $submenu_settings['hidden'] ) {
                            foreach ( $submenu[$parent_slug] as $key => $submenu_item ) {
                                if ( isset( $submenu_item[2] ) && $submenu_item[2] === $submenu_slug ) {
                                    unset( $submenu[$parent_slug][$key] );
                                    break;
                                }
                            }
                        }
                        
                        // Customize submenu title
                        if ( isset( $submenu_settings['title'] ) && ! empty( $submenu_settings['title'] ) ) {
                            foreach ( $submenu[$parent_slug] as $key => $submenu_item ) {
                                if ( isset( $submenu_item[2] ) && $submenu_item[2] === $submenu_slug ) {
                                    $submenu[$parent_slug][$key][0] = $submenu_settings['title'];
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Apply menu order
        if ( isset( $settings['menu_order'] ) && ! empty( $settings['menu_order'] ) ) {
            $this->apply_menu_order( $menu, $settings['menu_order'] );
        }
        
        // Apply menu groups
        if ( isset( $settings['menu_groups'] ) && ! empty( $settings['menu_groups'] ) ) {
            $this->apply_menu_groups( $menu, $settings['menu_groups'] );
        }
    }
    
    /**
     * Apply menu order to admin menu
     *
     * @param array $menu Admin menu array
     * @param array $menu_order Menu order settings
     */
    private function apply_menu_order( &$menu, $menu_order ) {
        // Create a new menu array with the desired order
        $new_menu = array();
        $remaining_menu = $menu;
        
        // Add menu items in the specified order
        foreach ( $menu_order as $menu_slug ) {
            foreach ( $remaining_menu as $key => $menu_item ) {
                if ( isset( $menu_item[2] ) && $menu_item[2] === $menu_slug ) {
                    $new_menu[] = $menu_item;
                    unset( $remaining_menu[$key] );
                    break;
                }
            }
        }
        
        // Add remaining menu items at the end
        foreach ( $remaining_menu as $menu_item ) {
            $new_menu[] = $menu_item;
        }
        
        // Update the menu with the new order
        $menu = $new_menu;
    }
    
    /**
     * Apply menu groups to admin menu
     *
     * @param array $menu Admin menu array
     * @param array $menu_groups Menu groups settings
     */
    private function apply_menu_groups( &$menu, $menu_groups ) {
        // This is a placeholder for menu groups implementation
        // Menu groups would require more complex changes to the WordPress menu system
        // For now, we'll just log that menu groups are enabled
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Menu groups enabled: ' . print_r( $menu_groups, true ) );
        }
    }
    
    /**
     * Customize admin bar
     *
     * @param WP_Admin_Bar $wp_admin_bar Admin bar object
     */
    public function customize_admin_bar( $wp_admin_bar ) {
        // Load settings
        $settings = $this->get_settings();
        
        // Customize admin bar items based on settings
        if ( isset( $settings['admin_bar_items'] ) ) {
            foreach ( $settings['admin_bar_items'] as $node_id => $node_settings ) {
                // Hide admin bar item
                if ( isset( $node_settings['hidden'] ) && $node_settings['hidden'] ) {
                    $wp_admin_bar->remove_node( $node_id );
                }
                
                // Customize admin bar item
                if ( isset( $node_settings['title'] ) && ! empty( $node_settings['title'] ) ) {
                    $node = $wp_admin_bar->get_node( $node_id );
                    if ( $node ) {
                        $node->title = $node_settings['title'];
                        $wp_admin_bar->add_node( $node );
                    }
                }
            }
        }
    }
    
    /**
     * Get menu customizer settings
     *
     * @return array Menu customizer settings
     */
    public function get_settings() {
        // Get settings from options
        $settings = ( function_exists( 'get_option' ) ? \get_option( 'wpca_menu_customizer_settings', array() ) : array() );
        
        // Set default settings
        $default_settings = array(
            'enabled' => false,
            'menu_items' => array(),
            'submenu_items' => array(),
            'admin_bar_items' => array()
        );
        
        return ( function_exists( 'wp_parse_args' ) ? \wp_parse_args( $settings, $default_settings ) : array_merge( $default_settings, $settings ) );
    }
    
    /**
     * Save menu customizer settings
     *
     * @param array $settings Settings to save
     * @return bool Save result
     */
    public function save_settings( $settings ) {
        // Save settings to options
        return ( function_exists( 'update_option' ) ? \update_option( 'wpca_menu_customizer_settings', $settings ) : false );
    }
    
    /**
     * Reset menu customizer settings
     *
     * @return bool Reset result
     */
    public function reset_settings() {
        // Delete settings from options
        return ( function_exists( 'delete_option' ) ? \delete_option( 'wpca_menu_customizer_settings' ) : false );
    }
    
    /**
     * Get admin menu structure
     *
     * @return array Admin menu structure
     */
    public function get_admin_menu_structure() {
        global $menu, $submenu;
        
        $menu_structure = array();
        
        // Get top-level menu items
        foreach ( $menu as $key => $menu_item ) {
            if ( ! empty( $menu_item[0] ) && $menu_item[0] !== '-' ) {
                $menu_data = array(
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
                        $menu_data['submenu'][] = array(
                            'id' => $submenu_key,
                            'title' => $submenu_item[0],
                            'slug' => $submenu_item[2],
                            'capability' => $submenu_item[1]
                        );
                    }
                }
                
                $menu_structure[] = $menu_data;
            }
        }
        
        return $menu_structure;
    }
    
    /**
     * Get admin bar structure
     *
     * @return array Admin bar structure
     */
    public function get_admin_bar_structure() {
        global $wp_admin_bar;
        
        $admin_bar_structure = array();
        
        // Get admin bar nodes
        $nodes = $wp_admin_bar->get_nodes();
        
        if ( ! empty( $nodes ) ) {
            foreach ( $nodes as $node_id => $node ) {
                $admin_bar_structure[] = array(
                    'id' => $node_id,
                    'title' => $node->title,
                    'parent' => $node->parent,
                    'href' => $node->href,
                    'group' => $node->group,
                    'meta' => $node->meta
                );
            }
        }
        
        return $admin_bar_structure;
    }
    
    /**
     * Export menu customizer settings
     *
     * @return string Exported settings
     */
    public function export_settings() {
        // Get settings
        $settings = $this->get_settings();
        
        // Export settings as JSON
        return json_encode( $settings, JSON_PRETTY_PRINT );
    }
    
    /**
     * Import menu customizer settings
     *
     * @param string $imported_settings Imported settings
     * @return array Import results
     */
    public function import_settings( $imported_settings ) {
        $results = array(
            'success' => false,
            'message' => \__( 'Failed to import settings', WPCA_TEXT_DOMAIN )
        );
        
        // Decode imported settings
        $settings = json_decode( $imported_settings, true );
        
        // Check if settings are valid
        if ( is_array( $settings ) ) {
            // Save imported settings
            if ( $this->save_settings( $settings ) ) {
                $results['success'] = true;
                $results['message'] = \__( 'Settings imported successfully', WPCA_TEXT_DOMAIN );
            }
        } else {
            $results['message'] = \__( 'Invalid settings format', WPCA_TEXT_DOMAIN );
        }
        
        return $results;
    }
}