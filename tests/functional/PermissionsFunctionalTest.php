<?php
/**
 * Functional Tests for Permissions Module
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @since 1.7.15
 */

require_once __DIR__ . '/WPCA_Functional_TestCase.php';

/**
 * Functional test class for Permissions module
 *
 * Tests the complete permission management functionality including
 * capability checking, role-based permissions, and access control
 */
class PermissionsFunctionalTest extends WPCA_Functional_TestCase {
    
    /**
     * Test permission checking functionality
     *
     * @return void
     */
    public function test_permission_checking() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test has_permission method
        $result = $permissions->has_permission( 'edit_posts' );
        $this->assertIsBool( $result );
    }
    
    /**
     * Test capability verification
     *
     * @return void
     */
    public function test_capability_verification() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test can method (shortcut for has_permission)
        $result = $permissions->can( 'manage_options' );
        $this->assertIsBool( $result );
    }
    
    /**
     * Test role permission assignment
     *
     * @return void
     */
    public function test_role_permission_assignment() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test add_role_capability method
        $result = $permissions->add_role_capability( 'editor', 'edit_theme_options' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test role permission removal
     *
     * @return void
     */
    public function test_role_permission_removal() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test remove_role_capability method
        $result = $permissions->remove_role_capability( 'editor', 'edit_theme_options' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test user permission override
     *
     * @return void
     */
    public function test_user_permission_override() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test add_user_capability method
        $result = $permissions->add_user_capability( 1, 'edit_others_posts' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test user capability removal
     *
     * @return void
     */
    public function test_user_capability_removal() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test remove_user_capability method
        $result = $permissions->remove_user_capability( 1, 'edit_others_posts' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test role capabilities list
     *
     * @return void
     */
    public function test_role_capabilities_list() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test get_role_capabilities method
        $caps = $permissions->get_role_capabilities( 'administrator' );
        $this->assertIsArray( $caps );
        $this->assertContains( 'manage_options', $caps );
    }
    
    /**
     * Test user capabilities list
     *
     * @return void
     */
    public function test_user_capabilities_list() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test get_user_capabilities method
        $caps = $permissions->get_user_capabilities( 1 );
        $this->assertIsArray( $caps );
    }
    
    /**
     * Test permission deny functionality
     *
     * @return void
     */
    public function test_permission_deny() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test deny_permission method
        $result = $permissions->deny_permission( 'delete_plugins' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test permission grant functionality
     *
     * @return void
     */
    public function test_permission_grant() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test grant_permission method
        $result = $permissions->grant_permission( 'edit_theme_options' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test role-based menu restriction
     *
     * @return void
     */
    public function test_role_menu_restriction() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test restrict_menu_for_role method
        $result = $permissions->restrict_menu_for_role( 'editor', 'tools.php' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test menu restriction removal
     *
     * @return void
     */
    public function test_menu_restriction_removal() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test unrestrict_menu_for_role method
        $result = $permissions->unrestrict_menu_for_role( 'editor', 'tools.php' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test capability filter
     *
     * @return void
     */
    public function test_capability_filter() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test filter_capabilities method
        $capabilities = array( 'edit_posts', 'delete_posts', 'manage_options' );
        $filtered = $permissions->filter_capabilities( $capabilities, 'subscriber' );
        $this->assertIsArray( $filtered );
    }
    
    /**
     * Test permission reset
     *
     * @return void
     */
    public function test_permission_reset() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test reset_permissions method
        $result = $permissions->reset_permissions();
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test permission export
     *
     * @return void
     */
    public function test_permission_export() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test export_permissions method
        $export = $permissions->export_permissions();
        $this->assertIsArray( $export );
        $this->assertArrayHasKey( 'roles', $export );
        $this->assertArrayHasKey( 'users', $export );
    }
    
    /**
     * Test permission import
     *
     * @return void
     */
    public function test_permission_import() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test import_permissions method
        $config = array(
            'roles' => array(),
            'users' => array(),
        );
        
        $result = $permissions->import_permissions( $config );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test get_roles_with_capability method
     *
     * @return void
     */
    public function test_get_roles_with_capability() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        $roles = $permissions->get_roles_with_capability( 'manage_options' );
        $this->assertIsArray( $roles );
        $this->assertContains( 'administrator', $roles );
    }
    
    /**
     * Test check_multiple_permissions method
     *
     * @return void
     */
    public function test_check_multiple_permissions() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        $caps = array( 'edit_posts', 'edit_others_posts' );
        $result = $permissions->check_multiple_permissions( $caps );
        $this->assertIsArray( $result );
    }
    
    /**
     * Test create_custom_capability method
     *
     * @return void
     */
    public function test_create_custom_capability() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        $result = $permissions->create_custom_capability( 'wpca_custom_cap' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test remove_custom_capability method
     *
     * @return void
     */
    public function test_remove_custom_capability() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        $result = $permissions->remove_custom_capability( 'wpca_custom_cap' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test permission migration method
     *
     * @return void
     */
    public function test_permission_migration() {
        $this->simulate_admin_page_load();
        
        $permissions = \WPCleanAdmin\Permissions::getInstance();
        
        // Test migrate_permissions method
        $result = $permissions->migrate_permissions();
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
}
