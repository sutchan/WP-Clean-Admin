/**
 * WP Clean Admin Combined JavaScript
 * Combines functionality from wp-clean-admin.js and wpca-settings.js
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Global variables
    var ajaxurl = typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php';
    var debounceTimer;

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
    // Menu sorting functionality
    // ======================================================
    
    // Make all menu items sortable in a flat list
    $('#wpca-menu-order').sortable({
        items: 'li',
        handle: '.dashicons-menu',
        update: function(event, ui) {
            // Update menu order
            var menuOrder = [];
            $('#wpca-menu-order li').each(function() {
                menuOrder.push($(this).data('menu-slug'));
            });
            $('#wpca_menu_order').val(JSON.stringify(menuOrder));
        }
    });

    // Reset menu order to default
    $('#wpca-reset-menu-order').click(function() {
        if (confirm('Are you sure you want to reset the menu order to default?')) {
            // Clear saved order
            $('input[name="wpca_settings[menu_order][]"]').val('');
            $('input[name="wpca_settings[submenu_order][]"]').val('');
            // Reload the page to show default order
            location.reload();
        }
    });

    // Top level menu sorting
    $('#wpca-menu-order').sortable({
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
    // General admin functionality (from wp-clean-admin.js)
    // ======================================================
    
    // Future JavaScript interactions will go here.
    // For example, toggling elements, handling live previews in settings, etc.

    // ======================================================
    // Toggle switch auto-save functionality
    // ======================================================
    
    // Initialize form submission state
    $('#wpca-settings-form').data('submitting', false);
    
    // Handle all toggle switches
    $('.wpca-toggle-switch input[type="checkbox"], #wpca-menu-toggle').on('change', function() {
        // Debounce to prevent rapid firing
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            if (!$('#wpca-settings-form').data('submitting')) {
                $('#wpca-settings-form').data('submitting', true);
                $('#wpca-settings-form').submit();
            }
        }, 300);
    });

    // console.log('WP Clean Admin JS Loaded');
});