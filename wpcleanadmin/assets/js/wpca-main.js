/**
 * Main functionality for WP Clean Admin
 * 
 * @file wpcleanadmin/assets/js/wpca-main.js
 * @version 1.7.15
 * @updated 2025-11-29
 */

jQuery(document).ready(function($) {
    'use strict';
    
    /**
     * Main WP Clean Admin class
     */
    var WPCAMain = {
        /**
         * Initialize main functionality
         */
        init: function() {
            this.bindGlobalEvents();
            this.initTooltips();
            this.initConfirmDialogs();
            this.initLoadingStates();
            this.loadAdminNotice();
        },
        
        /**
         * Bind global event handlers
         */
        bindGlobalEvents: function() {
            // Handle AJAX errors globally
            $(document).ajaxError(this.handleAjaxError.bind(this));
            
            // Handle custom events
            $(document).on('wpca-notification', this.showNotification.bind(this));
        },
        
        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            // Initialize tooltips for elements with data-tooltip attribute
            $('.wpca-tooltip').each(function() {
                var tooltipText = $(this).attr('data-tooltip');
                $(this).hover(
                    function() {
                        // Create tooltip element
                        var tooltip = $('<div class="wpca-tooltip-content">' + tooltipText + '</div>');
                        $(this).append(tooltip);
                    },
                    function() {
                        // Remove tooltip element
                        $(this).find('.wpca-tooltip-content').remove();
                    }
                );
            });
        },
        
        /**
         * Initialize confirm dialogs
         */
        initConfirmDialogs: function() {
            // Initialize confirm dialogs for elements with data-confirm attribute
            $('[data-confirm]').on('click', function(e) {
                var confirmText = $(this).attr('data-confirm');
                if (!confirm(confirmText)) {
                    e.preventDefault();
                    return false;
                }
                return true;
            });
        },
        
        /**
         * Initialize loading states
         */
        initLoadingStates: function() {
            // Handle loading states for buttons with data-loading attribute
            $('[data-loading]').on('click', function() {
                var button = $(this);
                var originalText = button.html();
                var loadingText = button.attr('data-loading');
                
                // Show loading state
                button.prop('disabled', true).html('<span class="spinner is-active"></span> ' + loadingText);
                
                // Store original text for later restoration
                button.data('original-text', originalText);
            });
        },
        
        /**
         * Load admin notice
         */
        loadAdminNotice: function() {
            // Check if there's a notice to display
            if (wpca_admin && wpca_admin.notice) {
                this.showNotification(wpca_admin.notice);
            }
        },
        
        /**
         * Handle AJAX errors globally
         * 
         * @param {Event} event AJAX event
         * @param {jqXHR} jqXHR jQuery XHR object
         * @param {Object} settings AJAX settings
         * @param {string} thrownError Thrown error
         */
        handleAjaxError: function(event, jqXHR, settings, thrownError) {
            // Skip handling for aborted requests
            if (jqXHR.statusText === 'abort') {
                return;
            }
            
            // Show generic error message
            this.showNotification({
                type: 'error',
                message: wpca_admin ? wpca_admin.error_server_error : 'An error occurred while processing your request.'
            });
        },
        
        /**
         * Show a notification message
         * 
         * @param {Object} notification Notification object with type and message
         */
        showNotification: function(notification) {
            var type = notification.type || 'info';
            var message = notification.message || '';
            
            // Create notification element
            var notificationElement = $('<div class="wpca-notification notice notice-' + type + ' is-dismissible">' +
                '<p>' + message + '</p>' +
                '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>' +
                '</div>');
            
            // Add to notifications container or top of page
            var container = $('#wpca-notifications-container');
            if (container.length) {
                container.append(notificationElement);
            } else {
                $('#wpbody-content').prepend(notificationElement);
            }
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                notificationElement.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        },
        
        /**
         * Restore button state after AJAX request
         * 
         * @param {jQuery} button Button element
         */
        restoreButtonState: function(button) {
            var originalText = button.data('original-text');
            if (originalText) {
                button.prop('disabled', false).html(originalText);
                button.removeData('original-text');
            }
        },
        
        /**
         * Format bytes to human readable format
         * 
         * @param {number} bytes Size in bytes
         * @return {string} Human readable size
         */
        formatBytes: function(bytes) {
            if (bytes === 0) return '0 B';
            var k = 1024;
            var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },
        
        /**
         * Get current timestamp
         * 
         * @return {number} Current timestamp in milliseconds
         */
        getTimestamp: function() {
            return Date.now();
        }
    };
    
    /**
     * Initialize all WP Clean Admin functionality
     */
    function initWPCleanAdmin() {
        // Initialize main functionality
        WPCAMain.init();
        
        // Initialize other modules if they exist
        if (typeof WPCATabs !== 'undefined') {
            WPCATabs.init();
        }
        
        if (typeof WPCADatabase !== 'undefined') {
            WPCADatabase.init();
        }
        
        if (typeof WPCASettings !== 'undefined') {
            WPCASettings.init();
        }
        
        if (typeof WPCAPerformance !== 'undefined') {
            WPCAPerformance.init();
        }
        
        if (typeof WPCAPermissions !== 'undefined') {
            WPCAPermissions.init();
        }
        
        if (typeof WPCALogin !== 'undefined') {
            WPCALogin.init();
        }
        
        if (typeof WPCAMenu !== 'undefined') {
            WPCAMenu.init();
        }
        
        if (typeof WPCAReset !== 'undefined') {
            WPCAReset.init();
        }
    }
    
    // Expose to global scope for debugging and external use
    window.WPCAMain = WPCAMain;
    
    // Initialize WP Clean Admin when DOM is ready
    initWPCleanAdmin();
});
