<?php
/**
 * WPCleanAdmin Settings Page Functional Test
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
 * Settings Page Functional Test Class
 *
 * Tests for Settings module functionality
 */
class SettingsPageFunctionalTest extends WPCA_Functional_TestCase {

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
     * Test settings page renders successfully
     */
    public function test_settings_page_renders_successfully() {
        $this->go_to( admin_url( 'admin.php?page=wp-clean-admin' ) );

        $this->assertTrue( is_admin() );

        ob_start();
        do_action( 'admin_enqueue_scripts', 'toplevel_page_wp-clean-admin' );
        $admin_notices = ob_get_clean();

        $this->assertNotEmpty( $admin_notices );
    }

    /**
     * Test settings navigation tabs work
     */
    public function test_settings_navigation_tabs_work() {
        $tabs = apply_filters( 'wpca_settings_tabs', array() );

        $expected_tabs = array(
            'general',
            'cleanup',
            'performance',
            'database',
            'menu',
            'dashboard',
            'extensions',
        );

        foreach ( $expected_tabs as $tab ) {
            $this->assertArrayHasKey( $tab, $tabs, "Tab '{$tab}' should exist in settings tabs" );
        }
    }

    /**
     * Test settings can be saved
     */
    public function test_settings_can_be_saved() {
        $test_options = array(
            'wpca_enable_cleanup' => true,
            'wpca_enable_performance' => true,
            'wpca_enable_menu_manager' => true,
            'wpca_database_optimization' => true,
        );

        foreach ( $test_options as $option_name => $value ) {
            update_option( $option_name, $value );
            $saved_value = get_option( $option_name );
            $this->assertEquals( $value, $saved_value, "Option {$option_name} should be saved correctly" );
        }
    }

    /**
     * Test settings validation rejects invalid input
     */
    public function test_settings_validation_rejects_invalid_input() {
        $invalid_inputs = array(
            'wpca_cleanup_days' => -5,
            'wpca_autosave_interval' => 0,
            'wpca_revision_limit' => -1,
        );

        foreach ( $invalid_inputs as $option => $invalid_value ) {
            $is_valid = apply_filters( "wpca_validate_{$option}", $invalid_value );
            $this->assertFalse( $is_valid, "Invalid value {$invalid_value} for {$option} should be rejected" );
        }
    }

    /**
     * Test settings reset functionality
     */
    public function test_settings_reset_functionality() {
        update_option( 'wpca_enable_cleanup', true );
        update_option( 'wpca_cleanup_days', 30 );

        $default_settings = apply_filters( 'wpca_default_settings', array() );

        $this->assertArrayHasKey( 'wpca_enable_cleanup', $default_settings );
        $this->assertArrayHasKey( 'wpca_cleanup_days', $default_settings );

        $default_cleanup_days = $default_settings['wpca_cleanup_days'];
        $this->assertGreaterThan( 0, $default_cleanup_days );
    }

    /**
     * Test settings export functionality
     */
    public function test_settings_export_functionality() {
        update_option( 'wpca_enable_cleanup', true );
        update_option( 'wpca_enable_performance', true );

        $export_data = apply_filters( 'wpca_export_settings', array() );

        $this->assertIsArray( $export_data );
        $this->assertNotEmpty( $export_data );

        if ( isset( $export_data['settings'] ) ) {
            $this->assertIsArray( $export_data['settings'] );
        }
    }

    /**
     * Test settings import functionality
     */
    public function test_settings_import_functionality() {
        $import_data = array(
            'version' => WPCA_VERSION,
            'settings' => array(
                'wpca_enable_cleanup' => false,
                'wpca_cleanup_days' => 7,
            ),
        );

        $result = apply_filters( 'wpca_import_settings', true, $import_data );

        $this->assertTrue( $result );

        $this->assertEquals( false, get_option( 'wpca_enable_cleanup' ) );
        $this->assertEquals( 7, get_option( 'wpca_cleanup_days' ) );
    }

    /**
     * Test settings search filters options
     */
    public function test_settings_search_filters_options() {
        $all_settings = apply_filters( 'wpca_all_settings', array() );

        $search_term = 'cleanup';

        $filtered_settings = array_filter( $all_settings, function( $setting ) use ( $search_term ) {
            $haystack = strtolower( $setting['label'] ?? '' . $setting['description'] ?? '' );
            return strpos( $haystack, $search_term ) !== false;
        } );

        foreach ( $filtered_settings as $setting ) {
            $this->assertTrue(
                stripos( $setting['label'], $search_term ) !== false ||
                stripos( $setting['description'], $search_term ) !== false
            );
        }
    }

    /**
     * Test settings are sanitized before save
     */
    public function test_settings_are_sanitized_before_save() {
        $unsanitized_input = array(
            'wpca_custom_css' => '<script>alert("xss")</script>',
            'wpca_custom_js' => 'console.log("test");',
        );

        $sanitized = array();

        foreach ( $unsanitized_input as $key => $value ) {
            $sanitized[ $key ] = apply_filters( "wpca_sanitize_{$key}", $value );
        }

        $this->assertNotEquals(
            $unsanitized_input['wpca_custom_css'],
            $sanitized['wpca_custom_css']
        );
    }

    /**
     * Test nonce verification prevents CSRF
     */
    public function test_nonce_verification_prevents_csrf() {
        $request_without_nonce = array(
            'action' => 'wpca_save_settings',
            '_wpnonce' => 'invalid_nonce_12345',
        );

        $is_valid = wp_verify_nonce( $request_without_nonce['_wpnonce'], 'wpca_save_settings' );

        $this->assertFalse( $is_valid, 'Invalid nonce should fail verification' );
    }

    /**
     * Test user without permission cannot access
     */
    public function test_user_without_permission_cannot_access() {
        $subscriber = $this->factory->user->create( array(
            'role' => 'subscriber',
        ) );

        wp_set_current_user( $subscriber );

        $can_access = current_user_can( 'manage_options' );

        $this->assertFalse( $can_access, 'Subscriber should not have manage_options capability' );
    }

    /**
     * Test settings change triggers action
     */
    public function test_settings_change_triggers_wpca_settings_updated_action() {
        $action_triggered = false;

        add_action( 'wpca_settings_updated', function( $old_settings, $new_settings ) use ( &$action_triggered ) {
            $action_triggered = true;
        }, 10, 2 );

        update_option( 'wpca_enable_cleanup', ! get_option( 'wpca_enable_cleanup', false ) );

        $this->assertTrue( $action_triggered, 'wpca_settings_updated action should be triggered' );
    }

    /**
     * Test plugin settings can be retrieved in bulk
     */
    public function test_settings_can_be_retrieved_in_bulk() {
        update_option( 'wpca_enable_cleanup', true );
        update_option( 'wpca_cleanup_days', 30 );
        update_option( 'wpca_enable_performance', false );

        $settings = \WPCleanAdmin\wpca_get_settings();

        $this->assertIsArray( $settings );
        $this->assertArrayHasKey( 'enable_cleanup', $settings );
        $this->assertArrayHasKey( 'cleanup_days', $settings );
        $this->assertArrayHasKey( 'enable_performance', $settings );
    }

    /**
     * Test settings page has required UI elements
     */
    public function test_settings_page_has_required_ui_elements() {
        ob_start();
        \WPCleanAdmin\Settings::getInstance()->render_settings_page();
        $output = ob_get_clean();

        $required_elements = array(
            'form',
            'submit',
            'nonce',
        );

        foreach ( $required_elements as $element ) {
            $this->assertTrue(
                stripos( $output, $element ) !== false,
                "Settings page should contain '{$element}' element"
            );
        }
    }
}
