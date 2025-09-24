jQuery(document).ready(function($) {
    // 触发选项卡初始化事件
    $(document).trigger('wpca.init.tabs');
    
    // Initialize color pickers
    if ($.fn.wpColorPicker) {
        $('.wpca-color-picker').wpColorPicker();
    }

    // Media Uploader functionality
    $(document).on('click', '.wpca-upload-button', function(e) {
        e.preventDefault();
        var button = $(this);
        var targetField = $('#' + button.data('target'));
        var previewDiv = $('#' + button.data('target') + '-preview');

        if (typeof wp === 'undefined' || !wp.media) {
            if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.showNotice === 'function') {
                WPCA.core.showNotice('error', wpca_admin.media_unavailable || 'WordPress media uploader not available.');
            } else {
                alert(wpca_admin.media_unavailable || 'WordPress media uploader not available.');
            }
            return;
        }

        var customUploader = wp.media({
            title: wpca_admin.media_title,
            library: {
                type: 'image'
            },
            button: {
                text: wpca_admin.media_button
            },
            multiple: false
        }).on('select', function() {
            var attachment = customUploader.state().get('selection').first().toJSON();
            targetField.val(attachment.url);
            previewDiv.html('<img src="' + attachment.url + '" alt="Preview" style="max-width:100%; height:auto;">').show();
        }).open();
    });

    $(document).on('click', '.wpca-remove-button', function(e) {
        e.preventDefault();
        var button = $(this);
        var targetField = $('#' + button.data('target'));
        var previewDiv = $('#' + button.data('target') + '-preview');
        targetField.val('');
        previewDiv.hide().html('');
    });

    // Menu Ordering Sortable
    $("#wpca-menu-order-list").sortable({
        axis: "y",
        handle: ".dashicons-menu",
        items: "li",
        placeholder: "ui-state-highlight",
        forcePlaceholderSize: true,
        update: function(event, ui) {
            // Update hidden input fields for menu order
            $(this).find('input[name="wpca_settings[menu_order][]"]').each(function(index) {
                $(this).val($(this).closest("li").data("menu-slug"));
            });
        }
    });

    // Initialize menu items with saved visibility states
    $(".wpca-menu-toggle-switch input[type=checkbox]").each(function() {
        var isVisible = $(this).is(":checked");
        $(this).closest(".wpca-menu-toggle-switch").toggleClass("checked", isVisible);
    });

    // Update menu visibility based on main toggle
    function updateMenuVisibility(isEnabled) {
        $(".wpca-menu-sortable, .wpca-menu-order-header").toggle(isEnabled);
        if (!isEnabled) {
            // If main toggle is disabled, uncheck all individual toggles
            $(".wpca-menu-toggle-switch input[type=checkbox]")
                .prop("checked", false)
                .trigger("change")
                .closest(".wpca-menu-toggle-switch")
                .removeClass("checked");
        }
    }

    // Handle main menu toggle
    $("#wpca-menu-toggle").on("change", function() {
        var isEnabled = $(this).is(":checked");
        updateMenuVisibility(isEnabled);
        $(this).closest(".wpca-toggle-switch").toggleClass("checked", isEnabled);
    }).trigger("change"); // Initialize on load

    // Handle individual menu item toggles
    $(document).on("change", ".wpca-menu-toggle-switch input[type=checkbox]", function() {
        var isChecked = $(this).is(":checked");
        $(this).closest(".wpca-menu-toggle-switch").toggleClass("checked", isChecked);
    });

    // Reset Menu Order button functionality
    $("#wpca-reset-menu-order").on("click", function() {
        if (confirm(wpca_admin.reset_confirm)) {
            var $button = $(this);
            var originalText = $button.html();
            
            // Show loading state
            $button.html('<span class="dashicons dashicons-update spin" style="vertical-align: middle; margin-right: 5px;"></span> ' + wpca_admin.resetting_text);
            
            // Simulate AJAX reset or perform client-side reset
            setTimeout(function() {
                // Reset visibility to default (all enabled)
                $(".wpca-menu-toggle-switch input[type=checkbox]").prop("checked", true)
                    .trigger("change")
                    .closest(".wpca-menu-toggle-switch")
                    .addClass("checked");
                
                // Reset menu order to default WordPress order (alphabetical by slug for simplicity)
                var $sortable = $("#wpca-menu-order-list");
                $sortable.find("li").sort(function(a, b) {
                    return $(a).data("menu-slug").localeCompare($(b).data("menu-slug"));
                }).appendTo($sortable);
                
                // Update the hidden fields with new order
                $sortable.find("input[name='wpca_settings[menu_order][]']").each(function(index) {
                    $(this).val($(this).closest("li").data("menu-slug"));
                });
                
                // Also reset the main toggle
                $("#wpca-menu-toggle").prop("checked", true).trigger("change");
                
                // Restore button text
                $button.html(originalText);
                
                // Show success notice
                if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.showNotice === 'function') {
                    WPCA.core.showNotice('success', wpca_admin.reset_text + ' ' + wpca_admin.reset_successful_text);
                } else {
                    alert(wpca_admin.reset_text + ' ' + wpca_admin.reset_successful_text);
                }
            }, 500);
        }
    });

    // Login Page Style Selector
    $('input[name="wpca_settings[login_style]"]').on('change', function() {
        $('.wpca-login-style-item').removeClass('active');
        $(this).closest('.wpca-login-style-item').addClass('active');
        
        // Show/hide custom options
        if ($(this).val() === 'custom') {
            $('#wpca-custom-login-options').slideDown();
        } else {
            $('#wpca-custom-login-options').slideUp();
        }
        // Trigger login preview update (assuming this function exists in wpca-login.js or wpca-core.js)
        if (typeof WPCA !== 'undefined' && WPCA.loginPage && WPCA.loginPage.updatePreview) {
            WPCA.loginPage.updatePreview();
        }
    });

    // Initial check for custom login options visibility
    if ($('input[name="wpca_settings[login_style]"]:checked').val() === 'custom') {
        $('#wpca-custom-login-options').show();
    } else {
        $('#wpca-custom-login-options').hide();
    }

    // Trigger login preview update on relevant field changes
    $('#wpca-login-logo, #wpca-login-background, textarea[name="wpca_settings[login_custom_css]"]').on('change keyup', function() {
        if (typeof WPCA !== 'undefined' && WPCA.loginPage && WPCA.loginPage.updatePreview) {
            WPCA.loginPage.updatePreview();
        }
    });
    // Initial login preview update
    if (typeof WPCA !== 'undefined' && WPCA.loginPage && WPCA.loginPage.updatePreview) {
        WPCA.loginPage.updatePreview();
    }
});