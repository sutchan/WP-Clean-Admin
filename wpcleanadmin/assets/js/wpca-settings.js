/**
 * Settings functionality for WP Clean Admin
 * 
 * @file wpcleanadmin/assets/js/wpca-settings.js
 * @version 1.7.15
 * @updated 2025-11-29
 */

jQuery(document).ready(function($) {
    'use strict';
    
    /**
     * Settings management class
     */
    var WPCASettings = {
        /**
         * Initialize settings functionality
         */
        init: function() {
            this.bindEvents();
            this.initSettingsTabs();
            this.initToggleOptions();
            this.initFormValidation();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Save settings form
            $('#wpca-settings-form').on('submit', this.saveSettings.bind(this));
            
            // Import settings form
            $('#wpca-import-settings-form').on('submit', this.importSettings.bind(this));
            
            // Export settings button
            $('#wpca-export-settings').on('click', this.exportSettings.bind(this));
            
            // Reset settings button
            $('#wpca-reset-settings').on('click', this.resetSettings.bind(this));
            
            // Toggle setting groups
            $('.wpca-setting-group-toggle').on('click', this.toggleSettingGroup.bind(this));
        },
        
        /**
         * Initialize settings tabs
         */
        initSettingsTabs: function() {
            // If tabs are already initialized, do nothing
            if (typeof WPCATabs !== 'undefined') {
                return;
            }
            
            // Simple tab functionality for settings page
            $('.wpca-settings-tab-nav a').on('click', function(e) {
                e.preventDefault();
                var tabId = $(this).attr('href');
                
                // Remove active class from all tabs
                $('.wpca-settings-tab-nav li').removeClass('active');
                $('.wpca-settings-tab-content').removeClass('active');
                
                // Add active class to the clicked tab and its content
                $(this).parent('li').addClass('active');
                $(tabId).addClass('active');
            });
        },
        
        /**
         * Initialize toggle options
         */
        initToggleOptions: function() {
            // Handle toggle switches
            $('.wpca-toggle-switch').on('change', function() {
                var toggle = $(this);
                var target = toggle.attr('data-target');
                
                if (toggle.is(':checked')) {
                    $(target).show();
                } else {
                    $(target).hide();
                }
            });
            
            // Initialize toggle states
            $('.wpca-toggle-switch').each(function() {
                var toggle = $(this);
                var target = toggle.attr('data-target');
                
                if (!toggle.is(':checked')) {
                    $(target).hide();
                }
            });
        },
        
        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            // Add validation rules for specific fields
            $('#wpca-settings-form').validate({
                rules: {
                    'wpca_settings[max_login_attempts]': {
                        required: true,
                        number: true,
                        min: 1,
                        max: 100
                    },
                    'wpca_settings[lockout_duration]': {
                        required: true,
                        number: true,
                        min: 1,
                        max: 1440
                    },
                    'wpca_settings[auto_cleanup_frequency]': {
                        required: true,
                        number: true,
                        min: 0,
                        max: 30
                    }
                },
                messages: {
                    'wpca_settings[max_login_attempts]': {
                        required: wpca_admin.error_required_field,
                        number: wpca_admin.error_must_be_number,
                        min: wpca_admin.error_min_value.replace('{min}', '1'),
                        max: wpca_admin.error_max_value.replace('{max}', '100')
                    },
                    'wpca_settings[lockout_duration]': {
                        required: wpca_admin.error_required_field,
                        number: wpca_admin.error_must_be_number,
                        min: wpca_admin.error_min_value.replace('{min}', '1'),
                        max: wpca_admin.error_max_value.replace('{max}', '1440')
                    },
                    'wpca_settings[auto_cleanup_frequency]': {
                        required: wpca_admin.error_required_field,
                        number: wpca_admin.error_must_be_number,
                        min: wpca_admin.error_min_value.replace('{min}', '0'),
                        max: wpca_admin.error_max_value.replace('{max}', '30')
                    }
                },
                errorElement: 'span',
                errorClass: 'wpca-error-message',
                errorPlacement: function(error, element) {
                    error.insertAfter(element);
                },
                submitHandler: function(form) {
                    WPCASettings.saveSettings(event);
                }
            });
        },
        
        /**
         * Save settings
         * 
         * @param {Event} e Submit event
         */
        saveSettings: function(e) {
            e.preventDefault();
            
            var form = $('#wpca-settings-form');
            var submitButton = form.find('input[type="submit"]');
            var resultsContainer = $('#wpca-settings-results');
            
            // Show loading state
            submitButton.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.saving_settings);
            resultsContainer.html('');
            
            // Get form data
            var formData = form.serialize();
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: formData + '&action=wpca_save_settings&nonce=' + wpca_admin.nonce,
                success: function(response) {
                    WPCASettings.handleSaveSettingsResponse(response);
                },
                error: function() {
                    resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                    submitButton.prop('disabled', false).html(wpca_admin.save_settings);
                }
            });
        },
        
        /**
         * Handle save settings response
         * 
         * @param {Object} response AJAX response
         */
        handleSaveSettingsResponse: function(response) {
            var submitButton = $('#wpca-settings-form').find('input[type="submit"]');
            var resultsContainer = $('#wpca-settings-results');
            
            submitButton.prop('disabled', false).html(wpca_admin.save_settings);
            
            if (response.success) {
                resultsContainer.html('<div class="success notice"><p>' + response.data.message + '</p></div>');
                
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
         * Import settings
         * 
         * @param {Event} e Submit event
         */
        importSettings: function(e) {
            e.preventDefault();
            
            var form = $('#wpca-import-settings-form');
            var submitButton = form.find('input[type="submit"]');
            var resultsContainer = $('#wpca-import-results');
            var settingsFile = $('#wpca-settings-file')[0].files[0];
            
            if (!settingsFile) {
                resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_no_file_selected + '</p></div>');
                return;
            }
            
            // Show loading state
            submitButton.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.importing_settings);
            resultsContainer.html('');
            
            // Create FormData object
            var formData = new FormData();
            formData.append('action', 'wpca_import_settings');
            formData.append('nonce', wpca_admin.nonce);
            formData.append('settings_file', settingsFile);
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    WPCASettings.handleImportSettingsResponse(response);
                },
                error: function() {
                    resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                    submitButton.prop('disabled', false).html(wpca_admin.import_settings);
                }
            });
        },
        
        /**
         * Handle import settings response
         * 
         * @param {Object} response AJAX response
         */
        handleImportSettingsResponse: function(response) {
            var submitButton = $('#wpca-import-settings-form').find('input[type="submit"]');
            var resultsContainer = $('#wpca-import-results');
            
            submitButton.prop('disabled', false).html(wpca_admin.import_settings);
            
            if (response.success) {
                resultsContainer.html('<div class="success notice"><p>' + response.data.message + '</p></div>');
                
                // Reload the page after successful import
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                resultsContainer.html('<div class="error notice"><p>' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p></div>');
            }
        },
        
        /**
         * Export settings
         */
        exportSettings: function() {
            var exportButton = $('#wpca-export-settings');
            
            // Show loading state
            exportButton.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.exporting_settings);
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_export_settings',
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    WPCASettings.handleExportSettingsResponse(response);
                },
                error: function() {
                    alert(wpca_admin.error_server_error);
                    exportButton.prop('disabled', false).html(wpca_admin.export_settings);
                }
            });
        },
        
        /**
         * Handle export settings response
         * 
         * @param {Object} response AJAX response
         */
        handleExportSettingsResponse: function(response) {
            var exportButton = $('#wpca-export-settings');
            
            exportButton.prop('disabled', false).html(wpca_admin.export_settings);
            
            if (response.success) {
                // Create a download link
                var blob = new Blob([response.data.settings_json], { type: 'application/json' });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'wp-clean-admin-settings-' + new Date().toISOString().slice(0, 10) + '.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            } else {
                alert(response.data.message || wpca_admin.error_request_processing_failed);
            }
        },
        
        /**
         * Reset settings
         */
        resetSettings: function() {
            if (!confirm(wpca_admin.confirm_reset_settings)) {
                return;
            }
            
            var resetButton = $('#wpca-reset-settings');
            var resultsContainer = $('#wpca-settings-results');
            
            // Show loading state
            resetButton.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.resetting_settings);
            resultsContainer.html('');
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_reset_settings',
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    WPCASettings.handleResetSettingsResponse(response);
                },
                error: function() {
                    resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                    resetButton.prop('disabled', false).html(wpca_admin.reset_settings);
                }
            });
        },
        
        /**
         * Handle reset settings response
         * 
         * @param {Object} response AJAX response
         */
        handleResetSettingsResponse: function(response) {
            var resetButton = $('#wpca-reset-settings');
            var resultsContainer = $('#wpca-settings-results');
            
            resetButton.prop('disabled', false).html(wpca_admin.reset_settings);
            
            if (response.success) {
                resultsContainer.html('<div class="success notice"><p>' + response.data.message + '</p></div>');
                
                // Reload the page after successful reset
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                resultsContainer.html('<div class="error notice"><p>' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p></div>');
            }
        },
        
        /**
         * Toggle setting group visibility
         * 
         * @param {Event} e Click event
         */
        toggleSettingGroup: function(e) {
            e.preventDefault();
            var toggle = $(e.currentTarget);
            var group = toggle.closest('.wpca-setting-group');
            var content = group.find('.wpca-setting-group-content');
            
            // Toggle visibility
            content.slideToggle();
            
            // Toggle active class
            group.toggleClass('active');
            
            // Update toggle text
            if (group.hasClass('active')) {
                toggle.html('<span class="dashicons dashicons-arrow-up"></span> ' + wpca_admin.hide_options);
            } else {
                toggle.html('<span class="dashicons dashicons-arrow-down"></span> ' + wpca_admin.show_options);
            }
        }
    };
    
    // Initialize settings when DOM is ready
    WPCASettings.init();
});
