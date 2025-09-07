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

    // Tab navigation preserved but form submission handling removed

    // ======================================================
    // Menu sorting functionality
    // ======================================================
    
    // Make all menu items sortable with hierarchy support
    // (Unified sortable initialization is below)



    // Reset menu order to default (modified to use localStorage)
    $('#wpca-reset-menu-order').click(function() {
        if (confirm(wpca_vars.reset_confirm)) {
            // Clear saved order from localStorage
            localStorage.removeItem('wpca_menu_order');
            localStorage.removeItem('wpca_hidden_items');
            // Reload the page to show default order
            location.reload();
        }
    });

    // Unified menu sorting for all levels
    $('#wpca-menu-order, .wpca-submenu-sortable').sortable({
        update: function(event, ui) {
            var $container = $(this);
            var isSubmenu = $container.hasClass('wpca-submenu-sortable');
            var menuOrder = [];
            
            $container.find('> li').each(function() {
                menuOrder.push($(this).data('menu-slug'));
            });
            
            if (isSubmenu) {
                var parentSlug = $container.data('parent-slug');
                localStorage.setItem('wpca_submenu_order_'+parentSlug, JSON.stringify(menuOrder));
            } else {
                localStorage.setItem('wpca_menu_order', JSON.stringify(menuOrder));
            }
        }
    });

    // ======================================================
    // Enhanced menu functionality
    // ======================================================
    
    // Handle toggle switch changes for all menu items with proper delegation
    $(document).off('change', '.wpca-slide-toggle input').on('change', '.wpca-slide-toggle input', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        var $switch = $(this);
        var $li = $switch.closest('li');
        var isChecked = $switch.prop('checked');
        
        // Toggle menu-hidden class based on checkbox state
        $li.toggleClass('menu-hidden', !isChecked);
        
        // Toggle child submenus as well
        $li.find('> ul li').each(function() {
            $(this).toggleClass('menu-hidden', !isChecked);
        });
        
        // Ensure switch state matches the class
        $switch.prop('checked', isChecked);
        
        // Update menu order and hidden status
        updateMenuOrder();
        
        // Debug log
        console.log('Switch toggled:', $switch.prop('checked'), 'for item:', $li.data('menu-slug'));
    });

    // Ensure switches work after drag-and-drop
    $('#wpca-menu-order, .wpca-submenu-sortable').on('sortstop', function() {
        $('.wpca-slide-toggle input').each(function() {
            var $li = $(this).closest('li');
            $(this).prop('checked', !$li.hasClass('menu-hidden'));
        });
    });

    // Initialize and refresh all toggle switches with state verification
    function refreshToggleSwitches() {
        $('.wpca-slide-toggle input').each(function() {
            var $switch = $(this);
            var $li = $switch.closest('li');
            var shouldBeChecked = !$li.hasClass('menu-hidden');
            
            // Only update if state differs
            if ($switch.prop('checked') !== shouldBeChecked) {
                $switch.prop('checked', shouldBeChecked);
                console.log('Switch state updated:', shouldBeChecked, 'for item:', $li.data('menu-slug'));
            }
        });
    }
    
    // Initialize on load
    refreshToggleSwitches();
    
    // Full hierarchical menu initialization with submenu support
    function initializeMenuHierarchy() {
        // First show all menu containers
        $('#wpca-menu-order, .wpca-submenu-sortable').css('display', 'block');
        
        // Process all menu items recursively
        function processMenuItem($item, level = 0) {
            var slug = $item.data('menu-slug');
            var isHidden = $item.hasClass('menu-hidden');
            var isSubmenu = $item.hasClass('submenu-item');
            var hasChildren = $item.find('> ul').length > 0;
            
            // Set item styling and visibility
            $item.css({
                'padding-left': (level * 25) + 'px',
                'display': 'block',
                'position': 'relative'
            }).show();
            
            // Mark submenu items explicitly
            if (level > 0) {
                $item.addClass('submenu-item level-' + level);
            }
            
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
            
            // Process child items if exists
            if (hasChildren) {
                $item.find('> ul').css({
                    'display': 'block',
                    'margin-left': '15px'
                }).show();
                
                $item.find('> ul > li').each(function() {
                    processMenuItem($(this), level + 1);
                });
                
                // Add expand/collapse icon for parent items
                if (!$item.find('.wpca-menu-expand').length) {
                    $item.append(`
                        <span class="dashicons dashicons-arrow-down wpca-menu-expand" 
                              style="position:absolute; right:30px; cursor:pointer;"></span>
                    `);
                }
            }
        }
        
        // Start processing from top level
        $('#wpca-menu-order > li').each(function() {
            processMenuItem($(this));
        });
        
        // Toggle child menu visibility
        $(document).off('click', '.wpca-menu-expand').on('click', '.wpca-menu-expand', function() {
            $(this).toggleClass('dashicons-arrow-down dashicons-arrow-up')
                   .siblings('ul').toggle();
        });
        

    }
    
    // Initialize menu with enhanced event handling
    function initMenuSystem() {
        initializeMenuHierarchy();
        refreshToggleSwitches();
        
        // Add click handler for slider track
        $(document).off('click', '.wpca-slide-toggle .slider').on('click', '.wpca-slide-toggle .slider', function(e) {
            e.preventDefault();
            var $switch = $(this).siblings('input');
            $switch.trigger('click');
        });
    }
    
    // Initialize on load and after sorting
    initMenuSystem();
    $('#wpca-menu-order').on('sortupdate', initMenuSystem);

    // Update menu order and hidden status (without form submission)
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
        
        // Store in localStorage instead of form fields
        localStorage.setItem('wpca_menu_order', JSON.stringify(menuOrder));
        localStorage.setItem('wpca_hidden_items', JSON.stringify(hiddenItems));
    }

    // ======================================================
    // General admin functionality (from wp-clean-admin.js)
    // ======================================================
    
    // Future JavaScript interactions will go here.
    // For example, toggling elements, handling live previews in settings, etc.

    // console.log('WP Clean Admin JS Loaded');
});