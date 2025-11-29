/**
 * Performance optimization functionality for WP Clean Admin
 * 
 * @file wpcleanadmin/assets/js/wpca-performance.js
 * @version 1.7.15
 * @updated 2025-11-29
 */

jQuery(document).ready(function($) {
    'use strict';
    
    /**
     * Performance management class
     */
    var WPCAPerformance = {
        /**
         * Initialize performance functionality
         */
        init: function() {
            this.bindEvents();
            this.loadPerformanceMetrics();
            this.initCacheManagement();
            this.initResourceOptimization();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Clear cache button
            $('#wpca-clear-cache').on('click', this.clearCache.bind(this));
            
            // Optimize images button
            $('#wpca-optimize-images').on('click', this.optimizeImages.bind(this));
            
            // Minify CSS/JS button
            $('#wpca-minify-assets').on('click', this.minifyAssets.bind(this));
            
            // Refresh performance metrics button
            $('#wpca-refresh-metrics').on('click', this.loadPerformanceMetrics.bind(this));
            
            // Enable/disable performance features
            $('.wpca-performance-feature-toggle').on('change', this.togglePerformanceFeature.bind(this));
        },
        
        /**
         * Load performance metrics
         */
        loadPerformanceMetrics: function() {
            var button = $('#wpca-refresh-metrics');
            var metricsContainer = $('#wpca-performance-metrics-container');
            
            // Show loading state
            if (button.length) {
                button.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.loading_metrics);
            } else {
                metricsContainer.html('<p>' + wpca_admin.loading + '</p>');
            }
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_get_performance_metrics',
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    WPCAPerformance.renderPerformanceMetrics(response, metricsContainer, button);
                },
                error: function() {
                    metricsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                    if (button.length) {
                        button.prop('disabled', false).html(wpca_admin.refresh_metrics);
                    }
                }
            });
        },
        
        /**
         * Render performance metrics
         * 
         * @param {Object} response AJAX response
         * @param {jQuery} metricsContainer Metrics container element
         * @param {jQuery} button Refresh button element
         */
        renderPerformanceMetrics: function(response, metricsContainer, button) {
            if (button.length) {
                button.prop('disabled', false).html(wpca_admin.refresh_metrics);
            }
            
            if (response.success) {
                var data = response.data;
                var html = '<div class="wpca-performance-metrics-grid">';
                
                // Core web vitals
                html += '<div class="wpca-info-card">';
                html += '<h3>' + data.lcp + 's</h3>';
                html += '<p>' + wpca_admin.largest_contentful_paint + '</p>';
                html += '<span class="wpca-metric-status ' + this.getMetricStatus(data.lcp, 2.5) + '">' + this.getMetricStatusText(data.lcp, 2.5) + '</span>';
                html += '</div>';
                
                html += '<div class="wpca-info-card">';
                html += '<h3>' + data.fid + 'ms</h3>';
                html += '<p>' + wpca_admin.first_input_delay + '</p>';
                html += '<span class="wpca-metric-status ' + this.getMetricStatus(data.fid, 100) + '">' + this.getMetricStatusText(data.fid, 100) + '</span>';
                html += '</div>';
                
                html += '<div class="wpca-info-card">';
                html += '<h3>' + data.cls + '</h3>';
                html += '<p>' + wpca_admin.cumulative_layout_shift + '</p>';
                html += '<span class="wpca-metric-status ' + this.getMetricStatus(data.cls, 0.1) + '">' + this.getMetricStatusText(data.cls, 0.1) + '</span>';
                html += '</div>';
                
                // Additional metrics
                html += '<div class="wpca-info-card">';
                html += '<h3>' + data.page_load_time + 's</h3>';
                html += '<p>' + wpca_admin.page_load_time + '</p>';
                html += '</div>';
                
                html += '<div class="wpca-info-card">';
                html += '<h3>' + data.total_requests + '</h3>';
                html += '<p>' + wpca_admin.total_requests + '</p>';
                html += '</div>';
                
                html += '<div class="wpca-info-card">';
                html += '<h3>' + this.formatSize(data.total_size) + '</h3>';
                html += '<p>' + wpca_admin.total_page_size + '</p>';
                html += '</div>';
                
                html += '</div>';
                
                // Cache statistics
                if (data.cache_stats) {
                    html += '<h3>' + wpca_admin.cache_statistics + '</h3>';
                    html += '<div class="wpca-cache-stats-grid">';
                    
                    html += '<div class="wpca-info-card">';
                    html += '<h3>' + data.cache_stats.cache_size + '</h3>';
                    html += '<p>' + wpca_admin.cache_size + '</p>';
                    html += '</div>';
                    
                    html += '<div class="wpca-info-card">';
                    html += '<h3>' + data.cache_stats.cache_hits + '</h3>';
                    html += '<p>' + wpca_admin.cache_hits + '</p>';
                    html += '</div>';
                    
                    html += '<div class="wpca-info-card">';
                    html += '<h3>' + data.cache_stats.cache_misses + '</h3>';
                    html += '<p>' + wpca_admin.cache_misses + '</p>';
                    html += '</div>';
                    
                    html += '<div class="wpca-info-card">';
                    html += '<h3>' + data.cache_stats.cache_hit_ratio + '%</h3>';
                    html += '<p>' + wpca_admin.cache_hit_ratio + '</p>';
                    html += '</div>';
                    
                    html += '</div>';
                }
                
                metricsContainer.html(html);
            } else {
                metricsContainer.html('<div class="error notice"><p>' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p></div>');
            }
        },
        
        /**
         * Initialize cache management
         */
        initCacheManagement: function() {
            // Cache clearing options
            $('.wpca-cache-clear-option').on('change', function() {
                var selectedOptions = $('.wpca-cache-clear-option:checked');
                var clearButton = $('#wpca-clear-cache');
                
                if (selectedOptions.length > 0) {
                    clearButton.prop('disabled', false);
                } else {
                    clearButton.prop('disabled', true);
                }
            });
        },
        
        /**
         * Initialize resource optimization
         */
        initResourceOptimization: function() {
            // Resource optimization options
            $('.wpca-resource-optimization-option').on('change', function() {
                var selectedOptions = $('.wpca-resource-optimization-option:checked');
                var optimizeButton = $('#wpca-optimize-resources');
                
                if (selectedOptions.length > 0) {
                    optimizeButton.prop('disabled', false);
                } else {
                    optimizeButton.prop('disabled', true);
                }
            });
        },
        
        /**
         * Clear cache
         */
        clearCache: function() {
            var button = $('#wpca-clear-cache');
            var resultsContainer = $('#wpca-cache-results');
            
            // Get selected cache types
            var selectedCacheTypes = [];
            $('.wpca-cache-clear-option:checked').each(function() {
                selectedCacheTypes.push($(this).val());
            });
            
            if (selectedCacheTypes.length === 0) {
                resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_select_cache_type + '</p></div>');
                return;
            }
            
            // Show loading state
            button.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.clearing_cache);
            resultsContainer.html('');
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_clear_cache',
                    nonce: wpca_admin.nonce,
                    cache_types: JSON.stringify(selectedCacheTypes)
                },
                success: function(response) {
                    WPCAPerformance.handleCacheClearResponse(response, button, resultsContainer);
                },
                error: function() {
                    resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                    button.prop('disabled', false).html(wpca_admin.clear_cache);
                }
            });
        },
        
        /**
         * Handle cache clear response
         * 
         * @param {Object} response AJAX response
         * @param {jQuery} button Clear cache button element
         * @param {jQuery} resultsContainer Results container element
         */
        handleCacheClearResponse: function(response, button, resultsContainer) {
            button.prop('disabled', false).html(wpca_admin.clear_cache);
            
            if (response.success) {
                resultsContainer.html('<div class="success notice"><p>' + response.data.message + '</p></div>');
                
                // Reload performance metrics
                WPCAPerformance.loadPerformanceMetrics();
                
                // Auto-dismiss success message after 3 seconds
                setTimeout(function() {
                    resultsContainer.fadeOut('slow', function() {
                        $(this).html('').show();
                    });
                }, 3000);
            } else {
                resultsContainer.html('<div class="error notice"><p>' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p></div>');
            }
        },
        
        /**
         * Optimize images
         */
        optimizeImages: function() {
            var button = $('#wpca-optimize-images');
            var resultsContainer = $('#wpca-image-optimization-results');
            
            // Show loading state
            button.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.optimizing_images);
            resultsContainer.html('');
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_optimize_images',
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    WPCAPerformance.handleImageOptimizationResponse(response, button, resultsContainer);
                },
                error: function() {
                    resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                    button.prop('disabled', false).html(wpca_admin.optimize_images);
                }
            });
        },
        
        /**
         * Handle image optimization response
         * 
         * @param {Object} response AJAX response
         * @param {jQuery} button Optimize images button element
         * @param {jQuery} resultsContainer Results container element
         */
        handleImageOptimizationResponse: function(response, button, resultsContainer) {
            button.prop('disabled', false).html(wpca_admin.optimize_images);
            
            if (response.success) {
                resultsContainer.html('<div class="success notice"><p>' + response.data.message + '</p></div>');
                
                // Show optimization details if available
                if (response.data.details) {
                    var details = response.data.details;
                    var html = '<div class="wpca-optimization-details">';
                    html += '<h4>' + wpca_admin.optimization_details + '</h4>';
                    html += '<p>' + wpca_admin.images_optimized + ': ' + details.optimized_images + '</p>';
                    html += '<p>' + wpca_admin.space_saved + ': ' + details.space_saved + '</p>';
                    html += '<p>' + wpca_admin.average_optimization + ': ' + details.average_optimization + '%</p>';
                    html += '</div>';
                    resultsContainer.append(html);
                }
                
                // Reload performance metrics
                WPCAPerformance.loadPerformanceMetrics();
            } else {
                resultsContainer.html('<div class="error notice"><p>' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p></div>');
            }
        },
        
        /**
         * Minify CSS/JS assets
         */
        minifyAssets: function() {
            var button = $('#wpca-minify-assets');
            var resultsContainer = $('#wpca-asset-minification-results');
            
            // Show loading state
            button.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.minifying_assets);
            resultsContainer.html('');
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_minify_assets',
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    WPCAPerformance.handleAssetMinificationResponse(response, button, resultsContainer);
                },
                error: function() {
                    resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                    button.prop('disabled', false).html(wpca_admin.minify_assets);
                }
            });
        },
        
        /**
         * Handle asset minification response
         * 
         * @param {Object} response AJAX response
         * @param {jQuery} button Minify assets button element
         * @param {jQuery} resultsContainer Results container element
         */
        handleAssetMinificationResponse: function(response, button, resultsContainer) {
            button.prop('disabled', false).html(wpca_admin.minify_assets);
            
            if (response.success) {
                resultsContainer.html('<div class="success notice"><p>' + response.data.message + '</p></div>');
                
                // Show minification details if available
                if (response.data.details) {
                    var details = response.data.details;
                    var html = '<div class="wpca-minification-details">';
                    html += '<h4>' + wpca_admin.minification_details + '</h4>';
                    html += '<p>' + wpca_admin.css_files_minified + ': ' + details.css_files + '</p>';
                    html += '<p>' + wpca_admin.js_files_minified + ': ' + details.js_files + '</p>';
                    html += '<p>' + wpca_admin.css_space_saved + ': ' + details.css_space_saved + '</p>';
                    html += '<p>' + wpca_admin.js_space_saved + ': ' + details.js_space_saved + '</p>';
                    html += '<p>' + wpca_admin.total_space_saved + ': ' + details.total_space_saved + '</p>';
                    html += '</div>';
                    resultsContainer.append(html);
                }
                
                // Reload performance metrics
                WPCAPerformance.loadPerformanceMetrics();
            } else {
                resultsContainer.html('<div class="error notice"><p>' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p></div>');
            }
        },
        
        /**
         * Toggle performance feature
         * 
         * @param {Event} e Change event
         */
        togglePerformanceFeature: function(e) {
            var toggle = $(e.currentTarget);
            var feature = toggle.attr('data-feature');
            var enabled = toggle.is(':checked');
            var resultsContainer = $('#wpca-performance-results');
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_toggle_performance_feature',
                    nonce: wpca_admin.nonce,
                    feature: feature,
                    enabled: enabled
                },
                success: function(response) {
                    if (response.success) {
                        resultsContainer.html('<div class="success notice"><p>' + response.data.message + '</p></div>');
                        
                        // Auto-dismiss success message after 3 seconds
                        setTimeout(function() {
                            resultsContainer.fadeOut('slow', function() {
                                $(this).html('').show();
                            });
                        }, 3000);
                    } else {
                        // Revert toggle state if there was an error
                        toggle.prop('checked', !enabled);
                        resultsContainer.html('<div class="error notice"><p>' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p></div>');
                    }
                },
                error: function() {
                    // Revert toggle state if there was an error
                    toggle.prop('checked', !enabled);
                    resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                }
            });
        },
        
        /**
         * Get metric status based on value and threshold
         * 
         * @param {number} value Metric value
         * @param {number} threshold Threshold for good performance
         * @return {string} Status class (good, average, poor)
         */
        getMetricStatus: function(value, threshold) {
            if (value <= threshold) {
                return 'good';
            } else if (value <= threshold * 2) {
                return 'average';
            } else {
                return 'poor';
            }
        },
        
        /**
         * Get metric status text
         * 
         * @param {number} value Metric value
         * @param {number} threshold Threshold for good performance
         * @return {string} Status text
         */
        getMetricStatusText: function(value, threshold) {
            if (value <= threshold) {
                return wpca_admin.good;
            } else if (value <= threshold * 2) {
                return wpca_admin.average;
            } else {
                return wpca_admin.poor;
            }
        },
        
        /**
         * Format size in bytes to human readable format
         * 
         * @param {number} bytes Size in bytes
         * @return {string} Human readable size
         */
        formatSize: function(bytes) {
            if (bytes === 0) return '0 B';
            var k = 1024;
            var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    };
    
    // Initialize performance functionality when DOM is ready
    WPCAPerformance.init();
});
