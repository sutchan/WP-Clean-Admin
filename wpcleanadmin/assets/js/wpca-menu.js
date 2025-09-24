/**
 * WP Clean Admin - 菜单管理模块
 * 合并了菜单排序和菜单切换功能
 */

// 确保WPCA命名空间存在
window.WPCA = window.WPCA || {};

/**
 * 菜单管理模块
 */
WPCA.menu = {
    config: null, // 将存储配置

    /**
     * 初始化菜单管理功能
     * @returns {boolean} - True on success, false on failure.
     */
    init: function() {
        const $ = jQuery;

        // --- 配置获取和验证 ---
        if (typeof window.wpca_admin === 'undefined') {
            console.error('WPCA Error: The configuration object "wpca_admin" is missing.');
            if (window.WPCA?.core?.displayErrorNotice) {
                WPCA.core.displayErrorNotice('菜单管理功能配置加载失败');
            }
            return false;
        }
        this.config = window.wpca_admin;

        if (this.config.debug) {
            console.log('WPCA Menu Management: Initializing...');
        }

        // --- 依赖检查 ---
        if (typeof $.ui === 'undefined' || typeof $.ui.sortable === 'undefined') {
            console.error('WPCA Error: jQuery UI Sortable is not loaded.');
            if (window.WPCA?.core?.displayErrorNotice) {
                WPCA.core.displayErrorNotice('菜单管理功能依赖 jQuery UI Sortable，请确保已加载');
            }
            return false;
        }

        // --- 初始化菜单排序功能 ---
        this.initMenuSorting();

        // --- 初始化菜单切换功能 ---
        this.initMenuToggle();

        if (this.config.debug) {
            console.log('WPCA Menu Management: Initialized successfully.');
        }

        return true;
    },

    /**
     * 初始化菜单排序功能
     */
    initMenuSorting: function() {
        const $ = jQuery;

        // 主菜单排序
        $('#wpca-menu-order').sortable({
            items: 'li.wpca-top-level-item', // 更精确的选择器，防止子菜单项被拖到顶层
            handle: '.wpca-drag-handle',
            placeholder: 'wpca-sortable-placeholder',
            axis: 'y',
            update: function() {
                const menuOrder = $(this).sortable('toArray', {
                    attribute: 'data-menu-slug'
                });
                // 将排序后的数组（JSON字符串）存入隐藏的 input 中，以便随表单提交
                $('#wpca-menu-order-input').val(JSON.stringify(menuOrder));
            }
        });

        // 子菜单排序
        $('.wpca-submenu-sortable').sortable({
            items: 'li.wpca-submenu-item',
            handle: '.wpca-drag-handle',
            placeholder: 'wpca-sortable-placeholder',
            axis: 'y',
            update: function() {
                const parentSlug = $(this).data('parent-slug');
                const submenuOrder = $(this).sortable('toArray', {
                    attribute: 'data-menu-slug'
                });
                // 找到对应的隐藏 input 并更新其值
                const inputName = `wpca_settings[submenu_order][${parentSlug}]`;
                $(`input[name="${inputName}"`).val(JSON.stringify(submenuOrder));
            }
        });

        if (this.config.debug) {
            console.log('WPCA Menu Sorting: Initialized successfully.');
        }
    },

    /**
     * 初始化菜单切换功能
     */
    initMenuToggle: function() {
        const $ = jQuery;

        // 将事件监听器绑定到 document，以支持动态添加的元素
        $(document).on('change', '.wpca-menu-toggle-checkbox', this.handleToggleChange.bind(this));

        if (this.config.debug) {
            console.log('WPCA Menu Toggle: Initialized successfully.');
        }
    },

    /**
     * 处理开关状态变化的事件回调
     * @param {Event} e - The change event object.
     */
    handleToggleChange: function(e) {
        const $checkbox = jQuery(e.currentTarget);
        const $switch = $checkbox.closest('.wpca-switch');
        
        // 依赖后端在 checkbox 上提供 slug
        const slug = $checkbox.data('menu-slug');
        const isChecked = $checkbox.is(':checked');
        const state = isChecked ? 1 : 0;

        if (!slug) {
            console.error('WPCA Error: Menu slug is missing from the checkbox data attribute.');
            if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.displayErrorNotice === 'function') {
                WPCA.core.displayErrorNotice('菜单slug缺失，请刷新页面重试');
            }
            return;
        }

        if (this.config.debug) {
            console.log('Toggle changed:', { slug, state });
        }
        
        // 乐观 UI 更新：立即更新左侧菜单的可见性
        this.updateAdminMenuVisibility(slug, isChecked);
        
        $switch.addClass('loading');

        // 使用 Promise-based AJAX 调用
        this.sendToggleRequest(slug, state)
            .done((response) => {
                if (response && response.success) {
                    if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.displaySuccessNotice === 'function') {
                        WPCA.core.displaySuccessNotice('设置已保存');
                    }
                } else {
                    // 如果后端返回失败，回滚 UI
                    const errorMessage = response?.data?.message || '发生未知错误';
                    if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.displayErrorNotice === 'function') {
                        WPCA.core.displayErrorNotice(errorMessage);
                    }
                    this.revertToggleState($checkbox, isChecked);
                }
            })
            .fail((xhr) => {
                // 如果请求本身失败（网络、服务器错误），也回滚 UI
                const message = this.getErrorMessageFromXHR(xhr);
                if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.displayErrorNotice === 'function') {
                    WPCA.core.displayErrorNotice(message);
                }
                this.revertToggleState($checkbox, isChecked);
            })
            .always(() => {
                $switch.removeClass('loading');
            });
    },

    /**
     * 发送 AJAX 请求并返回一个 Promise 对象
     * @param {string} slug - The menu slug.
     * @param {number} state - The new state (1 for visible, 0 for hidden).
     * @returns {jqXHR} - The jQuery AJAX promise.
     */
    sendToggleRequest: function(slug, state) {
        return jQuery.ajax({
            url: this.config.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpca_toggle_menu',
                nonce: this.config.nonce,
                slug: slug,
                state: state
            }
        });
    },

    /**
     * 回滚开关和菜单的 UI 状态
     * @param {jQuery} $checkbox - The checkbox element.
     * @param {boolean} originalIsChecked - The state before the user's action.
     */
    revertToggleState: function($checkbox, originalIsChecked) {
        const slug = $checkbox.data('menu-slug');
        $checkbox.prop('checked', !originalIsChecked);
        this.updateAdminMenuVisibility(slug, !originalIsChecked);
    },

    /**
     * 根据 XHR 对象生成用户友好的错误消息
     * @param {jqXHR} xhr - The jQuery XHR object from a failed request.
     * @returns {string} - The error message.
     */
    getErrorMessageFromXHR: function(xhr) {
        if (xhr.status === 403) {
            return '权限不足或安全验证失败。页面将在2秒后刷新。';
        }
        if (xhr.status === 0) {
            return '网络连接错误，请检查网络后重试。';
        }
        return `请求失败，服务器返回错误码: ${xhr.status}`;
    },

    /**
     * 更新 WordPress 后台左侧主菜单的可见性
     * @param {string} slug - The menu slug to update.
     * @param {boolean} isVisible - Whether the menu should be visible.
     */
    updateAdminMenuVisibility: function(slug, isVisible) {
        if (!slug) return;
        
        // 查找菜单项的逻辑可以保持不变，因为它很健壮
        const $menuItem = this.findMenuItem(jQuery, slug);

        if ($menuItem.length) {
            if (this.config.debug) {
                console.log(`Updating visibility for "${slug}" to ${isVisible}`);
            }
            // 使用 CSS 过渡比 jQuery 动画性能更好
            $menuItem.toggleClass('wpca-hidden-menu', !isVisible);
        } else if (this.config.debug) {
            console.warn(`Could not find admin menu item for slug: "${slug}"`);
        }
    },

    /**
     * 查找菜单项的辅助函数
     * @param {jQuery} $ - The jQuery object.
     * @param {string} slug - The menu slug to find.
     * @returns {jQuery} - The found menu item element.
     */
    findMenuItem: function($, slug) {
        // 尝试通过多种选择器找到菜单项
        const selectors = [
            `#toplevel_page_${slug}`,
            `#menu-posts-${slug}`,
            `#menu-${slug.replace(/\.php.*/, '')}`, // 匹配 edit.php, themes.php 等
            `li a[href$="page=${slug}"]`,
            `li a[href$="${slug}"]`,
        ];
        
        for (const selector of selectors) {
            const $item = $(selector).closest('li');
            if ($item.length) {
                return $item;
            }
        }
        return $(); // 返回一个空的 jQuery 对象
    }
};

// 页面加载完成后初始化
jQuery(document).ready(function() {
    if (WPCA.menu?.init) {
        WPCA.menu.init();
    } else {
        console.error('WPCA Error: Menu management module failed to load.');
        if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.displayErrorNotice === 'function') {
            WPCA.core.displayErrorNotice('菜单管理功能初始化失败');
        }
    }
});