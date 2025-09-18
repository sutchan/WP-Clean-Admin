jQuery(document).ready(function($) {
    // Apply login style based on settings
    if (typeof wpcaLoginVars !== 'undefined') {
        var style = wpcaLoginVars.loginStyle;
        var logo = wpcaLoginVars.loginLogo;
        var background = wpcaLoginVars.loginBackground;
        var controls = wpcaLoginVars.elementControls;
        
        $('body.login').addClass('wpca-login-' + style);
        
        // Apply custom logo if set
        if (style === 'custom' && logo) {
            $('#login h1 a').css('background-image', 'url(' + logo + ')');
        }
        
        // Apply custom background if set
        if (style === 'custom' && background) {
            $('body.login').css('background-image', 'url(' + background + ')');
        }

        // Apply element controls
        if (controls) {
            // Language switcher
            if (controls.show_language_switcher === '0') {
                $('.language-switcher').hide();
            }
            
            // Back to site link
            if (controls.show_back_to_site === '0') {
                $('#backtoblog').hide();
            }
            
            // Remember me checkbox
            if (controls.show_remember_me === '0') {
                $('.forgetmenot').hide();
            }
            
            // Login form (hide everything except submit button)
            if (controls.show_login_form === '0') {
                $('form#loginform p:not(.submit)').hide();
            }

            // Backend login link
            if (controls.show_backend_login === '0') {
                $('#nav').hide();
            }
        }
    }
    
    // Theme preset functionality
    $(document).on('change', 'select[name="wpca_settings[theme_style]"]', function() {
        var preset = $(this).val();
        if (preset !== 'custom' && typeof wpcaThemePresets !== 'undefined' && wpcaThemePresets[preset]) {
            $('input[name="wpca_settings[primary_color]"]').val(wpcaThemePresets[preset].primary_color).trigger('change');
            $('input[name="wpca_settings[background_color]"]').val(wpcaThemePresets[preset].background_color).trigger('change');
            $('input[name="wpca_settings[text_color]"]').val(wpcaThemePresets[preset].text_color).trigger('change');
        }
    });
});