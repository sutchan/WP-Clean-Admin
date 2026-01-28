# WP Clean Admin 项目规则

## 1. 项目概述

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

## 2. 编码规范

### 2.1 基础规范
- **编码格式**：所有文件必须使用 UTF-8 without BOM 编码
- **换行符**：统一使用 Unix LF 换行符
- **缩进**：使用 4 个空格作为缩进，禁止使用制表符
- **行宽**：每行代码长度建议不超过 120 个字符
- **文件名**：使用小写字母和连字符，例如 `class-wpca-core.php`

### 2.2 PHP 编码规范
- **PHP 标签**：使用 `<?php` 标签，禁止使用短标签 `<?`
- **闭合标签**：PHP 文件末尾禁止使用闭合标签 `?>`
- **命名空间**：使用 `WPCleanAdmin` 命名空间
- **自动加载**：遵循 PSR-4 自动加载规范
- **类名**：使用 PascalCase，例如 `WPCleanAdmin\Core`
- **方法名**：使用 camelCase，例如 `getInstance()`
- **函数名**：使用 snake_case，例如 `wpca_get_settings()`
- **变量名**：使用 snake_case，例如 `$plugin_settings`
- **常量名**：使用全大写字母和下划线，例如 `WPCA_VERSION`

### 2.3 JavaScript 编码规范
- **变量声明**：使用 `const` 或 `let`，禁止使用 `var`
- **分号**：必须使用分号结束语句
- **字符串引号**：使用单引号 `'` 定义字符串
- **变量名**：使用 camelCase，例如 `pluginSettings`
- **函数名**：使用 camelCase，例如 `initPlugin()`
- **类名**：使用 PascalCase，例如 `WPCleanAdmin`

### 2.4 CSS 编码规范
- **命名规范**：使用 BEM（Block Element Modifier）命名规范
- **颜色变量**：使用 CSS 变量定义颜色
- **盒模型**：使用 `box-sizing: border-box`
- **响应式设计**：使用媒体查询实现响应式设计

### 2.5 HTML 编码规范
- **DOCTYPE**：使用 `<!DOCTYPE html>`
- **语言属性**：添加 `lang` 属性，例如 `<html lang="zh-CN">`
- **字符集**：使用 `<meta charset="UTF-8" />` 定义字符集
- **语义化标签**：优先使用 HTML5 语义化标签

## 3. 目录结构

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

## 4. 命名规范

### 4.1 类命名
- 使用 PascalCase，例如 `WPCleanAdmin\Core`
- 类文件名使用 `class-wpca-{name}.php` 格式，例如 `class-wpca-core.php`

### 4.2 方法命名
- 使用 camelCase，例如 `getInstance()`
- 私有方法使用下划线前缀，例如 `_privateMethod()`

### 4.3 函数命名
- 使用 snake_case，例如 `wpca_get_settings()`
- 函数名前缀使用 `wpca_` 避免冲突

### 4.4 变量命名
- 使用 snake_case，例如 `$plugin_settings`
- 全局变量使用 `$wpca_` 前缀

### 4.5 常量命名
- 使用全大写字母和下划线，例如 `WPCA_VERSION`
- 常量名前缀使用 `WPCA_`

### 4.6 钩子命名
- 使用 snake_case，例如 `wpca_after_save_settings`
- 钩子名前缀使用 `wpca_`

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

## 6. 测试规范

### 6.1 测试类型
- **单元测试**：测试单个函数或方法
- **集成测试**：测试多个模块之间的交互
- **功能测试**：测试插件的完整功能
- **兼容性测试**：测试插件与不同环境的兼容性

### 6.2 测试工具
- **PHPUnit**：用于 PHP 单元测试
- **QUnit**：用于 JavaScript 单元测试
- **WP CLI**：用于 WordPress 集成测试

## 7. 文档规范

### 7.1 代码文档
- **函数注释**：每个函数必须包含 PHPDoc 注释
- **类注释**：每个类必须包含 PHPDoc 注释
- **文件注释**：每个文件必须包含文件头部注释
- **TODO 注释**：使用 `// TODO: 描述待完成的任务` 格式

### 7.2 项目文档
- **README.md**：项目介绍、功能特点、安装方法、使用说明
- **CHANGELOG.md**：版本更新记录
- **贡献指南**：如何贡献代码
- **API 文档**：函数和类的详细说明

### 7.3 文档格式
- 使用 Markdown 格式
- 清晰的结构，使用标题、列表、代码块等元素
- 提供完整的示例代码
- 文档与代码同步更新

## 8. 版本管理

### 8.1 版本号格式
- 遵循 Semantic Versioning 规范，格式为 `MAJOR.MINOR.PATCH`
- MAJOR：不兼容的 API 变更
- MINOR：向后兼容的新功能
- PATCH：向后兼容的 bug 修复

### 8.2 发布流程
- 更新代码中的版本号
- 更新 CHANGELOG.md 文件
- 运行测试
- 提交代码，创建 Git 标签
- 推送代码和标签到 GitHub
- 打包插件，发布到 WordPress.org

## 9. 安全规范

### 9.1 输入验证
- 所有用户输入必须经过验证和过滤
- 使用 WordPress 验证函数，例如 `sanitize_text_field()`、`sanitize_email()`

### 9.2 输出转义
- 所有输出到页面的内容必须经过转义
- 使用 WordPress 转义函数，例如 `esc_html()`、`esc_attr()`、`esc_url()`

### 9.3 SQL 注入防护
- 使用 `$wpdb->prepare()` 处理所有 SQL 查询
- 参数绑定，避免直接拼接 SQL

### 9.4 CSRF 防护
- 使用 nonce 验证所有表单提交
- 使用 WordPress nonce 函数，例如 `wp_nonce_field()`、`wp_verify_nonce()`

### 9.5 权限检查
- 所有管理操作必须检查权限
- 使用 WordPress 权限函数，例如 `current_user_can()`

### 9.6 安全头部
- 添加安全 HTTP 头部，例如 X-Frame-Options、X-XSS-Protection

## 10. 部署规范

### 10.1 插件安装
- 支持 WordPress 后台自动安装
- 支持手动上传安装
- 支持 FTP 上传安装
- 支持 WP CLI 安装

### 10.2 版本更新
- 支持 WordPress 后台自动更新
- 支持手动下载更新
- 支持 WP CLI 更新

### 10.3 数据备份
- 定期备份数据库
- 重要操作前备份数据
- 提供数据库备份功能

## 11. 兼容性规范

### 11.1 WordPress 版本
- 兼容 WordPress 5.0+
- 定期测试最新版本的 WordPress

### 11.2 PHP 版本
- 兼容 PHP 7.0+
- 定期测试最新版本的 PHP

### 11.3 浏览器兼容性
- 兼容主流浏览器：Chrome、Firefox、Safari、Edge
- 支持响应式设计

### 11.4 主题兼容性
- 与主流 WordPress 主题兼容
- 样式隔离，避免冲突

## 12. 国际化规范

### 12.1 文本域
- 使用 `WPCA_TEXT_DOMAIN` 常量定义文本域
- 文本域值为 `wp-clean-admin`

### 12.2 翻译函数
- 使用 WordPress 翻译函数，例如 `__()`、`_e()`、`_x()`、`_ex()`
- 所有可翻译字符串必须使用翻译函数

### 12.3 翻译文件
- 提供 POT 文件：`wp-clean-admin.pot`
- 支持多语言翻译，例如英文、中文
- 定期更新翻译文件

## 13. 贡献规范

### 13.1 贡献方式
- Fork 仓库
- 创建功能分支
- 提交代码
- 创建 Pull Request

### 13.2 代码要求
- 遵循项目编码规范
- 编写测试用例
- 更新文档
- 遵循提交信息格式

### 13.3 审查流程
- 代码审查
- 测试验证
- 合并到 develop 分支
- 定期发布到 main 分支

## 14. 总结

本项目规则旨在确保 WP Clean Admin 插件的质量、安全性和可维护性。所有开发者必须严格遵守这些规则，以保证插件的一致性和可靠性。

规则会根据项目发展和技术进步定期更新，开发者应关注最新版本的规则。