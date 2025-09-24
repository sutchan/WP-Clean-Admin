<?js wpcleanadmin/assets/js/main.js
/**
 * WP Clean Admin - 主入口文件
 */

// 确保WPCA命名空间存在
window.WPCA = window.WPCA || {};

// 主入口模块
WPCA.main = {
    init: function() {
        'use strict';
        
        try {
            // 初始化检查
            if (!WPCA.core.initCheck()) return;
            
            // 调试模式
            WPCA.debug = typeof wpca_admin !== 'undefined' && wpca_admin.debug || false;
            
            // 注意：各模块现在会在自己的文件中自动初始化
            // 这里不需要再调用它们的初始化方法
            
            if (WPCA.debug) {
                    WPCA.core.logDebug('WPCA Main: 初始化完成');
                }
        } catch (error) {
            console.error('WPCA Main: 初始化错误', error);
            WPCA.core.showNotice('error', '主模块初始化失败: ' + error.message);
            }
        }
};

// 页面加载完成后初始化
jQuery(document).ready(function() {
    WPCA.main.init();
});