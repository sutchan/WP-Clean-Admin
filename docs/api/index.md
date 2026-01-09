# WP Clean Admin API 文档

## 1. 概述

WP Clean Admin 提供了丰富的 API 接口，允许开发者扩展和定制插件功能。本文档详细介绍了插件的核心 API、钩子和扩展机制。

## 2. 核心类

### 2.1 WPCleanAdmin\Core

核心类是插件的入口点，负责初始化所有模块。

#### 方法

- **getInstance()**: 获取单例实例
  - 返回: `WPCleanAdmin\Core` 实例

- **init()**: 初始化插件
  - 返回: `void`

- **activate()**: 插件激活回调
  - 返回: `void`

- **deactivate()**: 插件停用回调
  - 返回: `void`

### 2.2 WPCleanAdmin\Settings

设置管理类，负责处理插件设置。

#### 方法

- **getInstance()**: 获取单例实例
  - 返回: `WPCleanAdmin\Settings` 实例

- **get_settings()**: 获取插件设置
  - 参数: `$key` (可选) - 设置键名
  - 返回: `array|mixed` 设置值

- **update_settings()**: 更新插件设置
  - 参数: `$settings` - 设置数组
  - 返回: `bool` 更新结果

### 2.3 WPCleanAdmin\Extension_API

扩展 API 类，允许开发者创建插件扩展。

#### 方法

- **getInstance()**: 获取单例实例
  - 返回: `WPCleanAdmin\Extension_API` 实例

- **register_extension()**: 注册扩展
  - 参数: `$extension_data` - 扩展数据
  - 返回: `bool` 注册结果

- **execute_in_sandbox()**: 在沙箱中执行扩展代码
  - 参数: `$extension_code` - 扩展代码
  - 参数: `$options` (可选) - 沙箱选项
  - 返回: `array` 执行结果

### 2.4 WPCleanAdmin\Error_Handler

错误处理类，负责统一的错误处理和日志记录。

#### 方法

- **getInstance()**: 获取单例实例
  - 返回: `WPCleanAdmin\Error_Handler` 实例

- **log_message()**: 记录日志消息
  - 参数: `$message` - 日志消息
  - 参数: `$level` (可选) - 日志级别
  - 返回: `void`

## 3. 钩子和过滤器

### 3.1 动作钩子

- **wpca_init**: 插件初始化完成后触发
- **wpca_settings_saved**: 设置保存后触发
- **wpca_extension_activated**: 扩展激活后触发
- **wpca_extension_deactivated**: 扩展停用时触发

### 3.2 过滤器钩子

- **wpca_settings**: 过滤插件设置
- **wpca_menu_items**: 过滤后台菜单项
- **wpca_dashboard_widgets**: 过滤仪表盘小工具
- **wpca_sandbox_enabled**: 控制沙箱是否启用

## 4. 扩展开发

### 4.1 创建扩展

扩展是 WP Clean Admin 的功能模块，可以通过以下方式创建：

```php
// 注册扩展
$extension_api = WPCleanAdmin\Extension_API::getInstance();

$extension_data = array(
    'id' => 'my_extension',
    'name' => '我的扩展',
    'version' => '1.0.0',
    'description' => '这是一个示例扩展',
    'author' => '开发者名称',
    'file' => __FILE__,
    'active' => true
);

$extension_api->register_extension($extension_data);
```

### 4.2 扩展沙箱

扩展代码在安全的沙箱环境中执行，限制了内存使用和执行时间：

```php
// 在沙箱中执行代码
$result = $extension_api->execute_in_sandbox(
    'echo "Hello from sandbox!";',
    array(
        'memory_limit' => '32M',
        'time_limit' => 3
    )
);

// 检查执行结果
if ($result['success']) {
    echo '执行成功: ' . $result['result'];
} else {
    echo '执行失败: ' . $result['error'];
}
```

## 5. 核心函数

### 5.1 wpca_get_settings()

获取插件设置。

**参数**:
- `$key` (可选): 设置键名
- `$default` (可选): 默认值

**返回**:
- `mixed`: 设置值

### 5.2 wpca_update_settings()

更新插件设置。

**参数**:
- `$settings`: 设置数组

**返回**:
- `bool`: 更新结果

### 5.3 wpca_clean_admin_bar()

清理管理栏。

**参数**:
- 无

**返回**:
- `void`

### 5.4 wpca_clean_dashboard()

清理仪表盘。

**参数**:
- 无

**返回**:
- `void`

## 6. 示例代码

### 6.1 创建简单扩展

```php
<?php
/**
 * 示例扩展
 *
 * @package WPCleanAdmin
 * @version 1.0.0
 */

// 确保插件已加载
if (class_exists('WPCleanAdmin\Extension_API')) {
    // 注册扩展
    $extension_api = WPCleanAdmin\Extension_API::getInstance();
    
    $extension_data = array(
        'id' => 'example_extension',
        'name' => '示例扩展',
        'version' => '1.0.0',
        'description' => '一个简单的示例扩展',
        'author' => '开发者',
        'file' => __FILE__,
        'active' => true
    );
    
    $extension_api->register_extension($extension_data);
    
    // 添加设置项
    add_filter('wpca_settings', function($settings) {
        $settings['example'] = array(
            'enabled' => true,
            'option1' => 'value1'
        );
        return $settings;
    });
    
    // 添加菜单项
    add_filter('wpca_menu_items', function($items) {
        $items['example'] = array(
            'title' => '示例菜单项',
            'capability' => 'manage_options',
            'menu_slug' => 'wpca-example',
            'callback' => 'example_menu_callback'
        );
        return $items;
    });
    
    // 菜单项回调
    function example_menu_callback() {
        echo '<div class="wrap">';
        echo '<h1>示例页面</h1>';
        echo '<p>这是示例扩展的页面</p>';
        echo '</div>';
    }
}
```

### 6.2 使用错误处理

```php
<?php
// 获取错误处理器实例
$error_handler = WPCleanAdmin\Error_Handler::getInstance();

// 记录不同级别的日志
$error_handler->log_message('这是一条调试信息', 'debug');
$error_handler->log_message('这是一条信息', 'info');
$error_handler->log_message('这是一条警告', 'warning');
$error_handler->log_message('这是一条错误', 'error');
$error_handler->log_message('这是一条严重错误', 'critical');

// 设置日志级别
$error_handler->set_log_level('info');

// 获取当前日志级别
$current_level = $error_handler->get_log_level();
echo '当前日志级别: ' . $current_level;
```

## 7. 最佳实践

1. **使用命名空间**: 所有扩展代码应使用命名空间，避免冲突
2. **遵循编码规范**: 遵循 WordPress 和 PHP 编码规范
3. **安全第一**: 不要在扩展中使用危险函数，如 `eval()`、`exec()` 等
4. **性能优化**: 避免长时间运行的操作，使用缓存机制
5. **错误处理**: 使用插件的错误处理机制记录错误
6. **文档完善**: 为扩展编写详细的文档

## 8. 故障排除

### 8.1 常见问题

- **扩展注册失败**: 检查扩展数据是否完整，特别是 `id` 和 `file` 字段
- **沙箱执行失败**: 检查代码是否有语法错误，或是否超出了内存和时间限制
- **权限问题**: 确保用户有足够的权限执行操作

### 8.2 调试技巧

- **启用调试模式**: 在 `wp-config.php` 中设置 `WP_DEBUG` 和 `WP_DEBUG_LOG`
- **查看日志**: 检查 `wp-content/debug.log` 和插件的 `logs` 目录
- **使用错误处理器**: 使用 `Error_Handler` 记录详细的错误信息

## 9. 版本历史

- **1.8.0**: 添加了扩展 API 和沙箱执行环境
- **1.7.0**: 重构了设置管理系统
- **1.6.0**: 添加了错误处理和日志记录功能
- **1.5.0**: 实现了模块化架构

## 10. 联系与支持

- **GitHub 仓库**: https://github.com/sutchan/WPCleanAdmin
- **问题反馈**: https://github.com/sutchan/WPCleanAdmin/issues
- **贡献代码**: 欢迎提交 Pull Request

---

本文档由 WP Clean Admin 团队维护，如有任何疑问或建议，请随时反馈。
