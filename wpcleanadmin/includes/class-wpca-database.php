﻿<?php
/**
 * Database class for WP Clean Admin plugin
 *
 * @package WPCleanAdmin
 */

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Database class
 */
class Database {
    
    /**
     * Singleton instance
     *
     * @var Database
     */
    private static $instance;
    
    /**
     * Get singleton instance
     *
     * @return Database
     */
    public static function get_instance() {
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
     * Initialize the database module
     */
    public function init() {
        // Add database optimization hooks
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'wpca_optimize_database', array( $this, 'optimize_database' ) );
        }
    }
    
    /**
     * Get database information
     *
     * @return array Database information
     */
    public function get_database_info() {
        global $wpdb;
        
        $info = array();
        
        // Get database name and version
        $info['name'] = $wpdb->dbname;
        $info['version'] = $wpdb->db_version();
        
        // Get table count
        $info['table_count'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM information_schema.TABLES WHERE table_schema = %s", $wpdb->dbname ) );
        
        // Get database size
        $result = $wpdb->get_row( $wpdb->prepare( "SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = %s", $wpdb->dbname ), ARRAY_A );
        $info['size'] = ( function_exists( 'size_format' ) ? \size_format( $result['size'], 2 ) : round( $result['size'] / 1024 / 1024, 2 ) . ' MB' );
        
        // Get WordPress tables
        $info['wp_tables'] = array();
        $tables = $wpdb->get_results( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . '%' ), ARRAY_N );
        
        foreach ( $tables as $table ) {
            $table_name = $table[0];
            $table_info = $wpdb->get_row( $wpdb->prepare( "SELECT data_length, index_length FROM information_schema.TABLES WHERE table_schema = %s AND table_name = %s", $wpdb->dbname, $table_name ), ARRAY_A );
            
            $total_size = $table_info['data_length'] + $table_info['index_length'];
            $data_size = $table_info['data_length'];
            $index_size = $table_info['index_length'];
            
            $info['wp_tables'][] = array(
                'name' => $table_name,
                'size' => ( function_exists( 'size_format' ) ? \size_format( $total_size, 2 ) : round( $total_size / 1024 / 1024, 2 ) . ' MB' ),
                'data_size' => ( function_exists( 'size_format' ) ? \size_format( $data_size, 2 ) : round( $data_size / 1024 / 1024, 2 ) . ' MB' ),
                'index_size' => ( function_exists( 'size_format' ) ? \size_format( $index_size, 2 ) : round( $index_size / 1024 / 1024, 2 ) . ' MB' )
            );
        }
        
        return $info;
    }
    
    /**
     * Optimize database tables
     *
     * @return array Optimization results
     */
    public function optimize_database() {
        global $wpdb;
        
        $results = array(
            'success' => true,
            'message' => \__( 'Database optimization completed successfully', WPCA_TEXT_DOMAIN ),
            'tables' => array()
        );
        
        // Get all WordPress tables
        $tables = $wpdb->get_results( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . '%' ), ARRAY_N );
        
        foreach ( $tables as $table ) {
            $table_name = $table[0];
            $result = $wpdb->query( $wpdb->prepare( "OPTIMIZE TABLE %s", $table_name ) );
            
            $results['tables'][] = array(
                'name' => $table_name,
                'optimized' => $result !== false
            );
        }
        
        return $results;
    }
    
    /**
     * Backup database
     *
     * @param array $options Backup options
     * @return array Backup results
     */
    public function backup_database( $options = array() ) {
        global $wpdb;
        
        $results = array(
            'success' => false,
            'message' => \__( 'Database backup failed', WPCA_TEXT_DOMAIN )
        );
        
        // Set default options
        $default_options = array(
            'tables' => 'all',
            'format' => 'sql',
            'compress' => true
        );
        
        $options = ( function_exists( '\wp_parse_args' ) ? \wp_parse_args( $options, $default_options ) : array_merge( $default_options, $options ) );
        
        // Create backup directory if it doesn't exist
        $backup_dir = WPCA_PLUGIN_DIR . 'backups/';
        if ( ! file_exists( $backup_dir ) ) {
            if ( function_exists( 'wp_mkdir_p' ) ) {
                \wp_mkdir_p( $backup_dir );
            } else {
                // Fallback to mkdir with recursive flag
                mkdir( $backup_dir, 0755, true );
            }
        }
        
        // Generate backup file name
        $backup_file = $backup_dir . 'wpca-backup-' . date( 'Y-m-d-H-i-s' ) . '.' . $options['format'];
        
        // Get tables to backup
        if ( $options['tables'] === 'all' ) {
            $tables = $wpdb->get_results( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . '%' ), ARRAY_N );
            $tables = array_column( $tables, 0 );
        } else {
            // Get all valid WordPress tables
            $all_wp_tables = $wpdb->get_results( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . '%' ), ARRAY_N );
            $all_wp_tables = array_column( $all_wp_tables, 0 );
            
            // Filter requested tables to only include valid WordPress tables
            $requested_tables = explode( ',', $options['tables'] );
            $tables = array();
            
            foreach ( $requested_tables as $table ) {
                $table = trim( $table );
                // Only include valid WordPress tables to prevent SQL injection
                if ( in_array( $table, $all_wp_tables ) ) {
                    $tables[] = $table;
                }
            }
        }
        
        // Start backup
        $backup_content = "-- WordPress Database Backup\n";
        $backup_content .= "-- Generated by WP Clean Admin on " . date( 'Y-m-d H:i:s' ) . "\n";
        $backup_content .= "-- Database: {$wpdb->dbname}\n\n";
        
        // Backup each table
        foreach ( $tables as $table ) {
            // Get table structure
            $create_table = $wpdb->get_var( $wpdb->prepare( "SHOW CREATE TABLE %s", $table ) );
            $backup_content .= "-- Table structure for table `{$table}`\n";
            $backup_content .= "{$create_table};\n\n";
            
            // Get table data
            $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %s", $table ), ARRAY_A );
            if ( count( $rows ) > 0 ) {
                $backup_content .= "-- Dumping data for table `{$table}`\n";
                $backup_content .= "INSERT INTO `{$table}` VALUES\n";
                
                $values = array();
                foreach ( $rows as $row ) {
                    $row_values = array();
                    foreach ( $row as $value ) {
                        $row_values[] = $wpdb->prepare( '%s', $value );
                    }
                    $values[] = "(" . implode( ',', $row_values ) . ")";
                }
                
                $backup_content .= implode( ",\n", $values ) . ";\n\n";
            }
        }
        
        // Save backup file
        if ( file_put_contents( $backup_file, $backup_content ) !== false ) {
            $results['success'] = true;
            $results['message'] = \__( 'Database backup created successfully', WPCA_TEXT_DOMAIN );
            $results['file'] = basename( $backup_file );
            $results['size'] = filesize( $backup_file );
        }
        
        return $results;
    }
    
    /**
     * Restore database from backup
     *
     * @param string $backup_file Backup file name
     * @return array Restore results
     */
    public function restore_database( $backup_file ) {
        global $wpdb;
        
        $results = array(
            'success' => false,
            'message' => \__( 'Database restore failed', WPCA_TEXT_DOMAIN )
        );
        
        // Get full backup file path
        $backup_path = WPCA_PLUGIN_DIR . 'backups/' . $backup_file;
        
        // Check if backup file exists
        if ( ! file_exists( $backup_path ) ) {
            $results['message'] = \__( 'Backup file not found', WPCA_TEXT_DOMAIN );
            return $results;
        }
        
        // Read backup file content
        $backup_content = file_get_contents( $backup_path );
        
        // Execute SQL queries
        $queries = explode( ';', $backup_content );
        $success = true;
        
        foreach ( $queries as $query ) {
            $query = trim( $query );
            if ( ! empty( $query ) ) {
                if ( $wpdb->query( $query ) === false ) {
                    $success = false;
                    break;
                }
            }
        }
        
        if ( $success ) {
            $results['success'] = true;
            $results['message'] = \__( 'Database restore completed successfully', WPCA_TEXT_DOMAIN );
        }
        
        return $results;
    }
    
    /**
     * Get database backups list
     *
     * @return array Database backups
     */
    public function get_database_backups() {
        $backups = array();
        
        // Get backup directory
        $backup_dir = WPCA_PLUGIN_DIR . 'backups/';
        
        // Check if backup directory exists
        if ( ! file_exists( $backup_dir ) ) {
            return $backups;
        }
        
        // Get backup files
        $files = glob( $backup_dir . '*.sql' );
        
        foreach ( $files as $file ) {
            $backups[] = array(
                'name' => basename( $file ),
                'path' => $file,
                'size' => filesize( $file ),
                'modified' => filemtime( $file )
            );
        }
        
        // Sort backups by modified date (newest first)
        usort( $backups, function( $a, $b ) {
            return $b['modified'] - $a['modified'];
        } );
        
        return $backups;
    }
    
    /**
     * Delete database backup
     *
     * @param string $backup_file Backup file name
     * @return array Delete results
     */
    public function delete_database_backup( $backup_file ) {
        $results = array(
            'success' => false,
            'message' => \__( 'Failed to delete backup file', WPCA_TEXT_DOMAIN )
        );
        
        // Get full backup file path
        $backup_path = WPCA_PLUGIN_DIR . 'backups/' . $backup_file;
        
        // Check if backup file exists
        if ( file_exists( $backup_path ) ) {
            // Delete backup file
            if ( unlink( $backup_path ) ) {
                $results['success'] = true;
                $results['message'] = \__( 'Backup file deleted successfully', WPCA_TEXT_DOMAIN );
            }
        }
        
        return $results;
    }
}