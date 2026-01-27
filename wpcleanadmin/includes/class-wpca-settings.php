<?php
/**
 * WPCleanAdmin Settings Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @update_date 2026-01-27
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */

namespace WPCleanAdmin;

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
        if ( function_exists( 'add_options_page' ) ) {
            \add_options_page(
                \__( 'WP Clean Admin', WPCA_TEXT_DOMAIN ),
                \__( 'Clean Admin', WPCA_TEXT_DOMAIN ),
                'manage_options',
                'wp-clean-admin',
                array( $this, 'render_settings_page' )
            );
        }
    }
    
    public function register_settings() {
        if ( function_exists( 'add_settings_section' ) ) {
            \add_settings_section(
                'wpca_general_settings',
                \__( 'General Settings', WPCA_TEXT_DOMAIN ),
                array( $this, 'render_general_settings_section' ),
                'wp-clean-admin'
            );
        }
        
        if ( function_exists( 'add_settings_field' ) ) {
            \add_settings_field(
                'wpca_clean_admin_bar',
                \__( 'Clean Admin Bar', WPCA_TEXT_DOMAIN ),
                array( $this, 'render_clean_admin_bar_field' ),
                'wp-clean-admin',
                'wpca_general_settings'
            );
        }
        
        if ( function_exists( 'register_setting' ) ) {
            \register_setting( 'wp-clean-admin', 'wpca_settings', array( $this, 'validate_settings' ) );
        }
    }
    
    public function render_general_settings_section() {
        echo '<p>' . \__( 'Configure general settings for WP Clean Admin plugin.', WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    public function render_clean_admin_bar_field() {
        $settings = array();
        if ( function_exists( 'get_option' ) ) {
            $settings = \get_option( 'wpca_settings', array() );
        }
        $clean_admin_bar = isset( $settings['general']['clean_admin_bar'] ) ? $settings['general']['clean_admin_bar'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[general][clean_admin_bar]" value="1" ' . ( function_exists( 'checked' ) ? \checked( $clean_admin_bar, 1, false ) : ( $clean_admin_bar ? 'checked="checked"' : '' ) ) . ' />';
        echo '<label for="wpca_clean_admin_bar"> ' . \__( 'Remove unnecessary items from the admin bar.', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo \esc_html( ( function_exists( 'get_admin_page_title' ) ? \get_admin_page_title() : 'WP Clean Admin' ) ); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                if ( function_exists( 'settings_fields' ) ) {
                    \settings_fields( 'wp-clean-admin' );
                }
                ?>
                
                <?php
                if ( function_exists( 'do_settings_sections' ) ) {
                    \do_settings_sections( 'wp-clean-admin' );
                }
                ?>
                
                <?php
                if ( function_exists( 'submit_button' ) ) {
                    \submit_button( \__( 'Save Changes', WPCA_TEXT_DOMAIN ) );
                }
                ?>
            </form>
        </div>
        <?php
    }
    
    public function validate_settings( $input ) {
        $validated = array();
        
        if ( isset( $input['general'] ) ) {
            $validated['general'] = array();
            $validated['general']['clean_admin_bar'] = isset( $input['general']['clean_admin_bar'] ) ? 1 : 0;
        }
        
        return $validated;
    }
    
    public function enqueue_scripts( $hook ) {
        if ( \strpos( $hook, 'wp-clean-admin' ) === false ) {
            return;
        }
        
        if ( function_exists( 'wp_enqueue_style' ) ) {
            \wp_enqueue_style(
                'wpca-admin',
                WPCA_PLUGIN_URL . 'assets/css/wpca-admin.css',
                array(),
                WPCA_VERSION
            );
        }
        
        if ( function_exists( 'wp_enqueue_script' ) ) {
            \wp_enqueue_script(
                'wpca-main',
                WPCA_PLUGIN_URL . 'assets/js/wpca-main.js',
                array( 'jquery' ),
                WPCA_VERSION,
                true
            );
        }
    }
}
