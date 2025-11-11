/**
 * WP Clean Admin - Tab Navigation Module
 * 处理设置页面选项卡切换功能
 */

// Ensure WPCA namespace exists
window.WPCA = window.WPCA || {};

// Tab navigation module
WPCA.tabs = {
    init: function() {
        console.log('WPCA Tabs: Initializing...');
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

        console.log(`WPCA Tabs: Found ${$tabs.length} tabs`);
        
        // 为所有选项卡设置cursor为pointer，确保用户知道它们是可点击的
        $tabs.css({ 
            cursor: 'pointer',
            userSelect: 'none',
            position: 'relative',
            zIndex: 10 // 确保它们在其他元素之上
        });

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
        $tabs.off('click.wpca-visual');
        
        // 为每个选项卡单独绑定点击事件，确保不会漏掉任何选项卡
        $tabs.each(function() {
            const $this = $(this);
            const tabId = $this.data('tab');
            
            console.log(`WPCA Tabs: Processing tab with data-tab="${tabId}"`);
            
            // 确保tabId存在
            if (!tabId || !$("#" + tabId).length) {
                console.warn(`Tab with data-tab="${tabId}" has no corresponding content`);
                return true; // 继续下一个循环
            }
            
            // 为每个选项卡添加点击事件
            $this.on('click.wpca', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log(`WPCA Tabs: Tab clicked: ${tabId}`);
                
                // Update active status
                $tabs.removeClass('active');
                $tabContents.removeClass('active');

                $this.addClass('active');
                $(`#${tabId}`).addClass('active');
                $currentTabInput.val(tabId);
                
                // Trigger custom event so other scripts can respond to tab switching
                $(document).trigger('wpca.tab.changed', [tabId]);
            });
            
            // 添加鼠标悬停效果，帮助用户识别可点击元素
            $this.on('mouseenter', function() {
                $(this).css('opacity', '0.8');
            }).on('mouseleave', function() {
                $(this).css('opacity', '1');
            });
        });
        
        // 额外添加委托事件处理，确保动态添加的选项卡也能正常工作
        $(document).off('click.wpca-delegate', '.wpca-tab');
        $(document).on('click.wpca-delegate', '.wpca-tab', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $this = $(this);
            const tabId = $this.data('tab');
            
            if (!tabId || !$("#" + tabId).length) {
                return;
            }
            
            console.log(`WPCA Tabs: Delegate click detected for tab: ${tabId}`);
            
            // 手动触发点击事件
            $this.trigger('click.wpca');
        });
        
        console.log('WPCA Tabs: Initialization complete');
    },
    
    // Reinitialize tabs
    reinit: function() {
        console.log('WPCA Tabs: Reinitializing...');
        this.init();
    }
};

// 多方式初始化以确保选项卡正确加载
// 1. 页面加载完成后初始化
jQuery(document).ready(function() {
    console.log('WPCA Tabs: Document ready, initializing...');
    // 使用更长的延迟确保DOM完全加载和所有内容渲染完成
    setTimeout(function() {
        if (typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
            WPCA.tabs.init();
        }
    }, 300);
});

// 2. 监听自定义初始化事件
jQuery(document).on('wpca.init.tabs', function() {
    console.log('WPCA Tabs: Custom init event received');
    setTimeout(function() {
        if (typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
            WPCA.tabs.init();
        }
    }, 100);
});

// 3. 添加DOM变化监听，确保在动态内容加载后重新初始化
jQuery(document).on('DOMContentLoaded', function() {
    console.log('WPCA Tabs: DOMContentLoaded event received');
    if (typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
        WPCA.tabs.init();
    }
});

// 4. 窗口加载完成后再次初始化，作为最后保障
jQuery(window).on('load', function() {
    console.log('WPCA Tabs: Window load event received');
    if (typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
        WPCA.tabs.init();
    }
});

// 5. 添加MutationObserver以监听DOM变化，确保动态添加的选项卡也能正常工作
if (typeof MutationObserver !== 'undefined') {
    console.log('WPCA Tabs: Setting up MutationObserver');
    new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                // 检查是否添加了选项卡相关元素
                const hasTabs = $(mutation.addedNodes).find('.wpca-tabs, .wpca-tab').length > 0;
                if (hasTabs && typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
                    console.log('WPCA Tabs: DOM changed, reinitializing...');
                    WPCA.tabs.init();
                }
            }
        });
    }).observe(document.body, {
        childList: true,
        subtree: true
    });
}