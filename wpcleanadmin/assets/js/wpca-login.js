/**
 * Login functionality for WP Clean Admin
 * 
 * @file wpcleanadmin/assets/js/wpca-login.js
 * @version 1.7.15
 * @updated 2025-11-29
 */

jQuery(document).ready(function($) {
    'use strict';
    
    /**
     * Login management class
     */
    var WPCALogin = {
        /**
         * Initialize login functionality
         */
        init: function() {
            this.bindEvents();
            this.initLoginForm();
            this.initLockoutNotice();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Login form submission
            $('#wpca-login-form').on('submit', this.handleLoginSubmit.bind(this));
            
            // Password reset form submission
            $('#wpca-password-reset-form').on('submit', this.handlePasswordResetSubmit.bind(this));
            
            // Show/hide password toggle
            $('.wpca-show-password').on('click', this.togglePasswordVisibility.bind(this));
            
            // Login attempt log refresh
            $('#wpca-refresh-login-logs').on('click', this.refreshLoginLogs.bind(this));
        },
        
        /**
         * Initialize login form
         */
        initLoginForm: function() {
            // Add loading state to login button
            $('#wpca-login-button').on('click', function() {
                var button = $(this);
                var originalText = button.html();
                button.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.logging_in);
                button.data('original-text', originalText);
            });
        },
        
        /**
         * Initialize lockout notice
         */
        initLockoutNotice: function() {
            // Check if user is locked out
            var lockoutNotice = $('#wpca-lockout-notice');
            if (lockoutNotice.length) {
                this.startLockoutCountdown();
            }
        },
        
        /**
         * Handle login form submission
         * 
         * @param {Event} e Submit event
         */
        handleLoginSubmit: function(e) {
            e.preventDefault();
            var form = $(e.currentTarget);
            var button = form.find('#wpca-login-button');
            var resultsContainer = $('#wpca-login-results');
            
            // Clear previous results
            resultsContainer.html('');
            
            // Validate form
            var username = form.find('#wpca-username').val().trim();
            var password = form.find('#wpca-password').val().trim();
            
            if (!username || !password) {
                resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_required_fields + '</p></div>');
                this.restoreButtonState(button);
                return;
            }
            
            // Submit form via AJAX
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: form.serialize() + '&action=wpca_handle_login&nonce=' + wpca_admin.nonce,
                success: function(response) {
                    WPCALogin.handleLoginResponse(response, button, resultsContainer);
                },
                error: function() {
                    resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                    WPCALogin.restoreButtonState(button);
                }
            });
        },
        
        /**
         * Handle login response
         * 
         * @param {Object} response AJAX response
         * @param {jQuery} button Login button element
         * @param {jQuery} resultsContainer Results container element
         */
        handleLoginResponse: function(response, button, resultsContainer) {
            this.restoreButtonState(button);
            
            if (response.success) {
                // Show success message and redirect
                resultsContainer.html('<div class="success notice"><p>' + response.data.message + '</p></div>');
                
                if (response.data.redirect_url) {
                    setTimeout(function() {
                        window.location.href = response.data.redirect_url;
                    }, 1500);
                }
            } else {
                // Show error message
                resultsContainer.html('<div class="error notice"><p>' + (response.data.message || wpca_admin.error_login_failed) + '</p></div>');
                
                // If locked out, show countdown
                if (response.data.locked_out) {
                    this.showLockoutCountdown(response.data.lockout_duration);
                }
            }
        },
        
        /**
         * Handle password reset form submission
         * 
         * @param {Event} e Submit event
         */
        handlePasswordResetSubmit: function(e) {
            e.preventDefault();
            var form = $(e.currentTarget);
            var button = form.find('#wpca-reset-password-button');
            var resultsContainer = $('#wpca-reset-password-results');
            
            // Clear previous results
            resultsContainer.html('');
            
            // Validate form
            var email = form.find('#wpca-reset-email').val().trim();
            if (!email) {
                resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_required_email + '</p></div>');
                return;
            }
            
            // Show loading state
            button.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.sending_reset_link);
            
            // Submit form via AJAX
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: form.serialize() + '&action=wpca_handle_password_reset&nonce=' + wpca_admin.nonce,
                success: function(response) {
                    WPCALogin.handlePasswordResetResponse(response, button, resultsContainer);
                },
                error: function() {
                    resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                    button.prop('disabled', false).html(wpca_admin.reset_password);
                }
            });
        },
        
        /**
         * Handle password reset response
         * 
         * @param {Object} response AJAX response
         * @param {jQuery} button Reset button element
         * @param {jQuery} resultsContainer Results container element
         */
        handlePasswordResetResponse: function(response, button, resultsContainer) {
            button.prop('disabled', false).html(wpca_admin.reset_password);
            
            if (response.success) {
                resultsContainer.html('<div class="success notice"><p>' + response.data.message + '</p></div>');
                // Clear form fields
                $('#wpca-reset-password-form')[0].reset();
            } else {
                resultsContainer.html('<div class="error notice"><p>' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p></div>');
            }
        },
        
        /**
         * Toggle password visibility
         * 
         * @param {Event} e Click event
         */
        togglePasswordVisibility: function(e) {
            e.preventDefault();
            var toggle = $(e.currentTarget);
            var passwordField = toggle.closest('.wpca-password-field').find('input[type="password"]');
            var fieldType = passwordField.attr('type');
            
            if (fieldType === 'password') {
                passwordField.attr('type', 'text');
                toggle.html('<span class="dashicons dashicons-hidden"></span> ' + wpca_admin.hide_password);
            } else {
                passwordField.attr('type', 'password');
                toggle.html('<span class="dashicons dashicons-visibility"></span> ' + wpca_admin.show_password);
            }
        },
        
        /**
         * Start lockout countdown
         */
        startLockoutCountdown: function() {
            var countdownElement = $('#wpca-lockout-countdown');
            if (!countdownElement.length) {
                return;
            }
            
            var remainingTime = parseInt(countdownElement.data('remaining-time'));
            if (isNaN(remainingTime)) {
                return;
            }
            
            var countdownInterval = setInterval(function() {
                remainingTime--;
                
                if (remainingTime <= 0) {
                    clearInterval(countdownInterval);
                    // Reload the page to check if lockout is lifted
                    window.location.reload();
                    return;
                }
                
                // Update countdown display
                var minutes = Math.floor(remainingTime / 60);
                var seconds = remainingTime % 60;
                countdownElement.html(minutes + ':' + (seconds < 10 ? '0' : '') + seconds);
            }, 1000);
        },
        
        /**
         * Show lockout countdown
         * 
         * @param {number} lockoutDuration Lockout duration in seconds
         */
        showLockoutCountdown: function(lockoutDuration) {
            var countdownElement = $('<span id="wpca-lockout-countdown" data-remaining-time="' + lockoutDuration + '"></span>');
            var lockoutNotice = $('#wpca-lockout-notice');
            
            if (lockoutNotice.length) {
                lockoutNotice.append(' ' + wpca_admin.lockout_countdown + ' ' + countdownElement);
                this.startLockoutCountdown();
            }
        },
        
        /**
         * Refresh login logs
         */
        refreshLoginLogs: function() {
            var button = $('#wpca-refresh-login-logs');
            var logsContainer = $('#wpca-login-logs-container');
            
            // Show loading state
            button.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.refreshing);
            logsContainer.html('<p>' + wpca_admin.loading + '</p>');
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_get_login_logs',
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    WPCALogin.handleLoginLogsResponse(response, button, logsContainer);
                },
                error: function() {
                    logsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                    button.prop('disabled', false).html(wpca_admin.refresh);
                }
            });
        },
        
        /**
         * Handle login logs response
         * 
         * @param {Object} response AJAX response
         * @param {jQuery} button Refresh button element
         * @param {jQuery} logsContainer Logs container element
         */
        handleLoginLogsResponse: function(response, button, logsContainer) {
            button.prop('disabled', false).html(wpca_admin.refresh);
            
            if (response.success) {
                logsContainer.html(response.data.html);
            } else {
                logsContainer.html('<div class="error notice"><p>' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p></div>');
            }
        },
        
        /**
         * Restore button state
         * 
         * @param {jQuery} button Button element
         */
        restoreButtonState: function(button) {
            var originalText = button.data('original-text');
            if (originalText) {
                button.prop('disabled', false).html(originalText);
                button.removeData('original-text');
            }
        }
    };
    
    // Initialize login functionality when DOM is ready
    WPCALogin.init();
});
