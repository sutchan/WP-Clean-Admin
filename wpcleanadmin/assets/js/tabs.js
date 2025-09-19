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

        let currentTab = $currentTabInput.val() || $tabs.first().data('tab');
        if (!$("#" + currentTab).length) {
            currentTab = $tabs.first().data('tab');
        }

        $tabs.removeClass('active');
        $tabContents.removeClass('active');
        $(`.wpca-tab[data-tab="${currentTab}"]`).addClass('active');
        $(`#${currentTab}`).addClass('active');

        $tabs.on('click', function(e) {
            e.preventDefault();
            const tabId = $(this).data('tab');
            $tabs.removeClass('active');
            $tabContents.removeClass('active');

            $(this).addClass('active');
            $(`#${tabId}`).addClass('active');
            $currentTabInput.val(tabId);
        });
    }
};

// 页面加载完成后初始化
jQuery(document).ready(function() {
    WPCA.tabs.init();
});