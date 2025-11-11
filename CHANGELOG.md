# WP Clean Admin Changelog

## 1.7.1 - 2024-01-30

### Fixed
- 增强了所有核心文件的安全检查，添加了函数存在性检查和变量访问保护
- 修复了直接访问文件的安全问题，添加了更严格的ABSPATH检查
- 优化了flush_rewrite_rules()的调用时机，避免插件激活/停用时的性能问题
- 改进了languages目录下脚本的安全处理，添加了完整的错误处理机制

## 1.7.0 - 2024-01-27

### Added
- 新增了`WPCA_Menu_Manager`类，专门负责菜单项显示/隐藏功能
- 实现了菜单项保护机制，确保核心功能菜单不被误隐藏
- 添加了菜单统计功能，显示菜单项数量、隐藏状态等信息

### Changed
- 重构了菜单管理架构，将菜单隐藏功能从Menu_Customizer迁移到专门的Menu_Manager类
- 优化了菜单隐藏的CSS生成逻辑，提高了选择器的精确性
- 改进了AJAX处理流程，增加了更详细的错误处理和日志记录

### Fixed
- 解决了某些子菜单无法正确隐藏的问题
- 修复了菜单缓存清理不完整的问题

## 1.6.1 - 2024-01-26

### Added
- 创建了《用户反馈收集与处理指南》文档，提供系统化的用户反馈管理流程
- 更新了文档搜索索引，添加了新文档的索引条目
- 在README.md中添加了新文档的导航链接

### Fixed
- 修复了运维手册中的章节重复问题
- 优化了文档内部结构和内容组织
- 修正了项目管理文档中的未来日期问题

### Changed
- 更新了所有文档的版本号为v1.6.1
- 更新了所有文档的最后更新日期

## 1.6.0 - 2024-01-15

### Added
- **Database Optimization Module**
  - Created dedicated database optimization class with table optimization functionality
  - Implemented comprehensive database cleanup capabilities (revisions, drafts, spam comments, etc.)
  - Added scheduled database maintenance tasks
  - Created database settings page with multiple tabs (Optimization, Cleanup, Scheduled Tasks, Info)
  - Implemented AJAX handlers for real-time database operations
  - Added database information display with table statistics
  - Created database cleanup statistics and reporting

- **Performance Testing Enhancements**
  - Enhanced performance statistics testing with detailed metric validation
  - Added specific checks for required performance metrics (total_samples, total_time, total_queries, etc.)
  - Improved test results reporting with more detailed feedback

### Changed

- **Architecture Enhancement**: Separated database logic from performance module for better code organization
- **Security**: Added nonce verification and input sanitization for all database operations
- **User Experience**: Added progress indicators and detailed results for optimization tasks
- **Multilingual Support**: Added all new strings to translation files
- **Testing**: Created dedicated database optimization test file and improved performance testing
- **Code Cleanup**: Removed duplicate code in test files

### Changed Files

- `class-wpca-database.php`: Enhanced database optimization functionality
- `class-wpca-database-settings.php`: New database settings management class
- `wpca-database.js`: Added AJAX support for database operations
- `wpca-database.css`: Added styles for database settings pages
- `test-wpca-database.php`: New database optimization test file
- `test-wpca-performance.php`: Improved performance testing with detailed metric validation
- `wp-clean-admin.php`: Updated version number and resource loading
- `wp-clean-admin-zh_CN.po`: Updated Chinese translations
- `wp-clean-admin-en_US.po`: Updated English translations
- `wp-clean-admin.pot`: Updated translation template

## 1.5.0 - 2024-01-10

### Added

- **Enhanced Login Page Customization**
  - Added support for 8 different login page style options (Default, Modern, Minimal, Dark, Gradient, Glassmorphism, Neumorphism, and Custom)
  - Implemented custom CSS support for advanced login page styling
  - Added login page elements control (Language Switcher, Back to Home Link, Register Link, Remember Me Checkbox)
  - Created AJAX handlers for real-time login settings management
  - Added login logo and background image customization with preview functionality
  - Implemented live preview for login page changes

### Changed

- **Security Enhancements**: Added nonce verification for all login settings operations
- **Code Quality**: Implemented singleton pattern for login management class
- **Style System**: Created comprehensive CSS framework for different login page styles
- **Multilingual Support**: Added all new strings to translation files (POT, PO)
- **User Experience**: Added status messages and error handling for login settings operations

### Changed Files

- `class-wpca-login.php`: Enhanced login page customization functionality
- `class-wpca-settings.php`: Updated settings to support new login page options
- `wpca-login.js`: Added AJAX support and style preset management
- `wpca-login-styles.css`: Added styles for new login page options
- `wp-clean-admin.pot`: Added new translation strings
- `wp-clean-admin-en_US.po`: Added English translations for new features
- `wp-clean-admin-zh_CN.po`: Added Chinese translations for new features

## 1.4.3 - 2024-01-08

### Fixed

- Fixed resource management functionality by adding singleton pattern implementation to WPCA_Resources class
- Added null checks for resources property in WPCA_Performance_Settings class
- Ensured proper initialization of resource management components

## 1.4.2 - 2024-01-05

### Fixed

- Fixed missing PHP closing tags in multiple files
- Fixed duplicate closing curly brace in class-wpca-ajax.php
- Fixed version number inconsistency between files
- Ensured all PHP files follow proper syntax standards
- Fixed class loading order issue by changing direct performance class includes to autoloader loading
- Fixed missing $raw_stats private property definition in WPCA_Performance class

### Documentation Updates

- Updated all documentation to version 1.4.2 to match plugin version
- Optimized documentation directory structure
- Removed duplicate documentation files
- Updated README.md and documentation index with correct file references
- Updated documentation version management logs
- Improved documentation organization and navigation

## 1.3.0 (Unreleased)

### New Features

- **Performance Optimization Module**: Added comprehensive performance optimization features to improve WordPress admin interface speed and efficiency.
  - **Database Optimization**: Implemented automatic and manual database table optimization to reduce overhead and improve query performance.
  - **Resource Management**: Added CSS/JS resource control, loading optimization, and cleanup functionality for admin interface.
  - **Performance Monitoring**: Built-in performance monitoring to track page load times, query counts, and memory usage.
  - **Performance Settings Page**: Created dedicated settings page for configuring all performance-related features.
  - **Database Cleanup**: Added options to clean up unnecessary data including revisions, auto-drafts, spam comments, etc.

### Improvements

- **Architecture Enhancement**: Implemented singleton pattern for all new components to ensure consistent object instantiation.
- **Security Improvements**: Added nonce verification and capability checks for all AJAX operations and admin actions.
- **Error Handling**: Improved error handling and user feedback throughout the performance optimization workflows.
- **Resource Loading**: Optimized JavaScript and CSS resource loading for the performance module interface.

### Added Files

- `class-wpca-performance.php`: Core performance optimization functionality.
- `class-wpca-resources.php`: Resource management and optimization.
- `class-wpca-database.php`: Database optimization and cleanup tools.
- `class-wpca-performance-settings.php`: Performance settings page and UI components.
- `wpca-performance.js`: JavaScript for performance module interface interactions.
- `wpca-performance.css`: Styling for performance module interface.
- `test-wpca-performance.php`: Testing suite for performance module functionality.

### Changed Files

- `wp-clean-admin.php`: Updated to include and initialize new performance components.

## 1.2.0

### Features

- Login page customization with multiple style options
- Menu management with hiding/restoring/sorting capabilities
- Dashboard widget removal and customization
- Admin interface styling improvements

## 1.1.0

### Features

- User permissions management
- Admin bar cleanup
- Page title optimization
- Translation support

## 1.0.0

### Initial Release

- Core functionality for WordPress admin cleanup and customization
- Basic styling improvements
- Admin menu management