<?php
/**
 * Unit tests for Core class
 *
 * @package WPCleanAdmin
 * @group core
 */

use PHPUnit\Framework\TestCase;
use WPCleanAdmin\Core;

class CoreTest extends TestCase {
    
    /**
     * Test that the Core class is a singleton
     */
    public function testSingleton() {
        $instance1 = Core::getInstance();
        $instance2 = Core::getInstance();
        
        $this->assertSame( $instance1, $instance2 );
    }
    
    /**
     * Test that the Core class initializes correctly
     */
    public function testInit() {
        $core = Core::getInstance();
        
        // Test that the core instance is not null
        $this->assertNotNull( $core );
        
        // Test that the core has the expected methods
        $this->assertTrue( method_exists( $core, 'init' ) );
        $this->assertTrue( method_exists( $core, 'activate' ) );
        $this->assertTrue( method_exists( $core, 'deactivate' ) );
    }
    
    /**
     * Test plugin activation
     */
    public function testActivate() {
        $core = Core::getInstance();
        
        // Mock WordPress functions
        $this->mockWordPressFunctions();
        
        // Test activation doesn't throw errors
        $this->expectNotToPerformAssertions();
        $core->activate();
    }
    
    /**
     * Test plugin deactivation
     */
    public function testDeactivate() {
        $core = Core::getInstance();
        
        // Mock WordPress functions
        $this->mockWordPressFunctions();
        
        // Test deactivation doesn't throw errors
        $this->expectNotToPerformAssertions();
        $core->deactivate();
    }
    
    /**
     * Mock WordPress functions for testing
     */
    private function mockWordPressFunctions() {
        // Mock WordPress option functions
        if ( ! function_exists( 'get_option' ) ) {
            function get_option( $name, $default = false ) {
                return $default;
            }
        }
        
        if ( ! function_exists( 'update_option' ) ) {
            function update_option( $name, $value ) {
                return true;
            }
        }
        
        if ( ! function_exists( 'wp_parse_args' ) ) {
            function wp_parse_args( $args, $defaults = '' ) {
                return array_merge( $defaults, $args );
            }
        }
        
        if ( ! function_exists( 'flush_rewrite_rules' ) ) {
            function flush_rewrite_rules() {
                return true;
            }
        }
    }
}