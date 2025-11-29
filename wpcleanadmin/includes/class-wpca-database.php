<?php

/**
 * Database optimization functionality for WP Clean Admin
 * 
 * @file wpcleanadmin/includes/class-wpca-database.php
 * @version 1.7.15
 * @updated 2025-11-29
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * WPCA_Database class
 * Handles database optimization and cleanup functionality
 */
class WPCA_Database {
    
    /**
     * Singleton instance
     * @var WPCA_Database|null
     */
    protected static $instance = null;
    
    /**
     * WordPress database object
     * @var wpdb
     */
    private $db;
    
    /**
     * WordPress table prefix
     * @var string
     */
    private $table_prefix = '';
    
    /**
     * Need to optimize tables
     * @var array
     */
    private $tables_to_optimize = array();
    
    /**
     * Cleanup items configuration
     * @var array
     */
    private $cleanup_items = array();
    
    /**
     * Constructor
     */
    private function __construct() {
        global $wpdb;
        
        $this->db = $wpdb;
        $this->table_prefix = $wpdb->prefix;
        
        $this->initialize_cleanup_items();
        $this->register_hooks();
    }
    
    /**
     * Get singleton instance
     * 
     * @return WPCA_Database Singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize cleanup items
     */
    private function initialize_cleanup_items() {
        $this->cleanup_items = array(
            'revision_posts' => array(
                'enabled' => true,
                'days' => 30,
                'description' => __('Clean up post revisions', 'wp-clean-admin')
            ),
            'auto_drafts' => array(
                'enabled' => true,
                'days' => 7,
                'description' => __('Clean up auto drafts', 'wp-clean-admin')
            ),
            'trashed_posts' => array(
                'enabled' => true,
                'days' => 30,
                'description' => __('Clean up trashed posts', 'wp-clean-admin')
            ),
            'spam_comments' => array(
                'enabled' => true,
                'days' => 7,
                'description' => __('Clean up spam comments', 'wp-clean-admin')
            ),
            'trash_comments' => array(
                'enabled' => true,
                'days' => 30,
                'description' => __('Clean up trashed comments', 'wp-clean-admin')
            ),
            'orphan_postmeta' => array(
                'enabled' => true,
                'days' => 0,
                'description' => __('Clean up orphan postmeta', 'wp-clean-admin')
            ),
            'orphan_commentmeta' => array(
                'enabled' => true,
                'days' => 0,
                'description' => __('Clean up orphan commentmeta', 'wp-clean-admin')
            ),
            'orphan_term_relationships' => array(
                'enabled' => true,
                'days' => 0,
                'description' => __('Clean up orphan term relationships', 'wp-clean-admin')
            ),
            'orphan_usermeta' => array(
                'enabled' => true,
                'days' => 0,
                'description' => __('Clean up orphan usermeta', 'wp-clean-admin')
            ),
            'expired_transients' => array(
                'enabled' => true,
                'days' => 0,
                'description' => __('Clean up expired transients', 'wp-clean-admin')
            ),
            'oembed_cache' => array(
                'enabled' => true,
                'days' => 30,
                'description' => __('Clean up oEmbed cache', 'wp-clean-admin')
            )
        );
        
        // Apply filters to allow customization
        $this->cleanup_items = apply_filters('wpca_cleanup_items', $this->cleanup_items);
    }
    
    /**
     * Register hooks
     */
    private function register_hooks() {
        // AJAX hooks
        add_action('wp_ajax_wpca_optimize_tables', array($this, 'ajax_optimize_tables'));
        add_action('wp_ajax_wpca_run_database_cleanup', array($this, 'ajax_run_database_cleanup'));
        add_action('wp_ajax_wpca_get_database_info', array($this, 'ajax_get_database_info'));
        add_action('wp_ajax_wpca_get_cleanup_statistics', array($this, 'ajax_get_cleanup_statistics'));
        
        // Schedule hooks
        add_action('wpca_daily_cleanup', array($this, 'run_scheduled_cleanup'));
        add_action('wpca_weekly_cleanup', array($this, 'run_scheduled_cleanup'));
        add_action('wpca_monthly_cleanup', array($this, 'run_scheduled_cleanup'));
    }
    
    /**
     * Get tables to optimize
     * 
     * @return array Tables to optimize
     */
    private function get_tables_to_optimize() {
        $tables = array(
            $this->table_prefix . 'posts',
            $this->table_prefix . 'postmeta',
            $this->table_prefix . 'comments',
            $this->table_prefix . 'commentmeta',
            $this->table_prefix . 'links',
            $this->table_prefix . 'term_relationships',
            $this->table_prefix . 'term_taxonomy',
            $this->table_prefix . 'terms',
            $this->table_prefix . 'options',
            $this->table_prefix . 'users',
            $this->table_prefix . 'usermeta'
        );
        
        // Apply filters to allow customization
        return apply_filters('wpca_tables_to_optimize', $tables);
    }
    
    /**
     * Optimize database tables
     * 
     * @return array Optimization results
     */
    public function optimize_tables() {
        $tables = $this->get_tables_to_optimize();
        $results = array(
            'success' => true,
            'optimized_tables' => 0,
            'total_tables' => count($tables),
            'space_saved' => 0,
            'details' => array()
        );
        
        foreach ($tables as $table) {
            // Trigger before optimization hook
            do_action('wpca_before_table_optimize', $table);
            
            $table_result = $this->db->query("OPTIMIZE TABLE {$table}");
            
            // Trigger after optimization hook
            do_action('wpca_after_table_optimize', $table, $table_result);
            
            if ($table_result !== false) {
                $results['optimized_tables']++;
                $results['details'][] = array(
                    'table' => $table,
                    'status' => 'optimized'
                );
            } else {
                $results['details'][] = array(
                    'table' => $table,
                    'status' => 'failed',
                    'error' => $this->db->last_error
                );
            }
        }
        
        return $results;
    }
    
    /**
     * Get cleanup query for specific item
     * 
     * @param string $item Cleanup item name
     * @param array $config Cleanup configuration
     * @return string|false Cleanup query or false if invalid
     */
    private function get_cleanup_query($item, $config) {
        $query = false;
        
        switch ($item) {
            case 'revision_posts':
                $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$config['days']} days"));
                $query = "DELETE FROM {$this->table_prefix}posts WHERE post_type = 'revision' AND post_modified < %s";
                break;
                
            case 'auto_drafts':
                $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$config['days']} days"));
                $query = "DELETE FROM {$this->table_prefix}posts WHERE post_status = 'auto-draft' AND post_date < %s";
                break;
                
            case 'trashed_posts':
                $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$config['days']} days"));
                $query = "DELETE FROM {$this->table_prefix}posts WHERE post_status = 'trash' AND post_modified < %s";
                break;
                
            case 'spam_comments':
                $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$config['days']} days"));
                $query = "DELETE FROM {$this->table_prefix}comments WHERE comment_approved = 'spam' AND comment_date < %s";
                break;
                
            case 'trash_comments':
                $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$config['days']} days"));
                $query = "DELETE FROM {$this->table_prefix}comments WHERE comment_approved = 'trash' AND comment_date < %s";
                break;
                
            case 'orphan_postmeta':
                $query = "DELETE pm FROM {$this->table_prefix}postmeta pm LEFT JOIN {$this->table_prefix}posts p ON pm.post_id = p.ID WHERE p.ID IS NULL";
                break;
                
            case 'orphan_commentmeta':
                $query = "DELETE cm FROM {$this->table_prefix}commentmeta cm LEFT JOIN {$this->table_prefix}comments c ON cm.comment_id = c.comment_ID WHERE c.comment_ID IS NULL";
                break;
                
            case 'orphan_term_relationships':
                $query = "DELETE tr FROM {$this->table_prefix}term_relationships tr LEFT JOIN {$this->table_prefix}posts p ON tr.object_id = p.ID WHERE p.ID IS NULL";
                break;
                
            case 'orphan_usermeta':
                $query = "DELETE um FROM {$this->table_prefix}usermeta um LEFT JOIN {$this->table_prefix}users u ON um.user_id = u.ID WHERE u.ID IS NULL";
                break;
                
            case 'expired_transients':
                $query = "DELETE FROM {$this->table_prefix}options WHERE option_name LIKE '_transient_timeout_%' AND option_value < %d";
                break;
                
            case 'oembed_cache':
                $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$config['days']} days"));
                $query = "DELETE FROM {$this->table_prefix}postmeta WHERE meta_key LIKE '_oembed_%' AND post_id IN (SELECT ID FROM {$this->table_prefix}posts WHERE post_modified < %s)";
                break;
        }
        
        // Apply filter for custom queries
        return apply_filters('wpca_cleanup_query', $query, $item);
    }
    
    /**
     * Run database cleanup
     * 
     * @param array $cleanup_items Cleanup items configuration
     * @return array Cleanup results
     */
    public function run_database_cleanup($cleanup_items = array()) {
        if (empty($cleanup_items)) {
            $cleanup_items = $this->cleanup_items;
        }
        
        // Trigger before cleanup hook
        do_action('wpca_before_database_cleanup', $cleanup_items);
        
        $results = array(
            'success' => true,
            'total_rows' => 0,
            'space_saved' => 0,
            'details' => array()
        );
        
        foreach ($cleanup_items as $item => $config) {
            if (isset($config['enabled']) && $config['enabled']) {
                $query = $this->get_cleanup_query($item, $config);
                
                if ($query) {
                    $row_count = 0;
                    
                    switch ($item) {
                        case 'expired_transients':
                            $row_count = $this->db->query($this->db->prepare($query, time()));
                            // Also clean up expired transients themselves
                            $this->db->query("DELETE t FROM {$this->table_prefix}options t INNER JOIN {$this->table_prefix}options tt ON t.option_name = CONCAT('_transient_', SUBSTRING(tt.option_name, 19)) WHERE tt.option_name LIKE '_transient_timeout_%' AND tt.option_value < %d", time());
                            break;
                            
                        default:
                            $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$config['days']} days"));
                            $row_count = $this->db->query($this->db->prepare($query, $cutoff_date));
                            break;
                    }
                    
                    $results['total_rows'] += $row_count;
                    $results['details'][] = array(
                        'item' => $item,
                        'rows_deleted' => $row_count,
                        'status' => 'completed'
                    );
                }
            }
        }
        
        // Optimize tables after cleanup
        $this->optimize_tables();
        
        // Trigger after cleanup hook
        do_action('wpca_after_database_cleanup', $results);
        
        return $results;
    }
    
    /**
     * Get database information
     * 
     * @return array Database information
     */
    public function get_database_info() {
        $tables = $this->get_tables_to_optimize();
        $total_size = 0;
        $overhead = 0;
        $table_details = array();
        
        foreach ($tables as $table) {
            $table_info = $this->db->get_row("SHOW TABLE STATUS LIKE '{$table}'", ARRAY_A);
            if ($table_info) {
                $table_size = $table_info['Data_length'] + $table_info['Index_length'];
                $total_size += $table_size;
                $overhead += $table_info['Data_free'];
                
                $table_details[] = array(
                    'name' => $table,
                    'size' => $table_size,
                    'overhead' => $table_info['Data_free'],
                    'rows' => $table_info['Rows'],
                    'engine' => $table_info['Engine']
                );
            }
        }
        
        return array(
            'tables' => $table_details,
            'total_size' => $total_size,
            'overhead' => $overhead,
            'total_tables' => count($tables)
        );
    }
    
    /**
     * Get cleanup statistics
     * 
     * @return array Cleanup statistics
     */
    public function get_cleanup_statistics() {
        $stats = get_option('wpca_cleanup_statistics', array(
            'last_cleanup' => '',
            'total_cleanups' => 0,
            'total_rows_cleaned' => 0,
            'total_space_saved' => 0
        ));
        
        return $stats;
    }
    
    /**
     * Update cleanup statistics
     * 
     * @param array $results Cleanup results
     */
    private function update_cleanup_statistics($results) {
        $stats = $this->get_cleanup_statistics();
        
        $stats['last_cleanup'] = current_time('mysql');
        $stats['total_cleanups']++;
        $stats['total_rows_cleaned'] += $results['total_rows'];
        
        update_option('wpca_cleanup_statistics', $stats);
    }
    
    /**
     * Set scheduled cleanup
     * 
     * @param string $frequency Cleanup frequency (daily, weekly, monthly)
     * @return bool Success status
     */
    public function set_scheduled_cleanup($frequency = 'weekly') {
        // Remove existing schedules first
        $this->remove_scheduled_cleanup();
        
        $hook = '';
        switch ($frequency) {
            case 'daily':
                $hook = 'wpca_daily_cleanup';
                break;
            case 'weekly':
                $hook = 'wpca_weekly_cleanup';
                break;
            case 'monthly':
                $hook = 'wpca_monthly_cleanup';
                break;
            default:
                return false;
        }
        
        return wp_schedule_event(time(), $frequency, $hook);
    }
    
    /**
     * Remove scheduled cleanup
     * 
     * @return bool Success status
     */
    public function remove_scheduled_cleanup() {
        $success = true;
        
        if (wp_next_scheduled('wpca_daily_cleanup')) {
            $success &= wp_clear_scheduled_hook('wpca_daily_cleanup');
        }
        
        if (wp_next_scheduled('wpca_weekly_cleanup')) {
            $success &= wp_clear_scheduled_hook('wpca_weekly_cleanup');
        }
        
        if (wp_next_scheduled('wpca_monthly_cleanup')) {
            $success &= wp_clear_scheduled_hook('wpca_monthly_cleanup');
        }
        
        return $success;
    }
    
    /**
     * Run scheduled cleanup
     */
    public function run_scheduled_cleanup() {
        $results = $this->run_database_cleanup();
        $this->update_cleanup_statistics($results);
    }
    
    /**
     * AJAX handler for optimizing tables
     */
    public function ajax_optimize_tables() {
        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')));
        }
        
        $results = $this->optimize_tables();
        
        if ($results['success']) {
            wp_send_json_success(array(
                'optimized_tables' => $results['optimized_tables'],
                'total_tables' => $results['total_tables'],
                'space_saved' => $results['space_saved'],
                'details' => $results['details']
            ), __('Database tables optimized successfully', 'wp-clean-admin'));
        } else {
            wp_send_json_error(array('message' => __('Failed to optimize tables', 'wp-clean-admin')));
        }
    }
    
    /**
     * AJAX handler for running database cleanup
     */
    public function ajax_run_database_cleanup() {
        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')));
        }
        
        $cleanup_items = isset($_POST['cleanup_items']) ? json_decode(stripslashes($_POST['cleanup_items']), true) : array();
        $results = $this->run_database_cleanup($cleanup_items);
        
        $this->update_cleanup_statistics($results);
        
        if ($results['success']) {
            wp_send_json_success(array(
                'total_rows' => $results['total_rows'],
                'space_saved' => $results['space_saved'],
                'details' => $results['details']
            ), __('Database cleanup completed successfully', 'wp-clean-admin'));
        } else {
            wp_send_json_error(array('message' => __('Failed to run database cleanup', 'wp-clean-admin')));
        }
    }
    
    /**
     * AJAX handler for getting database information
     */
    public function ajax_get_database_info() {
        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')));
        }
        
        $info = $this->get_database_info();
        wp_send_json_success($info);
    }
    
    /**
     * AJAX handler for getting cleanup statistics
     */
    public function ajax_get_cleanup_statistics() {
        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'wp-clean-admin')));
        }
        
        $stats = $this->get_cleanup_statistics();
        wp_send_json_success($stats);
    }
}
