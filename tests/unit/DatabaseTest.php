<?php
/**
 * Unit tests for Database class
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 *
 * @noinspection PhpUndefinedClassInspection PHPUnit is loaded via composer
 * @noinspection PhpUndefinedMethodInspection PHPUnit methods are available
 */

use PHPUnit\Framework\TestCase;
use WPCleanAdmin\Database;

/**
 * Test class for Database
 * @covers Database
 */
class DatabaseTest extends TestCase {
    
    /**
     * Test instance creation
     */
    public function test_instance_creation() {
        $database = Database::getInstance();
        $this->assertInstanceOf( Database::class, $database );
    }
    
    /**
     * Test singleton pattern
     */
    public function test_singleton_pattern() {
        $database1 = Database::getInstance();
        $database2 = Database::getInstance();
        $this->assertSame( $database1, $database2 );
    }
    
    /**
     * Test get_database_info method exists
     */
    public function test_get_database_info_method() {
        $database = Database::getInstance();
        $this->assertTrue( method_exists( $database, 'get_database_info' ) );
    }
    
    /**
     * Test optimize_database method exists
     */
    public function test_optimize_database_method() {
        $database = Database::getInstance();
        $this->assertTrue( method_exists( $database, 'optimize_database' ) );
    }
    
    /**
     * Test backup_database method exists
     */
    public function test_backup_database_method() {
        $database = Database::getInstance();
        $this->assertTrue( method_exists( $database, 'backup_database' ) );
    }
    
    /**
     * Test restore_database method exists
     */
    public function test_restore_database_method() {
        $database = Database::getInstance();
        $this->assertTrue( method_exists( $database, 'restore_database' ) );
    }
    
    /**
     * Test get_database_info returns array
     */
    public function test_get_database_info_returns_array() {
        $database = Database::getInstance();
        $info = $database->get_database_info();
        $this->assertIsArray( $info );
    }
    
    /**
     * Test get_database_info contains expected keys
     */
    public function test_get_database_info_contains_expected_keys() {
        $database = Database::getInstance();
        $info = $database->get_database_info();
        
        $expected_keys = array( 'name', 'version', 'table_count', 'size', 'wp_tables' );
        
        foreach ( $expected_keys as $key ) {
            $this->assertArrayHasKey( $key, $info, "Database info should contain '{$key}' key" );
        }
    }
    
    /**
     * Test optimize_database returns array with success key
     */
    public function test_optimize_database_returns_array() {
        $database = Database::getInstance();
        $result = $database->optimize_database();
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'tables', $result );
    }
    
    /**
     * Test backup_database with default options returns array
     */
    public function test_backup_database_with_defaults() {
        $database = Database::getInstance();
        $result = $database->backup_database();
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
    }
    
    /**
     * Test backup_database with custom options
     */
    public function test_backup_database_with_custom_options() {
        $database = Database::getInstance();
        
        $options = array(
            'tables' => 'all',
            'format' => 'sql',
            'compress' => false,
        );
        
        $result = $database->backup_database( $options );
        $this->assertIsArray( $result );
    }
    
    /**
     * Test restore_database returns array structure
     */
    public function test_restore_database_returns_array() {
        $database = Database::getInstance();
        $result = $database->restore_database( '' );
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
    }
    
    /**
     * Test restore_database handles invalid input
     */
    public function test_restore_database_handles_invalid_input() {
        $database = Database::getInstance();
        $result = $database->restore_database( 'invalid sql content' );
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        // For invalid input, success should be false
        $this->assertFalse( $result['success'] );
    }
}
