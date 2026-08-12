<?php
/**
 * WPCleanAdmin Dashboard Class
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

require_once __DIR__ . '/class-wpca-dashboard-data.php';

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Dashboard class
 */
class Dashboard {

    /**
     * Singleton instance
     *
     * @var Dashboard
     */
    private static $instance = null;

    /**
     * 仪表盘数据获取器
     *
     * @var Dashboard_Data
     */
    private $data;

    /**
     * Get singleton instance
     *
     * @return Dashboard
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->data = new Dashboard_Data();
        $this->init();
    }

    /**
     * Initialize the dashboard module
     */
    public function init() {
        // Register dashboard widgets
        if ( function_exists( '\add_action' ) ) {
            \add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );

            // Enqueue dashboard scripts and styles
            \add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_scripts' ) );
        }
    }

    /**
     * Register dashboard widgets
     */
    public function register_dashboard_widgets() {
        // Add WPCA dashboard widget
        if ( function_exists( '\wp_add_dashboard_widget' ) ) {
            \wp_add_dashboard_widget(
                'wpca_dashboard_widget',
                \__( 'WP Clean Admin', WPCA_TEXT_DOMAIN ),
                array( $this, 'render_dashboard_widget' )
            );
        }
    }

    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        $stats = $this->data->get_dashboard_stats();

        ?>
        <div class="wpca-dashboard-widget">
            <h3><?php \_e( 'Dashboard Overview', WPCA_TEXT_DOMAIN ); ?></h3>
            <div class="wpca-dashboard-stats">
                <div class="wpca-stat-item">
                    <span class="wpca-stat-label"><?php \_e( 'Database Size', WPCA_TEXT_DOMAIN ); ?></span>
                    <span class="wpca-stat-value"><?php echo esc_html( $stats['database_size'] ); ?></span>
                </div>
                <div class="wpca-stat-item">
                    <span class="wpca-stat-label"><?php \_e( 'Transients', WPCA_TEXT_DOMAIN ); ?></span>
                    <span class="wpca-stat-value"><?php echo esc_html( $stats['transients'] ); ?></span>
                </div>
                <div class="wpca-stat-item">
                    <span class="wpca-stat-label"><?php \_e( 'Orphaned Postmeta', WPCA_TEXT_DOMAIN ); ?></span>
                    <span class="wpca-stat-value"><?php echo esc_html( $stats['orphaned_postmeta'] ); ?></span>
                </div>
                <div class="wpca-stat-item">
                    <span class="wpca-stat-label"><?php \_e( 'Orphaned Termmeta', WPCA_TEXT_DOMAIN ); ?></span>
                    <span class="wpca-stat-value"><?php echo esc_html( $stats['orphaned_termmeta'] ); ?></span>
                </div>
            </div>
            <div class="wpca-dashboard-actions">
                <button class="button button-primary wpca-quick-action" data-action="cleanup_database">
                    <?php \_e( 'Quick Database Cleanup', WPCA_TEXT_DOMAIN ); ?>
                </button>
                <button class="button button-secondary wpca-quick-action" data-action="optimize_database">
                    <?php \_e( 'Optimize Database', WPCA_TEXT_DOMAIN ); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue dashboard scripts and styles
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_dashboard_scripts( string $hook ): void {
        // Only enqueue on dashboard page
        if ( $hook !== 'index.php' ) {
            return;
        }

        // Enqueue dashboard JS
        if ( function_exists( '\wp_enqueue_script' ) ) {
            \wp_enqueue_script(
                'wpca-dashboard',
                WPCA_PLUGIN_URL . 'assets/js/wpca-dashboard.js',
                array( 'jquery' ),
                WPCA_VERSION,
                true
            );
        }

        // Localize script
        if ( function_exists( '\wp_localize_script' ) && function_exists( '\wp_create_nonce' ) && function_exists( '\admin_url' ) ) {
            \wp_localize_script(
                'wpca-dashboard',
                'wpca_dashboard_vars',
                array(
                    'ajax_url' => \admin_url( 'admin-ajax.php' ),
                    'nonce'    => \wp_create_nonce( 'wpca_ajax_nonce' ),
                )
            );
        }
    }

    /**
     * Get dashboard statistics (delegated to data handler)
     *
     * @return array
     */
    public function get_dashboard_stats(): array {
        return $this->data->get_dashboard_stats();
    }

    /**
     * Get system information (delegated to data handler)
     *
     * @return array
     */
    public function get_system_info(): array {
        return $this->data->get_system_info();
    }

    /**
     * Run quick action
     *
     * @param string $action Action name
     * @return array Action result
     */
    public function run_quick_action( $action ) {
        $result = array(
            'success' => false,
            'message' => \__( 'Invalid action', WPCA_TEXT_DOMAIN )
        );

        switch ( $action ) {
            case 'cleanup_database':
                // Run database cleanup
                $cleanup        = new Cleanup();
                $cleanup_result = $cleanup->run_database_cleanup(
                    array(
                        'transients'        => true,
                        'orphaned_postmeta' => true,
                        'orphaned_termmeta' => true,
                    )
                );

                $result['success'] = true;
                $result['message'] = \__( 'Database cleanup completed successfully', WPCA_TEXT_DOMAIN );
                $result['data']    = $cleanup_result;
                break;

            case 'optimize_database':
                // Run database optimization
                $database        = new Database();
                $optimize_result = $database->optimize_database();

                $result['success'] = true;
                $result['message'] = \__( 'Database optimization completed successfully', WPCA_TEXT_DOMAIN );
                $result['data']    = $optimize_result;
                break;
        }

        return $result;
    }
}
