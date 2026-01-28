<?php
/**
 * WPCleanAdmin Permissions Class
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



/**
 * Permissions class
 */
class Permissions {
    
    /**
     * Singleton instance
     *
     * @var Permissions
     */
    private static $instance;
    
    /**
     * Get singleton instance
     *
     * @return Permissions
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
     * Initialize the permissions module
     */
    public function init() {
        // Add permissions hooks
        if ( function_exists( 'add_filter' ) ) {
            \add_filter( 'user_has_cap', array( $this, 'filter_user_capabilities' ), 10, 3 );
        }
    }
    
    /**
     * Filter user capabilities
     *
     * @param array $allcaps All capabilities assigned to the user
     * @param array $caps Required capabilities for the capability check
     * @param array $args Arguments passed to the capability check
     * @return array Modified capabilities
     * @uses wpca_get_settings() To retrieve plugin settings
     */
    public function filter_user_capabilities( $allcaps, $caps, $args ) {
        // Load settings
        $settings = wpca_get_settings();
        
        // Apply permission filters based on settings
        if ( isset( $settings['permissions'] ) ) {
            // Restrict access to certain features
            if ( isset( $settings['permissions']['restrict_features'] ) && $settings['permissions']['restrict_features'] ) {
                // Restrict access to specific capabilities
                $restricted_caps = array(
                    'manage_options',
                    'edit_theme_options',
                    'install_plugins',
                    'update_plugins',
                    'delete_plugins',
                    'install_themes',
                    'update_themes',
                    'delete_themes',
                    'import',
                    'export'
                );
                
                // Remove restricted capabilities for non-administrators
                if ( ! isset( $allcaps['administrator'] ) || ! $allcaps['administrator'] ) {
                    foreach ( $restricted_caps as $cap ) {
                        if ( isset( $allcaps[$cap] ) ) {
                            unset( $allcaps[$cap] );
                        }
                    }
                }
            }
        }
        
        return $allcaps;
    }
    
    /**
     * Check if user has permission to access a feature
     *
     * @param string $feature Feature name
     * @param int $user_id User ID
     * @return bool Permission result
     */
    public function has_feature_permission( string $feature, ?int $user_id = null ): bool {
        // Get user ID if not provided
        if ( $user_id === null ) {
            $user_id = ( function_exists( 'get_current_user_id' ) ? \get_current_user_id() : 0 );
        }
        
        // Get user object
        $user = ( function_exists( 'get_user_by' ) ? \get_user_by( 'id', $user_id ) : false );
        if ( ! $user ) {
            return false;
        }
        
        // Load settings
        $settings = wpca_get_settings();
        
        // Check if feature is restricted
        if ( isset( $settings['permissions']['feature_restrictions'] ) && isset( $settings['permissions']['feature_restrictions'][$feature] ) ) {
            $restriction = $settings['permissions']['feature_restrictions'][$feature];
            
            // Check if user has required role
            if ( isset( $restriction['roles'] ) && ! empty( $restriction['roles'] ) ) {
                $user_roles = $user->roles;
                $has_role = array_intersect( $user_roles, $restriction['roles'] );
                
                if ( empty( $has_role ) ) {
                    return false;
                }
            }
            
            // Check if user has required capability
            if ( isset( $restriction['capability'] ) && ! empty( $restriction['capability'] ) ) {
                if ( ! ( function_exists( 'user_can' ) && \user_can( $user_id, $restriction['capability'] ) ) ) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Get user permissions
     *
     * @param int $user_id User ID
     * @return array User permissions
     */
    public function get_user_permissions( $user_id = null ) {
        // Get user ID if not provided
        if ( $user_id === null ) {
            $user_id = ( function_exists( 'get_current_user_id' ) ? \get_current_user_id() : 0 );
        }
        
        // Get user object
        $user = ( function_exists( 'get_user_by' ) ? \get_user_by( 'id', $user_id ) : false );
        if ( ! $user ) {
            return array();
        }
        
        // Get user capabilities
        $capabilities = $user->allcaps;
        
        // Get user roles
        $roles = $user->roles;
        
        // Load settings
        $settings = wpca_get_settings();
        
        // Get feature permissions
        $feature_permissions = array();
        
        if ( isset( $settings['permissions']['feature_restrictions'] ) ) {
            foreach ( $settings['permissions']['feature_restrictions'] as $feature => $restriction ) {
                $feature_permissions[$feature] = $this->has_feature_permission( $feature, $user_id );
            }
        }
        
        return array(
            'capabilities' => $capabilities,
            'roles' => $roles,
            'feature_permissions' => $feature_permissions
        );
    }
    
    /**
     * Restrict access to admin pages
     */
    public function restrict_admin_access() {
        // Load settings
        $settings = wpca_get_settings();
        
        // Check if admin access restriction is enabled
        if ( isset( $settings['permissions']['restrict_admin_access'] ) && $settings['permissions']['restrict_admin_access'] ) {
            // Check if user has access to admin area
            if ( ! ( function_exists( 'current_user_can' ) && \current_user_can( 'manage_options' ) ) ) {
                // Redirect non-administrators to front-end
                if ( function_exists( 'wp_redirect' ) && function_exists( 'home_url' ) ) {
                    \wp_redirect( \home_url() );
                    exit;
                }
            }
        }
    }
    
    /**
     * Restrict access to specific admin pages
     */
    public function restrict_specific_admin_pages() {
        // Load settings
        $settings = wpca_get_settings();
        
        // Check if specific admin page restriction is enabled
        if ( isset( $settings['permissions']['restrict_specific_pages'] ) && $settings['permissions']['restrict_specific_pages'] ) {
            // Get current admin page
            $current_page = isset( $_GET['page'] ) ? ( function_exists( 'sanitize_text_field' ) ? \sanitize_text_field( $_GET['page'] ) : $_GET['page'] ) : '';
            
            // Check if current page is restricted
            if ( isset( $settings['permissions']['restricted_pages'] ) && in_array( $current_page, $settings['permissions']['restricted_pages'] ) ) {
                // Check if user has access to restricted page
                if ( ! ( function_exists( 'current_user_can' ) && \current_user_can( 'manage_options' ) ) ) {
                    // Redirect to admin dashboard
                    if ( function_exists( 'wp_redirect' ) && function_exists( 'admin_url' ) ) {
                        \wp_redirect( \admin_url() );
                        exit;
                    }
                }
            }
        }
    }
}

