/**
 * WP Clean Admin Reset JavaScript
 *
 * @package WPCA
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
// Use IIFE to encapsulate the reset functionality
(function($) {
    'use strict';
    
    /**
     * Reset functionality for WPCA
     */
    const WPCAReset = {
        /**
         * Initialize reset functionality
         */
        init: function() {
            this.bindEvents();
        },
        
        /**
         * Bind events for reset functionality
         */
        bindEvents: function() {
            // Handle reset confirmation dialogs
            $(document).on('click', '.wpca-reset-all-btn', this.handleResetAll.bind(this));
            $(document).on('click', '.wpca-reset-module-btn', this.handleResetModule.bind(this));
        },
        
        /**
         * Handle full reset confirmation
         * @param {Event} e - The click event
         */
        handleResetAll: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const confirmText = $btn.data('confirm') || 'Are you sure you want to reset all WPCA settings to their default values? This action cannot be undone.';
            
            if (confirm(confirmText)) {
                this.performReset($btn);
            }
        },
        
        /**
         * Handle module reset confirmation
         * @param {Event} e - The click event
         */
        handleResetModule: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const moduleName = $btn.data('module') || 'this module';
            const confirmText = $btn.data('confirm') || `Are you sure you want to reset ${moduleName} settings to their default values?`;
            
            if (confirm(confirmText)) {
                this.performReset($btn);
            }
        },
        
        /**
         * Perform the reset action
         * @param {jQuery} $btn - The reset button
         */
        performReset: function($btn) {
            const originalText = $btn.text();
            const resettingText = $btn.data('resetting-text') || 'Resetting...';
            
            // Add loading state
            $btn.prop('disabled', true).text(resettingText);
            
            // If this is a form button, submit the form
            const $form = $btn.closest('form');
            if ($form.length) {
                $form.submit();
            }
        }
    };
    
    // Initialize reset functionality when DOM is ready
    $(document).ready(function() {
        WPCAReset.init();
    });
    
    // Expose to global scope for potential external use
    window.WPCAReset = WPCAReset;
})(jQuery);
