/**
 * WP Clean Admin - Tab Navigation Module
 * 处理设置页面选项卡切换功能
 */

// Ensure WPCA namespace exists
window.WPCA = window.WPCA || {};

// Tab navigation module
WPCA.tabs = {
    init: function() {
        const $ = jQuery;
        
        // 确保DOM完全就绪
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded for WPCA tabs');
            return;
        }
        
        // 使用选择器重新获取元素，确保获取最新的DOM
        const $tabs = $('.wpca-tab');
        const $tabContents = $('.wpca-tab-content');
        const $currentTabInput = $('#wpca-current-tab');

        // If there are no tabs, return directly
        if ($tabs.length === 0) {
            console.debug('No WPCA tabs found');
            return;
        }

        console.debug(`Found ${$tabs.length} WPCA tabs`);

        // Get current active tab
        let activeTab = $currentTabInput.val() || $tabs.first().data('tab');
        if (!$("#" + activeTab).length) {
            activeTab = $tabs.first().data('tab');
        }

        // Set initial active tab
        $tabs.removeClass('active');
        $tabContents.removeClass('active');
        $(`.wpca-tab[data-tab="${activeTab}"]`).addClass('active');
        $(`#${activeTab}`).addClass('active');

        // 强制移除所有点击事件处理程序以确保不会有重复绑定
        $tabs.unbind('click');
        $tabs.off('click.wpca');
        
        // 为每个选项卡单独绑定点击事件，确保不会漏掉任何选项卡
        $tabs.each(function() {
            const $this = $(this);
            const tabId = $this.data('tab');
            
            // 确保tabId存在
            if (!tabId || !$("#" + tabId).length) {
                console.warn(`Tab with data-tab="${tabId}" has no corresponding content`);
                return true; // 继续下一个循环
            }
            
            // 为每个选项卡添加点击事件
            $this.on('click.wpca', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Update active status
                $tabs.removeClass('active');
                $tabContents.removeClass('active');

                $this.addClass('active');
                $(`#${tabId}`).addClass('active');
                $currentTabInput.val(tabId);
                
                // Trigger custom event so other scripts can respond to tab switching
                $(document).trigger('wpca.tab.changed', [tabId]);
            });
        });
        
        // 额外添加直接针对视觉样式选项卡的点击事件，确保它能正常工作
        const $visualStyleTab = $('.wpca-tab[data-tab="tab-visual-style"]');
        if ($visualStyleTab.length) {
            $visualStyleTab.css('cursor', 'pointer'); // 确保鼠标指针显示为手形
            
            // 为视觉样式选项卡添加额外的点击事件处理
            $visualStyleTab.on('click.wpca-visual', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const tabId = 'tab-visual-style';
                $tabs.removeClass('active');
                $tabContents.removeClass('active');
                
                $visualStyleTab.addClass('active');
                $(`#${tabId}`).addClass('active');
                $currentTabInput.val(tabId);
                
                $(document).trigger('wpca.tab.changed', [tabId]);
            });
        }
    },
    
    // Reinitialize tabs
    reinit: function() {
        this.init();
    }
};

// 多方式初始化以确保选项卡正确加载
// 1. 页面加载完成后初始化
jQuery(document).ready(function() {
    // 使用更长的延迟确保DOM完全加载和所有内容渲染完成
    setTimeout(function() {
        if (typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
            WPCA.tabs.init();
        }
    }, 300);
});

// 2. 监听自定义初始化事件
jQuery(document).on('wpca.init.tabs', function() {
    setTimeout(function() {
        if (typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
            WPCA.tabs.init();
        }
    }, 100);
});

// 3. 添加DOM变化监听，确保在动态内容加载后重新初始化
jQuery(document).on('DOMContentLoaded', function() {
    if (typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
        WPCA.tabs.init();
    }
});

// 4. 窗口加载完成后再次初始化，作为最后保障
jQuery(window).on('load', function() {
    if (typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
        WPCA.tabs.init();
    }
});