<?php
/**
 * WPCleanAdmin Menu Customization Settings
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @update_date 2026-01-30
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */

namespace WPCleanAdmin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Menu Customization Settings Class
 */
class Menu_Customization {
    
    /**
     * Render menu customization field
     *
     * @return void
     */
    public static function render_menu_customization_field() {
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
        
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        
        ?>
        <div class="wpca-menu-customization">
            <p><?php echo \esc_html( \__( 'Customize admin menu items and their order.', $text_domain ) ); ?></p>
            
            <div class="wpca-menu-items">
                <div class="wpca-menu-items-header">
                    <h4><?php echo \esc_html( \__( 'Menu Items', $text_domain ) ); ?></h4>
                    <div class="wpca-menu-items-actions">
                        <button type="button" class="button button-small" id="wpca-select-all-menu-items">
                            <?php echo \esc_html( \__( 'Select All', $text_domain ) ); ?>
                        </button>
                        <button type="button" class="button button-small" id="wpca-deselect-all-menu-items">
                            <?php echo \esc_html( \__( 'Deselect All', $text_domain ) ); ?>
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
                                <span class="wpca-menu-item-toggle">▼</span>
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
                    <h4><?php echo \esc_html( \__( 'Menu Order', $text_domain ) ); ?></h4>
                    <div class="wpca-menu-order-actions">
                        <button type="button" class="button button-small" id="wpca-reset-menu-order">
                            <?php echo \esc_html( \__( 'Reset to Default Order', $text_domain ) ); ?>
                        </button>
                    </div>
                </div>
                <p><?php echo \esc_html( \__( 'Drag and drop to reorder menu items:', $text_domain ) ); ?></p>
                <div class="wpca-menu-order-list" id="wpca-menu-order-list">
                    <?php foreach ( $menu_items as $menu_item ) : ?>
                        <div class="wpca-menu-order-item" data-menu-slug="<?php echo \esc_attr( $menu_item['slug'] ); ?>">
                            <span class="wpca-menu-order-handle">☰</span>
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
}
