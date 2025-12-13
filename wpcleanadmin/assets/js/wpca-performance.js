/**
 * WP Clean Admin Performance JavaScript
 *
 * @package WPCA
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
// Use IIFE to encapsulate the performance functionality
(function($) {
    'use strict';
    
    /**
     * Performance functionality for WPCA
     */
    const WPCAPerformance = {
        /**
         * Initialize performance functionality
         */
        init: function() {
            this.bindEvents();
            this.initPerformanceMetrics();
        },
        
        /**
         * Bind events for performance
         */
        bindEvents: function() {
            // Handle performance optimization buttons
            $(document).on('click', '.wpca-optimize-btn', this.handleOptimize.bind(this));
            
            // Handle performance metric updates
            $(document).on('click', '.wpca-refresh-metrics-btn', this.handleRefreshMetrics.bind(this));
        },
        
        /**
         * Initialize performance metrics display
         */
        initPerformanceMetrics: function() {
            // Add loading indicators for metrics
            $('.wpca-metrics-container').each(function() {
                const $container = $(this);
                $container.append('<div class="wpca-metrics-loading" style="display: none;">Loading metrics...</div>');
            });
        },
        
        /**
         * Handle optimization button clicks
         * @param {Event} e - The click event
         */
        handleOptimize: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const optimizationType = $btn.data('optimize-type') || 'general';
            const confirmText = $btn.data('confirm') || `Are you sure you want to perform ${optimizationType} optimization?`;
            
            if (confirm(confirmText)) {
                this.performOptimization($btn, optimizationType);
            }
        },
        
        /**
         * Handle metrics refresh button clicks
         * @param {Event} e - The click event
         */
        handleRefreshMetrics: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            this.refreshMetrics($btn);
        },
        
        /**
         * Perform optimization
         * @param {jQuery} $btn - The optimization button
         * @param {string} type - The optimization type
         */
        performOptimization: function($btn, type) {
            const originalText = $btn.text();
            const optimizingText = $btn.data('optimizing-text') || 'Optimizing...';
            
            // Add loading state
            $btn.prop('disabled', true).text(optimizingText);
            
            // Trigger custom event for optimization
            $(document).trigger('wpca_optimization_started', { 
                type: type, 
                button: $btn 
            });
        },
        
        /**
         * Refresh performance metrics
         * @param {jQuery} $btn - The refresh button
         */
        refreshMetrics: function($btn) {
            const originalText = $btn.text();
            const refreshingText = $btn.data('refreshing-text') || 'Refreshing...';
            const $metricsContainer = $btn.closest('.wpca-metrics-container');
            const $loadingIndicator = $metricsContainer.find('.wpca-metrics-loading');
            
            // Add loading states
            $btn.prop('disabled', true).text(refreshingText);
            $loadingIndicator.show();
            
            // Trigger custom event for metrics refresh
            $(document).trigger('wpca_metrics_refreshing', { 
                container: $metricsContainer, 
                button: $btn 
            });
        }
    };
    
    // Initialize performance functionality when DOM is ready
    $(document).ready(function() {
        WPCAPerformance.init();
    });
    
    // Expose to global scope for potential external use
    window.WPCAPerformance = WPCAPerformance;
})(jQuery);
