<?php
/**
 * Unit tests for Database class
 *
 * @package WPCleanAdmin
 * @group database
 */

use PHPUnit\Framework\TestCase;
use WPCleanAdmin\Database;

class DatabaseTest extends TestCase {
    
    /**
     * Test that the Database class is a singleton
     */
    public function testSingleton() {
        $instance1 = Database::getInstance();
        $instance2 = Database::getInstance();
        
        $this->assertSame( $instance1, $instance2 );
    }
    
    /**
     * Test that the Database class initializes correctly
     */
    public function testInit() {
        $database = Database::getInstance();
        
        // Test that the database instance is not null
        $this->assertNotNull( $database );
        
        // Test that the database has the expected methods
        $this->assertTrue( method_exists( $database, 'get_database_info' ) );
        $this->assertTrue( method_exists( $database, 'optimize_database' ) );
        $this->assertTrue( method_exists( $database, 'backup_database' ) );
        $this->assertTrue( method_exists( $database, 'restore_database' ) );
        $this->assertTrue( method_exists( $database, 'get_database_backups' ) );
        $this->assertTrue( method_exists( $database, 'delete_database_backup' ) );
    }
    
    /**
     * Test the is_safe_sql_query method
     */
    public function testIsSafeSqlQuery() {
        $database = Database::getInstance();
        
        // Create a reflection to access the private method
        $reflection = new ReflectionClass( $database );
        $method = $reflection->getMethod( 'is_safe_sql_query' );
        $method->setAccessible( true );
        
        // Test safe queries
        $this->assertTrue( $method->invoke( $database, 'SELECT * FROM wp_posts' ) );
        $this->assertTrue( $method->invoke( $database, 'INSERT INTO wp_posts (post_title) VALUES (\'Test\')' ) );
        $this->assertTrue( $method->invoke( $database, 'UPDATE wp_posts SET post_title = \'Updated\' WHERE ID = 1' ) );
        $this->assertTrue( $method->invoke( $database, 'DELETE FROM wp_posts WHERE ID = 1' ) );
        $this->assertTrue( $method->invoke( $database, 'OPTIMIZE TABLE wp_posts' ) );
        $this->assertTrue( $method->invoke( $database, 'REPAIR TABLE wp_posts' ) );
        
        // Test unsafe queries
        $this->assertFalse( $method->invoke( $database, 'DROP TABLE wp_posts' ) );
        $this->assertFalse( $method->invoke( $database, 'TRUNCATE TABLE wp_posts' ) );
        $this->assertFalse( $method->invoke( $database, 'UPDATE wp_posts SET post_title = \'Updated\'' ) ); // No WHERE clause
        $this->assertFalse( $method->invoke( $database, 'DELETE FROM wp_posts' ) ); // No WHERE clause
    }
    
    /**
     * Test database backup functionality
     */
    public function testBackupDatabase() {
        // Mock WordPress functions and global $wpdb
        global $wpdb;
        $wpdb = $this->getMockBuilder( 'stdClass' )->getMock();
        
        // Setup mock wpdb
        $wpdb->dbname = 'test_db';
        $wpdb->prefix = 'wp_';
        
        $wpdb->method( 'get_var' )->willReturn( '3' );
        $wpdb->method( 'get_row' )->willReturn( array( 'size' => 1024 * 1024 ) );
        $wpdb->method( 'get_results' )->willReturn( array( array( 'wp_posts' ), array( 'wp_users' ) ) );
        $wpdb->method( 'query' )->willReturn( true );
        $wpdb->method( 'prepare' )->will( $this->returnCallback( function( $query, $param ) {
            return str_replace( '%s', $param, $query );
        } ) );
        
        // Mock file functions
        $this->mockFileFunctions();
        
        // Mock WordPress functions
        $this->mockWordPressFunctions();
        
        $database = Database::getInstance();
        $result = $database->backup_database();
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
    }
    
    /**
     * Test getting database backups
     */
    public function testGetDatabaseBackups() {
        // Mock file functions
        $this->mockFileFunctions();
        
        $database = Database::getInstance();
        $backups = $database->get_database_backups();
        
        $this->assertIsArray( $backups );
    }
    
    /**
     * Mock file functions for testing
     */
    private function mockFileFunctions() {
        // Mock file_exists
        if ( ! function_exists( 'file_exists' ) ) {
            function file_exists( $path ) {
                return true;
            }
        }
        
        // Mock is_dir
        if ( ! function_exists( 'is_dir' ) ) {
            function is_dir( $path ) {
                return true;
            }
        }
        
        // Mock file_put_contents
        if ( ! function_exists( 'file_put_contents' ) ) {
            function file_put_contents( $path, $content ) {
                return strlen( $content );
            }
        }
        
        // Mock filesize
        if ( ! function_exists( 'filesize' ) ) {
            function filesize( $path ) {
                return 1024;
            }
        }
        
        // Mock filemtime
        if ( ! function_exists( 'filemtime' ) ) {
            function filemtime( $path ) {
                return time();
            }
        }
        
        // Mock glob
        if ( ! function_exists( 'glob' ) ) {
            function glob( $pattern ) {
                return array();
            }
        }
        
        // Mock realpath
        if ( ! function_exists( 'realpath' ) ) {
            function realpath( $path ) {
                return $path;
            }
        }
        
        // Mock file_get_contents
        if ( ! function_exists( 'file_get_contents' ) ) {
            function file_get_contents( $path ) {
                return '-- WordPress Database Backup\n-- Generated by WP Clean Admin on 2023-01-01 12:00:00\n-- Database: test_db\n\n';
            }
        }
        
        // Mock unlink
        if ( ! function_exists( 'unlink' ) ) {
            function unlink( $path ) {
                return true;
            }
        }
    }
    
    /**
     * Mock WordPress functions for testing
     */
    private function mockWordPressFunctions() {
        // Mock wp_mkdir_p
        if ( ! function_exists( 'wp_mkdir_p' ) ) {
            function wp_mkdir_p( $path ) {
                return true;
            }
        }
        
        // Mock wp_parse_args
        if ( ! function_exists( 'wp_parse_args' ) ) {
            function wp_parse_args( $args, $defaults = array() ) {
                return array_merge( $defaults, $args );
            }
        }
        
        // Mock __ (translation function)
        if ( ! function_exists( '__' ) ) {
            function __( $text, $domain = 'default' ) {
                return $text;
            }
        }
        
        // Mock size_format
        if ( ! function_exists( 'size_format' ) ) {
            function size_format( $bytes, $decimals = 0 ) {
                return round( $bytes / 1024 / 1024, $decimals ) . ' MB';
            }
        }
        
        // Mock add_action
        if ( ! function_exists( 'add_action' ) ) {
            function add_action( $hook, $function_to_add, $priority = 10, $accepted_args = 1 ) {
                return true;
            }
        }
    }
}