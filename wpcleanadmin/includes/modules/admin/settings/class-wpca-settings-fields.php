<?php
/**
 * WPCleanAdmin Settings Fields
 *
 * @package WPCleanAdmin\Modules\Admin\Settings
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

require_once __DIR__ . '/class-wpca-settings-field-renderers.php';

namespace WPCleanAdmin\Modules\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'add_settings_field' ) ) {
    function add_settings_field() {}
}
if ( ! function_exists( 'add_settings_section' ) ) {
    function add_settings_section() {}
}

/**
 * Settings Fields class
 */
class Settings_Fields {

    /**
     * 字段渲染器
     *
     * @var Settings_Field_Renderers
     */
    private $renderers;

    /**
     * Constructor
     */
    public function __construct() {
        $this->renderers = new Settings_Field_Renderers();
    }

    /**
     * Register all settings fields
     */
    public function register_fields() {
        // General settings
        if ( function_exists( '\add_settings_section' ) ) {
            \add_settings_section(
                'wpca_general_section',
                \__( 'General Settings', 'wp-clean-admin' ),
                array( $this, 'render_general_section' ),
                'wp-clean-admin'
            );
        }

        $fields = array(
            array(
                'id'          => 'clean_admin_bar',
                'title'       => \__( 'Clean Admin Bar', 'wp-clean-admin' ),
                'type'        => 'checkbox',
                'description' => \__( 'Remove unnecessary items from the admin bar.', 'wp-clean-admin' ),
                'section'     => 'wpca_general_section',
            ),
            array(
                'id'          => 'clean_dashboard',
                'title'       => \__( 'Clean Dashboard', 'wp-clean-admin' ),
                'type'        => 'checkbox',
                'description' => \__( 'Remove unnecessary dashboard widgets.', 'wp-clean-admin' ),
                'section'     => 'wpca_general_section',
            ),
            array(
                'id'          => 'remove_wp_logo',
                'title'       => \__( 'Remove WP Logo', 'wp-clean-admin' ),
                'type'        => 'checkbox',
                'description' => \__( 'Remove the WordPress logo from the admin bar.', 'wp-clean-admin' ),
                'section'     => 'wpca_general_section',
            ),
            array(
                'id'          => 'log_level',
                'title'       => \__( 'Log Level', 'wp-clean-admin' ),
                'type'        => 'select',
                'options'     => array(
                    'debug'    => \__( 'Debug', 'wp-clean-admin' ),
                    'info'     => \__( 'Info', 'wp-clean-admin' ),
                    'notice'   => \__( 'Notice', 'wp-clean-admin' ),
                    'warning'  => \__( 'Warning', 'wp-clean-admin' ),
                    'error'    => \__( 'Error', 'wp-clean-admin' ),
                    'critical' => \__( 'Critical', 'wp-clean-admin' ),
                ),
                'description' => \__( 'Select the minimum log level to record.', 'wp-clean-admin' ),
                'section'     => 'wpca_general_section',
            ),
        );

        foreach ( $fields as $field ) {
            if ( function_exists( '\add_settings_field' ) ) {
                \add_settings_field(
                    $field['id'],
                    $field['title'],
                    array( $this, 'render_field' ),
                    'wp-clean-admin',
                    $field['section'],
                    $field
                );
            }
        }
    }

    /**
     * Render a single field
     *
     * @param array $args
     */
    public function render_field( $args ) {
        $value    = isset( $args['value'] ) ? $args['value'] : '';
        $callback = 'render_' . $args['type'] . '_field';

        if ( method_exists( $this->renderers, $callback ) ) {
            $args['value'] = \get_option( $args['id'], $value );
            $this->renderers->{$callback}( $args );
        }
    }

    /**
     * Render general section description
     */
    public function render_general_section() {
        echo '<p>' . \esc_html( \__( 'Configure general plugin behavior.', 'wp-clean-admin' ) ) . '</p>';
    }

    /**
     * Render text field (delegated)
     */
    public function render_text_field( $args ) { $this->renderers->render_text_field( $args ); }

    /**
     * Render textarea field (delegated)
     */
    public function render_textarea_field( $args ) { $this->renderers->render_textarea_field( $args ); }

    /**
     * Render checkbox field (delegated)
     */
    public function render_checkbox_field( $args ) { $this->renderers->render_checkbox_field( $args ); }

    /**
     * Render radio field (delegated)
     */
    public function render_radio_field( $args ) { $this->renderers->render_radio_field( $args ); }

    /**
     * Render select field (delegated)
     */
    public function render_select_field( $args ) { $this->renderers->render_select_field( $args ); }

    /**
     * Render number field (delegated)
     */
    public function render_number_field( $args ) { $this->renderers->render_number_field( $args ); }

    /**
     * Render color field (delegated)
     */
    public function render_color_field( $args ) { $this->renderers->render_color_field( $args ); }

    /**
     * Render date field (delegated)
     */
    public function render_date_field( $args ) { $this->renderers->render_date_field( $args ); }

    /**
     * Render email field (delegated)
     */
    public function render_email_field( $args ) { $this->renderers->render_email_field( $args ); }

    /**
     * Render URL field (delegated)
     */
    public function render_url_field( $args ) { $this->renderers->render_url_field( $args ); }

    /**
     * Render password field (delegated)
     */
    public function render_password_field( $args ) { $this->renderers->render_password_field( $args ); }
}
