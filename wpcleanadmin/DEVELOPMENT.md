# WP Clean Admin 开发指南

## 目录结构

```
wpcleanadmin/
├── assets/                 # 静态资源 (CSS, JS, images)
├── includes/
│   ├── modules/            # 新模块化架构 (推荐使用)
│   │   ├── admin/          # 后台管理功能
│   │   ├── core/           # 核心功能
│   │   └── utilities/      # 工具类
│   ├── ajax/               # AJAX 处理程序
│   ├── class-*.php         # 旧架构类文件 (向后兼容)
│   └── autoload.php        # 自动加载器
├── languages/              # 翻译文件
├── tests/                  # 测试文件
└── wp-clean-admin.php      # 插件主文件
```

## 开发环境设置

### 1. 安装依赖

```bash
composer install
```

### 2. 运行测试

```bash
composer test
```

### 3. 代码规范检查

```bash
composer phpcs
```

## 代码规范

### PHP 编码规范

- 遵循 WordPress 编码规范
- 使用 PSR-4 自动加载
- 类名使用 PascalCase
- 方法名使用 camelCase
- 函数和变量使用 snake_case
- 所有类和方法必须有 PHPDoc 注释

### 安全最佳实践

- 所有用户输入必须验证和清理
- 使用 `$wpdb->prepare()` 进行数据库查询
- 使用 nonce 验证所有表单和 AJAX 请求
- 检查用户权限 (`current_user_can()`)
- 所有输出到页面的内容必须转义

## 添加新功能

### 创建新模块

1. 在 `includes/modules/` 相应目录创建类文件
2. 使用正确的命名空间
3. 在 `Core::init_modules()` 中注册新模块
4. 编写单元测试

### 贡献代码

1. Fork 项目
2. 创建功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交更改 (`git commit -m 'feat: Add some amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 创建 Pull Request

## 发布流程

1. 更新版本号
2. 更新 CHANGELOG.md
3. 运行所有测试
4. 创建 Git 标签
5. 发布新版本
