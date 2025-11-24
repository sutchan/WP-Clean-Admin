/**
 * WP Clean Admin - Settings Module
 * Handles settings page functionality including color pickers, media uploaders, and menu ordering
 * 
 * @file       wpcleanadmin/assets/js/wpca-settings.js
 * @version    1.7.13
 * @updated    2025-06-18
 */

jQuery(document).ready(function($) {
    // 触发选项卡初始化事件
    $(document).trigger('wpca.init.tabs');
    
    // Initialize color pickers
    if ($.fn.wpColorPicker) {
        $('.wpca-color-picker').wpColorPicker();
    }
    
    // Color picker interaction
    $(document).on('click', '.wpca-color-preset', function() {
        var color = $(this).data('color');
        var colorPicker = $(this).closest('.wpca-color-picker-wrap').find('.wpca-color-picker');
        var colorValue = $(this).closest('.wpca-color-picker-wrap').find('.wpca-color-value');
        var previewBox = $(this).closest('.wpca-color-picker-wrap').find('.wpca-color-preview');
        
        // Update color picker value
        colorPicker.val(color).trigger('input');
        
        // Update color value text
        colorValue.text(color);
        
        // Update preview box
        previewBox.css('background-color', color);
        
        // Update wpColorPicker if initialized
        if (colorPicker.hasClass('wp-color-picker')) {
            colorPicker.wpColorPicker('color', color);
        }
    });
    
    // Update color preview when input changes
    $(document).on('input', '.wpca-color-picker', function() {
        var color = $(this).val();
        var colorValue = $(this).closest('.wpca-color-picker-wrap').find('.wpca-color-value');
        var previewBox = $(this).closest('.wpca-color-picker-wrap').find('.wpca-color-preview');
        
        // Update color value text
        colorValue.text(color);
        
        // Update preview box
        previewBox.css('background-color', color);
    });
    
    // Initialize color values and previews on page load
    $('.wpca-color-picker-wrap').each(function() {
        var colorPicker = $(this).find('.wpca-color-picker');
        var colorValue = $(this).find('.wpca-color-value');
        var previewBox = $(this).find('.wpca-color-preview');
        var color = colorPicker.val();
        
        // Set initial color value text
        colorValue.text(color);
        
        // Set initial preview box color
        previewBox.css('background-color', color);
    });

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
        // Trigger login preview update (assuming this function exists in wpca-login.js or wpca-main.js)
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