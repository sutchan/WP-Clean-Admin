jQuery(document).ready(function($) {
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

    // Toggle submenu visibility
    $(document).on('click', '.toggle-submenu', function(e) {
        e.preventDefault();
        $(this).closest('.menu-item').find('.submenu-items').toggleClass('expanded');
        $(this).toggleClass('dashicons-arrow-down dashicons-arrow-right');
    });

    // Save menu order to hidden fields
    function saveMenuOrder() {
        var menuOrder = {};
        var submenuOrder = {};
        
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
});