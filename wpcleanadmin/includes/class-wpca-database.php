<?php
/**
 * WP Clean Admin Database Class
 *
 * Provides database optimization and cleanup functionality for WordPress admin,
 * including table optimization, orphaned data removal, and query optimization.
 *
 * @package WP_Clean_Admin
 * @since 1.6.0
 * @version 1.7.11
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * 数据库优化类
 * 提供数据库表优化、垃圾数据清理和查询优化功能
 */
class WPCA_Database {
    /**
     * 数据库对象
     * @var wpdb
     */
    private $db;
    
    /**
     * 插件设置
     * @var array
     */
    private $options;
    
    /**
     * WordPress表前缀
     * @var string
     */
    private $table_prefix;
    
    /**
     * 优化的表列表
     * @var array
     */
    private $tables_to_optimize = array();
    
    /**
     * 清理项目配置
     * @var array
     */
    private $cleanup_items = array();

    /**
     * WPCA_Database constructor.
     * Initialize database optimization functionality and register necessary hooks
     */
    public function __construct() {
        global $wpdb;
        
        // Check if $wpdb is available
        if (isset($wpdb) && is_object($wpdb)) {
            $this->db = $wpdb;
            $this->table_prefix = isset($wpdb->prefix) ? $wpdb->prefix : '';
        } else {
            $this->db = null;
            $this->table_prefix = '';
        }
        
        // Get plugin settings
        if (class_exists('WPCA_Settings') && method_exists('WPCA_Settings', 'get_options')) {
            $this->options = WPCA_Settings::get_options();
        } else {
            $this->options = array();
        }
        
        // Initialize configuration
        if (method_exists($this, 'initialize_config')) {
            $this->initialize_config();
        }
        
        // Register database optimization hooks
        if (method_exists($this, 'register_hooks')) {
            $this->register_hooks();
        }
    }

    /**
     * 初始化数据库优化配置
     */
    private function initialize_config() {
        // 设置需要优化的默认表
        $this->tables_to_optimize = array(
            "{$this->table_prefix}posts",
            "{$this->table_prefix}postmeta",
            "{$this->table_prefix}comments",
            "{$this->table_prefix}commentmeta",
            "{$this->table_prefix}links",
            "{$this->table_prefix}term_relationships",
            "{$this->table_prefix}term_taxonomy",
            "{$this->table_prefix}terms",
            "{$this->table_prefix}options",
            "{$this->table_prefix}users",
            "{$this->table_prefix}usermeta"
        );
        
        // 从设置中加载自定义表列表
        if (isset($this->options['tables_to_optimize']) && is_array($this->options['tables_to_optimize'])) {
            $custom_tables = array_filter($this->options['tables_to_optimize']);
            if (!empty($custom_tables)) {
                $this->tables_to_optimize = array_unique(array_merge($this->tables_to_optimize, $custom_tables));
            }
        }
        
        // Initialize cleanup items configuration
        $this->cleanup_items = array(
            'revision_posts' => array(
                'enabled' => isset($this->options['cleanup_revisions']) && $this->options['cleanup_revisions'],
                'days' => (function_exists('absint') ? absint($this->options['revision_days'] ?? 30) : intval($this->options['revision_days'] ?? 30)),
                'description' => function_exists('__') ? __('Remove old post revisions', 'wp-clean-admin') : 'Remove old post revisions'
            ),
            'auto_drafts' => array(
                'enabled' => isset($this->options['cleanup_auto_drafts']) && $this->options['cleanup_auto_drafts'],
                'description' => function_exists('__') ? __('Remove auto-saved drafts', 'wp-clean-admin') : 'Remove auto-saved drafts'
            ),
            'trashed_posts' => array(
                'enabled' => isset($this->options['cleanup_trashed_posts']) && $this->options['cleanup_trashed_posts'],
                'days' => (function_exists('absint') ? absint($this->options['trashed_days'] ?? 30) : intval($this->options['trashed_days'] ?? 30)),
                'description' => function_exists('__') ? __('Remove trashed posts', 'wp-clean-admin') : 'Remove trashed posts'
            ),
            'spam_comments' => array(
                'enabled' => isset($this->options['cleanup_spam_comments']) && $this->options['cleanup_spam_comments'],
                'days' => (function_exists('absint') ? absint($this->options['spam_days'] ?? 7) : intval($this->options['spam_days'] ?? 7)),
                'description' => function_exists('__') ? __('Remove spam comments', 'wp-clean-admin') : 'Remove spam comments'
            ),
            'trashed_comments' => array(
                'enabled' => isset($this->options['cleanup_trashed_comments']) && $this->options['cleanup_trashed_comments'],
                'days' => (function_exists('absint') ? absint($this->options['trashed_comments_days'] ?? 30) : intval($this->options['trashed_comments_days'] ?? 30)),
                'description' => function_exists('__') ? __('Remove trashed comments', 'wp-clean-admin') : 'Remove trashed comments'
            ),
            'pingbacks_trackbacks' => array(
                'enabled' => isset($this->options['cleanup_pingbacks_trackbacks']) && $this->options['cleanup_pingbacks_trackbacks'],
                'description' => function_exists('__') ? __('Remove pingbacks and trackbacks', 'wp-clean-admin') : 'Remove pingbacks and trackbacks'
            ),
            'orphaned_postmeta' => array(
                'enabled' => isset($this->options['cleanup_orphaned_postmeta']) && $this->options['cleanup_orphaned_postmeta'],
                'description' => function_exists('__') ? __('Remove orphaned postmeta entries', 'wp-clean-admin') : 'Remove orphaned postmeta entries'
            ),
            'orphaned_commentmeta' => array(
                'enabled' => isset($this->options['cleanup_orphaned_commentmeta']) && $this->options['cleanup_orphaned_commentmeta'],
                'description' => function_exists('__') ? __('Remove orphaned commentmeta entries', 'wp-clean-admin') : 'Remove orphaned commentmeta entries'
            ),
            'orphaned_relationships' => array(
                'enabled' => isset($this->options['cleanup_orphaned_relationships']) && $this->options['cleanup_orphaned_relationships'],
                'description' => function_exists('__') ? __('Remove orphaned term relationships', 'wp-clean-admin') : 'Remove orphaned term relationships'
            ),
            'orphaned_usermeta' => array(
                'enabled' => isset($this->options['cleanup_orphaned_usermeta']) && $this->options['cleanup_orphaned_usermeta'],
                'description' => function_exists('__') ? __('Remove orphaned usermeta entries', 'wp-clean-admin') : 'Remove orphaned usermeta entries'
            ),
            'expired_transients' => array(
                'enabled' => isset($this->options['cleanup_expired_transients']) && $this->options['cleanup_expired_transients'],
                'description' => function_exists('__') ? __('Remove expired transients', 'wp-clean-admin') : 'Remove expired transients'
            ),
            'all_transients' => array(
                'enabled' => isset($this->options['cleanup_all_transients']) && $this->options['cleanup_all_transients'],
                'description' => function_exists('__') ? __('Remove all transients (use with caution)', 'wp-clean-admin') : 'Remove all transients (use with caution)'
            ),
            'oembed_caches' => array(
                'enabled' => isset($this->options['cleanup_oembed_caches']) && $this->options['cleanup_oembed_caches'],
                'description' => function_exists('__') ? __('Remove oEmbed caches', 'wp-clean-admin') : 'Remove oEmbed caches'
            )
        );
    }

    /**
     * 注册数据库优化相关的钩子
     */
    private function register_hooks() {
        // 注册定期清理任务
        if (isset($this->options['enable_auto_cleanup']) && $this->options['enable_auto_cleanup'] && function_exists('add_action')) {
            add_action('wpca_database_cleanup', array($this, 'run_auto_cleanup'));
            
            // 注册定期清理的计划任务
            if (function_exists('wp_next_scheduled') && function_exists('wp_schedule_event') && function_exists('time')) {
                if (!wp_next_scheduled('wpca_database_cleanup')) {
                    // 获取清理间隔设置
                    $interval = isset($this->options['cleanup_interval']) ? $this->options['cleanup_interval'] : 'weekly';
                    wp_schedule_event(time(), $interval, 'wpca_database_cleanup');
                }
            }
        }
        
        // AJAX数据库优化操作
        if (function_exists('add_action')) {
            add_action('wp_ajax_wpca_optimize_tables', array($this, 'ajax_optimize_tables'));
            add_action('wp_ajax_wpca_cleanup_database', array($this, 'ajax_cleanup_database'));
            add_action('wp_ajax_wpca_get_database_info', array($this, 'ajax_get_database_info'));
            add_action('wp_ajax_wpca_get_cleanup_stats', array($this, 'ajax_get_cleanup_stats'));
        }
        
        // 插件激活时设置计划任务
        if (function_exists('register_activation_hook')) {
            if (defined('WPCA_MAIN_FILE')) { register_activation_hook(WPCA_MAIN_FILE, array($this, 'set_scheduled_cleanup')); }
        }
        
        // 插件停用时移除计划任务
        if (function_exists('register_deactivation_hook')) {
            if (defined('WPCA_MAIN_FILE')) { register_deactivation_hook(WPCA_MAIN_FILE, array($this, 'remove_scheduled_cleanup')); }
        }
    }

    /**
     * 设置定期清理计划任务
     */
    public function set_scheduled_cleanup() {
        // 移除可能存在的旧任务
        $this->remove_scheduled_cleanup();
        
        // 添加新的计划任务，默认为每周
        if (function_exists('wp_schedule_event') && function_exists('time')) {
            wp_schedule_event(time(), 'weekly', 'wpca_database_cleanup');
        }
    }

    /**
     * 移除定期清理计划任务
     */
    public function remove_scheduled_cleanup() {
        if (function_exists('wp_next_scheduled') && function_exists('wp_unschedule_event')) {
            $timestamp = wp_next_scheduled('wpca_database_cleanup');
            if ($timestamp) {
                wp_unschedule_event($timestamp, 'wpca_database_cleanup');
            }
        }
    }

    /**
     * Run automatic database cleanup
     */
    public function run_auto_cleanup() {
        if (method_exists($this, 'cleanup_database')) {
            $results = $this->cleanup_database($this->cleanup_items);
            
            // Log cleanup results
            if (isset($this->options['log_cleanup_results']) && $this->options['log_cleanup_results'] && method_exists($this, 'log_cleanup_results')) {
                $this->log_cleanup_results($results);
            }
            
            // Send cleanup notification if configured
            if (isset($this->options['notify_cleanup_complete']) && $this->options['notify_cleanup_complete'] && method_exists($this, 'send_cleanup_notification')) {
                $this->send_cleanup_notification($results);
            }
        }
    }

    /**
     * Optimize database tables
     * 
     * @param array $tables List of tables to optimize, defaults to all configured tables
     * @return array Optimization results
     */
    public function optimize_tables($tables = array()) {
        // Check if database object is available
        if (!isset($this->db) || !is_object($this->db)) {
            return array(
                'success' => 0,
                'failed' => 0,
                'total' => 0,
                'optimized_tables' => array(),
                'errors' => array(__('Database object not available', 'wp-clean-admin'))
            );
        }
        
        // If no tables specified, use configured table list
        if (empty($tables)) {
            $tables = $this->tables_to_optimize;
        }
        
        $results = array(
            'success' => 0,
            'failed' => 0,
            'total' => function_exists('count') ? count($tables) : 0,
            'optimized_tables' => array(),
            'errors' => array()
        );
        
        foreach ($tables as $table) {
            // Verify table name, ensure safety
            if (method_exists($this, 'sanitize_table_name')) {
                $safe_table = $this->sanitize_table_name($table);
            } else {
                $safe_table = $table; // Fallback if method doesn't exist
            }
            
            if (empty($safe_table)) {
                $results['errors'][] = function_exists('sprintf') && function_exists('__') ? sprintf(__('Invalid table name: %s', 'wp-clean-admin'), $table) : 'Invalid table name';
                $results['failed']++;
                continue;
            }
            
            // Check if table exists
            if (method_exists($this->db, 'get_var') && method_exists($this->db, 'prepare')) {
                if (!$this->db->get_var($this->db->prepare("SHOW TABLES LIKE %s", $safe_table))) {
                    $results['errors'][] = function_exists('sprintf') && function_exists('__') ? sprintf(__('Table does not exist: %s', 'wp-clean-admin'), $safe_table) : 'Table does not exist';
                    $results['failed']++;
                    continue;
                }
            } else {
                $results['errors'][] = __('Database methods not available', 'wp-clean-admin');
                $results['failed']++;
                continue;
            }
            
            try {
                // Optimize table
                if (method_exists($this->db, 'query')) {
                    $result = $this->db->query("OPTIMIZE TABLE {$safe_table}");
                    
                    if ($result !== false) {
                        $results['optimized_tables'][] = $safe_table;
                        $results['success']++;
                    } else {
                        $results['errors'][] = function_exists('sprintf') && function_exists('__') ? sprintf(__('Failed to optimize table: %s', 'wp-clean-admin'), $safe_table) : 'Failed to optimize table';
                        $results['failed']++;
                    }
                } else {
                    $results['errors'][] = function_exists('__') ? __('Database query method not available', 'wp-clean-admin') : 'Database query method not available';
                    $results['failed']++;
                }
            } catch (Exception $e) {
                $results['errors'][] = function_exists('sprintf') && function_exists('__') ? sprintf(__('Error optimizing table %s: %s', 'wp-clean-admin'), $safe_table, $e->getMessage()) : 'Error optimizing table';
                $results['failed']++;
            }
        }
        
        return $results;
    }

    /**
     * Clean up database garbage data
     * 
     * @param array $cleanup_items Cleanup items configuration
     * @return array Cleanup results
     */
    public function cleanup_database($cleanup_items = array()) {
        // Check if database object is available
        if (!isset($this->db) || !is_object($this->db) || !method_exists($this->db, 'query')) {
            return array(
                'success' => false,
                'removed' => 0,
                'details' => array(),
                'error' => function_exists('__') ? __('Database object not available', 'wp-clean-admin') : 'Database object not available'
            );
        }
        
        // If no cleanup items specified, use configured cleanup items
        if (empty($cleanup_items)) {
            $cleanup_items = $this->cleanup_items;
        }
        
        $results = array(
            'success' => true,
            'removed' => 0,
            'details' => array()
        );
        
        try {
            // Start transaction
            $this->db->query('START TRANSACTION');
            
            // Clean up old revisions
            if (isset($cleanup_items['revision_posts']['enabled']) && $cleanup_items['revision_posts']['enabled'] && method_exists($this, 'cleanup_old_revisions')) {
                $days = isset($cleanup_items['revision_posts']['days']) ? $cleanup_items['revision_posts']['days'] : 30;
                $removed = $this->cleanup_old_revisions($days);
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['revision_posts'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Clean up auto-drafts
            if (isset($cleanup_items['auto_drafts']['enabled']) && $cleanup_items['auto_drafts']['enabled'] && method_exists($this, 'cleanup_auto_drafts')) {
                $removed = $this->cleanup_auto_drafts();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['auto_drafts'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Clean up trashed posts
            if (isset($cleanup_items['trashed_posts']['enabled']) && $cleanup_items['trashed_posts']['enabled'] && method_exists($this, 'cleanup_trashed_posts')) {
                $days = isset($cleanup_items['trashed_posts']['days']) ? $cleanup_items['trashed_posts']['days'] : 30;
                $removed = $this->cleanup_trashed_posts($days);
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['trashed_posts'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Clean up spam comments
            if (isset($cleanup_items['spam_comments']['enabled']) && $cleanup_items['spam_comments']['enabled'] && method_exists($this, 'cleanup_spam_comments')) {
                $days = isset($cleanup_items['spam_comments']['days']) ? $cleanup_items['spam_comments']['days'] : 7;
                $removed = $this->cleanup_spam_comments($days);
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['spam_comments'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Clean up trashed comments
            if (isset($cleanup_items['trashed_comments']['enabled']) && $cleanup_items['trashed_comments']['enabled'] && method_exists($this, 'cleanup_trashed_comments')) {
                $days = isset($cleanup_items['trashed_comments']['days']) ? $cleanup_items['trashed_comments']['days'] : 30;
                $removed = $this->cleanup_trashed_comments($days);
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['trashed_comments'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Clean up pingbacks and trackbacks
            if (isset($cleanup_items['pingbacks_trackbacks']['enabled']) && $cleanup_items['pingbacks_trackbacks']['enabled'] && method_exists($this, 'cleanup_pingbacks_trackbacks')) {
                $removed = $this->cleanup_pingbacks_trackbacks();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['pingbacks_trackbacks'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Clean up orphaned postmeta
            if (isset($cleanup_items['orphaned_postmeta']['enabled']) && $cleanup_items['orphaned_postmeta']['enabled'] && method_exists($this, 'cleanup_orphaned_postmeta')) {
                $removed = $this->cleanup_orphaned_postmeta();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['orphaned_postmeta'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Clean up orphaned commentmeta
            if (isset($cleanup_items['orphaned_commentmeta']['enabled']) && $cleanup_items['orphaned_commentmeta']['enabled'] && method_exists($this, 'cleanup_orphaned_commentmeta')) {
                $removed = $this->cleanup_orphaned_commentmeta();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['orphaned_commentmeta'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Clean up orphaned term relationships
            if (isset($cleanup_items['orphaned_relationships']['enabled']) && $cleanup_items['orphaned_relationships']['enabled'] && method_exists($this, 'cleanup_orphaned_relationships')) {
                $removed = $this->cleanup_orphaned_relationships();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['orphaned_relationships'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Clean up orphaned usermeta
            if (isset($cleanup_items['orphaned_usermeta']['enabled']) && $cleanup_items['orphaned_usermeta']['enabled'] && method_exists($this, 'cleanup_orphaned_usermeta')) {
                $removed = $this->cleanup_orphaned_usermeta();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['orphaned_usermeta'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Clean up expired transients
            if (isset($cleanup_items['expired_transients']['enabled']) && $cleanup_items['expired_transients']['enabled'] && method_exists($this, 'cleanup_expired_transients')) {
                $removed = $this->cleanup_expired_transients();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['expired_transients'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Clean up all transients
            if (isset($cleanup_items['all_transients']['enabled']) && $cleanup_items['all_transients']['enabled'] && method_exists($this, 'cleanup_all_transients')) {
                $removed = $this->cleanup_all_transients();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['all_transients'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Clean up oEmbed caches
            if (isset($cleanup_items['oembed_caches']['enabled']) && $cleanup_items['oembed_caches']['enabled'] && method_exists($this, 'cleanup_oembed_caches')) {
                $removed = $this->cleanup_oembed_caches();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['oembed_caches'] = is_numeric($removed) ? $removed : 0;
            }
            
            // Commit transaction
            $this->db->query('COMMIT');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->query('ROLLBACK');
            $results['success'] = false;
            $results['error'] = function_exists('sprintf') && function_exists('__') ? sprintf(__('Database cleanup error: %s', 'wp-clean-admin'), $e->getMessage()) : 'Database cleanup error';
        }
        
        return $results;
    }