/**
 * WP Clean Admin - 登录页面前台脚本
 */

// 确保WPCA命名空间存在
window.WPCA = window.WPCA || {};

// 登录页面前台模块
WPCA.loginFrontend = {
    init: function() {
        const $ = jQuery;
        
        // Cache DOM elements
        const $body = $('body.login');
        const $logo = $('#login h1 a');
        const $form = $('form#loginform');
        
        // Apply login style based on settings
        if (window.wpcaLoginVars) {
            const vars = window.wpcaLoginVars;
            const style = vars.loginStyle || '';
            const logo = vars.loginLogo ? encodeURI(vars.loginLogo) : '';
            const background = vars.loginBackground ? encodeURI(vars.loginBackground) : '';
            const controls = vars.elementControls || {};
            
            $body.addClass('wpca-login-' + style);
            
            // Apply custom logo if set
            if (style === 'custom' && logo) {
                $logo.css('background-image', 'url(' + logo + ')');
            }
            
            // Apply custom background if set
            if (style === 'custom' && background) {
                $body.css('background-image', 'url(' + background + ')');
            }

            // Apply element controls
            if (controls.show_language_switcher === '0') {
                $('.language-switcher').hide();
            }
            
            if (controls.show_back_to_site === '0') {
                $('#backtoblog').hide();
            }
            
            if (controls.show_remember_me === '0') {
                $('.forgetmenot').hide();
            }
            
            if (controls.show_login_form === '0') {
                $form.find('p:not(.submit)').hide();
            }

            if (controls.show_register_link === '0') {
                $('#nav').hide();
            }
        }
        
        // Theme preset functionality
        $(document).on('change', 'select[name="wpca_settings[theme_style]"]', function() {
            const preset = $(this).val();
            if (preset && preset !== 'custom' && window.wpcaThemePresets && wpcaThemePresets[preset]) {
                const presetData = wpcaThemePresets[preset];
                $('input[name="wpca_settings[primary_color]"]').val(presetData.primary_color || '').trigger('change');
                $('input[name="wpca_settings[background_color]"]').val(presetData.background_color || '').trigger('change');
                $('input[name="wpca_settings[text_color]"]').val(presetData.text_color || '').trigger('change');
            }
        });
    }
};

// 页面加载完成后初始化
jQuery(document).ready(function() {
    WPCA.loginFrontend.init();
});