<?php
/**
 * Unit tests for Cleanup class - shortcode cleanup functionality
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
use WPCleanAdmin\Cleanup;

/**
 * Test class for Cleanup - Shortcode cleanup functionality
 * @covers Cleanup
 */
class CleanupShortcodeTest extends TestCase {
    
    /**
     * Test instance creation
     */
    public function test_instance_creation() {
        $cleanup = Cleanup::getInstance();
        $this->assertInstanceOf( Cleanup::class, $cleanup );
    }
    
    /**
     * Test remove_shortcode method exists
     */
    public function test_remove_shortcode_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'remove_shortcode' ) );
    }
    
    /**
     * Test remove_shortcode removes basic shortcode
     */
    public function test_remove_shortcode_removes_basic_shortcode() {
        $cleanup = Cleanup::getInstance();
        
        $content = 'This is some [shortcode]content[/shortcode] here.';
        $result = $cleanup->remove_shortcode( $content, 'shortcode' );
        
        $this->assertEquals( 'This is some  content here.', $result );
    }
    
    /**
     * Test remove_shortcode removes self-closing shortcode
     */
    public function test_remove_shortcode_removes_self_closing_shortcode() {
        $cleanup = Cleanup::getInstance();
        
        $content = 'This is [gallery id="123"] content.';
        $result = $cleanup->remove_shortcode( $content, 'gallery' );
        
        $this->assertEquals( 'This is  content.', $result );
    }
    
    /**
     * Test remove_shortcode handles shortcode with attributes
     */
    public function test_remove_shortcode_handles_attributes() {
        $cleanup = Cleanup::getInstance();
        
        $content = '[button type="primary" size="large" url="https://example.com"]Click here[/button]';
        $result = $cleanup->remove_shortcode( $content, 'button' );
        
        $this->assertEquals( '', $result );
    }
    
    /**
     * Test remove_shortcode returns original content if shortcode not found
     */
    public function test_remove_shortcode_returns_original_if_not_found() {
        $cleanup = Cleanup::getInstance();
        
        $content = 'This is some content without shortcodes.';
        $result = $cleanup->remove_shortcode( $content, 'nonexistent' );
        
        $this->assertEquals( $content, $result );
    }
    
    /**
     * Test remove_shortcode handles empty content
     */
    public function test_remove_shortcode_handles_empty_content() {
        $cleanup = Cleanup::getInstance();
        
        $this->assertEquals( '', $cleanup->remove_shortcode( '', 'shortcode' ) );
    }
    
    /**
     * Test remove_shortcode handles empty shortcode name
     */
    public function test_remove_shortcode_handles_empty_shortcode_name() {
        $cleanup = Cleanup::getInstance();
        
        $content = 'This is [test]content[/test] here.';
        $result = $cleanup->remove_shortcode( $content, '' );
        
        $this->assertEquals( $content, $result );
    }
    
    /**
     * Test run_content_cleanup method exists
     */
    public function test_run_content_cleanup_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'run_content_cleanup' ) );
    }
    
    /**
     * Test run_content_cleanup returns array structure
     */
    public function test_run_content_cleanup_returns_array() {
        $cleanup = Cleanup::getInstance();
        $result = $cleanup->run_content_cleanup();
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'cleaned', $result );
    }
    
    /**
     * Test run_content_cleanup with empty posts option
     */
    public function test_run_content_cleanup_with_empty_posts_option() {
        $cleanup = Cleanup::getInstance();
        
        $options = array(
            'unused_shortcodes' => false,
            'empty_posts' => false,
            'duplicate_posts' => false
        );
        
        $result = $cleanup->run_content_cleanup( $options );
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'cleaned', $result );
    }
}
