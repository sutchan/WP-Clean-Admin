(function ($) {
    'use strict';

    // 初始化插件
    $(document).ready(function () {
        // 加载配置
        const wpcaSettings = window.wpca_admin || {};
        const ajaxUrl = wpcaSettings.ajax_url || '';
        const nonce = wpcaSettings.nonce || '';

        // 初始化仪表盘
        initDashboard();

        // 绑定事件
        bindEvents();

        /**
         * 初始化仪表盘
         */
        function initDashboard() {
            console.log('WP Clean Admin Dashboard initialized.');
            // 加载默认配置
            loadSettings();
        }

        /**
         * 绑定事件
         */
        function bindEvents() {
            // 保存设置按钮
            $('#wpca-save-settings').on('click', saveSettings);

            // 重置设置按钮
            $('#wpca-reset-settings').on('click', resetSettings);
        }

        /**
         * 加载设置
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
         * 保存设置
         */
        function saveSettings() {
            const settings = {
                // 示例：收集表单数据
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
         * 重置设置
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
         * 更新 UI
         * @param {Object} data 配置数据
         */
        function updateUI(data) {
            // 示例：更新菜单顺序
            if (data.menu_order) {
                $('#wpca-menu-order').val(data.menu_order.join(','));
            }

            // 示例：更新菜单开关状态
            if (data.menu_toggles) {
                Object.keys(data.menu_toggles).forEach(function (key) {
                    $(`#wpca-toggle-${key}`).prop('checked', data.menu_toggles[key] === 1);
                });
            }
        }
    });
})(jQuery);