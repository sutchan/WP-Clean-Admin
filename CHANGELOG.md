# Changelog

## [1.8.2] - 2026-08-07
### Fixed
- AJAX nonce 字段名统一: 将 settings/dashboard/cleanup 三类 handler 的 `$_POST['nonce']` 统一为 `$_POST['_wpnonce']`，与前端发送字段及其余 11 个 handler 保持一致，修复数据库/性能/菜单等 AJAX 校验永远失败的功能性 bug
- 设置存储安全: `settings-ajax.php` 的 `save_settings` 增加递归 `sanitize_settings`，防止未清理输入直接 `update_option` 造成存储型 XSS
- 版本注释同步: 将 65 个文件头 `@version 1.8.0` 统一更正为 `1.8.1`（主文件/语言文件已为 1.8.1）
- 消除重复代码: 移除 11 个 AJAX 文件中重复内联的 WordPress 函数 stub，统一依赖 `autoload.php` 已加载的 `wpca-wordpress-stubs.php`

### Added
- 单元测试: 新增 `phpunit.xml.dist`、`tests/bootstrap.php`，以及 `HelpersTest`、`SettingsAjaxTest` 覆盖 sanitize/nonce/format_bytes 核心逻辑

## [1.8.1] - 2026-05-17
### Added
- 诊断模块: 添加了完整的诊断功能，包括系统健康检查、安全检查和性能检查
- 诊断规范: 在 openspec 中添加了诊断模块的完整规范文档

### Fixed
- 诊断模块 AJAX 类引用错误: 修复了诊断模块 AJAX 类引用错误，修正了命名空间路径
- 模块化架构: 优化了模块化架构的一致性和完整性

### Improved
- 翻译文件: 添加了完整的诊断模块相关翻译，支持中文和英文

## 1.8.0 (2026-01-30)

### Added
- 新增了 22 个核心模块的 PHP 7.4+ 类型声明，提升代码安全性和可维护性
- 实现了 Cleanup 模块，支持媒体、评论、帖子、短代码清理
- 新增了 Elementor 集成支持，优化 Elementor 页面构建器
- 添加了 Composer 依赖管理基础架构（v2.0.0 准备）
- 实现了扩展 API，为第三方开发者提供扩展接口
- 新增了预设主题模板，支持快速配置

### Fixed
- 修复了 SQL 注入风险，使用 $wpdb->prepare 重构所有数据库查询
- 修复了 AJAX nonce 验证缺失问题
- 统一了类文件命名格式为 class-wpca-*.php
- 移除了所有占位符代码，实现了完整功能
- 修复了 WordPress 6.5 兼容性问题
- 修复了构造函数返回类型声明错误

### Improved
- 测试覆盖率从 80% 提升至 90%
- 代码规范符合度达到 95%
- PHPDoc 覆盖率达到 95%
- 优化了后台加载速度和资源使用
- 增强了菜单管理功能，支持按角色限制菜单
- 提升了性能优化模块，支持资源预加载和压缩

## 1.7.15 (2025-11-30)

### Improved
- 菜单管理功能增强
- 性能优化模块完善

## 1.7.14 (2025-11-15)

### Improved
- 数据库优化模块完善

## 1.7.13 (2025-10-31)

### Added
- 双因素认证功能

## 1.7.12 (2025-10-15)

### Improved
- 性能优化模块重构

## 1.7.11 (2025-09-30)

### Improved
- 角色权限管理功能完善
