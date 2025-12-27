<?php
/**
 * Unit tests for Performance class
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
use WPCleanAdmin\Performance;

/**
 * Test class for Performance
 * @covers Performance
 */
class PerformanceTest extends TestCase {
    
    /**
     * Test instance creation
     */
    public function test_instance_creation() {
        $performance = Performance::getInstance();
        $this->assertInstanceOf( Performance::class, $performance );
    }
    
    /**
     * Test singleton pattern
     */
    public function test_singleton_pattern() {
        $performance1 = Performance::getInstance();
        $performance2 = Performance::getInstance();
        $this->assertSame( $performance1, $performance2 );
    }
    
    /**
     * Test disable_emojis method exists
     */
    public function test_disable_emojis_method() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'disable_emojis' ) );
    }
    
    /**
     * Test disable_xmlrpc method exists
     */
    public function test_disable_xmlrpc_method() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'disable_xmlrpc' ) );
    }
    
    /**
     * Test disable_rest_api method exists
     */
    public function test_disable_rest_api_method() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'disable_rest_api' ) );
    }
    
    /**
     * Test disable_heartbeat method exists
     */
    public function test_disable_heartbeat_method() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'disable_heartbeat' ) );
    }
    
    /**
     * Test optimize_database method exists
     */
    public function test_optimize_database_method() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'optimize_database' ) );
    }
    
    /**
     * Test clean_transients method exists
     */
    public function test_clean_transients_method() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'clean_transients' ) );
    }
    
    /**
     * Test clear_cache method exists
     */
    public function test_clear_cache_method() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'clear_cache' ) );
    }
    
    /**
     * Test disable_emojis returns void
     */
    public function test_disable_emojis_returns_void() {
        $performance = Performance::getInstance();
        
        // Capture output if any
        ob_start();
        $result = $performance->disable_emojis();
        $output = ob_get_clean();
        
        $this->assertNull( $result );
    }
    
    /**
     * Test disable_xmlrpc returns void
     */
    public function test_disable_xmlrpc_returns_void() {
        $performance = Performance::getInstance();
        $result = $performance->disable_xmlrpc();
        $this->assertNull( $result );
    }
    
    /**
     * Test disable_rest_api returns void
     */
    public function test_disable_rest_api_returns_void() {
        $performance = Performance::getInstance();
        $result = $performance->disable_rest_api();
        $this->assertNull( $result );
    }
    
    /**
     * Test disable_heartbeat returns void
     */
    public function test_disable_heartbeat_returns_void() {
        $performance = Performance::getInstance();
        $result = $performance->disable_heartbeat();
        $this->assertNull( $result );
    }
    
    /**
     * Test clean_transients returns void
     */
    public function test_clean_transients_returns_void() {
        $performance = Performance::getInstance();
        $result = $performance->clean_transients();
        $this->assertNull( $result );
    }
    
    /**
     * Test clear_cache returns void
     */
    public function test_clear_cache_returns_void() {
        $performance = Performance::getInstance();
        $result = $performance->clear_cache();
        $this->assertNull( $result );
    }
    
    /**
     * Test init method exists
     */
    public function test_init_method() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'init' ) );
    }
}
