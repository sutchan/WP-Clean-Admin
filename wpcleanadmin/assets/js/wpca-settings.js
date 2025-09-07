/**
 * WP Clean Admin Combined JavaScript
 * Combines functionality from wp-clean-admin.js and wpca-settings.js
 */

jQuery(document).ready(function($) {
    'use strict';

    // ======================================================
    // Settings page tab navigation
    // ======================================================
    $(".wpca-tab").click(function() {
        $(".wpca-tab, .wpca-tab-content").removeClass("active");
        $(this).addClass("active");
        $("#" + $(this).data("tab")).addClass("active");
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
        if (confirm(wpca_vars.reset_confirm)) {
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

    // console.log('WP Clean Admin JS Loaded');
});