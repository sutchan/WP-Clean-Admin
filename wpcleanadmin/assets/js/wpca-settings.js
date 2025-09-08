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

    // ======================================================
    // Menu sorting functionality
    // ======================================================

    // Reset menu order to default with enhanced functionality
    $('#wpca-reset-menu-order').click(function() {
        if (confirm('Are you sure you want to reset all menu settings to default?')) {
            // Save current active tab
            var currentTab = $('.wpca-tab.active').data('tab');
            
            // Clear all related localStorage items
            localStorage.removeItem('wpca_menu_order');
            localStorage.removeItem('wpca_hidden_items');
            
            // Clear any submenu orders - collect keys first to avoid index shifting
            var keysToRemove = [];
            for (var i = 0; i < localStorage.length; i++) {
                var key = localStorage.key(i);
                if (key && key.startsWith('wpca_submenu_order_')) {
                    keysToRemove.push(key);
                }
            }
            
            // Now remove the collected keys
            keysToRemove.forEach(function(key) {
                localStorage.removeItem(key);
            });
            
            // Show all menu items in the admin menu
            $('#adminmenu li').show();
            $('#adminmenu .wp-submenu').show();
            
            console.log('All menu settings reset to default');
            
            // Reinitialize menu system to reflect changes
            initMenuSystem();
            
            // Restore current tab
            if (currentTab) {
                $(".wpca-tab, .wpca-tab-content").removeClass("active");
                $(`.wpca-tab[data-tab="${currentTab}"]`).addClass("active");
                $(`#${currentTab}`).addClass("active");
            }
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
    
    // Full hierarchical menu initialization with submenu support
    function initializeMenuHierarchy() {
        // First show all menu containers
        $('#wpca-menu-order, .wpca-submenu-sortable').css('display', 'block');
        
        // Process all menu items recursively
        function processMenuItem($item, level = 0) {
            var slug = $item.data('menu-slug');
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
            
            // Ensure only one toggle switch exists per menu item
            if (!$item.find('.wpca-horizontal-toggle').length) {
                $item.find('.menu-item-handle').append(`
                    <div class="wpca-horizontal-toggle">
                        <input type="checkbox" id="toggle-${slug}" class="toggle-input">
                        <label for="toggle-${slug}" class="toggle-slider">
                            <span class="toggle-handle"></span>
                        </label>
                    </div>
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
                              style="position:absolute; right:40px; cursor:pointer;"></span>
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
        
        // Initialize on load and after sorting
        $('#wpca-menu-order').on('sortupdate', initMenuSystem);
    }

    // ======================================================
    // Layout & Typography Settings
    // ======================================================
    
    // Initialize on load
    initMenuSystem();
});