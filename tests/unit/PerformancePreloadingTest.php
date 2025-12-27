<?php
/**
 * Unit tests for Performance class - resource preloading functionality
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
 * Test class for Performance - Resource Preloading functionality
 * @covers Performance
 */
class PerformancePreloadingTest extends TestCase {
    
    /**
     * Test instance creation
     */
    public function test_instance_creation() {
        $performance = Performance::getInstance();
        $this->assertInstanceOf( Performance::class, $performance );
    }
    
    /**
     * Test enable_resource_preloading method exists
     */
    public function test_enable_resource_preloading_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'enable_resource_preloading' ) );
    }
    
    /**
     * Test add_resource_hints method exists
     */
    public function test_add_resource_hints_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'add_resource_hints' ) );
    }
    
    /**
     * Test preload_resource method exists
     */
    public function test_preload_resource_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'preload_resource' ) );
    }
    
    /**
     * Test preload_resource returns correct HTML tag for script
     */
    public function test_preload_resource_returns_script_tag() {
        $performance = Performance::getInstance();
        
        $result = $performance->preload_resource( 'https://example.com/script.js', 'script' );
        
        $this->assertStringContainsString( 'rel="preload"', $result );
        $this->assertStringContainsString( 'as="script"', $result );
        $this->assertStringContainsString( 'href="https://example.com/script.js"', $result );
    }
    
    /**
     * Test preload_resource returns correct HTML tag for style
     */
    public function test_preload_resource_returns_style_tag() {
        $performance = Performance::getInstance();
        
        $result = $performance->preload_resource( 'https://example.com/style.css', 'style' );
        
        $this->assertStringContainsString( 'rel="preload"', $result );
        $this->assertStringContainsString( 'as="style"', $result );
    }
    
    /**
     * Test preload_resource with media attribute
     */
    public function test_preload_resource_with_media() {
        $performance = Performance::getInstance();
        
        $result = $performance->preload_resource( 'https://example.com/print.css', 'style', 'print' );
        
        $this->assertStringContainsString( 'media="print"', $result );
    }
    
    /**
     * Test preload_resource returns empty for empty URL
     */
    public function test_preload_resource_empty_url() {
        $performance = Performance::getInstance();
        
        $result = $performance->preload_resource( '', 'script' );
        
        $this->assertEquals( '', $result );
    }
    
    /**
     * Test add_dns_prefetch method exists
     */
    public function test_add_dns_prefetch_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'add_dns_prefetch' ) );
    }
    
    /**
     * Test add_preconnect method exists
     */
    public function test_add_preconnect_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'add_preconnect' ) );
    }
    
    /**
     * Test prerender_url method exists
     */
    public function test_prerender_url_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'prerender_url' ) );
    }
    
    /**
     * Test prefetch_url method exists
     */
    public function test_prefetch_url_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'prefetch_url' ) );
    }
    
    /**
     * Test prerender_url returns correct HTML tag
     */
    public function test_prerender_url_returns_tag() {
        $performance = Performance::getInstance();
        
        $result = $performance->prerender_url( 'https://example.com/page' );
        
        $this->assertStringContainsString( 'rel="prerender"', $result );
        $this->assertStringContainsString( 'href="https://example.com/page"', $result );
    }
    
    /**
     * Test prefetch_url returns correct HTML tag
     */
    public function test_prefetch_url_returns_tag() {
        $performance = Performance::getInstance();
        
        $result = $performance->prefetch_url( 'https://example.com/page' );
        
        $this->assertStringContainsString( 'rel="prefetch"', $result );
        $this->assertStringContainsString( 'href="https://example.com/page"', $result );
    }
    
    /**
     * Test prerender_url returns empty for empty URL
     */
    public function test_prerender_url_empty_url() {
        $performance = Performance::getInstance();
        
        $result = $performance->prerender_url( '' );
        
        $this->assertEquals( '', $result );
    }
    
    /**
     * Test prefetch_url returns empty for empty URL
     */
    public function test_prefetch_url_empty_url() {
        $performance = Performance::getInstance();
        
        $result = $performance->prefetch_url( '' );
        
        $this->assertEquals( '', $result );
    }
    
    /**
     * Test get_preloading_status method exists
     */
    public function test_get_preloading_status_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'get_preloading_status' ) );
    }
    
    /**
     * Test get_preloading_status returns array
     */
    public function test_get_preloading_status_returns_array() {
        $performance = Performance::getInstance();
        
        $status = $performance->get_preloading_status();
        
        $this->assertIsArray( $status );
        $this->assertArrayHasKey( 'enabled', $status );
        $this->assertArrayHasKey( 'preload_count', $status );
        $this->assertArrayHasKey( 'dns_prefetch_count', $status );
        $this->assertArrayHasKey( 'preconnect_count', $status );
    }
    
    /**
     * Test add_resource_hints method returns array
     */
    public function test_add_resource_hints_returns_array() {
        $performance = Performance::getInstance();
        
        $hints = array();
        $result = $performance->add_resource_hints( $hints, 'preload' );
        
        $this->assertIsArray( $result );
    }
}
