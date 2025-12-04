/**
 * WP Clean Admin Settings JavaScript
 *
 * @package WPCA
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
// Use IIFE to encapsulate the settings functionality
(function($) {
    'use strict';
    
    /**
     * Settings functionality for WPCA
     */
    const WPCASettings = {
        /**
         * Initialize settings functionality
         */
        init: function() {
            this.bindEvents();
            this.initSettingsForms();
        },
        
        /**
         * Bind events for settings
         */
        bindEvents: function() {
            // Handle settings form submission
            $(document).on('submit', '.wpca-settings-form', this.handleFormSubmission.bind(this));
            
            // Handle reset buttons
            $(document).on('click', '.wpca-reset-btn', this.handleReset.bind(this));
        },
        
        /**
         * Initialize settings forms
         */
        initSettingsForms: function() {
            // Add loading state handlers
            $('.wpca-settings-form').each(function() {
                const $form = $(this);
                const $submitBtn = $form.find('input[type="submit"]');
                
                if ($submitBtn.length) {
                    $form.data('original-submit-text', $submitBtn.val());
                }
            });
        },
        
        /**
         * Handle form submission
         * @param {Event} e - The submit event
         */
        handleFormSubmission: function(e) {
            const $form = $(e.currentTarget);
            const $submitBtn = $form.find('input[type="submit"]');
            
            if ($submitBtn.length) {
                $submitBtn.prop('disabled', true).val($form.data('saving-text') || 'Saving...');
            }
        },
        
        /**
         * Handle reset button click
         * @param {Event} e - The click event
         */
        handleReset: function(e) {
            const $btn = $(e.currentTarget);
            const confirmText = $btn.data('confirm') || 'Are you sure you want to reset these settings?';
            
            if (!confirm(confirmText)) {
                e.preventDefault();
                return false;
            }
            
            // Add loading state
            $btn.prop('disabled', true).val($btn.data('resetting-text') || 'Resetting...');
            
            return true;
        }
    };
    
    // Initialize settings when DOM is ready
    $(document).ready(function() {
        WPCASettings.init();
    });
    
    // Expose to global scope for potential external use
    window.WPCASettings = WPCASettings;
})(jQuery);
