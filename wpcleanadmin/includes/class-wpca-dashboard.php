<?php
namespace WPCleanAdmin;

/**
 * Dashboard class for WP Clean Admin plugin
 *
 * @package WPCleanAdmin
 */

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
    private static $instance;
    
    /**
     * Get singleton instance
     *
     * @return Dashboard
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
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
        $stats = $this->get_dashboard_stats();
        
        ?>        <div class="wpca-dashboard-widget">
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
    public function enqueue_dashboard_scripts( $hook ) {
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
            \wp_localize_script( 'wpca-dashboard', 'wpca_dashboard_vars', array(
                'ajax_url' => \admin_url( 'admin-ajax.php' ),
                'nonce' => \wp_create_nonce( 'wpca_dashboard_nonce' )
            ));
        }
    }
    
    /**
     * Get dashboard statistics
     *
     * @return array Dashboard statistics
     */
    public function get_dashboard_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Get database size
        $result = $wpdb->get_row( $wpdb->prepare( "SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = %s", $wpdb->dbname ), ARRAY_A );
        $stats['database_size'] = ( function_exists( '\size_format' ) ? \size_format( $result['size'], 2 ) : $result['size'] );
        
        // Get transients count
        $stats['transients'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%transient%'" );
        
        // Get orphaned postmeta count
        $stats['orphaned_postmeta'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} LEFT JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.ID IS NULL" );
        
        // Get orphaned termmeta count
        $stats['orphaned_termmeta'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->termmeta} LEFT JOIN {$wpdb->terms} ON {$wpdb->termmeta}.term_id = {$wpdb->terms}.term_id WHERE {$wpdb->terms}.term_id IS NULL" );
        
        return $stats;
    }
    
    /**
     * Get system information
     *
     * @return array System information
     */
    public function get_system_info() {
        global $wp_version, $wpdb;
        
        $info = array();
        
        // WordPress information
        $info['wordpress'] = array(
            'version' => $wp_version,
            'language' => ( function_exists( '\get_locale' ) ? \get_locale() : 'en_US' ),
            'multisite' => ( function_exists( '\is_multisite' ) && \is_multisite() ) ? \__( 'Yes', WPCA_TEXT_DOMAIN ) : \__( 'No', WPCA_TEXT_DOMAIN ),
            'debug_mode' => defined( 'WP_DEBUG' ) && WP_DEBUG ? \__( 'Yes', WPCA_TEXT_DOMAIN ) : \__( 'No', WPCA_TEXT_DOMAIN )
        );
        
        // Server information
        $info['server'] = array(
            'php_version' => phpversion(),
            'mysql_version' => $wpdb->db_version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'],
            'memory_limit' => ini_get( 'memory_limit' )
        );
        
        // Plugin information
        $info['plugin'] = array(
            'version' => WPCA_VERSION,
            'active' => ( function_exists( '\is_plugin_active' ) && function_exists( '\plugin_basename' ) && \is_plugin_active( \plugin_basename( WPCA_PLUGIN_DIR . 'wp-clean-admin.php' ) ) ) ? \__( 'Yes', WPCA_TEXT_DOMAIN ) : \__( 'Yes', WPCA_TEXT_DOMAIN )
        );
        
        return $info;
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
                $cleanup = new Cleanup();
                $cleanup_result = $cleanup->run_database_cleanup( array(
                    'transients' => true,
                    'orphaned_postmeta' => true,
                    'orphaned_termmeta' => true
                ) );
                
                $result['success'] = true;
                $result['message'] = \__( 'Database cleanup completed successfully', WPCA_TEXT_DOMAIN );
                $result['data'] = $cleanup_result;
                break;
                
            case 'optimize_database':
                // Run database optimization
                $database = new Database();
                $optimize_result = $database->optimize_database();
                
                $result['success'] = true;
                $result['message'] = \__( 'Database optimization completed successfully', WPCA_TEXT_DOMAIN );
                $result['data'] = $optimize_result;
                break;
        }
        
        return $result;
    }
}