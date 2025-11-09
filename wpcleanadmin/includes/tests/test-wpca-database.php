<?php
/**
 * Tests for the database optimization module.
 * 
 * @package WP_Clean_Admin
 * @since 1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * WPCA_Database_Tests class.
 * 
 * Contains tests for the database optimization module.
 */
class WPCA_Database_Tests {

    /**
     * Instance of the class.
     *
     * @var WPCA_Database_Tests
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
            esc_html__( 'WPCA Database Tests', 'wp-clean-admin' ),
            esc_html__( 'Database Tests', 'wp-clean-admin' ),
            'manage_options',
            'wpca-database-tests',
            array( $this, 'render_test_page' ),
            99
        );
    }

    /**
     * Run tests when requested.
     */
    public function maybe_run_tests() {
        if ( isset( $_POST['wpca_run_db_tests'] ) && check_admin_referer( 'wpca-run-db-tests' ) ) {
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
            <h1><?php esc_html_e( 'WP Clean Admin - Database Tests', 'wp-clean-admin' ); ?></h1>
            
            <div class="wpca-test-description">
                <p><?php esc_html_e( 'This page allows you to test the functionality of the database optimization module. Click the button below to run the tests.', 'wp-clean-admin' ); ?></p>
                <p class="description"><?php esc_html_e( 'Note: These tests are for development purposes only and should not be run on production sites.', 'wp-clean-admin' ); ?></p>
            </div>
            
            <form method="post">
                <?php wp_nonce_field( 'wpca-run-db-tests' ); ?>
                <input type="hidden" name="wpca_run_db_tests" value="1" />
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
        
        // Test database information retrieval
        $this->test_database_information();
        
        // Test cleanup functionality
        $this->test_cleanup_functionality();
        
        // Test table optimization
        $this->test_table_optimization();
        
        // Test auto cleanup scheduler
        $this->test_auto_cleanup_scheduler();
        
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
        $message = sprintf( esc_html__( 'Database tests completed. %d of %d tests passed.', 'wp-clean-admin' ), $passed_count, $total_tests );
        
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
        // Test Database component
        $test_name = esc_html__( 'Database Component Existence', 'wp-clean-admin' );
        if ( class_exists( 'WPCA_Database' ) ) {
            $this->add_test_result( $test_name, 'pass', esc_html__( 'Database component exists.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Database component not found.', 'wp-clean-admin' ) );
        }
        
        // Test Database Settings component
        $test_name = esc_html__( 'Database Settings Component Existence', 'wp-clean-admin' );
        if ( class_exists( 'WPCA_Database_Settings' ) ) {
            $this->add_test_result( $test_name, 'pass', esc_html__( 'Database Settings component exists.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Database Settings component not found.', 'wp-clean-admin' ) );
        }
        
        // Test Permissions component
        $test_name = esc_html__( 'Permissions Component Existence', 'wp-clean-admin' );
        if ( class_exists( 'WPCA_Permissions' ) ) {
            $this->add_test_result( $test_name, 'pass', esc_html__( 'Permissions component exists.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Permissions component not found.', 'wp-clean-admin' ) );
        }
    }

    /**
     * Test database information retrieval functionality.
     */
    protected function test_database_information() {
        $test_name = esc_html__( 'Database Information Retrieval', 'wp-clean-admin' );
        
        if ( ! class_exists( 'WPCA_Database' ) ) {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Cannot test database information: component not found.', 'wp-clean-admin' ) );
            return;
        }
        
        $database = WPCA_Database::get_instance();
        
        // Test getting database info
        if ( method_exists( $database, 'get_database_info' ) ) {
            $info = $database->get_database_info();
            if ( is_array( $info ) && array_key_exists( 'version', $info ) && array_key_exists( 'table_count', $info ) ) {
                $this->add_test_result( esc_html__( 'Database Info Retrieval', 'wp-clean-admin' ), 'pass', esc_html__( 'Successfully retrieved database information.', 'wp-clean-admin' ) );
            } else {
                $this->add_test_result( esc_html__( 'Database Info Retrieval', 'wp-clean-admin' ), 'fail', esc_html__( 'Failed to retrieve database information.', 'wp-clean-admin' ) );
            }
        } else {
            $this->add_test_result( esc_html__( 'Database Info Method', 'wp-clean-admin' ), 'fail', esc_html__( 'get_database_info method not found.', 'wp-clean-admin' ) );
        }
        
        // Test getting cleanup stats
        if ( method_exists( $database, 'get_cleanup_stats' ) ) {
            $stats = $database->get_cleanup_stats();
            if ( is_array( $stats ) ) {
                $this->add_test_result( esc_html__( 'Cleanup Statistics Retrieval', 'wp-clean-admin' ), 'pass', esc_html__( 'Successfully retrieved cleanup statistics.', 'wp-clean-admin' ) );
            } else {
                $this->add_test_result( esc_html__( 'Cleanup Statistics Retrieval', 'wp-clean-admin' ), 'fail', esc_html__( 'Failed to retrieve cleanup statistics.', 'wp-clean-admin' ) );
            }
        } else {
            $this->add_test_result( esc_html__( 'Cleanup Stats Method', 'wp-clean-admin' ), 'fail', esc_html__( 'get_cleanup_stats method not found.', 'wp-clean-admin' ) );
        }
    }

    /**
     * Test cleanup functionality.
     */
    protected function test_cleanup_functionality() {
        $test_name = esc_html__( 'Database Cleanup Functionality', 'wp-clean-admin' );
        
        if ( ! class_exists( 'WPCA_Database' ) ) {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Cannot test cleanup functionality: component not found.', 'wp-clean-admin' ) );
            return;
        }
        
        $database = WPCA_Database::get_instance();
        
        // Test cleanup_database method exists
        if ( method_exists( $database, 'cleanup_database' ) ) {
            $this->add_test_result( esc_html__( 'Cleanup Method Existence', 'wp-clean-admin' ), 'pass', esc_html__( 'cleanup_database method exists.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( esc_html__( 'Cleanup Method Existence', 'wp-clean-admin' ), 'fail', esc_html__( 'cleanup_database method not found.', 'wp-clean-admin' ) );
        }
        
        // Test individual cleanup methods
        $cleanup_methods = array(
            'cleanup_old_revisions',
            'cleanup_auto_drafts',
            'cleanup_trashed_posts',
            'cleanup_spam_comments',
            'cleanup_trashed_comments',
            'cleanup_pingbacks_trackbacks',
            'cleanup_orphaned_postmeta',
            'cleanup_orphaned_commentmeta',
            'cleanup_orphaned_relationships',
            'cleanup_orphaned_usermeta',
            'cleanup_expired_transients',
            'cleanup_all_transients',
            'cleanup_oembed_caches'
        );
        
        $found_methods = 0;
        foreach ( $cleanup_methods as $method ) {
            if ( method_exists( $database, $method ) ) {
                $found_methods++;
            }
        }
        
        if ( $found_methods > 0 ) {
            $this->add_test_result( esc_html__( 'Cleanup Methods Existence', 'wp-clean-admin' ), 'pass', sprintf( esc_html__( 'Found %d of %d expected cleanup methods.', 'wp-clean-admin' ), $found_methods, count( $cleanup_methods ) ) );
        } else {
            $this->add_test_result( esc_html__( 'Cleanup Methods Existence', 'wp-clean-admin' ), 'fail', esc_html__( 'No cleanup methods found.', 'wp-clean-admin' ) );
        }
    }

    /**
     * Test table optimization functionality.
     */
    protected function test_table_optimization() {
        $test_name = esc_html__( 'Table Optimization Functionality', 'wp-clean-admin' );
        
        if ( ! class_exists( 'WPCA_Database' ) ) {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Cannot test table optimization: component not found.', 'wp-clean-admin' ) );
            return;
        }
        
        $database = WPCA_Database::get_instance();
        
        // Test optimize_tables method exists
        if ( method_exists( $database, 'optimize_tables' ) ) {
            $this->add_test_result( esc_html__( 'Optimize Method Existence', 'wp-clean-admin' ), 'pass', esc_html__( 'optimize_tables method exists.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( esc_html__( 'Optimize Method Existence', 'wp-clean-admin' ), 'fail', esc_html__( 'optimize_tables method not found.', 'wp-clean-admin' ) );
        }
        
        // Test sanitize_table_name method exists
        if ( method_exists( $database, 'sanitize_table_name' ) ) {
            $this->add_test_result( esc_html__( 'Table Name Sanitization', 'wp-clean-admin' ), 'pass', esc_html__( 'sanitize_table_name method exists.', 'wp-clean-admin' ) );
        } else {
            $this->add_test_result( esc_html__( 'Table Name Sanitization', 'wp-clean-admin' ), 'fail', esc_html__( 'sanitize_table_name method not found.', 'wp-clean-admin' ) );
        }
    }

    /**
     * Test auto cleanup scheduler.
     */
    protected function test_auto_cleanup_scheduler() {
        $test_name = esc_html__( 'Auto Cleanup Scheduler', 'wp-clean-admin' );
        
        if ( ! class_exists( 'WPCA_Database' ) ) {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Cannot test auto cleanup scheduler: component not found.', 'wp-clean-admin' ) );
            return;
        }
        
        $database = WPCA_Database::get_instance();
        
        // Test scheduler methods
        $scheduler_methods = array(
            'set_auto_cleanup_schedule',
            'remove_auto_cleanup_schedule',
            'run_auto_cleanup'
        );
        
        $found_methods = 0;
        foreach ( $scheduler_methods as $method ) {
            if ( method_exists( $database, $method ) ) {
                $found_methods++;
            }
        }
        
        if ( $found_methods > 0 ) {
            $this->add_test_result( esc_html__( 'Scheduler Methods Existence', 'wp-clean-admin' ), 'pass', sprintf( esc_html__( 'Found %d of %d expected scheduler methods.', 'wp-clean-admin' ), $found_methods, count( $scheduler_methods ) ) );
        } else {
            $this->add_test_result( esc_html__( 'Scheduler Methods Existence', 'wp-clean-admin' ), 'fail', esc_html__( 'No scheduler methods found.', 'wp-clean-admin' ) );
        }
    }

    /**
     * Test AJAX handlers.
     */
    protected function test_ajax_handlers() {
        $test_name = esc_html__( 'Database AJAX Handlers', 'wp-clean-admin' );
        
        if ( ! class_exists( 'WPCA_Database' ) ) {
            $this->add_test_result( $test_name, 'fail', esc_html__( 'Cannot test AJAX handlers: component not found.', 'wp-clean-admin' ) );
            return;
        }
        
        $database = WPCA_Database::get_instance();
        
        // Test AJAX handler methods exist
        $ajax_methods = array(
            'ajax_optimize_tables',
            'ajax_cleanup_database',
            'ajax_get_database_info',
            'ajax_get_cleanup_stats'
        );
        
        $found_methods = 0;
        foreach ( $ajax_methods as $method ) {
            if ( method_exists( $database, $method ) ) {
                $found_methods++;
            }
        }
        
        if ( $found_methods > 0 ) {
            $this->add_test_result( esc_html__( 'AJAX Methods Existence', 'wp-clean-admin' ), 'pass', sprintf( esc_html__( 'Found %d of %d expected AJAX methods.', 'wp-clean-admin' ), $found_methods, count( $ajax_methods ) ) );
        } else {
            $this->add_test_result( esc_html__( 'AJAX Methods Existence', 'wp-clean-admin' ), 'fail', esc_html__( 'No AJAX methods found.', 'wp-clean-admin' ) );
        }
        
        // Check if AJAX actions are registered
        $ajax_actions = array(
            'wpca_optimize_tables',
            'wpca_cleanup_database',
            'wpca_get_database_info',
            'wpca_get_cleanup_stats'
        );
        
        $found_actions = 0;
        foreach ( $ajax_actions as $action ) {
            global $wp_filter;
            if ( isset( $wp_filter['wp_ajax_' . $action] ) ) {
                $found_actions++;
            }
        }
        
        if ( $found_actions > 0 ) {
            $this->add_test_result( esc_html__( 'AJAX Actions Registration', 'wp-clean-admin' ), 'pass', sprintf( esc_html__( 'Found %d of %d expected AJAX actions.', 'wp-clean-admin' ), $found_actions, count( $ajax_actions ) ) );
        } else {
            $this->add_test_result( esc_html__( 'AJAX Actions Registration', 'wp-clean-admin' ), 'fail', esc_html__( 'No AJAX actions found.', 'wp-clean-admin' ) );
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
     * @return WPCA_Database_Tests The instance of the class.
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
function wpca_init_database_tests() {
    return WPCA_Database_Tests::get_instance();
}

// Initialize the tests if we're in admin area
if ( is_admin() ) {
    wpca_init_database_tests();
}
