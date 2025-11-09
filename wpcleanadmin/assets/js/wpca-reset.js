(function($) {
    'use strict';
    
    // 确保WPCA对象存在
    if (typeof WPCA === 'undefined') {
        window.WPCA = {};
    }
    
    // 重置功能模块
    WPCA.reset = {
        
        /**
         * 初始化重置功能
         */
        init: function() {
            // 添加重置按钮事件监听器
            this.addResetButtonListeners();
        },
        
        /**
         * 为重置按钮添加点击事件监听器
         */
        addResetButtonListeners: function() {
            // 常规设置重置按钮
            $(document).on('click', '#wpca-reset-general', function(e) {
                e.preventDefault();
                WPCA.reset.confirmReset('general');
            });
            
            // 视觉样式重置按钮
            $(document).on('click', '#wpca-reset-visual', function(e) {
                e.preventDefault();
                WPCA.reset.confirmReset('visual');
            });
            
            // 登录页面重置按钮
            $(document).on('click', '#wpca-reset-login', function(e) {
                e.preventDefault();
                WPCA.reset.confirmReset('login');
            });
            
            // 菜单设置重置按钮
            $(document).on('click', '#wpca-reset-menu', function(e) {
                e.preventDefault();
                WPCA.reset.confirmReset('menu');
            });
        },
        
        /**
         * 显示重置确认对话框
         */
        confirmReset: function(tab) {
            var confirmText = '';
            
            // 根据不同的标签页设置不同的确认文本
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
                case 'menu':
                    confirmText = wpca_admin.menu_reset_confirm || __('Are you sure you want to reset all menu settings to default?', 'wp-clean-admin');
                    break;
                default:
                    confirmText = wpca_admin.reset_confirm || __('Are you sure you want to reset these settings to default?', 'wp-clean-admin');
            }
            
            // 显示确认对话框
            if (confirm(confirmText)) {
                WPCA.reset.performReset(tab);
            }
        },
        
        /**
         * 执行重置操作
         */
        performReset: function(tab) {
            // 获取当前点击的按钮
            var $button = $('#wpca-reset-' + tab);
            var originalText = $button.html();
            
            // 显示加载状态
            $button.html('<span class="dashicons dashicons-update spin" style="vertical-align: middle; margin-right: 5px;"></span> ' + (wpca_admin.resetting_text || __('Resetting...', 'wp-clean-admin')));
            $button.prop('disabled', true);
            
            // 发送AJAX请求
            $.ajax({
                url: wpca_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpca_reset_tab_settings',
                    tab: tab,
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    // 恢复按钮状态
                    $button.html(originalText);
                    $button.prop('disabled', false);
                    
                    // 检查响应
                    if (response.success) {
                        // 显示成功通知
                        if (typeof WPCA.core !== 'undefined' && typeof WPCA.core.showNotice === 'function') {
                            WPCA.core.showNotice('success', (wpca_admin.reset_text || __('Reset Defaults', 'wp-clean-admin')) + ' ' + (wpca_admin.reset_successful_text || __('successful', 'wp-clean-admin')));
                        } else {
                            alert((wpca_admin.reset_text || __('Reset Defaults', 'wp-clean-admin')) + ' ' + (wpca_admin.reset_successful_text || __('successful', 'wp-clean-admin')));
                        }
                        
                        // 刷新页面以显示重置后的设置
                        location.reload();
                    } else {
                        // 显示错误通知
                        var errorMessage = response.data && response.data.message ? response.data.message : (wpca_admin.reset_failed || __('Reset failed. Please try again.', 'wp-clean-admin'));
                        if (typeof WPCA.core !== 'undefined' && typeof WPCA.core.showNotice === 'function') {
                            WPCA.core.showNotice('error', errorMessage);
                        } else {
                            alert(errorMessage);
                        }
                    }
                },
                error: function() {
                    // 恢复按钮状态
                    $button.html(originalText);
                    $button.prop('disabled', false);
                    
                    // 显示错误通知
                    if (typeof WPCA.core !== 'undefined' && typeof WPCA.core.showNotice === 'function') {
                        WPCA.core.showNotice('error', wpca_admin.reset_failed || __('Reset failed. Please try again.', 'wp-clean-admin'));
                    } else {
                        alert(wpca_admin.reset_failed || __('Reset failed. Please try again.', 'wp-clean-admin'));
                    }
                }
            });
        }
    };
    
    // 在文档加载完成后初始化重置功能
    $(document).ready(function() {
        WPCA.reset.init();
    });
})(jQuery);