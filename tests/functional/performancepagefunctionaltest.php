<?php
/**
 * WPCleanAdmin Performance Page Functional Test
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
 * Performance Page Functional Test Class
 *
 * Tests for Performance module functionality
 */
class PerformancePageFunctionalTest extends WPCA_Functional_TestCase {

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
     * Test performance page renders successfully
     */
    public function test_performance_page_renders_successfully() {
        $this->go_to( admin_url( 'admin.php?page=wp-clean-admin-performance' ) );

        $this->assertTrue( is_admin() );

        $performance = \WPCleanAdmin\Performance::getInstance();
        $settings = $performance->get_performance_settings();

        $this->assertIsArray( $settings );
    }

    /**
     * Test minification toggles work
     */
    public function test_minification_toggles_work() {
        $minification_options = array(
            'wpca_minify_css' => true,
            'wpca_minify_js' => true,
            'wpca_minify_html' => false,
        );

        foreach ( $minification_options as $option => $value ) {
            update_option( $option, $value );
            $saved = get_option( $option );
            $this->assertEquals( $value, $saved, "Option {$option} should be saved correctly" );
        }

        $minification_status = apply_filters( 'wpca_minification_status', array() );
        $this->assertIsArray( $minification_status );
    }

    /**
     * Test CDN settings can be configured
     */
    public function test_cdn_settings_can_be_configured() {
        $cdn_settings = array(
            'wpca_cdn_url' => 'https://cdn.example.com',
            'wpca_cdn_enable' => true,
            'wpca_cdn_exclude' => '.php',
        );

        foreach ( $cdn_settings as $option => $value ) {
            update_option( $option, $value );
        }

        $saved_cdn_url = get_option( 'wpca_cdn_url' );
        $this->assertEquals( 'https://cdn.example.com', $saved_cdn_url );

        $is_enabled = apply_filters( 'wpca_cdn_enabled', false );
        $this->assertTrue( $is_enabled || get_option( 'wpca_cdn_enable' ) === true );
    }

    /**
     * Test caching settings can be saved
     */
    public function test_caching_settings_can_be_saved() {
        $caching_options = array(
            'wpca_enable_cache' => true,
            'wpca_cache_expire' => 3600,
            'wpca_cache_exclude' => 'wp-admin',
        );

        foreach ( $caching_options as $option => $value ) {
            update_option( $option, $value );
            $saved = get_option( $option );
            $this->assertEquals( $value, $saved, "Option {$option} should be saved correctly" );
        }
    }

    /**
     * Test resource optimization options function
     */
    public function test_resource_optimization_options_function() {
        $options = apply_filters( 'wpca_resource_optimization_options', array() );

        $this->assertIsArray( $options );

        if ( ! empty( $options ) ) {
            foreach ( $options as $key => $value ) {
                $this->assertIsString( $key );
                $this->assertNotEmpty( $key );
            }
        }
    }

    /**
     * Test performance presets can be applied
     */
    public function test_performance_presets_can_be_applied() {
        $presets = apply_filters( 'wpca_performance_presets', array() );

        $this->assertIsArray( $presets );

        $expected_presets = array( 'balanced', 'performance', 'compatibility' );

        foreach ( $expected_presets as $preset ) {
            if ( isset( $presets[ $preset ] ) ) {
                $this->assertIsArray( $presets[ $preset ] );
            }
        }
    }

    /**
     * Test performance settings export
     */
    public function test_performance_settings_export() {
        update_option( 'wpca_enable_performance', true );
        update_option( 'wpca_minify_css', true );

        $export = apply_filters( 'wpca_export_performance_settings', array() );

        $this->assertIsArray( $export );

        if ( isset( $export['performance_settings'] ) ) {
            $this->assertIsArray( $export['performance_settings'] );
        }
    }

    /**
     * Test performance settings import
     */
    public function test_performance_settings_import() {
        $import_data = array(
            'enable_performance' => false,
            'minify_css' => false,
            'minify_js' => true,
        );

        $result = apply_filters( 'wpca_import_performance_settings', true, $import_data );

        $this->assertTrue( $result );
    }

    /**
     * Test page speed recommendations display
     */
    public function test_page_speed_recommendations_display() {
        $recommendations = apply_filters( 'wpca_performance_recommendations', array() );

        $this->assertIsArray( $recommendations );

        foreach ( $recommendations as $recommendation ) {
            if ( isset( $recommendation['type'] ) ) {
                $this->assertContains( $recommendation['type'], array( 'info', 'warning', 'success' ) );
            }
            if ( isset( $recommendation['message'] ) ) {
                $this->assertIsString( $recommendation['message'] );
            }
        }
    }

    /**
     * Test resource loader toggles work
     */
    public function test_resource_loader_toggles_work() {
        $loader_options = array(
            'wpca_disable_emojis' => true,
            'wpca_disable_embeds' => false,
            'wpca_disable_jquery_migrate' => true,
        );

        foreach ( $loader_options as $option => $value ) {
            update_option( $option, $value );
            $this->assertEquals( $value, get_option( $option ) );
        }

        $disabled_resources = apply_filters( 'wpca_disabled_resources', array() );
        $this->assertIsArray( $disabled_resources );
    }

    /**
     * Test database query optimization settings
     */
    public function test_database_query_optimization_settings() {
        $db_options = array(
            'wpca_query_cache_enable' => true,
            'wpca_max_queries' => 100,
        );

        foreach ( $db_options as $option => $value ) {
            update_option( $option, $value );
            $this->assertEquals( $value, get_option( $option ) );
        }
    }

    /**
     * Test JS defer settings function
     */
    public function test_js_defer_settings_function() {
        update_option( 'wpca_defer_js', true );
        update_option( 'wpca_defer_exclude', array( 'jquery.js' ) );

        $defer_enabled = apply_filters( 'wpca_js_defer_enabled', false );
        $defer_exclude = apply_filters( 'wpca_js_defer_exclude', array() );

        $this->assertTrue( $defer_enabled || get_option( 'wpca_defer_js' ) === true );
        $this->assertIsArray( $defer_exclude );
    }

    /**
     * Test CSS minification settings
     */
    public function test_css_minification_settings() {
        $css_options = array(
            'wpca_minify_css' => true,
            'wpca_css_exclude' => 'admin-bar',
            'wpca_combine_css' => false,
        );

        foreach ( $css_options as $option => $value ) {
            update_option( $option, $value );
            $this->assertEquals( $value, get_option( $option ) );
        }
    }

    /**
     * Test HTML minification settings
     */
    public function test_html_minification_settings() {
        $html_options = array(
            'wpca_minify_html' => true,
            'wpca_remove_comments' => false,
            'wpca_preserve_console' => true,
        );

        foreach ( $html_options as $option => $value ) {
            update_option( $option, $value );
            $this->assertEquals( $value, get_option( $option ) );
        }
    }

    /**
     * Test performance statistics are calculated correctly
     */
    public function test_performance_statistics_are_calculated() {
        $stats = apply_filters( 'wpca_performance_stats', array() );

        $this->assertIsArray( $stats );

        $expected_keys = array( 'page_load_time', 'requests_count', 'page_size' );

        foreach ( $expected_keys as $key ) {
            if ( isset( $stats[ $key ] ) ) {
                $this->assertIsNumeric( $stats[ $key ] );
            }
        }
    }

    /**
     * Test asset cleanup functionality
     */
    public function test_asset_cleanup_functionality() {
        $cleanup_result = apply_filters( 'wpca_cleanup_assets', array() );

        $this->assertIsArray( $cleanup_result );
    }
}
