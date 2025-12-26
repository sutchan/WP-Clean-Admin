# WP Clean Admin API 文档

## 1. 概述

WP Clean Admin API 提供了一系列用于管理和配置 WP Clean Admin 插件的编程接口，允许开发者通过代码与插件进行交互，实现自动化配置和扩展功能。

## 2. 基本信息

### 2.1 API 版本
当前 API 版本：1.7.15

### 2.2 命名空间
所有 API 函数都位于 `WPCleanAdmin` 命名空间下。

### 2.3 前缀
全局函数使用 `wpca_` 前缀，例如 `wpca_get_settings()`。

## 3. 核心 API

### 3.1 获取插件设置

```php
/**
 * 获取 WP Clean Admin 插件设置
 *
 * @param string $key 可选，设置键名
 * @param mixed $default 可选，默认值
 * @return mixed 插件设置值或数组
 */
function wpca_get_settings( $key = null, $default = null ) {}
```

**参数**：
- `$key`：可选，要获取的设置键名
- `$default`：可选，当设置不存在时返回的默认值

**返回值**：
- 如果提供了 `$key`，返回对应设置值
- 如果未提供 `$key`，返回包含所有插件设置的关联数组
- 如果设置不存在且提供了默认值，返回默认值

### 3.2 更新插件设置

```php
/**
 * 更新 WP Clean Admin 插件设置
 *
 * @param array $settings 要更新的设置数组
 * @return bool 更新是否成功
 */
function wpca_update_settings( $settings ) {}
```

**参数**：
- `$settings`：要更新的设置数组

**返回值**：
- `true`：更新成功
- `false`：更新失败

## 4. 菜单管理 API

### 4.1 获取菜单管理器实例

```php
/**
 * 获取菜单管理器单例实例
 *
 * @return WPCleanAdmin\Menu_Manager 菜单管理器实例
 */
WPCleanAdmin\Menu_Manager::getInstance();
```

### 4.2 获取所有菜单项目

```php
/**
 * 获取所有菜单项目
 *
 * @return array 菜单项目数组
 */
WPCleanAdmin\Menu_Manager::getInstance()->get_menu_items();
```

**返回值**：
- 包含所有顶级菜单和子菜单的数组

### 4.3 保存菜单项目

```php
/**
 * 保存菜单项目
 *
 * @param array $menu_items 要保存的菜单项目
 * @return array 保存结果
 */
WPCleanAdmin\Menu_Manager::getInstance()->save_menu_items( $menu_items );
```

**参数**：
- `$menu_items`：要保存的菜单项目数组

**返回值**：
- 包含保存结果的数组，格式为 `array( 'success' => bool, 'message' => string )`

### 4.4 应用角色基础菜单限制

```php
/**
 * 应用角色基础菜单限制
 */
WPCleanAdmin\Menu_Manager::getInstance()->apply_role_based_menu_restrictions();
```

## 5. 权限管理 API

### 5.1 获取权限管理器实例

```php
/**
 * 获取权限管理器单例实例
 *
 * @return WPCleanAdmin\Permissions 权限管理器实例
 */
WPCleanAdmin\Permissions::getInstance();
```

### 5.2 检查用户是否具有功能权限

```php
/**
 * 检查用户是否具有访问特定功能的权限
 *
 * @param string $feature 功能名称
 * @param int|null $user_id 用户 ID，默认为当前用户
 * @return bool 是否具有权限
 */
WPCleanAdmin\Permissions::getInstance()->has_feature_permission( $feature, $user_id = null );
```

**参数**：
- `$feature`：功能名称
- `$user_id`：用户 ID，默认为当前用户

**返回值**：
- `true`：具有权限
- `false`：不具有权限

### 5.3 获取用户权限

```php
/**
 * 获取用户的权限信息
 *
 * @param int|null $user_id 用户 ID，默认为当前用户
 * @return array 用户权限信息
 */
WPCleanAdmin\Permissions::getInstance()->get_user_permissions( $user_id = null );
```

**参数**：
- `$user_id`：用户 ID，默认为当前用户

**返回值**：
- 包含用户权限信息的数组，包括功能权限、角色和能力

## 6. 登录管理 API

### 6.1 获取登录管理器实例

```php
/**
 * 获取登录管理器单例实例
 *
 * @return WPCleanAdmin\Login 登录管理器实例
 */
WPCleanAdmin\Login::getInstance();
```

### 6.2 生成双因素认证密钥

```php
/**
 * 为用户生成双因素认证密钥
 *
 * @param int $user_id 用户 ID
 */
WPCleanAdmin\Login::getInstance()->generate_two_factor_secret( $user_id );
```

**参数**：
- `$user_id`：用户 ID

### 6.3 获取 QR 码 URL

```php
/**
 * 获取双因素认证的 QR 码 URL
 *
 * @param int $user_id 用户 ID
 * @return string QR 码 URL
 */
WPCleanAdmin\Login::getInstance()->get_qr_code_url( $user_id );
```

**参数**：
- `$user_id`：用户 ID

**返回值**：
- QR 码 URL 字符串

### 6.4 验证双因素认证代码

```php
/**
 * 验证双因素认证代码
 *
 * @param string $code 认证代码
 * @param int $user_id 用户 ID
 * @return bool 代码是否有效
 */
WPCleanAdmin\Login::getInstance()->verify_two_factor_code( $code, $user_id );
```

**参数**：
- `$code`：6 位认证代码
- `$user_id`：用户 ID

**返回值**：
- `true`：代码有效
- `false`：代码无效

## 7. 性能优化 API

### 7.1 获取性能管理器实例

```php
/**
 * 获取性能管理器单例实例
 *
 * @return WPCleanAdmin\Performance 性能管理器实例
 */
WPCleanAdmin\Performance::getInstance();
```

## 8. 数据库管理 API

### 8.1 获取数据库管理器实例

```php
/**
 * 获取数据库管理器单例实例
 *
 * @return WPCleanAdmin\Database 数据库管理器实例
 */
WPCleanAdmin\Database::getInstance();
```

## 9. 核心模块 API

### 9.1 获取核心实例

```php
/**
 * 获取核心类单例实例
 *
 * @return WPCleanAdmin\Core 核心类实例
 */
WPCleanAdmin\Core::getInstance();
```

### 9.2 激活插件

```php
/**
 * 激活插件
 */
WPCleanAdmin\Core::getInstance()->activate();
```

### 9.3 停用插件

```php
/**
 * 停用插件
 */
WPCleanAdmin\Core::getInstance()->deactivate();
```

## 10. 应用场景

### 10.1 示例：获取并更新插件设置

```php
// 获取当前设置
$settings = wpca_get_settings();

// 修改设置
$settings['general']['clean_admin_bar'] = 1;
$settings['menu']['simplify_admin_menu'] = 1;

// 保存设置
wpca_update_settings( $settings );
```

### 10.2 示例：为用户生成双因素认证密钥

```php
// 获取当前用户 ID
$user_id = get_current_user_id();

// 生成双因素认证密钥
WPCleanAdmin\Login::getInstance()->generate_two_factor_secret( $user_id );

// 获取 QR 码 URL
$qr_code_url = WPCleanAdmin\Login::getInstance()->get_qr_code_url( $user_id );

// 显示 QR 码
echo '<img src="' . esc_url( $qr_code_url ) . '" alt="Two-factor Authentication QR Code" />';
```

### 10.3 示例：检查用户权限

```php
// 检查当前用户是否具有某个功能的权限
if ( WPCleanAdmin\Permissions::getInstance()->has_feature_permission( 'manage_options' ) ) {
    // 执行需要权限的操作
    echo 'You have permission to manage options.';
} else {
    echo 'You do not have permission to manage options.';
}
```

## 11. 最佳实践

1. **权限检查**：在调用 API 之前，始终检查用户是否具有适当的权限
2. **输入验证**：验证所有输入数据，确保其格式正确且安全
3. **错误处理**：实现适当的错误处理机制，处理 API 调用可能出现的错误
4. **性能考虑**：避免在循环中频繁调用 API 函数，考虑缓存结果
5. **兼容性**：确保 API 调用与 WordPress 版本和 PHP 版本兼容

## 12. 版本历史

| 版本 | 日期 | 变更内容 |
|------|------|----------|
| 1.7.15 | 2025-11-30 | 初始 API 文档 |

## 13. 常见问题

### 13.1 API 函数返回错误结果

**问题**：API 函数返回错误结果或抛出异常

**解决方案**：
- 检查 WordPress 版本和 PHP 版本是否兼容
- 确保插件已正确激活
- 检查用户是否具有适当的权限
- 验证输入参数的格式和类型
- 查看 WordPress 错误日志获取详细信息

### 13.2 双因素认证无法正常工作

**问题**：双因素认证功能无法正常工作

**解决方案**：
- 确保双因素认证已在插件设置中启用
- 检查用户是否已生成双因素认证密钥
- 确保认证代码输入正确
- 检查服务器时间是否准确

## 14. 支持与反馈

如果您在使用 WP Clean Admin API 时遇到问题或有任何建议，欢迎通过以下方式联系我们：

- GitHub Issues：https://github.com/sutchan/WPCleanAdmin/issues
- 项目文档：https://github.com/sutchan/WPCleanAdmin/tree/main/openspec
- 电子邮件：[您的电子邮件地址]

## 15. 许可证

WP Clean Admin API 遵循 GPLv2 或更高版本许可证。