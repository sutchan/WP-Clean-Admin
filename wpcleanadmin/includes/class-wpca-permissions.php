<?php

/**
 * Permissions management for WPCleanAdmin plugin
 * 
 * @file wpcleanadmin/includes/class-wpca-permissions.php
 * @version 1.7.15
 * @updated 2025-11-28
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * WPCA_Permissions class
 * Manages user permissions for the plugin
 */
class WPCA_Permissions {
    
    /**
     * Default permissions array
     * 
     * @var array
     */
    private $default_permissions;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->default_permissions = $this->get_default_permissions();
        $this->init();
    }
    
    /**
     * Initialize the permissions class
     */
    private function init() {
        // Register hooks
        add_action('admin_init', array($this, 'register_capabilities'));
    }
    
    /**
     * Get default permissions
     * 
     * @return array Default permissions array
     */
    private function get_default_permissions() {
        return array(
            'manage_options' => array(
                'wpca_manage_settings',
                'wpca_manage_menu',
                'wpca_manage_dashboard',
                'wpca_manage_login',
                'wpca_manage_performance',
                'wpca_manage_security'
            )
        );
    }
    
    /**
     * Register capabilities for WordPress roles
     */
    public function register_capabilities() {
        // Get all WordPress roles
        $roles = wp_roles();
        
        if (! $roles) {
            return;
        }
        
        // Assign capabilities to roles
        foreach ($this->default_permissions as $role_name => $capabilities) {
            $role = $roles->get_role($role_name);
            
            if ($role) {
                foreach ($capabilities as $capability) {
                    $role->add_cap($capability);
                }
            }
        }
    }
    
    /**
     * Set default permissions
     */
    public function set_default_permissions() {
        $this->register_capabilities();
    }
    
    /**
     * Check if current user has a specific capability
     * 
     * @param string $capability Capability to check
     * @return bool True if user has the capability, false otherwise
     */
    public function current_user_can($capability) {
        // Check if WordPress function exists
        if (function_exists('current_user_can')) {
            return current_user_can($capability);
        }
        
        // Fallback: assume admin has all capabilities
        return true;
    }
    
    /**
     * Check if user has a specific capability
     * 
     * @param int $user_id User ID
     * @param string $capability Capability to check
     * @return bool True if user has the capability, false otherwise
     */
    public function user_can($user_id, $capability) {
        // Check if WordPress function exists
        if (function_exists('user_can')) {
            return user_can($user_id, $capability);
        }
        
        // Fallback: assume admin has all capabilities
        return true;
    }
    
    /**
     * Get all capabilities registered by the plugin
     * 
     * @return array Array of capabilities
     */
    public function get_plugin_capabilities() {
        $capabilities = array();
        
        foreach ($this->default_permissions as $role_capabilities) {
            $capabilities = array_merge($capabilities, $role_capabilities);
        }
        
        return array_unique($capabilities);
    }
    
    /**
     * Cleanup capabilities when plugin is deactivated
     */
    public function cleanup_capabilities() {
        // Get all WordPress roles
        $roles = wp_roles();
        
        if (! $roles) {
            return;
        }
        
        // Remove plugin capabilities from all roles
        $capabilities = $this->get_plugin_capabilities();
        
        foreach ($roles->roles as $role_name => $role_data) {
            $role = $roles->get_role($role_name);
            
            if ($role) {
                foreach ($capabilities as $capability) {
                    $role->remove_cap($capability);
                }
            }
        }
    }
    
    /**
     * Add capability to a role
     * 
     * @param string $role_name Role name
     * @param string $capability Capability to add
     * @return bool True if capability was added successfully
     */
    public function add_capability_to_role($role_name, $capability) {
        $roles = wp_roles();
        
        if (! $roles) {
            return false;
        }
        
        $role = $roles->get_role($role_name);
        
        if (! $role) {
            return false;
        }
        
        $role->add_cap($capability);
        return true;
    }
    
    /**
     * Remove capability from a role
     * 
     * @param string $role_name Role name
     * @param string $capability Capability to remove
     * @return bool True if capability was removed successfully
     */
    public function remove_capability_from_role($role_name, $capability) {
        $roles = wp_roles();
        
        if (! $roles) {
            return false;
        }
        
        $role = $roles->get_role($role_name);
        
        if (! $role) {
            return false;
        }
        
        $role->remove_cap($capability);
        return true;
    }
}