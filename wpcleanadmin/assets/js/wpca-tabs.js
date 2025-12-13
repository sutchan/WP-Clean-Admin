/**
 * WP Clean Admin Tabs JavaScript
 *
 * @package WPCA
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
// Use IIFE to encapsulate the tabs functionality
(function($) {
    'use strict';
    
    /**
     * Tabs functionality for WPCA
     */
    const WPCATabs = {
        /**
         * Initialize tabs functionality
         */
        init: function() {
            this.bindEvents();
        },
        
        /**
         * Bind events for tabs
         */
        bindEvents: function() {
            // Handle tab clicks
            $(document).on('click', '.wpca-tabs-nav li a', function(e) {
                e.preventDefault();
                const $this = $(this);
                const tabId = $this.attr('href');
                const $tabContainer = $this.closest('.wpca-tabs');
                
                // Remove active classes
                $tabContainer.find('.wpca-tabs-nav li').removeClass('active');
                $tabContainer.find('.wpca-tab-content').removeClass('active');
                
                // Add active classes
                $this.parent().addClass('active');
                $tabContainer.find(tabId).addClass('active');
                
                // Trigger custom event
                $(document).trigger('wpca_tab_changed', { tabId: tabId, tabContainer: $tabContainer });
            });
            
            // Handle tab navigation via keyboard
            $(document).on('keydown', '.wpca-tabs-nav li a', function(e) {
                const $this = $(this);
                const $parent = $this.parent();
                const $siblings = $parent.siblings();
                
                // Left arrow key
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    const $prev = $parent.prev().length ? $parent.prev() : $siblings.last();
                    $prev.find('a').trigger('click');
                }
                // Right arrow key
                else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    const $next = $parent.next().length ? $parent.next() : $siblings.first();
                    $next.find('a').trigger('click');
                }
            });
        }
    };
    
    // Initialize tabs when DOM is ready
    $(document).ready(function() {
        WPCATabs.init();
    });
    
    // Expose to global scope for potential external use
    window.WPCATabs = WPCATabs;
})(jQuery);
