<?php
/**
 * Functional Tests for Menu Manager Module
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @since 1.7.15
 */

require_once __DIR__ . '/WPCA_Functional_TestCase.php';

/**
 * Functional test class for Menu Manager module
 *
 * Tests the complete menu management functionality including
 * menu visibility, reordering, grouping, and role-based access
 */
class MenuManagerFunctionalTest extends WPCA_Functional_TestCase {
    
    /**
     * Test menu items visibility
     *
     * @return void
     */
    public function test_menu_visibility_functionality() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test get_visible_menu_items method
        $visible_items = $menu_manager->get_visible_menu_items();
        $this->assertIsArray( $visible_items );
    }
    
    /**
     * Test menu items count
     *
     * @return void
     */
    public function test_menu_items_count() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test get_all_menu_items method
        $all_items = $menu_manager->get_all_menu_items();
        $this->assertIsArray( $all_items );
        $this->assertGreaterThan( 0, count( $all_items ) );
    }
    
    /**
     * Test menu item structure
     *
     * @return void
     */
    public function test_menu_item_structure() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        $all_items = $menu_manager->get_all_menu_items();
        
        if ( ! empty( $all_items ) ) {
            $first_item = reset( $all_items );
            $this->assertArrayHasKey( 'id', $first_item );
            $this->assertArrayHasKey( 'title', $first_item );
            $this->assertArrayHasKey( 'slug', $first_item );
            $this->assertArrayHasKey( 'parent', $first_item );
        }
    }
    
    /**
     * Test menu reordering functionality
     *
     * @return void
     */
    public function test_menu_reordering() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test apply_menu_reorder method
        $reorder_data = array(
            'menu_order' => array(
                'dashboard' => 1,
                'posts' => 2,
                'media' => 3,
                'pages' => 4,
                'comments' => 5,
            )
        );
        
        $result = $menu_manager->apply_menu_reorder( $reorder_data );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test menu hiding functionality
     *
     * @return void
     */
    public function test_menu_hiding() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test hide_menu_item method
        $result = $menu_manager->hide_menu_item( 'tools' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test menu unhiding functionality
     *
     * @return void
     */
    public function test_menu_unhiding() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test unhide_menu_item method
        $result = $menu_manager->unhide_menu_item( 'tools' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test submenu management
     *
     * @return void
     */
    public function test_submenu_management() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test get_submenu_items method
        $submenus = $menu_manager->get_submenu_items( 'options-general.php' );
        $this->assertIsArray( $submenus );
    }
    
    /**
     * Test submenu hiding
     *
     * @return void
     */
    public function test_submenu_hiding() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test hide_submenu_item method
        $result = $menu_manager->hide_submenu_item( 'options-general.php', 'options-privacy.php' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test menu icon customization
     *
     * @return void
     */
    public function test_menu_icon_customization() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test update_menu_icon method
        $result = $menu_manager->update_menu_icon( 'posts', 'dashicons-admin-post' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test custom menu item addition
     *
     * @return void
     */
    public function test_custom_menu_item() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test add_custom_menu_item method
        $custom_item = array(
            'title' => 'Custom Link',
            'slug' => 'custom-link',
            'url' => 'https://example.com',
            'icon' => 'dashicons-admin-links',
            'position' => 50,
        );
        
        $result = $menu_manager->add_custom_menu_item( $custom_item );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test custom menu item removal
     *
     * @return void
     */
    public function test_custom_menu_item_removal() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test remove_custom_menu_item method
        $result = $menu_manager->remove_custom_menu_item( 'custom-link' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test menu separator management
     *
     * @return void
     */
    public function test_menu_separator_management() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test add_menu_separator method
        $result = $menu_manager->add_menu_separator( 30 );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test menu export functionality
     *
     * @return void
     */
    public function test_menu_export() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test export_menu_config method
        $export = $menu_manager->export_menu_config();
        $this->assertIsArray( $export );
        $this->assertArrayHasKey( 'menu_items', $export );
        $this->assertArrayHasKey( 'hidden_items', $export );
        $this->assertArrayHasKey( 'custom_items', $export );
    }
    
    /**
     * Test menu import functionality
     *
     * @return void
     */
    public function test_menu_import() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test import_menu_config method
        $config = array(
            'menu_items' => array(),
            'hidden_items' => array(),
            'custom_items' => array(),
        );
        
        $result = $menu_manager->import_menu_config( $config );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test menu reset functionality
     *
     * @return void
     */
    public function test_menu_reset() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        // Test reset_menu_config method
        $result = $menu_manager->reset_menu_config();
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test get_menu_structure method
     *
     * @return void
     */
    public function test_get_menu_structure() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        $structure = $menu_manager->get_menu_structure();
        $this->assertIsArray( $structure );
    }
    
    /**
     * Test save_menu_settings method
     *
     * @return void
     */
    public function test_save_menu_settings() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        $settings = array(
            'hide_items' => array( 'tools' ),
            'reorder' => array(),
            'custom_items' => array(),
        );
        
        $result = $menu_manager->save_menu_settings( $settings );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test get_menu_settings method
     *
     * @return void
     */
    public function test_get_menu_settings() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        $settings = $menu_manager->get_menu_settings();
        $this->assertIsArray( $settings );
        $this->assertArrayHasKey( 'hide_items', $settings );
        $this->assertArrayHasKey( 'reorder', $settings );
    }
    
    /**
     * Test update_menu_position method
     *
     * @return void
     */
    public function test_update_menu_position() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        $result = $menu_manager->update_menu_position( 'posts', 5 );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test rename_menu_item method
     *
     * @return void
     */
    public function test_rename_menu_item() {
        $this->simulate_admin_page_load();
        
        $menu_manager = \WPCleanAdmin\Menu_Manager::getInstance();
        
        $result = $menu_manager->rename_menu_item( 'posts', 'Articles' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
}
