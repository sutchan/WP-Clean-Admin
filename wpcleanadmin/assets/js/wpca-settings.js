/**
 * WP Clean Admin Combined JavaScript
 * Combines functionality from wp-clean-admin.js and wpca-settings.js
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // ==============================================
    // Original JS code starts here
    // ==============================================
    
    // Global variables
    var debounceTimer;
    var ajaxurl = wpca_admin?.ajaxurl || '/wp-admin/admin-ajax.php';

    // ======================================================
    // Settings page tab navigation
    // ======================================================
    
    // Set active tab based on hidden input value
    var currentTab = $('#wpca-current-tab').val() || 'tab-general';
    $(".wpca-tab, .wpca-tab-content").removeClass("active");
    $('.wpca-tab[data-tab="' + currentTab + '"]').addClass("active");
    $("#" + currentTab).addClass("active");
    
    // Handle tab clicks
    $(".wpca-tab").click(function() {
        var tabId = $(this).data("tab");
        $(".wpca-tab, .wpca-tab-content").removeClass("active");
        $(this).addClass("active");
        $("#" + tabId).addClass("active");
        
        // Update hidden input with current tab
        $('#wpca-current-tab').val(tabId);
        
        // Save tab preference immediately
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            // Only submit if not already submitting from another action
            if (!$('#wpca-settings-form').data('submitting')) {
                $('#wpca-settings-form').data('submitting', true);
                
                // Use AJAX to save just the tab preference
                var data = {
                    'action': 'wpca_save_tab_preference',
                    'tab': tabId,
                    'security': $('input[name="_wpnonce"]').val()
                };
                
                $.post(ajaxurl, data, function(response) {
                    $('#wpca-settings-form').data('submitting', false);
                });
            }
        }, 300);
    });

    // ======================================================
    // Menu sorting functionality (Optimized)
    // ======================================================
    
    // Unified menu sorting handler
    $('#wpca-menu-order').sortable({
        items: 'li',
        handle: '.dashicons-menu',
        update: function(event, ui) {
            var menuOrder = [];
            $('#wpca-menu-order li').each(function() {
                menuOrder.push($(this).data('menu-slug'));
            });
            $('#wpca_menu_order').val(JSON.stringify(menuOrder));
        }
    });
    
    // Submenu sorting
    $('.wpca-submenu-sortable').sortable({
        update: function(event, ui) {
            var parentSlug = $(this).data('parent-slug');
            var submenuOrder = [];
            $(this).find('li').each(function() {
                submenuOrder.push($(this).data('menu-slug'));
            });
            $('input[name="wpca_settings[submenu_order]['+parentSlug+'][]"]').val(JSON.stringify(submenuOrder));
        }
    });

    // ======================================================
    // Menu toggle switch functionality 
    // ======================================================
    
    /**
     * Initialize menu toggle switch data attributes
     */
    function initMenuToggleData() {
        $('.wpca-menu-toggle-switch input[type="checkbox"]').each(function() {
            var $checkbox = $(this);
            var $switch = $checkbox.closest('.wpca-menu-toggle-switch');
            var $menuItem = $switch.closest('li');
            var slug = $menuItem.data('menu-slug');
            
            if (slug) {
                $checkbox.data('slug', slug);
            }
        });
    }
    
    // Initialize on load
    initMenuToggleData();

    /**
     * Handle menu toggle switch changes with real-time menu synchronization
     * @param {Event} e - Change event
     */
    $(document).on('change', '.wpca-menu-toggle-switch input[type="checkbox"]', function() {
        var $checkbox = $(this);
        var $switch = $checkbox.closest('.wpca-menu-toggle-switch');
        var slug = $checkbox.data('slug');
        var state = $checkbox.is(':checked') ? 1 : 0;
        
        // If slug is not set, try to get it from the menu item
        if (!slug) {
            var $menuItem = $switch.closest('li');
            slug = $menuItem.data('menu-slug');
            if (slug) {
                $checkbox.data('slug', slug);
            }
        }
        
        // 先更新UI状态
        $switch.toggleClass('checked', state);
        $checkbox.prop('checked', state);
        
        // 实时更新WordPress后台菜单显示状态（仅影响实际后台菜单，不影响设置页面）
        if (slug) {
            // 只在WordPress后台页面（非设置页面）中操作实际的菜单元素
            if (window.location.href.indexOf('wp_clean_admin') === -1 && 
                window.location.href.indexOf('options-general.php') === -1) {
                
                var menuElement = $('#toplevel_page_' + slug);
                var submenuElement = $('#menu-' + slug);
                
                if (state) {
                    // 显示菜单项
                    menuElement.removeClass('wpca-hidden-menu');
                    submenuElement.removeClass('wpca-hidden-menu');
                    menuElement.css({ display: '', height: '', overflow: '' });
                    submenuElement.css({ display: '', height: '', overflow: '' });
                } else {
                    // 隐藏菜单项
                    menuElement.addClass('wpca-hidden-menu');
                    submenuElement.addClass('wpca-hidden-menu');
                    menuElement.css({ display: 'none', height: '0', overflow: 'hidden' });
                    submenuElement.css({ display: 'none', height: '0', overflow: 'hidden' });
                }
            }
            
            // 确保设置页面中的wpca-menu-order列表项始终保持显示
            var settingsMenuItem = $('#wpca-menu-order li[data-menu-slug="' + slug + '"]');
            if (settingsMenuItem.length) {
                settingsMenuItem.css({ display: '', opacity: '', pointerEvents: '' });
            }
        }
        
        // 保存到后端
        if (!slug || state === undefined || !wpca_admin.nonce) {
            // 恢复之前的状态
            $switch.toggleClass('checked', !state);
            $checkbox.prop('checked', !state);
            
            // 恢复菜单显示状态
            if (slug) {
                // 恢复WordPress后台菜单显示状态
                if (window.location.href.indexOf('wp_clean_admin') === -1 && 
                    window.location.href.indexOf('options-general.php') === -1) {
                    
                    var menuElement = $('#toplevel_page_' + slug);
                    var submenuElement = $('#menu-' + slug);
                    menuElement.removeClass('wpca-hidden-menu');
                    submenuElement.removeClass('wpca-hidden-menu');
                    menuElement.css({ display: '', height: '', overflow: '' });
                    submenuElement.css({ display: '', height: '', overflow: '' });
                }
                
                // 确保设置页面中的菜单项显示
                var settingsMenuItem = $('#wpca-menu-order li[data-menu-slug="' + slug + '"]');
                if (settingsMenuItem.length) {
                    settingsMenuItem.css({ display: '', opacity: '', pointerEvents: '' });
                }
            }
            return;
        }

        // AJAX请求处理
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpca_toggle_menu',
                slug: slug,
                state: state,
                nonce: wpca_admin.nonce
            },
            dataType: 'json',
            timeout: 10000
        }).done(function(response, textStatus, jqXHR) {
            // 验证响应结构
            if (!response || typeof response.success === 'undefined') {
                showErrorNotice('服务器返回了无效的响应格式', $switch);
                
                // AJAX失败时恢复菜单状态
                if (slug) {
                    // 恢复WordPress后台菜单显示状态
                    if (window.location.href.indexOf('wp_clean_admin') === -1 && 
                        window.location.href.indexOf('options-general.php') === -1) {
                        
                        var menuElement = $('#toplevel_page_' + slug);
                        var submenuElement = $('#menu-' + slug);
                        menuElement.removeClass('wpca-hidden-menu');
                        submenuElement.removeClass('wpca-hidden-menu');
                        menuElement.css({ display: '', height: '', overflow: '' });
                        submenuElement.css({ display: '', height: '', overflow: '' });
                    }
                    
                    // 确保设置页面中的菜单项显示
                    var settingsMenuItem = $('#wpca-menu-order li[data-menu-slug="' + slug + '"]');
                    if (settingsMenuItem.length) {
                        settingsMenuItem.css({ display: '', opacity: '', pointerEvents: '' });
                    }
                }
                return;
            }
            
            if (!response.success) {
                // 显示详细的错误信息
                const errorMsg = response.data?.message || 
                               (response.data || '操作失败');
                showErrorNotice(errorMsg, $switch);
                
                // AJAX失败时恢复菜单状态
                if (slug) {
                    // 恢复WordPress后台菜单显示状态
                    if (window.location.href.indexOf('wp_clean_admin') === -1 && 
                        window.location.href.indexOf('options-general.php') === -1) {
                        
                        var menuElement = $('#toplevel_page_' + slug);
                        var submenuElement = $('#menu-' + slug);
                        menuElement.removeClass('wpca-hidden-menu');
                        submenuElement.removeClass('wpca-hidden-menu');
                        menuElement.css({ display: '', height: '', overflow: '' });
                        submenuElement.css({ display: '', height: '', overflow: '' });
                    }
                    
                    // 确保设置页面中的菜单项显示
                    var settingsMenuItem = $('#wpca-menu-order li[data-menu-slug="' + slug + '"]');
                    if (settingsMenuItem.length) {
                        settingsMenuItem.css({ display: '', opacity: '', pointerEvents: '' });
                    }
                }
                return;
            }
            
            // 成功时显示短暂的成功提示
            showSuccessNotice('设置已保存', $switch);
        });
    });

    // Initialization complete
});