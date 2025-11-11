<?php
/**
 * Tests for the performance optimization module.
 * 
 * @package WP_Clean_Admin
 * @since 1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * WPCA_Performance_Tests class.
 * 
 * Contains tests for the performance optimization module.
 */
class WPCA_Performance_Tests {

    /**
     * Instance of the class.
     *
     * @var WPCA_Performance_Tests
     */
    protected static $instance = null;

    /**
     * Results of the tests.
     *
     * @var array
     */
    protected $results = array();

    /**
     * Initialize the class.
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'register_test_page' ) );
    }

    /**
     * Register the test page.
     */
    public function register_test_page() {
        add_action( 'admin_menu', array( $this, 'add_test_page' ) );
        add_action( 'admin_init', array( $this, 'maybe_run_tests' ) );
    }

    /**
     * Add the test page.
     */
    public function add_test_page() {
        add_submenu_page(
            'wpca-settings',
            esc_html__( 'WPCA Performance Tests', 'wp-clean-admin' ),
            esc_html__( 'Performance Tests', 'wp-clean-admin' ),
            'manage_options',
            'wpca-performance-tests',
            array( $this, 'render_test_page' ),
            99
        );
    }

    /**
     * Run tests when requested.
     */
    public function maybe_run_tests() {
        if ( isset( $_POST['wpca_run_tests'] ) && check_admin_referer( 'wpca-run-tests' ) ) {
            $this->run_tests();
            add_action( 'admin_notices', array( $this, 'show_test_results' ) );
        }
    }

    /**
     * Render the test page.
     */
    public function render_test_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WP Clean Admin - Performance Tests', 'wp-clean-admin' ); ?></h1>
            
            <div class="wpca-test-description">
                <p><?php esc_html_e( 'This page allows you to test the functionality of the performance optimization module. Click the button below to run the tests.', 'wp-clean-admin' ); ?></p>
                <p class="description"><?php esc_html_e( 'Note: These tests are for development purposes only and should not be run on production sites.', 'wp-clean-admin' ); ?></p>
            </div>
            
            <form method="post">
                <?php wp_nonce_field( 'wpca-run-tests' ); ?>
                <input type="hidden" name="wpca_run_tests" value="1" />
                <?php submit_button( esc_html__( 'Run Tests', 'wp-clean-admin' ), 'primary large', 'submit', false ); ?>
            </form>
            
            <?php if ( ! empty( $this->results ) ) : ?>
                <div class="wpca-test-results">
                    <h2><?php esc_html_e( 'Test Results', 'wp-clean-admin' ); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th scope="col" class="manage-column column-primary"><?php esc_html_e( 'Test Name', 'wp-clean-admin' ); ?></th>
                                <th scope="col" class="manage-column"><?php esc_html_e( 'Status', 'wp-clean-admin' ); ?></th>
                                <th scope="col" class="manage-column"><?php esc_html_e( 'Details', 'wp-clean-admin' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $this->results as $test_name => $result ) : ?>
                                <tr>
                                    <td class="column-primary">
                                        <?php echo esc_html( $test_name ); ?>
                                    </td>
                                    <td>
                                        <?php if ( $result['status'] === 'success' ) : ?>
                                            <span class="wpca-test-success">✓ <?php esc_html_e( 'Passed', 'wp-clean-admin' ); ?></span>
                                        <?php elseif ( $result['status'] === 'warning' ) : ?>
                                            <span class="wpca-test-warning">⚠ <?php esc_html_e( 'Warning', 'wp-clean-admin' ); ?></span>
                                        <?php else : ?>
                                            <span class="wpca-test-error">✗ <?php esc_html_e( 'Failed', 'wp-clean-admin' ); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ( ! empty( $result['details'] ) ) : ?>
                                            <?php echo esc_html( $result['details'] ); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Run all tests.
     */
    public function run_tests() {
        $this->results = array();
        
        // Performance optimization tests
        $this->test_query_counting();
        $this->test_memory_usage_tracking();
        $this->test_resource_cleanup();
        $this->test_database_connection();
        $this->test_options_loading();
        $this->test_hook_registration();
        $this->test_performance_stats();
        
        return $this->results;
    }
    
    /**
     * Test performance statistics functionality.
     */
    private function test_performance_stats() {
        $result = array('status' => 'success', 'details' => '');
        
        try {
            // Check if performance class exists and has the required method
            if (class_exists('WPCA_Performance') && method_exists('WPCA_Performance', 'get_instance')) {
                $performance = WPCA_Performance::get_instance();
                
                // Check if get_performance_stats method exists
                if (method_exists($performance, 'get_performance_stats')) {
                    $stats = $performance->get_performance_stats();
                    
                    if (is_array($stats) && !empty($stats)) {
                        // Validate key statistical metrics
                        $required_stats = array(
                            'total_samples',
                            'total_time',
                            'total_queries',
                            'average_memory',
                            'slow_queries',
                            'peak_memory'
                        );
                        
                        $missing_stats = array_diff($required_stats, array_keys($stats));
                        
                        if (empty($missing_stats)) {
                            $result['status'] = 'success';
                            $result['details'] = sprintf(
                                __('Performance statistics retrieved successfully. Found %d metrics including %d samples.', 'wp-clean-admin'),
                                count($stats),
                                $stats['total_samples']
                            );
                        } else {
                            $result['status'] = 'warning';
                            $result['details'] = sprintf(
                                __('Performance statistics missing required metrics: %s', 'wp-clean-admin'),
                                implode(', ', $missing_stats)
                            );
                        }
                    } else {
                        $result['status'] = 'warning';
                        $result['details'] = __('Performance statistics method exists but returned empty data.', 'wp-clean-admin');
                    }
                } else {
                    $result['status'] = 'warning';
                    $result['details'] = __('Performance class exists but get_performance_stats method is not available.', 'wp-clean-admin');
                }
            } else {
                $result['status'] = 'error';
                $result['details'] = __('Performance class not available.', 'wp-clean-admin');
            }
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'] = __('Exception during test: ', 'wp-clean-admin') . esc_html($e->getMessage());
        }
        
        $this->results[__('Performance Stats Test', 'wp-clean-admin')] = $result;
    }

    /**
     * Test query counting functionality.
     */
    private function test_query_counting() {
        $result = array('status' => 'success', 'details' => '');
        
        try {
            // Check if performance class exists
            if (class_exists('WPCA_Performance') && method_exists('WPCA_Performance', 'get_instance')) {
                $performance = WPCA_Performance::get_instance();
                
                // Simulate query counting
                global $wpdb;
                $old_queries = isset($wpdb->queries) ? count($wpdb->queries) : 0;
                
                // Run a test query
                $test_query = "SELECT 1 FROM {$wpdb->posts} LIMIT 1";
                $wpdb->get_var($test_query);
                
                $new_queries = isset($wpdb->queries) ? count($wpdb->queries) : 0;
                
                if ($new_queries > $old_queries) {
                    $result['status'] = 'success';
                    $result['details'] = sprintf(__('Query counting working properly. Detected %d new query.', 'wp-clean-admin'), ($new_queries - $old_queries));
                } else {
                    $result['status'] = 'warning';
                    $result['details'] = __('Could not detect query count change.', 'wp-clean-admin');
                }
            } else {
                $result['status'] = 'error';
                $result['details'] = __('Performance class not available.', 'wp-clean-admin');
            }
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'] = __('Exception during test: ', 'wp-clean-admin') . esc_html($e->getMessage());
        }
        
        $this->results[__('Query Counting Test', 'wp-clean-admin')] = $result;
    }

    /**
     * Test memory usage tracking functionality.
     */
    private function test_memory_usage_tracking() {
        $result = array('status' => 'success', 'details' => '');
        
        try {
            // Check if memory_get_usage function exists
            if (function_exists('memory_get_usage')) {
                $initial_memory = memory_get_usage();
                
                // Create a large array to consume memory
                $test_array = array_fill(0, 10000, 'test_string');
                $after_memory = memory_get_usage();
                
                if ($after_memory > $initial_memory) {
                    $memory_diff = ($after_memory - $initial_memory) / 1024; // Convert to KB
                    $result['status'] = 'success';
                    $result['details'] = sprintf(__('Memory tracking working. Memory increased by %.2f KB.', 'wp-clean-admin'), $memory_diff);
                } else {
                    $result['status'] = 'warning';
                    $result['details'] = __('Could not detect memory usage change.', 'wp-clean-admin');
                }
                
                // Free memory
                unset($test_array);
            } else {
                $result['status'] = 'warning';
                $result['details'] = __('memory_get_usage function not available on this server.', 'wp-clean-admin');
            }
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'] = __('Exception during test: ', 'wp-clean-admin') . esc_html($e->getMessage());
        }
        
        $this->results[__('Memory Usage Tracking Test', 'wp-clean-admin')] = $result;
    }

    /**
     * Test resource cleanup functionality.
     */
    private function test_resource_cleanup() {
        $result = array('status' => 'success', 'details' => '');
        
        try {
            // Check if resources class exists
            if (class_exists('WPCA_Resources') && method_exists('WPCA_Resources', 'get_instance')) {
                $resources = WPCA_Resources::get_instance();
                
                // Test if resources class can access its properties
                $reflector = new ReflectionClass($resources);
                $has_props = $reflector->hasProperty('css_to_remove') && $reflector->hasProperty('js_to_remove');
                
                if ($has_props) {
                    $result['status'] = 'success';
                    $result['details'] = __('Resources class loaded successfully with required properties.', 'wp-clean-admin');
                } else {
                    $result['status'] = 'warning';
                    $result['details'] = __('Resources class loaded but missing required properties.', 'wp-clean-admin');
                }
            } else {
                $result['status'] = 'error';
                $result['details'] = __('Resources class not available.', 'wp-clean-admin');
            }
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'] = __('Exception during test: ', 'wp-clean-admin') . esc_html($e->getMessage());
        }
        
        $this->results[__('Resource Cleanup Test', 'wp-clean-admin')] = $result;
    }

    /**
     * Test database connection functionality.
     */
    private function test_database_connection() {
        $result = array('status' => 'success', 'details' => '');
        
        try {
            // Check if database class exists
            if (class_exists('WPCA_Database')) {
                global $wpdb;
                
                // Test database connection with a simple query
                $test_result = $wpdb->get_var("SELECT VERSION()");
                
                if ($test_result) {
                    $result['status'] = 'success';
                    $result['details'] = sprintf(__('Database connection successful. MySQL version: %s', 'wp-clean-admin'), $test_result);
                } else {
                    $result['status'] = 'error';
                    $result['details'] = __('Database connection test failed.', 'wp-clean-admin');
                }
            } else {
                $result['status'] = 'error';
                $result['details'] = __('Database class not available.', 'wp-clean-admin');
            }
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'] = __('Exception during test: ', 'wp-clean-admin') . esc_html($e->getMessage());
        }
        
        $this->results[__('Database Connection Test', 'wp-clean-admin')] = $result;
    }

    /**
     * Test options loading functionality.
     */
    private function test_options_loading() {
        $result = array('status' => 'success', 'details' => '');
        
        try {
            // Test if options can be loaded
            if (function_exists('get_option')) {
                $options = get_option('wpca_settings', array());
                
                if (is_array($options)) {
                    $result['status'] = 'success';
                    $result['details'] = sprintf(__('Options loaded successfully. Found %d settings.', 'wp-clean-admin'), count($options));
                } else {
                    $result['status'] = 'warning';
                    $result['details'] = __('Options exist but are not in expected format.', 'wp-clean-admin');
                }
            } else {
                $result['status'] = 'error';
                $result['details'] = __('get_option function not available.', 'wp-clean-admin');
            }
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'] = __('Exception during test: ', 'wp-clean-admin') . esc_html($e->getMessage());
        }
        
        $this->results[__('Options Loading Test', 'wp-clean-admin')] = $result;
    }

    /**
     * Test hook registration functionality.
     */
    private function test_hook_registration() {
        $result = array('status' => 'success', 'details' => '');
        
        try {
            // Check if WordPress hooks system is available
            if (function_exists('has_action') && function_exists('add_action')) {
                // Test hook registration
                $test_hook_name = 'wpca_test_hook';
                add_action($test_hook_name, array($this, 'test_hook_callback'));
                
                if (has_action($test_hook_name, array($this, 'test_hook_callback'))) {
                    $result['status'] = 'success';
                    $result['details'] = __('Hook registration successful.', 'wp-clean-admin');
                    
                    // Remove the test hook
                    remove_action($test_hook_name, array($this, 'test_hook_callback'));
                } else {
                    $result['status'] = 'error';
                    $result['details'] = __('Hook registration failed.', 'wp-clean-admin');
                }
            } else {
                $result['status'] = 'error';
                $result['details'] = __('WordPress hooks system not available.', 'wp-clean-admin');
            }
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'] = __('Exception during test: ', 'wp-clean-admin') . esc_html($e->getMessage());
        }
        
        $this->results[__('Hook Registration Test', 'wp-clean-admin')] = $result;
    }

    /**
     * Test hook callback function.
     */
    public function test_hook_callback() {
        // This is just a test callback and doesn't need to do anything
    }

    /**
     * Show test results as admin notices.
     */
    public function show_test_results() {
        $total_tests = count($this->results);
        $passed_tests = 0;
        $warning_tests = 0;
        $failed_tests = 0;
        
        foreach ($this->results as $result) {
            switch ($result['status']) {
                case 'success':
                    $passed_tests++;
                    break;
                case 'warning':
                    $warning_tests++;
                    break;
                case 'error':
                    $failed_tests++;
                    break;
            }
        }
        
        $message = sprintf(
            __('Performance tests completed: %d passed, %d warnings, %d failed.', 'wp-clean-admin'),
            $passed_tests, $warning_tests, $failed_tests
        );
        
        $notice_class = 'notice-success';
        if ($failed_tests > 0) {
            $notice_class = 'notice-error';
        } elseif ($warning_tests > 0) {
            $notice_class = 'notice-warning';
        }
        
        echo "<div class='notice {$notice_class} is-dismissible'><p>{$message}</p></div>";
    }
    
    /**
     * Get the singleton instance of the class.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
}

// Initialize the tests class
new WPCA_Performance_Tests();
?>