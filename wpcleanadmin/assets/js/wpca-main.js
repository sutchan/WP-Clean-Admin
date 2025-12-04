/**
 * WP Clean Admin Main JavaScript
 *
 * @package WPCA
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
// Use IIFE to encapsulate the plugin
(function($) {
    'use strict';
    
    // Global WPCA object
    window.WPCleanAdmin = {
        /**
         * Initialize the plugin
         */
        init: function() {
            this.initTabs();
            this.initSettings();
            this.initAjax();
        },
        
        /**
         * Initialize tabs functionality
         */
        initTabs: function() {
            // Tab switching functionality
            $(document).on('click', '.wpca-tabs-nav li a', function(e) {
                e.preventDefault();
                
                const tabId = $(this).attr('href');
                const tabContainer = $(this).closest('.wpca-tabs');
                
                // Remove active classes
                tabContainer.find('.wpca-tabs-nav li').removeClass('active');
                tabContainer.find('.wpca-tab-content').removeClass('active');
                
                // Add active classes
                $(this).parent().addClass('active');
                tabContainer.find(tabId).addClass('active');
            });
        },
        
        /**
         * Initialize settings functionality
         */
        initSettings: function() {
            // Settings form submission
            $(document).on('submit', '.wpca-settings-form', function(e) {
                // Add loading state
                $(this).find('input[type="submit"]').prop('disabled', true).val(wpca_settings_vars.saving || 'Saving...');
            });
        },
        
        /**
         * Initialize AJAX functionality
         */
        initAjax: function() {
            // AJAX error handling
            $(document).ajaxError(function(event, xhr, settings, error) {
                console.error('WPCA AJAX Error:', error);
                alert(wpca_settings_vars.ajax_error || 'An AJAX error occurred. Please try again.');
            });
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        window.WPCleanAdmin.init();
    });
})(jQuery);
