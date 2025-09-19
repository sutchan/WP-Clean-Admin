/**
 * WP Clean Admin - 登录页面模块 (合并优化版)
 */

// 确保WPCA命名空间存在
window.WPCA = window.WPCA || {};

// 登录页面模块
WPCA.loginPage = {
    /**
     * 更新登录页面预览
     */
    updateLoginPreview: function(style) {
        const $ = jQuery;
        const preview = $('.wpca-login-preview-content');
        preview.removeClass('default-preview modern-preview minimal-preview dark-preview gradient-preview custom-preview');
        preview.addClass(style + '-preview');

        switch(style) {
            case 'default':
                preview.find('.wpca-login-preview-logo').css('background-image', 'url(data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 84 84"><path fill="%232271b1" d="M42,0C18.8,0,0,18.8,0,42s18.8,42,42,42s42-18.8,42-42S65.2,0,42,0z M42,64c-12.2,0-22-9.8-22-22s9.8-22,22-22 s22,9.8,22,22S54.2,64,42,64z"/></svg>)');
                preview.css('background-color', '#f1f1f1');
                break;
            case 'modern':
                preview.find('.wpca-login-preview-logo').css('background-image', 'url(data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 84 84"><path fill="%234A90E2" d="M42,0C18.8,0,0,18.8,0,42s18.8,42,42,42s42-18.8,42-42S65.2,0,42,0z M42,64c-12.2,0-22-9.8-22-22s9.8-22,22-22 s22,9.8,22,22S54.2,64,42,64z"/></svg>)');
                preview.css('background-color', '#f8f9fa');
                break;
            case 'minimal':
                preview.find('.wpca-login-preview-logo').css('background-image', '');
                preview.css('background-color', '#fff');
                break;
            case 'dark':
                preview.find('.wpca-login-preview-logo').css('background-image', 'url(data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 84 84"><path fill="%23fff" d="M42,0C18.8,0,0,18.8,0,42s18.8,42,42,42s42-18.8,42-42S65.2,0,42,0z M42,64c-12.2,0-22-9.8-22-22s9.8-22,22-22 s22,9.8,22,22S54.2,64,42,64z"/></svg>)');
                preview.css('background-color', '#222');
                break;
            case 'gradient':
                preview.find('.wpca-login-preview-logo').css('background-image', 'url(data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 84 84"><path fill="%23fff" d="M42,0C18.8,0,0,18.8,0,42s18.8,42,42,42s42-18.8,42-42S65.2,0,42,0z M42,64c-12.2,0-22-9.8-22-22s9.8-22,22-22 s22,9.8,22,22S54.2,64,42,64z"/></svg>)');
                preview.css('background', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)');
                break;
            case 'custom':
                const logoUrl = $('input[name="wpca_settings[login_logo]"]').val();
                const bgUrl = $('input[name="wpca_settings[login_background]"]').val();
                
                if (logoUrl) {
                    preview.find('.wpca-login-preview-logo').css('background-image', `url(${logoUrl})`);
                }
                
                if (bgUrl) {
                    preview.css('background-image', `url(${bgUrl})`);
                }
                break;
        }
    },

    /**
     * 初始化登录页面功能
     */
    init: function() {
        const $ = jQuery;
        const self = this;
        
        // 监听样式选择变化
        $('input[name="wpca_settings[login_style]"]').on('change', function() {
            self.updateLoginPreview($(this).val());
        });

        // 媒体上传功能
        $('.wpca-upload-button').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            const targetId = button.data('target');
            const field = $('#' + targetId);
            const preview = $('#' + targetId + '-preview');

            const frame = wp.media({
                title: window.wpca.media_title || '选择或上传媒体',
                button: { text: window.wpca.media_button || '使用此媒体' },
                multiple: false
            });

            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                field.val(attachment.url);
                preview.find('img').attr('src', attachment.url);
                preview.show();
                
                if ($('input[name="wpca_settings[login_style]"]:checked').val() === 'custom') {
                    self.updateLoginPreview('custom');
                }
            });

            frame.open();
        });

        // 移除媒体
        $('.wpca-remove-button').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            const targetId = button.data('target');
            const field = $('#' + targetId);
            const preview = $('#' + targetId + '-preview');
            
            field.val('');
            preview.hide();
            
            if ($('input[name="wpca_settings[login_style]"]:checked').val() === 'custom') {
                self.updateLoginPreview('custom');
            }
        });

        // 初始化预览
        if ($('#tab-login').length && $('input[name="wpca_settings[login_style]"]:checked').length) {
            self.updateLoginPreview($('input[name="wpca_settings[login_style]"]:checked').val());
        }
    }
};

// 页面加载完成后初始化
jQuery(document).ready(function() {
    WPCA.loginPage.init();
});