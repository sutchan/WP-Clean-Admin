(function ($) {
    'use strict';

    // Initialize plugin
    $(document).ready(function () {
        // Load configuration
        const wpcaSettings = window.wpca_admin || {};
        const ajaxUrl = wpcaSettings.ajax_url || '';
        const nonce = wpcaSettings.nonce || '';

        // Initialize dashboard
        initDashboard();

        // Bind events
        bindEvents();

        /**
         * Initialize dashboard
         */
        function initDashboard() {
            console.log('WP Clean Admin Dashboard initialized.');
            // Load default configuration
            loadSettings();
        }

        /**
         * Bind events
         */
        function bindEvents() {
            // Save settings button
            $('#wpca-save-settings').on('click', saveSettings);

            // Reset settings button
            $('#wpca-reset-settings').on('click', resetSettings);
        }

        /**
         * Load settings
         */
        function loadSettings() {
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpca_get_settings',
                    _wpnonce: nonce
                },
                success: function (response) {
                    if (response.success) {
                        updateUI(response.data);
                    } else {
                        console.error('Failed to load settings:', response.data);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }

        /**
         * Save settings
         */
        function saveSettings() {
            const settings = {
                // Example: Collect form data
                menu_order: $('#wpca-menu-order').val(),
                menu_toggles: $('#wpca-menu-toggles').val()
            };

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpca_save_settings',
                    _wpnonce: nonce,
                    settings: JSON.stringify(settings)
                },
                success: function (response) {
                    if (response.success) {
                        alert('Settings saved successfully!');
                    } else {
                        console.error('Failed to save settings:', response.data);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }

        /**
         * Reset settings
         */
        function resetSettings() {
            if (confirm('Are you sure you want to reset all settings to default?')) {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'wpca_reset_settings',
                        _wpnonce: nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            alert('Settings reset successfully!');
                            loadSettings();
                        } else {
                            console.error('Failed to reset settings:', response.data);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX error:', error);
                    }
                });
            }
        }

        /**
         * Update UI
         * @param {Object} data Configuration data
         */
        function updateUI(data) {
            // Example: Update menu order
            if (data.menu_order) {
                $('#wpca-menu-order').val(data.menu_order.join(','));
            }

            // Example: Update menu toggle status
            if (data.menu_toggles) {
                Object.keys(data.menu_toggles).forEach(function (key) {
                    $(`#wpca-toggle-${key}`).prop('checked', data.menu_toggles[key] === 1);
                });
            }
        }
    });
})(jQuery);