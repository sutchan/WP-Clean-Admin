<?php
/**
 * WPCleanAdmin Extension API Class
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Extension API class for plugin extensibility
 *
 * Provides APIs for third-party developers to extend plugin functionality
 * including hooks, filters, and custom modules
 */
class Extension_API {
    
    /**
     * Singleton instance
     *
     * @var Extension_API
     */
    private static ?Extension_API $instance = null;
    
    /**
     * Registered extensions
     *
     * @var array
     */
    private $extensions = array();
    
    /**
     * Extension hooks
     *
     * @var array
     */
    private $hooks = array();
    
    /**
     * Extension filters
     *
     * @var array
     */
    private $filters = array();
    
    /**
     * Get singleton instance
     *
     * @return Extension_API
     */
    public static function getInstance(): Extension_API {
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
     * Initialize the extension API
     *
     * @uses \add_action() To register initialization action
     * @return void
     */
    private function init(): void {
        \add_action( 'wpca_init', array( $this, 'load_extensions' ) );
    }
    
    /**
     * Load all registered extensions
     *
     * @uses \apply_filters() To get loaded extensions
     * @return void
     */
    public function load_extensions(): void {
        $extensions = \apply_filters( 'wpca_register_extensions', array() );
        
        foreach ( $extensions as $extension ) {
            $this->register_extension( $extension );
        }
        
        \do_action( 'wpca_extensions_loaded' );
    }
    
    /**
     * Register a new extension
     *
     * @param array $extension Extension configuration
     * @return bool Success status
     */
    public function register_extension( array $extension ): bool {
        if ( ! is_array( $extension ) ) {
            return false;
        }
        
        $required_keys = array( 'id', 'name', 'version', 'file' );
        
        foreach ( $required_keys as $key ) {
            if ( ! isset( $extension[ $key ] ) || empty( $extension[ $key ] ) ) {
                return false;
            }
        }
        
        $extension_id = \sanitize_key( $extension['id'] );
        
        if ( isset( $this->extensions[ $extension_id ] ) ) {
            return false;
        }
        
        $extension['registered_at'] = current_time( 'mysql' );
        $extension['active'] = false;
        
        $this->extensions[ $extension_id ] = $extension;
        
        \do_action( "wpca_extension_registered_{$extension_id}", $extension );
        \do_action( 'wpca_extension_registered', $extension_id, $extension );
        
        return true;
    }
    
    /**
     * Unregister an extension
     *
     * @param string $extension_id Extension ID
     * @return bool Success status
     */
    public function unregister_extension( string $extension_id ): bool {
        $extension_id = sanitize_key( $extension_id );
        
        if ( ! isset( $this->extensions[ $extension_id ] ) ) {
            return false;
        }
        
        $extension = $this->extensions[ $extension_id ];
        
        // Deactivate if active
        if ( $extension['active'] ) {
            $this->deactivate_extension( $extension_id );
        }
        
        unset( $this->extensions[ $extension_id ] );
        
        \do_action( "wpca_extension_unregistered_{$extension_id}", $extension );
        \do_action( 'wpca_extension_unregistered', $extension_id, $extension );
        
        return true;
    }
    
    /**
     * Activate an extension
     *
     * @param string $extension_id Extension ID
     * @return bool Success status
     */
    public function activate_extension( string $extension_id ): bool {
        $extension_id = sanitize_key( $extension_id );
        
        if ( ! isset( $this->extensions[ $extension_id ] ) ) {
            return false;
        }
        
        $extension = $this->extensions[ $extension_id ];
        
        if ( $extension['active'] ) {
            return true;
        }
        
        // Include extension file
        if ( file_exists( $extension['file'] ) ) {
            include_once $extension['file'];
        }
        
        $extension['active'] = true;
        $extension['activated_at'] = current_time( 'mysql' );
        $this->extensions[ $extension_id ] = $extension;
        
        \do_action( "wpca_extension_activated_{$extension_id}", $extension );
        \do_action( 'wpca_extension_activated', $extension_id, $extension );
        
        return true;
    }
    
    /**
     * Deactivate an extension
     *
     * @param string $extension_id Extension ID
     * @return bool Success status
     */
    public function deactivate_extension( string $extension_id ): bool {
        $extension_id = sanitize_key( $extension_id );
        
        if ( ! isset( $this->extensions[ $extension_id ] ) ) {
            return false;
        }
        
        $extension = $this->extensions[ $extension_id ];
        
        if ( ! $extension['active'] ) {
            return true;
        }
        
        $extension['active'] = false;
        unset( $extension['activated_at'] );
        $this->extensions[ $extension_id ] = $extension;
        
        \do_action( "wpca_extension_deactivated_{$extension_id}", $extension );
        \do_action( 'wpca_extension_deactivated', $extension_id, $extension );
        
        return true;
    }
    
    /**
     * Get all registered extensions
     *
     * @param string $status Filter by status (all, active, inactive)
     * @return array Registered extensions
     */
    public function get_extensions( string $status = 'all' ): array {
        if ( $status === 'all' ) {
            return $this->extensions;
        }
        
        $filtered = array();
        
        foreach ( $this->extensions as $id => $extension ) {
            if ( $status === 'active' && $extension['active'] ) {
                $filtered[ $id ] = $extension;
            } elseif ( $status === 'inactive' && ! $extension['active'] ) {
                $filtered[ $id ] = $extension;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Get single extension by ID
     *
     * @param string $extension_id Extension ID
     * @return array|null Extension data or null if not found
     */
    public function get_extension( string $extension_id ): ?array {
        $extension_id = \sanitize_key( $extension_id );
        
        return isset( $this->extensions[ $extension_id ] ) ? $this->extensions[ $extension_id ] : null;
    }
    
    /**
     * Add extension hook
     *
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Hook priority
     * @param int $accepted_args Number of accepted arguments
     * @return bool Success status
     */
    public function add_hook( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): bool {
        if ( ! is_callable( $callback ) ) {
            return false;
        }
        
        if ( ! isset( $this->hooks[ $hook ] ) ) {
            $this->hooks[ $hook ] = array();
        }
        
        $this->hooks[ $hook ][] = array(
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        );
        
        return true;
    }
    
    /**
     * Add extension filter
     *
     * @param string $filter Filter name
     * @param callable $callback Callback function
     * @param int $priority Hook priority
     * @param int $accepted_args Number of accepted arguments
     * @return bool Success status
     */
    public function add_filter( string $filter, callable $callback, int $priority = 10, int $accepted_args = 1 ): bool {
        if ( ! is_callable( $callback ) ) {
            return false;
        }
        
        if ( ! isset( $this->filters[ $filter ] ) ) {
            $this->filters[ $filter ] = array();
        }
        
        $this->filters[ $filter ][] = array(
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        );
        
        return true;
    }
    
    /**
     * Execute hooks
     *
     * @param string $hook Hook name
     * @param mixed $arg Optional argument
     * @return mixed Result
     */
    public function execute_hook( string $hook, $arg = '' ) {
        if ( ! isset( $this->hooks[ $hook ] ) ) {
            return $arg;
        }
        
        $args = func_get_args();
        array_shift( $args );
        
        foreach ( $this->hooks[ $hook ] as $hook_config ) {
            $result = call_user_func_array(
                $hook_config['callback'],
                array_slice( $args, 0, $hook_config['accepted_args'] )
            );
            if ( $result !== null ) {
                $arg = $result;
            }
        }
        
        return $arg;
    }
    
    /**
     * Apply filters
     *
     * @param string $filter Filter name
     * @param mixed $value Value to filter
     * @return mixed Filtered value
     */
    public function apply_filter( string $filter, $value ) {
        if ( ! isset( $this->filters[ $filter ] ) ) {
            return $value;
        }
        
        foreach ( $this->filters[ $filter ] as $filter_config ) {
            $value = call_user_func_array(
                $filter_config['callback'],
                array( $value )
            );
        }
        
        return $value;
    }
    
    /**
     * Create custom menu item
     *
     * @param array $menu_item Menu item configuration
     * @return bool Success status
     */
    public function register_menu_item( array $menu_item ): bool {
        $required = array( 'title', 'slug', 'callback' );
        
        foreach ( $required as $field ) {
            if ( ! isset( $menu_item[ $field ] ) || empty( $menu_item[ $field ] ) ) {
                return false;
            }
        }
        
        $defaults = array(
            'parent' => 'wp-clean-admin',
            'capability' => 'manage_options',
            'icon' => 'dashicons-admin-plugins',
            'position' => null,
        );
        
        $menu_item = wp_parse_args( $menu_item, $defaults );
        
        \add_action( 'wpca_admin_menu', function() use ( $menu_item ) {
            \add_submenu_page(
                $menu_item['parent'],
                $menu_item['title'],
                $menu_item['title'],
                $menu_item['capability'],
                $menu_item['slug'],
                $menu_item['callback'],
                $menu_item['position']
            );
        });
        
        return true;
    }
    
    /**
     * Register settings section
     *
     * @param array $section Section configuration
     * @return bool Success status
     */
    public function register_settings_section( array $section ): bool {
        $required = array( 'id', 'title', 'callback' );
        
        foreach ( $required as $field ) {
            if ( ! isset( $section[ $field ] ) || empty( $section[ $field ] ) ) {
                return false;
            }
        }
        
        $defaults = array(
            'page' => 'wpca_settings',
        );
        
        $section = wp_parse_args( $section, $defaults );
        
        \add_action( 'wpca_settings_sections', function() use ( $section ) {
            \add_settings_section(
                $section['id'],
                $section['title'],
                $section['callback'],
                $section['page']
            );
        });
        
        return true;
    }
    
    /**
     * Register settings field
     *
     * @param array $field Field configuration
     * @return bool Success status
     */
    public function register_settings_field( array $field ): bool {
        $required = array( 'id', 'title', 'callback', 'section' );
        
        foreach ( $required as $key ) {
            if ( ! isset( $field[ $key ] ) || empty( $field[ $key ] ) ) {
                return false;
            }
        }
        
        $defaults = array(
            'page' => 'wpca_settings',
            'args' => array(),
        );
        
        $field = wp_parse_args( $field, $defaults );
        
        \add_action( 'wpca_settings_fields', function() use ( $field ) {
            \add_settings_field(
                $field['id'],
                $field['title'],
                $field['callback'],
                $field['page'],
                $field['section'],
                $field['args']
            );
        });
        
        return true;
    }
    
    /**
     * Get extension API version
     *
     * @return string API version
     */
    public function get_api_version(): string {
        return '1.0.0';
    }
    
    /**
     * Get extension info
     *
     * @param string $extension_id Extension ID
     * @return array Extension information
     */
    public function get_extension_info( string $extension_id ): array {
        $extension = $this->get_extension( $extension_id );
        
        if ( ! $extension ) {
            return array();
        }
        
        return array(
            'id' => $extension['id'],
            'name' => $extension['name'],
            'version' => $extension['version'],
            'active' => $extension['active'],
            'author' => isset( $extension['author'] ) ? $extension['author'] : '',
            'description' => isset( $extension['description'] ) ? $extension['description'] : '',
            'file' => $extension['file'],
        );
    }
    
    /**
     * Check if extension is active
     *
     * @param string $extension_id Extension ID
     * @return bool True if active
     */
    public function is_extension_active( string $extension_id ): bool {
        $extension = $this->get_extension( $extension_id );
        
        return $extension && $extension['active'];
    }
    
    /**
     * Get extension count
     *
     * @param string $status Filter by status
     * @return int Extension count
     */
    public function get_extension_count( string $status = 'all' ): int {
        $extensions = $this->get_extensions( $status );
        return count( $extensions );
    }
    
    /**
     * Export extensions configuration
     *
     * @return array Exported data
     */
    public function export_extensions(): array {
        return array(
            'exported_at' => \current_time( 'mysql' ),
            'api_version' => $this->get_api_version(),
            'extensions' => $this->get_extensions(),
            'total_count' => $this->get_extension_count(),
            'active_count' => $this->get_extension_count( 'active' ),
        );
    }
}

/**
 * Register extension with WP Clean Admin
 *
 * Helper function for third-party developers to register extensions
 *
 * @param array $extension Extension configuration
 * @return bool Success status
 */
function wpca_register_extension( array $extension ): bool {
    $api = Extension_API::getInstance();
    return $api->register_extension( $extension );
}

/**
 * Get WP Clean Admin extension API instance
 *
 * @return Extension_API
 */
function wpca_get_extension_api(): Extension_API {
    return Extension_API::getInstance();
}
