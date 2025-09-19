/**
 * WP Clean Admin - 菜单切换模块
 */

// 立即初始化WPCA核心对象
(function() {
    window.WPCA = window.WPCA || {};
    window.WPCA.core = window.WPCA.core || {};
    
    // 确保基础方法存在
    if (!window.WPCA.core.showNotice) {
        window.WPCA.core.showNotice = function(message, type = 'success') {
            const $ = jQuery;
            const noticeClass = `notice notice-${type} is-dismissible`;
            const $notice = $(`<div class="${noticeClass}"><p>${message}</p></div>`);
            $notice.insertAfter($('.wrap h1').first());
            
            setTimeout(() => $notice.fadeOut(500, () => $notice.remove()), 
                type === 'success' ? 3000 : 5000);
        };
    }
    
    // 确保权限检查方法存在
    if (!window.WPCA.core.hasPermission) {
        window.WPCA.core.hasPermission = function() {
            return !!(window.wpca?.can_manage_options || window.WPCA?.core?.wpca?.can_manage_options);
        };
    }
})();

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
        
        // 调试日志
        console.log(`尝试更新菜单可见性: "${slug}" => ${isVisible ? '显示' : '隐藏'}`);
        
        const possibleIDs = [
            `#toplevel_page_${slug}`,
            `#menu-${slug}`,
            `#menu-posts-${slug}`
        ];
        
        for (const id of possibleIDs) {
            const $item = $(id);
            if ($item.length) {
                $menuItem = $item;
                console.log(`找到菜单项: ${id}`);
                break;
            }
        }
        
        if (!$menuItem || !$menuItem.length) {
            $menuItem = $(`#adminmenu a[href$="${slug}"], #adminmenu a[href*="page=${slug}"]`).closest('li');
            if ($menuItem.length) {
                console.log(`通过链接找到菜单项: ${slug}`);
            }
        }
        
        if ($menuItem && $menuItem.length) {
            $menuItem.css('transition', 'opacity 0.3s');
            
            if (isVisible) {
                $menuItem.show().css('opacity', 1);
            } else {
                $menuItem.css('opacity', 0).delay(300).hide(0);
            }
            
            $menuItem.toggleClass('wpca-hidden-menu', !isVisible);
            console.log(`菜单 "${slug}" 可见性已设置为 ${isVisible ? '可见' : '隐藏'}`);
            return true;
        }
        
        console.warn(`无法找到菜单项: "${slug}"`);
        return false;
    },

    init: function() {
        const $ = jQuery;
        const self = this;
        
        // 调试日志
        console.log('WPCA Menu Toggle 初始化');
        console.log('WPCA 全局对象:', window.WPCA);
        console.log('wpca 全局对象:', window.wpca);
        
        // 检查切换开关是否存在
        const toggleSwitches = $('.wpca-menu-toggle-switch input[type="checkbox"]');
        console.log('找到切换开关数量:', toggleSwitches.length);
        
        // 使用事件委托处理动态添加的元素
        $(document).on('change', '.wpca-menu-toggle-switch input[type="checkbox"]', function() {
            const $checkbox = $(this);
            const $switch = $checkbox.closest('.wpca-menu-toggle-switch');
            
            // 尝试多种方式获取菜单 slug
            let slug = $checkbox.data('menu-slug');
            if (!slug) {
                slug = $checkbox.closest('li').data('menu-slug');
            }
            if (!slug) {
                // 尝试从隐藏字段获取
                const $hiddenInput = $checkbox.closest('li').find('input[type="hidden"][name^="wpca_settings[menu_order]"]');
                if ($hiddenInput.length) {
                    slug = $hiddenInput.val();
                }
            }
            
            const isChecked = $checkbox.is(':checked');
            const state = isChecked ? 1 : 0;
            
            console.log('切换开关变化:', {
                slug: slug,
                isChecked: isChecked,
                state: state,
                checkbox: $checkbox[0],
                parentLi: $checkbox.closest('li')[0]
            });
            
            if (!slug) {
                console.error('WPCA 错误: 未找到菜单 slug');
                WPCA.core.showNotice('错误: 无法识别菜单项', 'error');
                return;
            }
            
            // 更新菜单可见性
            self.updateMenuVisibility(slug, state);
            
            // 获取AJAX URL和nonce
            const ajaxUrl = window.wpca?.ajaxurl || window.WPCA?.core?.wpca?.ajaxurl || window.wpca_admin?.ajaxurl;
            const nonce = window.wpca?.nonce || window.WPCA?.core?.wpca?.nonce || window.wpca_admin?.nonce;
            
            // 验证必要参数
            if (!ajaxUrl || !nonce) {
                console.error('WPCA 错误: 缺少必要的全局变量');
                WPCA.core.showNotice('初始化失败: 缺少必要参数', 'error');
                return;
            }
            
            // 检查用户权限
            if (typeof WPCA.core.hasPermission !== 'function' || !WPCA.core.hasPermission()) {
                console.error('WPCA 错误: 权限不足或方法未定义');
                WPCA.core.showNotice('错误: 您没有足够权限', 'error');
                return;
            }
            
            // 确保nonce有效
            if (typeof nonce !== 'string' || nonce.length < 10) {
                console.error('WPCA 错误: 无效的nonce');
                WPCA.core.showNotice('错误: 安全验证失败', 'error');
                return;
            }
            
            if (!ajaxUrl || !nonce) {
                console.error('WPCA 错误: 缺少必要的全局变量');
                console.log('可用的全局变量:', {
                    'window.wpca': window.wpca,
                    'window.WPCA': window.WPCA,
                    'window.wpca_admin': window.wpca_admin
                });
                WPCA.core.showNotice('初始化失败: 缺少必要参数', 'error');
                return;
            }
            
            // 显示加载指示器
            $switch.addClass('loading');
            
            // 发送 AJAX 请求
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpca_save_tab_preference',
                    slug: slug,
                    state: state,
                    nonce: nonce,
                    _wpnonce: nonce, // 双重验证
                    _wp_http_referer: encodeURIComponent(window.location.href)
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', nonce);
                },
                success: function(response) {
                    console.log('AJAX 响应:', response);
                    $switch.removeClass('loading');
                    
                    if (response.success) {
                        WPCA.core.showNotice('设置已保存', 'success');
                        // 更新本地菜单状态
                        if (response.data?.menu_toggles) {
                            Object.entries(response.data.menu_toggles).forEach(([slug, state]) => {
                                self.updateMenuVisibility(slug, state);
                            });
                        }
                    } else {
                        const errorMsg = response?.data?.message || '发生未知错误';
                        console.error('AJAX 错误:', errorMsg);
                        WPCA.core.showNotice(errorMsg, 'error');
                        $checkbox.prop('checked', !isChecked);
                        self.updateMenuVisibility(slug, !state);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX 请求失败:', status, error);
                    $switch.removeClass('loading');
                    
                    let message = '请求失败';
                    if (xhr.status === 403) {
                        message = '权限不足或安全验证失败';
                    } else if (xhr.status === 0) {
                        message = '网络连接错误，请检查网络';
                    }
                    
                    WPCA.core.showNotice(message, 'error');
                    $checkbox.prop('checked', !isChecked);
                    self.updateMenuVisibility(slug, !state);
                    
                    // 如果是权限问题，重新加载页面
                    if (xhr.status === 403) {
                        setTimeout(() => location.reload(), 2000);
                    }
                }
            });
        });

        // 菜单切换功能
        $('#wpca-menu-toggle').on('change', function() {
            const isEnabled = $(this).is(':checked');
            console.log('主菜单切换:', isEnabled ? '启用' : '禁用');
            
            $('.wpca-menu-sortable, .wpca-menu-order-wrapper').stop(true, true).animate({
                opacity: isEnabled ? 1 : 0
            }, 300, function() {
                $(this).toggle(isEnabled);
            });
            
            // 获取正确的 AJAX URL 和 nonce
            const ajaxUrl = window.wpca?.ajaxurl || window.WPCA?.core?.wpca?.ajaxurl || window.wpca_admin?.ajaxurl;
            const nonce = window.wpca?.nonce || window.WPCA?.core?.wpca?.nonce || window.wpca_admin?.nonce;
            
            if (ajaxUrl && nonce) {
                $.post(ajaxUrl, {
                    action: 'wpca_save_tab_preference',
                    enabled: isEnabled ? 1 : 0,
                    nonce: nonce,
                    _wpnonce: nonce,
                    _wp_http_referer: encodeURIComponent(window.location.href)
                }, response => {
                    if (!response.success) {
                        console.error('保存菜单切换状态失败:', response.data);
                    }
                });
            }
        });
    }
};

// 页面加载完成后初始化
jQuery(document).ready(function() {
    // 确保 WPCA.core 存在
    if (!window.WPCA.core) {
        window.WPCA.core = {
            showNotice: function(message, type) {
                const $ = jQuery;
                const noticeClass = `notice notice-${type} is-dismissible`;
                const $notice = $(`<div class="${noticeClass}"><p>${message}</p></div>`);
                $notice.insertAfter($('.wrap h1').first());
                
                setTimeout(() => $notice.fadeOut(500, () => $notice.remove()), 
                    type === 'success' ? 3000 : 5000);
            }
        };
    }
    
    // 初始化菜单切换功能
    WPCA.menuToggle.init();
});