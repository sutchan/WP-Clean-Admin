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
        this.initAJAXHandlers();

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
        if ($('#wpca-login-media-uploader').length) {
            // Media uploader for logo
            $('#wpca-upload-login-logo').on('click', function(e) {
                e.preventDefault();
                WPCA.login.openMediaUploader('login_logo', 'Select or Upload Media', 'image');
            });

            // Media uploader for background
            $('#wpca-upload-login-background').on('click', function(e) {
                e.preventDefault();
                WPCA.login.openMediaUploader('login_background', 'Select or Upload Media', 'image');
            });

            // Clear logo button
            $('#wpca-clear-login-logo').on('click', function(e) {
                e.preventDefault();
                $('#wpca-login-logo-url').val('');
                $('#wpca-login-logo-preview').html('<p>No logo selected</p>');
            });

            // Clear background button
            $('#wpca-clear-login-background').on('click', function(e) {
                e.preventDefault();
                $('#wpca-login-background-url').val('');
                $('#wpca-login-background-preview').html('<p>No background selected</p>');
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
            alert('WordPress media uploader is not available');
            return;
        }
        
        // Create the media frame if it doesn't exist
        let mediaFrame = wp.media({
            title: title,
            button: {
                text: 'Use this media'
            },
            multiple: false,
            library: {
                type: type
            }
        });
        
        // When an image is selected, run a callback
        mediaFrame.on('select', function() {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            
            // Update the URL field and preview
            const urlFieldId = `#wpca-${settingName}-url`;
            const previewId = `#wpca-${settingName}-preview`;
            
            jQuery(urlFieldId).val(attachment.url);
            
            // Create preview
            if (type === 'image') {
                jQuery(previewId).html(`<img src="${attachment.url}" style="max-width: 100%; max-height: 200px;" alt="Preview">`);
            } else {
                jQuery(previewId).html(`<p>Selected: ${attachment.filename}</p>`);
            }
        });
        
        // Open the modal
        mediaFrame.open();
    },

    /**
     * Initialize live preview updates for login page settings
     */
    initPreviewUpdate: function() {
        const $ = jQuery;
        
        // Only run on the settings page
        if (!$('#wpca-login-media-uploader').length) {
            return;
        }

        // Update preview when form fields change
        $('.wpca-login-setting').on('change', function() {
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
        
        // Get current settings
        const logoUrl = $('#wpca-login-logo-url').val();
        const backgroundColor = $('#wpca-login-background-color').val();
        const backgroundImage = $('#wpca-login-background-url').val();
        const textColor = $('#wpca-login-text-color').val();
        const buttonColor = $('#wpca-login-button-color').val();
        const buttonTextColor = $('#wpca-login-button-text-color').val();
        const borderRadius = $('#wpca-login-border-radius').val();
        const customCss = $('#wpca-login-custom-css').val();
        
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
            },
            'gradient': {
                backgroundColor: '#667eea',
                textColor: '#333333',
                buttonColor: '#667eea',
                buttonTextColor: '#ffffff',
                borderRadius: '12'
            },
            'glassmorphism': {
                backgroundColor: '#f5f7fa',
                textColor: '#333333',
                buttonColor: '#667eea',
                buttonTextColor: '#ffffff',
                borderRadius: '16'
            },
            'neumorphism': {
                backgroundColor: '#e6e9ef',
                textColor: '#333333',
                buttonColor: '#e6e9ef',
                buttonTextColor: '#2271b1',
                borderRadius: '20'
            },
            'custom': {
                // For custom style, we don't preset values
                // Users will define their own through custom CSS
                backgroundColor: '#f0f0f1',
                textColor: '#3c434a',
                buttonColor: '#007cba',
                buttonTextColor: '#ffffff',
                borderRadius: '8'
            }
        };
        
        // Apply the selected preset
        if (presets[presetName]) {
            const preset = presets[presetName];
            const $ = jQuery;
            
            // Update form fields with preset values
            $('#wpca-login-background-color').val(preset.backgroundColor);
            $('#wpca-login-text-color').val(preset.textColor);
            $('#wpca-login-button-color').val(preset.buttonColor);
            $('#wpca-login-button-text-color').val(preset.buttonTextColor);
            $('#wpca-login-border-radius').val(preset.borderRadius);
            
            // For custom style, focus on the custom CSS field
            if (presetName === 'custom' && $('#wpca-login-custom-css').length) {
                $('#wpca-login-custom-css').focus();
            }
            
            // Update the preview
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
    },
    
    /**
     * Initialize AJAX handlers for login settings
     */
    initAJAXHandlers: function() {
        const $ = jQuery;
        
        // Save login settings button
        if ($('#wpca-save-login-settings').length) {
            $('#wpca-save-login-settings').on('click', function(e) {
                e.preventDefault();
                WPCA.login.saveLoginSettings();
            });
        }
        
        // Reset login settings button
        if ($('#wpca-reset-login-settings').length) {
            $('#wpca-reset-login-settings').on('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to reset login settings to default?')) {
                    WPCA.login.resetLoginSettings();
                }
            });
        }
    },
    
    /**
     * Save login settings via AJAX
     */
    saveLoginSettings: function() {
        const $ = jQuery;
        const $saveButton = $('#wpca-save-login-settings');
        const $statusMessage = $saveButton.closest('form').find('.wpca-status-message');
        
        // Show loading state
        $saveButton.prop('disabled', true).data('original-text', $saveButton.html());
        $saveButton.html('<span class="spinner is-active"></span> Saving...');
        $statusMessage.html('').removeClass('success error');
        
        // Collect form data
        const formData = {
            login_style: $('#wpca-login-style').val(),
            login_logo_url: $('#wpca-login-logo-url').val(),
            login_background_url: $('#wpca-login-background-url').val(),
            login_custom_css: $('#wpca-login-custom-css').val(),
            login_elements: {}
        };
        
        // Collect login elements settings
        $('#wpca-login-elements-tabs input[type="checkbox"]').each(function() {
            const elementName = $(this).attr('name');
            if (elementName) {
                formData.login_elements[elementName] = $(this).is(':checked') ? 1 : 0;
            }
        });
        
        // Send AJAX request
        $.ajax({
            url: this.config.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpca_save_login_settings',
                nonce: this.config.nonce,
                settings: JSON.stringify(formData)
            },
            success: function(response) {
                $saveButton.prop('disabled', false).html($saveButton.data('original-text'));
                
                if (response.success) {
                    $statusMessage.html('<div class="updated notice notice-success"><p>Login settings saved successfully!</p></div>').addClass('success');
                    
                    // Update preview if it exists
                    WPCA.login.updatePreview();
                } else {
                    $statusMessage.html('<div class="error notice notice-error"><p>Failed to save settings. ' + (response.data.message || '') + '</p></div>').addClass('error');
                }
                
                // Clear status message after 5 seconds
                setTimeout(function() {
                    $statusMessage.html('').removeClass('success error');
                }, 5000);
            },
            error: function(xhr, status, error) {
                $saveButton.prop('disabled', false).html($saveButton.data('original-text'));
                $statusMessage.html('<div class="error notice notice-error"><p>AJAX Error: ' + error + '</p></div>').addClass('error');
            }
        });
    },
    
    /**
     * Get login settings via AJAX
     */
    getLoginSettings: function() {
        const $ = jQuery;
        
        $.ajax({
            url: this.config.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpca_get_login_settings',
                nonce: this.config.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    const settings = response.data;
                    
                    // Update form fields
                    if (settings.login_style) $('#wpca-login-style').val(settings.login_style);
                    if (settings.login_logo_url) $('#wpca-login-logo-url').val(settings.login_logo_url);
                    if (settings.login_background_url) $('#wpca-login-background-url').val(settings.login_background_url);
                    if (settings.login_custom_css) $('#wpca-login-custom-css').val(settings.login_custom_css);
                    
                    // Update login elements checkboxes
                    if (settings.login_elements) {
                        $.each(settings.login_elements, function(element, value) {
                            if ($('#' + element).length) {
                                $('#' + element).prop('checked', value === 1);
                            }
                        });
                    }
                    
                    // Update preview
                    WPCA.login.updatePreview();
                }
            },
            error: function(xhr, status, error) {
                console.error('WPCA Error: Failed to fetch login settings', error);
            }
        });
    },
    
    /**
     * Reset login settings via AJAX
     */
    resetLoginSettings: function() {
        const $ = jQuery;
        const $resetButton = $('#wpca-reset-login-settings');
        const $statusMessage = $resetButton.closest('form').find('.wpca-status-message');
        
        // Show loading state
        $resetButton.prop('disabled', true).data('original-text', $resetButton.html());
        $resetButton.html('<span class="spinner is-active"></span> Resetting...');
        $statusMessage.html('').removeClass('success error');
        
        // Send AJAX request
        $.ajax({
            url: this.config.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpca_reset_login_settings',
                nonce: this.config.nonce
            },
            success: function(response) {
                $resetButton.prop('disabled', false).html($resetButton.data('original-text'));
                
                if (response.success) {
                    // Clear form fields
                    $('#wpca-login-logo-url').val('');
                    $('#wpca-login-background-url').val('');
                    $('#wpca-login-custom-css').val('');
                    $('#wpca-login-style').val('default');
                    
                    // Reset checkboxes
                    $('#wpca-login-elements-tabs input[type="checkbox"]').prop('checked', true);
                    
                    // Clear previews
                    $('#wpca-login-logo-preview').html('<p>No logo selected</p>');
                    $('#wpca-login-background-preview').html('<p>No background selected</p>');
                    
                    $statusMessage.html('<div class="updated notice notice-success"><p>Login settings reset to default!</p></div>').addClass('success');
                    
                    // Update preview
                    WPCA.login.updatePreview();
                } else {
                    $statusMessage.html('<div class="error notice notice-error"><p>Failed to reset settings. ' + (response.data.message || '') + '</p></div>').addClass('error');
                }
                
                // Clear status message after 5 seconds
                setTimeout(function() {
                    $statusMessage.html('').removeClass('success error');
                }, 5000);
            },
            error: function(xhr, status, error) {
                $resetButton.prop('disabled', false).html($resetButton.data('original-text'));
                $statusMessage.html('<div class="error notice notice-error"><p>AJAX Error: ' + error + '</p></div>').addClass('error');
            }
        });
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
            .text('Show Login Form')
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
        // Load current settings on init
        WPCA.login.getLoginSettings();
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