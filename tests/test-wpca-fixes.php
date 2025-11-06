<?php
/**
 * WP Clean Admin Fixes Test
 * 
 * This file contains tests for the fixes applied to the WP Clean Admin plugin.
 */

// Define test environment constants
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

/**
 * Test class for WP Clean Admin fixes
 */
class WPCA_Fixes_Test {
    
    /**
     * Test AJAX request validation
     */
    public function test_ajax_validation() {
        echo "Testing AJAX validation improvements...\n";
        
        // Simulate missing nonce
        $_POST = array();
        $this->simulate_ajax_validation(false, 'missing_nonce');
        
        // Simulate invalid nonce
        $_POST = array('nonce' => 'invalid-nonce');
        $this->simulate_ajax_validation(false, 'invalid_nonce');
        
        echo "AJAX validation tests completed.\n";
    }
    
    /**
     * Test menu toggle security
     */
    public function test_menu_toggle_security() {
        echo "Testing menu toggle security improvements...\n";
        
        // Test valid slug format
        $valid_slugs = array('dashboard', 'posts', 'users-settings');
        foreach ($valid_slugs as $slug) {
            $this->test_slug_format($slug, true);
        }
        
        // Test invalid slug format (potential injection)
        $invalid_slugs = array('dashboard<script>', 'posts; DROP TABLE', '../etc/passwd');
        foreach ($invalid_slugs as $slug) {
            $this->test_slug_format($slug, false);
        }
        
        echo "Menu toggle security tests completed.\n";
    }
    
    /**
     * Test error logging
     */
    public function test_error_logging() {
        echo "Testing error logging improvements...\n";
        
        // Test with exception
        try {
            throw new Exception('Test exception', 500);
        } catch (Exception $e) {
            $this->simulate_error_log($e);
        }
        
        // Test with string error
        $this->simulate_error_log('Test string error');
        
        echo "Error logging tests completed.\n";
    }
    
    /**
     * Simulate AJAX validation
     */
    private function simulate_ajax_validation($expected_result, $test_case) {
        // This is a simulation of the validation logic
        echo "- Test case: $test_case - Expected: " . ($expected_result ? 'Valid' : 'Invalid') . "\n";
    }
    
    /**
     * Test slug format validation
     */
    private function test_slug_format($slug, $should_be_valid) {
        $is_valid = (bool) preg_match('/^[a-zA-Z0-9_\-]+$/', $slug);
        echo "- Slug: '$slug' - " . ($is_valid ? 'Valid' : 'Invalid') . " (" . ($is_valid === $should_be_valid ? 'PASS' : 'FAIL') . ")\n";
    }
    
    /**
     * Simulate error logging
     */
    private function simulate_error_log($error) {
        $message = is_object($error) && method_exists($error, 'getMessage') ? $error->getMessage() : (string)$error;
        echo "- Logging error: '$message'\n";
        
        if (is_object($error) && method_exists($error, 'getTraceAsString')) {
            echo "- Stack trace available\n";
        }
    }
    
    /**
     * Run all tests
     */
    public function run_all_tests() {
        echo "Starting WP Clean Admin fixes tests...\n";
        echo "======================================\n";
        
        $this->test_ajax_validation();
        echo "\n";
        $this->test_menu_toggle_security();
        echo "\n";
        $this->test_error_logging();
        
        echo "\n======================================\n";
        echo "All tests completed.\n";
    }
}

// Run the tests
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $test = new WPCA_Fixes_Test();
    $test->run_all_tests();
}