/**
 * WP Clean Admin - 标签导航模块
 */

// 确保WPCA命名空间存在
window.WPCA = window.WPCA || {};

// 标签导航模块
WPCA.tabs = {
    init: function() {
        const $ = jQuery;
        const $tabs = $('.wpca-tab');
        const $tabContents = $('.wpca-tab-content');
        const $currentTabInput = $('#wpca-current-tab');

        // 如果没有选项卡，直接返回
        if ($tabs.length === 0) {
            return;
        }

        // 获取当前活动选项卡
        let activeTab = $currentTabInput.val() || $tabs.first().data('tab');
        if (!$("#" + activeTab).length) {
            activeTab = $tabs.first().data('tab');
        }

        // 设置初始活动选项卡
        $tabs.removeClass('active');
        $tabContents.removeClass('active');
        $(`.wpca-tab[data-tab="${activeTab}"]`).addClass('active');
        $(`#${activeTab}`).addClass('active');

        // 解绑之前的点击事件，防止重复绑定
        $tabs.off('click.wpca');
        
        // 绑定点击事件
        $tabs.on('click.wpca', function(e) {
            e.preventDefault();
            const tabId = $(this).data('tab');
            
            // 更新活动状态
            $tabs.removeClass('active');
            $tabContents.removeClass('active');

            $(this).addClass('active');
            $(`#${tabId}`).addClass('active');
            $currentTabInput.val(tabId);
            
            // 触发自定义事件，以便其他脚本可以响应选项卡切换
            $(document).trigger('wpca.tab.changed', [tabId]);
        });
    },
    
    // 重新初始化选项卡
    reinit: function() {
        this.init();
    }
};

// 页面加载完成后初始化
jQuery(document).ready(function($) {
    // 使用稍微延迟的方式初始化，确保DOM完全加载
    setTimeout(function() {
        if (typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
            WPCA.tabs.init();
        }
    }, 100);
});

// 也监听自定义的初始化事件
jQuery(document).on('wpca.init.tabs', function() {
    if (typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
        WPCA.tabs.init();
    }
});