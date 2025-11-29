/**
 * Tab functionality for WP Clean Admin
 * 
 * @file wpcleanadmin/assets/js/wpca-tabs.js
 * @version 1.7.15
 * @updated 2025-11-29
 */

jQuery(document).ready(function($) {
    'use strict';
    
    /**
     * Tab management class
     */
    var WPCATabs = {
        /**
         * Initialize tab functionality
         */
        init: function() {
            this.bindEvents();
            this.activateDefaultTab();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Tab click event
            $('.wpca-tab-nav a').on('click', this.handleTabClick.bind(this));
            
            // Hash change event for deep linking
            $(window).on('hashchange', this.handleHashChange.bind(this));
        },
        
        /**
         * Activate the default tab
         */
        activateDefaultTab: function() {
            // Check if there's a hash in the URL
            if (window.location.hash) {
                this.handleHashChange();
            } else {
                // Activate the first tab by default
                var firstTab = $('.wpca-tab-nav li:first-child a');
                this.activateTab(firstTab);
            }
        },
        
        /**
         * Handle tab click event
         * 
         * @param {Event} e Click event
         */
        handleTabClick: function(e) {
            e.preventDefault();
            var tabLink = $(e.currentTarget);
            this.activateTab(tabLink);
        },
        
        /**
         * Handle hash change event
         */
        handleHashChange: function() {
            var hash = window.location.hash;
            if (hash) {
                var tabLink = $('.wpca-tab-nav a[href="' + hash + '"]');
                if (tabLink.length) {
                    this.activateTab(tabLink);
                }
            }
        },
        
        /**
         * Activate a specific tab
         * 
         * @param {jQuery} tabLink Tab link element
         */
        activateTab: function(tabLink) {
            var tabId = tabLink.attr('href');
            
            // Remove active class from all tabs
            $('.wpca-tab-nav li').removeClass('active');
            $('.wpca-tab-content').removeClass('active');
            
            // Add active class to the clicked tab and its content
            tabLink.parent('li').addClass('active');
            $(tabId).addClass('active');
            
            // Update URL hash
            window.location.hash = tabId;
            
            // Trigger custom event for tab activation
            $(document).trigger('wpca-tab-activated', [tabId]);
        }
    };
    
    // Initialize tabs when DOM is ready
    WPCATabs.init();
});
