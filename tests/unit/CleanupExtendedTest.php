<?php
/**
 * Extended Unit Tests for Cleanup Module
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @since 1.7.15
 */

use PHPUnit\Framework\TestCase;
use WPCleanAdmin\Cleanup;

/**
 * Test class for Cleanup module - Extended coverage
 * @covers Cleanup
 */
class CleanupExtendedTest extends TestCase {
    
    /**
     * Test instance creation
     */
    public function test_instance_creation() {
        $cleanup = Cleanup::getInstance();
        $this->assertInstanceOf( Cleanup::class, $cleanup );
    }
    
    /**
     * Test singleton pattern
     */
    public function test_singleton_pattern() {
        $cleanup1 = Cleanup::getInstance();
        $cleanup2 = Cleanup::getInstance();
        $this->assertSame( $cleanup1, $cleanup2 );
    }
    
    /**
     * Test get_cleanup_stats method
     */
    public function test_get_cleanup_stats_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'get_cleanup_stats' ) );
    }
    
    /**
     * Test get_cleanup_stats returns array
     */
    public function test_get_cleanup_stats_returns_array() {
        $cleanup = Cleanup::getInstance();
        $stats = $cleanup->get_cleanup_stats();
        $this->assertIsArray( $stats );
    }
    
    /**
     * Test run_media_cleanup method
     */
    public function test_run_media_cleanup_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'run_media_cleanup' ) );
    }
    
    /**
     * Test run_media_cleanup returns array
     */
    public function test_run_media_cleanup_returns_array() {
        $cleanup = Cleanup::getInstance();
        $result = $cleanup->run_media_cleanup();
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'cleaned', $result );
    }
    
    /**
     * Test run_media_cleanup with options
     */
    public function test_run_media_cleanup_with_options() {
        $cleanup = Cleanup::getInstance();
        
        $options = array(
            'orphaned_media' => true,
            'unused_media' => true,
            'duplicate_media' => true,
            'media_age_days' => 30
        );
        
        $result = $cleanup->run_media_cleanup( $options );
        $this->assertIsArray( $result );
    }
    
    /**
     * Test run_comments_cleanup method
     */
    public function test_run_comments_cleanup_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'run_comments_cleanup' ) );
    }
    
    /**
     * Test run_comments_cleanup returns array
     */
    public function test_run_comments_cleanup_returns_array() {
        $cleanup = Cleanup::getInstance();
        $result = $cleanup->run_comments_cleanup();
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'cleaned', $result );
    }
    
    /**
     * Test run_comments_cleanup with options
     */
    public function test_run_comments_cleanup_with_options() {
        $cleanup = Cleanup::getInstance();
        
        $options = array(
            'spam_comments' => true,
            'unapproved_comments' => false,
            'duplicate_comments' => true,
            'old_comments' => 30
        );
        
        $result = $cleanup->run_comments_cleanup( $options );
        $this->assertIsArray( $result );
    }
    
    /**
     * Test cleanup_empty_posts method
     */
    public function test_cleanup_empty_posts_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'cleanup_empty_posts' ) );
    }
    
    /**
     * Test cleanup_empty_posts returns array
     */
    public function test_cleanup_empty_posts_returns_array() {
        $cleanup = Cleanup::getInstance();
        $result = $cleanup->cleanup_empty_posts();
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'cleaned_count', $result );
        $this->assertArrayHasKey( 'posts_type', $result );
        $this->assertArrayHasKey( 'message', $result );
    }
    
    /**
     * Test cleanup_empty_posts with options
     */
    public function test_cleanup_empty_posts_with_options() {
        $cleanup = Cleanup::getInstance();
        
        $options = array(
            'post_types' => array( 'post', 'page' ),
            'post_statuses' => array( 'draft' ),
            'age_days' => 30
        );
        
        $result = $cleanup->cleanup_empty_posts( $options );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'cleaned_count', $result );
    }
    
    /**
     * Test cleanup_duplicate_posts method
     */
    public function test_cleanup_duplicate_posts_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'cleanup_duplicate_posts' ) );
    }
    
    /**
     * Test cleanup_duplicate_posts returns array
     */
    public function test_cleanup_duplicate_posts_returns_array() {
        $cleanup = Cleanup::getInstance();
        $result = $cleanup->cleanup_duplicate_posts();
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'cleaned_count', $result );
        $this->assertArrayHasKey( 'duplicates_found', $result );
        $this->assertArrayHasKey( 'type', $result );
    }
    
    /**
     * Test cleanup_duplicate_posts with options
     */
    public function test_cleanup_duplicate_posts_with_options() {
        $cleanup = Cleanup::getInstance();
        
        $options = array(
            'post_types' => array( 'post', 'page' ),
            'delete_method' => 'keep_newest'
        );
        
        $result = $cleanup->cleanup_duplicate_posts( $options );
        $this->assertIsArray( $result );
    }
    
    /**
     * Test get_orphaned_shortcodes method
     */
    public function test_get_orphaned_shortcodes_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'get_orphaned_shortcodes' ) );
    }
    
    /**
     * Test get_orphaned_shortcodes returns array
     */
    public function test_get_orphaned_shortcodes_returns_array() {
        $cleanup = Cleanup::getInstance();
        $orphaned = $cleanup->get_orphaned_shortcodes();
        $this->assertIsArray( $orphaned );
    }
    
    /**
     * Test run_database_cleanup method
     */
    public function test_run_database_cleanup_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'run_database_cleanup' ) );
    }
    
    /**
     * Test run_database_cleanup returns array
     */
    public function test_run_database_cleanup_returns_array() {
        $cleanup = Cleanup::getInstance();
        $result = $cleanup->run_database_cleanup();
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'cleaned', $result );
    }
    
    /**
     * Test run_content_cleanup method
     */
    public function test_run_content_cleanup_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'run_content_cleanup' ) );
    }
    
    /**
     * Test run_content_cleanup returns array
     */
    public function test_run_content_cleanup_returns_array() {
        $cleanup = Cleanup::getInstance();
        
        $options = array(
            'unused_shortcodes' => true,
            'empty_posts' => false,
            'duplicate_posts' => false
        );
        
        $result = $cleanup->run_content_cleanup( $options );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'cleaned', $result );
    }
    
    /**
     * Test remove_shortcode method
     */
    public function test_remove_shortcode_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'remove_shortcode' ) );
    }
    
    /**
     * Test remove_shortcode returns string
     */
    public function test_remove_shortcode_returns_string() {
        $cleanup = Cleanup::getInstance();
        
        $content = 'Before [shortcode]content[/shortcode] after';
        $result = $cleanup->remove_shortcode( $content, 'shortcode' );
        
        $this->assertIsString( $result );
        $this->assertEquals( 'Before  content  after', $result );
    }
    
    /**
     * Test get_registered_shortcodes method
     */
    public function test_get_registered_shortcodes_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'get_registered_shortcodes' ) );
    }
    
    /**
     * Test get_registered_shortcodes returns array
     */
    public function test_get_registered_shortcodes_returns_array() {
        $cleanup = Cleanup::getInstance();
        $shortcodes = $cleanup->get_registered_shortcodes();
        $this->assertIsArray( $shortcodes );
    }
    
    /**
     * Test cleanup_unused_shortcodes method
     */
    public function test_cleanup_unused_shortcodes_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'cleanup_unused_shortcodes' ) );
    }
    
    /**
     * Test cleanup_unused_shortcodes returns array
     */
    public function test_cleanup_unused_shortcodes_returns_array() {
        $cleanup = Cleanup::getInstance();
        $result = $cleanup->cleanup_unused_shortcodes();
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'cleaned', $result );
    }
    
    /**
     * Test remove_empty_shortcodes method
     */
    public function test_remove_empty_shortcodes_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'remove_empty_shortcodes' ) );
    }
    
    /**
     * Test remove_empty_shortcodes returns array
     */
    public function test_remove_empty_shortcodes_returns_array() {
        $cleanup = Cleanup::getInstance();
        
        $content = 'Content [empty_shortcode][/empty_shortcode] here';
        $result = $cleanup->remove_empty_shortcodes( $content );
        
        $this->assertIsString( $result );
    }
    
    /**
     * Test remove_nested_shortcodes method
     */
    public function test_remove_nested_shortcodes_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'remove_nested_shortcodes' ) );
    }
    
    /**
     * Test remove_nested_shortcodes returns string
     */
    public function test_remove_nested_shortcodes_returns_string() {
        $cleanup = Cleanup::getInstance();
        
        $content = 'Content [outer][inner]text[/inner][/outer] end';
        $result = $cleanup->remove_nested_shortcodes( $content, 'outer' );
        
        $this->assertIsString( $result );
        $this->assertStringNotContainsString( '[outer]', $result );
    }
    
    /**
     * Test remove_shortcode with self-closing shortcode
     */
    public function test_remove_self_closing_shortcode() {
        $cleanup = Cleanup::getInstance();
        
        $content = 'Before [gallery id="123"/] after';
        $result = $cleanup->remove_shortcode( $content, 'gallery' );
        
        $this->assertEquals( 'Before  after', $result );
    }
    
    /**
     * Test remove_shortcode with shortcode containing attributes
     */
    public function test_remove_shortcode_with_attributes() {
        $cleanup = Cleanup::getInstance();
        
        $content = '[button type="primary" size="large" class="btn"]Click Here[/button]';
        $result = $cleanup->remove_shortcode( $content, 'button' );
        
        $this->assertEquals( '', $result );
    }
    
    /**
     * Test remove_shortcode with multiple shortcodes
     */
    public function test_remove_multiple_shortcodes() {
        $cleanup = Cleanup::getInstance();
        
        $content = '[first]one[/first] and [second]two[/second]';
        $result = $cleanup->remove_shortcode( $content, 'first' );
        
        $this->assertStringContainsString( 'one', $result );
        $this->assertStringNotContainsString( '[first]', $result );
    }
    
    /**
     * Test remove_shortcode when shortcode not present
     */
    public function test_remove_nonexistent_shortcode() {
        $cleanup = Cleanup::getInstance();
        
        $content = 'Just regular content without any shortcodes.';
        $result = $cleanup->remove_shortcode( $content, 'nonexistent' );
        
        $this->assertEquals( $content, $result );
    }
    
    /**
     * Test remove_shortcode with empty content
     */
    public function test_remove_shortcode_empty_content() {
        $cleanup = Cleanup::getInstance();
        
        $result = $cleanup->remove_shortcode( '', 'shortcode' );
        $this->assertEquals( '', $result );
    }
    
    /**
     * Test get_shortcode_pattern method
     */
    public function test_get_shortcode_pattern_method_exists() {
        $cleanup = Cleanup::getInstance();
        $this->assertTrue( method_exists( $cleanup, 'get_shortcode_pattern' ) );
    }
    
    /**
     * Test get_shortcode_pattern returns string
     */
    public function test_get_shortcode_pattern_returns_string() {
        $cleanup = Cleanup::getInstance();
        $pattern = $cleanup->get_shortcode_pattern( 'test' );
        
        $this->assertIsString( $pattern );
        $this->assertStringContainsString( 'test', $pattern );
    }
    
    /**
     * Test cleanup statistics structure
     */
    public function test_cleanup_statistics_structure() {
        $cleanup = Cleanup::getInstance();
        $stats = $cleanup->get_cleanup_stats();
        
        $expected_keys = array(
            'orphaned_media',
            'unused_media',
            'spam_comments',
            'unapproved_comments',
            'empty_posts',
            'duplicate_posts',
            'unused_shortcodes'
        );
        
        foreach ( $expected_keys as $key ) {
            $this->assertArrayHasKey( $key, $stats, "Missing key: {$key}" );
        }
    }
    
    /**
     * Test cleanup statistics count format
     */
    public function test_cleanup_statistics_count_format() {
        $cleanup = Cleanup::getInstance();
        $stats = $cleanup->get_cleanup_stats();
        
        foreach ( $stats as $key => $count ) {
            $this->assertIsInt( $count, "Count for {$key} should be integer" );
            $this->assertGreaterThanOrEqual( 0, $count, "Count for {$key} should be non-negative" );
        }
    }
}
