/**
 * WP Clean Admin - Core Module
 * 包含全局变量、通知功能和基础工具
 */

// 创建全局命名空间
window.WPCA = window.WPCA || {};

// 全局变量
WPCA.core = {
    wpca: {
        ajaxurl: typeof wpca_admin !== 'undefined' ? wpca_admin.ajaxurl : '',
        nonce: typeof wpca_admin !== 'undefined' ? wpca_admin.nonce : '',
        debug: typeof wpca_admin !== 'undefined' ? wpca_admin.debug : false
    },

    // 初始化检查
    initCheck: function() {
        
        if (!this.wpca.ajaxurl || !this.wpca.nonce) {
            console.error('WPCA 脚本错误: 缺少必要设置');
            jQuery('<div class="notice notice-error"><p><strong>WP Clean Admin 错误:</strong> 无法加载必要的 JavaScript 设置。</p></div>')
                .prependTo('.wrap');
            return false;
        }
        return true;
    },


    /**
     * 初始化核心功能
     */
    init: function() {
        this.initCheck();
    }
};

// 为了向后兼容，保留一些全局变量
window.wpca = WPCA.core.wpca;

// 页面加载完成后初始化核心功能
jQuery(document).ready(function() {
    WPCA.core.init();
});