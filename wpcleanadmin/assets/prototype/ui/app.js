/**
 * WP Clean Admin 高保真原型交互脚本
 *
 * 规范要点（对齐设计规范_20260808.md）：
 *  - nonce 字段名固定 _wpnonce，随 FormData 自动携带
 *  - 所有写操作经 AJAX 网关；危险操作弹 modal 二次确认
 *  - 加载态按钮禁用；成功/失败 toast 反馈
 *
 * 原型模式：USE_MOCK=true 时用内置数据模拟响应（无需后端）；
 * 接入真实后端时置 false，走 window.ajaxurl。
 */
(function () {
    'use strict';

    const USE_MOCK = true;
    const ajaxUrl = window.ajaxurl || '/wp-admin/admin-ajax.php';

    /* ---------- 工具 ---------- */
    function qs(sel, root) { return (root || document).querySelector(sel); }
    function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

    function toast(msg, type) {
        const area = qs('#wpca-toast-area');
        const el = document.createElement('div');
        el.className = 'wpca-toast wpca-toast--' + (type || 'success');
        el.textContent = msg;
        area.appendChild(el);
        setTimeout(function () { el.remove(); }, 2600);
    }

    // 获取 _wpnonce（真实环境由 wp_nonce_field 渲染的隐藏字段提供）
    function getNonce() {
        const f = qs('#wpca-nonce-field');
        return f ? f.value : 'MOCK_NONCE';
    }

    /**
     * 统一 AJAX 封装：字段名固定 _wpnonce
     */
    function api(action, extra, mockHandler) {
        if (USE_MOCK) {
            return Promise.resolve(mockHandler(extra));
        }
        const fd = new FormData();
        fd.append('action', action);
        fd.append('_wpnonce', getNonce());
        Object.keys(extra || {}).forEach(function (k) { fd.append(k, extra[k]); });
        return fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) { return d && d.success ? d.data : Promise.reject(d && d.data); });
    }

    /* ---------- 页签切换 ---------- */
    qsa('.wpca-nav__item').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tab = btn.getAttribute('data-tab');
            qsa('.wpca-nav__item').forEach(function (b) { b.classList.toggle('is-active', b === btn); });
            qsa('.wpca-panel').forEach(function (p) {
                const on = p.getAttribute('data-panel') === tab;
                p.hidden = !on;
                p.classList.toggle('is-active', on);
            });
        });
    });

    /* ---------- 仪表盘：一键体检 ---------- */
    qs('#wpca-scan-btn').addEventListener('click', function () {
        const btn = this;
        const progress = qs('#wpca-scan-progress');
        const bar = qs('.wpca-progress__bar');
        btn.disabled = true;
        progress.hidden = false;
        bar.style.width = '0%';

        const metrics = [
            { label: '冗余修订版本', count: 1280, level: 'warning' },
            { label: '孤立元数据包', count: 342, level: 'warning' },
            { label: '垃圾评论', count: 57, level: 'danger' },
            { label: '过期临时选项', count: 89, level: 'info' },
            { label: '数据库体积', count: '48.2 MB', level: 'info' }
        ];

        let pct = 0;
        const timer = setInterval(function () {
            pct += 20;
            bar.style.width = pct + '%';
            if (pct >= 100) {
                clearInterval(timer);
                renderStats(metrics);
                progress.hidden = true;
                btn.disabled = false;
                toast('体检完成', 'success');
            }
        }, 180);
    });

    function renderStats(metrics) {
        const wrap = qs('#wpca-stats');
        wrap.innerHTML = '';
        metrics.forEach(function (m) {
            const card = document.createElement('div');
            card.className = 'wpca-stat' + (m.level === 'danger' ? ' wpca-stat--danger' : m.level === 'warning' ? ' wpca-stat--warning' : '');
            card.innerHTML = '<div class="wpca-stat__num">' + m.count + '</div><div class="wpca-stat__label">' + m.label + '</div>';
            wrap.appendChild(card);
        });
    }

    /* ---------- 清理：任务列表 + 危险确认 ---------- */
    const cleanupTasks = [
        { id: 'revisions', label: '文章修订版本', count: 1280, risk: 'low' },
        { id: 'drafts', label: '自动草稿', count: 43, risk: 'low' },
        { id: 'orphan_meta', label: '孤立元数据', count: 342, risk: 'medium' },
        { id: 'spam', label: '垃圾评论', count: 57, risk: 'low' },
        { id: 'trash', label: '回收站内容', count: 21, risk: 'medium' },
        { id: 'transients', label: '过期临时选项', count: 89, risk: 'low' }
    ];
    const riskBadge = { low: 'info', medium: 'warning', high: 'danger' };

    function renderCleanup() {
        const tb = qs('#wpca-cleanup-table tbody');
        tb.innerHTML = '';
        cleanupTasks.forEach(function (t) {
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + t.label + '</td>' +
                '<td>' + t.count + '</td>' +
                '<td><span class="wpca-badge wpca-badge--' + riskBadge[t.risk] + '">' + t.risk + '</span></td>' +
                '<td><button class="button wpca-btn" data-clean="' + t.id + '">清理</button></td>';
            tb.appendChild(tr);
        });
        qsa('[data-clean]').forEach(function (b) {
            b.addEventListener('click', function () {
                const id = b.getAttribute('data-clean');
                const task = cleanupTasks.find(function (x) { return x.id === id; });
                openModal('确认清理「' + task.label + '」？将影响 ' + task.count + ' 条记录。', function () {
                    api('wpca_cleanup_run', { task: id }, function () { return { task: id, affected: task.count }; })
                        .then(function (d) { toast('已清理 ' + d.affected + ' 条', 'success'); })
                        .catch(function () { toast('清理失败', 'error'); });
                });
            });
        });
    }
    renderCleanup();

    /* ---------- 性能：开关列表 ---------- */
    const perfOptions = [
        { id: 'disable_emojis', label: '禁用 Emoji 脚本', on: true },
        { id: 'disable_embeds', label: '禁用 oEmbed', on: true },
        { id: 'defer_scripts', label: '延迟加载脚本', on: false },
        { id: 'lazy_images', label: '图片懒加载', on: true },
        { id: 'minify_html', label: '压缩 HTML 输出', on: false }
    ];
    function renderPerf() {
        const list = qs('#wpca-perf-list');
        list.innerHTML = '';
        perfOptions.forEach(function (o, i) {
            const row = document.createElement('div');
            row.className = 'wpca-list__row';
            row.innerHTML =
                '<span>' + o.label + '</span>' +
                '<label class="wpca-switch"><input type="checkbox" ' + (o.on ? 'checked' : '') + ' data-perf="' + i + '"><span class="wpca-switch__slider"></span></label>';
            list.appendChild(row);
        });
        qsa('[data-perf]').forEach(function (c) {
            c.addEventListener('change', function () {
                const idx = +c.getAttribute('data-perf');
                const opt = perfOptions[idx];
                opt.on = c.checked;
                api('wpca_perf_toggle', { option: opt.id, value: c.checked ? 1 : 0 }, function () { return { option: opt.id, value: c.checked }; })
                    .then(function () { toast('已' + (c.checked ? '开启' : '关闭') + '：' + opt.label, 'success'); })
                    .catch(function () { toast('保存失败', 'error'); c.checked = !c.checked; });
            });
        });
    }
    renderPerf();

    /* ---------- 安全与菜单：可见性 ---------- */
    const menus = [
        { slug: 'index.php', label: '仪表盘', visible: true, system: true },
        { slug: 'edit.php', label: '文章', visible: true, system: false },
        { slug: 'upload.php', label: '媒体', visible: true, system: false },
        { slug: 'themes.php', label: '外观', visible: false, system: false },
        { slug: 'plugins.php', label: '插件', visible: true, system: true },
        { slug: 'users.php', label: '用户', visible: true, system: false },
        { slug: 'options-general.php', label: '设置', visible: true, system: true }
    ];
    function renderMenus() {
        const tb = qs('#wpca-menu-table tbody');
        tb.innerHTML = '';
        menus.forEach(function (m, i) {
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + m.label + '</td>' +
                '<td><span class="wpca-badge wpca-badge--' + (m.system ? 'info' : 'success') + '">' + (m.system ? '系统' : '自定义') + '</span></td>' +
                '<td><label class="wpca-switch"><input type="checkbox" ' + (m.visible ? 'checked' : '') + ' data-menu="' + i + '"><span class="wpca-switch__slider"></span></label></td>';
            tb.appendChild(tr);
        });
        qsa('[data-menu]').forEach(function (c) {
            c.addEventListener('change', function () { menus[+c.getAttribute('data-menu')].visible = c.checked; });
        });
    }
    renderMenus();
    qs('#wpca-menu-save').addEventListener('click', function () {
        api('wpca_security_save', { menus: JSON.stringify(menus) }, function () { return { saved: menus.length }; })
            .then(function () { toast('菜单设置已保存', 'success'); })
            .catch(function () { toast('保存失败', 'error'); });
    });

    /* ---------- 数据库：表 + 备份/优化 ---------- */
    const tables = [
        { name: 'wp_posts', rows: 12480, size: '12.4 MB', overhead: '0 B' },
        { name: 'wp_postmeta', rows: 58210, size: '21.8 MB', overhead: '1.2 MB' },
        { name: 'wp_options', rows: 932, size: '2.1 MB', overhead: '0 B' },
        { name: 'wp_comments', rows: 1840, size: '3.6 MB', overhead: '640 KB' },
        { name: 'wp_users', rows: 312, size: '0.9 MB', overhead: '0 B' }
    ];
    function renderDb() {
        const tb = qs('#wpca-db-table tbody');
        tb.innerHTML = '';
        tables.forEach(function (t) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td>' + t.name + '</td><td>' + t.rows + '</td><td>' + t.size + '</td><td>' + t.overhead + '</td>';
            tb.appendChild(tr);
        });
    }
    renderDb();
    qs('#wpca-db-backup').addEventListener('click', function () {
        api('wpca_db_backup', {}, function () { return { file: 'wpca-backup.sql', size: '38.8 MB' }; })
            .then(function (d) { toast('备份完成：' + d.file, 'success'); })
            .catch(function () { toast('备份失败', 'error'); });
    });
    qs('#wpca-db-optimize').addEventListener('click', function () {
        api('wpca_db_optimize', {}, function () { return { optimized: tables.length, tables_with_overhead: 2 }; })
            .then(function (d) { toast('已优化 ' + d.optimized + ' 张表', 'success'); })
            .catch(function () { toast('优化失败', 'error'); });
    });

    /* ---------- 诊断 ---------- */
    const checks = [
        { label: 'PHP 版本', value: '8.1.2', status: 'success', advice: '' },
        { label: 'WordPress 版本', value: '6.4.1', status: 'success', advice: '' },
        { label: 'MySQL 版本', value: '5.7.4', status: 'warning', advice: '建议升级到 8.0+' },
        { label: 'HTTPS', value: '已启用', status: 'success', advice: '' },
        { label: 'WP_DEBUG', value: '开启', status: 'danger', advice: '生产环境应关闭' },
        { label: '对象缓存', value: '未启用', status: 'warning', advice: '建议启用 Redis/Memcached' }
    ];
    qs('#wpca-diag-btn').addEventListener('click', function () {
        api('wpca_diag_run', {}, function () { return { checks: checks }; })
            .then(function (d) {
                const tb = qs('#wpca-diag-table tbody');
                tb.innerHTML = '';
                d.checks.forEach(function (c) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td>' + c.label + '</td><td>' + c.value + '</td>' +
                        '<td><span class="wpca-badge wpca-badge--' + c.status + '">' + c.status + '</span></td>' +
                        '<td>' + (c.advice || '—') + '</td>';
                    tb.appendChild(tr);
                });
                toast('诊断完成', 'success');
            })
            .catch(function () { toast('诊断失败', 'error'); });
    });

    /* ---------- 弹窗（危险操作二次确认） ---------- */
    let modalConfirmCb = null;
    function openModal(body, onConfirm) {
        qs('#wpca-modal-body').textContent = body;
        modalConfirmCb = onConfirm;
        qs('#wpca-modal').hidden = false;
        qs('#wpca-modal-confirm').focus();
    }
    function closeModal() {
        qs('#wpca-modal').hidden = true;
        modalConfirmCb = null;
    }
    qsa('#wpca-modal [data-close]').forEach(function (el) { el.addEventListener('click', closeModal); });
    qs('#wpca-modal-confirm').addEventListener('click', function () {
        const cb = modalConfirmCb;
        closeModal();
        if (cb) cb();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !qs('#wpca-modal').hidden) closeModal();
    });
})();
