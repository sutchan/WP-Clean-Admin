/**
 * WP Clean Admin Settings JavaScript
 * 
 * @package WPCleanAdmin\Modules\Admin\Settings
 * @version 1.8.0
 * @author Sut
 * @since 1.8.0
 */

(function($) {
    'use strict';

    /**
     * WPCleanAdmin Settings Module
     */
    window.WPCleanAdmin = window.WPCleanAdmin || {};
    WPCleanAdmin.Settings = WPCleanAdmin.Settings || {};

    /**
     * Initialize settings functionality
     */
    WPCleanAdmin.Settings.init = function() {
        this.setupTabs();
        this.setupFormHandling();
        this.setupSectionToggle();
        this.setupMenuCustomization();
        this.setupSaveMessage();
    };

    /**
     * Setup settings tabs functionality
     */
    WPCleanAdmin.Settings.setupTabs = function() {
        const tabButtons = $('.wpca-tab-button');
        const tabContents = $('.wpca-tab-content');

        tabButtons.on('click', function() {
            const tabId = $(this).data('tab');

            tabButtons.removeClass('active');
            tabContents.removeClass('active');

            $(this).addClass('active');
            $(`#wpca-tab-${tabId}`).addClass('active');

            $('html, body').animate({
                scrollTop: $('.wpca-settings-form').offset().top - 20
            }, 300);

            // Update URL hash
            window.history.pushState({ tab: tabId }, '', `?page=wp-clean-admin&tab=${tabId}`);
        });

        // Handle hash change
        $(window).on('hashchange', function() {
            const hash = window.location.hash.substring(1);
            if (hash.startsWith('tab-')) {
                const tabId = hash.replace('tab-', '');
                $(`.wpca-tab-button[data-tab="${tabId}"]`).click();
            }
        });

        // Initialize from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab');
        if (initialTab && $(`.wpca-tab-button[data-tab="${initialTab}"]`).length) {
            $(`.wpca-tab-button[data-tab="${initialTab}"]`).click();
        }
    };

    /**
     * Setup form submission handling
     */
    WPCleanAdmin.Settings.setupFormHandling = function() {
        const form = $('#wpca-settings-form');

        form.on('submit', function(e) {
            WPCleanAdmin.Settings.showSaving();
            
            const saveMessage = $('#wpca-save-message');
            
            // Add loading state to submit button
            const submitButton = form.find('input[type="submit"]');
            const originalText = submitButton.val();
            submitButton.val(wpcaSettingsLocalize.savingText).prop('disabled', true);

            // Handle successful submission
            form.on('submit_success', function() {
                WPCleanAdmin.Settings.showSuccess();
                submitButton.val(originalText).prop('disabled', false);
            });

            // Handle failed submission
            form.on('submit_error', function() {
                WPCleanAdmin.Settings.showError();
                submitButton.val(originalText).prop('disabled', false);
            });
        });

        // Handle form reset
        form.on('reset', function() {
            setTimeout(function() {
                WPCleanAdmin.Settings.hideSaveMessage();
            }, 100);
        });
    };

    /**
     * Setup section toggle functionality
     */
    WPCleanAdmin.Settings.setupSectionToggle = function() {
        $('.wpca-settings-section h3').on('click', function(e) {
            // Don't toggle if clicking on input elements
            if ($(e.target).is('input, label, a, button')) {
                return;
            }

            const section = $(this).closest('.wpca-settings-section');
            const content = section.nextUntil('.wpca-settings-section');

            section.toggleClass('collapsed');
            content.slideToggle(200);
        });
    };

    /**
     * Setup menu customization functionality
     */
    WPCleanAdmin.Settings.setupMenuCustomization = function() {
        // Select all menu items
        $('#wpca-select-all-menu-items').on('click', function() {
            $('.wpca-menu-tree input[type="checkbox"]').prop('checked', true);
        });

        // Deselect all menu items
        $('#wpca-deselect-all-menu-items').on('click', function() {
            $('.wpca-menu-tree input[type="checkbox"]').prop('checked', false);
        });

        // Toggle submenu items
        $('.wpca-menu-item-header').on('click', function(e) {
            if (!$(e.target).is('input[type="checkbox"]')) {
                const submenu = $(this).next('.wpca-submenu-items');
                if (submenu.length > 0) {
                    submenu.slideToggle(200);
                    $(this).toggleClass('expanded');
                }
            }
        });

        // Make menu order list sortable
        if ($.fn.sortable) {
            $('#wpca-menu-order-list').sortable({
                handle: '.wpca-menu-order-handle',
                update: function(event, ui) {
                    $(this).find('.wpca-menu-order-item').each(function(index) {
                        $(this).find('input[type="hidden"]').val($(this).data('menu-slug'));
                    });
                }
            });
        }

        // Reset menu order
        $('#wpca-reset-menu-order').on('click', function() {
            const menuOrderList = $('#wpca-menu-order-list');
            const originalOrder = [];

            menuOrderList.find('.wpca-menu-order-item').each(function() {
                originalOrder.push($(this).clone());
            });

            menuOrderList.empty();
            $.each(originalOrder, function(index, item) {
                menuOrderList.append(item);
            });

            menuOrderList.find('.wpca-menu-order-item').each(function(index) {
                $(this).find('input[type="hidden"]').val($(this).data('menu-slug'));
            });
        });
    };

    /**
     * Show saving message
     */
    WPCleanAdmin.Settings.showSaving = function() {
        const saveMessage = $('#wpca-save-message');
        saveMessage.html('<span class="wpca-saving">' + wpcaSettingsLocalize.savingText + '</span>');
        saveMessage.show();
    };

    /**
     * Show success message
     */
    WPCleanAdmin.Settings.showSuccess = function() {
        const saveMessage = $('#wpca-save-message');
        saveMessage.html('<span class="wpca-success">' + wpcaSettingsLocalize.successText + '</span>');
        
        setTimeout(function() {
            WPCleanAdmin.Settings.hideSaveMessage();
        }, 3000);
    };

    /**
     * Show error message
     */
    WPCleanAdmin.Settings.showError = function() {
        const saveMessage = $('#wpca-save-message');
        saveMessage.html('<span class="wpca-error">' + wpcaSettingsLocalize.errorText + '</span>');
    };

    /**
     * Hide save message
     */
    WPCleanAdmin.Settings.hideSaveMessage = function() {
        const saveMessage = $('#wpca-save-message');
        saveMessage.hide().html('');
    };

    /**
     * Setup save message styles
     */
    WPCleanAdmin.Settings.setupSaveMessage = function() {
        const style = `
            <style>
                .wpca-saving { color: #007cba; font-weight: 500; }
                .wpca-success { color: #46b450; font-weight: 500; }
                .wpca-error { color: #dc3232; font-weight: 500; }
            </style>
        `;
        $('head').append(style);
    };

    /**
     * Handle AJAX save
     */
    WPCleanAdmin.Settings.ajaxSave = function(data, callback) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            dataType: 'json',
            beforeSend: function() {
                WPCleanAdmin.Settings.showSaving();
            },
            success: function(response) {
                if (response.success) {
                    WPCleanAdmin.Settings.showSuccess();
                } else {
                    WPCleanAdmin.Settings.showError();
                }
                if (callback) callback(response);
            },
            error: function() {
                WPCleanAdmin.Settings.showError();
                if (callback) callback({ success: false });
            }
        });
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        WPCleanAdmin.Settings.init();
    });

    /**
     * Export for use in other scripts
     */
    window.WPCleanAdminSettings = WPCleanAdmin.Settings;

})(jQuery);