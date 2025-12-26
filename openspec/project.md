# Project Context

## Purpose
WP Clean Admin 是一个全面的 WordPress 后台清理和优化插件，旨在帮助管理员清理和优化 WordPress 后台，提高网站性能和管理效率。

## Tech Stack
- PHP 7.0+
- WordPress 5.0+
- MySQL 5.6+
- JavaScript (ES6+)
- CSS3

## Project Conventions

### Code Style
- 采用 WordPress 编码规范
- 使用 PSR-4 自动加载
- 类名采用 PascalCase 命名
- 方法名采用 camelCase 命名
- 函数名采用 snake_case 命名，以 `wpca_` 为前缀
- 变量名采用 snake_case 命名
- 常量名采用全大写加下划线命名，以 `WPCA_` 为前缀
- 代码缩进使用 4 个空格
- 文件编码为 UTF-8
- 行尾序列为 CRLF

### Architecture Patterns
- 模块化设计：功能划分为独立模块，便于维护和扩展
- 单例模式：核心类和功能模块使用单例模式
- 钩子机制：深度集成 WordPress 钩子系统
- 面向对象设计：采用面向对象编程范式
- 依赖注入：通过核心类初始化和协调其他模块

### Testing Strategy
- 单元测试：对核心功能进行单元测试
- 集成测试：测试模块之间的集成
- 手动测试：验证用户界面和功能完整性
- 兼容性测试：测试不同 WordPress 版本和主题的兼容性
- 代码覆盖率：确保代码覆盖率不低于 80%

### Git Workflow
- 主分支：main
- 开发分支：dev
- 功能分支：feature/功能名
- 修复分支：fix/问题描述
- 提交消息格式：<类型>: <描述>，如 feat: 添加新功能，fix: 修复bug

## Domain Context
WordPress 插件开发领域，专注于后台管理优化和性能提升。插件通过 WordPress 钩子系统扩展 WordPress 功能，无需修改核心文件。

## Important Constraints
- 不修改 WordPress 核心文件
- 兼容 WordPress 5.0+ 和 PHP 7.0+
- 遵循 WordPress 安全最佳实践
- 保持插件体积小，加载速度快
- 遵循 WordPress 插件开发规范
- 提供完整的国际化支持

## External Dependencies
- WordPress 核心功能
- MySQL 数据库
- PHP 标准库
- WordPress 钩子系统
- WordPress 函数和类
