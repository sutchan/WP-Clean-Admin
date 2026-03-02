<?php
/**
 * WPCleanAdmin Settings Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @update_date 2026-01-28
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include menu customization settings
if ( file_exists( dirname( __FILE__ ) . '/settings/menu-customization.php' ) ) {
    require_once dirname( __FILE__ ) . '/settings/menu-customization.php';
}

// Include settings fields classes
if ( file_exists( dirname( __FILE__ ) . '/settings/fields/class-wpca-general-settings-fields.php' ) ) {
    require_once dirname( __FILE__ ) . '/settings/fields/class-wpca-general-settings-fields.php';
}

if ( file_exists( dirname( __FILE__ ) . '/settings/fields/class-wpca-cleanup-settings-fields.php' ) ) {
    require_once dirname( __FILE__ ) . '/settings/fields/class-wpca-cleanup-settings-fields.php';
}

if ( file_exists( dirname( __FILE__ ) . '/settings/fields/class-wpca-performance-settings-fields.php' ) ) {
    require_once dirname( __FILE__ ) . '/settings/fields/class-wpca-performance-settings-fields.php';
}

if ( file_exists( dirname( __FILE__ ) . '/settings/fields/class-wpca-security-settings-fields.php' ) ) {
    require_once dirname( __FILE__ ) . '/settings/fields/class-wpca-security-settings-fields.php';
}

// Include settings validation class
if ( file_exists( dirname( __FILE__ ) . '/settings/class-wpca-settings-validation.php' ) ) {
    require_once dirname( __FILE__ ) . '/settings/class-wpca-settings-validation.php';
}

// Include settings scripts class
if ( file_exists( dirname( __FILE__ ) . '/settings/class-wpca-settings-scripts.php' ) ) {
    require_once dirname( __FILE__ ) . '/settings/class-wpca-settings-scripts.php';
}

// WordPress stubs are loaded in autoload.php for IDE compatibility only

class Settings {
    
    private static $instance = null;
    
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    public function init() {
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
            \add_action( 'admin_init', array( $this, 'register_settings' ) );
            \add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        }
    }
    
    public function register_settings_page() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        if ( function_exists( 'add_options_page' ) ) {
            \add_options_page(
                \__( 'WP Clean Admin', $text_domain ),
                \__( 'Clean Admin', $text_domain ),
                'manage_options',
                'wp-clean-admin',
                array( $this, 'render_settings_page' )
            );
        }
    }
    
    public function register_settings() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        // Register general settings section
        if ( function_exists( 'add_settings_section' ) ) {
            \add_settings_section(
                'wpca_general_settings',
                \__( 'General Settings', $text_domain ),
                array( $this, 'render_general_settings_section' ),
                'wp-clean-admin'
            );
        }
        
        // Register cleanup settings section
        if ( function_exists( 'add_settings_section' ) ) {
            \add_settings_section(
                'wpca_cleanup_settings',
                \__( 'Cleanup Settings', $text_domain ),
                array( $this, 'render_cleanup_settings_section' ),
                'wp-clean-admin'
            );
        }
        
        // Register performance settings section
        if ( function_exists( 'add_settings_section' ) ) {
            \add_settings_section(
                'wpca_performance_settings',
                \__( 'Performance Settings', $text_domain ),
                array( $this, 'render_performance_settings_section' ),
                'wp-clean-admin'
            );
        }
        
        // Register security settings section
        if ( function_exists( 'add_settings_section' ) ) {
            \add_settings_section(
                'wpca_security_settings',
                \__( 'Security Settings', $text_domain ),
                array( $this, 'render_security_settings_section' ),
                'wp-clean-admin'
            );
        }
        
        // Register general settings fields
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_clean_admin_bar',
                \__( 'Clean Admin Bar', $text_domain ),
                array( '\WPCleanAdmin\Settings\Fields\General_Settings_Fields', 'render_clean_admin_bar_field' ),
                'wp-clean-admin',
                'wpca_general_settings'
            );
            
            \add_settings_field(
                'wpca_remove_wp_logo',
                \__( 'Remove WordPress Logo', $text_domain ),
                array( '\WPCleanAdmin\Settings\Fields\General_Settings_Fields', 'render_remove_wp_logo_field' ),
                'wp-clean-admin',
                'wpca_general_settings'
            );
        }
        
        // Register cleanup settings fields
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_remove_dashboard_widgets',
                \__( 'Remove Dashboard Widgets', $text_domain ),
                array( '\WPCleanAdmin\Settings\Fields\Cleanup_Settings_Fields', 'render_remove_dashboard_widgets_field' ),
                'wp-clean-admin',
                'wpca_cleanup_settings'
            );
            
            \add_settings_field(
                'wpca_simplify_admin_menu',
                \__( 'Simplify Admin Menu', $text_domain ),
                array( '\WPCleanAdmin\Settings\Fields\Cleanup_Settings_Fields', 'render_simplify_admin_menu_field' ),
                'wp-clean-admin',
                'wpca_cleanup_settings'
            );
            
            \add_settings_field(
                'wpca_menu_customization',
                \__( 'Menu Customization', $text_domain ),
                array( '\WPCleanAdmin\Settings\Fields\Cleanup_Settings_Fields', 'render_menu_customization_field' ),
                'wp-clean-admin',
                'wpca_cleanup_settings'
            );
        }
        
        // Register performance settings fields
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_optimize_database',
                \__( 'Optimize Database', $text_domain ),
                array( '\WPCleanAdmin\Settings\Fields\Performance_Settings_Fields', 'render_optimize_database_field' ),
                'wp-clean-admin',
                'wpca_performance_settings'
            );
            
            \add_settings_field(
                'wpca_clean_transients',
                \__( 'Clean Transients', $text_domain ),
                array( '\WPCleanAdmin\Settings\Fields\Performance_Settings_Fields', 'render_clean_transients_field' ),
                'wp-clean-admin',
                'wpca_performance_settings'
            );
        }
        
        // Register security settings fields
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_hide_wp_version',
                \__( 'Hide WordPress Version', $text_domain ),
                array( '\WPCleanAdmin\Settings\Fields\Security_Settings_Fields', 'render_hide_wp_version_field' ),
                'wp-clean-admin',
                'wpca_security_settings'
            );
            
            \add_settings_field(
                'wpca_disable_xmlrpc',
                \__( 'Disable XML-RPC', $text_domain ),
                array( '\WPCleanAdmin\Settings\Fields\Security_Settings_Fields', 'render_disable_xmlrpc_field' ),
                'wp-clean-admin',
                'wpca_security_settings'
            );
            
            \add_settings_field(
                'wpca_restrict_rest_api',
                \__( 'Restrict REST API Access', $text_domain ),
                array( '\WPCleanAdmin\Settings\Fields\Security_Settings_Fields', 'render_restrict_rest_api_field' ),
                'wp-clean-admin',
                'wpca_security_settings'
            );
            
            \add_settings_field(
                'wpca_restrict_admin_access',
                \__( 'Restrict Admin Access', $text_domain ),
                array( '\WPCleanAdmin\Settings\Fields\Security_Settings_Fields', 'render_restrict_admin_access_field' ),
                'wp-clean-admin',
                'wpca_security_settings'
            );
        }
        
        // Register setting
        if ( function_exists( 'register_setting' ) ) {
            \register_setting( 'wp-clean-admin', 'wpca_settings', array( '\WPCleanAdmin\Settings\Settings_Validation', 'validate_settings' ) );
        }
    }
    
    public function render_general_settings_section() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        echo '<p>' . \__( 'Configure general settings for WP Clean Admin plugin.', $text_domain ) . '</p>';
    }
    
    public function render_cleanup_settings_section() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        echo '<p>' . \__( 'Configure cleanup settings for WP Clean Admin plugin.', $text_domain ) . '</p>';
    }
    
    public function render_performance_settings_section() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        echo '<p>' . \__( 'Configure performance optimization settings for WP Clean Admin plugin.', $text_domain ) . '</p>';
    }
    
    public function render_security_settings_section() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        echo '<p>' . \__( 'Configure security settings for WP Clean Admin plugin.', $text_domain ) . '</p>';
    }
    
    public function render_settings_page() {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        ?>
        <div class="wrap wpca-settings-wrap">
            <h1><?php echo \esc_html( ( function_exists( 'get_admin_page_title' ) ? \get_admin_page_title() : 'WP Clean Admin' ) ); ?></h1>
            
            <form method="post" action="options.php" id="wpca-settings-form">
                <?php
                if ( function_exists( 'settings_fields' ) ) {
                    \settings_fields( 'wp-clean-admin' );
                }
                ?>
                
                <!-- Settings Tabs -->
                <div class="wpca-settings-tabs">
                    <div class="wpca-tabs-nav">
                        <button type="button" class="wpca-tab-button active" data-tab="general">
                            <span class="dashicons dashicons-admin-generic"></span>
                            <?php echo \esc_html( \__( 'General', $text_domain ) ); ?>
                        </button>
                        <button type="button" class="wpca-tab-button" data-tab="cleanup">
                            <span class="dashicons dashicons-clipboard"></span>
                            <?php echo \esc_html( \__( 'Cleanup', $text_domain ) ); ?>
                        </button>
                        <button type="button" class="wpca-tab-button" data-tab="performance">
                            <span class="dashicons dashicons-chart-line"></span>
                            <?php echo \esc_html( \__( 'Performance', $text_domain ) ); ?>
                        </button>
                        <button type="button" class="wpca-tab-button" data-tab="security">
                            <span class="dashicons dashicons-shield"></span>
                            <?php echo \esc_html( \__( 'Security', $text_domain ) ); ?>
                        </button>
                    </div>
                    
                    <div class="wpca-tabs-content">
                        <!-- General Tab -->
                        <div class="wpca-tab-content active" id="wpca-tab-general">
                            <?php
                            if ( function_exists( 'do_settings_sections' ) ) {
                                // Render only general settings
                                \do_settings_sections( 'wp-clean-admin' );
                            }
                            ?>
                        </div>
                        
                        <!-- Cleanup Tab -->
                        <div class="wpca-tab-content" id="wpca-tab-cleanup">
                            <?php
                            if ( function_exists( 'do_settings_sections' ) ) {
                                // Render only cleanup settings
                                \do_settings_sections( 'wp-clean-admin' );
                            }
                            ?>
                        </div>
                        
                        <!-- Performance Tab -->
                        <div class="wpca-tab-content" id="wpca-tab-performance">
                            <?php
                            if ( function_exists( 'do_settings_sections' ) ) {
                                // Render only performance settings
                                \do_settings_sections( 'wp-clean-admin' );
                            }
                            ?>
                        </div>
                        
                        <!-- Security Tab -->
                        <div class="wpca-tab-content" id="wpca-tab-security">
                            <?php
                            if ( function_exists( 'do_settings_sections' ) ) {
                                // Render only security settings
                                \do_settings_sections( 'wp-clean-admin' );
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="wpca-settings-submit">
                    <?php
                    if ( function_exists( 'submit_button' ) ) {
                        \submit_button( \__( 'Save Changes', $text_domain ), 'primary', 'submit', false, array( 'id' => 'wpca-save-button' ) );
                    }
                    ?>
                    <div class="wpca-save-message" id="wpca-save-message"></div>
                </div>
            </form>
        </div>
        
        <script type="text/javascript">
        (function($) {
            'use strict';
            
            $(document).ready(function() {
                // Settings tabs functionality
                const tabButtons = $('.wpca-tab-button');
                const tabContents = $('.wpca-tab-content');
                
                tabButtons.on('click', function() {
                    const tabId = $(this).data('tab');
                    
                    // Remove active class from all tabs
                    tabButtons.removeClass('active');
                    tabContents.removeClass('active');
                    
                    // Add active class to selected tab
                    $(this).addClass('active');
                    $(`#wpca-tab-${tabId}`).addClass('active');
                    
                    // Scroll to top of settings
                    $('html, body').animate({
                        scrollTop: $('.wpca-settings-form').offset().top - 20
                    }, 300);
                });
                
                // Form submission handling
                $('#wpca-settings-form').on('submit', function(e) {
                    // Show saving message
                    $('#wpca-save-message').html('<span class="wpca-saving">' + <?php echo \json_encode( \__( 'Saving...', $text_domain ) ); ?> + '</span>');
                });
                
                // Add toggle functionality to setting sections
                $('.wpca-settings-section h3').on('click', function() {
                    const section = $(this).closest('.wpca-settings-section');
                    const content = section.nextUntil('.wpca-settings-section');
                    
                    section.toggleClass('collapsed');
                    content.slideToggle();
                });
            });
        })(jQuery);
        </script>
        
        <style type="text/css">
            .wpca-settings-wrap {
                max-width: 1200px;
                margin: 0 auto;
            }
            
            .wpca-settings-tabs {
                margin: 20px 0;
                background: #fff;
                border: 1px solid #e1e1e1;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }
            
            .wpca-tabs-nav {
                display: flex;
                flex-wrap: wrap;
                background: #f8f9fa;
                border-bottom: 1px solid #e1e1e1;
                padding: 0;
                margin: 0;
            }
            
            .wpca-tab-button {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 12px 20px;
                background: transparent;
                border: none;
                border-bottom: 3px solid transparent;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 14px;
                font-weight: 500;
                color: #555;
            }
            
            .wpca-tab-button:hover {
                background: rgba(0, 124, 186, 0.05);
                color: #007cba;
            }
            
            .wpca-tab-button.active {
                background: #fff;
                color: #007cba;
                border-bottom-color: #007cba;
            }
            
            .wpca-tab-button .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
            }
            
            .wpca-tabs-content {
                padding: 20px;
            }
            
            .wpca-tab-content {
                display: none;
            }
            
            .wpca-tab-content.active {
                display: block;
                animation: wpca-fade-in 0.3s ease;
            }
            
            .wpca-settings-section {
                margin-bottom: 20px;
                padding: 20px;
                background: #f8f9fa;
                border: 1px solid #e1e1e1;
                border-radius: 6px;
                transition: all 0.3s ease;
            }
            
            .wpca-settings-section:hover {
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .wpca-settings-section h3 {
                margin: 0 0 15px;
                padding: 0;
                font-size: 16px;
                font-weight: 600;
                color: #333;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            
            .wpca-settings-section h3:after {
                content: '▼';
                font-size: 12px;
                color: #666;
                transition: transform 0.3s ease;
            }
            
            .wpca-settings-section.collapsed h3:after {
                transform: rotate(-90deg);
            }
            
            .wpca-settings-section .form-table {
                margin: 0;
                background: #fff;
                border: 1px solid #e1e1e1;
                border-radius: 4px;
                overflow: hidden;
            }
            
            .wpca-settings-section .form-table tr {
                border-bottom: 1px solid #f0f0f0;
                transition: background-color 0.2s ease;
            }
            
            .wpca-settings-section .form-table tr:last-child {
                border-bottom: none;
            }
            
            .wpca-settings-section .form-table tr:hover {
                background-color: #f8f9fa;
            }
            
            .wpca-settings-section .form-table th {
                padding: 12px 15px;
                width: 300px;
                font-weight: 500;
                color: #333;
                background: #fafafa;
                border-right: 1px solid #f0f0f0;
            }
            
            .wpca-settings-section .form-table td {
                padding: 12px 15px;
                color: #555;
            }
            
            .wpca-settings-submit {
                margin: 30px 0;
                padding: 20px;
                background: #f8f9fa;
                border: 1px solid #e1e1e1;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            
            #wpca-save-button {
                font-size: 14px;
                padding: 8px 20px;
                font-weight: 500;
            }
            
            .wpca-save-message {
                font-size: 14px;
            }
            
            .wpca-saving {
                color: #007cba;
                font-weight: 500;
            }
            
            /* Responsive Design */
            @media screen and (max-width: 782px) {
                .wpca-settings-wrap {
                    padding: 0 10px;
                }
                
                .wpca-tabs-nav {
                    flex-direction: column;
                }
                
                .wpca-tab-button {
                    justify-content: flex-start;
                    border-bottom: 1px solid #e1e1e1;
                }
                
                .wpca-tab-button.active {
                    border-bottom: 1px solid #e1e1e1;
                    border-left: 3px solid #007cba;
                }
                
                .wpca-settings-section .form-table {
                    display: block;
                }
                
                .wpca-settings-section .form-table tr {
                    display: block;
                    border-bottom: 1px solid #f0f0f0;
                }
                
                .wpca-settings-section .form-table th,
                .wpca-settings-section .form-table td {
                    display: block;
                    width: 100%;
                    border-right: none;
                    border-bottom: 1px solid #f0f0f0;
                }
                
                .wpca-settings-section .form-table th {
                    background: #fafafa;
                    padding-bottom: 8px;
                }
                
                .wpca-settings-section .form-table td {
                    padding-top: 8px;
                    padding-bottom: 12px;
                }
                
                .wpca-settings-submit {
                    flex-direction: column;
                    gap: 15px;
                    align-items: stretch;
                }
                
                #wpca-save-button {
                    width: 100%;
                    text-align: center;
                }
            }
            
            /* Animation */
            @keyframes wpca-fade-in {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
        <?php
    }
    
    public function enqueue_scripts( $hook ) {
        \WPCleanAdmin\Settings\Settings_Scripts::enqueue_scripts( $hook );
    }
}
