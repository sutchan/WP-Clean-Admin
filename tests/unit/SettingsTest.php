<?php
/**
 * Unit tests for Settings class
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
use WPCleanAdmin\Settings;

/**
 * Test class for Settings
 * @covers Settings
 */
class SettingsTest extends TestCase {
    
    /**
     * Test instance creation
     */
    public function test_instance_creation() {
        $settings = Settings::getInstance();
        $this->assertInstanceOf( Settings::class, $settings );
    }
    
    /**
     * Test singleton pattern
     */
    public function test_singleton_pattern() {
        $settings1 = Settings::getInstance();
        $settings2 = Settings::getInstance();
        $this->assertSame( $settings1, $settings2 );
    }
    
    /**
     * Test register_settings method exists and is callable
     */
    public function test_register_settings_method() {
        $settings = Settings::getInstance();
        $this->assertTrue( method_exists( $settings, 'register_settings' ) );
        $this->assertTrue( is_callable( array( $settings, 'register_settings' ) ) );
    }
    
    /**
     * Test render_settings_page method exists
     */
    public function test_render_settings_page_method() {
        $settings = Settings::getInstance();
        $this->assertTrue( method_exists( $settings, 'render_settings_page' ) );
    }
    
    /**
     * Test validate_settings method exists and handles input
     */
    public function test_validate_settings_method() {
        $settings = Settings::getInstance();
        $this->assertTrue( method_exists( $settings, 'validate_settings' ) );
        
        // Test with empty input
        $input = array();
        $result = $settings->validate_settings( $input );
        $this->assertIsArray( $result );
    }
    
    /**
     * Test validate_settings sanitizes input
     */
    public function test_validate_settings_sanitizes_input() {
        $settings = Settings::getInstance();
        
        // Test with potentially unsafe input
        $input = array(
            'general' => array(
                'clean_admin_bar' => '<script>alert("xss")</script>',
            ),
            'security' => array(
                'two_factor_auth' => true,
            ),
        );
        
        $result = $settings->validate_settings( $input );
        $this->assertIsArray( $result );
    }
    
    /**
     * Test render methods exist
     */
    public function test_render_methods_exist() {
        $settings = Settings::getInstance();
        
        $render_methods = array(
            'render_general_settings_section',
            'render_clean_admin_bar_field',
            'render_remove_wp_logo_field',
            'render_cleanup_settings_section',
            'render_remove_dashboard_widgets_field',
            'render_simplify_admin_menu_field',
            'render_performance_settings_section',
            'render_optimize_database_field',
            'render_clean_transients_field',
            'render_disable_emojis_field',
            'render_security_settings_section',
            'render_hide_wp_version_field',
            'render_two_factor_auth_field',
            'render_role_menu_settings_section',
            'render_role_based_restrictions_field',
            'render_role_menu_restrictions_field',
        );
        
        foreach ( $render_methods as $method ) {
            $this->assertTrue( method_exists( $settings, $method ), "Method {$method} should exist" );
        }
    }
    
    /**
     * Test enqueue_scripts method exists
     */
    public function test_enqueue_scripts_method() {
        $settings = Settings::getInstance();
        $this->assertTrue( method_exists( $settings, 'enqueue_scripts' ) );
    }
    
    /**
     * Test register_settings_page method exists
     */
    public function test_register_settings_page_method() {
        $settings = Settings::getInstance();
        $this->assertTrue( method_exists( $settings, 'register_settings_page' ) );
    }
}
