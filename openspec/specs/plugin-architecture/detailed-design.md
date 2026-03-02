<!-- OPENSPEC:START -->
# WP Clean Admin 详细设计文档

## 1. 项目概述

WP Clean Admin 是一个 WordPress 插件，用于管理后台清理和优化，版本 1.8.0。插件采用模块化设计，遵循 WordPress 最佳实践，注重安全性、性能和用户体验。

**项目特点**：
- 模块化设计，便于扩展和维护
- 遵循 WordPress 最佳实践
- 注重安全性、性能和用户体验
- 支持多语言
- 提供丰富的 API 供开发者使用

**项目目标**：
- 清理 WordPress 后台冗余菜单和功能
- 优化后台加载性能
- 增强后台安全性
- 提供灵活的菜单定制功能
- 简化数据库管理

## 2. 目录结构

### 2.1 插件主目录结构

```
wpcleanadmin/
├── assets/                    # 静态资源目录
│   ├── css/                   # CSS 文件
│   │   ├── wpca-admin.css     # 后台管理样式
│   │   └── wpca-settings.css  # 设置页面样式
│   └── js/                    # JavaScript 文件
│       ├── wpca-login.js      # 登录页面脚本
│       ├── wpca-main.js       # 主脚本
│       └── wpca-settings.js   # 设置页面脚本
├── includes/                  # 核心功能目录
│   ├── ajax/                  # AJAX 处理目录
│   │   ├── cleanup-ajax.php   # 清理功能 AJAX 处理
│   │   ├── dashboard-ajax.php # 仪表盘 AJAX 处理
│   │   └── settings-ajax.php  # 设置 AJAX 处理
│   ├── modules/               # 模块化结构目录
│   │   ├── admin/             # 管理功能模块
│   │   │   ├── classes/       # 管理类
│   │   │   └── settings/      # 管理设置
│   │   ├── core/              # 核心模块
│   │   │   └── classes/       # 核心类
│   │   └── utilities/         # 工具模块
│   │       └── classes/       # 工具类
│   ├── settings/              # 设置相关目录
│   │   └── menu-customization.php # 菜单定制设置
│   ├── autoload.php           # 自动加载文件
│   ├── class-*.php            # 各个功能类
│   ├── wpca-core-functions.php # 核心函数
│   └── wpca-wordpress-stubs.php # WordPress 函数存根
├── languages/                 # 语言文件目录
│   ├── wp-clean-admin-en_US.mo # 英文翻译文件
│   ├── wp-clean-admin-en_US.po # 英文翻译源文件
│   ├── wp-clean-admin-zh_CN.mo # 中文翻译文件
│   ├── wp-clean-admin-zh_CN.po # 中文翻译源文件
│   └── wp-clean-admin.pot     # 翻译模板文件
├── CHANGELOG.md               # 版本更新记录
└── wp-clean-admin.php         # 插件主文件
```

### 2.2 核心文件说明

| 文件名 | 功能描述 | 位置 |
|--------|----------|------|
| wp-clean-admin.php | 插件主文件，负责初始化和设置 | 根目录 |
| autoload.php | 自动加载器，处理类的加载 | includes/ |
| class-wpca-core.php | 核心类，负责模块初始化和协调 | includes/ |
| wpca-core-functions.php | 核心函数，提供通用功能 | includes/ |
| wpca-wordpress-stubs.php | WordPress 函数存根，用于 IDE 支持 | includes/ |

## 3. 核心功能模块

### 3.1 插件主文件 (wp-clean-admin.php)

#### 3.1.1 基本信息

```php
<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI: https://github.com/sutchan/WP-Clean-Admin
 * Description: A comprehensive WordPress admin cleanup and optimization plugin
 * Version: 1.8.0
 * Author: Sut
 * Author URI: https://github.com/sutchan
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 * Network: true
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */
```

#### 3.1.2 安全检查

```php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

#### 3.1.3 WordPress 函数存根

```php
// Declare WordPress functions for IDE compatibility
if ( ! function_exists( 'plugin_dir_path' ) ) {
    function plugin_dir_path() {}
}
if ( ! function_exists( 'plugin_dir_url' ) ) {
    function plugin_dir_url() {}
}
if ( ! function_exists( 'load_plugin_textdomain' ) ) {
    function load_plugin_textdomain() {}
}
if ( ! function_exists( 'add_action' ) ) {
    function add_action() {}
}
if ( ! function_exists( 'deactivate_plugins' ) ) {
    function deactivate_plugins() {}
}
if ( ! function_exists( 'wp_die' ) ) {
    function wp_die() {}
}
if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__() {}
}
if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook() {}
}
if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook() {}
}
if ( ! function_exists( 'get_option' ) ) {
    function get_option() {}
}
if ( ! function_exists( 'update_option' ) ) {
    function update_option() {}
}
if ( ! function_exists( 'flush_rewrite_rules' ) ) {
    function flush_rewrite_rules() {}
}
if ( ! function_exists( 'admin_url' ) ) {
    function admin_url() {}
}
if ( ! function_exists( 'esc_url' ) ) {
    function esc_url() {}
}
if ( ! function_exists( 'esc_html' ) ) {
    function esc_html() {}
}
if ( ! function_exists( '__' ) ) {
    function __() {}
}
if ( ! function_exists( 'add_filter' ) ) {
    function add_filter() {}
}
if ( ! function_exists( 'plugin_basename' ) ) {
    function plugin_basename() {}
}
```

#### 3.1.4 插件常量定义

```php
// Define plugin constants
define( 'WPCA_VERSION', '1.8.0' );
define( 'WPCA_PLUGIN_DIR', ( function_exists( 'plugin_dir_path' ) ? plugin_dir_path( __FILE__ ) : dirname( __FILE__ ) . '/' ) );
define( 'WPCA_PLUGIN_URL', ( function_exists( 'plugin_dir_url' ) ? plugin_dir_url( __FILE__ ) : '' ) );
define( 'WPCA_TEXT_DOMAIN', 'wp-clean-admin' );
```

#### 3.1.5 自动加载器加载

```php
// Load autoloader
$autoloader_path = WPCA_PLUGIN_DIR . 'includes/autoload.php';
if ( file_exists( $autoloader_path ) ) {
    require_once $autoloader_path;
} else {
    // Log error if autoloader not found
    if ( function_exists( 'error_log' ) ) {
        error_log( 'WP Clean Admin Error: Autoloader file not found at ' . $autoloader_path );
    }
}
```

#### 3.1.6 后备自动加载器

```php
// Fallback autoloader if main autoloader fails
spl_autoload_register( function( $class ) {
    // Check if the class belongs to our namespace
    if ( strpos( $class, 'WPCleanAdmin\' ) !== 0 ) {
        return;
    }
    
    // Remove namespace prefix
    $class_name = str_replace( 'WPCleanAdmin\', '', $class );
    
    // Convert camelCase to kebab-case and replace underscores with hyphens
    $file_path = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $class_name ) );
    $file_path = str_replace( '_', '-', $file_path );
    
    // Build full file path
    $file = __DIR__ . '/includes/class-wpca-' . $file_path . '.php';
    
    // Check if file exists and include it
    if ( file_exists( $file ) ) {
        require_once $file;
    }
});
```

#### 3.1.7 插件初始化函数

```php
/**
 * Initialize the WP Clean Admin plugin
 *
 * This function loads the plugin text domain and initializes the core class.
 * It's hooked to the 'plugins_loaded' action.
 *
 * @since 1.7.15
 */
function wpca_init() {
    try {
        // Load text domain for translations
        if ( function_exists( 'load_plugin_textdomain' ) && function_exists( 'plugin_basename' ) ) {
            $plugin_basename = plugin_basename( __FILE__ );
            if ( is_string( $plugin_basename ) ) {
                load_plugin_textdomain( WPCA_TEXT_DOMAIN, false, dirname( $plugin_basename ) . '/languages/' );
            }
        }
        
        // Initialize core class
        if ( class_exists( 'WPCleanAdmin\Modules\Core\Classes\Core' ) ) {
            WPCleanAdmin\Modules\Core\Classes\Core::getInstance();
        } else {
            // Fallback to legacy core class
            if ( class_exists( 'WPCleanAdmin\Core' ) ) {
                WPCleanAdmin\Core::getInstance();
            } else {
                // Log error if core class not found
                if ( function_exists( 'error_log' ) ) {
                    error_log( 'WP Clean Admin Error: Core class not found' );
                }
            }
        }
    } catch ( \Exception $e ) {
        // Log any exceptions during initialization
        if ( function_exists( 'error_log' ) ) {
            error_log( 'WP Clean Admin Error during initialization: ' . $e->getMessage() );
            error_log( 'WP Clean Admin Error trace: ' . $e->getTraceAsString() );
        }
    }
}
```

#### 3.1.8 WordPress 初始化钩子

```php
// Hook into WordPress initialization
if ( function_exists( 'add_action' ) ) {
    add_action( 'plugins_loaded', 'wpca_init' );
}
```

#### 3.1.9 紧急停用插件函数

```php
/**
 * 紧急停用插件函数
 * 
 * 当插件出现严重错误时，可手动调用此函数立即停用插件。
 * 注意：此函数默认未挂载到任何钩子，需手动触发。
 *
 * @since 1.8.0
 */
function wpca_emergency_deactivate() {
    // 获取当前插件的 basename
    if ( function_exists( 'plugin_basename' ) ) {
        $plugin_basename = plugin_basename( __FILE__ );

        // 如果 deactivate_plugins 函数可用，则执行停用
        if ( function_exists( 'deactivate_plugins' ) ) {
            deactivate_plugins( array( $plugin_basename ) );

            // 若当前请求为激活操作，则输出停用提示并终止
            if ( isset( $_GET['action'] ) && $_GET['action'] === 'activate' && function_exists( 'wp_die' ) && function_exists( 'esc_html__' ) ) {
                wp_die(
                    esc_html__(
                        'WP Clean Admin 插件因严重错误已被自动停用。请查看错误日志以获取更多信息。',
                        'wp-clean-admin'
                    )
                );
            }
        }
    }
}
```

#### 3.1.10 激活钩子

```php
// Register activation hook
if ( function_exists( 'register_activation_hook' ) ) {
    register_activation_hook( __FILE__, function() {
        try {
            // Set default settings directly
            $default_settings = array(
                'general' => array(
                    'clean_admin_bar' => 1,
                    'clean_dashboard' => 1,
                    'remove_wp_logo' => 1,
                ),
                'performance' => array(
                    'optimize_database' => 1,
                    'clean_transients' => 1,
                    'disable_emojis' => 1,
                ),
                'menu' => array(
                    'remove_dashboard_widgets' => 1,
                    'simplify_admin_menu' => 1,
                ),
            );
            
            // Update settings if they don't exist
            if ( function_exists( 'get_option' ) && function_exists( 'update_option' ) ) {
                $current_settings = get_option( 'wpca_settings', array() );
                $current_settings = is_array( $current_settings ) ? $current_settings : array();
                $updated_settings = array_merge( $default_settings, $current_settings );
                update_option( 'wpca_settings', $updated_settings );
            }
            
            // Flush rewrite rules
            if ( function_exists( 'flush_rewrite_rules' ) ) {
                flush_rewrite_rules();
            }
        } catch ( \Exception $e ) {
            // Log error if activation fails
            if ( function_exists( 'error_log' ) ) {
                error_log( 'WP Clean Admin Activation Error: ' . $e->getMessage() );
                error_log( 'WP Clean Admin Activation Error Trace: ' . $e->getTraceAsString() );
            }
        }
    });
}
```

#### 3.1.11 停用钩子

```php
// Register deactivation hook
if ( function_exists( 'register_deactivation_hook' ) ) {
    register_deactivation_hook( __FILE__, function() {
        try {
            // Flush rewrite rules
            if ( function_exists( 'flush_rewrite_rules' ) ) {
                flush_rewrite_rules();
            }
        } catch ( \Exception $e ) {
            // Log error if deactivation fails
            if ( function_exists( 'error_log' ) ) {
                error_log( 'WP Clean Admin Deactivation Error: ' . $e->getMessage() );
                error_log( 'WP Clean Admin Deactivation Error Trace: ' . $e->getTraceAsString() );
            }
        }
    });
}
```

#### 3.1.12 插件操作链接

```php
/**
 * Add settings link to plugin management page
 *
 * @param array $links Existing plugin action links
 * @return array Modified plugin action links with settings link
 * @since 1.8.0
 */
function wpca_add_plugin_action_links( $links ) {
    if ( function_exists( '\admin_url' ) && function_exists( '\esc_url' ) && function_exists( '\esc_html' ) && function_exists( '\__' ) ) {
        $settings_link = array(
            '<a href="' . \esc_url( \admin_url( 'admin.php?page=wp-clean-admin' ) ) . '">' . \esc_html( \__( 'Settings', WPCA_TEXT_DOMAIN ) ) . '</a>'
        );
        return array_merge( $settings_link, $links );
    }
    return $links;
}

// Hook into plugin action links
if ( function_exists( '\add_filter' ) && function_exists( '\plugin_basename' ) ) {
    $plugin_basename = \plugin_basename( __FILE__ );
    if ( is_string( $plugin_basename ) ) {
        \add_filter( 'plugin_action_links_' . $plugin_basename, 'wpca_add_plugin_action_links' );
    }
}
```

### 3.2 自动加载器 (autoload.php)

#### 3.2.1 基本信息

```php
<?php
/**
 * WPCleanAdmin PSR-4 Autoloader
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
```

#### 3.2.2 IDE 支持存根加载

```php
// Load stubs for IDE support during development
// These stubs are only for IDE support during development
// They can cause conflicts with actual WordPress functions
if ( ! defined( 'ABSPATH' ) ) {
    // Load WordPress stubs for IDE support
    require_once __DIR__ . '/wpca-wordpress-stubs.php';
    
    // Load Composer stub for IDE support
    if ( file_exists( __DIR__ . '/composer-stub.php' ) ) {
        require_once __DIR__ . '/composer-stub.php';
    }
    
    // Load Elementor stub for IDE support
    if ( file_exists( __DIR__ . '/elementor-stub.php' ) ) {
        require_once __DIR__ . '/elementor-stub.php';
    }
    
    // Exit after loading stubs in IDE environment
    exit;
}
```

#### 3.2.3 自动加载注册

```php
/**
 * Register autoloader for WPCleanAdmin classes
 */
spl_autoload_register( function( $class ) {
    // Check if the class belongs to our namespace
    if ( strpos( $class, 'WPCleanAdmin\' ) !== 0 ) {
        return;
    }
    
    // Remove namespace prefix
    $class_name = str_replace( 'WPCleanAdmin\', '', $class );
    
    // Split namespace parts
    $namespace_parts = explode( '\', $class_name );
    $class_basename = array_pop( $namespace_parts );
    
    // Convert camelCase to kebab-case and replace underscores with hyphens for class name
    $class_file = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $class_basename ) );
    $class_file = str_replace( '_', '-', $class_file );
    
    // Build directory path from namespace parts
    $dir_path = '';
    if ( ! empty( $namespace_parts ) ) {
        foreach ( $namespace_parts as $part ) {
            $dir_path .= strtolower( $part ) . '/';
        }
    }
    
    // Build full file path
    $plugin_dir = defined( 'WPCA_PLUGIN_DIR' ) ? WPCA_PLUGIN_DIR : dirname( dirname( __FILE__ ) ) . '/';
    
    // Check for files in different locations
    $file_locations = array();
    
    // Check for modular structure first
    if ( ! empty( $namespace_parts ) && $namespace_parts[0] === 'Modules' ) {
        // Rebuild dir path without 'modules' prefix
        $modular_dir_path = '';
        if ( count( $namespace_parts ) > 1 ) {
            $modular_parts = array_slice( $namespace_parts, 1 ); // Remove 'Modules' part
            foreach ( $modular_parts as $part ) {
                $modular_dir_path .= strtolower( $part ) . '/';
            }
        }
        
        // For modular structure
        $file_locations[] = $plugin_dir . 'includes/modules/' . $modular_dir_path . 'class-wpca-' . $class_file . '.php';
        $file_locations[] = $plugin_dir . 'includes/modules/' . $modular_dir_path . $class_file . '.php';
        
        // For modular classes without prefix
        $file_locations[] = $plugin_dir . 'includes/modules/' . $modular_dir_path . $class_basename . '.php';
    } 
    // For AJAX handlers
    elseif ( ! empty( $namespace_parts ) && $namespace_parts[0] === 'AJAX' ) {
        $file_locations[] = $plugin_dir . 'includes/' . $dir_path . $class_file . '-ajax.php';
        $file_locations[] = $plugin_dir . 'includes/ajax/' . $class_file . '-ajax.php';
    } 
    // For Settings handlers
    elseif ( ! empty( $namespace_parts ) && $namespace_parts[0] === 'Settings' ) {
        $file_locations[] = $plugin_dir . 'includes/' . $dir_path . $class_file . '.php';
        $file_locations[] = $plugin_dir . 'includes/settings/' . $class_file . '.php';
    } 
    // For main classes
    else {
        $file_locations[] = $plugin_dir . 'includes/class-wpca-' . $class_file . '.php';
    }
    
    // Check if any of the file locations exist
    foreach ( $file_locations as $file ) {
        if ( file_exists( $file ) ) {
            require_once $file;
            return;
        }
    }
} );
```

### 3.3 核心类 (class-wpca-core.php)

#### 3.3.1 基本信息

```php
<?php
/**
 * WPCleanAdmin Core Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

#### 3.3.2 核心类定义

```php
/**
 * Core class
 */
class Core {
    
    /**
     * Singleton instance
     *
     * @var Core
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     *
     * @return Core
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Load core functions
        if ( defined( 'WPCA_PLUGIN_DIR' ) ) {
            require_once WPCA_PLUGIN_DIR . 'includes/wpca-core-functions.php';
        } else {
            // Fallback if WPCA_PLUGIN_DIR not defined
            require_once dirname( __DIR__ ) . '/wpca-core-functions.php';
        }
        
        // Add security headers
        $this->add_security_headers();
        
        // Initialize modules
        $this->init_modules();
    }
    
    /**
     * Add security HTTP headers
     */
    private function add_security_headers() {
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'send_headers', array( $this, 'send_security_headers' ) );
        }
    }
    
    /**
     * Send security HTTP headers
     */
    public function send_security_headers() {
        // X-Frame-Options: Prevent clickjacking
        if ( ! \headers_sent() ) {
            \header( 'X-Frame-Options: SAMEORIGIN' );
        }
        
        // X-XSS-Protection: Enable browser XSS filter
        if ( ! \headers_sent() ) {
            \header( 'X-XSS-Protection: 1; mode=block' );
        }
        
        // X-Content-Type-Options: Prevent MIME type sniffing
        if ( ! \headers_sent() ) {
            \header( 'X-Content-Type-Options: nosniff' );
        }
        
        // Referrer-Policy: Control referrer information
        if ( ! \headers_sent() ) {
            \header( 'Referrer-Policy: strict-origin-when-cross-origin' );
        }
        
        // Content-Security-Policy: Restrict resource loading (basic configuration)
        if ( ! \headers_sent() ) {
            \header( "Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';" );
        }
    }
    
    /**
     * Initialize all plugin modules
     */
    private function init_modules() {
        // Load legacy modules for backward compatibility
        $this->load_legacy_modules();
        
        // Load new modular structure
        $this->load_modular_modules();
    }
    
    /**
     * Load legacy modules for backward compatibility
     */
    private function load_legacy_modules() {
        // Module initialization order matters - load core modules first
        $modules = array(
            'Settings',
            'Dashboard',
            'Database',
            'Performance',
            'Menu_Manager',
            'Menu_Customizer',
            'Permissions',
            'User_Roles',
            'Login',
            'Cleanup',
            'Resources',
            'Reset',
            'AJAX',
            'i18n',
            'Error_Handler',
            'Cache',
            'Extension_API'
        );
        
        foreach ( $modules as $module ) {
            $class_name = 'WPCleanAdmin\\' . $module;
            
            try {
                if ( class_exists( $class_name ) ) {
                    // Check if getInstance method exists
                    if ( method_exists( $class_name, 'getInstance' ) ) {
                        $class_name::getInstance();
                    }
                }
            } catch ( \Exception $e ) {
                // Silently catch exceptions during module initialization
                // This prevents the entire plugin from failing if one module has an issue
                // Log error for debugging if needed
                if ( function_exists( 'error_log' ) ) {
                    \error_log( 'WPCA Legacy Module Init Error: ' . $e->getMessage() );
                }
            }
        }
    }
    
    /**
     * Load modules from new modular structure
     */
    private function load_modular_modules() {
        // Load admin settings module
        try {
            $class_name = 'WPCleanAdmin\\Modules\\Admin\\Settings\\Settings';
            if ( class_exists( $class_name ) ) {
                if ( method_exists( $class_name, 'getInstance' ) ) {
                    $class_name::getInstance();
                }
            }
        } catch ( \Exception $e ) {
            if ( function_exists( 'error_log' ) ) {
                \error_log( 'WPCA Modular Module Init Error: ' . $e->getMessage() );
            }
        }
    }
    
    /**
     * Plugin activation callback
     */
    public function activate() {
        // Set default settings
        $this->set_default_settings();
        
        // Flush rewrite rules
        if ( function_exists( 'flush_rewrite_rules' ) ) {
            \flush_rewrite_rules();
        }
    }
    
    /**
     * Plugin deactivation callback
     */
    public function deactivate() {
        // Flush rewrite rules
        if ( function_exists( 'flush_rewrite_rules' ) ) {
            \flush_rewrite_rules();
        }
    }
    
    /**
     * Set default plugin settings
     */
    private function set_default_settings() {
        $default_settings = array(
            'general' => array(
                'clean_admin_bar' => 1,
                'clean_dashboard' => 1,
                'remove_wp_logo' => 1,
            ),
            'performance' => array(
                'optimize_database' => 1,
                'clean_transients' => 1,
                'disable_emojis' => 1,
            ),
            'menu' => array(
                'remove_dashboard_widgets' => 1,
                'simplify_admin_menu' => 1,
            ),
        );
        
        // Update settings if they don't exist
        $current_settings = ( function_exists( 'get_option' ) ? \get_option( 'wpca_settings', array() ) : array() );
        $updated_settings = ( function_exists( 'wp_parse_args' ) ? \wp_parse_args( $current_settings, $default_settings ) : array_merge( $default_settings, $current_settings ) );
        
        if ( function_exists( 'update_option' ) ) {
            \update_option( 'wpca_settings', $updated_settings );
        }
    }
}
```

## 4. 核心功能模块详细设计

### 4.1 清理模块 (Cleanup)

**功能**：提供数据库、媒体、评论和内容清理功能

**主要职责**：
- 清理过期的transients
- 清理孤儿元数据
- 清理过期的cron事件
- 清理孤儿媒体和未使用的媒体
- 清理垃圾评论、回收站评论和未批准评论
- 清理未使用的短代码和空帖子

**技术实现**：
- 使用 WordPress 钩子 `wpca_cleanup_database` 运行数据库清理
- 使用 WordPress 钩子 `wpca_cleanup_media` 运行媒体清理
- 使用 WordPress 钩子 `wpca_cleanup_comments` 运行评论清理
- 使用 WordPress 钩子 `wpca_cleanup_content` 运行内容清理

**相关文件**：
- `includes/class-wpca-cleanup.php` - 后台清理核心类
- `includes/ajax/cleanup-ajax.php` - 清理功能 AJAX 处理

### 4.2 性能优化模块 (Performance)

**功能**：优化 WordPress 网站性能

**主要职责**：
- 禁用不必要的功能（Emojis、XML-RPC、REST API、Heartbeat）
- 优化数据库
- 清理Transients
- 提供缓存清理功能
- 提供性能统计信息

**技术实现**：
- 使用 WordPress 钩子系统禁用不需要的功能
- 提供数据库优化功能
- 实现缓存清理功能

**相关文件**：
- `includes/class-wpca-performance.php` - 性能优化核心类

### 4.3 菜单定制模块 (Menu_Customizer)

**功能**：自定义 WordPress 后台菜单和管理栏

**主要职责**：
- 自定义菜单顺序
- 自定义菜单项目（隐藏、修改标题、图标和位置）
- 自定义子菜单项目（隐藏、修改标题）
- 自定义管理栏项目（隐藏、修改标题）
- 提供设置导出/导入功能

**技术实现**：
- 使用 WordPress 钩子 `admin_menu` 定制菜单
- 使用 WordPress 钩子 `admin_bar_menu` 定制管理栏

**相关文件**：
- `includes/class-wpca-menu-customizer.php` - 菜单定制核心类
- `includes/settings/menu-customization.php` - 菜单定制设置

### 4.4 数据库管理模块 (Database)

**功能**：管理和优化 WordPress 数据库

**主要职责**：
- 清理数据库
- 优化数据库
- 备份数据库
- 恢复数据库

**技术实现**：
- 使用 WordPress `$wpdb` 类处理数据库操作
- 实现数据库备份和恢复功能

**相关文件**：
- `includes/class-wpca-database.php` - 数据库管理核心类

### 4.5 权限管理模块 (Permissions)

**功能**：管理用户权限和访问控制

**主要职责**：
- 过滤用户权限
- 检查用户功能访问权限
- 限制管理后台访问
- 限制特定管理页面访问

**技术实现**：
- 使用 WordPress 钩子 `user_has_cap` 过滤用户权限
- 使用 WordPress 函数 `current_user_can` 检查权限

**相关文件**：
- `includes/class-wpca-permissions.php` - 权限管理核心类

### 4.6 用户角色模块 (User_Roles)

**功能**：管理用户角色和权限

**主要职责**：
- 允许创建、编辑和删除角色
- 提供角色权限编辑器
- 管理角色的创建、编辑和删除

**技术实现**：
- 使用 WordPress 角色和权限系统
- 实现角色管理功能

**相关文件**：
- `includes/class-wpca-user-roles.php` - 用户角色管理核心类

### 4.7 登录页面模块 (Login)

**功能**：自定义 WordPress 登录页面

**主要职责**：
- 自定义登录页面样式
- 增强登录页面安全性

**技术实现**：
- 使用 WordPress 钩子 `login_enqueue_scripts` 加载自定义样式
- 实现登录页面安全增强功能

**相关文件**：
- `includes/class-wpca-login.php` - 登录页面优化核心类
- `assets/js/wpca-login.js` - 登录页面脚本

### 4.8 仪表盘模块 (Dashboard)

**功能**：优化 WordPress 仪表盘

**主要职责**：
- 提供插件的主要管理界面
- 显示系统信息和统计数据
- 隐藏不需要的仪表盘小工具

**技术实现**：
- 使用 WordPress 钩子 `wp_dashboard_setup` 定制仪表盘
- 实现系统信息和统计数据显示

**相关文件**：
- `includes/class-wpca-dashboard.php` - 仪表盘优化核心类
- `includes/ajax/dashboard-ajax.php` - 仪表盘 AJAX 处理

### 4.9 设置模块 (Settings)

**功能**：管理插件的所有设置

**主要职责**：
- 提供设置页面和表单处理
- 设置验证和保存
- 管理设置的导入和导出

**技术实现**：
- 使用 WordPress 设置 API
- 实现设置页面和表单处理

**相关文件**：
- `includes/class-wpca-settings.php` - 设置管理核心类
- `includes/modules/admin/settings/class-wpca-settings.php` - 模块化设置类
- `includes/ajax/settings-ajax.php` - 设置 AJAX 处理

### 4.10 国际化模块 (i18n)

**功能**：处理插件的国际化和本地化

**主要职责**：
- 加载翻译文件
- 提供国际化支持
- 处理多语言内容

**技术实现**：
- 使用 WordPress 翻译函数
- 加载翻译文件

**相关文件**：
- `includes/class-wpca-i18n.php` - 国际化核心类
- `languages/` - 语言文件目录

### 4.11 扩展 API 模块 (Extension_API)

**功能**：提供插件扩展 API

**主要职责**：
- 允许第三方扩展插件功能
- 提供扩展注册和管理

**技术实现**：
- 实现扩展注册和管理功能
- 提供扩展 API

**相关文件**：
- `includes/class-wpca-extension-api.php` - 扩展 API 核心类

## 5. 钩子系统

### 5.1 动作钩子

| 钩子名称 | 描述 | 参数 |
|---------|------|------|
| wpca_init | 插件初始化完成后触发 | 无 |
| wpca_after_save_settings | 设置保存后触发 | $settings |
| wpca_cleanup_database | 运行数据库清理时触发 | $options |
| wpca_cleanup_media | 运行媒体清理时触发 | $options |
| wpca_cleanup_comments | 运行评论清理时触发 | $options |
| wpca_cleanup_content | 运行内容清理时触发 | $options |
| wpca_clear_cache | 清除缓存时触发 | 无 |

### 5.2 过滤器钩子

| 钩子名称 | 描述 | 参数 | 返回值 |
|---------|------|------|--------|
| wpca_hidden_menus | 过滤要隐藏的菜单 | $menus | 过滤后的菜单数组 |
| wpca_disabled_features | 过滤要禁用的功能 | $features | 过滤后的功能数组 |
| wpca_optimization_options | 过滤优化选项 | $options | 过滤后的优化选项 |
| wpca_database_cleanup_options | 过滤数据库清理选项 | $options | 过滤后的清理选项 |
| wpca_settings | 过滤插件设置 | $settings | 过滤后的设置 |
| user_has_cap | 过滤用户权限 | $allcaps, $caps, $args | 过滤后的权限数组 |
| rest_authentication_errors | 过滤 REST API 认证结果 | $result | 过滤后的认证结果 |
| tiny_mce_plugins | 过滤 TinyMCE 插件 | $plugins | 过滤后的插件数组 |

## 6. API 文档

### 6.1 核心 API 函数

```php
// 获取插件设置
wpca_get_settings( $key, $default );

// 更新插件设置
wpca_update_settings( $settings );

// 检查用户权限
wpca_current_user_can();

// 获取清理统计信息
wpca_get_cleanup_stats();

// 清除缓存
wpca_clear_cache();

// 获取性能统计信息
wpca_get_performance_stats();

// 过滤用户权限
wpca_filter_user_capabilities( $allcaps, $caps, $args );

// 检查用户功能权限
wpca_has_feature_permission( $feature, $user_id );

// 限制管理后台访问
wpca_restrict_admin_access();

// 限制特定管理页面访问
wpca_restrict_specific_admin_pages();
```

### 6.2 扩展机制

插件支持开发者通过以下方式扩展功能：
1. 使用 WordPress 钩子系统（动作钩子和过滤器钩子）
2. 使用插件提供的 API 函数
3. 创建自定义模块（继承现有模块或创建新模块）

## 7. 安全设计

### 7.1 输入验证

- **所有用户输入必须经过验证**：使用 WordPress 验证函数
- **验证类型**：字符串、数字、邮箱、URL 等
- **验证函数**：`sanitize_text_field()`, `sanitize_email()`, `sanitize_url()`, `absint()` 等

### 7.2 输出转义

- **所有输出到页面的内容必须转义**：防止 XSS 攻击
- **转义函数**：`esc_html()`, `esc_attr()`, `esc_url()`, `esc_js()` 等

### 7.3 SQL 注入防护

- **使用 `$wpdb->prepare()` 处理所有 SQL 查询**：防止 SQL 注入
- **参数绑定**：将用户输入作为参数绑定，而不是直接插入 SQL

### 7.4 CSRF 防护

- **使用 nonce 验证所有表单提交**：防止 CSRF 攻击
- **函数**：`wp_nonce_field()`, `wp_verify_nonce()`

### 7.5 权限检查

- **所有管理操作必须检查权限**：防止未授权访问
- **函数**：`wpca_current_user_can()`, `current_user_can()`

### 7.6 安全头部

- **添加安全 HTTP 头部**：增强网站安全性
- **头部**：X-Frame-Options, X-XSS-Protection, X-Content-Type-Options, Strict-Transport-Security, Content-Security-Policy

## 8. 兼容性

### 8.1 WordPress 版本兼容性

- **最低版本**：WordPress 5.0+
- **兼容性检查**：使用 `function_exists()` 检查函数是否存在
- **功能降级**：根据 WordPress 版本提供不同功能

### 8.2 PHP 版本兼容性

- **最低版本**：PHP 7.0+
- **兼容性检查**：使用 `function_exists()` 检查函数是否存在
- **语法兼容**：使用兼容 PHP 7.0+ 的语法

### 8.3 浏览器兼容性

- **支持浏览器**：Chrome, Firefox, Safari, Edge
- **兼容性处理**：使用兼容的 HTML, CSS, JavaScript
- **响应式设计**：支持不同屏幕尺寸

### 8.4 主题兼容性

- **兼容性处理**：与主流 WordPress 主题兼容
- **样式隔离**：使用独特的 CSS 类名，避免样式冲突
- **钩子集成**：提供钩子供主题扩展

## 9. 性能优化

### 9.1 资源加载优化

- **按需加载**：只在需要时加载资源
- **合并和压缩**：合并和压缩 CSS 和 JavaScript 文件
- **缓存策略**：合理使用浏览器缓存

### 9.2 数据库优化

- **索引优化**：为常用查询添加索引
- **查询优化**：优化数据库查询，减少查询次数
- **清理优化**：定期清理数据库冗余数据

### 9.3 代码优化

- **减少代码复杂度**：简化代码逻辑
- **避免重复代码**：提取公共功能
- **延迟加载**：非关键功能延迟加载

### 9.4 缓存策略

- **对象缓存**：使用 WordPress 对象缓存
- **页面缓存**：支持页面缓存插件
- **Transients**：合理使用 transients 缓存

## 10. 国际化支持

### 10.1 文本域

- **文本域常量**：`WPCA_TEXT_DOMAIN`
- **文本域值**：`wp-clean-admin`

### 10.2 翻译函数

- **使用 WordPress 翻译函数**：`__()`, `_e()`, `_x()`, `_ex()`
- **所有可翻译字符串必须使用翻译函数**

### 10.3 翻译文件

- **POT 文件**：`wp-clean-admin.pot`
- **翻译文件**：支持英文、中文等多语言
- **定期更新**：定期更新翻译文件

## 11. 开发指南

### 11.1 编码规范

- **PHP 编码规范**：遵循 WordPress 编码规范
- **JavaScript 编码规范**：使用 ES6+ 语法，遵循 WordPress 编码规范
- **CSS 编码规范**：使用 BEM 命名规范
- **HTML 编码规范**：使用 HTML5 语义化标签

### 11.2 命名规范

- **类名**：使用 PascalCase，例如 `WPCleanAdmin\Core`
- **方法名**：使用 camelCase，例如 `getInstance()`
- **函数名**：使用 snake_case，例如 `wpca_get_settings()`
- **变量名**：使用 snake_case，例如 `$plugin_settings`
- **常量名**：使用全大写字母和下划线，例如 `WPCA_VERSION`
- **文件名**：使用小写字母和连字符，例如 `class-wpca-core.php`

### 11.3 开发流程

- **分支管理**：main（主分支）、develop（开发分支）、feature/xxx（功能分支）、bugfix/xxx（修复分支）
- **提交信息**：使用 Conventional Commits 规范
- **代码审查**：所有代码必须经过审查
- **测试**：每个功能必须编写测试用例

### 11.4 测试策略

- **单元测试**：测试单个函数或方法
- **集成测试**：测试模块之间的交互
- **功能测试**：测试完整功能
- **兼容性测试**：测试不同环境的兼容性

### 11.5 部署流程

- **版本管理**：使用 Semantic Versioning 规范
- **发布流程**：更新版本号、更新 CHANGELOG.md、运行测试、创建 Git 标签、推送代码、打包发布

## 12. 常见问题和解决方案

### 12.1 插件激活失败

**问题**：插件激活时出现错误
**解决方案**：
- 检查 PHP 版本是否满足要求（PHP 7.0+）
- 检查 WordPress 版本是否满足要求（WordPress 5.0+）
- 检查文件权限是否正确
- 查看错误日志获取详细信息

### 12.2 功能不生效

**问题**：插件功能不生效
**解决方案**：
- 检查插件设置是否正确配置
- 检查是否与其他插件冲突
- 检查主题是否覆盖了插件功能
- 查看错误日志获取详细信息

### 12.3 性能问题

**问题**：插件导致网站性能下降
**解决方案**：
- 优化插件设置，禁用不需要的功能
- 定期清理数据库
- 使用缓存插件
- 检查是否与其他插件冲突

### 12.4 安全问题

**问题**：插件存在安全漏洞
**解决方案**：
- 及时更新插件到最新版本
- 遵循 WordPress 安全最佳实践
- 定期进行安全审计
- 使用安全插件增强网站安全

## 13. 总结

WP Clean Admin 插件采用模块化设计，遵循 WordPress 最佳实践，提供了全面的后台清理和优化功能。通过深度集成 WordPress 钩子系统，插件实现了高度的可扩展性和灵活性。

插件的核心功能包括：
- 后台菜单和仪表盘清理
- 数据库管理和优化
- 性能优化
- 菜单定制
- 权限管理
- 登录页面优化
- 国际化支持

插件的设计注重安全性、性能和用户体验，通过严格的输入验证、输出转义、SQL 注入防护、CSRF 防护和权限检查，确保了插件的安全性。

通过本详细设计文档，开发者可以全面了解 WP Clean Admin 插件的架构、功能和实现细节，为插件的开发、维护和扩展提供了完整的指导。

<!-- OPENSPEC:END -->