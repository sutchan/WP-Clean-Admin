<?php
/**
 * WP Clean Admin - Test Cases for Bug Fixes
 */

/**
 * Test class for WP Clean Admin fixes
 */
class WPCA_Fixes_Test extends WP_UnitTestCase {
    
    /**
     * Test AJAX request validation
     */
    public function test_ajax_request_validation() {
        // Check if validate_ajax_request method exists
        $this->assertTrue( method_exists( 'WPCA_Ajax', 'validate_ajax_request' ), 'WPCA_Ajax::validate_ajax_request method exists' );
    }
    
    /**
     * Test that AJAX hooks are properly registered in WPCA_Ajax class
     */
    public function test_ajax_hooks_registration() {
        // This would require mocking the WordPress environment
        // For now, we'll just check if the methods exist
        $this->assertTrue( method_exists( 'WPCA_Ajax', 'init_hooks' ), 'WPCA_Ajax::init_hooks method exists' );
        $this->assertTrue( method_exists( 'WPCA_Ajax', 'toggle_menu' ), 'WPCA_Ajax::toggle_menu method exists' );
        $this->assertTrue( method_exists( 'WPCA_Ajax', 'update_menu_order' ), 'WPCA_Ajax::update_menu_order method exists' );
        // Test that new methods exist
        $this->assertTrue( method_exists( 'WPCA_Ajax', 'reset_menu' ), 'WPCA_Ajax::reset_menu method exists' );
        $this->assertTrue( method_exists( 'WPCA_Ajax', 'reset_menu_order' ), 'WPCA_Ajax::reset_menu_order method exists' );
    }
    
    /**
     * Test that permission checks work correctly
     */
    public function test_permission_checks() {
        // This would require mocking the WordPress environment
        // For now, we'll just check if the method exists
        $this->assertTrue( method_exists( 'WPCA_Permissions', 'current_user_can' ), 'WPCA_Permissions::current_user_can method exists' );
    }
    
    /**
     * Test that version constants are defined
     */
    public function test_version_constant() {
        $this->assertTrue( defined( 'WPCA_VERSION' ), 'WPCA_VERSION constant is defined' );
        // Check that version is at least 1.4.1 after our fixes
        $this->assertGreaterThanOrEqual( '1.4.1', WPCA_VERSION, 'WPCA_VERSION is at least 1.4.1' );
    }
    
}

// Run the tests
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $test = new WPCA_Fixes_Test();
    $test->run_all_tests();
}