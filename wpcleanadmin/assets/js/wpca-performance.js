/**
 * WP Clean Admin Performance Module JavaScript
 * 
 * Handles frontend interactions for performance optimization features,
 * including database cleanup, resource management, and performance monitoring.
 * 
 * @file       wpcleanadmin/assets/js/wpca-performance.js
 * @package    WP_Clean_Admin
 * @version    1.7.13
 * @updated    2025-06-18
 * @since      1.3.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Performance monitoring namespace
    window.WPCAPerformance = {
        // Initialize performance module
        init: function() {
            this.initDatabaseCleanup();
            this.initResourceManagement();
            this.initPerformanceMonitoring();
            this.initAjaxHandlers();
        },
        
        // Initialize database cleanup functionality
        initDatabaseCleanup: function() {
            // Handle database optimization button click
            $(document).on('click', '#wpca-optimize-tables', this.handleOptimizeTables);
            
            // Handle database cleanup button click
            $(document).on('click', '#wpca-cleanup-database', this.handleCleanupDatabase);
            
            // Handle cleanup item toggles
            $(document).on('change', '.wpca-cleanup-item', this.updateCleanupSummary);
            
            // Handle select/deselect all tables
            $(document).on('change', '#wpca-select-all-tables', this.toggleSelectAllTables);
            
            // Initialize cleanup summary
            this.updateCleanupSummary();
        },
        
        // Initialize resource management functionality
        initResourceManagement: function() {
            // Handle resource removal test
            $(document).on('click', '.wpca-test-resource-removal', this.handleTestResourceRemoval);
            
            // Handle critical CSS generation
            $(document).on('click', '.wpca-generate-critical-css', this.handleGenerateCriticalCss);
            
            // Handle resource filter
            $(document).on('keyup', '#wpca-resource-filter', this.filterResources);
            
            // Handle resource type tabs
            $(document).on('click', '.wpca-resource-tabs a', this.switchResourceTab);
        },
        
        // Initialize performance monitoring functionality
        initPerformanceMonitoring: function() {
            // Handle start/stop monitoring
            $(document).on('click', '#wpca-toggle-monitoring', this.toggleMonitoring);
            
            // Handle view performance report
            $(document).on('click', '#wpca-view-performance-report', this.viewPerformanceReport);
            
            // Handle clear performance data
            $(document).on('click', '#wpca-clear-performance-data', this.clearPerformanceData);
        },
        
        // Initialize AJAX handlers
        initAjaxHandlers: function() {
            // Setup global AJAX complete handler for performance module
            $(document).ajaxComplete(this.handleAjaxComplete);
        },
        
        // Handle database table optimization
        handleOptimizeTables: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var nonce = button.data('nonce');
            var selectedTables = [];
            
            // Collect selected tables
            $('.wpca-table-checkbox:checked').each(function() {
                selectedTables.push($(this).val());
            });
            
            if (selectedTables.length === 0) {
                alert(WPCA.i18n.selectTablesFirst);
                return;
            }
            
            if (!confirm(WPCA.i18n.confirmOptimizeTables)) {
                return;
            }
            
            // Show loading indicator
            WPCAPerformance.showLoading(button);
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_optimize_tables',
                    security: nonce,
                    tables: selectedTables
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        WPCAPerformance.showNotice('success', WPCAPerformance.formatOptimizationMessage(response.data));
                        WPCAPerformance.updateDatabaseStats(response.data);
                    } else {
                        WPCAPerformance.showNotice('error', response.data.message || WPCA.i18n.optimizeFailed);
                    }
                },
                error: function() {
                    WPCAPerformance.showNotice('error', WPCA.i18n.optimizeFailed);
                },
                complete: function() {
                    WPCAPerformance.hideLoading(button);
                }
            });
        },
        
        // Handle database cleanup
        handleCleanupDatabase: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var nonce = button.data('nonce');
            var cleanupItems = {};
            
            // Collect selected cleanup items
            $('.wpca-cleanup-item:checked').each(function() {
                var itemName = $(this).val();
                cleanupItems[itemName] = {
                    enabled: true
                };
                
                // Add days parameter if available
                var daysInput = $('#wpca-' + itemName + '-days');
                if (daysInput.length) {
                    cleanupItems[itemName].days = parseInt(daysInput.val(), 10);
                }
            });
            
            if (Object.keys(cleanupItems).length === 0) {
                alert(WPCA.i18n.selectCleanupItemsFirst);
                return;
            }
            
            if (!confirm(WPCA.i18n.confirmCleanup)) {
                return;
            }
            
            // Show loading indicator
            WPCAPerformance.showLoading(button);
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_cleanup_database',
                    security: nonce,
                    cleanup_items: cleanupItems
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        WPCAPerformance.showNotice('success', WPCAPerformance.formatCleanupMessage(response.data));
                        WPCAPerformance.updateCleanupStats(response.data);
                    } else {
                        WPCAPerformance.showNotice('error', response.data.message || WPCA.i18n.cleanupFailed);
                    }
                },
                error: function() {
                    WPCAPerformance.showNotice('error', WPCA.i18n.cleanupFailed);
                },
                complete: function() {
                    WPCAPerformance.hideLoading(button);
                }
            });
        },
        
        // Handle resource removal test
        handleTestResourceRemoval: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var nonce = button.data('nonce');
            var resourceType = button.data('resource-type');
            var resourceHandle = button.data('resource-handle');
            
            if (!confirm(WPCA.i18n.confirmTestResourceRemoval)) {
                return;
            }
            
            // Show loading indicator
            WPCAPerformance.showLoading(button);
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_test_resource_removal',
                    security: nonce,
                    resource_type: resourceType,
                    resource_handle: resourceHandle
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        WPCAPerformance.showNotice('success', response.data.message);
                        // Add visual indicator that resource was tested
                        button.closest('tr').addClass('wpca-tested-resource');
                    } else {
                        WPCAPerformance.showNotice('error', response.data.message || WPCA.i18n.testResourceFailed);
                    }
                },
                error: function() {
                    WPCAPerformance.showNotice('error', WPCA.i18n.testResourceFailed);
                },
                complete: function() {
                    WPCAPerformance.hideLoading(button);
                }
            });
        },
        
        // Handle critical CSS generation
        handleGenerateCriticalCss: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var nonce = button.data('nonce');
            var pageHook = button.data('page-hook');
            
            // Show loading indicator
            WPCAPerformance.showLoading(button);
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_generate_critical_css',
                    security: nonce,
                    page_hook: pageHook
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        WPCAPerformance.showNotice('success', response.data.message);
                        // Update the status of the critical CSS
                        button.closest('tr').find('.wpca-critical-css-status').text(WPCA.i18n.generated);
                    } else {
                        WPCAPerformance.showNotice('error', response.data.message || WPCA.i18n.generateCssFailed);
                    }
                },
                error: function() {
                    WPCAPerformance.showNotice('error', WPCA.i18n.generateCssFailed);
                },
                complete: function() {
                    WPCAPerformance.hideLoading(button);
                }
            });
        },
        
        // Toggle performance monitoring
        toggleMonitoring: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var nonce = button.data('nonce');
            var isActive = button.hasClass('wpca-monitoring-active');
            
            // Show loading indicator
            WPCAPerformance.showLoading(button);
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_toggle_monitoring',
                    security: nonce,
                    active: !isActive
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (response.data.active) {
                            button.addClass('wpca-monitoring-active').removeClass('wpca-monitoring-inactive');
                            button.text(WPCA.i18n.stopMonitoring);
                            WPCAPerformance.showNotice('success', WPCA.i18n.monitoringStarted);
                        } else {
                            button.removeClass('wpca-monitoring-active').addClass('wpca-monitoring-inactive');
                            button.text(WPCA.i18n.startMonitoring);
                            WPCAPerformance.showNotice('success', WPCA.i18n.monitoringStopped);
                        }
                    } else {
                        WPCAPerformance.showNotice('error', response.data.message || WPCA.i18n.toggleMonitoringFailed);
                    }
                },
                error: function() {
                    WPCAPerformance.showNotice('error', WPCA.i18n.toggleMonitoringFailed);
                },
                complete: function() {
                    WPCAPerformance.hideLoading(button);
                }
            });
        },
        
        // View performance report
        viewPerformanceReport: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var nonce = button.data('nonce');
            
            // Show loading indicator
            WPCAPerformance.showLoading(button);
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_get_performance_report',
                    security: nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Open report in a modal or new tab
                        WPCAPerformance.openPerformanceReport(response.data.report_html);
                    } else {
                        WPCAPerformance.showNotice('error', response.data.message || WPCA.i18n.reportFailed);
                    }
                },
                error: function() {
                    WPCAPerformance.showNotice('error', WPCA.i18n.reportFailed);
                },
                complete: function() {
                    WPCAPerformance.hideLoading(button);
                }
            });
        },
        
        // Clear performance data
        clearPerformanceData: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var nonce = button.data('nonce');
            
            if (!confirm(WPCA.i18n.confirmClearData)) {
                return;
            }
            
            // Show loading indicator
            WPCAPerformance.showLoading(button);
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_clear_performance_data',
                    security: nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        WPCAPerformance.showNotice('success', response.data.message || WPCA.i18n.dataCleared);
                        // Clear the report container
                        $('#wpca-performance-report-container').html('');
                    } else {
                        WPCAPerformance.showNotice('error', response.data.message || WPCA.i18n.clearDataFailed);
                    }
                },
                error: function() {
                    WPCAPerformance.showNotice('error', WPCA.i18n.clearDataFailed);
                },
                complete: function() {
                    WPCAPerformance.hideLoading(button);
                }
            });
        },
        
        // Update cleanup summary
        updateCleanupSummary: function() {
            var selectedCount = $('.wpca-cleanup-item:checked').length;
            var totalCount = $('.wpca-cleanup-item').length;
            
            $('#wpca-cleanup-summary').text(
                WPCA.i18n.cleanupSummary.replace('%d', selectedCount).replace('%d', totalCount)
            );
        },
        
        // Toggle select all tables
        toggleSelectAllTables: function() {
            var isChecked = $(this).prop('checked');
            $('.wpca-table-checkbox').prop('checked', isChecked);
        },
        
        // Filter resources
        filterResources: function() {
            var filterValue = $(this).val().toLowerCase();
            
            $('.wpca-resource-table tbody tr').each(function() {
                var text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(filterValue) > -1);
            });
        },
        
        // Switch resource tabs
        switchResourceTab: function(e) {
            e.preventDefault();
            
            var tab = $(this);
            var tabId = tab.attr('href').substring(1);
            
            // Deactivate all tabs and content
            $('.wpca-resource-tabs a').removeClass('active');
            $('.wpca-resource-content').removeClass('active');
            
            // Activate selected tab and content
            tab.addClass('active');
            $('#' + tabId).addClass('active');
        },
        
        // Handle AJAX complete events
        handleAjaxComplete: function(event, xhr, settings) {
            // Check if the request is related to performance module
            if (settings.data && settings.data.indexOf('wpca_') !== -1) {
                // Handle any global post-AJAX operations
                // For example, updating timestamps, refreshing stats, etc.
            }
        },
        
        // Format optimization success message
        formatOptimizationMessage: function(data) {
            return WPCA.i18n.optimizeSuccess
                .replace('%d', data.success)
                .replace('%d', data.total);
        },
        
        // Format cleanup success message
        formatCleanupMessage: function(data) {
            return WPCA.i18n.cleanupSuccess.replace('%d', data.removed);
        },
        
        // Update database stats
        updateDatabaseStats: function(data) {
            // Update UI elements to reflect new database stats
            // This would typically update table counts, sizes, etc.
        },
        
        // Update cleanup stats
        updateCleanupStats: function(data) {
            // Update UI elements to reflect new cleanup stats
            // This would typically update counts of cleaned items
        },
        
        // Show loading indicator
        showLoading: function(element) {
            var originalText = element.data('original-text') || element.text();
            element.data('original-text', originalText);
            element.prop('disabled', true);
            element.addClass('wpca-loading');
            element.html('<span class="spinner is-active"></span> ' + WPCA.i18n.processing);
        },
        
        // Hide loading indicator
        hideLoading: function(element) {
            var originalText = element.data('original-text') || WPCA.i18n.processing;
            element.prop('disabled', false);
            element.removeClass('wpca-loading');
            element.text(originalText);
        },
        
        // Show notice
        showNotice: function(type, message) {
            var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' + WPCA.i18n.dismiss + '</span></button></div>');
            
            // Add the notice after the first h1 on the page
            $('.wrap > h1').first().after(notice);
            
            // Add dismiss functionality
            notice.find('.notice-dismiss').on('click', function() {
                notice.remove();
            });
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                notice.fadeOut('slow', function() {
                    notice.remove();
                });
            }, 5000);
        },
        
        // Open performance report
        openPerformanceReport: function(reportHtml) {
            var modalId = 'wpca-performance-report-modal';
            var modalHtml = '<div id="' + modalId + '" class="wpca-modal">' +
                '<div class="wpca-modal-content">' +
                '<div class="wpca-modal-header">' +
                '<h2>' + WPCA.i18n.performanceReport + '</h2>' +
                '<button type="button" class="wpca-close-modal">×</button>' +
                '</div>' +
                '<div class="wpca-modal-body">' + reportHtml + '</div>' +
                '</div>' +
                '</div>';
            
            // Add modal to body
            $('body').append(modalHtml);
            
            // Show modal
            $('#' + modalId).fadeIn();
            
            // Close modal when clicking close button
            $('#' + modalId + ' .wpca-close-modal').on('click', function() {
                $('#' + modalId).fadeOut(function() {
                    $(this).remove();
                });
            });
            
            // Close modal when clicking outside
            $('#' + modalId).on('click', function(e) {
                if (e.target === this) {
                    $(this).fadeOut(function() {
                        $(this).remove();
                    });
                }
            });
        },
        
        // Format bytes to human readable size
        formatBytes: function(bytes, decimals) {
            if (bytes === 0) return '0 Bytes';
            
            var k = 1024;
            var dm = decimals || 2;
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        },
        
        // Format number with thousands separator
        formatNumber: function(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        
        // Get current timestamp
        getTimestamp: function() {
            return new Date().toLocaleString();
        },
        
        // 获取性能统计数据
        getPerformanceStats: function() {
            // 显示加载状态
            this.showLoading();
            
            // 发送AJAX请求
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_get_performance_stats',
                    security: WPCA.nonce || ''
                },
                dataType: 'json',
                timeout: 10000, // 10秒超时
                success: function(response) {
                    // 隐藏加载状态
                    WPCAPerformance.hideLoading();
                    
                    try {
                        if (response && response.success === true) {
                            // 更新统计数据
                            WPCAPerformance.updateStats(response.data || {});
                        } else {
                            // 显示错误消息
                            var errorMsg = response && response.data && response.data.message ? 
                                response.data.message : 
                                (WPCA.i18n && WPCA.i18n.failed_to_get_stats ? WPCA.i18n.failed_to_get_stats : 'Failed to retrieve performance statistics.');
                            WPCAPerformance.showNotice('error', errorMsg);
                        }
                    } catch (e) {
                        console.error('Error processing response:', e);
                        WPCAPerformance.showNotice('error', 
                            (WPCA.i18n && WPCA.i18n.processing_error ? 
                                WPCA.i18n.processing_error : 
                                'Error processing performance data.')
                        );
                    }
                },
                error: function(xhr, status, error) {
                    // 隐藏加载状态
                    WPCAPerformance.hideLoading();
                    
                    // 显示错误消息
                    var errorMessage = (WPCA.i18n && WPCA.i18n.ajax_error ? 
                        WPCA.i18n.ajax_error : 
                        'AJAX request failed.');
                    WPCAPerformance.showNotice('error', errorMessage);
                    console.error('AJAX Error:', status, error);
                },
                complete: function() {
                    // 确保加载状态被隐藏，即使发生错误
                    WPCAPerformance.hideLoading();
                }
            });
        },
        
        // 更新统计数据显示
        updateStats: function(stats) {
            // 确保stats是对象
            if (!stats || typeof stats !== 'object') {
                console.error('Invalid stats data:', stats);
                var invalidDataMsg = (WPCA.i18n && WPCA.i18n.invalid_data ? 
                    WPCA.i18n.invalid_data : 
                    'Invalid performance data received.');
                WPCAPerformance.showNotice('error', invalidDataMsg);
                return;
            }
            
            // 更新图表和统计信息
            if (typeof this.renderPerformanceChart === 'function') {
                try {
                    this.renderPerformanceChart(stats);
                } catch (e) {
                    console.error('Error rendering chart:', e);
                }
            }
            
            // 更新文本统计
            jQuery('#wpca-performance-total-time').text(this.formatTime(stats.total_time || 0));
            jQuery('#wpca-performance-average-time').text(this.formatTime(stats.average_time || 0));
            jQuery('#wpca-performance-peak-memory').text(this.formatBytes(stats.peak_memory || 0));
            jQuery('#wpca-performance-query-count').text(this.formatNumber(stats.query_count || 0));
            jQuery('#wpca-performance-sample-count').text(this.formatNumber(stats.sample_count || 0));
            
            // 显示最慢页面
            if (stats.slowest_page && stats.slowest_page !== 'N/A') {
                jQuery('#wpca-performance-slowest-page').text(stats.slowest_page);
            } else if (jQuery('#wpca-performance-slowest-page').length) {
                jQuery('#wpca-performance-slowest-page').text('-');
            }
            
            // 更新慢页面列表 - 已在下方通过displaySlowPagesList处理
            
            // 显示最慢的10个页面
            if (Array.isArray(stats.slow_pages) && stats.slow_pages.length > 0) {
                this.displaySlowPagesList(stats.slow_pages);
            } else if (jQuery('#wpca-slow-pages-list').length) {
                jQuery('#wpca-slow-pages-list').html('<li>' + 
                    ((WPCA.i18n && WPCA.i18n.no_slow_pages) ? 
                        WPCA.i18n.no_slow_pages : 
                        'No slow pages detected') + 
                    '</li>');
            }
        },
        
        // Format time
        formatTime: function(seconds) {
            if (typeof seconds !== 'number') {
                seconds = parseFloat(seconds) || 0;
            }
            if (seconds < 0.001) {
                return (seconds * 1000).toFixed(2) + ' ms';
            } else if (seconds < 1) {
                return (seconds * 1000).toFixed(0) + ' ms';
            } else {
                return seconds.toFixed(2) + ' s';
            }
        },
        
        // 显示最慢页面列表
        displaySlowPagesList: function(slowPages) {
            var listElement = jQuery('#wpca-slow-pages-list');
            if (!listElement.length || !Array.isArray(slowPages)) {
                return;
            }
            
            var listHtml = '';
            
            // 只显示前10个最慢的页面
            var pagesToShow = slowPages.slice(0, 10);
            
            if (pagesToShow.length === 0) {
                listHtml = '<li>' + 
                    ((WPCA.i18n && WPCA.i18n.no_slow_pages) ? 
                        WPCA.i18n.no_slow_pages : 
                        'No slow pages detected') + 
                    '</li>';
            } else {
                jQuery.each(pagesToShow, function(index, page) {
                    if (page && page.page && page.time) {
                        var pageName = page.page.length > 50 ? 
                            page.page.substring(0, 50) + '...' : 
                            page.page;
                        
                        var timeLabel = (WPCA.i18n && WPCA.i18n.avg_load_time) ? 
                            WPCA.i18n.avg_load_time : 
                            'Avg. Load Time';
                        
                        listHtml += '<li class="wpca-slow-page-item">';
                        listHtml += '<span class="wpca-page-name">' + pageName + '</span>';
                        listHtml += '<span class="wpca-page-time">' + timeLabel + ': ' + 
                            WPCAPerformance.formatTime(page.time) + '</span>';
                        
                        // 如果有额外信息，也显示出来
                        if (page.hits) {
                            var countLabel = (WPCA.i18n && WPCA.i18n.samples) ? 
                                WPCA.i18n.samples : 
                                'Samples';
                            listHtml += '<span class="wpca-page-count">' + countLabel + ': ' + 
                                WPCAPerformance.formatNumber(page.hits) + '</span>';
                        }
                        
                        listHtml += '</li>';
                    }
                });
            }
            
            // 更新DOM
            try {
                listElement.html(listHtml);
                // 添加CSS类以支持动画或样式
                listElement.addClass('wpca-slow-pages-updated');
                
                // 短暂延迟后移除类，以便下次更新时可以再次添加动画
                setTimeout(function() {
                    listElement.removeClass('wpca-slow-pages-updated');
                }, 500);
            } catch (e) {
                console.error('Error updating slow pages list DOM:', e);
            }
        }
    };
    
    // Initialize the performance module
    WPCAPerformance.init();
});