<!-- OPENSPEC:START -->
# 插件原型与设计规范

## 变更原因

WP Clean Admin 已迭代至 1.8.3，存在以下问题需通过"原型 + 设计规范"统一后续开发：

1. **架构冗余**：旧式 `includes/class-wpca-*.php` 与模块化 `includes/modules/**/class-wpca-*.php` 功能重叠并存，缺乏统一设计约束，新功能易再次产生双份实现。
2. **前端缺乏统一规范**：各 AJAX handler 的 nonce 字段名曾出现 `_wpnonce` / `nonce` 不统一（已在 1.8.3 修复），本质是没有前端交互设计规范。
3. **缺少可验证原型**：规范停留在文档层面，开发者需可直接运行的原型骨架作为落地基准。

本提案产出**插件原型**（可运行代码骨架 + 前端 UI 原型）与**设计规范**，作为后续所有功能开发的权威参照。

## 变更内容

### 1. 设计规范文档（deliverable）
- 架构分层与模块边界
- 前端交互规范（AJAX / nonce / 字段命名）
- 安全基线（输入校验、输出转义、权限、SQL）
- 编码与命名规范（对齐 AGENTS.md）
- UI/UX 设计语言（后台页面布局、组件库）

### 2. 可运行原型（deliverable）
- PHP 模块骨架：`WPCleanAdmin\Modules\*` 标准模板（单例 + hooks + AJAX）
- 前端 UI 原型：后台设置页 HTML/CSS/JS 原型（含 nonce 标准用法）
- 统一 AJAX 网关基类，固化 nonce 字段名为 `_wpnonce`

### 3. 原型验证
- PHPUnit 测试样例覆盖骨架关键路径（已具备 phpunit 配置）

## 影响范围

- 不修改现有 1.8.3 功能代码，仅新增 `prototype/` 与文档
- 后续新功能须以本原型为基线，旧 `class-wpca-*` 逐步迁移至模块化架构

## 实施计划

1. 编写设计规范（本提案 design.md 扩展）
2. 产出 PHP 模块骨架与 AJAX 网关基类
3. 产出前端 UI 原型
4. 补充骨架单元测试
5. 归档至 openspec/specs/ 作为基线规范
<!-- OPENSPEC:END -->
