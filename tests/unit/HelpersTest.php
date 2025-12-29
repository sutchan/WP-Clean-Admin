<?php
/**
 * Unit tests for the Helpers class
 *
 * @package WPCleanAdmin
 */

namespace WPCleanAdmin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPCleanAdmin\Helpers;

/**
 * Helpers test class
 */
class HelpersTest extends TestCase {

    /**
     * Test format_bytes method
     *
     * @covers Helpers::format_bytes
     */
    public function testFormatBytes() {
        $helpers = Helpers::getInstance();
        
        // Test with 0 bytes
        $this->assertEquals( '0 B', $helpers->format_bytes( 0 ) );
        
        // Test with 1 byte
        $this->assertEquals( '1 B', $helpers->format_bytes( 1 ) );
        
        // Test with 1 KB
        $this->assertEquals( '1.00 KB', $helpers->format_bytes( 1024 ) );
        
        // Test with 1 MB
        $this->assertEquals( '1.00 MB', $helpers->format_bytes( 1024 * 1024 ) );
        
        // Test with 1 GB
        $this->assertEquals( '1.00 GB', $helpers->format_bytes( 1024 * 1024 * 1024 ) );
        
        // Test with custom precision
        $this->assertEquals( '1.0 KB', $helpers->format_bytes( 1024, 1 ) );
    }
    
    /**
     * Test format_seconds method
     *
     * @covers Helpers::format_seconds
     */
    public function testFormatSeconds() {
        $helpers = Helpers::getInstance();
        
        // Test with 0 seconds
        $this->assertEquals( '', $helpers->format_seconds( 0 ) );
        
        // Test with 1 second
        $this->assertEquals( '1 second', $helpers->format_seconds( 1 ) );
        
        // Test with 2 seconds
        $this->assertEquals( '2 seconds', $helpers->format_seconds( 2 ) );
        
        // Test with 1 minute
        $this->assertEquals( '1 minute', $helpers->format_seconds( 60 ) );
        
        // Test with 1 hour
        $this->assertEquals( '1 hour', $helpers->format_seconds( 3600 ) );
        
        // Test with 1 day
        $this->assertEquals( '1 day', $helpers->format_seconds( 86400 ) );
        
        // Test with combination
        $this->assertEquals( '1 day, 1 hour, 1 minute, 1 second', $helpers->format_seconds( 86400 + 3600 + 60 + 1 ) );
    }
    
    /**
     * Test is_network_activated method
     *
     * @covers Helpers::is_network_activated
     */
    public function testIsNetworkActivated() {
        $helpers = Helpers::getInstance();
        
        // Test should return boolean
        $result = $helpers->is_network_activated();
        $this->assertIsBool( $result );
    }
    
    /**
     * Test get_php_version method
     *
     * @covers Helpers::get_php_version
     */
    public function testGetPhpVersion() {
        $helpers = Helpers::getInstance();
        
        // Test should return string
        $result = $helpers->get_php_version();
        $this->assertIsString( $result );
        $this->assertNotEmpty( $result );
    }
    
    /**
     * Test get_wp_version method
     *
     * @covers Helpers::get_wp_version
     */
    public function testGetWpVersion() {
        $helpers = Helpers::getInstance();
        
        // Test should return string
        $result = $helpers->get_wp_version();
        $this->assertIsString( $result );
    }
}
