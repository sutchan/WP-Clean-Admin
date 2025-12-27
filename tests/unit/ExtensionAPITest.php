<?php
/**
 * Unit tests for Extension_API class
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
use WPCleanAdmin\Extension_API;

/**
 * Test class for Extension_API
 * @covers Extension_API
 */
class ExtensionAPITest extends TestCase {
    
    /**
     * Test instance creation
     */
    public function test_instance_creation() {
        $api = Extension_API::getInstance();
        $this->assertInstanceOf( Extension_API::class, $api );
    }
    
    /**
     * Test singleton pattern
     */
    public function test_singleton_pattern() {
        $api1 = Extension_API::getInstance();
        $api2 = Extension_API::getInstance();
        $this->assertSame( $api1, $api2 );
    }
    
    /**
     * Test register_extension method exists
     */
    public function test_register_extension_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'register_extension' ) );
    }
    
    /**
     * Test unregister_extension method exists
     */
    public function test_unregister_extension_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'unregister_extension' ) );
    }
    
    /**
     * Test activate_extension method exists
     */
    public function test_activate_extension_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'activate_extension' ) );
    }
    
    /**
     * Test deactivate_extension method exists
     */
    public function test_deactivate_extension_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'deactivate_extension' ) );
    }
    
    /**
     * Test get_extensions method exists
     */
    public function test_get_extensions_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'get_extensions' ) );
    }
    
    /**
     * Test get_extension method exists
     */
    public function test_get_extension_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'get_extension' ) );
    }
    
    /**
     * Test add_hook method exists
     */
    public function test_add_hook_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'add_hook' ) );
    }
    
    /**
     * Test add_filter method exists
     */
    public function test_add_filter_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'add_filter' ) );
    }
    
    /**
     * Test register_menu_item method exists
     */
    public function test_register_menu_item_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'register_menu_item' ) );
    }
    
    /**
     * Test register_settings_section method exists
     */
    public function test_register_settings_section_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'register_settings_section' ) );
    }
    
    /**
     * Test register_settings_field method exists
     */
    public function test_register_settings_field_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'register_settings_field' ) );
    }
    
    /**
     * Test get_api_version method exists
     */
    public function test_get_api_version_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'get_api_version' ) );
    }
    
    /**
     * Test get_api_version returns version string
     */
    public function test_get_api_version_returns_string() {
        $api = Extension_API::getInstance();
        
        $version = $api->get_api_version();
        
        $this->assertIsString( $version );
        $this->assertEquals( '1.0.0', $version );
    }
    
    /**
     * Test get_extension returns null for non-existent extension
     */
    public function test_get_extension_returns_null_for_nonexistent() {
        $api = Extension_API::getInstance();
        
        $extension = $api->get_extension( 'nonexistent_extension' );
        
        $this->assertNull( $extension );
    }
    
    /**
     * Test get_extensions returns empty array initially
     */
    public function test_get_extensions_returns_empty_initially() {
        $api = Extension_API::getInstance();
        
        $extensions = $api->get_extensions();
        
        $this->assertIsArray( $extensions );
        $this->assertEmpty( $extensions );
    }
    
    /**
     * Test get_extensions with status filter
     */
    public function test_get_extensions_with_status_filter() {
        $api = Extension_API::getInstance();
        
        $all = $api->get_extensions( 'all' );
        $active = $api->get_extensions( 'active' );
        $inactive = $api->get_extensions( 'inactive' );
        
        $this->assertIsArray( $all );
        $this->assertIsArray( $active );
        $this->assertIsArray( $inactive );
    }
    
    /**
     * Test get_extension_count method exists
     */
    public function test_get_extension_count_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'get_extension_count' ) );
    }
    
    /**
     * Test get_extension_count returns zero initially
     */
    public function test_get_extension_count_returns_zero_initially() {
        $api = Extension_API::getInstance();
        
        $count = $api->get_extension_count();
        
        $this->assertEquals( 0, $count );
    }
    
    /**
     * Test is_extension_active method exists
     */
    public function test_is_extension_active_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'is_extension_active' ) );
    }
    
    /**
     * Test is_extension_active returns false for non-existent
     */
    public function test_is_extension_active_returns_false_for_nonexistent() {
        $api = Extension_API::getInstance();
        
        $result = $api->is_extension_active( 'nonexistent' );
        
        $this->assertFalse( $result );
    }
    
    /**
     * Test get_extension_info method exists
     */
    public function test_get_extension_info_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'get_extension_info' ) );
    }
    
    /**
     * Test get_extension_info returns empty for non-existent
     */
    public function test_get_extension_info_returns_empty_for_nonexistent() {
        $api = Extension_API::getInstance();
        
        $info = $api->get_extension_info( 'nonexistent' );
        
        $this->assertIsArray( $info );
        $this->assertEmpty( $info );
    }
    
    /**
     * Test export_extensions method exists
     */
    public function test_export_extensions_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'export_extensions' ) );
    }
    
    /**
     * Test export_extensions returns expected structure
     */
    public function test_export_extensions_returns_structure() {
        $api = Extension_API::getInstance();
        
        $export = $api->export_extensions();
        
        $this->assertIsArray( $export );
        $this->assertArrayHasKey( 'exported_at', $export );
        $this->assertArrayHasKey( 'api_version', $export );
        $this->assertArrayHasKey( 'extensions', $export );
        $this->assertArrayHasKey( 'total_count', $export );
        $this->assertArrayHasKey( 'active_count', $export );
    }
    
    /**
     * Test execute_hook method exists
     */
    public function test_execute_hook_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'execute_hook' ) );
    }
    
    /**
     * Test apply_filter method exists
     */
    public function test_apply_filter_method_exists() {
        $api = Extension_API::getInstance();
        $this->assertTrue( method_exists( $api, 'apply_filter' ) );
    }
    
    /**
     * Test execute_hook returns original value for non-existent hook
     */
    public function test_execute_hook_returns_original_for_nonexistent() {
        $api = Extension_API::getInstance();
        
        $result = $api->execute_hook( 'nonexistent_hook', 'test_value' );
        
        $this->assertEquals( 'test_value', $result );
    }
    
    /**
     * Test apply_filter returns original value for non-existent filter
     */
    public function test_apply_filter_returns_original_for_nonexistent() {
        $api = Extension_API::getInstance();
        
        $result = $api->apply_filter( 'nonexistent_filter', 'test_value' );
        
        $this->assertEquals( 'test_value', $result );
    }
    
    /**
     * Test add_hook returns false for non-callable
     */
    public function test_add_hook_returns_false_for_non_callable() {
        $api = Extension_API::getInstance();
        
        $result = $api->add_hook( 'test_hook', 'not_a_function' );
        
        $this->assertFalse( $result );
    }
    
    /**
     * Test add_filter returns false for non-callable
     */
    public function test_add_filter_returns_false_for_non_callable() {
        $api = Extension_API::getInstance();
        
        $result = $api->add_filter( 'test_filter', 'not_a_function' );
        
        $this->assertFalse( $result );
    }
}
