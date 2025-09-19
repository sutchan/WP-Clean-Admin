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
        debug: false
    },

    // 初始化检查
    initCheck: function() {
        if (!this.wpca.ajaxurl || !this.wpca.nonce) {
            console.error('WPCA Script Error: Required settings missing');
            jQuery('<div class="notice notice-error"><p><strong>WP Clean Admin Error:</strong> Required JavaScript settings could not be loaded.</p></div>')
                .prependTo('.wrap');
            return false;
        }
        return true;
    },

    /**
     * 显示通知
     * @param {string} message - 要显示的消息
     * @param {string} type - 'success'或'error'
     */
    showNotice: function(message, type = 'success') {
        const $ = jQuery;
        $('.wpca-notice').remove();
        const noticeClass = `wpca-notice notice notice-${type} is-dismissible`;
        const $notice = $(`<div class="${noticeClass}"><p>${message}</p></div>`);
        $notice.insertAfter($('.wrap h1').first());

        setTimeout(() => $notice.fadeOut(500, () => $notice.remove()), 
            type === 'success' ? 3000 : 5000);

        $notice.on('click', '.notice-dismiss', () => $notice.remove());
    }
};

// 为了向后兼容，保留一些全局变量
window.wpca = WPCA.core.wpca;