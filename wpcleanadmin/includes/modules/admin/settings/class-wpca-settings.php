<?php
/**
 * WPCleanAdmin Settings Class
 *
 * @package WPCleanAdmin\Modules\Admin\Settings
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */

namespace WPCleanAdmin\Modules\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
        
        // Register settings sections
        $sections = array(
            'general' => array(
                'id' => 'wpca_general_settings',
                'title' => \__( 'General Settings', $text_domain ),
                'callback' => array( $this, 'render_general_settings_section' )
            ),
            'cleanup' => array(
                'id' => 'wpca_cleanup_settings',
                'title' => \__( 'Cleanup Settings', $text_domain ),
                'callback' => array( $this, 'render_cleanup_settings_section' )
            ),
            'performance' => array(
                'id' => 'wpca_performance_settings',
                'title' => \__( 'Performance Settings', $text_domain ),
                'callback' => array( $this, 'render_performance_settings_section' )
            ),
            'security' => array(
                'id' => 'wpca_security_settings',
                'title' => \__( 'Security Settings', $text_domain ),
                'callback' => array( $this, 'render_security_settings_section' )
            )
        );
        
        foreach ( $sections as $section ) {
            if ( function_exists( 'add_settings_section' ) ) {
                \add_settings_section(
                    $section['id'],
                    $section['title'],
                    $section['callback'],
                    'wp-clean-admin'
                );
            }
        }
        
        // Register settings fields
        $this->register_settings_fields();
        
        // Register setting
        if ( function_exists( 'register_setting' ) ) {
            \register_setting( 'wp-clean-admin', 'wpca_settings', array( $this, 'validate_settings' ) );
        }
    }
    
    private function register_settings_fields() {
        // Include settings fields
        if ( file_exists( dirname( __FILE__ ) . '/class-wpca-settings-fields.php' ) ) {
            require_once dirname( __FILE__ ) . '/class-wpca-settings-fields.php';
        }
        
        // Register fields using the fields class
        if ( class_exists( 'WPCleanAdmin\Modules\Admin\Settings\Settings_Fields' ) ) {
            $fields = \WPCleanAdmin\Modules\Admin\Settings\Settings_Fields::getInstance();
            $fields->register_fields();
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
        
        <?php
        // Include scripts
        if ( file_exists( dirname( __FILE__ ) . '/class-wpca-settings-scripts.php' ) ) {
            require_once dirname( __FILE__ ) . '/class-wpca-settings-scripts.php';
        }
        
        // Render scripts using the scripts class
        if ( class_exists( 'WPCleanAdmin\Modules\Admin\Settings\Settings_Scripts' ) ) {
            $scripts = \WPCleanAdmin\Modules\Admin\Settings\Settings_Scripts::getInstance();
            $scripts->render_scripts();
        }
    }
    
    public function validate_settings( $input ) {
        // Include validation
        if ( file_exists( dirname( __FILE__ ) . '/class-wpca-settings-validation.php' ) ) {
            require_once dirname( __FILE__ ) . '/class-wpca-settings-validation.php';
        }
        
        // Validate using the validation class
        if ( class_exists( 'WPCleanAdmin\Modules\Admin\Settings\Settings_Validation' ) ) {
            $validation = \WPCleanAdmin\Modules\Admin\Settings\Settings_Validation::getInstance();
            return $validation->validate( $input );
        }
        
        return $input;
    }
    
    public function enqueue_scripts( $hook ) {
        if ( \strpos( $hook, 'wp-clean-admin' ) === false ) {
            return;
        }
        
        $plugin_url = defined( 'WPCA_PLUGIN_URL' ) ? WPCA_PLUGIN_URL : '';
        $plugin_version = defined( 'WPCA_VERSION' ) ? WPCA_VERSION : '1.8.0';
        
        if ( function_exists( 'wp_enqueue_style' ) ) {
            \wp_enqueue_style(
                'wpca-admin',
                $plugin_url . 'assets/css/wpca-admin.css',
                array(),
                $plugin_version
            );
        }
        
        if ( function_exists( 'wp_enqueue_script' ) ) {
            \wp_enqueue_script(
                'wpca-main',
                $plugin_url . 'assets/js/wpca-main.js',
                array( 'jquery' ),
                $plugin_version,
                true
            );
        }
    }
}