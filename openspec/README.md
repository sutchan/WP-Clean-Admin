<!-- OPENSPEC:START -->
# WP Clean Admin OpenSpec 文档

本文档包含 WP Clean Admin 项目的所有 OpenSpec 规范文档，用于指导项目开发和维护。

## 文档结构

```
openspec/
├── README.md              # 本文档
├── project.md             # 项目上下文和约定
├── AGENTS.md              # AI 助手指令
├── CHANGELOG.md           # 版本更新记录
├── 技术栈报告_YYYYMMDD_HHMM.md  # 技术栈分析报告
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
│   └── archive/           # 已完成的变更
└── archive/               # 归档的旧文档
```

## 快速开始

### 新增功能

1. 在 `changes/` 目录下创建变更文件夹
2. 编写 `proposal.md` 描述变更内容
3. 创建 `tasks.md` 规划实施任务
4. 审批后创建 `specs/` 目录下的规范文档

### 查看规范

- 查阅 `project.md` 了解项目整体上下文
- 查看 `specs/` 目录了解各功能模块的详细规范
- 参考 `AGENTS.md` 了解 AI 助手工作指南

## 文档约定

### 命名规范

- 规范文档使用小写字母和连字符
- 报告文件包含时间戳：`报告名_YYYYMMDD_HHMM.md`
- 变更提案使用描述性名称

### 文档格式

所有文档必须包含 `<!-- OPENSPEC:START -->` 和 `<!-- OPENSPEC:END -->` 标记。

### 版本管理

- 主版本号 (MAJOR): 不兼容的 API 变更
- 次版本号 (MINOR): 向后兼容的新功能
- 修订号 (PATCH): 向后兼容的 bug 修复

## 相关链接

- GitHub: https://github.com/sutchan/WPCleanAdmin
- 项目文档: https://github.com/sutchan/WPCleanAdmin/tree/main/openspec

## 许可证

本文档遵循 GPLv2 或更高版本许可证。

<!-- OPENSPEC:END -->
