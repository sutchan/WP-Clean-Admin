/**
 * WP Clean Admin Login JavaScript
 *
 * @package WPCA
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
// Use IIFE to encapsulate the login functionality
(function($) {
    'use strict';
    
    /**
     * Login functionality for WPCA
     */
    const WPCALogin = {
        /**
         * Initialize login functionality
         */
        init: function() {
            this.bindEvents();
            this.initLoginForm();
        },
        
        /**
         * Bind events for login
         */
        bindEvents: function() {
            // Handle login form submission
            $(document).on('submit', '#wpca-login-form', this.handleLoginSubmit.bind(this));
            
            // Handle password visibility toggles
            $(document).on('click', '.wpca-password-toggle', this.handlePasswordToggle.bind(this));
        },
        
        /**
         * Initialize login form
         */
        initLoginForm: function() {
            // Add visual enhancements to login form
            const $loginForm = $('#wpca-login-form');
            if ($loginForm.length) {
                $loginForm.addClass('wpca-login-form-enhanced');
            }
        },
        
        /**
         * Handle login form submission
         * @param {Event} e - The submit event
         */
        handleLoginSubmit: function(e) {
            const $form = $(e.currentTarget);
            const $submitBtn = $form.find('input[type="submit"]');
            
            if ($submitBtn.length) {
                $submitBtn.prop('disabled', true).val($submitBtn.data('loading-text') || 'Logging in...');
            }
        },
        
        /**
         * Handle password visibility toggles
         * @param {Event} e - The click event
         */
        handlePasswordToggle: function(e) {
            e.preventDefault();
            
            const $toggle = $(e.currentTarget);
            const $passwordField = $toggle.prev('input[type="password"]');
            
            if ($passwordField.length) {
                const type = $passwordField.attr('type');
                const newType = type === 'password' ? 'text' : 'password';
                const newIcon = type === 'password' ? 'hide' : 'show';
                
                $passwordField.attr('type', newType);
                $toggle.text($toggle.data(newIcon + '-text') || (newType === 'password' ? 'Show' : 'Hide'));
            }
        }
    };
    
    // Initialize login functionality when DOM is ready
    $(document).ready(function() {
        WPCALogin.init();
    });
    
    // Expose to global scope for potential external use
    window.WPCALogin = WPCALogin;
})(jQuery);

