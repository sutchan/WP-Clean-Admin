<?php
/**
 * Integration Test for Cleanup Module
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @since 1.7.15
 */

require_once __DIR__ . '/integration/WPCA_Integration_TestCase.php';

/**
 * Integration test for Cleanup module
 *
 * Tests the complete cleanup functionality with WordPress integration
 */
class CleanupIntegrationTest extends WPCA_Integration_TestCase {
    
    /**
     * Test cleanup module initialization
     */
    public function test_cleanup_initialization() {
        $this->simulate_admin_page_load();
        
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $this->assertInstanceOf( \WPCleanAdmin\Cleanup::class, $cleanup );
    }
    
    /**
     * Test media cleanup functionality
     */
    public function test_media_cleanup_functionality() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        // Test that run_media_cleanup method exists and returns expected structure
        $result = $cleanup->run_media_cleanup();
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'cleaned', $result );
        $this->assertArrayHasKey( 'orphaned_media', $result['cleaned'] );
        $this->assertArrayHasKey( 'unused_media', $result['cleaned'] );
        $this->assertArrayHasKey( 'duplicate_media', $result['cleaned'] );
    }
    
    /**
     * Test media cleanup with options
     */
    public function test_media_cleanup_with_options() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $options = array(
            'orphaned_media' => true,
            'unused_media' => true,
            'duplicate_media' => true,
            'media_age_days' => 30
        );
        
        $result = $cleanup->run_media_cleanup( $options );
        
        $this->assertIsArray( $result );
        $this->assertTrue( $result['success'] );
    }
    
    /**
     * Test comments cleanup functionality
     */
    public function test_comments_cleanup_functionality() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $result = $cleanup->run_comments_cleanup();
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'cleaned', $result );
        $this->assertArrayHasKey( 'spam_comments', $result['cleaned'] );
        $this->assertArrayHasKey( 'unapproved_comments', $result['cleaned'] );
        $this->assertArrayHasKey( 'old_comments', $result['cleaned'] );
        $this->assertArrayHasKey( 'duplicate_comments', $result['cleaned'] );
    }
    
    /**
     * Test comments cleanup with selective options
     */
    public function test_comments_cleanup_selective_options() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $options = array(
            'spam_comments' => true,
            'unapproved_comments' => false,
            'duplicate_comments' => false,
            'old_comments' => 0 // Disabled
        );
        
        $result = $cleanup->run_comments_cleanup( $options );
        
        $this->assertIsArray( $result );
        $this->assertTrue( $result['success'] );
    }
    
    /**
     * Test empty posts cleanup
     */
    public function test_empty_posts_cleanup() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $result = $cleanup->cleanup_empty_posts();
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'cleaned_count', $result );
        $this->assertArrayHasKey( 'posts_type', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertEquals( 'empty_posts', $result['posts_type'] );
    }
    
    /**
     * Test empty posts cleanup with filters
     */
    public function test_empty_posts_cleanup_with_filters() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $options = array(
            'post_types' => array( 'post' ),
            'post_statuses' => array( 'draft' ),
            'age_days' => 30
        );
        
        $result = $cleanup->cleanup_empty_posts( $options );
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'cleaned_count', $result );
    }
    
    /**
     * Test duplicate posts cleanup
     */
    public function test_duplicate_posts_cleanup() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $result = $cleanup->cleanup_duplicate_posts();
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'cleaned_count', $result );
        $this->assertArrayHasKey( 'duplicates_found', $result );
        $this->assertArrayHasKey( 'type', $result );
        $this->assertEquals( 'duplicate_posts', $result['type'] );
    }
    
    /**
     * Test duplicate posts cleanup with delete method
     */
    public function test_duplicate_posts_cleanup_with_delete_method() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $options = array(
            'post_types' => array( 'post', 'page' ),
            'delete_method' => 'keep_newest'
        );
        
        $result = $cleanup->cleanup_duplicate_posts( $options );
        
        $this->assertIsArray( $result );
    }
    
    /**
     * Test orphaned shortcodes detection
     */
    public function test_orphaned_shortcodes_detection() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $orphaned = $cleanup->get_orphaned_shortcodes();
        
        $this->assertIsArray( $orphaned );
    }
    
    /**
     * Test shortcode removal functionality
     */
    public function test_shortcode_removal() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        // Test basic shortcode removal
        $content = 'Before [shortcode]content[/shortcode] after';
        $result = $cleanup->remove_shortcode( $content, 'shortcode' );
        
        $this->assertEquals( 'Before  content  after', $result );
        
        // Test self-closing shortcode
        $self_closing = 'Content [gallery id="123"/] end';
        $result_closing = $cleanup->remove_shortcode( $self_closing, 'gallery' );
        
        $this->assertEquals( 'Content  end', $result_closing );
    }
    
    /**
     * Test cleanup unused shortcodes
     */
    public function test_cleanup_unused_shortcodes() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $result = $cleanup->cleanup_unused_shortcodes();
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'cleaned', $result );
    }
    
    /**
     * Test remove empty shortcodes
     */
    public function test_remove_empty_shortcodes() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $content = 'Text [empty_shortcode][/empty_shortcode] here';
        $result = $cleanup->remove_empty_shortcodes( $content );
        
        $this->assertEquals( 'Text  here', $result );
    }
    
    /**
     * Test remove nested shortcodes
     */
    public function test_remove_nested_shortcodes() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $nested = 'Content [outer][inner]text[/inner][/outer] end';
        $result = $cleanup->remove_nested_shortcodes( $nested, 'outer' );
        
        $this->assertStringNotContainsString( '[outer]', $result );
        $this->assertStringNotContainsString( '[/outer]', $result );
    }
    
    /**
     * Test shortcode pattern generation
     */
    public function test_shortcode_pattern_generation() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $pattern = $cleanup->get_shortcode_pattern( 'test' );
        
        $this->assertIsString( $pattern );
        $this->assertStringContainsString( 'test', $pattern );
    }
    
    /**
     * Test get registered shortcodes
     */
    public function test_get_registered_shortcodes() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $shortcodes = $cleanup->get_registered_shortcodes();
        
        $this->assertIsArray( $shortcodes );
    }
    
    /**
     * Test run content cleanup
     */
    public function test_run_content_cleanup() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $options = array(
            'unused_shortcodes' => true,
            'empty_posts' => true,
            'duplicate_posts' => true
        );
        
        $result = $cleanup->run_content_cleanup( $options );
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'cleaned', $result );
    }
    
    /**
     * Test run database cleanup
     */
    public function test_run_database_cleanup() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $result = $cleanup->run_database_cleanup();
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'cleaned', $result );
    }
    
    /**
     * Test get cleanup statistics
     */
    public function test_get_cleanup_statistics() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $stats = $cleanup->get_cleanup_stats();
        
        $this->assertIsArray( $stats );
        
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
            $this->assertArrayHasKey( $key, $stats );
            $this->assertIsInt( $stats[ $key ] );
        }
    }
    
    /**
     * Test shortcode removal edge cases
     */
    public function test_shortcode_removal_edge_cases() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        // Empty content
        $empty = $cleanup->remove_shortcode( '', 'test' );
        $this->assertEquals( '', $empty );
        
        // Non-existent shortcode
        $no_shortcode = 'Just content';
        $result = $cleanup->remove_shortcode( $no_shortcode, 'nonexistent' );
        $this->assertEquals( $no_shortcode, $result );
        
        // Multiple shortcodes of same type
        $multiple = '[test]first[/test] and [test]second[/test]';
        $result = $cleanup->remove_shortcode( $multiple, 'test' );
        $this->assertEquals( '  first  and  second ', $result );
    }
    
    /**
     * Test cleanup statistics values
     */
    public function test_cleanup_statistics_values() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        
        $stats = $cleanup->get_cleanup_stats();
        
        foreach ( $stats as $key => $count ) {
            $this->assertGreaterThanOrEqual( 0, $count, "{$key} count should be non-negative" );
            $this->assertIsInt( $count, "{$key} count should be integer" );
        }
    }
}
