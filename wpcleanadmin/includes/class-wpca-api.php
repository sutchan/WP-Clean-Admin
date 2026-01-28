<?php
/**
 * WPCleanAdmin API Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Declare WordPress functions for IDE compatibility
if ( ! function_exists( 'add_action' ) ) {
    function add_action() {}
}
if ( ! function_exists( 'register_rest_route' ) ) {
    function register_rest_route() {}
}
if ( ! function_exists( 'rest_ensure_response' ) ) {
    function rest_ensure_response() {}
}
if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can() {}
}
if ( ! function_exists( 'get_option' ) ) {
    function get_option() {}
}
if ( ! function_exists( 'update_option' ) ) {
    function update_option() {}
}

/**
 * API class
 */
class API {
    
    /**
     * Singleton instance
     *
     * @var API
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     *
     * @return API
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
        $this->init();
    }
    
    /**
     * Initialize the API module
     */
    public function init(): void {
        // Register REST API routes
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'rest_api_init', array( $this, 'register_routes' ) );
        }
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes(): void {
        // Register API routes
        if ( function_exists( 'register_rest_route' ) ) {
            // Settings route
            \register_rest_route( 'wpca/v1', '/settings', array(
                'methods' => 'GET',
                'callback' => array( $this, 'get_settings' ),
                'permission_callback' => array( $this, 'check_permissions' ),
            ));
            
            // Update settings route
            \register_rest_route( 'wpca/v1', '/settings', array(
                'methods' => 'POST',
                'callback' => array( $this, 'update_settings' ),
                'permission_callback' => array( $this, 'check_permissions' ),
            ));
            
            // Cleanup route
            \register_rest_route( 'wpca/v1', '/cleanup', array(
                'methods' => 'POST',
                'callback' => array( $this, 'run_cleanup' ),
                'permission_callback' => array( $this, 'check_permissions' ),
            ));
            
            // Database route
            \register_rest_route( 'wpca/v1', '/database', array(
                'methods' => 'POST',
                'callback' => array( $this, 'run_database_operation' ),
                'permission_callback' => array( $this, 'check_permissions' ),
            ));
            
            // Performance route
            \register_rest_route( 'wpca/v1', '/performance', array(
                'methods' => 'GET',
                'callback' => array( $this, 'get_performance_stats' ),
                'permission_callback' => array( $this, 'check_permissions' ),
            ));
        }
    }
    
    /**
     * Check API permissions
     *
     * @return bool Permission result
     */
    public function check_permissions(): bool {
        return function_exists( 'current_user_can' ) && \current_user_can( 'manage_options' );
    }
    
    /**
     * Get settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_settings( $request ) {
        $settings = wpca_get_settings();
        
        if ( function_exists( 'rest_ensure_response' ) ) {
            return \rest_ensure_response( array(
                'success' => true,
                'data' => $settings,
            ));
        }
    }
    
    /**
     * Update settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function update_settings( $request ) {
        $settings = $request->get_json_params();
        
        if ( function_exists( 'update_option' ) ) {
            \update_option( 'wpca_settings', $settings );
        }
        
        if ( function_exists( 'rest_ensure_response' ) ) {
            return \rest_ensure_response( array(
                'success' => true,
                'message' => \__( 'Settings updated successfully', WPCA_TEXT_DOMAIN ),
            ));
        }
    }
    
    /**
     * Run cleanup
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function run_cleanup( $request ) {
        $params = $request->get_json_params();
        $type = isset( $params['type'] ) ? $params['type'] : 'all';
        $options = isset( $params['options'] ) ? $params['options'] : array();
        
        $results = array();
        
        // Run cleanup based on type
        switch ( $type ) {
            case 'database':
                $cleanup = Cleanup::getInstance();
                $results = $cleanup->run_database_cleanup( $options );
                break;
            case 'media':
                $cleanup = Cleanup::getInstance();
                $results = $cleanup->run_media_cleanup( $options );
                break;
            case 'comments':
                $cleanup = Cleanup::getInstance();
                $results = $cleanup->run_comments_cleanup( $options );
                break;
            case 'content':
                $cleanup = Cleanup::getInstance();
                $results = $cleanup->run_content_cleanup( $options );
                break;
            case 'all':
                $cleanup = Cleanup::getInstance();
                $results['database'] = $cleanup->run_database_cleanup( $options );
                $results['media'] = $cleanup->run_media_cleanup( $options );
                $results['comments'] = $cleanup->run_comments_cleanup( $options );
                $results['content'] = $cleanup->run_content_cleanup( $options );
                break;
        }
        
        if ( function_exists( 'rest_ensure_response' ) ) {
            return \rest_ensure_response( array(
                'success' => true,
                'data' => $results,
            ));
        }
    }
    
    /**
     * Run database operation
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function run_database_operation( $request ) {
        $params = $request->get_json_params();
        $operation = isset( $params['operation'] ) ? $params['operation'] : 'optimize';
        $options = isset( $params['options'] ) ? $params['options'] : array();
        
        $results = array();
        
        // Run database operation based on type
        switch ( $operation ) {
            case 'optimize':
                $database = Database::getInstance();
                $results = $database->optimize_database();
                break;
            case 'backup':
                $database = Database::getInstance();
                $results = $database->backup_database( $options );
                break;
            case 'restore':
                $database = Database::getInstance();
                $backup_file = isset( $options['backup_file'] ) ? $options['backup_file'] : '';
                $results = $database->restore_database( $backup_file );
                break;
        }
        
        if ( function_exists( 'rest_ensure_response' ) ) {
            return \rest_ensure_response( array(
                'success' => true,
                'data' => $results,
            ));
        }
    }
    
    /**
     * Get performance statistics
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_performance_stats( $request ) {
        $performance = Performance::getInstance();
        $stats = $performance->get_performance_stats();
        
        if ( function_exists( 'rest_ensure_response' ) ) {
            return \rest_ensure_response( array(
                'success' => true,
                'data' => $stats,
            ));
        }
    }
    
    /**
     * Get cleanup statistics
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_cleanup_stats( $request ) {
        $cleanup = Cleanup::getInstance();
        $stats = $cleanup->get_cleanup_stats();
        
        if ( function_exists( 'rest_ensure_response' ) ) {
            return \rest_ensure_response( array(
                'success' => true,
                'data' => $stats,
            ));
        }
    }
    
    /**
     * Get database information
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_database_info( $request ) {
        $database = Database::getInstance();
        $info = $database->get_database_info();
        
        if ( function_exists( 'rest_ensure_response' ) ) {
            return \rest_ensure_response( array(
                'success' => true,
                'data' => $info,
            ));
        }
    }
}
