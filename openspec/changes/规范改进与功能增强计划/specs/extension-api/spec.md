<!-- OPENSPEC:START -->
## 设计参考

### 架构设计
- **详细设计**: [plugin-architecture/design.md](../plugin-architecture/design.md)

## ADDED Requirements

### Requirement: 扩展注册系统
系统SHALL提供扩展注册功能，允许第三方开发者注册自定义扩展

#### Scenario: 成功注册扩展
- **WHEN** 开发者调用 `Extension_API::register_extension()` 注册扩展
- **THEN** 系统将验证扩展配置并将其添加到扩展列表
- **AND** 扩展的钩子、设置页面和权限将被注册到系统
- **AND** 系统将返回注册结果

#### Scenario: 注册失败
- **WHEN** 开发者提供的扩展配置缺少必要字段
- **THEN** 系统将返回验证错误
- **AND** 扩展不会被添加到系统

### Requirement: 扩展生命周期管理
系统SHALL提供扩展的安装、激活、停用和卸载生命周期管理

#### Scenario: 安装扩展
- **WHEN** 管理员从扩展市场安装扩展
- **THEN** 系统将下载并解压扩展文件
- **AND** 系统将验证扩展签名
- **AND** 系统将运行扩展的安装脚本

#### Scenario: 激活扩展
- **WHEN** 管理员激活已安装的扩展
- **THEN** 系统将运行扩展的激活脚本
- **AND** 扩展的钩子将被注册
- **AND** 扩展的设置页面将被添加

#### Scenario: 停用扩展
- **WHEN** 管理员停用扩展
- **THEN** 系统将运行扩展的停用脚本
- **AND** 扩展的钩子将被移除
- **AND** 扩展的数据将被保留

#### Scenario: 卸载扩展
- **WHEN** 管理员卸载扩展
- **THEN** 系统将运行扩展的卸载脚本
- **AND** 扩展的文件将被删除
- **AND** 扩展的数据将被清除

### Requirement: 扩展隔离
系统SHALL提供扩展隔离机制，防止扩展之间或扩展与核心之间的冲突

#### Scenario: 沙箱执行
- **WHEN** 扩展代码在沙箱中执行
- **THEN** 扩展无法访问被禁止的函数
- **AND** 扩展无法访问未授权的数据
- **AND** 扩展的内存和时间使用受到限制

#### Scenario: 钩子命名空间
- **THEN** 扩展的钩子使用 `wpca_ext_*` 命名空间
- **AND** 扩展的设置使用 `wpca_ext_*` 选项名
- **AND** 扩展的菜单使用 `wpca-ext-*` 菜单 slug

### Requirement: 扩展市场集成
系统SHALL提供扩展市场集成功能

#### Scenario: 浏览扩展
- **WHEN** 管理员访问扩展市场页面
- **THEN** 系统将获取扩展列表
- **AND** 系统将显示扩展的标题、描述、评分和价格
- **AND** 系统将支持分类和搜索筛选

#### Scenario: 安装扩展
- **WHEN** 管理员点击安装扩展
- **THEN** 系统将验证扩展签名
- **AND** 系统将下载并安装扩展
- **AND** 系统将显示安装结果

#### Scenario: 检查更新
- **WHEN** 系统检查扩展更新
- **THEN** 系统将获取已安装扩展的版本信息
- **AND** 系统将对比扩展市场的最新版本
- **AND** 系统将显示可用的更新

### Requirement: 扩展 API 接口
系统SHALL提供丰富的 API 接口供扩展使用

#### Scenario: 获取插件设置
- **WHEN** 扩展调用 `wpca_get_settings()` 函数
- **THEN** 系统将返回当前插件设置
- **AND** 扩展可以访问除敏感数据外的所有设置

#### Scenario: 注册自定义钩子
- **WHEN** 扩展调用 `add_action()` 或 `add_filter()` 注册钩子
- **THEN** 系统将验证钩子是否在白名单中
- **AND** 钩子将被添加到相应的钩子列表

#### Scenario: 添加菜单页面
- **WHEN** 扩展调用 `add_menu_page()` 添加菜单
- **THEN** 系统将自动添加扩展标识
- **AND** 菜单将显示在正确的位置

### Requirement: 扩展配置管理
系统SHALL提供扩展配置管理功能

#### Scenario: 保存扩展设置
- **WHEN** 扩展调用 `wpca_save_extension_settings()` 保存设置
- **THEN** 系统将验证设置数据
- **AND** 系统将保存设置到数据库
- **AND** 系统将触发 `wpca_ext_settings_saved` 钩子

#### Scenario: 获取扩展设置
- **WHEN** 扩展调用 `wpca_get_extension_settings()` 获取设置
- **THEN** 系统将验证扩展权限
- **AND** 系统将返回设置数据

#### Scenario: 重置扩展设置
- **WHEN** 扩展调用 `wpca_reset_extension_settings()` 重置设置
- **THEN** 系统将恢复默认设置
- **AND** 系统将触发 `wpca_ext_settings_reset` 钩子

### Requirement: 扩展安全性
系统SHALL提供扩展安全机制

#### Scenario: 代码签名验证
- **WHEN** 安装扩展时
- **THEN** 系统将验证扩展的签名
- **AND** 未签名的扩展将被标记为风险

#### Scenario: 权限检查
- **WHEN** 扩展访问敏感功能时
- **THEN** 系统将验证用户权限
- **AND** 未授权的访问将被拒绝

#### Scenario: 安全审计日志
- **WHEN** 扩展执行敏感操作时
- **THEN** 系统将记录操作日志
- **AND** 日志将包含时间、用户、操作和结果

## MODIFIED Requirements

### Requirement: 扩展启用状态
系统SHALL允许配置扩展的启用状态

#### Scenario: 成功启用扩展
- **WHEN** 管理员在扩展管理页面启用了扩展
- **THEN** 系统将运行扩展的启用逻辑
- **AND** 扩展的功能将对用户可见

### Requirement: 扩展依赖处理
系统SHALL处理扩展之间的依赖关系

#### Scenario: 安装依赖检查
- **WHEN** 安装扩展时
- **THEN** 系统将检查依赖的其他扩展
- **AND** 如果依赖不存在，系统将提示安装

#### Scenario: 依赖冲突检测
- **WHEN** 安装扩展时
- **THEN** 系统将检测版本冲突
- **AND** 如果存在冲突，系统将阻止安装

## Design References

### 技术实现

#### 扩展注册

```php
Extension_API::register_extension(array(
    'id' => 'my-extension',
    'name' => '我的扩展',
    'version' => '1.0.0',
    'description' => '扩展描述',
    'author' => '开发者名称',
    'author_uri' => 'https://example.com',
    'text_domain' => 'my-extension',
    'hooks' => array(
        'wpca_before_cleanup' => 'my_cleanup_handler',
        'wpca_after_optimize' => 'my_optimize_handler',
    ),
    'settings_page' => 'my-extension-settings',
    'permissions' => array('manage_options'),
    'resources' => array(
        'css' => array('my-extension.css'),
        'js' => array('my-extension.js'),
    ),
));
```

#### 扩展生命周期

```php
// 安装时调用
function my_extension_install() {
    // 创建数据库表
    // 初始化默认设置
}

// 激活时调用
function my_extension_activate() {
    // 注册钩子
    // 刷新路由规则
}

// 停用时调用
function my_extension_deactivate() {
    // 清理临时数据
    // 移除钩子
}

// 卸载时调用
function my_extension_uninstall() {
    // 删除数据库表
    // 清除设置
    // 删除文件
}

register_extension_install_hook('my-extension', 'my_extension_install');
register_extension_activation_hook('my-extension', 'my_extension_activate');
register_extension_deactivation_hook('my-extension', 'my_extension_deactivate');
register_extension_uninstall_hook('my-extension', 'my_extension_uninstall');
```

#### 沙箱执行

```php
$sandbox = new Extension_Sandbox();

$result = $sandbox->execute($extension_code, array(
    'memory_limit' => '64M',
    'time_limit' => 5,
    'allowed_functions' => array('wpca_*', 'wp_*', 'add_*'),
    'blocked_functions' => array('eval', 'exec', 'shell_exec'),
));

if (!$result->is_success()) {
    // 处理错误
    wpca_admin_notice('扩展执行失败: ' . $result->get_error());
}
```

### 相关文件

- `includes/class-wpca-extension-api.php` - 扩展 API 核心类
- `includes/class-wpca-composer.php` - Composer 依赖管理
- `includes/class-wpca-core.php` - 核心类（扩展集成）

### 相关 API

```php
// 注册扩展
Extension_API::register_extension($config);

// 获取扩展列表
Extension_API::get_extensions();

// 获取单个扩展
Extension_API::get_extension($id);

// 检查扩展状态
Extension_API::is_extension_active($id);

// 安装扩展
Extension_API::install_extension($id);

// 激活扩展
Extension_API::activate_extension($id);

// 停用扩展
Extension_API::deactivate_extension($id);

// 卸载扩展
Extension_API::uninstall_extension($id);

// 保存扩展设置
wpca_save_extension_settings($id, $settings);

// 获取扩展设置
wpca_get_extension_settings($id);
```

### 扩展市场 API

```php
// 获取扩展列表
WPCA_Extension_Market::get_extensions($filter);

// 获取扩展详情
WPCA_Extension_Market::get_extension_info($id);

// 安装扩展
WPCA_Extension_Market::install_extension($id);

// 检查更新
WPCA_Extension_Market::check_updates($installed_extensions);

// 搜索扩展
WPCA_Extension_Market::search_extensions($query);
```

### 安全相关 API

```php
// 验证扩展签名
WPCA_Security::verify_extension_signature($id);

// 检查扩展权限
WPCA_Security::check_extension_permission($id, $capability);

// 获取扩展安全评分
WPCA_Security::get_extension_security_score($id);

// 记录安全日志
WPCA_Security::log_extension_action($id, $action, $details);
```

<!-- OPENSPEC:END -->
