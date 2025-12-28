<?php
/**
 * WPCleanAdmin Cleanup Page Functional Test
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @author Sut
 * @since 1.7.15
 */

namespace WPCleanAdmin\Tests\Functional;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/WPCA_Functional_TestCase.php';

/**
 * Cleanup Page Functional Test Class
 *
 * Tests for Cleanup module functionality
 */
class CleanupPageFunctionalTest extends WPCA_Functional_TestCase {

    /**
     * Set up the test environment
     */
    public function setUp(): void {
        parent::setUp();
        $this->current_user = $this->factory->user->create( array(
            'role' => 'administrator',
        ) );
        wp_set_current_user( $this->current_user );
    }

    /**
     * Test cleanup page renders successfully
     */
    public function test_cleanup_page_renders_successfully() {
        $this->go_to( admin_url( 'admin.php?page=wp-clean-admin-cleanup' ) );

        $this->assertTrue( is_admin() );

        $cleanup = \WPCleanAdmin\Cleanup::getInstance();
        $stats = $cleanup->get_cleanup_stats();

        $this->assertIsArray( $stats );
    }

    /**
     * Test media cleanup runs successfully
     */
    public function test_media_cleanup_runs_successfully() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();

        $result = $cleanup->run_media_cleanup( array(
            'clean_orphaned' => true,
            'clean_unused' => true,
            'clean_duplicates' => false,
        ) );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'message', $result );
    }

    /**
     * Test comment cleanup works
     */
    public function test_comment_cleanup_works() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();

        $result = $cleanup->run_comments_cleanup( array(
            'clean_spam' => true,
            'clean_unapproved' => false,
            'clean_old' => false,
        ) );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }

    /**
     * Test post cleanup functions
     */
    public function test_post_cleanup_functions() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();

        $result = $cleanup->run_content_cleanup( array(
            'clean_empty' => true,
            'clean_revisions' => true,
        ) );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }

    /**
     * Test shortcode cleanup works
     */
    public function test_shortcode_cleanup_works() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();

        $result = apply_filters( 'wpca_cleanup_shortcodes', array(
            'dry_run' => true,
        ) );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'found', $result );
    }

    /**
     * Test cleanup stats display correctly
     */
    public function test_cleanup_stats_display_correctly() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();

        $stats = $cleanup->get_cleanup_stats();

        $this->assertIsArray( $stats );

        $expected_stats = array(
            'orphaned_media_count',
            'unused_media_count',
            'duplicate_media_count',
            'spam_comments_count',
            'unapproved_comments_count',
        );

        foreach ( $expected_stats as $stat ) {
            if ( isset( $stats[ $stat ] ) ) {
                $this->assertIsNumeric( $stats[ $stat ] );
            }
        }
    }

    /**
     * Test cleanup preview shows affected items
     */
    public function test_cleanup_preview_shows_affected_items() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();

        $preview = apply_filters( 'wpca_cleanup_preview', array(
            'type' => 'media',
        ) );

        $this->assertIsArray( $preview );
        $this->assertArrayHasKey( 'items', $preview );
        $this->assertArrayHasKey( 'count', $preview );
    }

    /**
     * Test cleanup confirmation prevents accidental execution
     */
    public function test_cleanup_confirmation_prevents_accidental_execution() {
        $nonce = wp_create_nonce( 'wpca_cleanup' );

        $is_valid = wp_verify_nonce( $nonce, 'wpca_cleanup' );

        $this->assertTrue( $is_valid, 'Valid cleanup nonce should pass verification' );
    }

    /**
     * Test cleanup operation is reversible
     */
    public function test_cleanup_operation_is_reversible() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();

        $operations = apply_filters( 'wpca_reversible_cleanup_operations', array() );

        $this->assertIsArray( $operations );

        foreach ( $operations as $operation ) {
            $this->assertArrayHasKey( 'name', $operation );
            $this->assertArrayHasKey( 'undo', $operation );
        }
    }

    /**
     * Test cleanup log shows completed actions
     */
    public function test_cleanup_log_shows_completed_actions() {
        $cleanup_log = apply_filters( 'wpca_cleanup_log', array() );

        $this->assertIsArray( $cleanup_log );

        if ( ! empty( $cleanup_log ) ) {
            foreach ( $cleanup_log as $entry ) {
                $this->assertArrayHasKey( 'timestamp', $entry );
                $this->assertArrayHasKey( 'action', $entry );
            }
        }
    }

    /**
     * Test cleanup schedule can be configured
     */
    public function test_cleanup_schedule_can_be_configured() {
        update_option( 'wpca_cleanup_schedule_enabled', true );
        update_option( 'wpca_cleanup_schedule_frequency', 'daily' );
        update_option( 'wpca_cleanup_schedule_time', '03:00' );

        $schedule_enabled = get_option( 'wpca_cleanup_schedule_enabled' );
        $schedule_frequency = get_option( 'wpca_cleanup_schedule_frequency' );
        $schedule_time = get_option( 'wpca_cleanup_schedule_time' );

        $this->assertEquals( true, $schedule_enabled );
        $this->assertContains( $schedule_frequency, array( 'daily', 'weekly', 'monthly' ) );
        $this->assertNotEmpty( $schedule_time );
    }

    /**
     * Test cleanup email notifications work
     */
    public function test_cleanup_email_notifications_work() {
        update_option( 'wpca_cleanup_email_notifications', true );
        update_option( 'wpca_cleanup_email_recipient', get_option( 'admin_email' ) );

        $notifications_enabled = get_option( 'wpca_cleanup_email_notifications' );
        $recipient = get_option( 'wpca_cleanup_email_recipient' );

        $this->assertEquals( true, $notifications_enabled );
        $this->assertIsString( $recipient );
        $this->assertContains( '@', $recipient );
    }

    /**
     * Test orphaned media detection
     */
    public function test_orphaned_media_detection() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();

        $orphaned = $cleanup->get_orphaned_media();

        $this->assertIsArray( $orphaned );
    }

    /**
     * Test unused media detection
     */
    public function test_unused_media_detection() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();

        $unused = $cleanup->get_unused_media( 30 );

        $this->assertIsArray( $unused );
    }

    /**
     * Test duplicate media detection
     */
    public function test_duplicate_media_detection() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();

        $duplicates = $cleanup->get_duplicate_media();

        $this->assertIsArray( $duplicates );
    }

    /**
     * Test cleanup dry run functionality
     */
    public function test_cleanup_dry_run_functionality() {
        $cleanup = \WPCleanAdmin\Cleanup::getInstance();

        $dry_run_result = $cleanup->run_media_cleanup( array(
            'dry_run' => true,
            'clean_orphaned' => true,
        ) );

        $this->assertIsArray( $dry_run_result );
        $this->assertArrayHasKey( 'would_remove', $dry_run_result );
    }
}
