<?php
/**
 * Unit tests for Menu_Customizer class - menu groups functionality
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
use WPCleanAdmin\Menu_Customizer;

/**
 * Test class for Menu_Customizer - Menu Groups functionality
 * @covers Menu_Customizer
 */
class MenuCustomizerGroupsTest extends TestCase {
    
    /**
     * Test instance creation
     */
    public function test_instance_creation() {
        $customizer = Menu_Customizer::getInstance();
        $this->assertInstanceOf( Menu_Customizer::class, $customizer );
    }
    
    /**
     * Test get_menu_groups method exists
     */
    public function test_get_menu_groups_method_exists() {
        $customizer = Menu_Customizer::getInstance();
        $this->assertTrue( method_exists( $customizer, 'get_menu_groups' ) );
    }
    
    /**
     * Test create_menu_group method exists
     */
    public function test_create_menu_group_method_exists() {
        $customizer = Menu_Customizer::getInstance();
        $this->assertTrue( method_exists( $customizer, 'create_menu_group' ) );
    }
    
    /**
     * Test delete_menu_group method exists
     */
    public function test_delete_menu_group_method_exists() {
        $customizer = Menu_Customizer::getInstance();
        $this->assertTrue( method_exists( $customizer, 'delete_menu_group' ) );
    }
    
    /**
     * Test update_menu_group method exists
     */
    public function test_update_menu_group_method_exists() {
        $customizer = Menu_Customizer::getInstance();
        $this->assertTrue( method_exists( $customizer, 'update_menu_group' ) );
    }
    
    /**
     * Test get_menu_groups returns array
     */
    public function test_get_menu_groups_returns_array() {
        $customizer = Menu_Customizer::getInstance();
        $groups = $customizer->get_menu_groups();
        
        $this->assertIsArray( $groups );
    }
    
    /**
     * Test create_menu_group returns false for empty group_id
     */
    public function test_create_menu_group_returns_false_for_empty_id() {
        $customizer = Menu_Customizer::getInstance();
        
        $result = $customizer->create_menu_group( '', 'Test Group', array() );
        $this->assertFalse( $result );
    }
    
    /**
     * Test create_menu_group returns false for empty group_name
     */
    public function test_create_menu_group_returns_false_for_empty_name() {
        $customizer = Menu_Customizer::getInstance();
        
        $result = $customizer->create_menu_group( 'test-group', '', array() );
        $this->assertFalse( $result );
    }
    
    /**
     * Test delete_menu_group returns false for empty group_id
     */
    public function test_delete_menu_group_returns_false_for_empty_id() {
        $customizer = Menu_Customizer::getInstance();
        
        $result = $customizer->delete_menu_group( '' );
        $this->assertFalse( $result );
    }
    
    /**
     * Test update_menu_group returns false for empty group_id
     */
    public function test_update_menu_group_returns_false_for_empty_id() {
        $customizer = Menu_Customizer::getInstance();
        
        $result = $customizer->update_menu_group( '', array() );
        $this->assertFalse( $result );
    }
    
    /**
     * Test update_menu_group returns false for empty settings
     */
    public function test_update_menu_group_returns_false_for_empty_settings() {
        $customizer = Menu_Customizer::getInstance();
        
        $result = $customizer->update_menu_group( 'test-group', array() );
        $this->assertFalse( $result );
    }
    
    /**
     * Test apply_menu_groups method exists (even if private)
     */
    public function test_apply_menu_groups_method_exists() {
        $customizer = Menu_Customizer::getInstance();
        $reflection = new ReflectionClass( $customizer );
        
        $this->assertTrue( $reflection->hasMethod( 'apply_menu_groups' ) );
    }
    
    /**
     * Test get_settings method exists
     */
    public function test_get_settings_method_exists() {
        $customizer = Menu_Customizer::getInstance();
        $this->assertTrue( method_exists( $customizer, 'get_settings' ) );
    }
    
    /**
     * Test get_settings returns array
     */
    public function test_get_settings_returns_array() {
        $customizer = Menu_Customizer::getInstance();
        $settings = $customizer->get_settings();
        
        $this->assertIsArray( $settings );
        $this->assertArrayHasKey( 'enabled', $settings );
        $this->assertArrayHasKey( 'menu_items', $settings );
    }
}
