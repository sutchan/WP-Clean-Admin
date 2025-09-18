/**
 * WP Clean Admin Settings JavaScript (Optimized)
 *
 * 整合了所有管理界面功能，包括标签切换、菜单排序和登录页面预览
 */
jQuery(document).ready(function($) {
    'use strict';

    // ==============================================
    // Pre-flight Checks & Global Variables
    // ==============================================

    // Ensure the localized object from PHP is available.
    if (typeof wpca_admin === 'undefined' || !wpca_admin.ajaxurl || !wpca_admin.nonce) {
        console.error('WPCA Script Error: The "wpca_admin" localization object is missing or incomplete.');
        // Display a persistent error on the page for the user.
        $('<div class="notice notice-error"><p><strong>WP Clean Admin Error:</strong> The required JavaScript settings could not be loaded. Some features may not work. Please try refreshing the page or contact support.</p></div>').prependTo('.wrap');
        return; // Halt execution.
    }

    var ajaxurl = wpca_admin.ajaxurl;
    var nonce = wpca_admin.nonce;

    // ==============================================
    // Notification Functions
    // ==============================================

    /**
     * Shows a dismissible notice.
     * @param {string} message - The message to display.
     * @param {string} type - 'success' or 'error'.
     */
    function showNotice(message, type = 'success') {
        $('.wpca-notice').remove();
        const noticeClass = `wpca-notice notice notice-${type} is-dismissible`;
        const $notice = $(`<div class="${noticeClass}"><p>${message}</p></div>`);
        $notice.insertAfter($('.wrap h1').first());

        // Auto-fade after a delay.
        setTimeout(() => $notice.fadeOut(500, () => $notice.remove()), type === 'success' ? 3000 : 5000);

        // Allow manual dismissal.
        $notice.on('click', '.notice-dismiss', function() {
            $notice.remove();
        });
    }

    // ======================================================
    // Settings Page Tab Navigation (Simplified)
    // ======================================================

    function initializeTabs() {
        const $tabs = $('.wpca-tab');
        const $tabContents = $('.wpca-tab-content');
        const $currentTabInput = $('#wpca-current-tab');

        // Set active tab based on hidden input value on page load.
        let currentTab = $currentTabInput.val() || $tabs.first().data('tab');
        if (!$("#" + currentTab).length) {
            currentTab = $tabs.first().data('tab');
        }

        $tabs.removeClass('active');
        $tabContents.removeClass('active');
        $(`.wpca-tab[data-tab="${currentTab}"]`).addClass('active');
        $(`#${currentTab}`).addClass('active');

        // Handle tab clicks.
        $tabs.on('click', function(e) {
            e.preventDefault();
            const tabId = $(this).data('tab');
            $tabs.removeClass('active');
            $tabContents.removeClass('active');

            $(this).addClass('active');
            $(`#${tabId}`).addClass('active');

            // Update hidden input. This value will be saved when the main form is submitted.
            // No AJAX call is needed here.
            $currentTabInput.val(tabId);
        });
    }
    initializeTabs();

    // ======================================================
    // Menu Sorting Functionality
    // ======================================================

    // Main menu sorting.
    $('#wpca-menu-order').sortable({
        items: 'li',
        handle: '.dashicons-menu',
        placeholder: 'wpca-sortable-placeholder',
        update: function() {
            const menuOrder = $(this).sortable('toArray', {
                attribute: 'data-menu-slug'
            });
            // Assumes a single hidden input named "wpca_settings[menu_order]"
            $('#wpca_menu_order_input').val(JSON.stringify(menuOrder));
        }
    });

    // Submenu sorting.
    $('.wpca-submenu-sortable').sortable({
        items: 'li',
        placeholder: 'wpca-sortable-placeholder',
        update: function() {
            const parentSlug = $(this).data('parent-slug');
            const submenuOrder = $(this).sortable('toArray', {
                attribute: 'data-menu-slug'
            });
            // Assumes a hidden input like name="wpca_settings[submenu_order][parent-slug]"
            const inputName = `wpca_settings[submenu_order][${parentSlug}]`;
            $(`input[name="${inputName}"]`).val(JSON.stringify(submenuOrder));
        }
    });

    // ======================================================
    // Menu Toggle Switch Functionality (No localStorage)
    // ======================================================

    $(document).on('change', '.wpca-menu-toggle-switch input[type="checkbox"]', function() {
        const $checkbox = $(this);
        const $switch = $checkbox.closest('.wpca-menu-toggle-switch');
        const slug = $checkbox.data('menu-slug'); // Assumes data-menu-slug is directly on the input.
        const isChecked = $checkbox.is(':checked');
        const state = isChecked ? 1 : 0;

        if (!slug) {
            console.error('WPCA Error: Menu slug not found on checkbox.');
            return;
        }

        // Optimistic UI update.
        updateMenuVisibility(slug, state);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpca_toggle_menu',
                slug: slug,
                state: state,
                nonce: nonce
            },
            beforeSend: () => $switch.addClass('loading'),
            complete: () => $switch.removeClass('loading'),
            dataType: 'json'
        }).done(function(response) {
            if (response && response.success) {
                showNotice('Setting saved.', 'success');
            } else {
                // On failure, revert the UI and show an error.
                const errorMessage = response.data?.message || 'An unknown error occurred.';
                showNotice(errorMessage, 'error');
                
                $checkbox.prop('checked', !isChecked);
                updateMenuVisibility(slug, !state);
            }
        }).fail(function() {
            // On catastrophic failure (e.g., server error), also revert and show an error.
            showNotice('Request failed. Please check your connection and try again.', 'error');
            
            $checkbox.prop('checked', !isChecked);
            updateMenuVisibility(slug, !state);
        });
    });

    /**
     * Instantly updates the visibility of the corresponding admin menu item in the sidebar.
     * This function only affects the *actual* admin menu, not the list on the settings page.
     * 
     * @param {string} slug - The menu slug to target
     * @param {boolean|number} isVisible - Whether the menu should be visible (true/1) or hidden (false/0)
     * @return {boolean} - Whether the update was successful
     */
    function updateMenuVisibility(slug, isVisible) {
        if (!slug) {
            console.warn('WPCA: Cannot update menu visibility - empty slug provided');
            return false;
        }
        
        // Convert to boolean to ensure consistent behavior
        isVisible = !!isVisible;
        
        // Try multiple selector strategies for better compatibility
        let $menuItem = null;
        
        // Strategy 1: Try exact ID match first (most reliable)
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
        
        // Strategy 2: If no direct ID match, try href matching (fallback)
        if (!$menuItem || !$menuItem.length) {
            // More precise href matching with URL path ending
            $menuItem = $(`#adminmenu a[href$="${slug}"], #adminmenu a[href*="page=${slug}"]`).closest('li');
        }
        
        // Apply visibility change if we found a matching menu item
        if ($menuItem && $menuItem.length) {
            // Add visual transition for smoother UX
            $menuItem.css('transition', 'opacity 0.3s');
            
            if (isVisible) {
                $menuItem.show().css('opacity', 1);
            } else {
                $menuItem.css('opacity', 0).delay(300).hide(0);
            }
            
            // Also update any related CSS classes
            $menuItem.toggleClass('wpca-hidden-menu', !isVisible);
            
            // Log success for debugging
            if (wpca_admin.debug) {
                console.log(`WPCA: Menu "${slug}" visibility set to ${isVisible ? 'visible' : 'hidden'}`);
            }
            
            return true;
        } else {
            // Log failure for debugging
            console.warn(`WPCA: Could not find menu item with slug "${slug}" to update visibility`);
            return false;
        }
    }

    // ======================================================
    // 菜单切换功能
    // ======================================================

    // 监听菜单切换开关
    $('#wpca-menu-toggle').on('change', function() {
        var isEnabled = $(this).is(':checked');
        
        // 平滑过渡效果
        $('.wpca-menu-sortable, .wpca-menu-order-wrapper').stop(true, true).animate({
            opacity: isEnabled ? 1 : 0
        }, 300, function() {
            $(this).toggle(isEnabled);
        });
        
        // 保存状态到数据库
        $.post(wpca_admin.ajaxurl, {
            action: 'wpca_toggle_menu_customization',
            enabled: isEnabled ? 1 : 0,
            nonce: wpca_admin.nonce
        }, function(response) {
            if (!response.success) {
                console.error('Failed to save menu toggle state:', response.data);
            }
        });
    });

    // ======================================================
    // 登录页面预览功能 (从wpca-admin-settings.js合并)
    // ======================================================

    /**
     * 更新登录页面预览
     * @param {string} style - 登录页面样式
     */
    function updateLoginPreview(style) {
        var preview = $('.wpca-login-preview-content');
        preview.removeClass('default-preview modern-preview minimal-preview dark-preview gradient-preview custom-preview');
        preview.addClass(style + '-preview');

        switch(style) {
            case 'default':
                preview.find('.wpca-login-preview-logo').css('background-image', 'url(data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 84 84"><path fill="%232271b1" d="M42,0C18.8,0,0,18.8,0,42s18.8,42,42,42s42-18.8,42-42S65.2,0,42,0z M42,64c-12.2,0-22-9.8-22-22s9.8-22,22-22 s22,9.8,22,22S54.2,64,42,64z"/></svg>)');
                preview.css('background-color', '#f1f1f1');
                break;
            case 'modern':
                preview.find('.wpca-login-preview-logo').css('background-image', 'url(data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 84 84"><path fill="%234A90E2" d="M42,0C18.8,0,0,18.8,0,42s18.8,42,42,42s42-18.8,42-42S65.2,0,42,0z M42,64c-12.2,0-22-9.8-22-22s9.8-22,22-22 s22,9.8,22,22S54.2,64,42,64z"/></svg>)');
                preview.css('background-color', '#f8f9fa');
                break;
            case 'minimal':
                preview.find('.wpca-login-preview-logo').css('background-image', '');
                preview.css('background-color', '#fff');
                break;
            case 'dark':
                preview.find('.wpca-login-preview-logo').css('background-image', 'url(data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 84 84"><path fill="%23fff" d="M42,0C18.8,0,0,18.8,0,42s18.8,42,42,42s42-18.8,42-42S65.2,0,42,0z M42,64c-12.2,0-22-9.8-22-22s9.8-22,22-22 s22,9.8,22,22S54.2,64,42,64z"/></svg>)');
                preview.css('background-color', '#222');
                break;
            case 'gradient':
                preview.find('.wpca-login-preview-logo').css('background-image', 'url(data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 84 84"><path fill="%23fff" d="M42,0C18.8,0,0,18.8,0,42s18.8,42,42,42s42-18.8,42-42S65.2,0,42,0z M42,64c-12.2,0-22-9.8-22-22s9.8-22,22-22 s22,9.8,22,22S54.2,64,42,64z"/></svg>)');
                preview.css('background', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)');
                break;
            case 'custom':
                var logoUrl = $('input[name="wpca_settings[login_logo]"]').val();
                var bgUrl = $('input[name="wpca_settings[login_background]"]').val();
                
                if (logoUrl) {
                    preview.find('.wpca-login-preview-logo').css('background-image', 'url(' + logoUrl + ')');
                } else {
                    preview.find('.wpca-login-preview-logo').css('background-image', '');
                }
                
                if (bgUrl) {
                    preview.css('background-image', 'url(' + bgUrl + ')');
                } else {
                    preview.css('background-image', '');
                }
                break;
        }
    }

    // 登录样式选择变更时更新预览
    $('input[name="wpca_settings[login_style]"]').on('change', function() {
        updateLoginPreview($(this).val());
    });

    // 媒体上传功能
    $('.wpca-upload-button').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var targetId = button.data('target');
        var field = $('#' + targetId);
        var preview = $('#' + targetId + '-preview');

        var frame = wp.media({
            title: wpca_admin.media_title || '选择或上传媒体',
            button: { text: wpca_admin.media_button || '使用此媒体' },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            field.val(attachment.url);
            preview.find('img').attr('src', attachment.url);
            preview.show();
            
            // 如果是登录页面相关的上传，更新自定义样式预览
            if (targetId === 'wpca-login-logo' || targetId === 'wpca-login-background') {
                // 确保当前选择的是自定义样式
                if ($('input[name="wpca_settings[login_style]"]:checked').val() === 'custom') {
                    updateLoginPreview('custom');
                }
            }
        });

        frame.open();
    });

    // 移除媒体
    $('.wpca-remove-button').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var targetId = button.data('target');
        var field = $('#' + targetId);
        var preview = $('#' + targetId + '-preview');
        
        field.val('');
        preview.hide();
        
        // 如果是登录页面相关的移除，更新自定义样式预览
        if (targetId === 'wpca-login-logo' || targetId === 'wpca-login-background') {
            // 确保当前选择的是自定义样式
            if ($('input[name="wpca_settings[login_style]"]:checked').val() === 'custom') {
                updateLoginPreview('custom');
            }
        }
    });

    // 初始化登录预览（如果在登录页面选项卡）
    if ($('#tab-login').length && $('input[name="wpca_settings[login_style]"]:checked').length) {
        updateLoginPreview($('input[name="wpca_settings[login_style]"]:checked').val());
    }
});