/**
 * WP Clean Admin - 主入口文件
 */

// 确保WPCA命名空间存在
window.WPCA = window.WPCA || {};

// 主入口模块
WPCA.main = {
    init: function() {
        'use strict';
        
        // 初始化检查
        if (!WPCA.core.initCheck()) return;
        
        // 调试模式
        window.wpca.debug = typeof wpca_admin !== 'undefined' && wpca_admin.debug || false;
        
        // 注意：各模块现在会在自己的文件中自动初始化
        // 这里不需要再调用它们的初始化方法
    }
};

// 页面加载完成后初始化
jQuery(document).ready(function() {
    WPCA.main.init();
});