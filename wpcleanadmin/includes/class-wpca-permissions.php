<?php
/**
 * WP Clean Admin - Permission Manager
 *
 * Implements fine-grained user permission management, supporting permission registration, checking, and inheritance
 *
 * @package WPCleanAdmin
 * @subpackage Permissions
 * @since 1.1.0
 */

// If accessed directly, abort
if (!defined('ABSPATH')) {
    exit;
}

// No need to include utility class as we're using native WordPress functions directly

/**
 * Permission management class
 */
class WPCA_Permissions {

    /**
     * Plugin permission list
     *
     * @var array
     */
    const CAP_VIEW_SETTINGS = 'wpca_view_settings';
    const CAP_MANAGE_MENUS = 'wpca_manage_menus';
    const CAP_MANAGE_ALL = 'wpca_manage_all';

    private $capabilities = array(
        self::CAP_VIEW_SETTINGS  => 'View Settings',
        self::CAP_MANAGE_MENUS   => 'Manage Menus',
        self::CAP_MANAGE_ALL     => 'Full Control'
    );

    /**
     * Permission inheritance relationships
     *
     * @var array
     */
    private $capability_hierarchy = array(
        'wpca_manage_all'   => array('wpca_manage_menus', 'wpca_view_settings'),
        'wpca_manage_menus' => array('wpca_view_settings')
    );

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'register_capabilities'));
        add_action('wp_ajax_wpca_check_permission', array($this, 'ajax_check_permission'));
        add_filter('wpca_localize_script_data', array($this, 'add_capabilities_to_js'));
    }

    /**
     * Register plugin permissions
     */
    public function register_capabilities() {
        // Define permission configuration for each role
        $role_configs = array(
            'administrator' => array(
                'wpca_view_settings' => true,
                'wpca_manage_menus' => true,
                'wpca_manage_all' => true
            ),
            'editor' => array(
                'wpca_view_settings' => true,
                'wpca_manage_menus' => true,
                'wpca_manage_all' => false
            ),
            'author' => array(
                'wpca_view_settings' => true,
                'wpca_manage_menus' => false,
                'wpca_manage_all' => false
            )
        );
        
        // Apply permission configuration for each role
        foreach ($role_configs as $role_name => $caps) {
            $role = get_role($role_name);
            if (!$role) continue;
            
            foreach ($caps as $cap => $grant) {
                if ($grant && method_exists($role, 'add_cap')) {
                    $role->add_cap($cap, true);
                } else if (!$grant && method_exists($role, 'remove_cap')) {
                    // Safely remove capability
                    $role->remove_cap($cap);
                }
            }
        }
    }

    /**
     * Set default permissions
     */
    public function set_default_permissions() {
        // Called during plugin activation
        $this->register_capabilities();
    }

    /**
     * Cleanup plugin permissions
     */
    public function cleanup_capabilities() {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            if (class_exists('WP_Roles')) {
                $wp_roles = new WP_Roles();
            } else {
                return;
            }
        }
        
        if (isset($wp_roles->role_objects)) {
            foreach ($wp_roles->role_objects as $role) {
                foreach (array_keys($this->capabilities) as $cap) {
                    if (method_exists($role, 'remove_cap')) {
                        $role->remove_cap($cap);
                    }
                }
            }
        }
    }

    /**
     * Check if current user has specified permission
     *
     * @param string $capability Permission name
     * @return bool Whether has permission
     */
    public static function current_user_can($capability) {
        // Simplified version to avoid recursive calls and complex permission checks
        
        // Super admins always have permissions
        if (is_multisite() && is_super_admin()) {
            return true;
        }
        
        // Get current user
        $user = wp_get_current_user();
        
        // If user is not logged in, return false
        if (!$user || !$user->ID) {
            return false;
        }
        
        // Admin permission check
        if (isset($user->allcaps['manage_options']) && $user->allcaps['manage_options']) {
            return true;
        }
        
        // Directly check specified permission
        if (isset($user->allcaps[$capability]) && $user->allcaps[$capability]) {
            return true;
        }
        
        // Define permission hierarchy for static access
        // This is duplicated for static access since we can't access private properties
        // from a static method in PHP without reflection
        $capability_hierarchy = array(
            'wpca_manage_all'   => array('wpca_manage_menus', 'wpca_view_settings'),
            'wpca_manage_menus' => array('wpca_view_settings')
        );
        
        // Check for higher-level permissions using the predefined hierarchy
        foreach ($capability_hierarchy as $higher_cap => $lower_caps) {
            if (in_array($capability, $lower_caps) && 
                isset($user->allcaps[$higher_cap]) && 
                $user->allcaps[$higher_cap]) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * AJAX permission check
     */
    public function ajax_check_permission() {
        // Verify nonce
        check_ajax_referer('wpca_settings-options', 'nonce');
        
        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => 'Insufficient permissions'
            ), 403);
            return;
        }
        
        // Check parameters
        if (!isset($_POST['capability']) || empty($_POST['capability'])) {
            wp_send_json_error(array(
                'message' => 'Missing permission parameter'
            ), 400);
            return;
        }
        
        $capability = sanitize_text_field($_POST['capability']);
        
        // Validate permission name
        $valid_caps = array_keys($this->capabilities);
        if (!in_array($capability, $valid_caps)) {
            wp_send_json_error(array(
                'message' => 'Invalid permission name'
            ), 400);
            return;
        }
        
        // Check permission
        $has_permission = self::current_user_can($capability);
        
        wp_send_json_success(array(
            'has_permission' => $has_permission,
            'capability' => $capability,
            'timestamp' => time()
        ));
    }

    /**
     * Add user permissions to JavaScript
     *
     * @param array $data Existing data
     * @return array Data with added permissions
     */
    public function add_capabilities_to_js($data) {
        $user_capabilities = array();
        
        foreach (array_keys($this->capabilities) as $cap) {
            $user_capabilities[$cap] = self::current_user_can($cap);
        }
        
        $data['user_capabilities'] = $user_capabilities;
        $data['can_manage_options'] = current_user_can('manage_options');
        
        return $data;
    }

    /**
     * Get all permissions
     *
     * @return array Permission list
     */
    public function get_capabilities() {
        return $this->capabilities;
    }

    /**
     * Get permission inheritance relationships
     *
     * @return array Permission inheritance relationships
     */
    public function get_capability_hierarchy() {
        return $this->capability_hierarchy;
    }
}