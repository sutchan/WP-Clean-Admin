# WP Clean Admin 高保真原型

本目录为插件的高保真可运行原型，独立于 `wpcleanadmin/includes/` 主代码，
用于验证设计规范（`../docs/设计规范_20260808.md`）并作为新功能开发基线。

## 目录结构

```
prototype/
├─ php/
│  ├─ class-wpca-module-base.php       模块抽象基类（单例 + register_ajax + sanitize）
│  ├─ class-wpca-ajax-gateway-base.php 统一 AJAX 网关（_wpnonce + capability）
│  └─ modules/
│     ├─ class-wpca-module-dashboard.php    仪表盘（体检总览）
│     ├─ class-wpca-module-cleanup.php      清理（DB/媒体/评论/内容）
│     ├─ class-wpca-module-performance.php  性能优化（开关）
│     ├─ class-wpca-module-security.php     安全与菜单（可见性）
│     ├─ class-wpca-module-database.php     数据库（表/备份/优化）
│     └─ class-wpca-module-diagnostics.php  诊断（环境检测）
└─ ui/
   ├─ index.html           后台多页签布局（唯一入口，shadcn 风格）
   ├─ wpca-components.css  组件库样式（shadcn 风格设计令牌 + 组件）
   └─ app.js               交互（页签/渲染/AJAX/modal/toast/移动端抽屉）
```

> 合并说明：已删除 `ui/dashboard.html` 与 `ui/settings-page.css` 冗余文件，
> 统一以 `index.html` + `wpca-components.css` 为单一原型来源（2026-08-08）。

## 运行方式

### 前端预览
直接用浏览器打开 `ui/index.html` 即可预览（默认 `USE_MOCK=true`，
内置模拟数据，无需后端）。接入真实后端时，将 `app.js` 中 `USE_MOCK` 置 `false`
并确保页面包含 `wp_nonce_field('wpca_ajax_nonce', '_wpnonce')` 渲染的隐藏字段。

### 后端原型
模块为独立 PHP 类，需 WordPress 环境运行；可经主插件 autoload 桥接加载，
或直接 `require` 后调用 `Module_X::get_instance()`。

## 规范对应

| 设计规范组件 | 原型实现 |
|------|------|
| 模块基类 | php/class-wpca-module-base.php |
| AJAX 网关 | php/class-wpca-ajax-gateway-base.php |
| UI 组件 | ui/wpca-components.css |
| 交互流 | ui/app.js |

## AJAX 契约（与 openspec/api.md §2.4 严格一致）

- 请求：`action=<action>`，nonce 字段名 **`_wpnonce`**，nonce action **`wpca_ajax_nonce`**
- 响应：成功 `{success:true,data:{...}}`；失败 `{success:false,data:{message:<code>}}`
- 已注册 action：见 `openspec/specs/api/spec.md` §2.4.4
- 数据结构 Schema：见 `openspec/specs/api/spec.md` §2.4.5 / `../docs/设计规范_20260808.md` §9
