<?php
/**
 * WPCleanAdmin - 辅助函数类
 *
 * 提供各种辅助函数和工具方法，包括增强的日志记录功能
 *
 * @package WPCleanAdmin
 * @since 1.0.0
 */

/**
 * WPCA_Helpers 类
 *
 * 提供各种辅助函数和工具方法
 */
class WPCA_Helpers {
    
    /**
     * 检查是否为多站点
     * 
     * @return bool 是否为多站点
     */
    public static function is_multisite() {
        return (function_exists('is_multisite') && is_multisite());
    }
    
    /**
     * 记录安全审计日志
     * 
     * @param string $action 执行的操作
     * @param string $result 操作结果 (success, failed, attempt)
     * @param array $details 详细信息
     * @param int $user_id 用户ID，默认为当前用户
     * @return void
     */
    public static function audit_log($action, $result = 'attempt', $details = array(), $user_id = null) {
        // 确保启用了审计日志功能
        if (!defined('WPCA_ENABLE_AUDIT_LOGS') || !WPCA_ENABLE_AUDIT_LOGS) {
            return;
        }
        
        // 获取当前用户ID（如果未提供）
        if ($user_id === null && function_exists('get_current_user_id')) {
            $user_id = get_current_user_id();
        }
        
        // 获取用户信息（如果可用）
        $user_info = array();
        if ($user_id && function_exists('get_userdata')) {
            $user = get_userdata($user_id);
            if ($user) {
                $user_info = array(
                    'id' => $user->ID,
                    'username' => $user->user_login,
                    'email' => $user->user_email,
                    'role' => !empty($user->roles) ? $user->roles[0] : 'unknown'
                );
            }
        }
        
        // 获取请求信息
        $request_info = array(
            'timestamp' => current_time('mysql'),
            'ip_address' => self::get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
            'request_uri' => isset($_SERVER['REQUEST_URI']) ? sanitize_text_field($_SERVER['REQUEST_URI']) : '',
            'referrer' => isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field($_SERVER['HTTP_REFERER']) : ''
        );
        
        // 构建审计日志数据
        $audit_data = array(
            'action' => sanitize_text_field($action),
            'result' => sanitize_text_field($result),
            'user' => $user_info,
            'request' => $request_info,
            'details' => $details
        );
        
        // 记录审计日志
        $log_message = sprintf(
            '[WPCA Security Audit] %s - %s - User: %s',
            strtoupper($result),
            sanitize_text_field($action),
            !empty($user_info['username']) ? $user_info['username'] : 'unknown'
        );
        
        // 调用标准日志方法记录审计日志
        self::log(
            $log_message,
            $audit_data,
            'info',
            ($result === 'failed') // 失败操作包含堆栈跟踪
        );
        
        // 额外处理：将审计日志保存到数据库（如果启用）
        if (defined('WPCA_SAVE_AUDIT_TO_DB') && WPCA_SAVE_AUDIT_TO_DB && self::can_write_to_db()) {
            self::save_audit_to_db($audit_data);
        }
    }
    
    /**
     * 获取客户端IP地址
     * 
     * @return string 客户端IP地址
     */
    private static function get_client_ip() {
        $ip = '';
        
        // 检查各种可能的IP来源
        $ip_sources = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_sources as $source) {
            if (isset($_SERVER[$source]) && !empty($_SERVER[$source])) {
                $ip = sanitize_text_field($_SERVER[$source]);
                
                // 对于代理，取第一个IP
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                // 验证IP格式
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    break;
                }
            }
        }
        
        return $ip;
    }
    
    /**
     * 检查是否可以写入数据库
     * 
     * @return bool 是否可以写入数据库
     */
    private static function can_write_to_db() {
        return function_exists('wpdb') && isset($GLOBALS['wpdb']);
    }
    
    /**
     * 将审计日志保存到数据库
     * 
     * @param array $audit_data 审计日志数据
     * @return bool 是否保存成功
     */
    private static function save_audit_to_db($audit_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wpca_audit_logs';
        
        // 检查表是否存在，如果不存在则创建
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s", 
            $table_name
        )) === $table_name;
        
        if (!$table_exists) {
            self::create_audit_log_table($table_name);
        }
        
        // 准备数据
        $data = array(
            'action' => $audit_data['action'],
            'result' => $audit_data['result'],
            'user_id' => !empty($audit_data['user']['id']) ? (int) $audit_data['user']['id'] : 0,
            'username' => !empty($audit_data['user']['username']) ? $audit_data['user']['username'] : '',
            'ip_address' => $audit_data['request']['ip_address'],
            'user_agent' => substr($audit_data['request']['user_agent'], 0, 500), // 限制长度
            'request_uri' => substr($audit_data['request']['request_uri'], 0, 500), // 限制长度
            'details' => maybe_serialize($audit_data['details']),
            'timestamp' => current_time('mysql')
        );
        
        $format = array(
            '%s', // action
            '%s', // result
            '%d', // user_id
            '%s', // username
            '%s', // ip_address
            '%s', // user_agent
            '%s', // request_uri
            '%s', // details
            '%s'  // timestamp
        );
        
        // 插入数据
        return $wpdb->insert($table_name, $data, $format);
    }
    
    /**
     * 创建审计日志表
     * 
     * @param string $table_name 表名
     * @return bool 是否创建成功
     */
    private static function create_audit_log_table($table_name) {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            action varchar(255) NOT NULL,
            result varchar(50) NOT NULL,
            user_id bigint(20) NOT NULL DEFAULT 0,
            username varchar(100) NOT NULL DEFAULT '',
            ip_address varchar(50) NOT NULL DEFAULT '',
            user_agent longtext NOT NULL,
            request_uri longtext NOT NULL,
            details longtext NOT NULL,
            timestamp datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY timestamp (timestamp)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        return dbDelta($sql);
    }
    
    /**
     * 获取插件版本号
     *
     * @return string 插件版本号
     */
    public static function get_plugin_version() {
        if (defined('WPCA_VERSION')) {
            return WPCA_VERSION;
        }
        return '1.0.0';
    }
    
    /**
     * 获取插件名称
     *
     * @return string 插件名称
     */
    public static function get_plugin_name() {
        return 'WPCleanAdmin';
    }
    
    /**
     * 获取插件前缀
     *
     * @return string 插件前缀
     */
    public static function get_plugin_prefix() {
        return 'wpca_';
    }
    
    /**
     * 获取文本域
     *
     * @return string 文本域
     */
    public static function get_text_domain() {
        return 'wp-clean-admin';
    }
    
    /**
     * 增强的错误日志记录函数
     *
     * @param string $message 日志消息
     * @param array $context 上下文信息（可选）
     * @param string $level 日志级别（debug, info, notice, warning, error, critical）
     * @param bool $include_stack 是否包含堆栈跟踪（默认false）
     * @return void
     */
    public static function log($message, $context = array(), $level = 'info', $include_stack = false) {
        // 检查是否可以记录日志
        if (!function_exists('error_log')) {
            return;
        }
        
        // 获取调用者信息
        $caller = self::get_caller_info();
        $caller_class = isset($caller['class']) ? $caller['class'] : 'Unknown';
        $caller_function = isset($caller['function']) ? $caller['function'] : 'Unknown';
        $caller_line = isset($caller['line']) ? $caller['line'] : 'Unknown';
        $caller_file = isset($caller['file']) ? $caller['file'] : 'Unknown';
        
        // 构建日志消息
        $timestamp = gmdate('Y-m-d H:i:s');
        $log_message = sprintf(
            '[%s] [%s] [%s] [%s::%s():%s] %s',
            $timestamp,
            strtoupper($level),
            self::get_plugin_name(),
            $caller_class,
            $caller_function,
            $caller_line,
            $message
        );
        
        // 添加上下文信息
        if (!empty($context)) {
            $log_message .= "\nContext: " . print_r($context, true);
        }
        
        // 添加堆栈跟踪（如果需要）
        if ($include_stack && defined('WP_DEBUG') && WP_DEBUG) {
            $stack_trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            // 移除当前函数调用
            array_shift($stack_trace);
            
            $stack_info = "\nStack Trace:";
            foreach ($stack_trace as $index => $trace) {
                $trace_class = isset($trace['class']) ? $trace['class'] : '';
                $trace_function = isset($trace['function']) ? $trace['function'] : '';
                $trace_file = isset($trace['file']) ? $trace['file'] : 'Unknown';
                $trace_line = isset($trace['line']) ? $trace['line'] : 'Unknown';
                
                $stack_info .= sprintf(
                    "\n#%d %s%s%s() %s:%s",
                    $index,
                    $trace_class,
                    !empty($trace_class) ? '::' : '',
                    $trace_function,
                    $trace_file,
                    $trace_line
                );
            }
            
            $log_message .= $stack_info;
        }
        
        // 记录日志
        error_log($log_message);
    }
    
    /**
     * 获取调用者信息
     *
     * @return array 调用者信息
     */
    private static function get_caller_info() {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        
        // 跳过当前函数和log函数
        $skip = 2;
        
        if (isset($backtrace[$skip])) {
            $caller = $backtrace[$skip];
            return array(
                'class' => isset($caller['class']) ? $caller['class'] : null,
                'function' => isset($caller['function']) ? $caller['function'] : null,
                'file' => isset($caller['file']) ? $caller['file'] : null,
                'line' => isset($caller['line']) ? $caller['line'] : null
            );
        }
        
        return array();
    }
}