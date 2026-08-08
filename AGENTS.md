<!-- OPENSPEC:START -->
# WP Clean Admin AI 助手指令

本文档为 AI 助手提供工作指南，确保在处理 WP Clean Admin 项目时遵循正确的流程和规范。

## 1. 何时参考本文件

当请求包含以下情况时，请始终打开 `@/openspec/AGENTS.md`：
- 涉及项目规划或提案（如 proposal、spec、change、plan 等词汇）
- 引入新功能、破坏性变更、架构调整或重大性能/安全工作
- 需求模糊不清，需要权威规范作为参考
- 需要检查和整理 OpenSpec 文档

## 2. 目录边界约定（重要）

**`/wpcleanadmin` 为插件目录，只用于存放插件运行所需的代码文件**，包括：
- `wp-clean-admin.php`（插件主文件）
- `includes/`（PHP 类、模块、AJAX、autoload）
- `assets/css/`、`assets/js/`（插件后台样式与脚本）
- `languages/`（翻译 .po/.mo/.pot）
- `tests/`（单元测试）
- `composer.json`、`phpunit.xml.dist`（依赖与测试配置）

**以下项目级文件禁止放入 `/wpcleanadmin`，统一放在项目根目录**：
- 原型：`/prototype`（高保真 UI 原型、模块演示 PHP）
- 文档：`/docs`（审计报告、设计规范等）
- 规范：`/openspec`（OpenSpec 提案与规范）
- 项目文档：`CHANGELOG.md`、`DEVELOPMENT.md`、`README.*`、`AGENTS.md`、`LICENSE`

> 规则：凡非插件运行时必需的文件（原型、文档、规范、项目级 README/CHANGELOG），
> 一律置于仓库根目录对应目录，不得写入 `/wpcleanadmin`。

## 3. 项目概述

WP Clean Admin 是一个 WordPress 插件，用于管理后台清理和优化，版本 1.8.2。

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

## 3. OpenSpec 文档结构

```
openspec/
├── README.md              # 项目文档入口
├── project.md             # 项目上下文和约定
├── AGENTS.md              # AI 助手指令（本文档）
├── CHANGELOG.md           # 版本更新记录
├── specs/                 # 功能规范文档
│   ├── admin-cleanup/     # 后台清理规范
│   ├── api/               # API 接口规范
│   ├── database-management/ # 数据库管理规范
│   ├── diagnostics/       # 诊断模块规范
│   ├── menu-customization/  # 菜单定制规范
│   ├── performance-optimization/ # 性能优化规范
│   ├── permission-management/ # 权限管理规范
│   ├── plugin-architecture/ # 插件架构设计
│   └── security-enhancement/ # 安全增强规范
├── changes/               # 变更提案（待处理）
└── archive/               # 归档的旧文档
```

## 4. 工作流程

### 4.1 新增功能流程

1. 在 `changes/` 目录下创建变更文件夹
2. 编写 `proposal.md` 描述变更内容
3. 创建 `tasks.md` 规划实施任务
4. 审批后创建 `specs/` 目录下的规范文档
5. 实施代码变更
6. 更新 `CHANGELOG.md`
7. 归档变更提案

### 4.2 修改现有功能流程

1. 查看相关功能的规范文档
2. 更新规范文档（如需要）
3. 实施代码变更
4. 更新 `CHANGELOG.md`

### 4.3 修复 Bug 流程

1. 定位问题所在模块
2. 查看相关规范文档
3. 实施修复
4. 更新 `CHANGELOG.md`

## 5. 文档约定

### 5.1 命名规范

- 规范文档使用小写字母和连字符
- 报告文件包含时间戳：`报告名_YYYYMMDD_HHMM.md`
- 变更提案使用描述性名称

### 5.2 文档格式

所有文档必须包含 `<!-- OPENSPEC:START -->` 和 `<!-- OPENSPEC:END -->` 标记。

### 5.3 版本管理

- 主版本号 (MAJOR): 不兼容的 API 变更
- 次版本号 (MINOR): 向后兼容的新功能
- 修订号 (PATCH): 向后兼容的 bug 修复

## 6. 代码规范参考

### 6.1 PHP 编码规范

- 遵循 WordPress 编码规范
- 使用 PSR-4 自动加载
- 类名使用 PascalCase
- 方法名使用 camelCase
- 函数和变量使用 snake_case
- 所有类和方法必须有 PHPDoc 注释

### 6.2 安全最佳实践

- 所有用户输入必须验证和清理
- 使用 `$wpdb->prepare()` 进行数据库查询
- 使用 nonce 验证所有表单和 AJAX 请求
- 检查用户权限 (`current_user_can()`)
- 所有输出到页面的内容必须转义

## 7. 核心功能模块

| 模块 | 描述 | 状态 |
|------|------|------|
| Core | 插件核心初始化和模块协调 | 稳定 |
| Settings | 设置管理 | 稳定 |
| Dashboard | 仪表盘优化 | 稳定 |
| Cleanup | 清理功能（数据库、媒体、评论、内容） | 稳定 |
| Performance | 性能优化 | 稳定 |
| Permissions | 权限管理 | 稳定 |
| User_Roles | 用户角色管理 | 稳定 |
| Menu_Manager | 菜单管理 | 稳定 |
| Menu_Customizer | 菜单定制 | 稳定 |
| Login | 登录页面优化 | 稳定 |
| Database | 数据库管理 | 稳定 |
| Resources | 资源管理 | 稳定 |
| Reset | 设置重置 | 稳定 |
| AJAX | AJAX 处理 | 稳定 |
| i18n | 国际化支持 | 稳定 |
| Extension_API | 扩展 API | 稳定 |
| Diagnostics | 诊断模块 | 稳定 |

## 8. 重要链接

- GitHub: https://github.com/Tanox/WP-Clean-Admin
- 项目文档: https://github.com/Tanox/WP-Clean-Admin/tree/main/openspec
- WordPress.org: 待发布

## 9. 许可证

WP Clean Admin 插件遵循 GPLv2 或更高版本许可证。

<!-- OPENSPEC:END -->
