<?php
/**
 * WPCleanAdmin Settings Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @update_date 2026-01-28
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load WordPress stubs for IDE compatibility
require_once __DIR__ . '/wpca-wordpress-stubs.php';

class Settings {
    
    private static $instance = null;
    
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    public function init() {
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
            \add_action( 'admin_init', array( $this, 'register_settings' ) );
            \add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        }
    }
    
    public function register_settings_page() {
        if ( function_exists( 'add_options_page' ) ) {
            \add_options_page(
                \__( 'WP Clean Admin', \WPCA_TEXT_DOMAIN ),
                \__( 'Clean Admin', \WPCA_TEXT_DOMAIN ),
                'manage_options',
                'wp-clean-admin',
                array( $this, 'render_settings_page' )
            );
        }
    }
    
    public function register_settings() {
        // Register general settings section
        if ( function_exists( 'add_settings_section' ) ) {
            \add_settings_section(
                'wpca_general_settings',
                \__( 'General Settings', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_general_settings_section' ),
                'wp-clean-admin'
            );
        }
        
        // Register cleanup settings section
        if ( function_exists( 'add_settings_section' ) ) {
            \add_settings_section(
                'wpca_cleanup_settings',
                \__( 'Cleanup Settings', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_cleanup_settings_section' ),
                'wp-clean-admin'
            );
        }
        
        // Register performance settings section
        if ( function_exists( 'add_settings_section' ) ) {
            \add_settings_section(
                'wpca_performance_settings',
                \__( 'Performance Settings', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_performance_settings_section' ),
                'wp-clean-admin'
            );
        }
        
        // Register security settings section
        if ( function_exists( 'add_settings_section' ) ) {
            \add_settings_section(
                'wpca_security_settings',
                \__( 'Security Settings', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_security_settings_section' ),
                'wp-clean-admin'
            );
        }
        
        // Register general settings fields
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_clean_admin_bar',
                \__( 'Clean Admin Bar', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_clean_admin_bar_field' ),
                'wp-clean-admin',
                'wpca_general_settings'
            );
            
            \add_settings_field(
                'wpca_remove_wp_logo',
                \__( 'Remove WordPress Logo', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_remove_wp_logo_field' ),
                'wp-clean-admin',
                'wpca_general_settings'
            );
        }
        
        // Register cleanup settings fields
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_remove_dashboard_widgets',
                \__( 'Remove Dashboard Widgets', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_remove_dashboard_widgets_field' ),
                'wp-clean-admin',
                'wpca_cleanup_settings'
            );
            
            \add_settings_field(
                'wpca_simplify_admin_menu',
                \__( 'Simplify Admin Menu', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_simplify_admin_menu_field' ),
                'wp-clean-admin',
                'wpca_cleanup_settings'
            );
            
            \add_settings_field(
                'wpca_menu_customization',
                \__( 'Menu Customization', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_menu_customization_field' ),
                'wp-clean-admin',
                'wpca_cleanup_settings'
            );
        }
        
        // Register performance settings fields
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_optimize_database',
                \__( 'Optimize Database', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_optimize_database_field' ),
                'wp-clean-admin',
                'wpca_performance_settings'
            );
            
            \add_settings_field(
                'wpca_clean_transients',
                \__( 'Clean Transients', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_clean_transients_field' ),
                'wp-clean-admin',
                'wpca_performance_settings'
            );
        }
        
        // Register security settings fields
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_hide_wp_version',
                \__( 'Hide WordPress Version', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_hide_wp_version_field' ),
                'wp-clean-admin',
                'wpca_security_settings'
            );
            
            \add_settings_field(
                'wpca_disable_xmlrpc',
                \__( 'Disable XML-RPC', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_disable_xmlrpc_field' ),
                'wp-clean-admin',
                'wpca_security_settings'
            );
            
            \add_settings_field(
                'wpca_restrict_rest_api',
                \__( 'Restrict REST API Access', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_restrict_rest_api_field' ),
                'wp-clean-admin',
                'wpca_security_settings'
            );
            
            \add_settings_field(
                'wpca_restrict_admin_access',
                \__( 'Restrict Admin Access', \WPCA_TEXT_DOMAIN ),
                array( $this, 'render_restrict_admin_access_field' ),
                'wp-clean-admin',
                'wpca_security_settings'
            );
        }
        
        // Register setting
        if ( function_exists( 'register_setting' ) ) {
            \register_setting( 'wp-clean-admin', 'wpca_settings', array( $this, 'validate_settings' ) );
        }
    }
    
    public function render_general_settings_section() {
        echo '<p>' . \__( 'Configure general settings for WP Clean Admin plugin.', \WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    public function render_cleanup_settings_section() {
        echo '<p>' . \__( 'Configure cleanup settings for WP Clean Admin plugin.', \WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    public function render_performance_settings_section() {
        echo '<p>' . \__( 'Configure performance optimization settings for WP Clean Admin plugin.', \WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    public function render_security_settings_section() {
        echo '<p>' . \__( 'Configure security settings for WP Clean Admin plugin.', \WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    public function render_clean_admin_bar_field() {
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $clean_admin_bar = isset( $settings['general']['clean_admin_bar'] ) ? $settings['general']['clean_admin_bar'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[general][clean_admin_bar]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $clean_admin_bar, 1, false ) : ( $clean_admin_bar ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_clean_admin_bar"> ' . \__( 'Remove unnecessary items from the admin bar.', \WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    public function render_remove_wp_logo_field() {
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $remove_wp_logo = isset( $settings['general']['remove_wp_logo'] ) ? $settings['general']['remove_wp_logo'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[general][remove_wp_logo]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $remove_wp_logo, 1, false ) : ( $remove_wp_logo ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_remove_wp_logo"> ' . \__( 'Remove WordPress logo from admin bar.', \WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    public function render_remove_dashboard_widgets_field() {
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $remove_dashboard_widgets = isset( $settings['menu']['remove_dashboard_widgets'] ) ? $settings['menu']['remove_dashboard_widgets'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[menu][remove_dashboard_widgets]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $remove_dashboard_widgets, 1, false ) : ( $remove_dashboard_widgets ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_remove_dashboard_widgets"> ' . \__( 'Remove unnecessary dashboard widgets.', \WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    public function render_simplify_admin_menu_field() {
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $simplify_admin_menu = isset( $settings['menu']['simplify_admin_menu'] ) ? $settings['menu']['simplify_admin_menu'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[menu][simplify_admin_menu]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $simplify_admin_menu, 1, false ) : ( $simplify_admin_menu ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_simplify_admin_menu"> ' . \__( 'Simplify admin menu by removing unnecessary items.', \WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    public function render_menu_customization_field() {
        // Get menu manager instance
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        $menu_items = $menu_manager->get_menu_items();
        
        // Get current settings
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $menu_settings = isset( $settings['menu'] ) ? $settings['menu'] : array();
        $menu_items_settings = isset( $menu_settings['menu_items'] ) ? $menu_settings['menu_items'] : array();
        $menu_order = isset( $menu_settings['menu_order'] ) ? $menu_settings['menu_order'] : array();
        
        ?>
        <div class="wpca-menu-customization">
            <p><?php echo \esc_html( \__( 'Customize admin menu items and their order.', \WPCA_TEXT_DOMAIN ) ); ?></p>
            
            <div class="wpca-menu-items">
                <div class="wpca-menu-items-header">
                    <h4><?php echo \esc_html( \__( 'Menu Items', \WPCA_TEXT_DOMAIN ) ); ?></h4>
                    <div class="wpca-menu-items-actions">
                        <button type="button" class="button button-small" id="wpca-select-all-menu-items">
                            <?php echo \esc_html( \__( 'Select All', \WPCA_TEXT_DOMAIN ) ); ?>
                        </button>
                        <button type="button" class="button button-small" id="wpca-deselect-all-menu-items">
                            <?php echo \esc_html( \__( 'Deselect All', \WPCA_TEXT_DOMAIN ) ); ?>
                        </button>
                    </div>
                </div>
                <div class="wpca-menu-tree">
                    <?php foreach ( $menu_items as $menu_item ) : ?>
                        <div class="wpca-menu-item wpca-menu-item-level-1">
                            <div class="wpca-menu-item-header">
                                <input type="checkbox" name="wpca_settings[menu][menu_items][<?php echo \esc_attr( $menu_item['slug'] ); ?>]" value="1" <?php echo ( isset( $menu_items_settings[$menu_item['slug']] ) && $menu_items_settings[$menu_item['slug']] ) ? 'checked="checked"' : ''; ?> />
                                <label><?php echo \esc_html( $menu_item['title'] ); ?></label>
                                <span class="wpca-menu-item-slug"><?php echo \esc_html( $menu_item['slug'] ); ?></span>
                                <span class="wpca-menu-item-toggle">鈻?/span>
                            </div>
                            
                            <?php if ( ! empty( $menu_item['submenu'] ) ) : ?>
                                <div class="wpca-submenu-items">
                                    <?php foreach ( $menu_item['submenu'] as $submenu_item ) : ?>
                                        <div class="wpca-menu-item wpca-menu-item-level-2">
                                            <input type="checkbox" name="wpca_settings[menu][menu_items][<?php echo \esc_attr( $submenu_item['slug'] ); ?>]" value="1" <?php echo ( isset( $menu_items_settings[$submenu_item['slug']] ) && $menu_items_settings[$submenu_item['slug']] ) ? 'checked="checked"' : ''; ?> />
                                            <label><?php echo \esc_html( $submenu_item['title'] ); ?></label>
                                            <span class="wpca-menu-item-slug"><?php echo \esc_html( $submenu_item['slug'] ); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="wpca-menu-order">
                <div class="wpca-menu-order-header">
                    <h4><?php echo \esc_html( \__( 'Menu Order', \WPCA_TEXT_DOMAIN ) ); ?></h4>
                    <div class="wpca-menu-order-actions">
                        <button type="button" class="button button-small" id="wpca-reset-menu-order">
                            <?php echo \esc_html( \__( 'Reset to Default Order', \WPCA_TEXT_DOMAIN ) ); ?>
                        </button>
                    </div>
                </div>
                <p><?php echo \esc_html( \__( 'Drag and drop to reorder menu items:', \WPCA_TEXT_DOMAIN ) ); ?></p>
                <div class="wpca-menu-order-list" id="wpca-menu-order-list">
                    <?php foreach ( $menu_items as $menu_item ) : ?>
                        <div class="wpca-menu-order-item" data-menu-slug="<?php echo \esc_attr( $menu_item['slug'] ); ?>">
                            <span class="wpca-menu-order-handle">鈽?/span>
                            <span class="wpca-menu-order-title"><?php echo \esc_html( $menu_item['title'] ); ?></span>
                            <input type="hidden" name="wpca_settings[menu][menu_order][]" value="<?php echo \esc_attr( $menu_item['slug'] ); ?>" />
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <style type="text/css">
            .wpca-menu-customization {
                background: #f8f9fa;
                padding: 15px;
                border: 1px solid #e1e1e1;
                border-radius: 6px;
            }
            
            .wpca-menu-items {
                margin-bottom: 20px;
            }
            
            .wpca-menu-items-header,
            .wpca-menu-order-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }
            
            .wpca-menu-items-actions,
            .wpca-menu-order-actions {
                display: flex;
                gap: 5px;
            }
            
            .wpca-menu-tree {
                background: #fff;
                border: 1px solid #e1e1e1;
                border-radius: 4px;
                padding: 10px;
                max-height: 300px;
                overflow-y: auto;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }
            
            .wpca-menu-item {
                margin-bottom: 10px;
            }
            
            .wpca-menu-item-header {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 8px;
                background: #f5f5f5;
                border-radius: 4px;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .wpca-menu-item-header:hover {
                background: #e9ecef;
            }
            
            .wpca-menu-item-header.expanded {
                background: #dee2e6;
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
            }
            
            .wpca-menu-item-toggle {
                font-size: 10px;
                color: #666;
                transition: transform 0.3s ease;
            }
            
            .wpca-menu-item-header.expanded .wpca-menu-item-toggle {
                transform: rotate(180deg);
            }
            
            .wpca-menu-item-level-2 {
                margin-left: 30px;
                margin-top: 5px;
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 5px;
                background: #f9f9f9;
                border-radius: 4px;
                transition: all 0.2s ease;
            }
            
            .wpca-menu-item-level-2:hover {
                background: #f1f3f5;
            }
            
            .wpca-menu-item-slug {
                font-size: 12px;
                color: #666;
                margin-left: auto;
            }
            
            .wpca-submenu-items {
                margin-top: 0;
                background: #fff;
                border: 1px solid #e1e1e1;
                border-top: none;
                border-bottom-left-radius: 4px;
                border-bottom-right-radius: 4px;
                padding: 10px;
            }
            
            .wpca-menu-order {
                margin-top: 20px;
            }
            
            .wpca-menu-order-list {
                background: #fff;
                border: 1px solid #e1e1e1;
                border-radius: 4px;
                padding: 10px;
                max-height: 300px;
                overflow-y: auto;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }
            
            .wpca-menu-order-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 8px;
                background: #f5f5f5;
                border-radius: 4px;
                margin-bottom: 5px;
                cursor: move;
                transition: all 0.2s ease;
            }
            
            .wpca-menu-order-item:hover {
                background: #e9ecef;
            }
            
            .wpca-menu-order-handle {
                font-size: 16px;
                color: #666;
                cursor: move;
                user-select: none;
            }
            
            .wpca-menu-order-title {
                flex: 1;
            }
            
            /* Responsive design */
            @media screen and (max-width: 782px) {
                .wpca-menu-item-header,
                .wpca-menu-item-level-2,
                .wpca-menu-order-item {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 5px;
                }
                
                .wpca-menu-item-slug {
                    margin-left: 0;
                }
            }
        </style>
        
        <script type="text/javascript">
        (function($) {
            'use strict';
            
            $(document).ready(function() {
                // Make menu order list sortable
                $('#wpca-menu-order-list').sortable({
                    handle: '.wpca-menu-order-handle',
                    update: function(event, ui) {
                        // Update hidden input values
                        $(this).find('.wpca-menu-order-item').each(function(index) {
                            $(this).find('input[type="hidden"]').val($(this).data('menu-slug'));
                        });
                    }
                });
                
                // Toggle submenu items
                $('.wpca-menu-item-header').on('click', function(e) {
                    // Only toggle if clicked on header or toggle icon, not checkbox
                    if (!$(e.target).is('input[type="checkbox"]')) {
                        var submenu = $(this).next('.wpca-submenu-items');
                        if (submenu.length > 0) {
                            submenu.slideToggle();
                            $(this).toggleClass('expanded');
                        }
                    }
                });
                
                // Select all menu items
                $('#wpca-select-all-menu-items').on('click', function() {
                    $('.wpca-menu-tree input[type="checkbox"]').prop('checked', true);
                });
                
                // Deselect all menu items
                $('#wpca-deselect-all-menu-items').on('click', function() {
                    $('.wpca-menu-tree input[type="checkbox"]').prop('checked', false);
                });
                
                // Reset menu order to default
                $('#wpca-reset-menu-order').on('click', function() {
                    var menuOrderList = $('#wpca-menu-order-list');
                    var originalOrder = [];
                    
                    // Get original order from hidden inputs
                    menuOrderList.find('.wpca-menu-order-item').each(function() {
                        originalOrder.push($(this));
                    });
                    
                    // Append items in original order
                    menuOrderList.empty();
                    $.each(originalOrder, function(index, item) {
                        menuOrderList.append(item);
                    });
                    
                    // Update hidden input values
                    menuOrderList.find('.wpca-menu-order-item').each(function(index) {
                        $(this).find('input[type="hidden"]').val($(this).data('menu-slug'));
                    });
                });
                
                // Initially collapse all submenu items
                $('.wpca-submenu-items').hide();
            });
        })(jQuery);
        </script>
        <?php
    }
    
    public function render_optimize_database_field() {
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $optimize_database = isset( $settings['performance']['optimize_database'] ) ? $settings['performance']['optimize_database'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[performance][optimize_database]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $optimize_database, 1, false ) : ( $optimize_database ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_optimize_database"> ' . \__( 'Automatically optimize database tables.', \WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    public function render_clean_transients_field() {
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $clean_transients = isset( $settings['performance']['clean_transients'] ) ? $settings['performance']['clean_transients'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[performance][clean_transients]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $clean_transients, 1, false ) : ( $clean_transients ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_clean_transients"> ' . \__( 'Automatically clean expired transients.', \WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    public function render_hide_wp_version_field() {
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $hide_wp_version = isset( $settings['security']['hide_wp_version'] ) ? $settings['security']['hide_wp_version'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[security][hide_wp_version]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $hide_wp_version, 1, false ) : ( $hide_wp_version ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_hide_wp_version"> ' . \__( 'Hide WordPress version information.', \WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    public function render_disable_xmlrpc_field() {
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $disable_xmlrpc = isset( $settings['security']['disable_xmlrpc'] ) ? $settings['security']['disable_xmlrpc'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[security][disable_xmlrpc]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $disable_xmlrpc, 1, false ) : ( $disable_xmlrpc ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_disable_xmlrpc"> ' . \__( 'Disable XML-RPC functionality.', \WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    public function render_restrict_rest_api_field() {
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $restrict_rest_api = isset( $settings['security']['restrict_rest_api'] ) ? $settings['security']['restrict_rest_api'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[security][restrict_rest_api]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $restrict_rest_api, 1, false ) : ( $restrict_rest_api ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_restrict_rest_api"> ' . \__( 'Restrict REST API access to authenticated users only.', \WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    public function render_restrict_admin_access_field() {
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $restrict_admin_access = isset( $settings['security']['restrict_admin_access'] ) ? $settings['security']['restrict_admin_access'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[security][restrict_admin_access]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $restrict_admin_access, 1, false ) : ( $restrict_admin_access ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_restrict_admin_access"> ' . \__( 'Restrict admin area access to users with proper permissions.', \WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap wpca-settings-wrap">
            <h1><?php echo \esc_html( ( function_exists( 'get_admin_page_title' ) ? \get_admin_page_title() : 'WP Clean Admin' ) ); ?></h1>
            
            <form method="post" action="options.php" id="wpca-settings-form">
                <?php
                if ( function_exists( 'settings_fields' ) ) {
                    \settings_fields( 'wp-clean-admin' );
                }
                ?>
                
                <!-- Settings Tabs -->
                <div class="wpca-settings-tabs">
                    <div class="wpca-tabs-nav">
                        <button type="button" class="wpca-tab-button active" data-tab="general">
                            <span class="dashicons dashicons-admin-generic"></span>
                            <?php echo \esc_html( \__( 'General', \WPCA_TEXT_DOMAIN ) ); ?>
                        </button>
                        <button type="button" class="wpca-tab-button" data-tab="cleanup">
                            <span class="dashicons dashicons-clipboard"></span>
                            <?php echo \esc_html( \__( 'Cleanup', \WPCA_TEXT_DOMAIN ) ); ?>
                        </button>
                        <button type="button" class="wpca-tab-button" data-tab="performance">
                            <span class="dashicons dashicons-chart-line"></span>
                            <?php echo \esc_html( \__( 'Performance', \WPCA_TEXT_DOMAIN ) ); ?>
                        </button>
                        <button type="button" class="wpca-tab-button" data-tab="security">
                            <span class="dashicons dashicons-shield"></span>
                            <?php echo \esc_html( \__( 'Security', \WPCA_TEXT_DOMAIN ) ); ?>
                        </button>
                    </div>
                    
                    <div class="wpca-tabs-content">
                        <!-- General Tab -->
                        <div class="wpca-tab-content active" id="wpca-tab-general">
                            <?php
                            if ( function_exists( 'do_settings_sections' ) ) {
                                // Render only general settings
                                \do_settings_sections( 'wp-clean-admin' );
                            }
                            ?>
                        </div>
                        
                        <!-- Cleanup Tab -->
                        <div class="wpca-tab-content" id="wpca-tab-cleanup">
                            <?php
                            if ( function_exists( 'do_settings_sections' ) ) {
                                // Render only cleanup settings
                                \do_settings_sections( 'wp-clean-admin' );
                            }
                            ?>
                        </div>
                        
                        <!-- Performance Tab -->
                        <div class="wpca-tab-content" id="wpca-tab-performance">
                            <?php
                            if ( function_exists( 'do_settings_sections' ) ) {
                                // Render only performance settings
                                \do_settings_sections( 'wp-clean-admin' );
                            }
                            ?>
                        </div>
                        
                        <!-- Security Tab -->
                        <div class="wpca-tab-content" id="wpca-tab-security">
                            <?php
                            if ( function_exists( 'do_settings_sections' ) ) {
                                // Render only security settings
                                \do_settings_sections( 'wp-clean-admin' );
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="wpca-settings-submit">
                    <?php
                    if ( function_exists( 'submit_button' ) ) {
                        \submit_button( \__( 'Save Changes', \WPCA_TEXT_DOMAIN ), 'primary', 'submit', false, array( 'id' => 'wpca-save-button' ) );
                    }
                    ?>
                    <div class="wpca-save-message" id="wpca-save-message"></div>
                </div>
            </form>
        </div>
        
        <script type="text/javascript">
        (function($) {
            'use strict';
            
            $(document).ready(function() {
                // Settings tabs functionality
                const tabButtons = $('.wpca-tab-button');
                const tabContents = $('.wpca-tab-content');
                
                tabButtons.on('click', function() {
                    const tabId = $(this).data('tab');
                    
                    // Remove active class from all tabs
                    tabButtons.removeClass('active');
                    tabContents.removeClass('active');
                    
                    // Add active class to selected tab
                    $(this).addClass('active');
                    $(`#wpca-tab-${tabId}`).addClass('active');
                    
                    // Scroll to top of settings
                    $('html, body').animate({
                        scrollTop: $('.wpca-settings-form').offset().top - 20
                    }, 300);
                });
                
                // Form submission handling
                $('#wpca-settings-form').on('submit', function(e) {
                    // Show saving message
                    $('#wpca-save-message').html('<span class="wpca-saving">' + <?php echo \json_encode( \__( 'Saving...', \WPCA_TEXT_DOMAIN ) ); ?> + '</span>');
                });
                
                // Add toggle functionality to setting sections
                $('.wpca-settings-section h3').on('click', function() {
                    const section = $(this).closest('.wpca-settings-section');
                    const content = section.nextUntil('.wpca-settings-section');
                    
                    section.toggleClass('collapsed');
                    content.slideToggle();
                });
            });
        })(jQuery);
        </script>
        
        <style type="text/css">
            .wpca-settings-wrap {
                max-width: 1200px;
                margin: 0 auto;
            }
            
            .wpca-settings-tabs {
                margin: 20px 0;
                background: #fff;
                border: 1px solid #e1e1e1;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }
            
            .wpca-tabs-nav {
                display: flex;
                flex-wrap: wrap;
                background: #f8f9fa;
                border-bottom: 1px solid #e1e1e1;
                padding: 0;
                margin: 0;
            }
            
            .wpca-tab-button {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 12px 20px;
                background: transparent;
                border: none;
                border-bottom: 3px solid transparent;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 14px;
                font-weight: 500;
                color: #555;
            }
            
            .wpca-tab-button:hover {
                background: rgba(0, 124, 186, 0.05);
                color: #007cba;
            }
            
            .wpca-tab-button.active {
                background: #fff;
                color: #007cba;
                border-bottom-color: #007cba;
            }
            
            .wpca-tab-button .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
            }
            
            .wpca-tabs-content {
                padding: 20px;
            }
            
            .wpca-tab-content {
                display: none;
            }
            
            .wpca-tab-content.active {
                display: block;
                animation: wpca-fade-in 0.3s ease;
            }
            
            .wpca-settings-section {
                margin-bottom: 20px;
                padding: 20px;
                background: #f8f9fa;
                border: 1px solid #e1e1e1;
                border-radius: 6px;
                transition: all 0.3s ease;
            }
            
            .wpca-settings-section:hover {
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .wpca-settings-section h3 {
                margin: 0 0 15px;
                padding: 0;
                font-size: 16px;
                font-weight: 600;
                color: #333;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            
            .wpca-settings-section h3:after {
                content: '▼';
                font-size: 12px;
                color: #666;
                transition: transform 0.3s ease;
            }
            
            .wpca-settings-section.collapsed h3:after {
                transform: rotate(-90deg);
            }
            
            .wpca-settings-section .form-table {
                margin: 0;
                background: #fff;
                border: 1px solid #e1e1e1;
                border-radius: 4px;
                overflow: hidden;
            }
            
            .wpca-settings-section .form-table tr {
                border-bottom: 1px solid #f0f0f0;
                transition: background-color 0.2s ease;
            }
            
            .wpca-settings-section .form-table tr:last-child {
                border-bottom: none;
            }
            
            .wpca-settings-section .form-table tr:hover {
                background-color: #f8f9fa;
            }
            
            .wpca-settings-section .form-table th {
                padding: 12px 15px;
                width: 300px;
                font-weight: 500;
                color: #333;
                background: #fafafa;
                border-right: 1px solid #f0f0f0;
            }
            
            .wpca-settings-section .form-table td {
                padding: 12px 15px;
                color: #555;
            }
            
            .wpca-settings-submit {
                margin: 30px 0;
                padding: 20px;
                background: #f8f9fa;
                border: 1px solid #e1e1e1;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            
            #wpca-save-button {
                font-size: 14px;
                padding: 8px 20px;
                font-weight: 500;
            }
            
            .wpca-save-message {
                font-size: 14px;
            }
            
            .wpca-saving {
                color: #007cba;
                font-weight: 500;
            }
            
            /* Responsive Design */
            @media screen and (max-width: 782px) {
                .wpca-settings-wrap {
                    padding: 0 10px;
                }
                
                .wpca-tabs-nav {
                    flex-direction: column;
                }
                
                .wpca-tab-button {
                    justify-content: flex-start;
                    border-bottom: 1px solid #e1e1e1;
                }
                
                .wpca-tab-button.active {
                    border-bottom: 1px solid #e1e1e1;
                    border-left: 3px solid #007cba;
                }
                
                .wpca-settings-section .form-table {
                    display: block;
                }
                
                .wpca-settings-section .form-table tr {
                    display: block;
                    border-bottom: 1px solid #f0f0f0;
                }
                
                .wpca-settings-section .form-table th,
                .wpca-settings-section .form-table td {
                    display: block;
                    width: 100%;
                    border-right: none;
                    border-bottom: 1px solid #f0f0f0;
                }
                
                .wpca-settings-section .form-table th {
                    background: #fafafa;
                    padding-bottom: 8px;
                }
                
                .wpca-settings-section .form-table td {
                    padding-top: 8px;
                    padding-bottom: 12px;
                }
                
                .wpca-settings-submit {
                    flex-direction: column;
                    gap: 15px;
                    align-items: stretch;
                }
                
                #wpca-save-button {
                    width: 100%;
                    text-align: center;
                }
            }
            
            /* Animation */
            @keyframes wpca-fade-in {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
        <?php
    }
    
    public function validate_settings( $input ) {
        $validated = array();
        
        // Validate general settings
        if ( isset( $input['general'] ) ) {
            $validated['general'] = array();
            
            // Validate clean admin bar setting
            $validated['general']['clean_admin_bar'] = isset( $input['general']['clean_admin_bar'] ) ? 1 : 0;
            
            // Validate remove WordPress logo setting
            $validated['general']['remove_wp_logo'] = isset( $input['general']['remove_wp_logo'] ) ? 1 : 0;
        }
        
        // Validate menu settings
        if ( isset( $input['menu'] ) ) {
            $validated['menu'] = array();
            
            // Validate remove dashboard widgets setting
            $validated['menu']['remove_dashboard_widgets'] = isset( $input['menu']['remove_dashboard_widgets'] ) ? 1 : 0;
            
            // Validate simplify admin menu setting
            $validated['menu']['simplify_admin_menu'] = isset( $input['menu']['simplify_admin_menu'] ) ? 1 : 0;
            
            // Validate menu items setting
            if ( isset( $input['menu']['menu_items'] ) ) {
                $validated['menu']['menu_items'] = $input['menu']['menu_items'];
            }
            
            // Validate menu order setting
            if ( isset( $input['menu']['menu_order'] ) ) {
                $validated['menu']['menu_order'] = $input['menu']['menu_order'];
            }
        }
        
        // Validate performance settings
        if ( isset( $input['performance'] ) ) {
            $validated['performance'] = array();
            
            // Validate optimize database setting
            $validated['performance']['optimize_database'] = isset( $input['performance']['optimize_database'] ) ? 1 : 0;
            
            // Validate clean transients setting
            $validated['performance']['clean_transients'] = isset( $input['performance']['clean_transients'] ) ? 1 : 0;
        }
        
        // Validate security settings
        if ( isset( $input['security'] ) ) {
            $validated['security'] = array();
            
            // Validate hide WordPress version setting
            $validated['security']['hide_wp_version'] = isset( $input['security']['hide_wp_version'] ) ? 1 : 0;
            
            // Validate disable XML-RPC setting
            $validated['security']['disable_xmlrpc'] = isset( $input['security']['disable_xmlrpc'] ) ? 1 : 0;
            
            // Validate restrict REST API setting
            $validated['security']['restrict_rest_api'] = isset( $input['security']['restrict_rest_api'] ) ? 1 : 0;
            
            // Validate restrict admin access setting
            $validated['security']['restrict_admin_access'] = isset( $input['security']['restrict_admin_access'] ) ? 1 : 0;
        }
        
        return $validated;
    }
    
    public function enqueue_scripts( $hook ) {
        if ( \strpos( $hook, 'wp-clean-admin' ) === false ) {
            return;
        }
        
        if ( function_exists( 'wp_enqueue_style' ) ) {
            \wp_enqueue_style(
                'wpca-admin',
                \WPCA_PLUGIN_URL . 'assets/css/wpca-admin.css',
                array(),
                \WPCA_VERSION
            );
        }
        
        if ( function_exists( 'wp_enqueue_script' ) ) {
            \wp_enqueue_script(
                'wpca-main',
                \WPCA_PLUGIN_URL . 'assets/js/wpca-main.js',
                array( 'jquery' ),
                \WPCA_VERSION,
                true
            );
        }
    }
}

