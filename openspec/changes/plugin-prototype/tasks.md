<!-- OPENSPEC:START -->
# 实施任务清单

## 阶段一：设计规范
- [x] 架构分层与模块边界定义
- [x] 前端交互规范（AJAX / nonce / 字段命名）
- [x] 安全基线（输入校验、输出转义、权限、SQL）
- [x] UI/UX 设计语言（布局、组件库、配色）

## 阶段二：PHP 模块骨架原型
- [x] 抽象基类 `Module_Base`（单例 + hooks + AJAX 注册）
- [x] 统一 AJAX 网关基类（固化 `_wpnonce` 字段名与权限校验）
- [x] 示例模块 `Dashboard`（展示标准写法）
- [x] PSR-4 autoload 适配

## 阶段三：前端 UI 原型
- [x] 后台设置页 HTML 结构原型
- [x] 后台 UI CSS（BEM + CSS 变量，对齐 WP 后台风格）
- [x] JS 原型（标准 `_wpnonce` 发送，含失败处理）

## 阶段四：验证
- [x] 骨架单元测试（`phpunit.xml.dist` 已就绪）
- [ ] 在有 PHP 环境实跑 `composer test` 验证

## 阶段五：归档
- [ ] 将设计规范归档至 `openspec/specs/plugin-architecture/`
- [ ] 更新 CHANGELOG
<!-- OPENSPEC:END -->
