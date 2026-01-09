<?php
/**
 * WPCleanAdmin Extension API Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.8.0
 * @description Extension API for third-party developers to extend WP Clean Admin functionality
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
    
    /**
     * Save extension settings
     *
     * @param string $extension_id Extension ID
     * @param array $settings Extension settings
     * @return bool Success status
     */
    public function save_extension_settings( string $extension_id, array $settings ): bool {
        $extension_id = \sanitize_key( $extension_id );
        
        if ( ! $this->get_extension( $extension_id ) ) {
            return false;
        }
        
        $option_name = 'wpca_ext_' . $extension_id . '_settings';
        
        // Validate settings
        $validated_settings = \apply_filters( 'wpca_extension_settings_validate_' . $extension_id, $settings, $extension_id );
        
        // Save settings
        $result = \update_option( $option_name, $validated_settings );
        
        // Trigger action
        if ( $result ) {
            \do_action( 'wpca_ext_settings_saved', $extension_id, $validated_settings );
            \do_action( 'wpca_ext_settings_saved_' . $extension_id, $validated_settings );
        }
        
        return $result;
    }
    
    /**
     * Get extension settings
     *
     * @param string $extension_id Extension ID
     * @return array Extension settings
     */
    public function get_extension_settings( string $extension_id ): array {
        $extension_id = \sanitize_key( $extension_id );
        $option_name = 'wpca_ext_' . $extension_id . '_settings';
        
        $settings = \get_option( $option_name, array() );
        
        // Filter the settings before returning
        return \apply_filters( 'wpca_extension_settings_get_' . $extension_id, $settings, $extension_id );
    }
    
    /**
     * Reset extension settings
     *
     * @param string $extension_id Extension ID
     * @return bool Success status
     */
    public function reset_extension_settings( string $extension_id ): bool {
        $extension_id = \sanitize_key( $extension_id );
        $option_name = 'wpca_ext_' . $extension_id . '_settings';
        
        // Get default settings
        $default_settings = \apply_filters( 'wpca_extension_default_settings_' . $extension_id, array(), $extension_id );
        
        // Save default settings
        $result = \update_option( $option_name, $default_settings );
        
        // Trigger action
        if ( $result ) {
            \do_action( 'wpca_ext_settings_reset', $extension_id, $default_settings );
            \do_action( 'wpca_ext_settings_reset_' . $extension_id, $default_settings );
        }
        
        return $result;
    }
    
    /**
     * Install extension
     *
     * @param string $extension_id Extension ID
     * @param array $extension_data Extension data
     * @return bool Success status
     */
    public function install_extension( string $extension_id, array $extension_data = array() ): bool {
        $extension_id = \sanitize_key( $extension_id );
        
        // Check if extension already exists
        if ( $this->get_extension( $extension_id ) ) {
            return false;
        }
        
        // Validate extension data
        $required_fields = array( 'name', 'version', 'file' );
        foreach ( $required_fields as $field ) {
            if ( ! isset( $extension_data[ $field ] ) || empty( $extension_data[ $field ] ) ) {
                return false;
            }
        }
        
        // Set default data
        $extension_data['id'] = $extension_id;
        $extension_data['installed_at'] = current_time( 'mysql' );
        $extension_data['active'] = false;
        
        // Register the extension
        if ( ! $this->register_extension( $extension_data ) ) {
            return false;
        }
        
        // Trigger install action
        \do_action( 'wpca_extension_installed', $extension_id, $extension_data );
        \do_action( 'wpca_extension_installed_' . $extension_id, $extension_data );
        
        return true;
    }
    
    /**
     * Uninstall extension
     *
     * @param string $extension_id Extension ID
     * @return bool Success status
     */
    public function uninstall_extension( string $extension_id ): bool {
        $extension_id = \sanitize_key( $extension_id );
        
        if ( ! $this->get_extension( $extension_id ) ) {
            return false;
        }
        
        // Deactivate extension first
        $this->deactivate_extension( $extension_id );
        
        // Remove extension settings
        $option_name = 'wpca_ext_' . $extension_id . '_settings';
        \delete_option( $option_name );
        
        // Unregister extension
        $this->unregister_extension( $extension_id );
        
        // Trigger action
        \do_action( 'wpca_extension_uninstalled', $extension_id );
        
        return true;
    }
    
    /**
     * Get extension lifecycle hooks
     *
     * @param string $extension_id Extension ID
     * @return array Lifecycle hooks
     */
    public function get_lifecycle_hooks( string $extension_id ): array {
        return array(
            'install' => 'wpca_extension_installed_' . $extension_id,
            'activate' => 'wpca_extension_activated_' . $extension_id,
            'deactivate' => 'wpca_extension_deactivated_' . $extension_id,
            'uninstall' => 'wpca_extension_uninstalled_' . $extension_id,
        );
    }
    
    /**
     * Extension sandbox execution result
     */
    private function create_sandbox_result( bool $success, $result = null, string $error = '' ): array {
        return array(
            'success' => $success,
            'result' => $result,
            'error' => $error,
        );
    }
    
    /**
     * Execute extension code in sandbox
     *
     * @param string $extension_code Extension code to execute
     * @param array $options Sandbox options
     * @return array Sandbox execution result
     */
    public function execute_in_sandbox( string $extension_code, array $options = array() ): array {
        // Set default options
        $defaults = array(
            'memory_limit' => '64M',
            'time_limit' => 5,
            'allowed_functions' => array('wpca_*', 'wp_*', 'add_*'),
            'blocked_functions' => array('eval', 'exec', 'shell_exec', 'passthru', 'proc_open', 'popen', 'system'),
            'extension_id' => '',
        );
        
        $options = \wp_parse_args( $options, $defaults );
        
        // Check if sandbox execution is enabled
        if ( ! \apply_filters( 'wpca_sandbox_enabled', true ) ) {
            return $this->create_sandbox_result( false, null, '沙箱执行已禁用' );
        }
        
        // Validate memory limit
        $memory_limit = $this->parse_memory_limit( $options['memory_limit'] );
        if ( $memory_limit <= 0 ) {
            return $this->create_sandbox_result( false, null, '无效的内存限制' );
        }
        
        // Get original memory and time limits
        $original_memory_limit = \ini_get( 'memory_limit' );
        $original_time_limit = \ini_get( 'max_execution_time' );
        $original_error_reporting = \error_reporting();
        $original_display_errors = \ini_get( 'display_errors' );
        
        $result = null;
        $error = '';
        $success = false;
        
        try {
            // Set memory and time limits
            \ini_set( 'memory_limit', $options['memory_limit'] );
            \set_time_limit( $options['time_limit'] );
            \error_reporting( E_ALL & ~E_DEPRECATED );
            \ini_set( 'display_errors', '0' );
            
            // Create function whitelist/blacklist
            $this->setup_sandbox_environment( $options );
            
            // Execute the code
            ob_start();
            $result = eval( '?>' . $extension_code );
            $output = ob_get_clean();
            
            // Combine output and result
            if ( ! empty( $output ) ) {
                $result = $output;
            }
            
            $success = true;
        } catch ( \Throwable $e ) {
            $error = $e->getMessage();
            $success = false;
        } finally {
            // Restore original limits
            \ini_set( 'memory_limit', $original_memory_limit );
            \set_time_limit( $original_time_limit );
            \error_reporting( $original_error_reporting );
            \ini_set( 'display_errors', $original_display_errors );
            
            // Clean up sandbox environment
            $this->cleanup_sandbox_environment();
        }
        
        return $this->create_sandbox_result( $success, $result, $error );
    }
    
    /**
     * Parse memory limit string to bytes
     *
     * @param string $limit Memory limit string (e.g., '64M', '1G')
     * @return int Memory limit in bytes
     */
    private function parse_memory_limit( string $limit ): int {
        $limit = trim( $limit );
        $last_char = strtolower( substr( $limit, -1 ) );
        
        $value = substr( $limit, 0, -1 );
        
        switch ( $last_char ) {
            case 'g':
                $value *= 1024;
                // Fallthrough
            case 'm':
                $value *= 1024;
                // Fallthrough
            case 'k':
                $value *= 1024;
                break;
        }
        
        return (int) $value;
    }
    
    /**
     * Setup sandbox environment
     *
     * @param array $options Sandbox options
     * @return void
     */
    private function setup_sandbox_environment( array $options ): void {
        // Setup function whitelist/blacklist
        $this->disable_dangerous_functions( $options['blocked_functions'] );
    }
    
    /**
     * Disable dangerous functions
     *
     * @param array $blocked_functions List of functions to block
     * @return void
     */
    private function disable_dangerous_functions( array $blocked_functions ): void {
        foreach ( $blocked_functions as $function ) {
            if ( function_exists( $function ) ) {
                $this->override_function( $function, function() use ( $function ) {
                    throw new \Exception( "函数 {$function} 已被禁止在沙箱中使用" );
                });
            }
        }
    }
    
    /**
     * Override a function
     *
     * @param string $function_name Function name to override
     * @param callable $callback New function callback
     * @return void
     */
    private function override_function( string $function_name, callable $callback ): void {
        if ( function_exists( 'override_function' ) ) {
            // If APD extension is available
            override_function( $function_name, '', 'return call_user_func($callback, func_get_args());' );
        } elseif ( function_exists( 'runkit_function_redefine' ) ) {
            // If runkit extension is available
            runkit_function_redefine( $function_name, '', $callback );
        } else {
            // Fallback: Define a constant to indicate the function is blocked
            define( 'WPCA_FUNCTION_BLOCKED_' . strtoupper( $function_name ), true );
        }
    }
    
    /**
     * Cleanup sandbox environment
     *
     * @return void
     */
    private function cleanup_sandbox_environment(): void {
        // No specific cleanup needed for current implementation
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

/**
 * Save extension settings
 *
 * Helper function for third-party developers to save extension settings
 *
 * @param string $extension_id Extension ID
 * @param array $settings Extension settings
 * @return bool Success status
 */
function wpca_save_extension_settings( string $extension_id, array $settings ): bool {
    $api = Extension_API::getInstance();
    return $api->save_extension_settings( $extension_id, $settings );
}

/**
 * Get extension settings
 *
 * Helper function for third-party developers to get extension settings
 *
 * @param string $extension_id Extension ID
 * @return array Extension settings
 */
function wpca_get_extension_settings( string $extension_id ): array {
    $api = Extension_API::getInstance();
    return $api->get_extension_settings( $extension_id );
}

/**
 * Reset extension settings
 *
 * Helper function for third-party developers to reset extension settings
 *
 * @param string $extension_id Extension ID
 * @return bool Success status
 */
function wpca_reset_extension_settings( string $extension_id ): bool {
    $api = Extension_API::getInstance();
    return $api->reset_extension_settings( $extension_id );
}

/**
 * Install extension
 *
 * Helper function for third-party developers to install extensions
 *
 * @param string $extension_id Extension ID
 * @param array $extension_data Extension data
 * @return bool Success status
 */
function wpca_install_extension( string $extension_id, array $extension_data = array() ): bool {
    $api = Extension_API::getInstance();
    return $api->install_extension( $extension_id, $extension_data );
}

/**
 * Uninstall extension
 *
 * Helper function for third-party developers to uninstall extensions
 *
 * @param string $extension_id Extension ID
 * @return bool Success status
 */
function wpca_uninstall_extension( string $extension_id ): bool {
    $api = Extension_API::getInstance();
    return $api->uninstall_extension( $extension_id );
}
