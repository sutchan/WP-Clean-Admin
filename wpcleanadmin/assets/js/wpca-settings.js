/**
 * WP Clean Admin Combined JavaScript
 * Combines functionality from wp-clean-admin.js and wpca-settings.js
 */

jQuery(document).ready(function($) {
    'use strict';

    // ======================================================
    // Settings page tab navigation
    // ======================================================
    // Store active tab in localStorage
    var activeTab = localStorage.getItem('wpca_active_tab');
    if (activeTab) {
        $(".wpca-tab, .wpca-tab-content").removeClass("active");
        $(`.wpca-tab[data-tab="${activeTab}"]`).addClass("active");
        $(`#${activeTab}`).addClass("active");
    }

    $(".wpca-tab").click(function() {
        var tabId = $(this).data("tab");
        localStorage.setItem('wpca_active_tab', tabId);
        $(".wpca-tab, .wpca-tab-content").removeClass("active");
        $(this).addClass("active");
        $("#" + tabId).addClass("active");
    });

    // Preserve active tab after form submission
    $('form').on('submit', function() {
        var activeTab = $('.wpca-tab.active').data('tab');
        localStorage.setItem('wpca_active_tab', activeTab);
    });

    // ======================================================
    // Menu sorting functionality
    // ======================================================
    
    // Make all menu items sortable with hierarchy support
    $('#wpca-menu-order').sortable({
        items: 'li',
        handle: '.dashicons-menu',
        tolerance: 'pointer',
        update: function(event, ui) {
            updateMenuOrder();
        }
    });

    // Update menu order and hidden status
    function updateMenuOrder() {
        var menuOrder = [];
        var hiddenItems = [];
        
        $('#wpca-menu-order li').each(function() {
            var $item = $(this);
            var slug = $item.data('menu-slug');
            menuOrder.push(slug);
            
            if ($item.hasClass('menu-hidden')) {
                hiddenItems.push(slug);
            }
        });
        
        $('#wpca_menu_order').val(JSON.stringify(menuOrder));
        $('#wpca_hidden_items').val(JSON.stringify(hiddenItems));
    }

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
    // Enhanced menu functionality
    // ======================================================
    
    // Handle toggle switch changes for all menu items
    $(document).on('change', '.wpca-slide-toggle input', function() {
        var $li = $(this).closest('li');
        var isChecked = this.checked;
        
        // Toggle menu-hidden class based on checkbox state
        $li.toggleClass('menu-hidden', isChecked);
        
        // Toggle child submenus as well
        $li.find('> ul li').each(function() {
            $(this).toggleClass('menu-hidden', isChecked);
        });
        
        // Update menu order and hidden status
        updateMenuOrder();
    });

    // Initialize all toggle switches
    $('.wpca-slide-toggle input').each(function() {
        var $li = $(this).closest('li');
        $(this).prop('checked', !$li.hasClass('menu-hidden'));
    });
    
    // Full hierarchical menu initialization
    function initializeMenuHierarchy() {
        // First show all menu containers
        $('#wpca-menu-order, .wpca-submenu-sortable').css('display', 'block');
        
        // Process all menu items
        $('#wpca-menu-order').find('li').each(function() {
            var $item = $(this);
            var slug = $item.data('menu-slug');
            var isHidden = $item.hasClass('menu-hidden');
            var level = $item.data('level') || 0;
            var isSubmenu = $item.hasClass('submenu-item');
            var hasChildren = $item.find('> ul').length > 0;
            
            // Set item styling
            $item.css({
                'padding-left': (level * 25) + 'px',
                'display': 'block',
                'position': 'relative'
            });
            
            // Add toggle switch if missing
            if (!$item.find('.wpca-slide-toggle').length) {
                $item.append(`
                    <label class="wpca-slide-toggle" style="position:absolute; right:10px;">
                        <input type="checkbox" name="wpca_settings[menu_hidden][${slug}]" 
                            ${isHidden ? 'checked' : ''}>
                        <span class="slider">
                            <span class="slide-handle"></span>
                        </span>
                    </label>
                `);
            }
            
            // Ensure child UL is visible for parent items
            if (hasChildren) {
                $item.find('> ul').css({
                    'display': 'block',
                    'margin-left': '15px'
                });
            }
            
            // Add expand/collapse icon for parent items
            if (hasChildren && !$item.find('.wpca-menu-expand').length) {
                $item.append(`
                    <span class="dashicons dashicons-arrow-down wpca-menu-expand" 
                          style="position:absolute; right:10px; cursor:pointer;"></span>
                `);
            }
        });
        
        // Toggle child menu visibility
        $(document).on('click', '.wpca-menu-expand', function() {
            $(this).toggleClass('dashicons-arrow-down dashicons-arrow-up')
                   .siblings('ul').toggle();
        });
    }
    
    // Initialize menu on load and after sorting
    initializeMenuHierarchy();
    $('#wpca-menu-order').on('sortupdate', initializeMenuHierarchy);

    // Update menu order and hidden status
    function updateMenuOrder() {
        var menuOrder = [];
        var hiddenItems = [];
        
        $('#wpca-menu-order li').each(function() {
            var $item = $(this);
            var slug = $item.data('menu-slug');
            var level = $item.data('level') || 0;
            $item.css('padding-left', (level * 20) + 'px');
            menuOrder.push(slug);
            
            if ($item.hasClass('menu-hidden')) {
                hiddenItems.push(slug);
            }
        });
        
        $('#wpca_menu_order').val(JSON.stringify(menuOrder));
        $('#wpca_hidden_items').val(JSON.stringify(hiddenItems));
    }

    // ======================================================
    // General admin functionality (from wp-clean-admin.js)
    // ======================================================
    
    // Future JavaScript interactions will go here.
    // For example, toggling elements, handling live previews in settings, etc.

    // console.log('WP Clean Admin JS Loaded');
});