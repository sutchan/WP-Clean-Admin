<?php
/**
 * WP Clean Admin 数据库管理类
 *
 * 提供数据库优化与清理能力，包括表优化、孤儿数据清理、临时数据清理与统计。
 *
 * @package WP_Clean_Admin
 * @since 1.6.0
 * @version 1.7.13
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WPCA_Database
 * 数据库优化与清理的核心实现
 */
class WPCA_Database {
    /**
     * 单例实例
     * @var WPCA_Database|null
     */
    protected static $instance = null;

    /**
     * WordPress 数据库对象
     * @var wpdb
     */
    private $db;

    /**
     * 插件设置
     * @var array
     */
    private $options = array();

    /**
     * WordPress 表前缀
     * @var string
     */
    private $table_prefix = '';

    /**
     * 需要优化的表列表
     * @var array
     */
    private $tables_to_optimize = array();

    /**
     * 清理项目配置
     * @var array
     */
    private $cleanup_items = array();

    /**
     * 构造函数
     * 初始化数据库优化功能并注册必要钩子
     */
    public function __construct() {
        global $wpdb;

        if (isset($wpdb) && is_object($wpdb)) {
            $this->db = $wpdb;
            $this->table_prefix = isset($wpdb->prefix) ? $wpdb->prefix : '';
        }

        if (class_exists('WPCA_Settings') && method_exists('WPCA_Settings', 'get_options')) {
            $this->options = WPCA_Settings::get_options();
        }

        $this->initialize_config();
        $this->register_hooks();
    }

    /**
     * 获取单例实例
     * @return WPCA_Database
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 初始化数据库优化与清理的配置
     */
    private function initialize_config() {
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

        if (isset($this->options['tables_to_optimize']) && is_array($this->options['tables_to_optimize'])) {
            $custom_tables = array_filter($this->options['tables_to_optimize']);
            if (!empty($custom_tables)) {
                $this->tables_to_optimize = array_unique(array_merge($this->tables_to_optimize, $custom_tables));
            }
        }

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
     * 注册数据库优化与清理相关钩子
     */
    private function register_hooks() {
        if (isset($this->options['enable_auto_cleanup']) && $this->options['enable_auto_cleanup'] && function_exists('add_action')) {
            add_action('wpca_database_cleanup', array($this, 'run_auto_cleanup'));
            if (function_exists('wp_next_scheduled') && function_exists('wp_schedule_event') && function_exists('time')) {
                if (!wp_next_scheduled('wpca_database_cleanup')) {
                    $interval = isset($this->options['cleanup_interval']) ? $this->options['cleanup_interval'] : 'weekly';
                    wp_schedule_event(time(), $interval, 'wpca_database_cleanup');
                }
            }
        }

        if (function_exists('add_action')) {
            add_action('wp_ajax_wpca_optimize_tables', array($this, 'ajax_optimize_tables'));
            add_action('wp_ajax_wpca_cleanup_database', array($this, 'ajax_cleanup_database'));
            add_action('wp_ajax_wpca_get_database_info', array($this, 'ajax_get_database_info'));
            add_action('wp_ajax_wpca_get_cleanup_stats', array($this, 'ajax_get_cleanup_stats'));
        }

        if (function_exists('register_activation_hook')) {
            if (defined('WPCA_MAIN_FILE')) { register_activation_hook(WPCA_MAIN_FILE, array($this, 'set_scheduled_cleanup')); }
        }
        if (function_exists('register_deactivation_hook')) {
            if (defined('WPCA_MAIN_FILE')) { register_deactivation_hook(WPCA_MAIN_FILE, array($this, 'remove_scheduled_cleanup')); }
        }
    }

    /**
     * 设置计划任务：定期清理数据库
     */
    public function set_scheduled_cleanup() {
        $this->remove_scheduled_cleanup();
        if (function_exists('wp_schedule_event') && function_exists('time')) {
            wp_schedule_event(time(), 'weekly', 'wpca_database_cleanup');
        }
    }

    /**
     * 移除计划任务：数据库清理
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
        if (isset($this->options['log_cleanup_results']) && $this->options['log_cleanup_results'] && method_exists($this, 'log_cleanup_results')) {
            $this->log_cleanup_results($results);
        }
        if (isset($this->options['notify_cleanup_complete']) && $this->options['notify_cleanup_complete'] && method_exists($this, 'send_cleanup_notification')) {
            $this->send_cleanup_notification($results);
        }
    }

    /**
     * 优化数据库表
     *
     * @param array $tables 指定优化的表，不传则使用默认列表
     * @return array 优化结果
     */
    public function optimize_tables($tables = array()) {
        if (!isset($this->db) || !is_object($this->db)) {
            return array(
                'success' => 0,
                'failed' => 0,
                'total' => 0,
                'optimized_tables' => array(),
                'errors' => array(__('Database object not available', 'wp-clean-admin'))
            );
        }

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
            $safe_table = $this->sanitize_table_name($table);
            if (empty($safe_table)) {
                $results['errors'][] = function_exists('sprintf') && function_exists('__') ? sprintf(__('Invalid table name: %s', 'wp-clean-admin'), $table) : 'Invalid table name';
                $results['failed']++;
                continue;
            }

            if (method_exists($this->db, 'get_var') && method_exists($this->db, 'prepare')) {
                $exists = $this->db->get_var($this->db->prepare('SHOW TABLES LIKE %s', $safe_table));
                if (!$exists) {
                    $results['errors'][] = function_exists('sprintf') && function_exists('__') ? sprintf(__('Table does not exist: %s', 'wp-clean-admin'), $safe_table) : 'Table does not exist';
                    $results['failed']++;
                    continue;
                }
            }

            try {
                $result = $this->db->query("OPTIMIZE TABLE `{$safe_table}`");
                if ($result !== false) {
                    $results['optimized_tables'][] = $safe_table;
                    $results['success']++;
                } else {
                    $results['errors'][] = function_exists('sprintf') && function_exists('__') ? sprintf(__('Failed to optimize table: %s', 'wp-clean-admin'), $safe_table) : 'Failed to optimize table';
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
     * 清理数据库垃圾数据
     *
     * @param array $cleanup_items 清理项目配置
     * @return array 清理结果
     */
    public function cleanup_database($cleanup_items = array()) {
        if (!isset($this->db) || !is_object($this->db) || !method_exists($this->db, 'query')) {
            return array(
                'success' => false,
                'removed' => 0,
                'details' => array(),
                'error' => function_exists('__') ? __('Database object not available', 'wp-clean-admin') : 'Database object not available'
            );
        }

        if (empty($cleanup_items)) {
            $cleanup_items = $this->cleanup_items;
        }

        $results = array(
            'success' => true,
            'removed' => 0,
            'details' => array()
        );

        try {
            $this->db->query('START TRANSACTION');

            if (isset($cleanup_items['revision_posts']['enabled']) && $cleanup_items['revision_posts']['enabled']) {
                $days = isset($cleanup_items['revision_posts']['days']) ? $cleanup_items['revision_posts']['days'] : 30;
                $removed = $this->cleanup_old_revisions($days);
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['revision_posts'] = is_numeric($removed) ? $removed : 0;
            }

            if (isset($cleanup_items['auto_drafts']['enabled']) && $cleanup_items['auto_drafts']['enabled']) {
                $removed = $this->cleanup_auto_drafts();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['auto_drafts'] = is_numeric($removed) ? $removed : 0;
            }

            if (isset($cleanup_items['trashed_posts']['enabled']) && $cleanup_items['trashed_posts']['enabled']) {
                $days = isset($cleanup_items['trashed_posts']['days']) ? $cleanup_items['trashed_posts']['days'] : 30;
                $removed = $this->cleanup_trashed_posts($days);
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['trashed_posts'] = is_numeric($removed) ? $removed : 0;
            }

            if (isset($cleanup_items['spam_comments']['enabled']) && $cleanup_items['spam_comments']['enabled']) {
                $days = isset($cleanup_items['spam_comments']['days']) ? $cleanup_items['spam_comments']['days'] : 7;
                $removed = $this->cleanup_spam_comments($days);
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['spam_comments'] = is_numeric($removed) ? $removed : 0;
            }

            if (isset($cleanup_items['trashed_comments']['enabled']) && $cleanup_items['trashed_comments']['enabled']) {
                $days = isset($cleanup_items['trashed_comments']['days']) ? $cleanup_items['trashed_comments']['days'] : 30;
                $removed = $this->cleanup_trashed_comments($days);
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['trashed_comments'] = is_numeric($removed) ? $removed : 0;
            }

            if (isset($cleanup_items['pingbacks_trackbacks']['enabled']) && $cleanup_items['pingbacks_trackbacks']['enabled']) {
                $removed = $this->cleanup_pingbacks_trackbacks();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['pingbacks_trackbacks'] = is_numeric($removed) ? $removed : 0;
            }

            if (isset($cleanup_items['orphaned_postmeta']['enabled']) && $cleanup_items['orphaned_postmeta']['enabled']) {
                $removed = $this->cleanup_orphaned_postmeta();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['orphaned_postmeta'] = is_numeric($removed) ? $removed : 0;
            }

            if (isset($cleanup_items['orphaned_commentmeta']['enabled']) && $cleanup_items['orphaned_commentmeta']['enabled']) {
                $removed = $this->cleanup_orphaned_commentmeta();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['orphaned_commentmeta'] = is_numeric($removed) ? $removed : 0;
            }

            if (isset($cleanup_items['orphaned_relationships']['enabled']) && $cleanup_items['orphaned_relationships']['enabled']) {
                $removed = $this->cleanup_orphaned_relationships();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['orphaned_relationships'] = is_numeric($removed) ? $removed : 0;
            }

            if (isset($cleanup_items['orphaned_usermeta']['enabled']) && $cleanup_items['orphaned_usermeta']['enabled']) {
                $removed = $this->cleanup_orphaned_usermeta();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['orphaned_usermeta'] = is_numeric($removed) ? $removed : 0;
            }

            if (isset($cleanup_items['expired_transients']['enabled']) && $cleanup_items['expired_transients']['enabled']) {
                $removed = $this->cleanup_expired_transients();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['expired_transients'] = is_numeric($removed) ? $removed : 0;
            }

            if (isset($cleanup_items['all_transients']['enabled']) && $cleanup_items['all_transients']['enabled']) {
                $removed = $this->cleanup_all_transients();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['all_transients'] = is_numeric($removed) ? $removed : 0;
            }

            if (isset($cleanup_items['oembed_caches']['enabled']) && $cleanup_items['oembed_caches']['enabled']) {
                $removed = $this->cleanup_oembed_caches();
                $results['removed'] += is_numeric($removed) ? $removed : 0;
                $results['details']['oembed_caches'] = is_numeric($removed) ? $removed : 0;
            }

            $this->db->query('COMMIT');
        } catch (Exception $e) {
            $this->db->query('ROLLBACK');
            $results['success'] = false;
            $results['error'] = function_exists('sprintf') && function_exists('__') ? sprintf(__('Database cleanup error: %s', 'wp-clean-admin'), $e->getMessage()) : 'Database cleanup error';
        }

        return $results;
    }

    /**
     * 安全清理：旧修订版本
     * @param int $days 天数阈值
     * @return int 清理行数
     */
    private function cleanup_old_revisions($days) {
        $days = function_exists('absint') ? absint($days) : intval($days);
        $posts = $this->table_prefix . 'posts';
        $sql = $this->db->prepare("DELETE FROM `{$posts}` WHERE post_type='revision' AND post_modified < DATE_SUB(NOW(), INTERVAL %d DAY)", $days);
        $this->db->query($sql);
        return isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
    }

    /**
     * 清理自动草稿
     * @return int 清理行数
     */
    private function cleanup_auto_drafts() {
        $posts = $this->table_prefix . 'posts';
        $sql = "DELETE FROM `{$posts}` WHERE post_status='auto-draft'";
        $this->db->query($sql);
        return isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
    }

    /**
     * 清理回收站文章
     * @param int $days 天数阈值
     * @return int 清理行数
     */
    private function cleanup_trashed_posts($days) {
        $days = function_exists('absint') ? absint($days) : intval($days);
        $posts = $this->table_prefix . 'posts';
        $sql = $this->db->prepare("DELETE FROM `{$posts}` WHERE post_status='trash' AND post_modified < DATE_SUB(NOW(), INTERVAL %d DAY)", $days);
        $this->db->query($sql);
        return isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
    }

    /**
     * 清理垃圾评论
     * @param int $days 天数阈值
     * @return int 清理行数
     */
    private function cleanup_spam_comments($days) {
        $days = function_exists('absint') ? absint($days) : intval($days);
        $comments = $this->table_prefix . 'comments';
        $sql = $this->db->prepare("DELETE FROM `{$comments}` WHERE comment_approved='spam' AND comment_date < DATE_SUB(NOW(), INTERVAL %d DAY)", $days);
        $this->db->query($sql);
        return isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
    }

    /**
     * 清理回收站评论
     * @param int $days 天数阈值
     * @return int 清理行数
     */
    private function cleanup_trashed_comments($days) {
        $days = function_exists('absint') ? absint($days) : intval($days);
        $comments = $this->table_prefix . 'comments';
        $sql = $this->db->prepare("DELETE FROM `{$comments}` WHERE comment_approved='trash' AND comment_date < DATE_SUB(NOW(), INTERVAL %d DAY)", $days);
        $this->db->query($sql);
        return isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
    }

    /**
     * 清理 pingbacks/trackbacks
     * @return int 清理行数
     */
    private function cleanup_pingbacks_trackbacks() {
        $comments = $this->table_prefix . 'comments';
        $sql = "DELETE FROM `{$comments}` WHERE comment_type IN ('pingback','trackback')";
        $this->db->query($sql);
        return isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
    }

    /**
     * 清理孤儿 postmeta
     * @return int 清理行数
     */
    private function cleanup_orphaned_postmeta() {
        $postmeta = $this->table_prefix . 'postmeta';
        $posts = $this->table_prefix . 'posts';
        $sql = "DELETE pm FROM `{$postmeta}` pm LEFT JOIN `{$posts}` p ON pm.post_id = p.ID WHERE p.ID IS NULL";
        $this->db->query($sql);
        return isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
    }

    /**
     * 清理孤儿 commentmeta
     * @return int 清理行数
     */
    private function cleanup_orphaned_commentmeta() {
        $commentmeta = $this->table_prefix . 'commentmeta';
        $comments = $this->table_prefix . 'comments';
        $sql = "DELETE cm FROM `{$commentmeta}` cm LEFT JOIN `{$comments}` c ON cm.comment_id = c.comment_ID WHERE c.comment_ID IS NULL";
        $this->db->query($sql);
        return isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
    }

    /**
     * 清理孤儿 term_relationships
     * @return int 清理行数
     */
    private function cleanup_orphaned_relationships() {
        $relationships = $this->table_prefix . 'term_relationships';
        $posts = $this->table_prefix . 'posts';
        $sql = "DELETE tr FROM `{$relationships}` tr LEFT JOIN `{$posts}` p ON tr.object_id = p.ID WHERE p.ID IS NULL";
        $this->db->query($sql);
        return isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
    }

    /**
     * 清理孤儿 usermeta
     * @return int 清理行数
     */
    private function cleanup_orphaned_usermeta() {
        $usermeta = $this->table_prefix . 'usermeta';
        $users = $this->table_prefix . 'users';
        $sql = "DELETE um FROM `{$usermeta}` um LEFT JOIN `{$users}` u ON um.user_id = u.ID WHERE u.ID IS NULL";
        $this->db->query($sql);
        return isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
    }

    /**
     * 清理过期 transients
     * @return int 清理数量（近似）
     */
    private function cleanup_expired_transients() {
        if (function_exists('delete_expired_transients')) {
            delete_expired_transients();
            return 1;
        }

        $options = $this->table_prefix . 'options';
        $expired = $this->db->get_results("SELECT option_name, option_value FROM `{$options}` WHERE option_name LIKE '_transient_timeout_%'");
        $count = 0;
        $now = time();
        if (is_array($expired)) {
            foreach ($expired as $row) {
                $timeout = intval($row->option_value);
                if ($timeout > 0 && $timeout < $now) {
                    $key = substr($row->option_name, strlen('_transient_timeout_'));
                    $this->db->query($this->db->prepare("DELETE FROM `{$options}` WHERE option_name IN (%s, %s)", '_transient_' . $key, '_transient_timeout_' . $key));
                    $count += isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
                }
            }
        }
        return $count;
    }

    /**
     * 清理全部 transients（谨慎使用）
     * @return int 清理行数
     */
    private function cleanup_all_transients() {
        $options = $this->table_prefix . 'options';
        $sql = "DELETE FROM `{$options}` WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'";
        $this->db->query($sql);
        return isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
    }

    /**
     * 清理 oEmbed 缓存
     * @return int 清理行数
     */
    private function cleanup_oembed_caches() {
        $postmeta = $this->table_prefix . 'postmeta';
        $sql = "DELETE FROM `{$postmeta}` WHERE meta_key LIKE '_oembed_%'";
        $this->db->query($sql);
        return isset($this->db->rows_affected) ? (int)$this->db->rows_affected : 0;
    }

    /**
     * AJAX：优化数据表
     */
    public function ajax_optimize_tables() {
        if (!function_exists('wp_send_json_error') || !function_exists('wp_send_json_success')) { return; }
        if (function_exists('wp_doing_ajax') && !wp_doing_ajax()) { wp_send_json_error(array('message' => __('Invalid request', 'wp-clean-admin')), 400); return; }
        if (!isset($_POST['nonce']) || (function_exists('wp_verify_nonce') && !wp_verify_nonce($_POST['nonce'], 'wpca_ajax_request'))) { wp_send_json_error(array('message' => __('Security verification failed', 'wp-clean-admin')), 403); return; }
        if (!function_exists('current_user_can') || !current_user_can('manage_options')) { wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')), 403); return; }

        $tables = array();
        if (isset($_POST['tables']) && is_array($_POST['tables'])) {
            foreach ($_POST['tables'] as $t) {
                if (is_string($t)) { $tables[] = $t; }
            }
        }
        $r = $this->optimize_tables($tables);
        $payload = array(
            'successful_tables' => intval($r['success'] ?? 0),
            'failed_tables' => intval($r['failed'] ?? 0),
            'total_tables' => intval($r['total'] ?? 0),
            'optimized_tables' => $r['optimized_tables'] ?? array(),
            'errors' => $r['errors'] ?? array()
        );
        wp_send_json_success($payload);
    }

    /**
     * AJAX：执行数据库清理
     */
    public function ajax_cleanup_database() {
        if (!function_exists('wp_send_json_error') || !function_exists('wp_send_json_success')) { return; }
        if (function_exists('wp_doing_ajax') && !wp_doing_ajax()) { wp_send_json_error(array('message' => __('Invalid request', 'wp-clean-admin')), 400); return; }
        if (!isset($_POST['nonce']) || (function_exists('wp_verify_nonce') && !wp_verify_nonce($_POST['nonce'], 'wpca_ajax_request'))) { wp_send_json_error(array('message' => __('Security verification failed', 'wp-clean-admin')), 403); return; }
        if (!function_exists('current_user_can') || !current_user_can('manage_options')) { wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')), 403); return; }

        $items = $this->cleanup_items;
        $r = $this->cleanup_database($items);
        $payload = array(
            'total_items_removed' => intval($r['removed'] ?? 0),
            'detailed_results' => $r['details'] ?? array(),
            'success' => (bool)($r['success'] ?? false)
        );
        wp_send_json_success($payload);
    }

    /**
     * AJAX：获取数据库信息（表大小与行数概览）
     */
    public function ajax_get_database_info() {
        if (!function_exists('wp_send_json_error') || !function_exists('wp_send_json_success')) { return; }
        if (function_exists('wp_doing_ajax') && !wp_doing_ajax()) { wp_send_json_error(array('message' => __('Invalid request', 'wp-clean-admin')), 400); return; }
        if (!isset($_POST['nonce']) || (function_exists('wp_verify_nonce') && !wp_verify_nonce($_POST['nonce'], 'wpca_ajax_request'))) { wp_send_json_error(array('message' => __('Security verification failed', 'wp-clean-admin')), 403); return; }
        if (!function_exists('current_user_can') || !current_user_can('manage_options')) { wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')), 403); return; }

        $info = array();
        foreach ($this->tables_to_optimize as $table) {
            $safe = $this->sanitize_table_name($table);
            if (!$safe) { continue; }
            $status = $this->db->get_row($this->db->prepare('SHOW TABLE STATUS LIKE %s', $safe));
            if ($status) {
                $info[$safe] = array(
                    'rows' => intval($status->Rows ?? 0),
                    'size' => intval(($status->Data_length ?? 0) + ($status->Index_length ?? 0))
                );
            }
        }
        wp_send_json_success(array('tables' => $info));
    }

    /**
     * AJAX：获取清理统计（预估）
     */
    public function ajax_get_cleanup_stats() {
        if (!function_exists('wp_send_json_error') || !function_exists('wp_send_json_success')) { return; }
        if (function_exists('wp_doing_ajax') && !wp_doing_ajax()) { wp_send_json_error(array('message' => __('Invalid request', 'wp-clean-admin')), 400); return; }
        if (!isset($_POST['nonce']) || (function_exists('wp_verify_nonce') && !wp_verify_nonce($_POST['nonce'], 'wpca_ajax_request'))) { wp_send_json_error(array('message' => __('Security verification failed', 'wp-clean-admin')), 403); return; }
        if (!function_exists('current_user_can') || !current_user_can('manage_options')) { wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')), 403); return; }

        $prefix = $this->table_prefix;
        $stats = array();
        $days_rev = $this->cleanup_items['revision_posts']['days'] ?? 30;
        $days_trash_posts = $this->cleanup_items['trashed_posts']['days'] ?? 30;
        $days_spam = $this->cleanup_items['spam_comments']['days'] ?? 7;
        $days_trash_comments = $this->cleanup_items['trashed_comments']['days'] ?? 30;

        $stats['revision_posts'] = intval($this->db->get_var($this->db->prepare("SELECT COUNT(1) FROM `{$prefix}posts` WHERE post_type='revision' AND post_modified < DATE_SUB(NOW(), INTERVAL %d DAY)", $days_rev)));
        $stats['auto_drafts'] = intval($this->db->get_var("SELECT COUNT(1) FROM `{$prefix}posts` WHERE post_status='auto-draft'"));
        $stats['trashed_posts'] = intval($this->db->get_var($this->db->prepare("SELECT COUNT(1) FROM `{$prefix}posts` WHERE post_status='trash' AND post_modified < DATE_SUB(NOW(), INTERVAL %d DAY)", $days_trash_posts)));
        $stats['spam_comments'] = intval($this->db->get_var($this->db->prepare("SELECT COUNT(1) FROM `{$prefix}comments` WHERE comment_approved='spam' AND comment_date < DATE_SUB(NOW(), INTERVAL %d DAY)", $days_spam)));
        $stats['trashed_comments'] = intval($this->db->get_var($this->db->prepare("SELECT COUNT(1) FROM `{$prefix}comments` WHERE comment_approved='trash' AND comment_date < DATE_SUB(NOW(), INTERVAL %d DAY)", $days_trash_comments)));
        $stats['pingbacks_trackbacks'] = intval($this->db->get_var("SELECT COUNT(1) FROM `{$prefix}comments` WHERE comment_type IN ('pingback','trackback')"));
        $stats['orphaned_postmeta'] = intval($this->db->get_var("SELECT COUNT(1) FROM `{$prefix}postmeta` pm LEFT JOIN `{$prefix}posts` p ON pm.post_id = p.ID WHERE p.ID IS NULL"));
        $stats['orphaned_commentmeta'] = intval($this->db->get_var("SELECT COUNT(1) FROM `{$prefix}commentmeta` cm LEFT JOIN `{$prefix}comments` c ON cm.comment_id = c.comment_ID WHERE c.comment_ID IS NULL"));
        $stats['orphaned_relationships'] = intval($this->db->get_var("SELECT COUNT(1) FROM `{$prefix}term_relationships` tr LEFT JOIN `{$prefix}posts` p ON tr.object_id = p.ID WHERE p.ID IS NULL"));
        $stats['orphaned_usermeta'] = intval($this->db->get_var("SELECT COUNT(1) FROM `{$prefix}usermeta` um LEFT JOIN `{$prefix}users` u ON um.user_id = u.ID WHERE u.ID IS NULL"));
        $stats['oembed_caches'] = intval($this->db->get_var("SELECT COUNT(1) FROM `{$prefix}postmeta` WHERE meta_key LIKE '_oembed_%'"));

        wp_send_json_success(array('stats' => $stats));
    }

    /**
     * 表名安全处理（仅允许当前站点前缀与合法字符）
     * @param string $table 原始表名
     * @return string 安全表名或空字符串
     */
    private function sanitize_table_name($table) {
        if (!is_string($table) || $table === '') { return ''; }
        $pattern = '/^[a-zA-Z0-9_]+$/';
        if (!preg_match($pattern, $table)) { return ''; }
        if ($this->table_prefix && strpos($table, $this->table_prefix) !== 0) { return ''; }
        return $table;
    }
}

?>