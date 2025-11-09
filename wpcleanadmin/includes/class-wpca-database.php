<?php
/**
 * WP Clean Admin Database Class
 *
 * Provides database optimization and cleanup functionality for WordPress admin,
 * including table optimization, orphaned data removal, and query optimization.
 *
 * @package WP_Clean_Admin
 * @since 1.6.0
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
     * 初始化数据库优化功能，注册必要的钩子
     */
    public function __construct() {
        global $wpdb;
        
        $this->db = $wpdb;
        $this->table_prefix = $wpdb->prefix;
        
        // 获取插件设置
        if (class_exists('WPCA_Settings') && method_exists('WPCA_Settings', 'get_options')) {
            $this->options = WPCA_Settings::get_options();
        } else {
            $this->options = array();
        }
        
        // 初始化配置
        $this->initialize_config();
        
        // 注册数据库优化钩子
        $this->register_hooks();
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
        
        // 初始化清理项目配置
        $this->cleanup_items = array(
            'revision_posts' => array(
                'enabled' => isset($this->options['cleanup_revisions']) && $this->options['cleanup_revisions'],
                'days' => isset($this->options['revision_days']) ? absint($this->options['revision_days']) : 30,
                'description' => __('Remove old post revisions', 'wp-clean-admin')
            ),
            'auto_drafts' => array(
                'enabled' => isset($this->options['cleanup_auto_drafts']) && $this->options['cleanup_auto_drafts'],
                'description' => __('Remove auto-saved drafts', 'wp-clean-admin')
            ),
            'trashed_posts' => array(
                'enabled' => isset($this->options['cleanup_trashed_posts']) && $this->options['cleanup_trashed_posts'],
                'days' => isset($this->options['trashed_days']) ? absint($this->options['trashed_days']) : 30,
                'description' => __('Remove trashed posts', 'wp-clean-admin')
            ),
            'spam_comments' => array(
                'enabled' => isset($this->options['cleanup_spam_comments']) && $this->options['cleanup_spam_comments'],
                'days' => isset($this->options['spam_days']) ? absint($this->options['spam_days']) : 7,
                'description' => __('Remove spam comments', 'wp-clean-admin')
            ),
            'trashed_comments' => array(
                'enabled' => isset($this->options['cleanup_trashed_comments']) && $this->options['cleanup_trashed_comments'],
                'days' => isset($this->options['trashed_comments_days']) ? absint($this->options['trashed_comments_days']) : 30,
                'description' => __('Remove trashed comments', 'wp-clean-admin')
            ),
            'pingbacks_trackbacks' => array(
                'enabled' => isset($this->options['cleanup_pingbacks_trackbacks']) && $this->options['cleanup_pingbacks_trackbacks'],
                'description' => __('Remove pingbacks and trackbacks', 'wp-clean-admin')
            ),
            'orphaned_postmeta' => array(
                'enabled' => isset($this->options['cleanup_orphaned_postmeta']) && $this->options['cleanup_orphaned_postmeta'],
                'description' => __('Remove orphaned postmeta entries', 'wp-clean-admin')
            ),
            'orphaned_commentmeta' => array(
                'enabled' => isset($this->options['cleanup_orphaned_commentmeta']) && $this->options['cleanup_orphaned_commentmeta'],
                'description' => __('Remove orphaned commentmeta entries', 'wp-clean-admin')
            ),
            'orphaned_relationships' => array(
                'enabled' => isset($this->options['cleanup_orphaned_relationships']) && $this->options['cleanup_orphaned_relationships'],
                'description' => __('Remove orphaned term relationships', 'wp-clean-admin')
            ),
            'orphaned_usermeta' => array(
                'enabled' => isset($this->options['cleanup_orphaned_usermeta']) && $this->options['cleanup_orphaned_usermeta'],
                'description' => __('Remove orphaned usermeta entries', 'wp-clean-admin')
            ),
            'expired_transients' => array(
                'enabled' => isset($this->options['cleanup_expired_transients']) && $this->options['cleanup_expired_transients'],
                'description' => __('Remove expired transients', 'wp-clean-admin')
            ),
            'all_transients' => array(
                'enabled' => isset($this->options['cleanup_all_transients']) && $this->options['cleanup_all_transients'],
                'description' => __('Remove all transients (use with caution)', 'wp-clean-admin')
            ),
            'oembed_caches' => array(
                'enabled' => isset($this->options['cleanup_oembed_caches']) && $this->options['cleanup_oembed_caches'],
                'description' => __('Remove oEmbed caches', 'wp-clean-admin')
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
            register_activation_hook(WPCA_MAIN_FILE, array($this, 'set_scheduled_cleanup'));
        }
        
        // 插件停用时移除计划任务
        if (function_exists('register_deactivation_hook')) {
            register_deactivation_hook(WPCA_MAIN_FILE, array($this, 'remove_scheduled_cleanup'));
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
     * 运行自动数据库清理
     */
    public function run_auto_cleanup() {
        $results = $this->cleanup_database($this->cleanup_items);
        
        // 记录清理日志
        if (isset($this->options['log_cleanup_results']) && $this->options['log_cleanup_results']) {
            $this->log_cleanup_results($results);
        }
        
        // 如果配置了通知，发送清理完成通知
        if (isset($this->options['notify_cleanup_complete']) && $this->options['notify_cleanup_complete']) {
            $this->send_cleanup_notification($results);
        }
    }

    /**
     * 优化数据库表
     * 
     * @param array $tables 要优化的表列表，默认优化所有配置的表
     * @return array 优化结果
     */
    public function optimize_tables($tables = array()) {
        // 如果未指定表，使用配置的表列表
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
            // 验证表名，确保安全
            $safe_table = $this->sanitize_table_name($table);
            if (empty($safe_table)) {
                $results['errors'][] = sprintf(__('Invalid table name: %s', 'wp-clean-admin'), $table);
                $results['failed']++;
                continue;
            }
            
            // 检查表是否存在
            if (!$this->db->get_var($this->db->prepare("SHOW TABLES LIKE %s", $safe_table))) {
                $results['errors'][] = sprintf(__('Table does not exist: %s', 'wp-clean-admin'), $safe_table);
                $results['failed']++;
                continue;
            }
            
            try {
                // 优化表
                $result = $this->db->query("OPTIMIZE TABLE {$safe_table}");
                
                if ($result !== false) {
                    $results['optimized_tables'][] = $safe_table;
                    $results['success']++;
                } else {
                    $results['errors'][] = sprintf(__('Failed to optimize table: %s', 'wp-clean-admin'), $safe_table);
                    $results['failed']++;
                }
            } catch (Exception $e) {
                $results['errors'][] = sprintf(__('Error optimizing table %s: %s', 'wp-clean-admin'), $safe_table, $e->getMessage());
                $results['failed']++;
            }
        }
        
        return $results;
    }

    /**
     * 清理数据库垃圾数据
     * 
     * @param array $cleanup_items 清理项目配置
     * @return array 清理结果
     */
    public function cleanup_database($cleanup_items = array()) {
        // 如果未指定清理项目，使用配置的清理项目
        if (empty($cleanup_items)) {
            $cleanup_items = $this->cleanup_items;
        }
        
        $results = array(
            'success' => true,
            'removed' => 0,
            'details' => array()
        );
        
        // 开始事务
        $this->db->query('START TRANSACTION');
        
        try {
            // 清理旧版本
            if (isset($cleanup_items['revision_posts']['enabled']) && $cleanup_items['revision_posts']['enabled']) {
                $days = isset($cleanup_items['revision_posts']['days']) ? $cleanup_items['revision_posts']['days'] : 30;
                $removed = $this->cleanup_old_revisions($days);
                $results['removed'] += $removed;
                $results['details']['revision_posts'] = $removed;
            }
            
            // 清理自动保存草稿
            if (isset($cleanup_items['auto_drafts']['enabled']) && $cleanup_items['auto_drafts']['enabled']) {
                $removed = $this->cleanup_auto_drafts();
                $results['removed'] += $removed;
                $results['details']['auto_drafts'] = $removed;
            }
            
            // 清理已删除的文章
            if (isset($cleanup_items['trashed_posts']['enabled']) && $cleanup_items['trashed_posts']['enabled']) {
                $days = isset($cleanup_items['trashed_posts']['days']) ? $cleanup_items['trashed_posts']['days'] : 30;
                $removed = $this->cleanup_trashed_posts($days);
                $results['removed'] += $removed;
                $results['details']['trashed_posts'] = $removed;
            }
            
            // 清理垃圾评论
            if (isset($cleanup_items['spam_comments']['enabled']) && $cleanup_items['spam_comments']['enabled']) {
                $days = isset($cleanup_items['spam_comments']['days']) ? $cleanup_items['spam_comments']['days'] : 7;
                $removed = $this->cleanup_spam_comments($days);
                $results['removed'] += $removed;
                $results['details']['spam_comments'] = $removed;
            }
            
            // 清理已删除的评论
            if (isset($cleanup_items['trashed_comments']['enabled']) && $cleanup_items['trashed_comments']['enabled']) {
                $days = isset($cleanup_items['trashed_comments']['days']) ? $cleanup_items['trashed_comments']['days'] : 30;
                $removed = $this->cleanup_trashed_comments($days);
                $results['removed'] += $removed;
                $results['details']['trashed_comments'] = $removed;
            }
            
            // 清理pingback和trackback
            if (isset($cleanup_items['pingbacks_trackbacks']['enabled']) && $cleanup_items['pingbacks_trackbacks']['enabled']) {
                $removed = $this->cleanup_pingbacks_trackbacks();
                $results['removed'] += $removed;
                $results['details']['pingbacks_trackbacks'] = $removed;
            }
            
            // 清理孤立的postmeta
            if (isset($cleanup_items['orphaned_postmeta']['enabled']) && $cleanup_items['orphaned_postmeta']['enabled']) {
                $removed = $this->cleanup_orphaned_postmeta();
                $results['removed'] += $removed;
                $results['details']['orphaned_postmeta'] = $removed;
            }
            
            // 清理孤立的commentmeta
            if (isset($cleanup_items['orphaned_commentmeta']['enabled']) && $cleanup_items['orphaned_commentmeta']['enabled']) {
                $removed = $this->cleanup_orphaned_commentmeta();
                $results['removed'] += $removed;
                $results['details']['orphaned_commentmeta'] = $removed;
            }
            
            // 清理孤立的term relationships
            if (isset($cleanup_items['orphaned_relationships']['enabled']) && $cleanup_items['orphaned_relationships']['enabled']) {
                $removed = $this->cleanup_orphaned_relationships();
                $results['removed'] += $removed;
                $results['details']['orphaned_relationships'] = $removed;
            }
            
            // 清理孤立的usermeta
            if (isset($cleanup_items['orphaned_usermeta']['enabled']) && $cleanup_items['orphaned_usermeta']['enabled']) {
                $removed = $this->cleanup_orphaned_usermeta();
                $results['removed'] += $removed;
                $results['details']['orphaned_usermeta'] = $removed;
            }
            
            // 清理过期的transients
            if (isset($cleanup_items['expired_transients']['enabled']) && $cleanup_items['expired_transients']['enabled']) {
                $removed = $this->cleanup_expired_transients();
                $results['removed'] += $removed;
                $results['details']['expired_transients'] = $removed;
            }
            
            // 清理所有transients
            if (isset($cleanup_items['all_transients']['enabled']) && $cleanup_items['all_transients']['enabled']) {
                $removed = $this->cleanup_all_transients();
                $results['removed'] += $removed;
                $results['details']['all_transients'] = $removed;
            }
            
            // 清理oEmbed缓存
            if (isset($cleanup_items['oembed_caches']['enabled']) && $cleanup_items['oembed_caches']['enabled']) {
                $removed = $this->cleanup_oembed_caches();
                $results['removed'] += $removed;
                $results['details']['oembed_caches'] = $removed;
            }
            
            // 提交事务
            $this->db->query('COMMIT');
            
        } catch (Exception $e) {
            // 出错时回滚事务
            $this->db->query('ROLLBACK');
            $results['success'] = false;
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * 清理旧版本
     * 
     * @param int $days 天数
     * @return int 删除的记录数
     */
    private function cleanup_old_revisions($days = 30) {
        if (function_exists('date') && function_exists('strtotime')) {
            $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            $this->db->query($this->db->prepare(
                "DELETE p, pm FROM {$this->table_prefix}posts p 
                LEFT JOIN {$this->table_prefix}postmeta pm ON p.ID = pm.post_id 
                WHERE p.post_type = 'revision' AND p.post_modified < %s",
                $date
            ));
            
            return $this->db->rows_affected;
        }
        return 0;
    }

    /**
     * 清理自动保存草稿
     * 
     * @return int 删除的记录数
     */
    private function cleanup_auto_drafts() {
        if (function_exists('date') && function_exists('strtotime')) {
            $this->db->query($this->db->prepare(
                "DELETE p, pm FROM {$this->table_prefix}posts p 
                LEFT JOIN {$this->table_prefix}postmeta pm ON p.ID = pm.post_id 
                WHERE p.post_status = 'auto-draft' AND p.post_modified < %s",
                date('Y-m-d H:i:s', strtotime("-1 day"))
            ));
            
            return $this->db->rows_affected;
        }
        return 0;
    }

    /**
     * 清理已删除的文章
     * 
     * @param int $days 天数
     * @return int 删除的记录数
     */
    private function cleanup_trashed_posts($days = 30) {
        if (function_exists('date') && function_exists('strtotime')) {
            $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            $this->db->query($this->db->prepare(
                "DELETE p, pm FROM {$this->table_prefix}posts p 
                LEFT JOIN {$this->table_prefix}postmeta pm ON p.ID = pm.post_id 
                WHERE p.post_status = 'trash' AND p.post_modified < %s",
                $date
            ));
            
            return $this->db->rows_affected;
        }
        return 0;
    }

    /**
     * 清理垃圾评论
     * 
     * @param int $days 天数
     * @return int 删除的记录数
     */
    private function cleanup_spam_comments($days = 7) {
        if (function_exists('date') && function_exists('strtotime')) {
            $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            $this->db->query($this->db->prepare(
                "DELETE c, cm FROM {$this->table_prefix}comments c 
                LEFT JOIN {$this->table_prefix}commentmeta cm ON c.comment_ID = cm.comment_id 
                WHERE c.comment_approved = 'spam' AND c.comment_date < %s",
                $date
            ));
            
            return $this->db->rows_affected;
        }
        return 0;
    }

    /**
     * 清理已删除的评论
     * 
     * @param int $days 天数
     * @return int 删除的记录数
     */
    private function cleanup_trashed_comments($days = 30) {
        if (function_exists('date') && function_exists('strtotime')) {
            $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            $this->db->query($this->db->prepare(
                "DELETE c, cm FROM {$this->table_prefix}comments c 
                LEFT JOIN {$this->table_prefix}commentmeta cm ON c.comment_ID = cm.comment_id 
                WHERE c.comment_approved = 'trash' AND c.comment_date < %s",
                $date
            ));
            
            return $this->db->rows_affected;
        }
        return 0;
    }

    /**
     * 清理pingback和trackback
     * 
     * @return int 删除的记录数
     */
    private function cleanup_pingbacks_trackbacks() {
        $this->db->query(
            "DELETE c, cm FROM {$this->table_prefix}comments c 
            LEFT JOIN {$this->table_prefix}commentmeta cm ON c.comment_ID = cm.comment_id 
            WHERE c.comment_type IN ('pingback', 'trackback')"
        );
        
        return $this->db->rows_affected;
    }

    /**
     * 清理孤立的postmeta
     * 
     * @return int 删除的记录数
     */
    private function cleanup_orphaned_postmeta() {
        $this->db->query(
            "DELETE pm FROM {$this->table_prefix}postmeta pm 
            LEFT JOIN {$this->table_prefix}posts p ON pm.post_id = p.ID 
            WHERE p.ID IS NULL"
        );
        
        return $this->db->rows_affected;
    }

    /**
     * 清理孤立的commentmeta
     * 
     * @return int 删除的记录数
     */
    private function cleanup_orphaned_commentmeta() {
        $this->db->query(
            "DELETE cm FROM {$this->table_prefix}commentmeta cm 
            LEFT JOIN {$this->table_prefix}comments c ON cm.comment_id = c.comment_ID 
            WHERE c.comment_ID IS NULL"
        );
        
        return $this->db->rows_affected;
    }

    /**
     * 清理孤立的term relationships
     * 
     * @return int 删除的记录数
     */
    private function cleanup_orphaned_relationships() {
        // 清理没有对应post的关系
        $this->db->query(
            "DELETE tr FROM {$this->table_prefix}term_relationships tr 
            LEFT JOIN {$this->table_prefix}posts p ON tr.object_id = p.ID 
            WHERE p.ID IS NULL"
        );
        
        $removed_post_relationships = $this->db->rows_affected;
        
        // 清理没有对应term的关系
        $this->db->query(
            "DELETE tr FROM {$this->table_prefix}term_relationships tr 
            LEFT JOIN {$this->table_prefix}terms t ON tr.term_taxonomy_id = t.term_id 
            WHERE t.term_id IS NULL"
        );
        
        return $removed_post_relationships + $this->db->rows_affected;
    }

    /**
     * 清理孤立的usermeta
     * 
     * @return int 删除的记录数
     */
    private function cleanup_orphaned_usermeta() {
        $this->db->query(
            "DELETE um FROM {$this->table_prefix}usermeta um 
            LEFT JOIN {$this->table_prefix}users u ON um.user_id = u.ID 
            WHERE u.ID IS NULL"
        );
        
        return $this->db->rows_affected;
    }

    /**
     * 清理过期的transients
     * 
     * @return int 删除的记录数
     */
    private function cleanup_expired_transients() {
        // 清理所有过期的transients
        if (function_exists('time')) {
            $this->db->query(
                "DELETE FROM {$this->table_prefix}options 
                WHERE option_name LIKE '_transient_timeout_%' AND option_value < %d",
                time()
            );
        }

        $expired_count = $this->db->rows_affected;
        
        // 获取所有过期的transient超时键
        if (function_exists('time')) {
            $expired_transients = $this->db->get_col(
                "SELECT REPLACE(option_name, '_transient_timeout_', '_transient_') 
                FROM {$this->table_prefix}options 
                WHERE option_name LIKE '_transient_timeout_%' AND option_value < %d",
                time()
            );
            
            // 删除对应的transient值
            if (!empty($expired_transients) && function_exists('count') && function_exists('implode') && function_exists('array_fill')) {
                $placeholders = implode(',', array_fill(0, count($expired_transients), '%s'));
                $this->db->query(
                    "DELETE FROM {$this->table_prefix}options 
                    WHERE option_name IN ({$placeholders})",
                    $expired_transients
                );
            }
        }
        
        return $expired_count;
    }

    /**
     * 清理所有transients
     * 
     * @return int 删除的记录数
     */
    private function cleanup_all_transients() {
        // 清理所有transients
        $this->db->query(
            "DELETE FROM {$this->table_prefix}options 
            WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'"
        );
        
        return $this->db->rows_affected;
    }

    /**
     * 清理oEmbed缓存
     * 
     * @return int 删除的记录数
     */
    private function cleanup_oembed_caches() {
        $this->db->query(
            "DELETE FROM {$this->table_prefix}postmeta 
            WHERE meta_key LIKE '_oembed_%'"
        );
        
        return $this->db->rows_affected;
    }

    /**
     * 获取数据库信息
     * 
     * @return array 数据库信息
     */
    public function get_database_info() {
        $info = array();
        
        // 获取数据库服务器版本
        $info['version'] = $this->db->get_var("SELECT VERSION()");
        
        // 获取数据库大小
        $tables = $this->db->get_results("SHOW TABLES");
        $info['table_count'] = count($tables);
        $info['total_size'] = 0;
        $info['tables'] = array();
        
        foreach ($tables as $table) {
            $table_name = current((array)$table);
            $table_info = $this->db->get_row("SHOW TABLE STATUS LIKE '{$table_name}'");
            
            if ($table_info) {
                $table_size = $table_info->Data_length + $table_info->Index_length;
                $info['total_size'] += $table_size;
                
                $info['tables'][] = array(
                    'name' => $table_name,
                    'size' => $table_size,
                    'rows' => $table_info->Rows,
                    'engine' => $table_info->Engine,
                    'collation' => $table_info->Collation,
                    'data_free' => $table_info->Data_free
                );
            }
        }
        
        return $info;
    }

    /**
     * 获取清理统计信息
     * 
     * @return array 清理统计信息
     */
    public function get_cleanup_stats() {
        $stats = array();
        
        // 获取修订版本数量
        $stats['revisions_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}posts WHERE post_type = 'revision'");
        
        // 获取自动草稿数量
        $stats['auto_drafts_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}posts WHERE post_status = 'auto-draft'");
        
        // 获取回收站文章数量
        $stats['trashed_posts_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}posts WHERE post_status = 'trash'");
        
        // 获取垃圾评论数量
        $stats['spam_comments_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}comments WHERE comment_approved = 'spam'");
        
        // 获取回收站评论数量
        $stats['trashed_comments_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}comments WHERE comment_approved = 'trash'");
        
        // 获取pingback和trackback数量
        $stats['pingbacks_trackbacks_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}comments WHERE comment_type IN ('pingback', 'trackback')");
        
        // 获取过期transients数量
        $stats['expired_transients_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}options WHERE option_name LIKE '_transient_timeout_%' AND option_value < %d", time());
        
        // 获取所有transients数量
        $stats['all_transients_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}options WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'");
        
        // 获取oEmbed缓存数量
        $stats['oembed_caches_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}postmeta WHERE meta_key LIKE '_oembed_%'");
        
        // 获取孤立记录数量
        $stats['orphaned_postmeta_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}postmeta pm LEFT JOIN {$this->table_prefix}posts p ON pm.post_id = p.ID WHERE p.ID IS NULL");
        $stats['orphaned_commentmeta_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}commentmeta cm LEFT JOIN {$this->table_prefix}comments c ON cm.comment_id = c.comment_ID WHERE c.comment_ID IS NULL");
        $stats['orphaned_relationships_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}term_relationships tr LEFT JOIN {$this->table_prefix}posts p ON tr.object_id = p.ID WHERE p.ID IS NULL");
        $stats['orphaned_usermeta_count'] = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_prefix}usermeta um LEFT JOIN {$this->table_prefix}users u ON um.user_id = u.ID WHERE u.ID IS NULL");
        
        return $stats;
    }

    /**
     * 记录清理结果
     * 
     * @param array $results 清理结果
     */
    private function log_cleanup_results($results) {
        // 实现日志记录功能
        // 可以使用WordPress的错误日志或自定义日志文件
        if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
            $log_message = 'WP Clean Admin: Database cleanup completed. Removed ' . (isset($results['removed']) ? $results['removed'] : 0) . ' items.';
            error_log($log_message);
        }
    }

    /**
     * 发送清理完成通知
     * 
     * @param array $results 清理结果
     */
    private function send_cleanup_notification($results) {
        // 实现邮件通知功能
        if (function_exists('wp_mail')) {
            // 这里可以实现邮件通知功能
            // 但需要确保所有使用的函数都有存在性检查
        }
    }

    /**
     * 清理表名，确保安全
     * 
     * @param string $table 表名
     * @return string 清理后的表名
     */
    private function sanitize_table_name($table) {
        // 移除任何可能的SQL注入字符
        if (function_exists('preg_replace')) {
            $safe_table = preg_replace('/[^a-zA-Z0-9_`.-]/', '', $table);
            
            // 确保表名不以分号开始，防止SQL注入
            $safe_table = preg_replace('/^;/', '', $safe_table);
            
            return $safe_table;
        }
        return '';
    }

    /**
     * AJAX优化数据库表
     */
    /**
     * 处理数据库表优化的AJAX请求
     * 包含完整的安全验证和错误处理
     */
    public function ajax_optimize_tables() {
        // 安全函数检查
        $required_functions = array('wp_die', 'check_ajax_referer', 'wp_send_json_success', 'wp_send_json_error');
        foreach ($required_functions as $func) {
            if (!function_exists($func)) {
                if (function_exists('wp_die')) {
                    wp_die(__('Required functions not available', 'wp-clean-admin'), 500);
                }
                die(__('Required functions not available', 'wp-clean-admin'));
            }
        }
        
        // 检查是否为AJAX请求（兼容旧版本WordPress）
        if (function_exists('wp_doing_ajax')) {
            if (!wp_doing_ajax()) {
                wp_die(__('Invalid request', 'wp-clean-admin'), 400);
            }
        } else if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            wp_die(__('Invalid request', 'wp-clean-admin'), 400);
        }
        
        // 检查nonce参数存在性
        if (!isset($_POST['security'])) {
            wp_send_json_error(array('message' => __('Security verification failed', 'wp-clean-admin')), 403);
            wp_die();
        }
        
        // 检查nonce安全验证
        check_ajax_referer('wpca-settings-options', 'security');
        
        // 检查用户权限
        if (!class_exists('WPCA_Permissions') || !method_exists('WPCA_Permissions', 'current_user_can') || !WPCA_Permissions::current_user_can('wpca_manage_all')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')), 403);
            wp_die();
        }
        
        // 获取请求参数并严格验证
        $tables = array();
        if (isset($_POST['tables']) && is_array($_POST['tables'])) {
            // 清理和验证表名数组
            $tables = array_map('sanitize_text_field', $_POST['tables']);
        }
        
        try {
            // 优化表
            $results = $this->optimize_tables($tables);
            
            wp_send_json_success($results);
        } catch (Exception $e) {
            // 记录错误日志
            if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                error_log('WP Clean Admin: Database optimization error: ' . $e->getMessage());
            }
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'debug_info' => defined('WP_DEBUG') && WP_DEBUG ? $e->getMessage() : null
            ));
        } finally {
            wp_die();
        }
    }

    /**
     * AJAX清理数据库
     */
    /**
     * 处理数据库清理的AJAX请求
     * 包含完整的安全验证和错误处理
     */
    public function ajax_cleanup_database() {
        // 安全函数检查
        $required_functions = array('wp_die', 'check_ajax_referer', 'wp_send_json_success', 'wp_send_json_error');
        foreach ($required_functions as $func) {
            if (!function_exists($func)) {
                if (function_exists('wp_die')) {
                    wp_die(__('Required functions not available', 'wp-clean-admin'), 500);
                }
                die(__('Required functions not available', 'wp-clean-admin'));
            }
        }
        
        // 检查是否为AJAX请求（兼容旧版本WordPress）
        if (function_exists('wp_doing_ajax')) {
            if (!wp_doing_ajax()) {
                wp_die(__('Invalid request', 'wp-clean-admin'), 400);
            }
        } else if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            wp_die(__('Invalid request', 'wp-clean-admin'), 400);
        }
        
        // 检查nonce参数存在性
        if (!isset($_POST['security'])) {
            wp_send_json_error(array('message' => __('Security verification failed', 'wp-clean-admin')), 403);
            wp_die();
        }
        
        // 检查nonce安全验证
        check_ajax_referer('wpca-settings-options', 'security');
        
        // 检查用户权限
        if (!class_exists('WPCA_Permissions') || !method_exists('WPCA_Permissions', 'current_user_can') || !WPCA_Permissions::current_user_can('wpca_manage_all')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')), 403);
            wp_die();
        }
        
        // 获取请求参数并严格验证
        $cleanup_items = array();
        if (isset($_POST['cleanup_items']) && is_array($_POST['cleanup_items'])) {
            // 深度清理和验证清理项目配置
            foreach ($_POST['cleanup_items'] as $key => $item) {
                if (is_array($item)) {
                    $cleanup_items[sanitize_text_field($key)] = array();
                    foreach ($item as $subkey => $value) {
                        $cleanup_items[sanitize_text_field($key)][sanitize_text_field($subkey)] = is_bool($value) ? $value : sanitize_text_field($value);
                    }
                }
            }
        }
        
        try {
            // 清理数据库
            $results = $this->cleanup_database($cleanup_items);
            
            wp_send_json_success($results);
        } catch (Exception $e) {
            // 记录错误日志
            if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                error_log('WP Clean Admin: Database cleanup error: ' . $e->getMessage());
            }
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'debug_info' => defined('WP_DEBUG') && WP_DEBUG ? $e->getMessage() : null
            ));
        } finally {
            wp_die();
        }
    }

    /**
     * AJAX获取数据库信息
     */
    /**
     * 处理获取数据库信息的AJAX请求
     * 包含完整的安全验证和错误处理
     */
    public function ajax_get_database_info() {
        // 安全函数检查
        $required_functions = array('wp_die', 'check_ajax_referer', 'wp_send_json_success', 'wp_send_json_error');
        foreach ($required_functions as $func) {
            if (!function_exists($func)) {
                if (function_exists('wp_die')) {
                    wp_die(__('Required functions not available', 'wp-clean-admin'), 500);
                }
                die(__('Required functions not available', 'wp-clean-admin'));
            }
        }
        
        // 检查是否为AJAX请求（兼容旧版本WordPress）
        if (function_exists('wp_doing_ajax')) {
            if (!wp_doing_ajax()) {
                wp_die(__('Invalid request', 'wp-clean-admin'), 400);
            }
        } else if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            wp_die(__('Invalid request', 'wp-clean-admin'), 400);
        }
        
        // 检查nonce参数存在性
        if (!isset($_POST['security'])) {
            wp_send_json_error(array('message' => __('Security verification failed', 'wp-clean-admin')), 403);
            wp_die();
        }
        
        // 检查nonce安全验证
        check_ajax_referer('wpca-settings-options', 'security');
        
        // 检查用户权限
        if (!WPCA_Permissions::current_user_can('wpca_view_database_info')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')), 403);
            wp_die();
        }
        
        try {
            // 获取数据库信息
            $info = $this->get_database_info();
            
            // 确保返回的数据类型正确
            if (!is_array($info)) {
                $info = array();
            }
            
            wp_send_json_success($info);
        } catch (Exception $e) {
            // 记录错误日志
            if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                error_log('WP Clean Admin: Get database info error: ' . $e->getMessage());
            }
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'debug_info' => defined('WP_DEBUG') && WP_DEBUG ? $e->getMessage() : null
            ));
        } finally {
            wp_die();
        }
    }

    /**
     * AJAX获取清理统计信息
     */
    /**
     * 处理获取清理统计信息的AJAX请求
     * 包含完整的安全验证和错误处理
     */
    public function ajax_get_cleanup_stats() {
        // 安全函数检查
        $required_functions = array('wp_die', 'check_ajax_referer', 'wp_send_json_success', 'wp_send_json_error');
        foreach ($required_functions as $func) {
            if (!function_exists($func)) {
                if (function_exists('wp_die')) {
                    wp_die(__('Required functions not available', 'wp-clean-admin'), 500);
                }
                die(__('Required functions not available', 'wp-clean-admin'));
            }
        }
        
        // 检查是否为AJAX请求（兼容旧版本WordPress）
        if (function_exists('wp_doing_ajax')) {
            if (!wp_doing_ajax()) {
                wp_die(__('Invalid request', 'wp-clean-admin'), 400);
            }
        } else if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            wp_die(__('Invalid request', 'wp-clean-admin'), 400);
        }
        
        // 检查nonce参数存在性
        if (!isset($_POST['security'])) {
            wp_send_json_error(array('message' => __('Security verification failed', 'wp-clean-admin')), 403);
            wp_die();
        }
        
        // 检查nonce安全验证
        check_ajax_referer('wpca-settings-options', 'security');
        
        // 检查用户权限
        if (!WPCA_Permissions::current_user_can('wpca_view_database_info')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')), 403);
            wp_die();
        }
        
        try {
            // 获取清理统计信息
            $stats = $this->get_cleanup_stats();
            
            // 确保返回的数据类型正确
            if (!is_array($stats)) {
                $stats = array();
            }
            
            wp_send_json_success($stats);
        } catch (Exception $e) {
            // 记录错误日志
            if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                error_log('WP Clean Admin: Get cleanup stats error: ' . $e->getMessage());
            }
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'debug_info' => defined('WP_DEBUG') && WP_DEBUG ? $e->getMessage() : null
            ));
        } finally {
            wp_die();
        }
    }
}
?>