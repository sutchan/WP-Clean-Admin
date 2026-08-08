<!-- OPENSPEC:START -->
# 实施任务清单

## 阶段一：设计规范
- [x] 架构分层与模块边界定义
- [x] 前端交互规范（AJAX / nonce / 字段命名）
- [x] 安全基线（输入校验、输出转义、权限、SQL）
- [x] UI/UX 设计语言（布局、组件库、配色）

## 阶段二：PHP 模块骨架原型（高保真）
- [x] 抽象基类 `Module_Base`（单例 + hooks + AJAX 注册 + sanitize_settings）
- [x] 统一 AJAX 网关基类（固化 `_wpnonce` 字段名与权限校验）
- [x] 6 个核心模块：Dashboard / Cleanup / Performance / Security / Database / Diagnostics
- [x] 模块放置 `assets/prototype/php/modules/`，独立于主代码

## 阶段三：前端 UI 原型（高保真）
- [x] 后台多页签布局 dashboard.html（WP 后台风格 + 响应式 782/600px）
- [x] 完整组件库样式 wpca-components.css（stat/table/badge/switch/progress/modal/toast 等）
- [x] 交互脚本 app.js（页签切换、mock 数据渲染、AJAX 封装、危险操作二次确认、toast）
- [x] 设计规范文档 设计规范_20260808.md（IA/令牌/组件/交互/可访问性）

## 阶段四：验证
- [x] 骨架单元测试（`phpunit.xml.dist` 已就绪）
- [x] 测试覆盖 7 个模块（含 Settings）
- [ ] 在有 PHP 环境实跑 `composer test` 验证

## 阶段五：与原型同步 openspec 规范（2026-08-08）
- [x] api.md 增补 AJAX 接口标准（_wpnonce/wpca_ajax_nonce、错误码、已注册 action、业务数据结构）、版本 1.8.2
- [x] plugin-architecture/design.md 增补 AJAX 网关决策 + 原型基线决策，架构层次模块化
- [x] plugin-architecture/detailed-design.md 同步版本 1.8.2、AJAX 网关标准章节、模块→action 映射

## 阶段六：归档
- [ ] 将设计规范归档至 `openspec/specs/plugin-architecture/`
- [ ] 更新 CHANGELOG 至 1.8.2 条目
<!-- OPENSPEC:END -->
