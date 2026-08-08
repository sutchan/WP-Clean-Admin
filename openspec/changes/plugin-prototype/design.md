<!-- OPENSPEC:START -->
# 插件原型设计规范

## Context

WP Clean Admin 1.8.3 已修复 nonce 字段名不统一、设置未 sanitize、版本注释不一致等缺陷。为避免后续功能开发再次引入同类问题，需建立统一的设计规范与可运行原型，作为所有新功能的落地基线。

## Goals / Non-Goals

### Goals
- 定义清晰的架构分层与模块边界，终结新旧架构并存
- 固化前端交互规范（AJAX / nonce / 字段命名）
- 建立安全基线（输入校验、输出转义、权限、SQL）
- 提供 UI/UX 设计语言，统一后台界面
- 产出可运行原型骨架，降低新功能起步成本

### Non-Goals
- 不重写现有 1.8.3 已稳定功能
- 不引入与 WordPress 后台管理无关的能力

## Decisions

### 1. 架构分层（终结双架构）
- **Decision**: 以 `includes/modules/**/class-wpca-*.php` 为唯一权威实现，旧 `includes/class-wpca-*.php` 标记为 `@deprecated`，仅保留适配桥接，下一大版本删除。
- **Reason**: 消除重复实现与命名冲突隐患。
- **Alternatives considered**: 反向保留旧架构——已不适应模块化扩展需求。

### 2. 统一 AJAX 网关
- **Decision**: 所有 AJAX handler 继承 `AJAX_Gateway_Base`，强制使用 `$_POST['_wpnonce']` 字段名 + `wp_verify_nonce(..., 'wpca_ajax_nonce' )`，统一权限校验 `current_user_can( 'manage_options' )`。
- **Reason**: 根治 1.8.3 中 nonce 字段名不统一导致的功能失效。
- **Alternatives considered**: 每 handler 自行校验——曾导致不一致。

### 3. 前端交互规范
- **Decision**: 前端统一通过 `wpca_*.vars._wpnonce` 注入 nonce，AJAX 请求 `data` 中字段名固定为 `_wpnonce`；统一使用 `wp.ajax` 或 `jQuery.ajax` 标准封装，禁止裸 `fetch` 绕过 nonce。
- **Reason**: 与后端字段名对齐，避免校验失败。

### 4. 输入/输出安全基线
- **Decision**: 所有设置写入前递归 `sanitize_*`；所有输出经 `esc_html/esc_attr/esc_url`；SQL 一律 `$wpdb->prepare`；所有写操作 nonce + capability 双校验。
- **Reason**: 1.8.3 已验证未 sanitize 的存储风险。

### 5. UI/UX 设计语言
- **Decision**: 后台页面采用 WordPress 原生 `.wp-*` 样式基底 + 自定义 BEM 组件 `wpca-*`；配色变量集中定义；响应式断点 782px（WP 移动后台阈值）。
- **Reason**: 与 WP 后台视觉一致，降低用户认知成本。

## Architecture

### 模块骨架
```
WPCleanAdmin\Modules\<Domain>\Classes\Module_<Name>
  ├─ init()          注册 hooks
  ├─ register_ajax() 注册 AJAX action（经网关）
  └─ 业务逻辑
WPCleanAdmin\AJAX\Gateway_Base  ← 统一 nonce + capability
```

### 原型文件位置
- `prototype/php/` — PHP 骨架
- `prototype/ui/` — 前端 UI 原型（入口 `index.html`，样式 `wpca-components.css`）

## Security

基线见 `AGENTS.md` 第 8 节，本原型强制：
- AJAX 入口 100% 经网关（nonce + capability）
- 设置写入 100% 经 `sanitize_settings`
- 无裸 SQL

## Test Plan

- `tests/PrototypeModuleTest.php` 验证骨架可实例化、网关 nonce 校验拒绝无效值
- 在有 PHP 环境执行 `composer test`
<!-- OPENSPEC:END -->
