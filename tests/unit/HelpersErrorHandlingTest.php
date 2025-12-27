<?php
/**
 * Unit tests for Helpers class - error handling functionality
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
use WPCleanAdmin\Helpers;
use WPCleanAdmin\WPCA_Errors;

/**
 * Test class for Helpers - Error Handling functionality
 * @covers Helpers
 */
class HelpersErrorHandlingTest extends TestCase {
    
    /**
     * Test instance creation
     */
    public function test_instance_creation() {
        $helpers = Helpers::getInstance();
        $this->assertInstanceOf( Helpers::class, $helpers );
    }
    
    /**
     * Test register_error_handler method exists
     */
    public function test_register_error_handler_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'register_error_handler' ) );
    }
    
    /**
     * Test custom_error_handler method exists
     */
    public function test_custom_error_handler_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'custom_error_handler' ) );
    }
    
    /**
     * Test custom_exception_handler method exists
     */
    public function test_custom_exception_handler_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'custom_exception_handler' ) );
    }
    
    /**
     * Test handle_ajax_error method exists
     */
    public function test_handle_ajax_error_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'handle_ajax_error' ) );
    }
    
    /**
     * Test handle_validation_errors method exists
     */
    public function test_handle_validation_errors_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'handle_validation_errors' ) );
    }
    
    /**
     * Test validate_required_params method exists
     */
    public function test_validate_required_params_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'validate_required_params' ) );
    }
    
    /**
     * Test validate_ajax_nonce method exists
     */
    public function test_validate_ajax_nonce_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'validate_ajax_nonce' ) );
    }
    
    /**
     * Test check_capability method exists
     */
    public function test_check_capability_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'check_capability' ) );
    }
    
    /**
     * Test log_error method exists
     */
    public function test_log_error_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'log_error' ) );
    }
    
    /**
     * Test log_success method exists
     */
    public function test_log_success_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'log_success' ) );
    }
    
    /**
     * Test get_error_stats method exists
     */
    public function test_get_error_stats_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'get_error_stats' ) );
    }
    
    /**
     * Test clear_logs method exists
     */
    public function test_clear_logs_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'clear_logs' ) );
    }
    
    /**
     * Test export_logs method exists
     */
    public function test_export_logs_method_exists() {
        $helpers = Helpers::getInstance();
        $this->assertTrue( method_exists( $helpers, 'export_logs' ) );
    }
    
    /**
     * Test get_error_stats returns array with expected keys
     */
    public function test_get_error_stats_returns_expected_structure() {
        $helpers = Helpers::getInstance();
        
        $stats = $helpers->get_error_stats();
        
        $this->assertIsArray( $stats );
        $this->assertArrayHasKey( 'total_errors', $stats );
        $this->assertArrayHasKey( 'error_types', $stats );
        $this->assertArrayHasKey( 'recent_errors', $stats );
        $this->assertArrayHasKey( 'period', $stats );
    }
    
    /**
     * Test get_error_stats accepts days parameter
     */
    public function test_get_error_stats_accepts_days_parameter() {
        $helpers = Helpers::getInstance();
        
        $stats_7 = $helpers->get_error_stats( 7 );
        $stats_30 = $helpers->get_error_stats( 30 );
        
        $this->assertEquals( '7 days', $stats_7['period'] );
        $this->assertEquals( '30 days', $stats_30['period'] );
    }
    
    /**
     * Test clear_logs returns bool
     */
    public function test_clear_logs_returns_bool() {
        $helpers = Helpers::getInstance();
        
        $result = $helpers->clear_logs();
        
        $this->assertIsBool( $result );
        $this->assertTrue( $result );
    }
    
    /**
     * Test export_logs returns array with expected keys
     */
    public function test_export_logs_returns_expected_structure() {
        $helpers = Helpers::getInstance();
        
        $logs = $helpers->export_logs();
        
        $this->assertIsArray( $logs );
        $this->assertArrayHasKey( 'exported_at', $logs );
        $this->assertArrayHasKey( 'wp_version', $logs );
        $this->assertArrayHasKey( 'php_version', $logs );
        $this->assertArrayHasKey( 'plugin_version', $logs );
        $this->assertArrayHasKey( 'logs', $logs );
    }
    
    /**
     * Test validate_required_params returns null for valid params
     */
    public function test_validate_required_params_returns_null_for_valid() {
        $helpers = Helpers::getInstance();
        
        $params = array( 'action' => 'test', 'nonce' => 'abc123' );
        $required = array( 'action', 'nonce' );
        
        $result = $helpers->validate_required_params( $params, $required );
        
        $this->assertNull( $result );
    }
    
    /**
     * Test validate_required_params returns errors for missing params
     */
    public function test_validate_required_params_returns_errors_for_missing() {
        $helpers = Helpers::getInstance();
        
        $params = array( 'action' => 'test' );
        $required = array( 'action', 'nonce', 'data' );
        
        $result = $helpers->validate_required_params( $params, $required );
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'nonce', $result );
        $this->assertArrayHasKey( 'data', $result );
    }
    
    /**
     * Test check_capability returns bool
     */
    public function test_check_capability_returns_bool() {
        $helpers = Helpers::getInstance();
        
        $result = $helpers->check_capability( 'manage_options' );
        
        $this->assertIsBool( $result );
    }
    
    /**
     * Test handle_validation_errors returns array structure
     */
    public function test_handle_validation_errors_returns_structure() {
        $helpers = Helpers::getInstance();
        
        $errors = array( 'field1' => 'Field 1 is required', 'field2' => 'Field 2 is invalid' );
        $result = $helpers->handle_validation_errors( $errors );
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
        $this->assertArrayHasKey( 'error_code', $result );
        $this->assertArrayHasKey( 'message', $result );
        $this->assertArrayHasKey( 'data', $result );
        $this->assertFalse( $result['success'] );
        $this->assertEquals( WPCA_Errors::ERROR_VALIDATION, $result['error_code'] );
        $this->assertArrayHasKey( 'validation_errors', $result['data'] );
    }
    
    /**
     * Test WPCA_Errors class constants
     */
    public function test_wpca_errors_constants() {
        $this->assertEquals( 0, WPCA_Errors::ERROR_NONE );
        $this->assertEquals( 1001, WPCA_Errors::ERROR_DATABASE );
        $this->assertEquals( 1002, WPCA_Errors::ERROR_PERMISSION );
        $this->assertEquals( 1003, WPCA_Errors::ERROR_INVALID_INPUT );
        $this->assertEquals( 1004, WPCA_Errors::ERROR_FILE_OPERATION );
        $this->assertEquals( 1005, WPCA_Errors::ERROR_AJAX );
        $this->assertEquals( 1006, WPCA_Errors::ERROR_VALIDATION );
        $this->assertEquals( 1007, WPCA_Errors::ERROR_AUTH );
        $this->assertEquals( 9999, WPCA_Errors::ERROR_UNKNOWN );
    }
}
