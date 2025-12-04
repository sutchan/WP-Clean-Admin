/**
 * WP Clean Admin Permissions JavaScript
 *
 * @package WPCA
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
// Use IIFE to encapsulate the permissions functionality
(function($) {
    'use strict';
    
    /**
     * Permissions functionality for WPCA
     */
    const WPCAPermissions = {
        /**
         * Initialize permissions functionality
         */
        init: function() {
            this.bindEvents();
            this.initPermissionCheckboxes();
        },
        
        /**
         * Bind events for permissions
         */
        bindEvents: function() {
            // Handle permission checkbox changes
            $(document).on('change', '.wpca-permission-checkbox', this.handlePermissionChange.bind(this));
            
            // Handle role selection changes
            $(document).on('change', '.wpca-role-select', this.handleRoleChange.bind(this));
        },
        
        /**
         * Initialize permission checkboxes
         */
        initPermissionCheckboxes: function() {
            // Add visual feedback for permission states
            $('.wpca-permission-checkbox').each(function() {
                const $checkbox = $(this);
                WPCAPermissions.updatePermissionVisuals($checkbox);
            });
        },
        
        /**
         * Handle permission checkbox changes
         * @param {Event} e - The change event
         */
        handlePermissionChange: function(e) {
            const $checkbox = $(e.currentTarget);
            this.updatePermissionVisuals($checkbox);
        },
        
        /**
         * Handle role selection changes
         * @param {Event} e - The change event
         */
        handleRoleChange: function(e) {
            const $select = $(e.currentTarget);
            const roleId = $select.val();
            const $permissionContainer = $select.closest('.wpca-permissions-container');
            
            // Trigger custom event for role change
            $(document).trigger('wpca_role_changed', { 
                roleId: roleId, 
                permissionContainer: $permissionContainer 
            });
        },
        
        /**
         * Update visual feedback for permission checkboxes
         * @param {jQuery} $checkbox - The checkbox element
         */
        updatePermissionVisuals: function($checkbox) {
            const $container = $checkbox.closest('.wpca-permission-item');
            
            if ($checkbox.is(':checked')) {
                $container.addClass('wpca-permission-enabled');
                $container.removeClass('wpca-permission-disabled');
            } else {
                $container.addClass('wpca-permission-disabled');
                $container.removeClass('wpca-permission-enabled');
            }
        }
    };
    
    // Initialize permissions functionality when DOM is ready
    $(document).ready(function() {
        WPCAPermissions.init();
    });
    
    // Expose to global scope for potential external use
    window.WPCAPermissions = WPCAPermissions;
})(jQuery);
