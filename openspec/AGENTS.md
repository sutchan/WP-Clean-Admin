<!-- OPENSPEC:START -->
# OpenSpec 指令

这些指令适用于在本项目中工作的 AI 助手。

## 1. 何时参考本文件

当请求包含以下情况时，请始终打开 `@/openspec/AGENTS.md`：
- 提及规划或提案（如 proposal、spec、change、plan 等词）
- 引入新功能、破坏性变更、架构变更或重大性能/安全工作
- 听起来模棱两可，在编码前需要权威规范
- 需要了解项目结构和开发流程
- 需要遵循特定的代码规范或文档格式

## 2. 项目概述

WP Clean Admin 是一个 WordPress 插件，用于管理后台清理和优化，版本 1.7.15。

**项目特点**：
- 模块化设计，便于扩展和维护
- 遵循 WordPress 最佳实践
- 注重安全性、性能和用户体验
- 支持多语言
- 提供丰富的 API 供开发者使用

**项目目标**：
- 清理 WordPress 后台冗余菜单和功能
- 优化后台加载性能
- 增强后台安全性
- 提供灵活的菜单定制功能
- 简化数据库管理

## 3. 目录结构

### 插件主目录结构
```
wpcleanadmin/
├── assets/                    # 静态资源目录
│   ├── css/                   # CSS 文件
│   └── js/                    # JavaScript 文件
├── includes/                  # 核心功能目录
│   ├── autoload.php           # 自动加载文件
│   ├── class-*.php            # 各个功能类
│   └── wpca-core-functions.php # 核心函数
├── languages/                 # 语言文件目录
└── wp-clean-admin.php         # 插件主文件
```

### OpenSpec 目录结构
```
openspec/
├── README.md              # 本文档
├── project.md             # 项目上下文和约定
├── AGENTS.md              # AI 助手指令
├── CHANGELOG.md           # 版本更新记录
├── specs/                 # 功能规范文档
│   ├── admin-cleanup/     # 后台清理规范
│   ├── api/               # API 接口规范
│   ├── database-management/ # 数据库管理规范
│   ├── menu-customization/  # 菜单定制规范
│   ├── performance-optimization/ # 性能优化规范
│   ├── permission-management/ # 权限管理规范
│   ├── plugin-architecture/ # 插件架构设计
│   └── security-enhancement/ # 安全增强规范
├── changes/               # 变更提案（待处理）
│   └── 规范改进与功能增强计划/ # 示例变更提案
└── archive/               # 归档的旧文档
```

## 4. 编码规范

### 4.1 基础规范
- **编码格式**：所有文件必须使用 UTF-8 编码，无 BOM
- **换行符**：统一使用 Unix LF 换行符
- **缩进**：使用 4 个空格作为缩进，禁止使用制表符
- **行宽**：每行代码长度建议不超过 120 个字符
- **文件名**：使用小写字母和连字符，例如 `class-wpca-core.php`

### 4.2 PHP 编码规范
- **PHP 标签**：使用 `<?php` 标签，禁止使用短标签 `<?`
- **闭合标签**：PHP 文件末尾禁止使用闭合标签 `?>`
- **命名空间**：使用 `WPCleanAdmin` 命名空间
- **自动加载**：遵循 PSR-4 自动加载规范
- **类名**：使用 PascalCase，例如 `WPCleanAdmin\Core`
- **方法名**：使用 camelCase，例如 `getInstance()`
- **函数名**：使用 snake_case，例如 `wpca_get_settings()`
- **变量名**：使用 snake_case，例如 `$plugin_settings`
- **常量名**：使用全大写字母和下划线，例如 `WPCA_VERSION`

### 4.3 JavaScript 编码规范
- **变量声明**：使用 `const` 或 `let`，禁止使用 `var`
- **分号**：必须使用分号结束语句
- **字符串引号**：使用单引号 `'` 定义字符串
- **变量名**：使用 camelCase，例如 `pluginSettings`
- **函数名**：使用 camelCase，例如 `initPlugin()`
- **类名**：使用 PascalCase，例如 `WPCleanAdmin`

### 4.4 CSS 编码规范
- **命名规范**：使用 BEM（Block Element Modifier）命名规范
- **颜色变量**：使用 CSS 变量定义颜色
- **盒模型**：使用 `box-sizing: border-box`
- **响应式设计**：使用媒体查询实现响应式设计

### 4.5 HTML 编码规范
- **DOCTYPE**：使用 `<!DOCTYPE html>`
- **语言属性**：添加 `lang` 属性，例如 `<html lang="zh-CN">`
- **字符集**：使用 `<meta charset="UTF-8" />` 定义字符集
- **语义化标签**：优先使用 HTML5 语义化标签

## 5. 开发流程

### 5.1 分支管理
- **main**：主分支，用于发布稳定版本
- **develop**：开发分支，用于集成新功能
- **feature/xxx**：功能分支，用于开发新功能
- **bugfix/xxx**：修复分支，用于修复 bug

### 5.2 提交信息格式
- 使用 Conventional Commits 规范
- 格式：`type(scope): description`
- 类型：feat, fix, docs, style, refactor, test, chore
- 示例：`feat(cleanup): 添加菜单清理功能`

### 5.3 代码审查
- 所有代码必须经过审查才能合并到 main 分支
- 审查内容包括：代码质量、安全性、性能、可读性

### 5.4 测试
- 每个功能必须编写测试用例
- 测试覆盖率争取达到 80% 以上
- 提交代码前必须运行所有测试

## 6. 变更提案流程

### 6.1 创建变更提案
1. 在 `changes/` 目录下创建变更文件夹
2. 编写 `proposal.md` 描述变更内容
3. 创建 `tasks.md` 规划实施任务
4. 审批后创建 `specs/` 目录下的规范文档

### 6.2 变更提案内容
- **proposal.md**：包含变更原因、详细内容、影响范围和实施计划
- **tasks.md**：包含具体的实施任务和时间线
- **design.md**：包含技术设计和架构变更
- **specs/**：包含详细的功能规范文档

### 6.3 规范文档格式
- 所有文档必须包含 `<!-- OPENSPEC:START -->` 和 `<!-- OPENSPEC:END -->` 标记
- 使用 Markdown 格式编写
- 包含完整的功能描述、技术实现和测试计划

## 7. 文档约定

### 7.1 命名规范
- 规范文档使用小写字母和连字符
- 报告文件包含时间戳：`报告名_YYYYMMDD_HHMM.md`
- 变更提案使用描述性名称

### 7.2 文档结构
- 标题层级清晰，使用 Markdown 标题格式
- 包含目录和章节划分
- 使用代码块展示示例代码
- 使用列表展示要点

### 7.3 版本管理
- 主版本号 (MAJOR): 不兼容的 API 变更
- 次版本号 (MINOR): 向后兼容的新功能
- 修订号 (PATCH): 向后兼容的 bug 修复

## 8. 安全规范

### 8.1 输入验证
- 所有用户输入必须经过验证和过滤
- 使用 WordPress 验证函数，例如 `sanitize_text_field()`、`sanitize_email()`

### 8.2 输出转义
- 所有输出到页面的内容必须经过转义
- 使用 WordPress 转义函数，例如 `esc_html()`、`esc_attr()`、`esc_url()`

### 8.3 SQL 注入防护
- 使用 `$wpdb->prepare()` 处理所有 SQL 查询
- 参数绑定，避免直接拼接 SQL

### 8.4 CSRF 防护
- 使用 nonce 验证所有表单提交
- 使用 WordPress nonce 函数，例如 `wp_nonce_field()`、`wp_verify_nonce()`

### 8.5 权限检查
- 所有管理操作必须检查权限
- 使用 WordPress 权限函数，例如 `current_user_can()`

## 9. 兼容性规范

### 9.1 WordPress 版本
- 兼容 WordPress 5.0+
- 定期测试最新版本的 WordPress

### 9.2 PHP 版本
- 兼容 PHP 7.0+
- 定期测试最新版本的 PHP

### 9.3 浏览器兼容性
- 兼容主流浏览器：Chrome、Firefox、Safari、Edge
- 支持响应式设计

### 9.4 主题兼容性
- 与主流 WordPress 主题兼容
- 样式隔离，避免冲突

## 10. 国际化规范

### 10.1 文本域
- 使用 `WPCA_TEXT_DOMAIN` 常量定义文本域
- 文本域值为 `wp-clean-admin`

### 10.2 翻译函数
- 使用 WordPress 翻译函数，例如 `__()`、`_e()`、`_x()`、`_ex()`
- 所有可翻译字符串必须使用翻译函数

### 10.3 翻译文件
- 提供 POT 文件：`wp-clean-admin.pot`
- 支持多语言翻译，例如英文、中文
- 定期更新翻译文件

## 11. 开发工具和最佳实践

### 11.1 开发工具
- **IDE**：推荐使用 Visual Studio Code 或 PhpStorm
- **版本控制**：使用 Git 进行版本控制
- **构建工具**：使用 Composer 管理 PHP 依赖
- **测试工具**：使用 PHPUnit 进行 PHP 单元测试，QUnit 进行 JavaScript 单元测试

### 11.2 最佳实践
- **模块化设计**：将功能划分为独立模块
- **面向对象**：使用面向对象编程范式
- **钩子系统**：深度集成 WordPress 钩子系统
- **缓存策略**：合理使用缓存提高性能
- **错误处理**：完善的错误处理和日志记录
- **代码复用**：避免重复代码，提取公共功能

### 11.3 性能优化
- **资源加载**：按需加载资源，避免不必要的资源加载
- **数据库优化**：合理使用数据库索引，避免复杂查询
- **代码优化**：减少代码复杂度，提高执行效率
- **缓存优化**：使用 WordPress 缓存机制

## 12. 常见问题和解决方案

### 12.1 代码规范问题
- **问题**：代码不符合 WordPress 编码规范
- **解决方案**：使用 PHP_CodeSniffer 工具检查和修复代码规范问题

### 12.2 兼容性问题
- **问题**：插件与 WordPress 版本或主题不兼容
- **解决方案**：使用 `function_exists()` 检查函数是否存在，提供降级方案

### 12.3 性能问题
- **问题**：插件加载缓慢或影响网站性能
- **解决方案**：使用性能分析工具识别瓶颈，优化代码和资源加载

### 12.4 安全问题
- **问题**：插件存在安全漏洞
- **解决方案**：定期进行安全审计，使用 WordPress 安全最佳实践

## 13. 参考资料

- [WordPress 编码规范](https://developer.wordpress.org/coding-standards/)
- [PSR-4 自动加载规范](https://www.php-fig.org/psr/psr-4/)
- [Conventional Commits 规范](https://www.conventionalcommits.org/)
- [Semantic Versioning 规范](https://semver.org/)
- [WordPress 插件开发手册](https://developer.wordpress.org/plugins/)

## 14. 总结

本指令文档旨在为在 WP Clean Admin 项目中工作的 AI 助手提供全面的指导。遵循这些指令可以确保代码质量、安全性和可维护性，同时提高开发效率和一致性。

随着项目的发展和技术的进步，这些指令会定期更新和完善。AI 助手应始终参考最新版本的指令文档，以确保符合项目的最新要求和最佳实践。

保留此管理块，以便 'openspec update' 可以刷新指令。

<!-- OPENSPEC:END -->