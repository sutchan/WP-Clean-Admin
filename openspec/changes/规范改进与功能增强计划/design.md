<!-- OPENSPEC:START -->
# 技术决策：规范改进与功能增强计划

## 决策记录

| 编号 | 决策 | 状态 | 决策日期 |
|------|------|------|----------|
| D001 | 扩展 API 架构设计 | 已决定 | 2025-12-26 |
| D002 | 错误处理模式 | 已决定 | 2025-12-26 |
| D003 | 性能监控方案 | 已决定 | 2025-12-26 |
| D004 | 安全审计机制 | 已决定 | 2025-12-26 |
| D005 | 第三方集成策略 | 已决定 | 2025-12-26 |

## 扩展 API 架构设计

### 备选方案

#### 方案 A：事件驱动架构

使用 WordPress 现有的动作和过滤器系统作为事件总线，所有扩展通过订阅事件来实现功能。

**优点：**
- 与 WordPress 生态深度集成
- 学习曲线低
- 现有钩子系统可直接使用

**缺点：**
- 事件命名混乱风险
- 性能开销较大
- 调试困难

#### 方案 B：中间件模式

实现类似 Laravel 的中间件管道，请求通过一系列中间件处理。

**优点：**
- 清晰的请求处理流程
- 易于测试和维护
- 性能开销可控

**缺点：**
- 需要全新的 API 设计
- 与 WordPress 集成需要适配层

#### 方案 C：混合架构

结合事件驱动和中间件模式，核心功能使用中间件，扩展功能使用事件系统。

**优点：**
- 兼顾性能和灵活性
- 向后兼容现有代码
- 渐进式升级路径

**缺点：**
- 复杂度较高
- 需要良好的架构设计

### 最终决策

**选择方案 C：混合架构**

决策原因：

1. **渐进式升级**：可以在不影响现有功能的情况下逐步引入新架构
2. **向后兼容**：现有的钩子系统继续工作，保护用户投资
3. **性能优化**：核心路径使用中间件减少事件分发开销
4. **扩展灵活**：扩展功能使用事件系统便于第三方集成

### 技术细节

#### 扩展注册流程

```php
// 注册扩展
Extension_API::register_extension(array(
    'id' => 'my-extension',
    'name' => '我的扩展',
    'version' => '1.0.0',
    'hooks' => array(
        'wpca_before_cleanup' => 'my_cleanup_handler',
        'wpca_after_optimize' => 'my_optimize_handler',
    ),
    'settings_page' => 'my-extension-settings',
    'permissions' => array('manage_options'),
));
```

#### 中间件管道

```php
// 创建中间件管道
$pipeline = new Pipeline();

// 添加中间件
$pipeline->pipe(new AuthenticationMiddleware());
$pipeline->pipe(new ValidationMiddleware());
$pipeline->pipe(new LoggingMiddleware());
$pipeline->pipe(new CoreMiddleware());

// 执行请求
$result = $pipeline->process($request);
```

#### 事件系统

```php
// 触发事件
do_action('wpca_extension_loaded', $extension);

// 带参数触发
do_action('wpca_before_action', $action_name, $params);

// 过滤器使用
apply_filters('wpca_settings_values', $settings, $user_id);
```

## 错误处理模式

### 备选方案

#### 方案 A：异常驱动

所有错误抛出异常，使用 try-catch 捕获处理。

#### 方案 B：结果对象模式

函数返回包含结果和错误信息的对象。

#### 方案 C：混合模式

核心功能使用异常，API 使用结果对象。

### 最终决策

**选择方案 C：混合模式**

决策原因：

1. **核心代码**：使用异常可以清晰表达错误状态
2. **公共 API**：使用结果对象便于开发者处理错误
3. **WordPress 兼容**：WordPress 主要使用返回 false 或 null 的模式

### 技术细节

#### 异常类层次

```php
namespace WPCleanAdmin\Exceptions;

class WPCA_Exception extends \Exception implements \Stringable {
    protected $error_code;
    protected $error_data;
    
    public function get_error_code() {
        return $this->error_code;
    }
    
    public function get_error_data() {
        return $this->error_data;
    }
}

class WPCA_Validation_Exception extends WPCA_Exception {}
class WPCA_Authentication_Exception extends WPCA_Exception {}
class WPCA_Authorization_Exception extends WPCA_Exception {}
class WPCA_NotFound_Exception extends WPCA_Exception {}
```

#### 标准错误处理

```php
try {
    $result = $this->perform_operation($data);
    
    if ($result === false) {
        throw new WPCA_Operation_Exception(
            '操作失败',
            'operation_failed',
            $data
        );
    }
    
    return $result;
} catch (WPCA_Exception $e) {
    // 记录错误日志
    wpca_log_error($e, __METHOD__);
    
    // 显示管理通知
    if (current_user_can('manage_options')) {
        wpca_admin_notice($e->getMessage(), 'error');
    }
    
    return false;
}
```

#### API 结果对象

```php
class WPCA_Result {
    private $success;
    private $data;
    private $error;
    private $error_code;
    
    public static function success($data = null) {
        $instance = new self();
        $instance->success = true;
        $instance->data = $data;
        return $instance;
    }
    
    public static function error($message, $code = null, $data = null) {
        $instance = new self();
        $instance->success = false;
        $instance->error = $message;
        $instance->error_code = $code;
        $instance->data = $data;
        return $instance;
    }
    
    public function is_success() {
        return $this->success;
    }
    
    public function get_data() {
        return $this->data;
    }
    
    public function get_error() {
        return $this->error;
    }
}

// 使用示例
$result = wpca_get_settings();
if ($result->is_success()) {
    $settings = $result->get_data();
} else {
    $error = $result->get_error();
}
```

## 性能监控方案

### 备选方案

#### 方案 A：WordPress Debug 模式

使用 WordPress 自带的 WP_DEBUG 和 WP_DEBUG_LOG。

#### 方案 B：独立监控服务

集成 New Relic、Query Monitor 等外部服务。

#### 方案 C：内置监控框架

实现轻量级内置性能监控。

### 最终决策

**选择方案 C：内置监控框架**

决策原因：

1. **零依赖**：不增加额外依赖
2. **可控性**：完全掌控监控逻辑
3. **可扩展**：可集成外部服务
4. **WordPress 友好**：与 WordPress 生态兼容

### 技术细节

#### 性能指标收集

```php
class WPCA_Performance_Monitor {
    private static $instance;
    private $metrics = array();
    private $start_time;
    private $start_memory;
    
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function start_transaction($name) {
        $this->start_time = microtime(true);
        $this->start_memory = memory_get_usage(true);
        
        $this->metrics[$name] = array(
            'start_time' => $this->start_time,
            'start_memory' => $this->start_memory,
            'queries' => get_num_queries(),
        );
    }
    
    public function end_transaction($name) {
        if (!isset($this->metrics[$name])) {
            return;
        }
        
        $this->metrics[$name]['end_time'] = microtime(true);
        $this->metrics[$name]['end_memory'] = memory_get_usage(true);
        $this->metrics[$name]['duration'] = 
            $this->metrics[$name]['end_time'] - $this->metrics[$name]['start_time'];
        $this->metrics[$name]['memory_used'] = 
            $this->metrics[$name]['end_memory'] - $this->metrics[$name]['start_memory'];
        $this->metrics[$name]['queries'] = 
            get_num_queries() - $this->metrics[$name]['queries'];
    }
    
    public function get_report() {
        return $this->metrics;
    }
}
```

#### 数据库查询监控

```php
// 保存原始查询函数
global $wpdb;
$original_query = array($wpdb, 'query');

// 监控查询
$wpdb->query = function($query) use ($original_query) {
    $start = microtime(true);
    $result = call_user_func($original_query, $query);
    $duration = microtime(true) - $start;
    
    if ($duration > 0.5) {
        wpca_log_slow_query($query, $duration);
    }
    
    return $result;
};
```

#### 性能报告生成

```php
function wpca_generate_performance_report() {
    $monitor = WPCA_Performance_Monitor::getInstance();
    $report = $monitor->get_report();
    
    $summary = array(
        'total_duration' => 0,
        'total_memory' => 0,
        'slow_operations' => array(),
        'db_queries' => 0,
    );
    
    foreach ($report as $name => $metric) {
        $summary['total_duration'] += $metric['duration'];
        $summary['total_memory'] += $metric['memory_used'];
        $summary['db_queries'] += $metric['queries'];
        
        if ($metric['duration'] > 1.0) {
            $summary['slow_operations'][] = array(
                'name' => $name,
                'duration' => $metric['duration'],
            );
        }
    }
    
    return $summary;
}
```

## 安全审计机制

### 备选方案

#### 方案 A：运行时检查

在代码执行过程中进行安全检查。

#### 方案 B：代码静态分析

在提交前使用静态分析工具检查。

#### 方案 C：运行时与静态结合

运行时防护 + 提交时扫描。

### 最终决策

**选择方案 C：运行时与静态结合**

决策原因：

1. **多层次防护**：运行时和静态检查互补
2. **及时发现**：静态分析可在开发阶段发现问题
3. **运行时保护**：即使静态检查遗漏，运行时仍可防护

### 技术细节

#### 安全审计清单

```php
class WPCA_Security_Auditor {
    private $checkpoints = array();
    
    public function register_checkpoint($id, $callback, $severity = 'high') {
        $this->checkpoints[$id] = array(
            'callback' => $callback,
            'severity' => $severity,
            'enabled' => true,
        );
    }
    
    public function run_audit($context) {
        $results = array(
            'passed' => array(),
            'warnings' => array(),
            'failed' => array(),
        );
        
        foreach ($this->checkpoints as $id => $checkpoint) {
            if (!$checkpoint['enabled']) {
                continue;
            }
            
            $result = call_user_func($checkpoint['callback'], $context);
            
            if ($result === true) {
                $results['passed'][] = $id;
            } elseif (is_string($result)) {
                $results['warnings'][] = array(
                    'checkpoint' => $id,
                    'message' => $result,
                );
            } else {
                $results['failed'][] = $id;
            }
        }
        
        return $results;
    }
    
    public function get_security_score($results) {
        $total = count($this->checkpoints);
        $passed = count($results['passed']);
        $warnings = count($results['warnings']);
        $failed = count($results['failed']);
        
        $score = ($passed * 100 + $warnings * 50) / ($total * 100);
        return min(100, round($score * 100));
    }
}
```

#### 安全检查点

```php
// 输入验证检查
$auditor->register_checkpoint('input_validation', function($data) {
    if (!isset($data['nonce']) || !wp_verify_nonce($data['nonce'], 'wpca_security')) {
        return 'Nonce 验证失败';
    }
    
    if (!current_user_can('manage_options')) {
        return '权限检查失败';
    }
    
    return true;
}, 'critical');

// SQL 注入检查
$auditor->register_checkpoint('sql_injection', function($query) {
    if (preg_match('/\bUNION\b/i', $query) || 
        preg_match('/\bSELECT\b.*\bFROM\b/i', $query)) {
        return '检测到可能的 SQL 注入模式';
    }
    return true;
}, 'high');

// 输出转义检查
$auditor->register_checkpoint('output_escaping', function($output) {
    if (strpos($output, '<script>') !== false && 
        strpos($output, 'esc_html') === false &&
        strpos($output, 'esc_attr') === false) {
        return '可能存在未转义的输出';
    }
    return true;
}, 'medium');
```

#### 安全报告生成

```php
function wpca_generate_security_report() {
    $auditor = new WPCA_Security_Auditor();
    
    // 注册安全检查点
    wpca_register_security_checkpoints($auditor);
    
    // 运行审计
    $context = array(
        'user' => wp_get_current_user(),
        'time' => current_time('mysql'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    );
    
    $results = $auditor->run_audit($context);
    $score = $auditor->get_security_score($results);
    
    return array(
        'timestamp' => current_time('mysql'),
        'score' => $score,
        'total_checkpoints' => count($auditor->get_checkpoints()),
        'passed' => count($results['passed']),
        'warnings' => count($results['warnings']),
        'failed' => count($results['failed']),
        'details' => $results,
    );
}
```

## 第三方集成策略

### 备选方案

#### 方案 A：封闭生态

仅支持内置功能，不开放第三方集成。

#### 方案 B：完全开放

提供完整 API，支持任意第三方扩展。

#### 方案 C：受限开放

提供有限 API，支持经过审核的扩展。

### 最终决策

**选择方案 B：完全开放，但提供安全机制**

决策原因：

1. **生态系统建设**：开放 API 促进生态发展
2. **用户需求**：用户需要更多扩展功能
3. **安全可控**：通过沙箱机制和安全审计确保安全
4. **市场趋势**：主流插件都提供扩展 API

### 技术细节

#### 扩展市场集成

```php
class WPCA_Extension_Market {
    private $api_url = 'https://api.wpcleanadmin.com/extensions';
    private $cache_key = 'wpca_extension_market';
    private $cache_time = 3600; // 1 小时
    
    public function get_extensions($filter = array()) {
        $cache_key = $this->cache_key . '_' . md5(json_encode($filter));
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $response = wp_remote_get($this->api_url . '?' . http_build_query($filter));
        
        if (is_wp_error($response)) {
            return array();
        }
        
        $extensions = json_decode(wp_remote_retrieve_body($response), true);
        
        set_transient($cache_key, $extensions, $this->cache_time);
        
        return $extensions;
    }
    
    public function install_extension($extension_id) {
        $extension = $this->get_extension_info($extension_id);
        
        if (!$extension) {
            return new WPCA_Result_Error('扩展不存在');
        }
        
        // 下载扩展包
        $package = download_url($extension['download_url']);
        
        if (is_wp_error($package)) {
            return new WPCA_Result_Error('下载失败: ' . $package->get_error_message());
        }
        
        // 解压安装
        $result = $this->extract_package($package, $extension_id);
        
        // 清理临时文件
        unlink($package);
        
        return $result;
    }
    
    public function check_updates($installed_extensions) {
        $market_extensions = $this->get_extensions();
        
        $updates = array();
        foreach ($installed_extensions as $ext) {
            foreach ($market_extensions as $market_ext) {
                if ($ext['id'] === $market_ext['id'] && 
                    version_compare($ext['version'], $market_ext['version'], '<')) {
                    $updates[] = $market_ext;
                }
            }
        }
        
        return $updates;
    }
}
```

#### 沙箱机制

```php
class WPCA_Extension_Sandbox {
    private $allowed_hooks = array(
        'wpca_*',
        'init',
        'admin_menu',
        'wp_ajax_*',
    );
    
    private $blocked_functions = array(
        'eval',
        'exec',
        'shell_exec',
        'system',
        'passthru',
        'popen',
        'proc_open',
    );
    
    public function validate_extension($extension_code) {
        // 检查是否包含被禁止的函数调用
        foreach ($this->blocked_functions as $func) {
            if (preg_match('/\b' . preg_quote($func, '/') . '\s*\(/i', $extension_code)) {
                return new WPCA_Result_Error("检测到被禁止的函数: {$func}");
            }
        }
        
        // 检查钩子白名单
        if (preg_match('/add_action\s*\(\s*[\'"](\w+)[\'"]/', $extension_code, $matches)) {
            $hook = $matches[1];
            $allowed = false;
            
            foreach ($this->allowed_hooks as $pattern) {
                if (fnmatch($pattern, $hook)) {
                    $allowed = true;
                    break;
                }
            }
            
            if (!$allowed) {
                return new WPCA_Result_Error("不被允许的钩子: {$hook}");
            }
        }
        
        return new WPCA_Result_Success();
    }
    
    public function execute_in_sandbox($extension_code, $context) {
        // 在隔离环境中执行扩展代码
        // 限制内存和时间
        $memory_limit = 64 * 1024 * 1024; // 64MB
        $time_limit = 5; // 5秒
        
        ini_set('memory_limit', $memory_limit);
        set_time_limit($time_limit);
        
        // 执行代码
        try {
            eval($extension_code);
            return new WPCA_Result_Success();
        } catch (Throwable $e) {
            return new WPCA_Result_Error("执行错误: " . $e->getMessage());
        }
    }
}
```

#### 开发者 API 文档生成

```php
class WPCA_API_Docs_Generator {
    public function generate_docs($output_dir) {
        $classes = $this->discover_extension_classes();
        $docs = array();
        
        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            
            $class_doc = array(
                'name' => $class->getName(),
                'description' => $this->extract_docblock($reflection->getDocComment()),
                'methods' => array(),
                'hooks' => array(),
            );
            
            // 提取方法文档
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (strpos($method->getDeclaringClass()->getName(), 'WPCleanAdmin\\') !== 0) {
                    continue;
                }
                
                $class_doc['methods'][] = array(
                    'name' => $method->getName(),
                    'description' => $this->extract_docblock($method->getDocComment()),
                    'parameters' => $this->extract_parameters($method),
                    'return' => $this->extract_return($method),
                );
            }
            
            $docs[] = $class_doc;
        }
        
        // 生成 Markdown 文档
        $this->render_markdown($docs, $output_dir);
        
        return $docs;
    }
    
    private function render_markdown($docs, $output_dir) {
        foreach ($docs as $class) {
            $filename = $output_dir . '/' . strtolower(str_replace('\\', '-', $class['name'])) . '.md';
            
            $content = "# {$class['name']}\n\n";
            $content .= $class['description'] . "\n\n";
            
            if (!empty($class['methods'])) {
                $content .= "## 方法\n\n";
                
                foreach ($class['methods'] as $method) {
                    $content .= "### `{$method['name']}()`\n\n";
                    $content .= $method['description'] . "\n\n";
                    
                    if (!empty($method['parameters'])) {
                        $content .= "**参数：**\n\n";
                        $content .= "| 名称 | 类型 | 描述 |\n";
                        $content .= "|------|------|------|\n";
                        
                        foreach ($method['parameters'] as $param) {
                            $content .= "| \${$param['name']} | {$param['type']} | {$param['description']} |\n";
                        }
                        
                        $content .= "\n";
                    }
                    
                    $content .= "**返回：** `{$method['return']['type']}` - {$method['return']['description']}\n\n";
                }
            }
            
            file_put_contents($filename, $content);
        }
    }
}
```

## 实施建议

### 优先级排序

| 优先级 | 功能 | 原因 |
|--------|------|------|
| P0 | 错误处理规范 | 基础功能，修复现有问题 |
| P1 | 扩展 API 架构 | 核心架构，影响后续开发 |
| P2 | 安全审计机制 | 安全要求，法规合规 |
| P3 | 性能监控方案 | 性能优化，提升用户体验 |
| P4 | 第三方集成 | 生态建设，长期价值 |

### 回滚策略

1. **数据库迁移**：保留迁移脚本，支持回滚
2. **配置备份**：自动备份配置文件
3. **版本标记**：每个阶段创建 Git 标签
4. **功能开关**：所有新功能可通过配置启用/禁用

### 监控指标

- 错误率：< 0.1%
- API 响应时间：< 500ms
- 内存使用：< 50MB
- 安全审计分数：> 90

<!-- OPENSPEC:END -->
