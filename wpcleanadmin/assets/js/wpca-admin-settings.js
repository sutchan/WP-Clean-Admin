jQuery(document).ready(function($) {
    // Tab switching functionality
    $('.wpca-tab').on('click', function() {
        var tabId = $(this).data('tab');
        $('.wpca-tab').removeClass('active');
        $(this).addClass('active');
        $('.wpca-tab-content').removeClass('active');
        $('#' + tabId).addClass('active');
        $('#wpca-current-tab').val(tabId);
    });

    // Menu order functionality
    $('#wpca-menu-order').sortable({
        handle: '.dashicons-menu',
        placeholder: 'wpca-menu-item-placeholder',
        update: function() {
            $(this).find('input[name="wpca_settings[menu_order][]"]').each(function(index) {
                $(this).val($(this).closest('li').data('menu-slug'));
            });
        }
    });

    // Login page preview functionality
    function updateLoginPreview(style) {
        var preview = $('.wpca-login-preview-content');
        preview.removeClass('default-preview modern-preview minimal-preview dark-preview gradient-preview custom-preview');
        preview.addClass(style + '-preview');

        switch(style) {
            case 'default':
                preview.find('.wpca-login-preview-logo').css('background-image', 'url(data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 84 84"><path fill="%232271b1" d="M42,0C18.8,0,0,18.8,0,42s18.8,42,42,42s42-18.8,42-42S65.2,0,42,0z M42,64c-12.2,0-22-9.8-22-22s9.8-22,22-22 s22,9.8,22,22S54.2,64,42,64z"/></svg>)');
                preview.css('background-color', '#f1f1f1');
                break;
            case 'modern':
                preview.find('.wpca-login-preview-logo').css('background-image', 'url(data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 84 84"><path fill="%234A90E2" d="M42,0C18.8,0,0,18.8,0,42s18.8,42,42,42s42-18.8,42-42S65.2,0,42,0z M42,64c-12.2,0-22-9.8-22-22s9.8-22,22-22 s22,9.8,22,22S54.2,64,42,64z"/></svg>)');
                preview.css('background-color', '#f8f9fa');
                break;
            case 'minimal':
                preview.find('.wpca-login-preview-logo').css('background-image', '');
                preview.css('background-color', '#fff');
                break;
            case 'dark':
                preview.find('.wpca-login-preview-logo').css('background-image', 'url(data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 84 84"><path fill="%23fff" d="M42,0C18.8,0,0,18.8,0,42s18.8,42,42,42s42-18.8,42-42S65.2,0,42,0z M42,64c-12.2,0-22-9.8-22-22s9.8-22,22-22 s22,9.8,22,22S54.2,64,42,64z"/></svg>)');
                preview.css('background-color', '#222');
                break;
            case 'gradient':
                preview.find('.wpca-login-preview-logo').css('background-image', 'url(data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 84 84"><path fill="%23fff" d="M42,0C18.8,0,0,18.8,0,42s18.8,42,42,42s42-18.8,42-42S65.2,0,42,0z M42,64c-12.2,0-22-9.8-22-22s9.8-22,22-22 s22,9.8,22,22S54.2,64,42,64z"/></svg>)');
                preview.css('background', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)');
                break;
            case 'custom':
                var logoUrl = $('input[name="wpca_settings[login_logo]"]').val();
                var bgUrl = $('input[name="wpca_settings[login_background]"]').val();
                
                if (logoUrl) {
                    preview.find('.wpca-login-preview-logo').css('background-image', 'url(' + logoUrl + ')');
                } else {
                    preview.find('.wpca-login-preview-logo').css('background-image', '');
                }
                
                if (bgUrl) {
                    preview.css('background-image', 'url(' + bgUrl + ')');
                } else {
                    preview.css('background-image', '');
                }
                break;
        }
    }

    $('input[name="wpca_settings[login_style]"]').on('change', function() {
        updateLoginPreview($(this).val());
    });

    // Media uploader functionality
    $('.wpca-upload-button').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var targetId = button.data('target');
        var field = $('#' + targetId);
        var preview = $('#' + targetId + '-preview');

        var frame = wp.media({
            title: wpca_admin.media_title,
            button: { text: wpca_admin.media_button },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            field.val(attachment.url);
            preview.find('img').attr('src', attachment.url);
            preview.show();
        });

        frame.open();
    });

    $('.wpca-remove-button').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var targetId = button.data('target');
        var field = $('#' + targetId);
        var preview = $('#' + targetId + '-preview');
        
        field.val('');
        preview.hide();
    });

    // Initialize
    updateLoginPreview($('input[name="wpca_settings[login_style]"]:checked').val());
});