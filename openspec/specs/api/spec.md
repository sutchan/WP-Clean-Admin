<!-- OPENSPEC:START -->
# WP Clean Admin API 文档

## 1. 概述

WP Clean Admin API 提供了一系列用于管理和配置 WP Clean Admin 插件的编程接口，允许开发者通过代码与插件进行交互，实现自动化配置和扩展功能。

## 2. 基本信息

### 2.1 API 版本
当前 API 版本：1.8.0

### 2.2 命名空间
核心类位于 `WPCleanAdmin` 命名空间下，全局函数直接可用。

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
function wpca_get_settings( $key = '', $default = false ) {}
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

### 3.3 获取数据库设置

```php
/**
 * 获取数据库设置
 *
 * @param string $key 可选，设置键名
 * @param mixed $default 可选，默认值
 * @return mixed 数据库设置值或数组
 */
function wpca_get_database_settings( $key = '', $default = false ) {}
```

**参数**：
- `$key`：可选，要获取的设置键名
- `$default`：可选，当设置不存在时返回的默认值

**返回值**：
- 如果提供了 `$key`，返回对应设置值
- 如果未提供 `$key`，返回包含所有数据库设置的关联数组
- 如果设置不存在且提供了默认值，返回默认值

### 3.4 获取性能设置

```php
/**
 * 获取性能设置
 *
 * @param string $key 可选，设置键名
 * @param mixed $default 可选，默认值
 * @return mixed 性能设置值或数组
 */
function wpca_get_performance_settings( $key = '', $default = false ) {}
```

**参数**：
- `$key`：可选，要获取的设置键名
- `$default`：可选，当设置不存在时返回的默认值

**返回值**：
- 如果提供了 `$key`，返回对应设置值
- 如果未提供 `$key`，返回包含所有性能设置的关联数组
- 如果设置不存在且提供了默认值，返回默认值

### 3.5 检查用户权限

```php
/**
 * 检查当前用户是否有访问 WPCA 功能的权限
 *
 * @return bool True if user has access, false otherwise
 */
function wpca_current_user_can() {}
```

**返回值**：
- `true`：用户有访问权限
- `false`：用户没有访问权限

### 3.6 获取插件 URL

```php
/**
 * 获取插件 URL
 *
 * @param string $path 可选，插件目录相对路径
 * @return string 完整插件 URL
 */
function wpca_get_plugin_url( $path = '' ) {}
```

**参数**：
- `$path`：可选，插件目录相对路径

**返回值**：
- 完整的插件 URL，包含可选的路径

### 3.7 获取插件目录路径

```php
/**
 * 获取插件目录路径
 *
 * @param string $path 可选，插件目录相对路径
 * @return string 完整插件目录路径
 */
function wpca_get_plugin_dir( $path = '' ) {}
```

**参数**：
- `$path`：可选，插件目录相对路径

**返回值**：
- 完整的插件目录路径，包含可选的路径

### 3.8 获取资源 URL

```php
/**
 * 获取资源 URL
 *
 * @param string $asset_path 可选，资源相对路径
 * @return string 完整资源 URL
 */
function wpca_get_asset_url( $asset_path = '' ) {}
```

**参数**：
- `$asset_path`：可选，资源相对路径

**返回值**：
- 完整的资源 URL

### 3.9 获取模板路径

```php
/**
 * 获取模板路径
 *
 * @param string $template_name 模板名称
 * @return string 完整模板路径
 */
function wpca_get_template_path( $template_name ) {}
```

**参数**：
- `$template_name`：模板名称

**返回值**：
- 完整的模板路径

### 3.10 加载模板

```php
/**
 * 加载模板
 *
 * @param string $template_name 模板名称
 * @param array $args 可选，传递给模板的参数
 */
function wpca_load_template( $template_name, $args = array() ) {}
```

**参数**：
- `$template_name`：模板名称
- `$args`：可选，传递给模板的参数

### 3.11 显示管理通知

```php
/**
 * 显示管理通知
 *
 * @param string $message 通知消息
 * @param string $type 通知类型（success, error, warning, info）
 */
function wpca_admin_notice( $message, $type = 'info' ) {}
```

**参数**：
- `$message`：通知消息
- `$type`：通知类型，可选值：success, error, warning, info

### 3.12 获取插件版本

```php
/**
 * 获取插件版本
 *
 * @return string 插件版本号
 */
function wpca_get_version() {}
```

**返回值**：
- 插件版本号

### 3.13 检查调试模式

```php
/**
 * 检查插件是否处于调试模式
 *
 * @return bool True if debug mode is enabled, false otherwise
 */
function wpca_is_debug() {}
```

**返回值**：
- `true`：调试模式已启用
- `false`：调试模式未启用

### 3.14 记录日志

```php
/**
 * 记录调试日志
 *
 * @param mixed $message 要记录的消息
 * @param string $context 日志上下文
 */
function wpca_log( $message, $context = 'general' ) {}
```

**参数**：
- `$message`：要记录的消息
- `$context`：日志上下文

### 3.15 净化数组数据

```php
/**
 * 净化数组数据
 *
 * @param array $data 要净化的数据
 * @return array 净化后的数据
 */
function wpca_sanitize_array( $data ) {}
```

**参数**：
- `$data`：要净化的数据

**返回值**：
- 净化后的数据

### 3.16 获取当前标签

```php
/**
 * 获取当前标签
 *
 * @param string $default 可选，默认标签
 * @return string 当前标签
 */
function wpca_get_current_tab( $default = 'dashboard' ) {}
```

**参数**：
- `$default`：可选，默认标签

**返回值**：
- 当前标签

### 3.17 获取设置页面 URL

```php
/**
 * 获取设置页面 URL
 *
 * @param string $tab 可选，标签
 * @return string 设置页面 URL
 */
function wpca_get_settings_url( $tab = '' ) {}
```

**参数**：
- `$tab`：可选，标签

**返回值**：
- 设置页面 URL

### 3.18 检查当前页面是否为设置页面

```php
/**
 * 检查当前页面是否为 WPCA 设置页面
 *
 * @return bool True if current page is WPCA settings page, false otherwise
 */
function wpca_is_settings_page() {}
```

**返回值**：
- `true`：当前页面是 WPCA 设置页面
- `false`：当前页面不是 WPCA 设置页面

### 3.19 获取插件菜单 slug

```php
/**
 * 获取插件菜单 slug
 *
 * @return string 插件菜单 slug
 */
function wpca_get_menu_slug() {}
```

**返回值**：
- 插件菜单 slug

### 3.20 获取插件文本域

```php
/**
 * 获取插件文本域
 *
 * @return string 插件文本域
 */
function wpca_get_text_domain() {}
```

**返回值**：
- 插件文本域

### 3.21 检查 WordPress 版本

```php
/**
 * 检查 WordPress 版本是否大于或等于指定版本
 *
 * @param string $version 要检查的版本
 * @return bool True if WordPress version is greater than or equal to specified version, false otherwise
 */
function wpca_is_wp_version_gte( $version ) {}
```

**参数**：
- `$version`：要检查的版本

**返回值**：
- `true`：WordPress 版本大于或等于指定版本
- `false`：WordPress 版本小于指定版本

### 3.22 获取管理页面标题

```php
/**
 * 获取管理页面标题
 *
 * @param string $tab 当前标签
 * @return string 管理页面标题
 */
function wpca_get_admin_page_title( $tab = '' ) {}
```

**参数**：
- `$tab`：当前标签

**返回值**：
- 管理页面标题

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
| 1.8.0 | 2026-01-02 | 更新了API文档，添加了所有实际存在的函数 |
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

<!-- OPENSPEC:END -->