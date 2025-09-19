/**
 * WP Clean Admin - 菜单切换模块
 */

// 确保WPCA命名空间存在
window.WPCA = window.WPCA || {};

// 菜单切换模块
WPCA.menuToggle = {
    /**
     * 更新菜单可见性
     */
    updateMenuVisibility: function(slug, isVisible) {
        const $ = jQuery;
        if (!slug) return false;
        
        isVisible = !!isVisible;
        let $menuItem = null;
        
        const possibleIDs = [
            `#toplevel_page_${slug}`,
            `#menu-${slug}`,
            `#menu-posts-${slug}`
        ];
        
        for (const id of possibleIDs) {
            const $item = $(id);
            if ($item.length) {
                $menuItem = $item;
                break;
            }
        }
        
        if (!$menuItem || !$menuItem.length) {
            $menuItem = $(`#adminmenu a[href$="${slug}"], #adminmenu a[href*="page=${slug}"]`).closest('li');
        }
        
        if ($menuItem && $menuItem.length) {
            $menuItem.css('transition', 'opacity 0.3s');
            
            if (isVisible) {
                $menuItem.show().css('opacity', 1);
            } else {
                $menuItem.css('opacity', 0).delay(300).hide(0);
            }
            
            $menuItem.toggleClass('wpca-hidden-menu', !isVisible);
            
            if (window.wpca.debug) {
                console.log(`WPCA: Menu "${slug}" visibility set to ${isVisible ? 'visible' : 'hidden'}`);
            }
            
            return true;
        }
        
        console.warn(`WPCA: Could not find menu item with slug "${slug}"`);
        return false;
    },

    init: function() {
        const $ = jQuery;
        const self = this;
        
        $(document).on('change', '.wpca-menu-toggle-switch input[type="checkbox"]', function() {
            const $checkbox = $(this);
            const $switch = $checkbox.closest('.wpca-menu-toggle-switch');
            const slug = $checkbox.data('menu-slug');
            const isChecked = $checkbox.is(':checked');
            const state = isChecked ? 1 : 0;

            if (!slug) {
                console.error('WPCA Error: Menu slug not found');
                return;
            }

            self.updateMenuVisibility(slug, state);

            $.ajax({
                url: window.wpca.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_toggle_menu',
                    slug: slug,
                    state: state,
                    nonce: window.wpca.nonce
                },
                beforeSend: () => $switch.addClass('loading'),
                complete: () => $switch.removeClass('loading')
            }).done(response => {
                if (response?.success) {
                    WPCA.core.showNotice('设置已保存', 'success');
                } else {
                    const errorMsg = response?.data?.message || '发生未知错误';
                    WPCA.core.showNotice(errorMsg, 'error');
                    $checkbox.prop('checked', !isChecked);
                    self.updateMenuVisibility(slug, !state);
                }
            }).fail(() => {
                WPCA.core.showNotice('请求失败，请检查网络连接', 'error');
                $checkbox.prop('checked', !isChecked);
                self.updateMenuVisibility(slug, !state);
            });
        });

        // 菜单切换功能
        $('#wpca-menu-toggle').on('change', function() {
            const isEnabled = $(this).is(':checked');
            
            $('.wpca-menu-sortable, .wpca-menu-order-wrapper').stop(true, true).animate({
                opacity: isEnabled ? 1 : 0
            }, 300, function() {
                $(this).toggle(isEnabled);
            });
            
            $.post(window.wpca.ajaxurl, {
                action: 'wpca_toggle_menu_customization',
                enabled: isEnabled ? 1 : 0,
                nonce: window.wpca.nonce
            }, response => {
                if (!response.success) {
                    console.error('保存菜单切换状态失败:', response.data);
                }
            });
        });
    }
};

// 页面加载完成后初始化
jQuery(document).ready(function() {
    WPCA.menuToggle.init();
});