/**
 * WP Clean Admin - 权限管理JavaScript
 * 
 * 提供前端权限检查功能，允许基于用户权限显示或隐藏UI元素
 */

(function($) {
    'use strict';

    // 初始化WPCA权限对象
    window.WPCA = window.WPCA || {};
    window.WPCA.permissions = {
        /**
         * 存储用户权限
         */
        userCaps: {},

        /**
         * 初始化权限系统
         */
        init: function() {
            // 从全局变量获取用户权限
            if (typeof wpca_admin !== 'undefined' && wpca_admin.user_capabilities) {
                this.userCaps = wpca_admin.user_capabilities;
            }

            // 初始化UI权限
            this.setupUI();
            
            // 添加权限检查到WPCA核心
            if (window.WPCA.core) {
                window.WPCA.core.hasPermission = this.hasPermission.bind(this);
                window.WPCA.core.checkPermission = this.checkPermission.bind(this);
            }
        },

        /**
         * 检查用户是否有指定权限
         * 
         * @param {string} capability 权限名称
         * @return {boolean} 是否有权限
         */
        hasPermission: function(capability) {
            // 默认检查管理选项权限
            if (capability === 'manage_options' && this.userCaps.can_manage_options === true) {
                return true;
            }
            
            // 检查特定权限
            if (this.userCaps[capability] === true) {
                return true;
            }
            
            return false;
        },

        /**
         * 通过AJAX检查权限（用于需要实时检查的场景）
         * 
         * @param {string} capability 权限名称
         * @param {function} callback 回调函数
         */
        checkPermission: function(capability, callback) {
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_check_permission',
                    capability: capability,
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        callback(response.data.has_permission);
                    } else {
                        callback(false);
                    }
                },
                error: function() {
                    callback(false);
                }
            });
        },

        /**
         * 根据权限设置UI元素
         */
        setupUI: function() {
            // 隐藏没有权限的UI元素
            $('.wpca-requires-permission').each(function() {
                var $element = $(this);
                var requiredPermission = $element.data('permission');
                
                if (requiredPermission && !window.WPCA.permissions.hasPermission(requiredPermission)) {
                    $element.hide();
                }
            });
            
            // 禁用没有权限的按钮
            $('.wpca-button-requires-permission').each(function() {
                var $button = $(this);
                var requiredPermission = $button.data('permission');
                
                if (requiredPermission && !window.WPCA.permissions.hasPermission(requiredPermission)) {
                    $button.prop('disabled', true)
                           .addClass('wpca-disabled')
                           .attr('title', '权限不足');
                }
            });
        }
    };

    // 在文档加载完成后初始化
    $(document).ready(function() {
        window.WPCA.permissions.init();
    });

})(jQuery);