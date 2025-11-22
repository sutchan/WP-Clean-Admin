<?php
/**
 * WP Clean Admin Performance Class
 *
 * Handles all performance-related optimizations and monitoring for the WordPress admin area.
 *
 * @package WP_Clean_Admin
 * @since 1.6.0
 * @version 1.7.13
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * 性能优化类 * 提供数据库优化、查询监控和性能统计功能
 */
class WPCA_Performance {
    /**
     * 类实例     * @var WPCA_Performance
     */
    private static $instance = null;
    
    /**
     * 插件设置
     * @var array
     */
    private $options = array();

    /**
     * 性能统计数据
     * @var array
     */
    private $stats = array();

    /**
     * 数据库查询计数     * @var int
     */
    private $query_count = 0;
    
    /**
     * 原始统计数据
     * @var array
     */
    private $raw_stats = array();

    /**
     * WPCA_Performance constructor.
     * 初始化性能优化功能，注册必要的钩子
     */
    private function __construct() {
        // 获取插件设置
        if (class_exists('WPCA_Settings') && method_exists('WPCA_Settings', 'get_options')) {
            $this->options = WPCA_Settings::get_options();
        } else {
            $this->options = array();
        }
        
        // 注册性能相关钩子
        if (method_exists($this, 'register_hooks')) {
            $this->register_hooks();
        }
    }
    
    /**
     * 获取类的单例实例
     * 
     * @return WPCA_Performance|null 类的单例实例或null
     */
    public static function get_instance() {
        try {
            if (null === self::$instance) {
                self::$instance = new self;

            
            return self::$instance;
        } catch (Exception $e) {
            // 安全记录异常但不泄露敏感信息
            if (function_exists('error_log')) {
                error_log('WPCA: Error creating performance instance');
            }
            return null;
        }
    }

    /**
     * 注册性能优化相关的钩子     */
    private function register_hooks() {
        try {
            // 只在管理区域加载性能优化
            $is_admin_area = false;
            if (function_exists('is_admin')) {
                $is_admin_area = is_admin();
            }
            
            if ($is_admin_area) {
                // 数据库优化钩子                if (isset($this->options['enable_db_optimization']) && $this->options['enable_db_optimization']) {
                    if (function_exists('add_action')) {
                        add_action('admin_init', array($this, 'optimize_database_on_init'));
                    }
                }
                
                // 性能监控钩子
                if (isset($this->options['enable_performance_monitoring']) && $this->options['enable_performance_monitoring']) {
                    if (function_exists('add_action')) {
                        // 初始化性能监控
                        add_action('admin_init', array($this, 'init_performance_monitoring'));
                        // 保存性能数据
                        add_action('shutdown', array($this, 'save_performance_data'));
                    }
                    
                    // 监控数据库查询                    if (function_exists('add_filter')) {
                        add_filter('query', array($this, 'track_db_queries'));
                    }
                }
                
                // AJAX钩子
                if (function_exists('add_action')) {
                    add_action('wp_ajax_wpca_toggle_performance_monitoring', array($this, 'ajax_toggle_performance_monitoring'));
                    add_action('wp_ajax_wpca_get_performance_report', array($this, 'ajax_get_performance_report'));
                    add_action('wp_ajax_wpca_clear_performance_data', array($this, 'ajax_clear_performance_data'));
                }
            }
        } catch (Exception $e) {
            // 安全记录异常但不泄露敏感信息
            if (function_exists('error_log')) {
                error_log('WPCA: Error registering performance hooks');
            }
        }
    }

    /**
     * 初始化性能监控
     */
    public function init_performance_monitoring() {
        try {
            // 初始化统计数据数组
            $this->stats = array();
            
            // 获取采样率设置
            $sampling_rate = 100; // 榛樿閲囨牱鐜囦负100%
            if (isset($this->options['performance_monitoring_sampling_rate'])) {
                $sampling_rate = max(1, min(100, intval($this->options['performance_monitoring_sampling_rate'])));
            }
            
            // 根据采样率决定是否进行监控            if (function_exists('rand') && rand(1, 100) <= $sampling_rate) {
                // 记录开始时间和内存使用
                if (function_exists('microtime')) {
                    $this->stats['start_time'] = microtime(true);
                }
                if (function_exists('memory_get_usage')) {
                    $this->stats['start_memory'] = memory_get_usage(true);
                }
                
                // 记录页面信息
                if (isset($_SERVER['REQUEST_URI'])) {
                    // 安全处理URI
                    $uri = $_SERVER['REQUEST_URI'];
                    // 过滤掉敏感信息                    if (function_exists('strpos') && strpos($uri, '?') !== false) {
                        if (function_exists('explode')) {
                            list($path, $query) = explode('?', $uri, 2);
                            // 保留路径，只取查询参数的前100个字符                            $uri = $path . '?' . (function_exists('substr') ? substr($query, 0, 100) : '');
                        }
                    }
                    // 限制URI长度
                    $uri = function_exists('substr') ? substr($uri, 0, 255) : $uri;
                    $this->stats['page'] = $uri;
                } else {
                    $this->stats['page'] = 'Unknown';
                }
                
                // 重置查询计数
                $this->query_count = 0;
            }
        } catch (Exception $e) {
            // 安全记录异常但不泄露敏感信息
            if (function_exists('error_log')) {
                error_log('WPCA: Error initializing performance monitoring');
            }
        }
    }

    /**
     * 跟踪数据库查询
     * @param string $query 数据库查询字符串
     * @return string 原始查询字符串     */
    public function track_db_queries($query) {
        try {
            // 只在初始化了性能数据后才跟踪查询
            if (!empty($this->stats) && isset($this->stats['start_time'])) {
                // 增加查询计数
                $this->query_count++;
                
                // 可选：记录查询详情（仅在调试模式下）                if (defined('WP_DEBUG') && WP_DEBUG && $this->query_count <= 100) {
                    // 确保stats数组存在且可以存储查询                    if (!isset($this->stats['queries'])) {
                        $this->stats['queries'] = array();
                    }
                    
                    // 只记录非空查询                    if (!empty($query)) {
                        // 安全处理查询字符串，移除可能的敏感信息                        $safe_query = is_string($query) ? substr($query, 0, 500) : '';
                        $this->stats['queries'][] = $safe_query;
                    }
                }
            }
        } catch (Exception $e) {
            // 安全记录异常但不泄露敏感信息
            if (function_exists('error_log')) {
                error_log('WPCA: Error tracking database queries');
            }
        }
        
        return $query;
    }

    /**
     * 显示查询统计信息（仅在调试模式下）     */
    public function display_query_stats() {
        try {
            // 鍙湪璋冭瘯妯″紡涓嬫樉绀?            if (defined('WP_DEBUG') && WP_DEBUG && !empty($this->stats)) {
                // 瀹夊叏获取性能数据
                $query_count = isset($this->query_count) ? max(0, intval($this->query_count)) : 0;
                $load_time = 0;
                $memory_usage = 0;
                
                // 璁＄畻鍔犺浇鏃堕棿
                if (isset($this->stats['start_time']) && function_exists('microtime')) {
                    $load_time = microtime(true) - $this->stats['start_time'];
                }
                
                // 璁＄畻鍐呭瓨浣跨敤
                if (isset($this->stats['start_memory']) && function_exists('memory_get_usage')) {
                    $memory_usage = memory_get_usage(true) - $this->stats['start_memory'];
                }
                
                // 瀹夊叏鏋勫缓鏃ュ織娑堟伅
                $log_message = 'WPCA: ' . number_format($load_time, 4) . 's | ' . $query_count . ' queries | ' . number_format($memory_usage / 1024 / 1024, 2) . 'MB';
                
                // 瀹夊叏璁板綍鏃ュ織
                if (function_exists('error_log')) {
                    error_log($log_message);
                }
            }
        } catch (Exception $e) {
            // 瀹夊叏璁板綍寮傚父浣嗕笉娉勯湶鏁忔劅淇℃伅
            if (function_exists('error_log')) {
                error_log('WPCA: Error displaying query stats');
            }
        }
    }

    /**
     * 淇濆瓨鎬ц兘鏁版嵁
     */
    public function save_performance_data() {
        try {
            // 妫€鏌ユ槸鍚︽湁鍒濆鍖栫殑鎬ц兘鏁版嵁
            if (!isset($this->stats['start_time']) || !is_array($this->stats)) {
                return;
            }
            
            // 璁＄畻鏈€缁堟€ц兘鏁版嵁锛屽鍔犲嚱鏁板瓨鍦ㄦ€ф鏌ュ拰绫诲瀷瀹夊叏
            $timestamp = function_exists('current_time') ? 
                current_time('timestamp') : 
                (function_exists('time') ? time() : 0);
            $user_id = function_exists('get_current_user_id') ? get_current_user_id() : 0;
            
            // 瀹夊叏璁＄畻鍔犺浇鏃堕棿
              $load_time = 0;
              // isset鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?              if (function_exists('microtime') && isset($this->stats['start_time'])) {
                $load_time = microtime(true) - $this->stats['start_time'];
            }
            
            // 瀹夊叏璁＄畻鍐呭瓨浣跨敤閲?              $memory_usage = 0;
              // isset鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?              if (function_exists('memory_get_peak_usage') && isset($this->stats['start_memory'])) {
                $memory_usage = memory_get_peak_usage(true) - $this->stats['start_memory'];
                // 纭繚鍐呭瓨鍊间负姝ｆ暟
                $memory_usage = max(0, $memory_usage);
            }
            
            $performance_data = array(
                'page' => isset($this->stats['page']) ? 
                    (is_string($this->stats['page']) ? 
                        (function_exists('sanitize_text_field') ? 
                            sanitize_text_field($this->stats['page']) : 
                            (function_exists('substr') ? substr($this->stats['page'], 0, 255) : '')) : 
                        '') : 
                    '',
                'load_time' => floatval($load_time),
                'memory_usage' => intval($memory_usage),
                'query_count' => isset($this->query_count) ? intval($this->query_count) : 0,
                'timestamp' => intval($timestamp),
                'user_id' => intval($user_id),
            );
            
            // 鑾峰彇鐜版湁鎬ц兘鏁版嵁
            $performance_log = array();
            if (function_exists('get_option')) {
                $existing_log = get_option('wpca_performance_log', array());
                // 确保$performance_log是数组                if (is_array($existing_log)) {
                    $performance_log = $existing_log;
                }
            }
            
            // 娣诲姞鏂版暟鎹?            $performance_log[] = $performance_data;
            
            // 闄愬埗鏁版嵁閲忥紝鍙繚鐣欐渶杩戠殑1000鏉¤褰?            $max_entries = 1000;
            if (function_exists('count') && count($performance_log) > $max_entries) {
                if (function_exists('array_slice')) {
                    $performance_log = array_slice($performance_log, -$max_entries);
                }
            }
            
            // 淇濆瓨鏁版嵁
            if (function_exists('update_option')) {
                update_option('wpca_performance_log', $performance_log);
            }
            
            // 瀹夊叏璋冪敤娓呯悊鏂规硶
            if (method_exists($this, 'cleanup_old_performance_data')) {
                $this->cleanup_old_performance_data();
            }
        } catch (Exception $e) {
            // 瀹夊叏璁板綍寮傚父浣嗕笉娉勯湶鏁忔劅淇℃伅
            if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                error_log('WPCA: Error saving performance data');
            }
        }
    }

    /**
     * 娓呯悊杩囨湡鐨勬€ц兘鏁版嵁
     */
    private function cleanup_old_performance_data() {
        try {
            // 鑾峰彇淇濈暀澶╂暟璁剧疆锛屽鍔犵被鍨嬪畨鍏?            $retention_days = 7; // 榛樿鍊?            if (function_exists('get_option')) {
                $saved_retention = get_option('wpca_monitoring_data_retention', 7);
                $retention_days = intval($saved_retention);
                // 纭繚淇濈暀澶╂暟鍦ㄥ悎鐞嗚寖鍥村唴锛?-365澶╋級
                $retention_days = max(1, min(365, $retention_days));
            }
            
            // 瀹夊叏璁＄畻鎴鏃堕棿
            $current_timestamp = function_exists('current_time') ? 
                current_time('timestamp') : 
                (function_exists('time') ? time() : 0);
            
            // 瀹夊叏浣跨敤DAY_IN_SECONDS甯搁噺鎴栧畾涔?            $day_in_seconds = defined('DAY_IN_SECONDS') ? DAY_IN_SECONDS : (24 * 60 * 60);
            $cutoff_timestamp = $current_timestamp - ($retention_days * $day_in_seconds);
            
            // 鑾峰彇鐜版湁鎬ц兘鏁版嵁
            $performance_log = array();
            if (function_exists('get_option')) {
                $existing_log = get_option('wpca_performance_log', array());
                // 纭繚$performance_log鏄暟缁?                if (is_array($existing_log)) {
                    $performance_log = $existing_log;
                }
            }
            
            // 杩囨护鎺夎繃鏈熸暟鎹?            $filtered_log = array();
            foreach ($performance_log as $entry) {
                // 瀹夊叏妫€鏌ユ潯鐩槸鍚︽湁鏁?                if (is_array($entry) && isset($entry['timestamp']) && intval($entry['timestamp']) > $cutoff_timestamp) {
                    $filtered_log[] = $entry;
                }
            }
            
            // 鍙湁鍦ㄦ暟鎹彂鐢熷彉鍖栨椂鎵嶆洿鏂?            if (count($filtered_log) !== count($performance_log)) {
                if (function_exists('update_option')) {
                    update_option('wpca_performance_log', $filtered_log);
                }
            }
        } catch (Exception $e) {
            // 瀹夊叏璁板綍寮傚父浣嗕笉娉勯湶鏁忔劅淇℃伅
            if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                error_log('WPCA: Error cleaning up old performance data');
            }
        }
    }

    /**
     * 鑾峰彇鍘嗗彶鎬ц兘缁熻
     * @return array 鎬ц兘缁熻鏁版嵁
     */
    public function get_historical_performance_stats() {
        try {
            // 鑾峰彇鎬ц兘鏃ュ織鏁版嵁
            $performance_log = array();
            if (function_exists('get_option')) {
                $existing_log = get_option('wpca_performance_log', array());
                // 纭繚$performance_log鏄暟缁?                if (is_array($existing_log)) {
                    $performance_log = $existing_log;
                }
            }
            
            // 鍒濆鍖栫粺璁℃暟鎹?            $stats = array(
                'total_samples' => 0,
                'total_load_time' => 0,
                'total_queries' => 0,
                'total_memory' => 0,
                'avg_load_time' => 0,
                'avg_queries' => 0,
                'avg_memory' => 0,
                'slow_pages_count' => 0,
                'peak_memory' => 0,
                'slow_queries_count' => 0,
                'date_range' => array(
                    'start' => null,
                    'end' => null
                )
            );
            
            return $stats;
        } catch (Exception $e) {
            // 瀹夊叏璁板綍寮傚父骞惰繑鍥炵┖缁熻
            if (function_exists('error_log')) {
                error_log('WPCA: Error getting historical performance stats');
            }
            
            return array(
                'total_samples' => 0,
                'total_load_time' => 0,
                'total_queries' => 0,
                'total_memory' => 0,
                'avg_load_time' => 0,
                'avg_queries' => 0,
                'avg_memory' => 0,
                'slow_pages_count' => 0,
                'peak_memory' => 0,
                'slow_queries_count' => 0,
                'date_range' => array(
                    'start' => null,
                    'end' => null
                )
            );
        }
    }

    /**
     * 鑾峰彇鎬ц兘缁熻鏁版嵁
     * @return array 鎬ц兘缁熻鏁版嵁
     */
    public function get_performance_stats() {
        try {
            // 鍩虹缁熻鏁版嵁
            $stats = array(
                'total_samples' => 0,
                'total_load_time' => 0,
                'total_queries' => 0,
                'total_memory' => 0,
                'avg_load_time' => 0,
                'avg_queries' => 0,
                'avg_memory' => 0,
                'slow_pages_count' => 0,
                'peak_memory' => 0,
                'slow_queries_count' => 0
            );
            
            // 鑾峰彇鎬ц兘鏃ュ織鏁版嵁
            $performance_log = array();
            if (function_exists('get_option')) {
                $existing_log = get_option('wpca_performance_log', array());
                // 纭繚$performance_log鏄暟缁?                if (is_array($existing_log)) {
                    $performance_log = $existing_log;
                }
            }
            
            // 濡傛灉鏈夊巻鍙茬粺璁℃暟鎹紝鍒欏悎骞?            if (method_exists($this, 'get_historical_performance_stats')) {
                $historical_stats = $this->get_historical_performance_stats();
                // 瀹夊叏鍚堝苟鏁版嵁
                if (is_array($historical_stats)) {
                    // 纭繚瀹夊叏鍚堝苟鏁扮粍
                    if (function_exists('array_merge')) {
                        $stats = array_merge($stats, $historical_stats);
                    } else {
                        // 鎵嬪姩鍚堝苟鍏抽敭缁熻鏁版嵁
                        foreach (array_keys($stats) as $key) {
                            if (isset($historical_stats[$key])) {
                                $stats[$key] = $historical_stats[$key];
                            }
                        }
                    }
                }
            }
            
            // 瀹夊叏澶勭悊宄板€煎唴瀛?              // isset鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?              $stats['peak_memory'] = function_exists('max') && isset($stats['peak_memory']) ? max(0, $stats['peak_memory']) : 0;
            
            // 瀹夊叏澶勭悊鎱㈡煡璇㈣鏁?            $stats['slow_queries_count'] = isset($stats['slow_queries_count']) ? max(0, intval($stats['slow_queries_count'])) : 0;
            
            // 瀹夊叏澶勭悊鎬绘牱鏈暟
            $stats['total_samples'] = isset($stats['total_samples']) ? max(0, intval($stats['total_samples'])) : 0;
            
            // 瀹夊叏澶勭悊鎬诲姞杞芥椂闂?            $stats['total_load_time'] = isset($stats['total_load_time']) ? max(0, floatval($stats['total_load_time'])) : 0;
            
            // 瀹夊叏澶勭悊鎬绘煡璇㈡暟
            $stats['total_queries'] = isset($stats['total_queries']) ? max(0, intval($stats['total_queries'])) : 0;
            
            // 瀹夊叏澶勭悊鎬诲唴瀛?            $stats['total_memory'] = isset($stats['total_memory']) ? max(0, intval($stats['total_memory'])) : 0;
            
            // 瀹夊叏璁＄畻骞冲潎鍊硷紝閬垮厤闄ら浂閿欒
            if ($stats['total_samples'] > 0) {
                $stats['avg_load_time'] = floatval($stats['total_load_time']) / $stats['total_samples'];
                $stats['avg_queries'] = floatval($stats['total_queries']) / $stats['total_samples'];
                $stats['avg_memory'] = floatval($stats['total_memory']) / $stats['total_samples'];
            }
            
            // 瀹夊叏澶勭悊鎱㈤〉闈㈣鏁?            $stats['slow_pages_count'] = isset($stats['slow_pages_count']) ? max(0, intval($stats['slow_pages_count'])) : 0;
            
            // 瀹夊叏娣诲姞WordPress鐗堟湰淇℃伅
            $wp_version = defined('WP_VERSION') ? WP_VERSION : 'Unknown';
            if (function_exists('sanitize_text_field')) {
                $wp_version = sanitize_text_field($wp_version);
            }
            // 闄愬埗鐗堟湰瀛楃涓查暱搴?            $wp_version = function_exists('substr') ? substr($wp_version, 0, 50) : $wp_version;
            $stats['wp_version'] = $wp_version;
            
            // 瀹夊叏娣诲姞PHP鐗堟湰淇℃伅
            $php_version = function_exists('phpversion') ? phpversion() : (PHP_VERSION ?? 'Unknown');
            if (function_exists('sanitize_text_field')) {
                $php_version = sanitize_text_field($php_version);
            }
            // 闄愬埗鐗堟湰瀛楃涓查暱搴?            $php_version = function_exists('substr') ? substr($php_version, 0, 50) : $php_version;
            $stats['php_version'] = $php_version;
            
            // 瀹夊叏澶勭悊鍘熷缁熻鏁版嵁
            $raw_stats = isset($this->raw_stats) && is_array($this->raw_stats) ? $this->raw_stats : array();
            $stats['raw_data'] = $raw_stats;
            
            return $stats;
        } catch (Exception $e) {
            // 瀹夊叏璁板綍寮傚父骞惰繑鍥炵┖缁熻
            if (function_exists('error_log')) {
                $error_msg = 'WPCA: Error getting performance stats: ';
                if ($e instanceof Exception && method_exists($e, 'getMessage')) {
                    $error_msg .= $e->getMessage();
                } else {
                    $error_msg .= 'Unknown error';
                }
                error_log($error_msg);
            }
            
            return array(
                'total_samples' => 0,
                'total_load_time' => 0,
                'total_queries' => 0,
                'total_memory' => 0,
                'avg_load_time' => 0,
                'avg_queries' => 0,
                'avg_memory' => 0,
                'slow_pages_count' => 0,
                'peak_memory' => 0,
                'slow_queries_count' => 0,
                'wp_version' => 'Unknown',
                'php_version' => function_exists('phpversion') ? phpversion() : (PHP_VERSION ?? 'Unknown'),
                'raw_data' => array()
            );
        }
    }

    /**
     * 鐢熸垚鎬ц兘鎶ュ憡
     * 
     * @param array $performance_log 鎬ц兘鏃ュ織鏁版嵁
     * @return array 鐢熸垚鐨勬姤鍛?     */
    private function generate_performance_report($performance_log) {
        try {
            // 鍙傛暟绫诲瀷楠岃瘉
            if (!is_array($performance_log)) {
                $no_data_text = function_exists('__') ? __('No performance data available.', 'wp-clean-admin') : 'No performance data available.';
                return array(
                    'summary' => $no_data_text,
                    'detailed' => array()
                );
            }
            
            if (empty($performance_log)) {
                $no_data_text = function_exists('__') ? __('No performance data available.', 'wp-clean-admin') : 'No performance data available.';
                return array(
                    'summary' => $no_data_text,
                    'detailed' => array()
                );
            }
            
            // 鎸夐〉闈㈠垎缁勭粺璁?            $page_stats = array();
            foreach ($performance_log as $entry) {
                if (!is_array($entry)) {
                    continue; // 璺宠繃闈炴暟缁勬潯鐩?                }
                
                // 瀹夊叏鑾峰彇骞惰繃婊ら〉闈㈣矾寰?                $page = isset($entry['page']) ? $entry['page'] : 'Unknown';
                // 瀹夊叏杩囨护椤甸潰璺緞
                if (function_exists('sanitize_text_field')) {
                    $page = sanitize_text_field($page);
                }
                // 闄愬埗椤甸潰鍚嶇О闀垮害
                $page = substr($page, 0, 255);
                
                if (!isset($page_stats[$page])) {
                    $page_stats[$page] = array(
                        'count' => 0,
                        'total_load_time' => 0,
                        'total_queries' => 0,
                        'total_memory' => 0,
                        'min_load_time' => defined('PHP_INT_MAX') ? PHP_INT_MAX : PHP_INT_MAX,
                        'max_load_time' => 0
                    );
                }
                
                // 类型安全处理和非负确保                $page_stats[$page]['count']++;
                $load_time = isset($entry['load_time']) ? floatval($entry['load_time']) : 0;
                $page_stats[$page]['total_load_time'] += max(0, $load_time);
                
                $query_count = isset($entry['query_count']) ? intval($entry['query_count']) : 0;
                $page_stats[$page]['total_queries'] += max(0, $query_count);
                
                $memory_usage = isset($entry['memory_usage']) ? intval($entry['memory_usage']) : 0;
                $page_stats[$page]['total_memory'] += max(0, $memory_usage);
                
                // 安全使用min和max函数
                $current_load_time = max(0, floatval($load_time));
                if (function_exists('min')) {
                    $page_stats[$page]['min_load_time'] = min($page_stats[$page]['min_load_time'], $current_load_time);
                }
                if (function_exists('max')) {
                    $page_stats[$page]['max_load_time'] = max($page_stats[$page]['max_load_time'], $current_load_time);
                }
            }
            
            // 计算平均值并排序
            $detailed = array();
            foreach ($page_stats as $page => $stats) {
                $safe_count = max(1, intval($stats['count'])); // 閬垮厤闄ら浂閿欒
                $detailed[] = array(
                    'page' => $page,
                    'avg_load_time' => floatval($stats['total_load_time']) / $safe_count,
                    'avg_queries' => floatval($stats['total_queries']) / $safe_count,
                    'avg_memory' => floatval($stats['total_memory']) / $safe_count,
                    'min_load_time' => floatval($stats['min_load_time']),
                    'max_load_time' => floatval($stats['max_load_time']),
                    'count' => intval($stats['count'])
                );
            }
            
            // 按平均加载时间排序（最慢的在前）            if (function_exists('usort')) {
                usort($detailed, function($a, $b) {
                    $a_time = isset($a['avg_load_time']) ? floatval($a['avg_load_time']) : 0;
                    $b_time = isset($b['avg_load_time']) ? floatval($b['avg_load_time']) : 0;
                    return $b_time - $a_time;
                });
            }
            
            // 限制详细数据数量
            if (function_exists('array_slice') && count($detailed) > 100) {
                $detailed = array_slice($detailed, 0, 100);
            }
            
            // 生成摘要
            $total_pages = function_exists('count') ? count($detailed) : 0;
            $slow_pages_count = 0;
            
            // 安全计算慢页面数量            if (function_exists('count') && function_exists('array_filter')) {
                $slow_pages = array_filter($detailed, function($page) {
                    return isset($page['avg_load_time']) && floatval($page['avg_load_time']) > 1; // 瓒呰繃1绉掔殑椤甸潰瑙嗕负鎱㈤〉闈?                });
                $slow_pages_count = count($slow_pages);
            }
            
            // 安全地使用国际化函数
            $summary_text = function_exists('__') ? 
                __('Performance report based on %d samples across %d pages. Found %d slow pages (loading time > 1s).', 'wp-clean-admin') :
                'Performance report based on %d samples across %d pages. Found %d slow pages (loading time > 1s).';
            
            // 安全使用sprintf函数
            $sample_count = function_exists('count') ? count($performance_log) : 0;
            $safe_sample_count = max(0, intval($sample_count));
            $safe_total_pages = max(0, intval($total_pages));
            $safe_slow_pages_count = max(0, intval($slow_pages_count));
            
            if (function_exists('sprintf')) {
                $summary = sprintf($summary_text, $safe_sample_count, $safe_total_pages, $safe_slow_pages_count);
            } else {
                $summary = "Performance report based on $safe_sample_count samples across $safe_total_pages pages. Found $safe_slow_pages_count slow pages (loading time > 1s).";
            }
            
            return array(
                'summary' => $summary,
                'detailed' => $detailed
            );
        } catch (Exception $e) {
            // 安全记录异常并返回空报告
            $error_message = 'Error generating performance report: ' . ($e instanceof Exception ? $e->getMessage() : 'Unknown error');
            
            // 安全记录错误（如果可能）
            if (function_exists('error_log')) {
                error_log($error_message);
            }
            
            $error_text = function_exists('__') ? __('Error generating performance report.', 'wp-clean-admin') : 'Error generating performance report.';
            return array(
                'summary' => $error_text,
                'detailed' => array()
            );
        }
    }

    /**
     * AJAX切换性能监控状态     */
    public function ajax_toggle_performance_monitoring() {
        try {
            // 检查是否为AJAX请求
            $is_ajax_request = false;
            if (function_exists('wp_doing_ajax')) {
                $is_ajax_request = wp_doing_ajax();
            } elseif (defined('DOING_AJAX') && DOING_AJAX) {
                $is_ajax_request = true;
            }
            
            if (!$is_ajax_request) {
                if (function_exists('wp_send_json_error')) {
                    $error_message = function_exists('__') ? __('Invalid request', 'wp-clean-admin') : 'Invalid request';
                    wp_send_json_error(array('message' => $error_message), 400);
                } elseif (function_exists('wp_die')) {
                    $error_message = function_exists('__') ? __('Invalid request', 'wp-clean-admin') : 'Invalid request';
                    wp_die($error_message, 400);
                } else {
                    die('Invalid request');
                }
                return;
            }
            
            // 检查用户权限            $has_permission = false;
            if (function_exists('current_user_can')) {
                $has_permission = current_user_can('manage_options');
            }
            
            if (!$has_permission) {
                if (function_exists('wp_send_json_error')) {
                    $error_message = function_exists('__') ? __('Insufficient permissions', 'wp-clean-admin') : 'Insufficient permissions';
                    wp_send_json_error(array('message' => $error_message), 403);
                } elseif (function_exists('wp_die')) {
                    $error_message = function_exists('__') ? __('Insufficient permissions', 'wp-clean-admin') : 'Insufficient permissions';
                    wp_die($error_message, 403);
                } else {
                    die('Insufficient permissions');
                }
                return;
            }
            
            // 获取新的监控状态            $new_status = false;
            if (isset($_POST['enabled'])) {
                $new_status = (bool)$_POST['enabled'];
            }
            
            // 更新监控状态            $updated = function_exists('update_option') ? update_option('wpca_monitoring_enabled', $new_status) : false;
            
            // 如果开启监控，初始化性能监控
            if ($new_status) {
                $this->init_performance_monitoring();
            }
            
            if ($updated) {
                // 返回成功结果
                $start_message = function_exists('__') ? __('Performance monitoring has been started.', 'wp-clean-admin') : 'Performance monitoring has been started.';
                $stop_message = function_exists('__') ? __('Performance monitoring has been stopped.', 'wp-clean-admin') : 'Performance monitoring has been stopped.';
                
                $response = array(
                    'enabled' => $new_status,
                    'message' => $new_status ? $start_message : $stop_message
                );
                
                if (function_exists('wp_send_json_success')) {
                    wp_send_json_success($response);
                } else {
                    echo json_encode(array('success' => true, 'data' => $response));
                }
            } else {
                $error_msg = function_exists('__') ? __('Failed to update monitoring status', 'wp-clean-admin') : 'Failed to update monitoring status';
                throw new Exception($error_msg);
            }
        } catch (Exception $e) {
            $error_msg = function_exists('__') ? __('Failed to toggle monitoring status.', 'wp-clean-admin') : 'Failed to toggle monitoring status.';
            $error_response = array('message' => $error_msg);
            
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error($error_response);
            } else {
                echo json_encode(array('success' => false, 'data' => $error_response));
            }
        }
        
        if (function_exists('wp_die')) {
            wp_die();
        } else {
            die();
        }
    }

    /**
     * AJAX获取性能报告
     */
    public function ajax_get_performance_report() {
        try {
            // 妫€鏌ユ槸鍚︿负AJAX璇锋眰
            $is_ajax_request = false;
            if (function_exists('wp_doing_ajax')) {
                $is_ajax_request = wp_doing_ajax();
            } elseif (defined('DOING_AJAX') && DOING_AJAX) {
                $is_ajax_request = true;
            }
            
            if (!$is_ajax_request) {
                if (function_exists('wp_send_json_error')) {
                    $error_message = function_exists('__') ? __('Invalid request', 'wp-clean-admin') : 'Invalid request';
                    wp_send_json_error(array('message' => $error_message), 400);
                } elseif (function_exists('wp_die')) {
                    $error_message = function_exists('__') ? __('Invalid request', 'wp-clean-admin') : 'Invalid request';
                    wp_die($error_message, 400);
                } else {
                    die('Invalid request');
                }
                return;
            }
            
            // 检查nonce安全验证
            $security_valid = true;
            if (function_exists('check_ajax_referer')) {
                $security_param = isset($_POST['security']) ? $_POST['security'] : '';
                $security_valid = check_ajax_referer('wpca-get-performance-report', 'security', false); // 涓嶈嚜鍔╠ie锛岀敱鎴戜滑鎺у埗閿欒澶勭悊
            }
            
            if (!$security_valid) {
                if (function_exists('wp_send_json_error')) {
                    $error_message = function_exists('__') ? __('Security check failed', 'wp-clean-admin') : 'Security check failed';
                    wp_send_json_error(array('message' => $error_message), 403);
                } elseif (function_exists('wp_die')) {
                    $error_message = function_exists('__') ? __('Security check failed', 'wp-clean-admin') : 'Security check failed';
                    wp_die($error_message, 403);
                } else {
                    die('Security check failed');
                }
                return;
            }
            
            // 妫€鏌ョ敤鎴锋潈闄?            $has_permission = false;
            if (function_exists('current_user_can')) {
                $has_permission = current_user_can('manage_options');
            }
            
            if (!$has_permission) {
                if (function_exists('wp_send_json_error')) {
                    $error_message = function_exists('__') ? __('Insufficient permissions', 'wp-clean-admin') : 'Insufficient permissions';
                    wp_send_json_error(array('message' => $error_message), 403);
                } elseif (function_exists('wp_die')) {
                    $error_message = function_exists('__') ? __('Insufficient permissions', 'wp-clean-admin') : 'Insufficient permissions';
                    wp_die($error_message, 403);
                } else {
                    die('Insufficient permissions');
                }
                return;
            }
            
            // 鑾峰彇鎬ц兘鏁版嵁
            $performance_log = array();
            if (function_exists('get_option')) {
                $existing_log = get_option('wpca_performance_log', array());
                // 纭繚$performance_log鏄暟缁?                if (is_array($existing_log)) {
                    $performance_log = $existing_log;
                }
            }
            
            // 生成报告
            if (method_exists($this, 'generate_performance_report')) {
                $report = $this->generate_performance_report($performance_log);
            } else {
                $report = array(
                    'summary' => function_exists('__') ? __('Report generation method not available.', 'wp-clean-admin') : 'Report generation method not available.',
                    'detailed' => array()
                );
            }
            
            // 返回报告数据
            if (function_exists('wp_send_json_success')) {
                wp_send_json_success($report);
            } else {
                echo json_encode(array('success' => true, 'data' => $report));
            }
        } catch (Exception $e) {
            // 安全记录异常并返回错误            $error_message = function_exists('__') ? __('Error generating performance report.', 'wp-clean-admin') : 'Error generating performance report.';
            
            if (function_exists('error_log')) {
                error_log('WPCA: ' . $error_message . ' - ' . ($e instanceof Exception ? $e->getMessage() : 'Unknown error'));
            }
            
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array('message' => $error_message));
            } else {
                echo json_encode(array('success' => false, 'message' => $error_message));
            }
        }
        
        if (function_exists('wp_die')) {
            wp_die();
        } else {
            die();
        }
    }

    /**
     * AJAX清理性能数据
     */
    public function ajax_clear_performance_data() {
        // 检查是否为AJAX请求（兼容WordPress不同版本）        $is_ajax_request = false;
        if (function_exists('wp_doing_ajax')) {
            $is_ajax_request = wp_doing_ajax();
        } elseif (defined('DOING_AJAX') && DOING_AJAX) {
            $is_ajax_request = true;
        }
        
        if (!$is_ajax_request) {
            if (function_exists('wp_send_json_error')) {
                $error_message = function_exists('__') ? __('Invalid request', 'wp-clean-admin') : 'Invalid request';
                wp_send_json_error(array('message' => $error_message), 400);
            } elseif (function_exists('wp_die')) {
                $error_message = function_exists('__') ? __('Invalid request', 'wp-clean-admin') : 'Invalid request';
                wp_die($error_message, 400);
            } else {
                die('Invalid request');
            }
            return;
        }
        
        try {
            // 妫€鏌ョ敤鎴锋潈闄?            $has_permission = false;
            if (function_exists('current_user_can')) {
                $has_permission = current_user_can('manage_options');
            }
            
            if (!$has_permission) {
                if (function_exists('wp_send_json_error')) {
                    $error_message = function_exists('__') ? __('Insufficient permissions', 'wp-clean-admin') : 'Insufficient permissions';
                    wp_send_json_error(array('message' => $error_message), 403);
                } elseif (function_exists('wp_die')) {
                    $error_message = function_exists('__') ? __('Insufficient permissions', 'wp-clean-admin') : 'Insufficient permissions';
                    wp_die($error_message, 403);
                } else {
                    die('Insufficient permissions');
                }
                return;
            }
            
            // 妫€鏌once瀹夊叏楠岃瘉
            $security_valid = true;
            if (function_exists('check_ajax_referer')) {
                $security_param = isset($_POST['security']) ? $_POST['security'] : '';
                $security_valid = check_ajax_referer('wpca-clear-performance-data', 'security', false); // 涓嶈嚜鍔╠ie锛岀敱鎴戜滑鎺у埗閿欒澶勭悊
            }
            
            if (!$security_valid) {
                if (function_exists('wp_send_json_error')) {
                    $error_message = function_exists('__') ? __('Security check failed', 'wp-clean-admin') : 'Security check failed';
                    wp_send_json_error(array('message' => $error_message), 403);
                } elseif (function_exists('wp_die')) {
                    $error_message = function_exists('__') ? __('Security check failed', 'wp-clean-admin') : 'Security check failed';
                    wp_die($error_message, 403);
                } else {
                    die('Security check failed');
                }
                return;
            }
            
            // 清理性能数据
            $cleared = false;
            if (function_exists('update_option')) {
                $cleared = update_option('wpca_performance_log', array());
            }
            
            if ($cleared) {
                // 返回成功消息
                $success_message = function_exists('__') ? __('Performance data has been cleared.', 'wp-clean-admin') : 'Performance data has been cleared.';
                
                if (function_exists('wp_send_json_success')) {
                    wp_send_json_success(array('message' => $success_message));
                } else {
                    echo json_encode(array('success' => true, 'message' => $success_message));
                }
            } else {
                throw new Exception(function_exists('__') ? __('Failed to clear performance data.', 'wp-clean-admin') : 'Failed to clear performance data.');
            }
        } catch (Exception $e) {
            // 处理异常
            if (function_exists('wp_send_json_error')) {
                wp_send_json_error(array(
                    'message' => function_exists('__') ? __('Failed to clear performance data.', 'wp-clean-admin') : 'Failed to clear performance data.'
                ));
            }
        }
        
        // 安全地调用wp_die或die
        if (function_exists('wp_die')) {
            wp_die();
        } else {
            die();
        }
    }
}
?>