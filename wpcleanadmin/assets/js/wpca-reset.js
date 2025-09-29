(function($) {
    'use strict';
    
    // Ensure WPCA object exists
    if (typeof WPCA === 'undefined') {
        window.WPCA = {};
    }
    
    // Reset functionality module
    WPCA.reset = {
        
        /**
         * Initialize reset functionality
         */
        init: function() {
            // Add reset button event listeners
            this.addResetButtonListeners();
        },
        
        /**
         * Add click event listeners for reset buttons
         */
        addResetButtonListeners: function() {
            // General settings reset button
            $(document).on('click', '#wpca-reset-general', function(e) {
                e.preventDefault();
                WPCA.reset.confirmReset('general');
            });
            
            // Visual style reset button
            $(document).on('click', '#wpca-reset-visual', function(e) {
                e.preventDefault();
                WPCA.reset.confirmReset('visual');
            });
            
            // Login page reset button
            $(document).on('click', '#wpca-reset-login', function(e) {
                e.preventDefault();
                WPCA.reset.confirmReset('login');
            });
        },
        
        /**
         * Show reset confirmation dialog
         */
        confirmReset: function(tab) {
            var confirmText = '';
            
            // Set different confirmation text based on tab
            switch(tab) {
                case 'general':
                    confirmText = wpca_admin.general_reset_confirm || __('Are you sure you want to reset all general settings to default?', 'wp-clean-admin');
                    break;
                case 'visual':
                    confirmText = wpca_admin.visual_reset_confirm || __('Are you sure you want to reset all visual style settings to default?', 'wp-clean-admin');
                    break;
                case 'login':
                    confirmText = wpca_admin.login_reset_confirm || __('Are you sure you want to reset all login page settings to default?', 'wp-clean-admin');
                    break;
                default:
                    confirmText = wpca_admin.reset_confirm || __('Are you sure you want to reset these settings to default?', 'wp-clean-admin');
            }
            
            // Show confirmation dialog
            if (confirm(confirmText)) {
                WPCA.reset.performReset(tab);
            }
        },
        
        /**
         * Perform reset operation
         */
        performReset: function(tab) {
            // Get currently clicked button
            var $button = $('#wpca-reset-' + tab);
            var originalText = $button.html();
            
            // Show loading state
            $button.html('<span class="dashicons dashicons-update spin" style="vertical-align: middle; margin-right: 5px;"></span> ' + (wpca_admin.resetting_text || __('Resetting...', 'wp-clean-admin')));
            $button.prop('disabled', true);
            
            // Send AJAX request
            $.ajax({
                url: wpca_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpca_reset_settings',
                    tab: tab,
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    // Restore button state
                    $button.html(originalText);
                    $button.prop('disabled', false);
                    
                    // Check response
                    if (response.success) {
                        // Show success notification
                        if (typeof WPCA.core !== 'undefined' && typeof WPCA.core.showNotice === 'function') {
                            WPCA.core.showNotice('success', (wpca_admin.reset_text || __('Reset Defaults', 'wp-clean-admin')) + ' ' + (wpca_admin.reset_successful_text || __('successful', 'wp-clean-admin')));
                        } else {
                            alert((wpca_admin.reset_text || __('Reset Defaults', 'wp-clean-admin')) + ' ' + (wpca_admin.reset_successful_text || __('successful', 'wp-clean-admin')));
                        }
                        
                        // Reload page to show reset settings
                        location.reload();
                    } else {
                        // Show error notification
                        var errorMessage = response.data && response.data.message ? response.data.message : (wpca_admin.reset_failed || 'Reset failed. Please try again.');
                        if (typeof WPCA.core !== 'undefined' && typeof WPCA.core.showNotice === 'function') {
                            WPCA.core.showNotice('error', errorMessage);
                        } else {
                            alert(errorMessage);
                        }
                    }
                },
                error: function() {
                    // Restore button state
                    $button.html(originalText);
                    $button.prop('disabled', false);
                    
                    // Show error notification
                    if (typeof WPCA.core !== 'undefined' && typeof WPCA.core.showNotice === 'function') {
                        WPCA.core.showNotice('error', wpca_admin.reset_failed || 'Reset failed. Please try again.');
                    } else {
                        alert(wpca_admin.reset_failed || 'Reset failed. Please try again.');
                    }
                }
            });
        }
    };
    
    // Initialize reset functionality after document is loaded
    $(document).ready(function() {
        WPCA.reset.init();
    });
})(jQuery);