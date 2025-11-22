<?php
/**
 * WP Clean Admin Performance Settings
 * 
 * Provides settings page and UI for performance optimization features.
 * Handles database optimization, resource management, and performance monitoring.
 * 
 * @package WP_Clean_Admin
 * @since 1.3.0
 * @version 1.7.12
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * WPCA_Performance_Settings class.
 * 
 * Manages performance optimization settings page and UI components.
 */
class WPCA_Performance_Settings {

    /**
     * Instance of the class.
     *
     * @var WPCA_Performance_Settings
     */
    protected static $instance = null;

    /**
     * Performance component instance.
     *
     * @var WPCA_Performance
     */
    protected $performance;

    /**
     * Resources component instance.
     *
     * @var WPCA_Resources
     */
    protected $resources;

    /**
     * Database component instance.
     *
     * @var WPCA_Database
     */
    protected $database;

    /**
     * Settings page slug.
     *
     * @var string
     */
    protected $page_slug = 'wpca-performance';

    /**
     * Current tab.
     *
     * @var string
     */
    protected $current_tab = 'database';

    /**
     * Available tabs.
     *
     * @var array
     */
    protected $tabs = array();

    /**
     * Initialize the class.
     */
    public function __construct() {
        // Check if required components exist
        if ( class_exists( 'WPCA_Performance' ) ) {
            $this->performance = WPCA_Performance::get_instance();
        }
        
        if ( class_exists( 'WPCA_Resources' ) ) {
            $this->resources = WPCA_Resources::get_instance();
        }
        
        if ( class_exists( 'WPCA_Database' ) ) {
            $this->database = WPCA_Database::get_instance();
        }

        $this->init_tabs();
        $this->hooks();
    }

    /**
     * Hook into WordPress.
     */
    public function hooks() {
        add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'wp_ajax_wpca_get_table_info', array( $this, 'ajax_get_table_info' ) );
    }

    /**
     * Initialize tabs.
     */
    public function init_tabs() {
        $this->tabs = array(
            'database' => array(
                'title'    => esc_html__( 'Database Optimization', 'wp-clean-admin' ),
                'callback' => array( $this, 'render_database_tab' ),
                'icon'     => 'dashicons-database',
            ),
            'resources' => array(
                'title'    => esc_html__( 'Resource Management', 'wp-clean-admin' ),
                'callback' => array( $this, 'render_resources_tab' ),
                'icon'     => 'dashicons-media-code',
            ),
            'monitoring' => array(
                'title'    => esc_html__( 'Performance Monitoring', 'wp-clean-admin' ),
                'callback' => array( $this, 'render_monitoring_tab' ),
                'icon'     => 'dashicons-chart-pie',
            ),
            'settings' => array(
                'title'    => esc_html__( 'Settings', 'wp-clean-admin' ),
                'callback' => array( $this, 'render_settings_tab' ),
                'icon'     => 'dashicons-admin-settings',
            ),
        );
    }

    /**
     * Register the settings page.
     */
    public function register_menu_page() {
        add_submenu_page(
            'wpca-settings',
            esc_html__( 'Performance Optimization', 'wp-clean-admin' ),
            esc_html__( 'Performance', 'wp-clean-admin' ),
            'manage_options',
            $this->page_slug,
            array( $this, 'render_settings_page' ),
            20
        );
    }

    /**
     * Register settings.
     */
    public function register_settings() {
        // Performance monitoring settings
        register_setting( 'wpca-performance-monitoring', 'wpca_monitoring_enabled', array( 'default' => false ) );
        register_setting( 'wpca-performance-monitoring', 'wpca_monitoring_sample_rate', array( 'default' => 10 ) );
        register_setting( 'wpca-performance-monitoring', 'wpca_monitoring_data_retention', array( 'default' => 7 ) );
        
        // Resource management settings
        register_setting( 'wpca-resource-management', 'wpca_remove_unused_css', array( 'default' => false ) );
        register_setting( 'wpca-resource-management', 'wpca_delay_non_critical_js', array( 'default' => false ) );
        register_setting( 'wpca-resource-management', 'wpca_enable_critical_css', array( 'default' => false ) );
        register_setting( 'wpca-resource-management', 'wpca_combine_css', array( 'default' => false ) );
        register_setting( 'wpca-resource-management', 'wpca_combine_js', array( 'default' => false ) );
        
        // Database optimization settings
        register_setting( 'wpca-database-optimization', 'wpca_auto_optimize_tables', array( 'default' => false ) );
        register_setting( 'wpca-database-optimization', 'wpca_optimize_interval', array( 'default' => 7 ) );
        register_setting( 'wpca-database-optimization', 'wpca_cleanup_interval', array( 'default' => 30 ) );
        register_setting( 'wpca-database-optimization', 'wpca_cleanup_items', array( 'default' => array() ) );
    }

    /**
     * Enqueue scripts and styles.
     *
     * @param string $hook Hook suffix.
     */
    public function enqueue_scripts( $hook ) {
        // Only enqueue on our settings page
        if ( 'settings_page_' . $this->page_slug !== $hook ) {
            return;
        }

        // Enqueue performance module scripts
        wp_enqueue_script(
            'wpca-performance',
            WPCA_PLUGIN_URL . 'assets/js/wpca-performance.js',
            array( 'jquery' ),
            WPCA_VERSION,
            true
        );

        // Enqueue performance module styles
        wp_enqueue_style(
            'wpca-performance',
            WPCA_PLUGIN_URL . 'assets/css/wpca-performance.css',
            array(),
            WPCA_VERSION
        );

        // Localize script with necessary variables
        wp_localize_script( 'wpca-performance', 'WPCA', array(
            'i18n' => array(
                'processing'                => esc_html__( 'Processing...', 'wp-clean-admin' ),
                'dismiss'                   => esc_html__( 'Dismiss', 'wp-clean-admin' ),
                'selectTablesFirst'         => esc_html__( 'Please select at least one table to optimize.', 'wp-clean-admin' ),
                'confirmOptimizeTables'     => esc_html__( 'Are you sure you want to optimize the selected tables? This may take a few moments.', 'wp-clean-admin' ),
                'optimizeSuccess'           => esc_html__( 'Successfully optimized %d of %d tables.', 'wp-clean-admin' ),
                'optimizeFailed'            => esc_html__( 'Table optimization failed.', 'wp-clean-admin' ),
                'selectCleanupItemsFirst'   => esc_html__( 'Please select at least one cleanup item.', 'wp-clean-admin' ),
                'confirmCleanup'            => esc_html__( 'Are you sure you want to run the selected cleanup tasks? This cannot be undone.', 'wp-clean-admin' ),
                'cleanupSuccess'            => esc_html__( 'Successfully removed %d items from the database.', 'wp-clean-admin' ),
                'cleanupFailed'             => esc_html__( 'Database cleanup failed.', 'wp-clean-admin' ),
                'confirmTestResourceRemoval'=> esc_html__( 'Are you sure you want to test removing this resource? This will not affect your live site.', 'wp-clean-admin' ),
                'testResourceFailed'        => esc_html__( 'Resource removal test failed.', 'wp-clean-admin' ),
                'generateCssFailed'         => esc_html__( 'Critical CSS generation failed.', 'wp-clean-admin' ),
                'generated'                 => esc_html__( 'Generated', 'wp-clean-admin' ),
                'startMonitoring'           => esc_html__( 'Start Monitoring', 'wp-clean-admin' ),
                'stopMonitoring'            => esc_html__( 'Stop Monitoring', 'wp-clean-admin' ),
                'monitoringStarted'         => esc_html__( 'Performance monitoring has been started.', 'wp-clean-admin' ),
                'monitoringStopped'         => esc_html__( 'Performance monitoring has been stopped.', 'wp-clean-admin' ),
                'toggleMonitoringFailed'    => esc_html__( 'Failed to toggle monitoring status.', 'wp-clean-admin' ),
                'performanceReport'         => esc_html__( 'Performance Report', 'wp-clean-admin' ),
                'reportFailed'              => esc_html__( 'Failed to generate performance report.', 'wp-clean-admin' ),
                'confirmClearData'          => esc_html__( 'Are you sure you want to clear all performance monitoring data? This cannot be undone.', 'wp-clean-admin' ),
                'dataCleared'               => esc_html__( 'Performance data has been cleared.', 'wp-clean-admin' ),
                'clearDataFailed'           => esc_html__( 'Failed to clear performance data.', 'wp-clean-admin' ),
                'cleanupSummary'            => esc_html__( 'Selected %d of %d cleanup items.', 'wp-clean-admin' ),
            ),
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ));
    }

    /**
     * Render the main settings page.
     */
    public function render_settings_page() {
        // Get current tab
        $this->current_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->tabs ) ? $_GET['tab'] : 'database';

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WP Clean Admin - Performance Optimization', 'wp-clean-admin' ); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <?php foreach ( $this->tabs as $tab_id => $tab ) : ?>
                    <a href="?page=<?php echo esc_attr( $this->page_slug ); ?>&tab=<?php echo esc_attr( $tab_id ); ?>" 
                       class="nav-tab <?php echo $this->current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                        <span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>" style="margin-top: 3px;"></span>
                        <?php echo esc_html( $tab['title'] ); ?>
                    </a>
                <?php endforeach; ?>
            </h2>

            <div class="wpca-settings-content">
                <?php call_user_func( $this->tabs[ $this->current_tab ]['callback'] ); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render the database optimization tab.
     */
    public function render_database_tab() {
        $database_stats = $this->database ? $this->database->get_database_stats() : array();
        $tables = $this->database ? $this->database->get_all_tables() : array();
        $cleanup_items = $this->database ? $this->database->get_available_cleanup_items() : array();
        $optimization_status = $this->database ? $this->database->get_optimization_status() : array();
        
        $auto_optimize_enabled = get_option( 'wpca_auto_optimize_tables', false );
        $optimize_interval = get_option( 'wpca_optimize_interval', 7 );
        $cleanup_interval = get_option( 'wpca_cleanup_interval', 30 );
        
        // Security nonce
        $optimize_nonce = wp_create_nonce( 'wpca-optimize-tables' );
        $cleanup_nonce = wp_create_nonce( 'wpca-cleanup-database' );
        
        ?>
        <div class="wpca-performance-section">
            <h3><?php esc_html_e( 'Database Overview', 'wp-clean-admin' ); ?></h3>
            <div class="wpca-database-stats">
                <div class="stat-item">
                    <span class="stat-value"><?php echo esc_html( $database_stats['table_count'] ?? 0 ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Tables', 'wp-clean-admin' ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo esc_html( size_format( $database_stats['total_size'] ?? 0 ) ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Total Size', 'wp-clean-admin' ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo esc_html( size_format( $database_stats['overhead_size'] ?? 0 ) ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Overhead', 'wp-clean-admin' ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo esc_html( $optimization_status['optimized_tables'] ?? 0 ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Optimized Tables', 'wp-clean-admin' ); ?></span>
                </div>
            </div>
        </div>

        <div class="wpca-performance-section">
            <h3><?php esc_html_e( 'Table Optimization', 'wp-clean-admin' ); ?></h3>
            <p><?php esc_html_e( 'Optimizing database tables can improve performance by reclaiming unused space and defragmenting data. This operation is safe and will not delete any data.', 'wp-clean-admin' ); ?></p>
            
            <div class="wpca-table-list">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-cb check-column">
                                <input type="checkbox" id="wpca-select-all-tables" />
                            </th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Table Name', 'wp-clean-admin' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Size', 'wp-clean-admin' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Overhead', 'wp-clean-admin' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Status', 'wp-clean-admin' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $tables as $table ) : ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" class="wpca-table-checkbox" value="<?php echo esc_attr( $table['name'] ); ?>" <?php checked( $table['overhead'] > 0 ); ?> />
                                </th>
                                <td><?php echo esc_html( $table['name'] ); ?></td>
                                <td class="table-size"><?php echo esc_html( size_format( $table['size'] ) ); ?></td>
                                <td class="table-size"><?php echo esc_html( size_format( $table['overhead'] ) ); ?></td>
                                <td>
                                    <span class="table-status <?php echo $table['overhead'] > 0 ? 'table-status-needs-optimization' : 'table-status-optimal'; ?>">
                                        <?php echo $table['overhead'] > 0 ? esc_html__( 'Needs Optimization', 'wp-clean-admin' ) : esc_html__( 'Optimal', 'wp-clean-admin' ); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <button id="wpca-optimize-tables" class="wpca-button wpca-button-primary" data-nonce="<?php echo esc_attr( $optimize_nonce ); ?>">
                <?php esc_html_e( 'Optimize Selected Tables', 'wp-clean-admin' ); ?>
            </button>
        </div>

        <div class="wpca-performance-section">
            <h3><?php esc_html_e( 'Database Cleanup', 'wp-clean-admin' ); ?></h3>
            <p><?php esc_html_e( 'Clean up unnecessary data from your database to improve performance and reduce database size. Be careful as this will permanently delete data.', 'wp-clean-admin' ); ?></p>
            
            <div class="wpca-cleanup-list">
                <?php foreach ( $cleanup_items as $key => $item ) : ?>
                    <div class="wpca-cleanup-item-row">
                        <label>
                            <input type="checkbox" class="wpca-cleanup-item" value="<?php echo esc_attr( $key ); ?>" />
                            <?php echo esc_html( $item['title'] ); ?>
                            <?php if ( $item['requires_days'] ) : ?>
                                <input type="number" min="1" max="365" value="30" class="wpca-cleanup-days" id="wpca-<?php echo esc_attr( $key ); ?>-days" />
                                <?php esc_html_e( 'days old or older', 'wp-clean-admin' ); ?>
                            <?php endif; ?>
                        </label>
                        <span class="wpca-cleanup-description"><?php echo esc_html( $item['description'] ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="wpca-cleanup-summary">
                <span id="wpca-cleanup-summary"><?php esc_html_e( 'Selected 0 of 0 cleanup items.', 'wp-clean-admin' ); ?></span>
            </div>
            
            <button id="wpca-cleanup-database" class="wpca-button wpca-button-warning" data-nonce="<?php echo esc_attr( $cleanup_nonce ); ?>">
                <?php esc_html_e( 'Run Selected Cleanup Tasks', 'wp-clean-admin' ); ?>
            </button>
        </div>
        
        <div class="wpca-performance-section">
            <h3><?php esc_html_e( 'Scheduled Optimization Settings', 'wp-clean-admin' ); ?></h3>
            <p><?php esc_html_e( 'Configure automatic database optimization and cleanup schedules to maintain optimal performance.', 'wp-clean-admin' ); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'wpca-database-optimization' ); ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="wpca_auto_optimize_tables"><?php esc_html_e( 'Automatic Table Optimization', 'wp-clean-admin' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wpca_auto_optimize_tables" name="wpca_auto_optimize_tables" value="1" <?php checked( $auto_optimize_enabled ); ?> />
                            <label for="wpca_auto_optimize_tables"><?php esc_html_e( 'Enable automatic table optimization', 'wp-clean-admin' ); ?></label>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">
                            <label for="wpca_optimize_interval"><?php esc_html_e( 'Optimization Interval', 'wp-clean-admin' ); ?></label>
                        </th>
                        <td>
                            <input type="number" min="1" max="90" id="wpca_optimize_interval" name="wpca_optimize_interval" value="<?php echo esc_attr( $optimize_interval ); ?>" />
                            <span class="description"><?php esc_html_e( 'days', 'wp-clean-admin' ); ?></span>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">
                            <label for="wpca_cleanup_interval"><?php esc_html_e( 'Cleanup Interval', 'wp-clean-admin' ); ?></label>
                        </th>
                        <td>
                            <input type="number" min="1" max="90" id="wpca_cleanup_interval" name="wpca_cleanup_interval" value="<?php echo esc_attr( $cleanup_interval ); ?>" />
                            <span class="description"><?php esc_html_e( 'days', 'wp-clean-admin' ); ?></span>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render the resource management tab.
     */
    public function render_resources_tab() {
        $css_resources = $this->resources ? $this->resources->get_loaded_styles() : array();
        $js_resources = $this->resources ? $this->resources->get_loaded_scripts() : array();
        
        $remove_unused_css = get_option( 'wpca_remove_unused_css', false );
        $delay_non_critical_js = get_option( 'wpca_delay_non_critical_js', false );
        $enable_critical_css = get_option( 'wpca_enable_critical_css', false );
        $combine_css = get_option( 'wpca_combine_css', false );
        $combine_js = get_option( 'wpca_combine_js', false );
        
        $test_resource_nonce = wp_create_nonce( 'wpca-test-resource-removal' );
        $generate_css_nonce = wp_create_nonce( 'wpca-generate-critical-css' );
        
        ?>
        <div class="wpca-performance-section">
            <h3><?php esc_html_e( 'Resource Manager', 'wp-clean-admin' ); ?></h3>
            <p><?php esc_html_e( 'Manage CSS and JavaScript resources loaded on your admin pages to improve performance and reduce page load time.', 'wp-clean-admin' ); ?></p>
            
            <div class="wpca-resource-tabs">
                <ul>
                    <li><a href="#wpca-css-resources" class="active">CSS (<?php echo esc_html( count( $css_resources ) ); ?>)</a></li>
                    <li><a href="#wpca-js-resources">JavaScript (<?php echo esc_html( count( $js_resources ) ); ?>)</a></li>
                </ul>
            </div>
            
            <div id="wpca-css-resources" class="wpca-resource-content active">
                <input type="text" id="wpca-resource-filter" class="wpca-resource-filter" placeholder="<?php esc_attr_e( 'Filter CSS resources...', 'wp-clean-admin' ); ?>" />
                
                <table class="wpca-resource-table wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e( 'Handle', 'wp-clean-admin' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Source', 'wp-clean-admin' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Path', 'wp-clean-admin' ); ?></th>
                            <th scope="col" class="column-actions"><?php esc_html_e( 'Actions', 'wp-clean-admin' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $css_resources as $handle => $resource ) : ?>
                            <tr>
                                <td class="resource-handle"><?php echo esc_html( $handle ); ?></td>
                                <td><?php echo esc_html( $resource['source'] ); ?></td>
                                <td class="resource-path"><?php echo esc_html( $resource['src'] ); ?></td>
                                <td class="resource-actions">
                                    <button class="wpca-button wpca-button-secondary wpca-button-sm wpca-test-resource-removal" 
                                            data-nonce="<?php echo esc_attr( $test_resource_nonce ); ?>" 
                                            data-resource-type="css" 
                                            data-resource-handle="<?php echo esc_attr( $handle ); ?>">
                                        <?php esc_html_e( 'Test Removal', 'wp-clean-admin' ); ?>
                                    </button>
                                    <span class="wpca-critical-css-status wpca-critical-css-status-pending">
                                        <?php esc_html_e( 'Pending', 'wp-clean-admin' ); ?>
                                    </span>
                                    <button class="wpca-button wpca-button-primary wpca-button-sm wpca-generate-critical-css" 
                                            data-nonce="<?php echo esc_attr( $generate_css_nonce ); ?>" 
                                            data-page-hook="<?php echo esc_attr( get_current_screen()->id ); ?>">
                                        <?php esc_html_e( 'Generate Critical CSS', 'wp-clean-admin' ); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div id="wpca-js-resources" class="wpca-resource-content">
                <input type="text" id="wpca-resource-filter" class="wpca-resource-filter" placeholder="<?php esc_attr_e( 'Filter JavaScript resources...', 'wp-clean-admin' ); ?>" />
                
                <table class="wpca-resource-table wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e( 'Handle', 'wp-clean-admin' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Source', 'wp-clean-admin' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Path', 'wp-clean-admin' ); ?></th>
                            <th scope="col" class="column-actions"><?php esc_html_e( 'Actions', 'wp-clean-admin' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $js_resources as $handle => $resource ) : ?>
                            <tr>
                                <td class="resource-handle"><?php echo esc_html( $handle ); ?></td>
                                <td><?php echo esc_html( $resource['source'] ); ?></td>
                                <td class="resource-path"><?php echo esc_html( $resource['src'] ); ?></td>
                                <td class="resource-actions">
                                    <button class="wpca-button wpca-button-secondary wpca-button-sm wpca-test-resource-removal" 
                                            data-nonce="<?php echo esc_attr( $test_resource_nonce ); ?>" 
                                            data-resource-type="js" 
                                            data-resource-handle="<?php echo esc_attr( $handle ); ?>">
                                        <?php esc_html_e( 'Test Removal', 'wp-clean-admin' ); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="wpca-performance-section">
            <h3><?php esc_html_e( 'Resource Optimization Settings', 'wp-clean-admin' ); ?></h3>
            <p><?php esc_html_e( 'Configure resource optimization options to improve admin interface performance.', 'wp-clean-admin' ); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'wpca-resource-management' ); ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="wpca_remove_unused_css"><?php esc_html_e( 'Remove Unused CSS', 'wp-clean-admin' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wpca_remove_unused_css" name="wpca_remove_unused_css" value="1" <?php checked( $remove_unused_css ); ?> />
                            <label for="wpca_remove_unused_css"><?php esc_html_e( 'Automatically remove unused CSS from admin pages', 'wp-clean-admin' ); ?></label>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">
                            <label for="wpca_delay_non_critical_js"><?php esc_html_e( 'Delay Non-Critical JavaScript', 'wp-clean-admin' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wpca_delay_non_critical_js" name="wpca_delay_non_critical_js" value="1" <?php checked( $delay_non_critical_js ); ?> />
                            <label for="wpca_delay_non_critical_js"><?php esc_html_e( 'Load non-critical JavaScript asynchronously', 'wp-clean-admin' ); ?></label>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">
                            <label for="wpca_enable_critical_css"><?php esc_html_e( 'Critical CSS', 'wp-clean-admin' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wpca_enable_critical_css" name="wpca_enable_critical_css" value="1" <?php checked( $enable_critical_css ); ?> />
                            <label for="wpca_enable_critical_css"><?php esc_html_e( 'Inline critical CSS to improve rendering performance', 'wp-clean-admin' ); ?></label>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">
                            <label for="wpca_combine_css"><?php esc_html_e( 'Combine CSS Files', 'wp-clean-admin' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wpca_combine_css" name="wpca_combine_css" value="1" <?php checked( $combine_css ); ?> />
                            <label for="wpca_combine_css"><?php esc_html_e( 'Combine multiple CSS files to reduce HTTP requests', 'wp-clean-admin' ); ?></label>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">
                            <label for="wpca_combine_js"><?php esc_html_e( 'Combine JavaScript Files', 'wp-clean-admin' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wpca_combine_js" name="wpca_combine_js" value="1" <?php checked( $combine_js ); ?> />
                            <label for="wpca_combine_js"><?php esc_html_e( 'Combine multiple JavaScript files to reduce HTTP requests', 'wp-clean-admin' ); ?></label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render the performance monitoring tab.
     */
    public function render_monitoring_tab() {
        $monitoring_enabled = get_option( 'wpca_monitoring_enabled', false );
        $performance_stats = $this->performance ? $this->performance->get_performance_stats() : array();
        
        $toggle_monitoring_nonce = wp_create_nonce( 'wpca-toggle-monitoring' );
        $view_report_nonce = wp_create_nonce( 'wpca-get-performance-report' );
        $clear_data_nonce = wp_create_nonce( 'wpca-clear-performance-data' );
        
        ?>
        <div class="wpca-performance-section">
            <h3><?php esc_html_e( 'Performance Monitoring', 'wp-clean-admin' ); ?></h3>
            
            <div class="wpca-monitoring-status <?php echo $monitoring_enabled ? 'wpca-monitoring-status-active' : 'wpca-monitoring-status-inactive'; ?>">
                <h4>
                    <span class="status-indicator <?php echo $monitoring_enabled ? 'status-indicator-active' : 'status-indicator-inactive'; ?>"></span>
                    <?php echo $monitoring_enabled ? esc_html__( 'Monitoring Active', 'wp-clean-admin' ) : esc_html__( 'Monitoring Inactive', 'wp-clean-admin' ); ?>
                </h4>
                <p><?php echo $monitoring_enabled ? esc_html__( 'Performance monitoring is currently active and collecting data about your admin interface.', 'wp-clean-admin' ) : esc_html__( 'Performance monitoring is not currently active. Start monitoring to collect performance data.', 'wp-clean-admin' ); ?></p>
            </div>
            
            <button id="wpca-toggle-monitoring" class="wpca-button wpca-button-primary" data-nonce="<?php echo esc_attr( $toggle_monitoring_nonce ); ?>">
                <?php echo $monitoring_enabled ? esc_html__( 'Stop Monitoring', 'wp-clean-admin' ) : esc_html__( 'Start Monitoring', 'wp-clean-admin' ); ?>
            </button>
        </div>

        <div class="wpca-performance-section">
            <h3><?php esc_html_e( 'Performance Statistics', 'wp-clean-admin' ); ?></h3>
            
            <div class="wpca-monitoring-stats">
                <div class="wpca-monitoring-stat">
                    <span class="stat-value"><?php echo esc_html( number_format( $performance_stats['avg_page_load_time'] ?? 0, 2 ) ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Avg. Load Time (s)', 'wp-clean-admin' ); ?></span>
                </div>
                <div class="wpca-monitoring-stat">
                    <span class="stat-value"><?php echo esc_html( $performance_stats['slowest_page'] ?? 'N/A' ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Slowest Page', 'wp-clean-admin' ); ?></span>
                </div>
                <div class="wpca-monitoring-stat">
                    <span class="stat-value"><?php echo esc_html( number_format( $performance_stats['avg_query_count'] ?? 0 ) ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Avg. Queries Per Page', 'wp-clean-admin' ); ?></span>
                </div>
                <div class="wpca-monitoring-stat">
                    <span class="stat-value"><?php echo esc_html( size_format( $performance_stats['avg_memory_usage'] ?? 0 ) ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Avg. Memory Usage', 'wp-clean-admin' ); ?></span>
                </div>
            </div>
            
            <div class="wpca-performance-chart">
                <!-- Placeholder for performance chart visualization -->
                <p><?php esc_html_e( 'Performance chart visualization will appear here once data is collected.', 'wp-clean-admin' ); ?></p>
            </div>
        </div>

        <div class="wpca-performance-section">
            <h3><?php esc_html_e( 'Performance Reports', 'wp-clean-admin' ); ?></h3>
            <p><?php esc_html_e( 'View detailed performance reports based on collected monitoring data to identify potential issues and optimization opportunities.', 'wp-clean-admin' ); ?></p>
            
            <div id="wpca-performance-report-container">
                <!-- Performance report will be loaded here via AJAX -->
                <button id="wpca-view-performance-report" class="wpca-button wpca-button-primary" data-nonce="<?php echo esc_attr( $view_report_nonce ); ?>">
                    <?php esc_html_e( 'View Performance Report', 'wp-clean-admin' ); ?>
                </button>
                
                <button id="wpca-clear-performance-data" class="wpca-button wpca-button-danger" data-nonce="<?php echo esc_attr( $clear_data_nonce ); ?>">
                    <?php esc_html_e( 'Clear Performance Data', 'wp-clean-admin' ); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render the settings tab.
     */
    public function render_settings_tab() {
        $monitoring_enabled = get_option( 'wpca_monitoring_enabled', false );
        $sample_rate = get_option( 'wpca_monitoring_sample_rate', 10 );
        $data_retention = get_option( 'wpca_monitoring_data_retention', 7 );
        
        ?>
        <div class="wpca-performance-section">
            <h3><?php esc_html_e( 'Performance Monitoring Settings', 'wp-clean-admin' ); ?></h3>
            <p><?php esc_html_e( 'Configure advanced settings for performance monitoring.', 'wp-clean-admin' ); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'wpca-performance-monitoring' ); ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="wpca_monitoring_enabled"><?php esc_html_e( 'Enable Performance Monitoring', 'wp-clean-admin' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wpca_monitoring_enabled" name="wpca_monitoring_enabled" value="1" <?php checked( $monitoring_enabled ); ?> />
                            <label for="wpca_monitoring_enabled"><?php esc_html_e( 'Collect performance data about admin interface usage', 'wp-clean-admin' ); ?></label>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">
                            <label for="wpca_monitoring_sample_rate"><?php esc_html_e( 'Sample Rate', 'wp-clean-admin' ); ?></label>
                        </th>
                        <td>
                            <input type="number" min="1" max="100" id="wpca_monitoring_sample_rate" name="wpca_monitoring_sample_rate" value="<?php echo esc_attr( $sample_rate ); ?>" />
                            <span class="description"><?php esc_html_e( 'Percentage of page loads to monitor (1-100)', 'wp-clean-admin' ); ?></span>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">
                            <label for="wpca_monitoring_data_retention"><?php esc_html_e( 'Data Retention Period', 'wp-clean-admin' ); ?></label>
                        </th>
                        <td>
                            <input type="number" min="1" max="90" id="wpca_monitoring_data_retention" name="wpca_monitoring_data_retention" value="<?php echo esc_attr( $data_retention ); ?>" />
                            <span class="description"><?php esc_html_e( 'Days to keep performance monitoring data', 'wp-clean-admin' ); ?></span>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <div class="wpca-performance-section">
            <h3><?php esc_html_e( 'Performance Tips', 'wp-clean-admin' ); ?></h3>
            <p><?php esc_html_e( 'Best practices for maintaining optimal WordPress admin performance.', 'wp-clean-admin' ); ?></p>
            
            <ul>
                <li><?php esc_html_e( 'Regularly optimize your database tables to reduce overhead.', 'wp-clean-admin' ); ?></li>
                <li><?php esc_html_e( 'Clean up unused data like post revisions, auto-drafts, and spam comments.', 'wp-clean-admin' ); ?></li>
                <li><?php esc_html_e( 'Remove unnecessary plugins that are not actively being used.', 'wp-clean-admin' ); ?></li>
                <li><?php esc_html_e( 'Keep WordPress, themes, and plugins updated to benefit from performance improvements.', 'wp-clean-admin' ); ?></li>
                <li><?php esc_html_e( 'Use a caching plugin for your frontend to reduce server load.', 'wp-clean-admin' ); ?></li>
                <li><?php esc_html_e( 'Consider using a hosting provider with good performance characteristics.', 'wp-clean-admin' ); ?></li>
            </ul>
        </div>
        <?php
    }

    /**
     * AJAX handler to get table information.
     */
    public function ajax_get_table_info() {
        check_ajax_referer( 'wpca-get-table-info', 'security' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions.', 'wp-clean-admin' ) ) );
        }
        
        $table_name = isset( $_POST['table'] ) ? sanitize_text_field( $_POST['table'] ) : '';
        
        if ( empty( $table_name ) || ! $this->database ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid table name.', 'wp-clean-admin' ) ) );
        }
        
        $table_info = $this->database->get_table_info( $table_name );
        
        if ( ! $table_info ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Failed to get table information.', 'wp-clean-admin' ) ) );
        }
        
        wp_send_json_success( array( 'table_info' => $table_info ) );
    }

    /**
     * Get the singleton instance of the class.
     *
     * @return WPCA_Performance_Settings The instance of the class.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
}
?>