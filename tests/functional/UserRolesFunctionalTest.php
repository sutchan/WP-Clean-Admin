<?php
/**
 * Functional Tests for User Roles Module
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @since 1.7.15
 */

require_once __DIR__ . '/WPCA_Functional_TestCase.php';

/**
 * Functional test class for User Roles module
 *
 * Tests the complete user roles functionality including
 * role creation, modification, deletion, and capability management
 */
class UserRolesFunctionalTest extends WPCA_Functional_TestCase {
    
    /**
     * Test role retrieval functionality
     *
     * @return void
     */
    public function test_role_retrieval() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test get_roles method
        $roles = $user_roles->get_roles();
        $this->assertIsArray( $roles );
        $this->assertNotEmpty( $roles );
    }
    
    /**
     * Test single role retrieval
     *
     * @return void
     */
    public function test_single_role_retrieval() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test get_role method
        $role = $user_roles->get_role( 'administrator' );
        $this->assertIsArray( $role );
        $this->assertArrayHasKey( 'name', $role );
        $this->assertArrayHasKey( 'capabilities', $role );
    }
    
    /**
     * Test role creation
     *
     * @return void
     */
    public function test_role_creation() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test create_role method
        $result = $user_roles->create_role( 'wpca_custom_role', 'Custom Role', array(
            'read' => true,
            'edit_posts' => true,
        ) );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test role update
     *
     * @return void
     */
    public function test_role_update() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test update_role method
        $result = $user_roles->update_role( 'wpca_custom_role', 'Updated Custom Role', array(
            'read' => true,
            'edit_posts' => true,
            'edit_others_posts' => true,
        ) );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test role deletion
     *
     * @return void
     */
    public function test_role_deletion() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test delete_role method
        $result = $user_roles->delete_role( 'wpca_custom_role' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test role capability addition
     *
     * @return void
     */
    public function test_role_capability_addition() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test add_capability_to_role method
        $result = $user_roles->add_capability_to_role( 'editor', 'wpca_extra_cap' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test role capability removal
     *
     * @return void
     */
    public function test_role_capability_removal() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test remove_capability_from_role method
        $result = $user_roles->remove_capability_from_role( 'editor', 'wpca_extra_cap' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test role cloning
     *
     * @return void
     */
    public function test_role_cloning() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test clone_role method
        $result = $user_roles->clone_role( 'editor', 'wpca_cloned_editor' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test role renaming
     *
     * @return void
     */
    public function test_role_renaming() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test rename_role method
        $result = $user_roles->rename_role( 'wpca_cloned_editor', 'Cloned Editor Role' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test user role assignment
     *
     * @return void
     */
    public function test_user_role_assignment() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test set_user_role method
        $result = $user_roles->set_user_role( 1, 'administrator' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test user multiple role assignment
     *
     * @return void
     */
    public function test_user_multiple_roles() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test add_user_role method
        $result = $user_roles->add_user_role( 1, 'wpca_extra_role' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test user role removal
     *
     * @return void
     */
    public function test_user_role_removal() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test remove_user_role method
        $result = $user_roles->remove_user_role( 1, 'wpca_extra_role' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test get user roles
     *
     * @return void
     */
    public function test_get_user_roles() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test get_user_roles method
        $roles = $user_roles->get_user_roles( 1 );
        $this->assertIsArray( $roles );
    }
    
    /**
     * Test role capabilities listing
     *
     * @return void
     */
    public function test_role_capabilities_listing() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test get_role_capabilities method
        $caps = $user_roles->get_role_capabilities( 'administrator' );
        $this->assertIsArray( $caps );
        $this->assertNotEmpty( $caps );
    }
    
    /**
     * Test role existence check
     *
     * @return void
     */
    public function test_role_existence_check() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test role_exists method
        $exists = $user_roles->role_exists( 'administrator' );
        $this->assertTrue( $exists );
        
        $not_exists = $user_roles->role_exists( 'nonexistent_role' );
        $this->assertFalse( $not_exists );
    }
    
    /**
     * Test role count by capability
     *
     * @return void
     */
    public function test_role_count_by_capability() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test count_roles_with_capability method
        $count = $user_roles->count_roles_with_capability( 'manage_options' );
        $this->assertIsInt( $count );
        $this->assertGreaterThan( 0, $count );
    }
    
    /**
     * Test default role setting
     *
     * @return void
     */
    public function test_default_role_setting() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test set_default_role method
        $result = $user_roles->set_default_role( 'subscriber' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test get default role
     *
     * @return void
     */
    public function test_get_default_role() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test get_default_role method
        $default = $user_roles->get_default_role();
        $this->assertIsString( $default );
    }
    
    /**
     * Test role export
     *
     * @return void
     */
    public function test_role_export() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test export_roles method
        $export = $user_roles->export_roles();
        $this->assertIsArray( $export );
        $this->assertArrayHasKey( 'roles', $export );
    }
    
    /**
     * Test role import
     *
     * @return void
     */
    public function test_role_import() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test import_roles method
        $config = array(
            'roles' => array(),
        );
        
        $result = $user_roles->import_roles( $config );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test role reset
     *
     * @return void
     */
    public function test_role_reset() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        // Test reset_roles method
        $result = $user_roles->reset_roles();
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test get_editable_roles method
     *
     * @return void
     */
    public function test_get_editable_roles() {
        $this->simulate_admin_page_load();
        
        $user_roles = \WPCleanAdmin\User_Roles::getInstance();
        
        $roles = $user_roles->get_editable_roles();
        $this->assertIsArray( $roles );
    }
}
