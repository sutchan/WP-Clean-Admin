# WP Clean Admin - Translation Guide

## 翻译文件结构

本目录包含 WP Clean Admin 插件的国际化翻译文件：

- `wp-clean-admin.pot` - 翻译模板文件，包含所有可翻译字符串
- `wp-clean-admin-en_US.po` - 英文翻译文件
- `wp-clean-admin-zh_CN.po` - 简体中文翻译文件

## 为什么翻译未生效？

WordPress 需要编译后的 `.mo` 文件来加载翻译，而不是 `.po` 文件。当前目录中缺少这些编译后的文件。

## 如何编译 .mo 文件

### 方法 1：使用 Poedit（推荐）

1. 下载并安装 Poedit：https://poedit.net/
2. 打开 `.po` 文件
3. 点击 "文件" > "保存"
4. Poedit 会自动生成对应的 `.mo` 文件

### 方法 2：使用 WordPress 插件

1. 安装 "Loco Translate" 插件
2. 在 WordPress 后台导航到 "Loco Translate" > "插件"
3. 找到 "WP Clean Admin"
4. 点击 "编辑" 按钮
5. 点击 "保存" 按钮，Loco Translate 会自动编译 `.mo` 文件

### 方法 3：使用命令行工具

如果您有 PHP 环境，可以使用 WordPress 提供的 `msgfmt` 工具：

```bash
# 安装 gettext 工具（包含 msgfmt）
# Ubuntu/Debian
sudo apt-get install gettext

# macOS
brew install gettext

# 编译 .mo 文件
msgfmt wp-clean-admin-zh_CN.po -o wp-clean-admin-zh_CN.mo
msgfmt wp-clean-admin-en_US.po -o wp-clean-admin-en_US.mo
```

## 验证翻译是否生效

1. 编译 `.mo` 文件后，确保它们与 `.po` 文件在同一目录
2. 在 WordPress 后台，导航到 "设置" > "常规"
3. 将 "站点语言" 设置为您要测试的语言（例如 "简体中文"）
4. 刷新页面，查看 WP Clean Admin 的界面是否已翻译

## 如何添加新语言

1. 复制 `wp-clean-admin.pot` 文件，并重命名为 `wp-clean-admin-{locale}.po`，其中 `{locale}` 是语言代码（例如 `fr_FR` 表示法语）
2. 使用 Poedit 或其他翻译工具编辑 `.po` 文件
3. 保存文件，生成对应的 `.mo` 文件
4. 将新的 `.po` 和 `.mo` 文件上传到 `languages` 目录

## 翻译文件命名规范

WordPress 要求翻译文件遵循以下命名规范：

```
wp-clean-admin-{locale}.po
wp-clean-admin-{locale}.mo
```

其中 `{locale}` 是语言代码，格式为 `language_COUNTRY`，例如：
- `en_US` - 英文（美国）
- `zh_CN` - 中文（简体）
- `zh_TW` - 中文（繁体）
- `fr_FR` - 法语（法国）
- `de_DE` - 德语（德国）

## 故障排除

### 翻译仍未生效？

1. **检查 .mo 文件是否存在**：确保每个 `.po` 文件都有对应的 `.mo` 文件
2. **检查文件权限**：确保 `.mo` 文件可被服务器读取
3. **检查文件命名**：确保文件名符合 `wp-clean-admin-{locale}.mo` 格式
4. **检查 WordPress 语言设置**：确保您的 WordPress 站点语言设置正确
5. **清除缓存**：如果您使用了缓存插件，请清除缓存
6. **检查文本域**：确保翻译函数中的文本域为 `'wp-clean-admin'`

### 示例翻译函数

正确的翻译函数调用方式：

```php
// 基本翻译
__('Text to translate', 'wp-clean-admin');

// 直接输出翻译
_e('Text to translate', 'wp-clean-admin');

// 带上下文的翻译
_x('Text to translate', 'Context description', 'wp-clean-admin');

// 复数形式翻译
_n('Singular text', 'Plural text', $count, 'wp-clean-admin');

// 带上下文的复数形式翻译
_nx('Singular text', 'Plural text', $count, 'Context description', 'wp-clean-admin');
```

## 贡献翻译

如果您想贡献翻译，请：
1. 翻译 `.po` 文件
2. 编译生成 `.mo` 文件
3. 提交 Pull Request 到 GitHub 仓库：https://github.com/sutchan/WP-Clean-Admin

感谢您对 WP Clean Admin 国际化的贡献！