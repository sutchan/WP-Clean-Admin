<?php
/**
 * Tests for the performance optimization module.
 * 
 * @package WP_Clean_Admin
 * @since 1.3.0
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
                                <th scope="col" class="manage-column"><?php esc_html_e( 'Message', 'wp-clean-admin' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $this->results as $test ) : ?>
                                <tr>
                                    <td class="test-name"><?php echo esc_html( $test['name'] ); ?></td>
                                    <td class="test-status">
                                        <span class="wpca-test-status wpca-test-status-<?php echo $test['status']; ?>">
                                            <?php echo $test['status'] === 'pass' ? esc_html__( 'Pass', 'wp-clean-admin' ) : esc_html__( 'Fail', 'wp-clean-admin' ); ?>
                                        </span>
                                    </td>
                                    <td class="test-message"><?php echo esc_html( $test['message'] ); ?></td>
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
     * Run all the tests.
     */
    public function run_tests() {
        $this->results = array();
        
        // Test component existence
        $this->test_component_existence();
        
        // Test database optimization
        $this->test_database_optimization();
        
        // Test resource management
        $this->test_resource_management();
        
        // Test performance monitoring
        $this->test_performance_monitoring();
        
        // Test settings page
        $this->test_settings_page();
        
        // Test AJAX handlers
        $this->test_ajax_handlers();
        
        return $this->results;
    }

    /**
     * Show test results as admin notice.
     */
    public function show_test_results() {
        $passed_tests = array_filter( $this->results, function( $test ) {
            return $test['status'] === 'pass';
        });
        
        $total_tests = count( $this->results );
        $passed_count = count( $passed_tests );
        
        $class = 'notice notice-success';
        $message = sprintf( esc_html__( 'Performance tests completed. %d of %d tests passed.', 'wp-clean-admin' ), $passed_count, $total_tests );
        
        if ( $passed_count < $total_tests ) {
            $class = 'notice notice-error';
            $message .= ' ' . esc_html__( 'Some tests failed. Please check the details below.', 'wp-clean-admin' );
        }
        
        printf( '<div class="%s"><p>%s</p></div>', esc_attr( $class ), esc_html( $message ) );
    }

    /**
     * Test if all required components exist.
     */
    protected function test_component_existence() {
        // Test Performance component
        $test_name = esc_html__( 'Performance Component Existence', 'wp-clean-admin' );
        if ( class_exists( 'WPCA_Performance' ) ) {
            $this->add_test_result( $test_name, 'pass', esc_html__( 'Performance component exists.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Performance component not found.', 'wp-clean-admin' ) );
        }
        
        // Test Resources component
        $test_name = esc_html__( 'Resources Component Existence', 'wp-clean-admin' );
        if ( class_exists( 'WPCA_Resources' ) ) {
            $this->add_test_result( $test_name, 'pass', esc_html__( 'Resources component exists.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Resources component not found.', 'wp-clean-admin' ) );
        }
        
        // Test Database component
        $test_name = esc_html__( 'Database Component Existence', 'wp-clean-admin' );
        if ( class_exists( 'WPCA_Database' ) ) {
            $this->add_test_result( $test_name, 'pass', esc_html__( 'Database component exists.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Database component not found.', 'wp-clean-admin' ) );
        }
        
        // Test Performance Settings component
        $test_name = esc_html__( 'Performance Settings Component Existence', 'wp-clean-admin' );
        if ( class_exists( 'WPCA_Performance_Settings' ) ) {
            $this->add_test_result( $test_name, 'pass', esc_html__( 'Performance Settings component exists.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Performance Settings component not found.', 'wp-clean-admin' ) );
        }
    }

    /**
     * Test database optimization functionality.
     */
    protected function test_database_optimization() {
        $test_name = esc_html__( 'Database Optimization', 'wp-clean-admin' );
        
        if ( ! class_exists( 'WPCA_Database' ) ) {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Cannot test database optimization: component not found.', 'wp-clean-admin' ) );
            return;
        }
        
        $database = WPCA_Database::get_instance();
        
        // Test getting database stats
        $stats = $database->get_database_stats();
        if ( is_array( $stats ) && array_key_exists( 'table_count', $stats ) ) {
            $this->add_test_result( $test_name, 'pass', esc_html__( 'Database statistics retrieval successful.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Failed to retrieve database statistics.', 'wp-clean-admin' ) );
        }
        
        // Test getting tables
        $tables = $database->get_all_tables();
        if ( is_array( $tables ) && count( $tables ) > 0 ) {
            $this->add_test_result( esc_html__( 'Database Tables Retrieval', 'wp-clean-admin' ), 'pass', esc_html__( 'Successfully retrieved database tables.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( esc_html__( 'Database Tables Retrieval', 'wp-clean-admin' ), 'fail', esc_html__( 'Failed to retrieve database tables.', 'wp-clean-admin' ) );
        }
        
        // Test cleanup items
        $cleanup_items = $database->get_available_cleanup_items();
        if ( is_array( $cleanup_items ) && count( $cleanup_items ) > 0 ) {
            $this->add_test_result( esc_html__( 'Database Cleanup Items', 'wp-clean-admin' ), 'pass', esc_html__( 'Successfully retrieved cleanup items.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( esc_html__( 'Database Cleanup Items', 'wp-clean-admin' ), 'fail', esc_html__( 'Failed to retrieve cleanup items.', 'wp-clean-admin' ) );
        }
    }

    /**
     * Test resource management functionality.
     */
    protected function test_resource_management() {
        $test_name = esc_html__( 'Resource Management', 'wp-clean-admin' );
        
        if ( ! class_exists( 'WPCA_Resources' ) ) {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Cannot test resource management: component not found.', 'wp-clean-admin' ) );
            return;
        }
        
        $resources = WPCA_Resources::get_instance();
        
        // Test getting CSS resources
        $css_resources = $resources->get_loaded_styles();
        if ( is_array( $css_resources ) ) {
            $this->add_test_result( esc_html__( 'CSS Resources Retrieval', 'wp-clean-admin' ), 'pass', sprintf( esc_html__( 'Successfully retrieved %d CSS resources.', 'wp-clean-admin' ), count( $css_resources ) ) );
        } else {
            $this->add_test_result( esc_html__( 'CSS Resources Retrieval', 'wp-clean-admin' ), 'fail', esc_html__( 'Failed to retrieve CSS resources.', 'wp-clean-admin' ) );
        }
        
        // Test getting JS resources
        $js_resources = $resources->get_loaded_scripts();
        if ( is_array( $js_resources ) ) {
            $this->add_test_result( esc_html__( 'JavaScript Resources Retrieval', 'wp-clean-admin' ), 'pass', sprintf( esc_html__( 'Successfully retrieved %d JavaScript resources.', 'wp-clean-admin' ), count( $js_resources ) ) );
        } else {
            $this->add_test_result( esc_html__( 'JavaScript Resources Retrieval', 'wp-clean-admin' ), 'fail', esc_html__( 'Failed to retrieve JavaScript resources.', 'wp-clean-admin' ) );
        }
    }

    /**
     * Test performance monitoring functionality.
     */
    protected function test_performance_monitoring() {
        $test_name = esc_html__( 'Performance Monitoring', 'wp-clean-admin' );
        
        if ( ! class_exists( 'WPCA_Performance' ) ) {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Cannot test performance monitoring: component not found.', 'wp-clean-admin' ) );
            return;
        }
        
        $performance = WPCA_Performance::get_instance();
        
        // Test getting performance stats
        $stats = $performance->get_performance_stats();
        if ( is_array( $stats ) ) {
            $this->add_test_result( esc_html__( 'Performance Statistics', 'wp-clean-admin' ), 'pass', esc_html__( 'Successfully retrieved performance statistics.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( esc_html__( 'Performance Statistics', 'wp-clean-admin' ), 'fail', esc_html__( 'Failed to retrieve performance statistics.', 'wp-clean-admin' ) );
        }
    }

    /**
     * Test settings page functionality.
     */
    protected function test_settings_page() {
        $test_name = esc_html__( 'Settings Page', 'wp-clean-admin' );
        
        if ( ! class_exists( 'WPCA_Performance_Settings' ) ) {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Cannot test settings page: component not found.', 'wp-clean-admin' ) );
            return;
        }
        
        // Check if menu page is registered
        global $submenu;
        $menu_exists = false;
        
        if ( isset( $submenu['wpca-settings'] ) ) {
            foreach ( $submenu['wpca-settings'] as $menu_item ) {
                if ( 'wpca-performance' === $menu_item[2] ) {
                    $menu_exists = true;
                    break;
                }
            }
        }
        
        if ( $menu_exists ) {
            $this->add_test_result( $test_name, 'pass', esc_html__( 'Performance settings page is registered.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Performance settings page is not registered.', 'wp-clean-admin' ) );
        }
    }

    /**
     * Test AJAX handlers.
     */
    protected function test_ajax_handlers() {
        $test_name = esc_html__( 'AJAX Handlers', 'wp-clean-admin' );
        
        // This is a basic check to see if AJAX actions are registered
        // In a real test environment, we would use wp_ajax_get_ajax_data() or similar
        
        // Check for common AJAX actions
        $ajax_actions = array(
            'wpca_get_table_info',
            'wpca_optimize_tables',
            'wpca_cleanup_database',
            'wpca_test_resource_removal',
            'wpca_generate_critical_css',
            'wpca_toggle_monitoring',
            'wpca_get_performance_report',
            'wpca_clear_performance_data',
        );
        
        $found_actions = 0;
        
        foreach ( $ajax_actions as $action ) {
            global $wp_filter;
            $has_action = false;
            
            if ( isset( $wp_filter['wp_ajax_' . $action] ) ) {
                $has_action = true;
                $found_actions++;
            }
        }
        
        if ( $found_actions > 0 ) {
            $this->add_test_result( $test_name, 'pass', sprintf( esc_html__( 'Found %d of %d expected AJAX actions.', 'wp-clean-admin' ), $found_actions, count( $ajax_actions ) ) );
        } else {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'No AJAX actions found.', 'wp-clean-admin' ) );
        }
    }

    /**
     * Add a test result.
     *
     * @param string $name    Test name.
     * @param string $status  Test status ('pass' or 'fail').
     * @param string $message Test message.
     */
    protected function add_test_result( $name, $status, $message ) {
        $this->results[] = array(
            'name'    => $name,
            'status'  => $status,
            'message' => $message,
        );
    }

    /**
     * Get the singleton instance of the class.
     *
     * @return WPCA_Performance_Tests The instance of the class.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
}

/**
 * Initialize the tests class.
 */
function wpca_init_performance_tests() {
    return WPCA_Performance_Tests::get_instance();
}

// Initialize the tests if we're in admin area
if ( is_admin() ) {
    wpca_init_performance_tests();
}