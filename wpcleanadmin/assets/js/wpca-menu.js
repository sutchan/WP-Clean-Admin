/**
 * WP Clean Admin Menu JavaScript
 *
 * @package WPCA
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
// Use IIFE to encapsulate the menu functionality
(function($) {
    'use strict';
    
    /**
     * Menu functionality for WPCA
     */
    const WPCAMenu = {
        /**
         * Initialize menu functionality
         */
        init: function() {
            this.bindEvents();
            this.initMenuItems();
        },
        
        /**
         * Bind events for menu
         */
        bindEvents: function() {
            // Handle menu item toggles
            $(document).on('click', '.wpca-menu-toggle', this.handleMenuToggle.bind(this));
            
            // Handle menu item reordering
            $(document).on('click', '.wpca-menu-reorder-btn', this.handleMenuReorder.bind(this));
            
            // Handle menu item deletion
            $(document).on('click', '.wpca-menu-delete-btn', this.handleMenuDelete.bind(this));
        },
        
        /**
         * Initialize menu items
         */
        initMenuItems: function() {
            // Add visual indicators for menu item states
            $('.wpca-menu-item').each(function() {
                const $item = $(this);
                WPCAMenu.updateMenuItemVisuals($item);
            });
        },
        
        /**
         * Handle menu item toggles
         * @param {Event} e - The click event
         */
        handleMenuToggle: function(e) {
            e.preventDefault();
            
            const $toggle = $(e.currentTarget);
            const $menuItem = $toggle.closest('.wpca-menu-item');
            const isActive = $menuItem.hasClass('active');
            
            if (isActive) {
                $menuItem.removeClass('active');
            } else {
                $menuItem.addClass('active');
            }
            
            // Update visual feedback
            this.updateMenuItemVisuals($menuItem);
        },
        
        /**
         * Handle menu item reordering
         * @param {Event} e - The click event
         */
        handleMenuReorder: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const direction = $btn.data('direction') || 'up';
            const $menuItem = $btn.closest('.wpca-menu-item');
            const $menuList = $menuItem.closest('.wpca-menu-list');
            
            // Trigger custom event for menu reordering
            $(document).trigger('wpca_menu_reorder', { 
                menuItem: $menuItem, 
                direction: direction, 
                menuList: $menuList 
            });
        },
        
        /**
         * Handle menu item deletion
         * @param {Event} e - The click event
         */
        handleMenuDelete: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const $menuItem = $btn.closest('.wpca-menu-item');
            const menuItemName = $menuItem.data('menu-item-name') || 'this menu item';
            const confirmText = $btn.data('confirm') || `Are you sure you want to delete ${menuItemName}?`;
            
            if (confirm(confirmText)) {
                // Trigger custom event for menu item deletion
                $(document).trigger('wpca_menu_delete', { menuItem: $menuItem });
            }
        },
        
        /**
         * Update visual feedback for menu items
         * @param {jQuery} $menuItem - The menu item element
         */
        updateMenuItemVisuals: function($menuItem) {
            const isActive = $menuItem.hasClass('active');
            const $toggle = $menuItem.find('.wpca-menu-toggle');
            
            if (isActive) {
                $toggle.text('Disable');
                $menuItem.addClass('wpca-menu-item-active');
            } else {
                $toggle.text('Enable');
                $menuItem.removeClass('wpca-menu-item-active');
            }
        }
    };
    
    // Initialize menu functionality when DOM is ready
    $(document).ready(function() {
        WPCAMenu.init();
    });
    
    // Expose to global scope for potential external use
    window.WPCAMenu = WPCAMenu;
})(jQuery);
