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
            
            // Reset all toggle switches to checked (visible)
            $('.wpca-slide-toggle input').prop('checked', true);
            
            // Remove menu-hidden class from all items
            $('#wpca-menu-order li').removeClass('menu-hidden');
            
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
    
    // Handle toggle switch changes with persistent state and apply to WP admin menu
    $(document).off('change', '.wpca-slide-toggle input').on('change', '.wpca-slide-toggle input', function(e) {
        e.stopPropagation();
        var $switch = $(this);
        var $li = $switch.closest('li');
        var slug = $li.data('menu-slug');
        var isChecked = $switch.prop('checked');
        
        // Update visual state immediately
        $li.toggleClass('menu-hidden', !isChecked);
        $li.find('> ul li').toggleClass('menu-hidden', !isChecked);
        
        // Save state to localStorage
        var hiddenItems = JSON.parse(localStorage.getItem('wpca_hidden_items') || '[]');
        if (isChecked) {
            hiddenItems = hiddenItems.filter(item => item !== slug);
        } else {
            if (!hiddenItems.includes(slug)) {
                hiddenItems.push(slug);
            }
        }
        localStorage.setItem('wpca_hidden_items', JSON.stringify(hiddenItems));
        
        // Apply to actual WP admin menu with improved selector
        applyMenuVisibility(slug, isChecked);
        
        console.log('Switch state saved and applied:', isChecked, 'for:', slug);
    });

    // Ensure switches work after drag-and-drop
    $('#wpca-menu-order, .wpca-submenu-sortable').on('sortstop', function() {
        $('.wpca-slide-toggle input').each(function() {
            var $li = $(this).closest('li');
            $(this).prop('checked', !$li.hasClass('menu-hidden'));
        });
    });

    // Initialize switches with saved state and apply to WP admin menu
    function refreshToggleSwitches() {
        var hiddenItems = JSON.parse(localStorage.getItem('wpca_hidden_items') || '[]');
        
        $('.wpca-slide-toggle input').each(function() {
            var $switch = $(this);
            var $li = $switch.closest('li');
            var slug = $li.data('menu-slug');
            var shouldBeChecked = !hiddenItems.includes(slug);
            
            $switch.prop('checked', shouldBeChecked);
            $li.toggleClass('menu-hidden', !shouldBeChecked);
            
            // Apply to actual WP admin menu with improved selector
            applyMenuVisibility(slug, shouldBeChecked);
            
            console.log('Switch initialized and applied:', shouldBeChecked, 'for:', slug);
        });
    }
    

    
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
                            ${!isHidden ? 'checked' : ''}>
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

    // Helper function to apply menu visibility with comprehensive selectors
    function applyMenuVisibility(slug, isVisible) {
        // Try multiple selector strategies
        var $wpMenu;
        var found = false;
        
        // Map common menu slugs to their actual menu IDs/classes
        var menuMap = {
            'posts': 'menu-posts',
            'post': 'menu-posts',
            'pages': 'menu-pages',
            'page': 'menu-pages',
            'media': 'menu-media',
            'upload': 'menu-media',
            'site-kit': 'toplevel_page_googlesitekit-dashboard',
            'googlesitekit': 'toplevel_page_googlesitekit-dashboard'
        };
        
        // Check if we have a mapping for this slug
        var mappedSlug = menuMap[slug] || slug;
        
        // Strategy 1: Match by menu ID (most reliable)
        $wpMenu = $('#' + mappedSlug);
        if ($wpMenu.length) {
            found = true;
            console.log('Found by ID:', mappedSlug);
        }
        
        // Strategy 2: Match by menu class
        if (!found) {
            $wpMenu = $('#adminmenu li.' + mappedSlug);
            if ($wpMenu.length) {
                found = true;
                console.log('Found by class:', mappedSlug);
            }
        }
        
        // Strategy 3: Direct match by slug in URL
        if (!found) {
            $wpMenu = $('#adminmenu a[href$="=' + slug + '"]').closest('li.menu-top');
            if ($wpMenu.length) {
                found = true;
                console.log('Found by URL exact match:', slug);
            }
        }
        
        // Strategy 4: Partial match in URL
        if (!found) {
            $wpMenu = $('#adminmenu a[href*="' + slug + '"]').closest('li.menu-top');
            if ($wpMenu.length) {
                found = true;
                console.log('Found by URL partial match:', slug);
            }
        }
        
        // Strategy 5: Text content match
        if (!found) {
            $('#adminmenu li.menu-top').each(function() {
                var menuText = $(this).text().toLowerCase();
                if (menuText.indexOf(slug.toLowerCase()) !== -1) {
                    $wpMenu = $(this);
                    found = true;
                    console.log('Found by text content:', slug);
                    return false; // Break the loop
                }
            });
        }
        
        // Special handling for common menu items
        if (!found) {
            // Posts
            if (slug === 'posts' || slug === 'post') {
                $wpMenu = $('#menu-posts');
                found = true;
            }
            // Pages
            else if (slug === 'pages' || slug === 'page') {
                $wpMenu = $('#menu-pages');
                found = true;
            }
            // Media
            else if (slug === 'media' || slug === 'upload') {
                $wpMenu = $('#menu-media');
                found = true;
            }
            // Site Kit
            else if (slug.indexOf('site-kit') !== -1 || slug.indexOf('googlesitekit') !== -1) {
                $wpMenu = $('li[id*="googlesitekit"]');
                found = true;
            }
            
            if (found) {
                console.log('Found by special case handling:', slug);
            }
        }
        
        // Apply visibility if found
        if (found && $wpMenu.length) {
            $wpMenu.toggle(isVisible);
            $wpMenu.find('.wp-submenu').toggle(isVisible);
            console.log('Menu visibility applied:', isVisible, 'for:', slug, 'Found:', $wpMenu.length);
        } else {
            console.log('Menu item not found for slug:', slug);
            
            // Last resort: try to find by text content in any menu item
            $('#adminmenu li').each(function() {
                var menuText = $(this).text().toLowerCase();
                if (menuText.indexOf(slug.toLowerCase()) !== -1) {
                    $(this).toggle(isVisible);
                    console.log('Last resort visibility applied to:', $(this).text());
                }
            });
        }
        
        // Special handling for dashboard
        if (slug === 'dashboard' || slug === 'index.php') {
            $('#adminmenu li.menu-top:first').toggle(isVisible);
        }
    }
    
    // ======================================================
    // Menu Icon Visibility Control
    // ======================================================
    
    // Add icon toggle control at the end of menu management section
    $('#wpca-menu-order').after(`
        <div class="wpca-icon-toggle-container" style="margin-top: 20px; padding: 10px; background: #f5f5f5;">
            <label class="wpca-slide-toggle">
                <input type="checkbox" id="wpca-toggle-menu-icons" name="wpca_settings[show_menu_icons]">
                <span class="slider">
                    <span class="slide-handle"></span>
                </span>
                <span style="margin-left: 10px;">显示菜单图标</span>
            </label>
        </div>
    `);

    // Initialize icon toggle state
    var showIcons = localStorage.getItem('wpca_show_menu_icons') !== 'false';
    $('#wpca-toggle-menu-icons').prop('checked', showIcons);
    toggleMenuIcons(showIcons);

    // Handle icon toggle changes
    $('#wpca-toggle-menu-icons').change(function() {
        var isChecked = $(this).prop('checked');
        localStorage.setItem('wpca_show_menu_icons', isChecked);
        toggleMenuIcons(isChecked);
    });

    // Function to toggle menu icons visibility
    function toggleMenuIcons(show) {
        if (show) {
            $('#adminmenu .wp-menu-image').show();
        } else {
            $('#adminmenu .wp-menu-image').hide();
        }
    }

    // ======================================================
    // End of WP Clean Admin JS
    // ======================================================
});