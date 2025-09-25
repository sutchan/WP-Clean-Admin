(function($) {
    'use strict';

    window.WPCA = window.WPCA || {};
    window.WPCA.permissions = {
        userCaps: {},
        init: function() {
            if (typeof wpca_admin !== 'undefined' && wpca_admin.user_capabilities) {
                this.userCaps = wpca_admin.user_capabilities;
            }
            this.setupUI();
            if (window.WPCA.core) {
                window.WPCA.core.hasPermission = this.hasPermission.bind(this);
                window.WPCA.core.checkPermission = this.checkPermission.bind(this);
            }
        },
        hasPermission: function(capability) {
            if (capability === 'manage_options' && this.userCaps.can_manage_options === true) {
                return true;
            }
            if (this.userCaps[capability] === true) {
                return true;
            }
            return false;
        },
        checkPermission: function(capability, callback) {
            if (typeof wpca_admin === 'undefined' || !wpca_admin.ajaxurl || !wpca_admin.nonce) {
                if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.logError === 'function') {
                    WPCA.core.logError('WPCA Permissions: Missing required configuration');
                } else {
                    console.error('WPCA Permissions: Missing required configuration');
                }
                callback(false);
                return;
            }
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_check_permission',
                    capability: capability,
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        callback(response.data.has_permission);
                    } else {
                        if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.logWarning === 'function') {
                            WPCA.core.logWarning('WPCA Permissions: Permission check failed -', response.data?.message || 'Unknown error');
                        } else {
                            console.warn('WPCA Permissions: Permission check failed -', response.data?.message || 'Unknown error');
                        }
                        callback(false);
                    }
                },
                error: function(xhr, status, error) {
                    if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.logError === 'function') {
                        WPCA.core.logError('WPCA Permissions: AJAX error -', error);
                    } else {
                        console.error('WPCA Permissions: AJAX error -', error);
                    }
                    callback(false);
                }
            });
        },
        setupUI: function() {
            $('.wpca-requires-permission').each(function() {
                var $element = $(this);
                var requiredPermission = $element.data('permission');
                if (requiredPermission && !window.WPCA.permissions.hasPermission(requiredPermission)) {
                    $element.hide();
                }
            });
            $('.wpca-button-requires-permission').each(function() {
                var $button = $(this);
                var requiredPermission = $button.data('permission');
                if (requiredPermission && !window.WPCA.permissions.hasPermission(requiredPermission)) {
                    $button.prop('disabled', true)
                           .addClass('wpca-disabled')
                           .attr('title', wpca_admin.error_insufficient_permissions || '权限不足');
                }
            });
        }
    };
    $(document).ready(function() {
        if ($('#wpcontent').length > 0) {
            window.WPCA.permissions.init();
        }
    });
})(jQuery);