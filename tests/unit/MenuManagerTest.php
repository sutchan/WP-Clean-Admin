<?php
/**
 * Unit tests for Menu_Manager class
 *
 * @package WPCleanAdmin
 *
 * @noinspection PhpUndefinedClassInspection PHPUnit is loaded via composer
 * @noinspection PhpUndefinedMethodInspection PHPUnit methods are available
 */

use PHPUnit\Framework\TestCase;
use WPCleanAdmin\Menu_Manager;

/**
 * Test class for Menu_Manager
 * @covers Menu_Manager
 */
class MenuManagerTest extends TestCase {
    
    /**
     * Test instance creation
     */
    public function test_instance_creation() {
        $menu_manager = Menu_Manager::getInstance();
        $this->assertInstanceOf( Menu_Manager::class, $menu_manager );
    }
    
    /**
     * Test singleton pattern
     */
    public function test_singleton_pattern() {
        $menu_manager1 = Menu_Manager::getInstance();
        $menu_manager2 = Menu_Manager::getInstance();
        $this->assertSame( $menu_manager1, $menu_manager2 );
    }
    
    /**
     * Test get_menu_items method
     */
    public function test_get_menu_items() {
        $menu_manager = Menu_Manager::getInstance();
        $menu_items = $menu_manager->get_menu_items();
        $this->assertIsArray( $menu_items );
    }
    
    /**
     * Test save_menu_items method
     */
    public function test_save_menu_items() {
        $menu_manager = Menu_Manager::getInstance();
        $test_menu = array(
            array(
                'id' => 1,
                'title' => 'Test Menu',
                'slug' => 'test-menu',
                'capability' => 'manage_options',
                'icon' => 'dashicons-test',
                'position' => 10,
                'submenu' => array()
            )
        );
        
        $result = $menu_manager->save_menu_items( $test_menu );
        $this->assertIsArray( $result );
        $this->assertTrue( $result['success'] );
    }
}