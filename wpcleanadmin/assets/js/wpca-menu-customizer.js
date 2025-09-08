jQuery(document).ready(function($) {
    // Initialize submenu states from saved data
    if (wpcaMenuData.submenuStates) {
        $.each(wpcaMenuData.submenuStates, function(menuSlug, expanded) {
            var $menuItem = $('.menu-item[data-menu-slug=\"' + menuSlug + '\"]');
            if ($menuItem.length) {
                $menuItem.find('.submenu-items').toggleClass('expanded', expanded);
                $menuItem.find('.toggle-submenu').toggleClass(
                    'dashicons-arrow-down dashicons-arrow-right', 
                    expanded
                );
            }
        });
    }

    // Initialize nested sortable for menu items
    $('.wpca-menu-sortable').sortable({
        handle: '.menu-item-handle',
        placeholder: 'menu-item-placeholder',
        tolerance: 'pointer',
        update: function(event, ui) {
            saveMenuOrder();
        }
    });

    // Initialize submenu sortable
    $('.wpca-submenu-sortable').sortable({
        handle: '.submenu-item-handle',
        connectWith: '.wpca-submenu-sortable',
        placeholder: 'submenu-item-placeholder',
        tolerance: 'pointer',
        update: function(event, ui) {
            saveMenuOrder();
        }
    });

    // Toggle menu item visibility
    $(document).on('click', '.toggle-menu-visibility', function(e) {
        e.preventDefault();
        var $button = $(this);
        var $item = $button.closest('li');
        var menuSlug = $item.data('menu-slug');
        
        $.post(wpcaMenuData.ajaxUrl, {
            action: 'wpca_toggle_menu_item',
            menu_slug: menuSlug,
            hidden: !$button.hasClass('dashicons-hidden'),
            nonce: wpcaMenuData.nonce
        }, function(response) {
            if (response.success) {
                $button.toggleClass('dashicons-hidden dashicons-visibility');
                $item.find('.menu-item-title').toggleClass('hidden-item');
                
                // Update status text based on locale
                var statusText = response.data.hidden ? 
                    (wpcaMenuData.locale === 'zh_CN' ? '(已隐藏)' : '(Hidden)') : 
                    '';
                $item.find('.menu-status').text(statusText);
            }
        });
    });

    // Toggle submenu visibility and save state
    $(document).on('click', '.toggle-submenu', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $button = $(this);
        var $menuItem = $button.closest('.menu-item');
        var menuSlug = $menuItem.data('menu-slug');
        var $submenuItems = $menuItem.find('.submenu-items');
        
        if ($submenuItems.length === 0) {
            console.error('Submenu items container not found for menu:', menuSlug);
            return;
        }

        // Toggle UI state with animation
        $submenuItems.stop(true, true).slideToggle(200, function() {
            var isExpanded = $(this).is(':visible');
            $button.toggleClass('dashicons-arrow-down dashicons-arrow-right', isExpanded);
            
            // Save submenu visibility state
            $.post(wpcaMenuData.ajaxUrl, {
                action: 'wpca_toggle_submenu',
                menu_slug: menuSlug,
                expanded: isExpanded,
                nonce: wpcaMenuData.nonce
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Failed to save submenu state:', textStatus, errorThrown);
            });
        });
    });

    // Initialize submenu states - force all submenus to be hidden by default
    function initSubmenuStates() {
        // First hide ALL submenus and set arrows to right
        $('.submenu-items').hide();
        $('.toggle-submenu').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-right');
        
        // Then apply saved expanded states if they exist
        if (wpcaMenuData.submenuStates) {
            $.each(wpcaMenuData.submenuStates, function(menuSlug, isExpanded) {
                var $menuItem = $('.menu-item[data-menu-slug="' + menuSlug + '"]');
                if ($menuItem.length) {
                    $menuItem.find('.submenu-items').toggle(isExpanded);
                    $menuItem.find('.toggle-submenu')
                        .toggleClass('dashicons-arrow-down', isExpanded)
                        .toggleClass('dashicons-arrow-right', !isExpanded);
                }
            });
        }
    }

    // Initialize on DOM ready
    initSubmenuStates();

    // Save menu order to hidden fields
    function saveMenuOrder() {
        var menuOrder = {};
        
        $('.wpca-menu-sortable > li').each(function() {
            var slug = $(this).data('menu-slug');
            menuOrder[slug] = [];
            
            $(this).find('.submenu-items li').each(function() {
                var subSlug = $(this).data('menu-slug');
                menuOrder[slug].push(subSlug);
            });
        });

        $('#wpca_menu_order').val(JSON.stringify(menuOrder));
        $('#wpca_submenu_order').val(JSON.stringify(submenuOrder));
    }

    // Handle AJAX save
    $('#wpca-save-menu-order').on('click', function(e) {
        e.preventDefault();
        
        $.post(wpcaMenuData.ajaxUrl, {
            action: 'wpca_save_menu_order',
            menu_order: $('#wpca_menu_order').val(),
            submenu_order: $('#wpca_submenu_order').val(),
            nonce: wpcaMenuData.nonce
        }, function(response) {
            if (response.success) {
                alert('Menu order saved successfully');
            } else {
                alert('Error saving menu order');
            }
        });
    });

    // Robust reset button handler with namespace and debounce
    $(document).off('click.wpcaReset').on('click.wpcaReset', '#wpca-reset-menu-order', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        var $button = $(this);
        if ($button.hasClass('processing')) return;
        
        // Disable button during request
        $button.addClass('processing').prop('disabled', true);
        
        // Localized messages
        var messages = {
            confirm: wpcaMenuData.locale === 'zh_CN' ? 
                '确定要重置所有菜单自定义设置吗？' : 
                'Are you sure you want to reset all menu customizations?',
            success: wpcaMenuData.locale === 'zh_CN' ? 
                '菜单顺序已重置' : 'Menu order has been reset',
            error: wpcaMenuData.locale === 'zh_CN' ? 
                '重置菜单顺序时出错' : 'Error resetting menu order'
        };
        
        if (confirm(messages.confirm)) {
            $.ajax({
                url: wpcaMenuData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpca_reset_menu_order',
                    nonce: wpcaMenuData.nonce
                },
                complete: function() {
                    $button.removeClass('processing').prop('disabled', false);
                },
                success: function(response) {
                    if (response.success) {
                        alert(messages.success);
                        location.reload();
                    } else {
                        alert(messages.error);
                    }
                },
                error: function() {
                    alert(messages.error);
                }
            });
        } else {
            $button.removeClass('processing').prop('disabled', false);
        }
    });
});