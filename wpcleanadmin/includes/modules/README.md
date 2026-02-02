# WP Clean Admin 模块结构

## 模块组织方式

WP Clean Admin 插件采用模块化结构组织代码，以便于维护和扩展。模块结构如下：

```
modules/
├── admin/            # 后台管理相关模块
│   ├── ajax/         # AJAX 处理程序
│   ├── classes/      # 后台管理类
│   └── settings/     # 设置相关功能
├── core/             # 核心功能模块
│   └── classes/      # 核心类
├── frontend/         # 前端相关模块
│   └── classes/      # 前端类
└── utilities/        # 工具类模块
    └── classes/      # 工具类
```

## 模块说明

### admin 模块

包含与 WordPress 后台管理相关的功能：

- **ajax/**: 处理后台 AJAX 请求的处理程序
- **classes/**: 后台管理相关的类，如仪表盘、菜单管理、权限管理等
- **settings/**: 插件设置相关的功能，如设置页面、字段渲染等

### core 模块

包含插件的核心功能：

- **classes/**: 核心类，如核心初始化、错误处理等

### frontend 模块

包含与 WordPress 前端相关的功能：

- **classes/**: 前端相关的类，如前端资源管理等

### utilities 模块

包含通用工具类：

- **classes/**: 工具类，如缓存管理、国际化、资源管理等

## 使用方法

### 加载模块

模块通过 `WPCleanAdmin\Modules\Core\Classes\Core` 类的 `init_modules()` 方法加载。加载顺序为：

1. 首先加载核心模块
2. 然后加载管理模块
3. 最后加载工具模块

### 添加新模块

要添加新模块，请按照以下步骤操作：

1. 在相应的模块目录下创建新的类文件
2. 使用正确的命名空间（如 `WPCleanAdmin\Modules\Admin\Classes`）
3. 在 `Core` 类的 `init_modules()` 方法中添加对新模块的加载

## 命名规范

- **类名**: 使用 PascalCase，如 `Menu_Manager`
- **文件名**: 使用 `class-wpca-{kebab-case}.php` 格式，如 `class-wpca-menu-manager.php`
- **命名空间**: 使用 `WPCleanAdmin\Modules\{ModuleName}\Classes` 格式

## 最佳实践

1. **单一职责**: 每个类应该只负责一个功能
2. **依赖注入**: 避免硬编码依赖，使用依赖注入或服务容器
3. **错误处理**: 使用 `Error_Handler` 类处理错误和日志
4. **文档**: 为每个类和方法添加详细的注释
5. **测试**: 为新功能编写测试用例

## 版本控制

模块结构从版本 1.8.0 开始引入，所有模块文件的版本号应与插件版本保持一致。
