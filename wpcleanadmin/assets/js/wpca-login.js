/**
 * WP Clean Admin - Login Management Module
 * Handles login page customization and media upload
 */

// Ensure WPCA namespace exists
window.WPCA = window.WPCA || {};

/**
 * Login management module
 */
WPCA.login = {
    config: null, // Will store configuration
    
    /**
     * Initialize login management functionality
     * @returns {boolean} - True on success, false on failure.
     */
    init: function() {
        const $ = jQuery;

        // --- Configuration retrieval and validation ---
        if (typeof window.wpca_admin === 'undefined') {
            console.error('WPCA Error: The configuration object "wpca_admin" is missing.');
            return false;
        }
        this.config = window.wpca_admin;

        if (this.config.debug) {
            console.log('WPCA Login Management: Initializing...');
        }

        // --- Initialize different components ---
        this.initMediaUploader();
        this.initPreviewUpdate();
        this.initStyleToggle();
        this.initAutoHideForm();

        if (this.config.debug) {
            console.log('WPCA Login Management: Initialized successfully.');
        }

        return true;
    },

    /**
     * Initialize media uploader for login logo and background
     */
    initMediaUploader: function() {
        const $ = jQuery;
        
        // --- Admin settings page media uploader ---
        if ($('#wpca-login-media-uploader').length || $('.wpca-media-uploader').length) {
            // Use event delegation for upload buttons to work with the new HTML structure
            $(document).on('click', '.wpca-upload-button', function(e) {
                e.preventDefault();
                const button = $(this);
                const target = button.data('target');
                const settingName = target.replace('wpca-login-', '');
                WPCA.login.openMediaUploader(settingName, 'Select or Upload Media', 'image');
            });

            // Use event delegation for remove buttons
            $(document).on('click', '.wpca-remove-button', function(e) {
                e.preventDefault();
                const button = $(this);
                const target = button.data('target');
                const targetField = $('#' + target);
                const previewDiv = $('#' + target + '-preview');
                
                targetField.val('');
                previewDiv.hide().html('');
            });
        }
    },

    /**
     * Open WordPress media uploader
     * @param {string} settingName - The name of the setting to update
     * @param {string} title - The title of the media uploader
     * @param {string} type - The type of media to allow
     */
    openMediaUploader: function(settingName, title, type) {
        // Check if WordPress media uploader is available
        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            console.error('WPCA Error: WordPress media uploader is not available');
            alert(wpca_admin.media_uploader_not_available || 'WordPress media uploader is not available');
            return;
        }
        
        // Create the media frame if it doesn't exist
        let mediaFrame = wp.media({
            title: title,
            button: {
                text: wpca_admin.use_this_media || 'Use this media'
            },
            multiple: false,
            library: {
                type: type
            }
        });
        
        // When an image is selected, run a callback
        mediaFrame.on('select', function() {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            
            // Update the URL field and preview - using the correct IDs from the HTML
            const urlFieldId = `#wpca-login-${settingName}`; // ID format from the HTML is wpca-login-logo, not wpca-login-logo-url
            const previewId = `#wpca-login-${settingName}-preview`;
            
            // Update the field value
            jQuery(urlFieldId).val(attachment.url);
            
            // Create preview and show it
            if (type === 'image') {
                jQuery(previewId).html(`<img src="${attachment.url}" style="max-width: 100%; max-height: 200px;" alt="Preview">`);
            } else {
                jQuery(previewId).html(`<p>Selected: ${attachment.filename}</p>`);
            }
            
            // Ensure preview is visible
            jQuery(previewId).show();
        });
        
        // Open the modal
        mediaFrame.open();
    },

    /**
     * Initialize live preview updates for login page settings
     */
    initPreviewUpdate: function() {
        const $ = jQuery;
        
        // Only run on the login settings page - using preview iframe element as criterion
        if (!$('#wpca-login-preview-iframe').length) {
            return;
        }

        // Update preview when login-related form fields change
        $('#wpca-login-logo, #wpca-login-background, #wpca-login-custom-css').on('change', function() {
            WPCA.login.updatePreview();
        });
        
        // Update preview when general color settings change
        $('.wpca-color-picker-wrap input[data-color-type]').on('change', function() {
            WPCA.login.updatePreview();
        });

        // Initial preview update
        this.updatePreview();
    },

    /**
     * Update the login page preview based on current settings
     */
    updatePreview: function() {
        const $ = jQuery;
        
        // Get current settings - using actual field IDs and default values from the page
        const logoUrl = $('#wpca-login-logo').val(); // Logo URL field
        const backgroundImage = $('#wpca-login-background').val(); // Background image field
        const customCss = $('#wpca-login-custom-css').val(); // Custom CSS field
        
        // Use color field values from main interface, or provide default values
        const primaryColor = $('.wpca-color-picker-wrap input[data-color-type="primary"]').val() || '#007cba';
        const backgroundColor = $('.wpca-color-picker-wrap input[data-color-type="background"]').val() || '#f0f0f1';
        const textColor = $('.wpca-color-picker-wrap input[data-color-type="text"]').val() || '#3c434a';
        
        // Use default values for button color and border radius
        const buttonColor = primaryColor; // Button color uses primary color
        const buttonTextColor = '#ffffff'; // Button text color defaults to white
        const borderRadius = '3'; // Border radius defaults to 3px
        
        // Generate the preview HTML
        let previewHtml = `
            <style>
                body {
                    background-color: ${backgroundColor} !important;
                    color: ${textColor} !important;
                }
                
                ${backgroundImage ? `body.login { background-image: url('${backgroundImage}') !important; background-size: cover !important; background-position: center !important; }` : ''}
                
                #login h1 a {
                    ${logoUrl ? `background-image: url('${logoUrl}') !important;` : ''}
                    background-size: contain !important;
                    width: 100% !important;
                    height: 100px !important;
                    margin-bottom: 20px !important;
                }
                
                .login form {
                    border-radius: ${borderRadius}px !important;
                }
                
                .login .button-primary {
                    background-color: ${buttonColor} !important;
                    border-color: ${buttonColor} !important;
                    color: ${buttonTextColor} !important;
                    text-shadow: none !important;
                    box-shadow: none !important;
                    border-radius: ${borderRadius}px !important;
                }
                
                .login .button-primary:hover {
                    opacity: 0.9 !important;
                }
                
                ${customCss}
            </style>
        `;
        
        // Update the preview iframe
        const $previewIframe = $('#wpca-login-preview-iframe');
        if ($previewIframe.length) {
            // Create a new iframe or update the existing one
            if ($previewIframe[0].contentWindow) {
                const previewDoc = $previewIframe[0].contentWindow.document;
                previewDoc.open();
                previewDoc.write(previewHtml);
                previewDoc.close();
            }
        }
    },

    /**
     * Initialize style toggle functionality for login page
     */
    initStyleToggle: function() {
        const $ = jQuery;
        
        // Only run on the settings page
        if (!$('#wpca-login-style-toggle').length) {
            return;
        }

        // Toggle between different style presets
        $('#wpca-login-style-toggle').on('change', function() {
            const selectedStyle = $(this).val();
            WPCA.login.applyStylePreset(selectedStyle);
        });
    },

    /**
     * Apply a style preset to the login page settings
     * @param {string} presetName - The name of the style preset to apply
     */
    applyStylePreset: function(presetName) {
        // Define style presets
        const presets = {
            'default': {
                backgroundColor: '#f0f0f1',
                textColor: '#3c434a',
                buttonColor: '#007cba',
                buttonTextColor: '#ffffff',
                borderRadius: '3'
            },
            'dark': {
                backgroundColor: '#1d2327',
                textColor: '#d1d5db',
                buttonColor: '#2563eb',
                buttonTextColor: '#ffffff',
                borderRadius: '6'
            },
            'light': {
                backgroundColor: '#ffffff',
                textColor: '#111827',
                buttonColor: '#3b82f6',
                buttonTextColor: '#ffffff',
                borderRadius: '8'
            },
            'minimal': {
                backgroundColor: '#f9fafb',
                textColor: '#374151',
                buttonColor: '#10b981',
                buttonTextColor: '#ffffff',
                borderRadius: '4'
            }
        };
        
        // Apply the selected preset
        if (presets[presetName]) {
            const preset = presets[presetName];
            const $ = jQuery;
            
            // Use the same selector strategy as updatePreview function to update existing color fields
            $('.wpca-color-picker-wrap input[data-color-type="background"]').val(preset.backgroundColor);
            $('.wpca-color-picker-wrap input[data-color-type="text"]').val(preset.textColor);
            $('.wpca-color-picker-wrap input[data-color-type="primary"]').val(preset.buttonColor);
            
            // Update preview
            this.updatePreview();
        }
    },

    /**
     * Initialize auto hide login form functionality
     */
    initAutoHideForm: function() {
        const $ = jQuery;
        
        // Only run on the settings page
        if (!$('#wpca-login-auto-hide-form').length) {
            return;
        }

        // Show/hide delay field based on auto hide checkbox
        $('#wpca-login-auto-hide-form').on('change', function() {
            $('#wpca-login-auto-hide-delay-container').toggle($(this).is(':checked'));
        });

        // Initial state
        $('#wpca-login-auto-hide-delay-container').toggle($('#wpca-login-auto-hide-form').is(':checked'));
    }
};

/**
 * Login frontend module
 */
WPCA.loginFrontend = {
    config: null,
    
    /**
     * Initialize login frontend functionality
     * @returns {boolean} - True on success, false on failure.
     */
    init: function() {
        const $ = jQuery;
        
        // --- Configuration retrieval and validation ---
        if (typeof window.wpca_login_frontend === 'undefined') {
            console.error('WPCA Error: The configuration object "wpca_login_frontend" is missing.');
            return false;
        }
        this.config = window.wpca_login_frontend;
        
        // --- Apply custom styles ---
        if (this.config.custom_styles) {
            this.applyCustomStyles();
        }
        
        // --- Initialize auto hide form functionality ---
        if (this.config.auto_hide_form) {
            this.initAutoHideForm();
        }
        
        if (this.config.debug) {
            console.log('WPCA Login Frontend: Initialized successfully.');
        }
        
        return true;
    },
    
    /**
     * Apply custom styles to the login page
     */
    applyCustomStyles: function() {
        // Add custom styles to the head
        const styleElement = document.createElement('style');
        styleElement.textContent = this.config.custom_styles;
        document.head.appendChild(styleElement);
    },
    
    /**
     * Initialize auto hide form functionality
     */
    initAutoHideForm: function() {
        const $ = jQuery;
        
        // Only run on the actual login page
        if (!$('body.login').length) {
            return;
        }
        
        // Add some padding to body
        $('body.login').css('padding-top', '5%');
        
        // Get the form element
        const $loginForm = $('#loginform');
        
        // If no delay is set, use a default of 3 seconds
        const delay = this.config.auto_hide_delay || 3000;
        
        // Create a reveal button
        const $revealButton = $('<button>')
            .attr('type', 'button')
            .attr('id', 'wpca-reveal-login-form')
            .text(wpca_admin.show_login_form || 'Show Login Form')
            .css({
                display: 'none',
                margin: '20px auto',
                padding: '10px 20px',
                backgroundColor: '#007cba',
                color: 'white',
                border: 'none',
                borderRadius: '3px',
                cursor: 'pointer',
                fontSize: '16px'
            })
            .on('click', function() {
                $loginForm.fadeIn();
                $(this).fadeOut();
            });
        
        // Add the button after the form
        $loginForm.after($revealButton);
        
        // Hide the form after the specified delay
        setTimeout(function() {
            $loginForm.fadeOut();
            $revealButton.fadeIn();
        }, delay);
    }
};

// Initialize admin module
jQuery(document).ready(function() {
    if (typeof WPCA !== 'undefined' && typeof WPCA.login !== 'undefined' && typeof WPCA.login.init === 'function') {
        WPCA.login.init();
        if (WPCA.login.config.debug) {
            console.log('Admin module initialized');
        }
    }
    
    // Initialize frontend module
    if (typeof WPCA !== 'undefined' && typeof WPCA.loginFrontend !== 'undefined' && typeof WPCA.loginFrontend.init === 'function') {
        WPCA.loginFrontend.init();
        if (WPCA.loginFrontend.config.debug) {
            console.log('Frontend module initialized');
        }
    }
    
    // Error handling if modules fail to load
    try {
        if (typeof WPCA === 'undefined' || typeof WPCA.login === 'undefined' && typeof WPCA.loginFrontend === 'undefined') {
            console.error('WPCA Error: Login management modules failed to load.');
        }
    } catch (error) {
        console.error('WPCA Login initialization failed: ', error);
    }
});