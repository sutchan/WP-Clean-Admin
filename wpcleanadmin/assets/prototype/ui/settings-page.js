/**
 * WP Clean Admin 设置页前端原型（标准 AJAX 用法）
 *
 * 规范要点：
 *  - nonce 字段名固定为 _wpnonce（与后端 AJAX_Gateway_Base 一致）
 *  - 通过 FormData 自动携带 _wpnonce 隐藏字段
 *  - 统一错误处理与状态反馈
 */
(function () {
    'use strict';

    const form = document.getElementById('wpca-settings-form');
    const status = document.getElementById('wpca-status');

    if (!form) {
        return;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        status.textContent = '';
        status.className = 'wpca-status';

        const formData = new FormData(form);
        formData.append('action', 'wpca_dashboard_save'); // AJAX action 名

        // 注意：_wpnonce 已由 wp_nonce_field 渲染为隐藏字段，FormData 自动携带
        fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(function (data) {
                if (data && data.success) {
                    status.textContent = '保存成功';
                } else {
                    status.textContent = '保存失败：' + ((data && data.data && data.data.message) || '未知错误');
                    status.classList.add('wpca-status--error');
                }
            })
            .catch(function (err) {
                status.textContent = '请求错误：' + err.message;
                status.classList.add('wpca-status--error');
            });
    });
})();
