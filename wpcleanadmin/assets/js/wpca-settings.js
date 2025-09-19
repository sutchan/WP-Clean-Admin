/**
 * 设置页面功能脚本
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // 初始化颜色选择器
        $('.wpca-color-picker').wpColorPicker();
        
        // 标签切换
        $('.wpca-tab').on('click', function() {
            var tab = $(this).data('tab');
            
            // 更新活动标签
            $('.wpca-tab').removeClass('active');
            $(this).addClass('active');
            
            // 更新标签内容
            $('.wpca-tab-content').removeClass('active');
            $('#' + tab).addClass('active');
            
            // 更新隐藏字段
            $('#wpca-current-tab').val(tab);
            
            // 保存用户偏好
            saveTabPreference(tab);
        });
        
        // 保存标签偏好
        function saveTabPreference(tab) {
            $.ajax({
                url: wpca_settings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_save_tab_preference',
                    tab: tab,
                    _ajax_nonce: wpca_settings._wpnonce
                }
            });
        }
        
        // 登录样式选择
        $('.wpca-login-style-preview').on('click', function() {
            var $item = $(this).closest('.wpca-login-style-item');
            var style = $item.data('style');
            
            $('.wpca-login-style-item').removeClass('active');
            $item.addClass('active');
            $('#wpca_login_style').val(style);
        });
        
        // 媒体上传按钮
        $('.wpca-media-upload-button').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $input = $button.siblings('input');
            var $preview = $button.siblings('.wpca-media-preview');
            
            var frame = wp.media({
                title: wpca_settings.media_title,
                button: {
                    text: wpca_settings.media_button
                },
                multiple: false
            });
            
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.url);
                
                if (attachment.type === 'image') {
                    $preview.attr('src', attachment.url).show();
                } else {
                    $preview.hide();
                }
            });
            
            frame.open();
        });
        
        // 移除媒体按钮
        $('.wpca-media-remove-button').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $input = $button.siblings('input');
            var $preview = $button.siblings('.wpca-media-preview');
            
            $input.val('');
            $preview.hide();
        });
    });
})(jQuery);