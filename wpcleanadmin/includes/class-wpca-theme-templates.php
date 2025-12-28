<?php
/**
 * WPCleanAdmin Theme Templates Class
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
 * Theme Templates class for preset admin configurations
 *
 * Provides pre-configured theme templates for quick setup
 */
class Theme_Templates {
    
    /**
     * Singleton instance
     *
     * @var Theme_Templates
     */
    private static $instance;
    
    /**
     * Registered templates
     *
     * @var array
     */
    private $templates = array();
    
    /**
     * Get singleton instance
     *
     * @return Theme_Templates
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
        $this->register_default_templates();
    }
    
    /**
     * Register default theme templates
     *
     * @return void
     */
    private function register_default_templates() {
        $this->templates = array(
            'minimal' => array(
                'id' => 'minimal',
                'name' => __( 'Minimal', WPCA_TEXT_DOMAIN ),
                'description' => __( 'A clean, minimal admin interface with essential features only.', WPCA_TEXT_DOMAIN ),
                'preview_color' => '#6c757d',
                'settings' => array(
                    'general' => array(
                        'enabled' => true,
                        'clean_admin_bar' => 1,
                        'remove_wp_logo' => 1,
                    ),
                    'menu' => array(
                        'enabled' => true,
                        'simplify_admin_menu' => 1,
                        'remove_dashboard_widgets' => 1,
                    ),
                    'performance' => array(
                        'enabled' => true,
                        'resource_preloading' => 0,
                        'disable_emojis' => 1,
                        'disable_heartbeat' => 1,
                    ),
                ),
            ),
            'developer' => array(
                'id' => 'developer',
                'name' => __( 'Developer', WPCA_TEXT_DOMAIN ),
                'description' => __( 'Optimized for developers with debugging tools and performance features.', WPCA_TEXT_DOMAIN ),
                'preview_color' => '#0073aa',
                'settings' => array(
                    'general' => array(
                        'enabled' => true,
                        'clean_admin_bar' => 1,
                        'remove_wp_logo' => 1,
                    ),
                    'menu' => array(
                        'enabled' => true,
                        'simplify_admin_menu' => 0,
                        'role_based_restrictions' => 1,
                    ),
                    'performance' => array(
                        'enabled' => true,
                        'resource_preloading' => 1,
                        'disable_emojis' => 1,
                        'disable_heartbeat' => 0,
                        'minify_css' => 1,
                        'minify_js' => 1,
                    ),
                    'cleanup' => array(
                        'enabled' => true,
                        'unused_shortcodes' => 1,
                    ),
                ),
            ),
            'business' => array(
                'id' => 'business',
                'name' => __( 'Business', WPCA_TEXT_DOMAIN ),
                'description' => __( 'Professional setup for business websites with enhanced security.', WPCA_TEXT_DOMAIN ),
                'preview_color' => '#28a745',
                'settings' => array(
                    'general' => array(
                        'enabled' => true,
                        'clean_admin_bar' => 1,
                        'remove_wp_logo' => 1,
                    ),
                    'menu' => array(
                        'enabled' => true,
                        'simplify_admin_menu' => 1,
                        'remove_dashboard_widgets' => 1,
                        'role_based_restrictions' => 1,
                    ),
                    'security' => array(
                        'enabled' => true,
                        'hide_wp_version' => 1,
                        'two_factor_auth' => 1,
                    ),
                    'performance' => array(
                        'enabled' => true,
                        'resource_preloading' => 1,
                        'disable_emojis' => 1,
                        'disable_heartbeat' => 1,
                    ),
                ),
            ),
            'ecommerce' => array(
                'id' => 'ecommerce',
                'name' => __( 'E-Commerce', WPCA_TEXT_DOMAIN ),
                'description' => __( 'Tailored for WooCommerce and e-commerce sites.', WPCA_TEXT_DOMAIN ),
                'preview_color' => '#96588a',
                'settings' => array(
                    'general' => array(
                        'enabled' => true,
                        'clean_admin_bar' => 1,
                        'remove_wp_logo' => 0,
                    ),
                    'menu' => array(
                        'enabled' => true,
                        'simplify_admin_menu' => 1,
                        'remove_dashboard_widgets' => 1,
                        'role_based_restrictions' => 1,
                    ),
                    'security' => array(
                        'enabled' => true,
                        'hide_wp_version' => 1,
                        'two_factor_auth' => 1,
                    ),
                    'performance' => array(
                        'enabled' => true,
                        'resource_preloading' => 1,
                        'disable_emojis' => 0,
                        'disable_heartbeat' => 0,
                        'minify_css' => 1,
                        'minify_js' => 1,
                    ),
                ),
            ),
            'blogger' => array(
                'id' => 'blogger',
                'name' => __( 'Blogger', WPCA_TEXT_DOMAIN ),
                'description' => __( 'Simplified setup for bloggers and content creators.', WPCA_TEXT_DOMAIN ),
                'preview_color' => '#dc3545',
                'settings' => array(
                    'general' => array(
                        'enabled' => true,
                        'clean_admin_bar' => 1,
                        'remove_wp_logo' => 1,
                    ),
                    'menu' => array(
                        'enabled' => true,
                        'simplify_admin_menu' => 1,
                        'remove_dashboard_widgets' => 1,
                    ),
                    'performance' => array(
                        'enabled' => true,
                        'resource_preloading' => 0,
                        'disable_emojis' => 0,
                        'disable_heartbeat' => 1,
                    ),
                ),
            ),
            'security_first' => array(
                'id' => 'security_first',
                'name' => __( 'Security First', WPCA_TEXT_DOMAIN ),
                'description' => __( 'Maximum security configuration for high-risk websites.', WPCA_TEXT_DOMAIN ),
                'preview_color' => '#ffc107',
                'settings' => array(
                    'general' => array(
                        'enabled' => true,
                        'clean_admin_bar' => 1,
                        'remove_wp_logo' => 1,
                    ),
                    'menu' => array(
                        'enabled' => true,
                        'simplify_admin_menu' => 1,
                        'remove_dashboard_widgets' => 1,
                        'role_based_restrictions' => 1,
                    ),
                    'security' => array(
                        'enabled' => true,
                        'hide_wp_version' => 1,
                        'two_factor_auth' => 1,
                        'limit_login_attempts' => 1,
                    ),
                    'performance' => array(
                        'enabled' => true,
                        'disable_emojis' => 1,
                        'disable_heartbeat' => 1,
                        'disable_xmlrpc' => 1,
                        'disable_rest_api' => 0,
                    ),
                ),
            ),
        );
        
        $this->templates = apply_filters( 'wpca_register_templates', $this->templates );
    }
    
    /**
     * Get all registered templates
     *
     * @return array Registered templates
     */
    public function get_templates() {
        return $this->templates;
    }
    
    /**
     * Get single template by ID
     *
     * @param string $template_id Template ID
     * @return array|null Template data or null
     */
    public function get_template( $template_id ) {
        $template_id = sanitize_key( $template_id );
        
        return isset( $this->templates[ $template_id ] ) ? $this->templates[ $template_id ] : null;
    }
    
    /**
     * Apply template settings
     *
     * @param string $template_id Template ID
     * @return array Result with success status
     */
    public function apply_template( $template_id ) {
        $template = $this->get_template( $template_id );
        
        if ( ! $template ) {
            return array(
                'success' => false,
                'message' => __( 'Template not found.', WPCA_TEXT_DOMAIN ),
            );
        }
        
        $settings = wpca_get_settings();
        
        foreach ( $template['settings'] as $section => $section_settings ) {
            if ( ! isset( $settings[ $section ] ) ) {
                $settings[ $section ] = array();
            }
            
            foreach ( $section_settings as $key => $value ) {
                $settings[ $section ][ $key ] = $value;
            }
        }
        
        update_option( 'wpca_settings', $settings );
        
        return array(
            'success' => true,
            'message' => sprintf(
                __( 'Template "%s" applied successfully.', WPCA_TEXT_DOMAIN ),
                $template['name']
            ),
            'template' => $template,
        );
    }
    
    /**
     * Export template from current settings
     *
     * @param string $name Template name
     * @param string $description Template description
     * @return array Exported template data
     */
    public function export_current_as_template( $name, $description = '' ) {
        $settings = wpca_get_settings();
        
        $template = array(
            'id' => sanitize_key( $name ),
            'name' => sanitize_text_field( $name ),
            'description' => sanitize_text_field( $description ),
            'preview_color' => '#4a9dec',
            'settings' => $settings,
            'exported_at' => current_time( 'mysql' ),
            'version' => WPCA_VERSION,
        );
        
        return $template;
    }
    
    /**
     * Import template
     *
     * @param array $template_data Template data to import
     * @return array Result with success status
     */
    public function import_template( $template_data ) {
        if ( ! isset( $template_data['settings'] ) || ! is_array( $template_data['settings'] ) ) {
            return array(
                'success' => false,
                'message' => __( 'Invalid template data.', WPCA_TEXT_DOMAIN ),
            );
        }
        
        $settings = wpca_get_settings();
        
        foreach ( $template_data['settings'] as $section => $section_settings ) {
            if ( ! isset( $settings[ $section ] ) ) {
                $settings[ $section ] = array();
            }
            
            foreach ( $section_settings as $key => $value ) {
                $settings[ $section ][ $key ] = $value;
            }
        }
        
        update_option( 'wpca_settings', $settings );
        
        return array(
            'success' => true,
            'message' => __( 'Template imported successfully.', WPCA_TEXT_DOMAIN ),
            'template_name' => isset( $template_data['name'] ) ? $template_data['name'] : __( 'Custom Template', WPCA_TEXT_DOMAIN ),
        );
    }
    
    /**
     * Get template categories
     *
     * @return array Template categories
     */
    public function get_categories() {
        return array(
            'all' => array(
                'id' => 'all',
                'name' => __( 'All Templates', WPCA_TEXT_DOMAIN ),
                'icon' => 'dashicons-admin-multisite',
            ),
            'minimal' => array(
                'id' => 'minimal',
                'name' => __( 'Minimal', WPCA_TEXT_DOMAIN ),
                'icon' => 'dashicons-minus',
            ),
            'professional' => array(
                'id' => 'professional',
                'name' => __( 'Professional', WPCA_TEXT_DOMAIN ),
                'icon' => 'dashicons-businessman',
            ),
            'security' => array(
                'id' => 'security',
                'name' => __( 'Security', WPCA_TEXT_DOMAIN ),
                'icon' => 'dashicons-lock',
            ),
        );
    }
    
    /**
     * Get featured templates
     *
     * @return array Featured templates
     */
    public function get_featured() {
        return array( 'developer', 'business', 'security_first' );
    }
    
    /**
     * Get template count
     *
     * @return int Template count
     */
    public function get_count() {
        return count( $this->templates );
    }
    
    /**
     * Search templates
     *
     * @param string $query Search query
     * @return array Matching templates
     */
    public function search_templates( $query ) {
        $query = strtolower( sanitize_text_field( $query ) );
        $results = array();
        
        foreach ( $this->templates as $id => $template ) {
            $search_text = strtolower( $template['name'] . ' ' . $template['description'] );
            
            if ( strpos( $search_text, $query ) !== false ) {
                $results[ $id ] = $template;
            }
        }
        
        return $results;
    }
    
    /**
     * Validate template data
     *
     * @param array $data Template data to validate
     * @return bool True if valid
     */
    public function validate_template( $data ) {
        if ( ! isset( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
            return false;
        }
        
        if ( ! isset( $data['name'] ) || empty( $data['name'] ) ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get default template ID
     *
     * @return string Default template ID
     */
    public function get_default_template() {
        return 'minimal';
    }
    
    /**
     * Get template preview styles
     *
     * @param string $template_id Template ID
     * @return string CSS styles
     */
    public function get_preview_styles( $template_id ) {
        $template = $this->get_template( $template_id );
        
        if ( ! $template ) {
            return '';
        }
        
        $color = isset( $template['preview_color'] ) ? $template['preview_color'] : '#4a9dec';
        
        return "background: linear-gradient(135deg, {$color} 0%, " . $this->adjust_color_brightness( $color, -30 ) . " 100%);";
    }
    
    /**
     * Adjust color brightness
     *
     * @param string $color Hex color
     * @param int $percent Adjustment percentage
     * @return string Adjusted hex color
     */
    private function adjust_color_brightness( $color, $percent ) {
        $color = ltrim( $color, '#' );
        $num = hexdec( $color );
        
        $amt = $percent * 2.55;
        $R = ( $num >> 16 ) + $amt;
        $G = ( $num >> 8 & 0x00FF ) + $amt;
        $B = ( $num & 0x0000FF ) + $amt;
        
        $R = min( 255, max( 0, $R ) );
        $G = min( 255, max( 0, $G ) );
        $B = min( 255, max( 0, $B ) );
        
        return '#' . dechex( ( $R << 16 ) | ( $G << 8 ) | $B );
    }
}

/**
 * Apply template helper function
 *
 * @param string $template_id Template ID to apply
 * @return array Result
 */
function wpca_apply_template( $template_id ) {
    $templates = Theme_Templates::getInstance();
    return $templates->apply_template( $template_id );
}

/**
 * Get theme templates instance
 *
 * @return Theme_Templates
 */
function wpca_get_theme_templates() {
    return Theme_Templates::getInstance();
}
