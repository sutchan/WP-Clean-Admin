<?php
/**
 * WPCleanAdmin Settings Class
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin;

/**
 * Settings class for WP Clean Admin plugin
 *
 * @package WPCleanAdmin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings class
 */
class Settings {
    
    /**
     * Singleton instance
     *
     * @var Settings
     */
    private static $instance;
    
    /**
     * Get singleton instance
     *
     * @return Settings
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
     * Initialize settings module
     */
    public function init() {
        // Register settings page
        \add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
        
        // Register settings
        \add_action( 'admin_init', array( $this, 'register_settings' ) );
        
        // Enqueue admin scripts and styles
        \add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }
    
    /**
     * Register settings page
     */
    public function register_settings_page() {
        // Add main settings page
        \add_menu_page(
            \__( 'WP Clean Admin', WPCA_TEXT_DOMAIN ),
            \__( 'Clean Admin', WPCA_TEXT_DOMAIN ),
            'manage_options',
            'wp-clean-admin',
            array( $this, 'render_settings_page' ),
            'dashicons-admin-generic',
            60
        );
        
        // Add submenu pages
        \add_submenu_page(
            'wp-clean-admin',
            \__( 'Settings', WPCA_TEXT_DOMAIN ),
            \__( 'Settings', WPCA_TEXT_DOMAIN ),
            'manage_options',
            'wp-clean-admin',
            array( $this, 'render_settings_page' )
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        // Register general settings section
        \add_settings_section(
            'wpca_general_settings',
            \__( 'General Settings', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_general_settings_section' ),
            'wp-clean-admin'
        );
        
        // Register general settings fields
        \add_settings_field(
            'wpca_clean_admin_bar',
            \__( 'Clean Admin Bar', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_clean_admin_bar_field' ),
            'wp-clean-admin',
            'wpca_general_settings'
        );
        
        \add_settings_field(
            'wpca_remove_wp_logo',
            \__( 'Remove WordPress Logo', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_remove_wp_logo_field' ),
            'wp-clean-admin',
            'wpca_general_settings'
        );
        
        // Register cleanup settings section
        \add_settings_section(
            'wpca_cleanup_settings',
            \__( 'Cleanup Settings', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_cleanup_settings_section' ),
            'wp-clean-admin'
        );
        
        // Register cleanup settings fields
        \add_settings_field(
            'wpca_remove_dashboard_widgets',
            \__( 'Remove Dashboard Widgets', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_remove_dashboard_widgets_field' ),
            'wp-clean-admin',
            'wpca_cleanup_settings'
        );
        
        \add_settings_field(
            'wpca_simplify_admin_menu',
            \__( 'Simplify Admin Menu', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_simplify_admin_menu_field' ),
            'wp-clean-admin',
            'wpca_cleanup_settings'
        );
        
        // Register performance settings section
        \add_settings_section(
            'wpca_performance_settings',
            \__( 'Performance Settings', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_performance_settings_section' ),
            'wp-clean-admin'
        );
        
        // Register performance settings fields
        \add_settings_field(
            'wpca_optimize_database',
            \__( 'Optimize Database', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_optimize_database_field' ),
            'wp-clean-admin',
            'wpca_performance_settings'
        );
        
        \add_settings_field(
            'wpca_clean_transients',
            \__( 'Clean Transients', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_clean_transients_field' ),
            'wp-clean-admin',
            'wpca_performance_settings'
        );
        
        \add_settings_field(
            'wpca_disable_emojis',
            \__( 'Disable Emojis', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_disable_emojis_field' ),
            'wp-clean-admin',
            'wpca_performance_settings'
        );
        
        // Register security settings section
        \add_settings_section(
            'wpca_security_settings',
            \__( 'Security Settings', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_security_settings_section' ),
            'wp-clean-admin'
        );
        
        // Register security settings fields
        \add_settings_field(
            'wpca_hide_wp_version',
            \__( 'Hide WordPress Version', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_hide_wp_version_field' ),
            'wp-clean-admin',
            'wpca_security_settings'
        );
        
        // Register settings
        \register_setting( 'wp-clean-admin', 'wpca_settings', array( $this, 'validate_settings' ) );
    }
    
    /**
     * Render general settings section
     */
    public function render_general_settings_section() {
        echo '<p>' . \__( 'Configure general settings for WP Clean Admin plugin.', WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    /**
     * Render clean admin bar field
     */
    public function render_clean_admin_bar_field() {
        $settings = \get_option( 'wpca_settings', array() );
        $clean_admin_bar = isset( $settings['general']['clean_admin_bar'] ) ? $settings['general']['clean_admin_bar'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[general][clean_admin_bar]" value="1" ' . \checked( $clean_admin_bar, 1, false ) . ' />';
        echo '<label for="wpca_clean_admin_bar"> ' . \__( 'Remove unnecessary items from the admin bar.', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Render remove WordPress logo field
     */
    public function render_remove_wp_logo_field() {
        $settings = \get_option( 'wpca_settings', array() );
        $remove_wp_logo = isset( $settings['general']['remove_wp_logo'] ) ? $settings['general']['remove_wp_logo'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[general][remove_wp_logo]" value="1" ' . \checked( $remove_wp_logo, 1, false ) . ' />';
        echo '<label for="wpca_remove_wp_logo"> ' . \__( 'Remove WordPress logo from admin bar.', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Render cleanup settings section
     */
    public function render_cleanup_settings_section() {
        echo '<p>' . \__( 'Configure cleanup settings for WP Clean Admin plugin.', WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    /**
     * Render remove dashboard widgets field
     */
    public function render_remove_dashboard_widgets_field() {
        $settings = \get_option( 'wpca_settings', array() );
        $remove_dashboard_widgets = isset( $settings['menu']['remove_dashboard_widgets'] ) ? $settings['menu']['remove_dashboard_widgets'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[menu][remove_dashboard_widgets]" value="1" ' . \checked( $remove_dashboard_widgets, 1, false ) . ' />';
        echo '<label for="wpca_remove_dashboard_widgets"> ' . \__( 'Remove unnecessary dashboard widgets.', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Render simplify admin menu field
     */
    public function render_simplify_admin_menu_field() {
        $settings = \get_option( 'wpca_settings', array() );
        $simplify_admin_menu = isset( $settings['menu']['simplify_admin_menu'] ) ? $settings['menu']['simplify_admin_menu'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[menu][simplify_admin_menu]" value="1" ' . \checked( $simplify_admin_menu, 1, false ) . ' />';
        echo '<label for="wpca_simplify_admin_menu"> ' . \__( 'Simplify admin menu by removing unnecessary items.', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Render performance settings section
     */
    public function render_performance_settings_section() {
        echo '<p>' . \__( 'Configure performance optimization settings for WP Clean Admin plugin.', WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    /**
     * Render optimize database field
     */
    public function render_optimize_database_field() {
        $settings = \get_option( 'wpca_settings', array() );
        $optimize_database = isset( $settings['performance']['optimize_database'] ) ? $settings['performance']['optimize_database'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[performance][optimize_database]" value="1" ' . \checked( $optimize_database, 1, false ) . ' />';
        echo '<label for="wpca_optimize_database"> ' . \__( 'Automatically optimize database tables.', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Render clean transients field
     */
    public function render_clean_transients_field() {
        $settings = \get_option( 'wpca_settings', array() );
        $clean_transients = isset( $settings['performance']['clean_transients'] ) ? $settings['performance']['clean_transients'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[performance][clean_transients]" value="1" ' . \checked( $clean_transients, 1, false ) . ' />';
        echo '<label for="wpca_clean_transients"> ' . \__( 'Automatically clean expired transients.', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Render disable emojis field
     */
    public function render_disable_emojis_field() {
        $settings = \get_option( 'wpca_settings', array() );
        $disable_emojis = isset( $settings['performance']['disable_emoji'] ) ? $settings['performance']['disable_emoji'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[performance][disable_emoji]" value="1" ' . \checked( $disable_emojis, 1, false ) . ' />';
        echo '<label for="wpca_disable_emojis"> ' . \__( 'Disable WordPress emoji support.', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Render security settings section
     */
    public function render_security_settings_section() {
        echo '<p>' . \__( 'Configure security settings for WP Clean Admin plugin.', WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    /**
     * Render hide WordPress version field
     */
    public function render_hide_wp_version_field() {
        $settings = \get_option( 'wpca_settings', array() );
        $hide_wp_version = isset( $settings['security']['hide_wp_version'] ) ? $settings['security']['hide_wp_version'] : 1;
        
        echo '<input type="checkbox" name="wpca_settings[security][hide_wp_version]" value="1" ' . \checked( $hide_wp_version, 1, false ) . ' />';
        echo '<label for="wpca_hide_wp_version"> ' . \__( 'Hide WordPress version information.', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo \esc_html( \get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
                <?php
                // Output settings fields
                \settings_fields( 'wp-clean-admin' );
                \do_settings_sections( 'wp-clean-admin' );
                \submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Validate settings
     *
     * @param array $input Input settings
     * @return array Validated settings
     */
    public function validate_settings( $input ) {
        $validated = array();
        
        // Validate general settings
        if ( isset( $input['general'] ) ) {
            $validated['general'] = array();
            
            // Validate clean admin bar setting
            $validated['general']['clean_admin_bar'] = isset( $input['general']['clean_admin_bar'] ) ? 1 : 0;
            
            // Validate remove WordPress logo setting
            $validated['general']['remove_wp_logo'] = isset( $input['general']['remove_wp_logo'] ) ? 1 : 0;
        }
        
        // Validate menu settings
        if ( isset( $input['menu'] ) ) {
            $validated['menu'] = array();
            
            // Validate remove dashboard widgets setting
            $validated['menu']['remove_dashboard_widgets'] = isset( $input['menu']['remove_dashboard_widgets'] ) ? 1 : 0;
            
            // Validate simplify admin menu setting
            $validated['menu']['simplify_admin_menu'] = isset( $input['menu']['simplify_admin_menu'] ) ? 1 : 0;
        }
        
        // Validate performance settings
        if ( isset( $input['performance'] ) ) {
            $validated['performance'] = array();
            
            // Validate optimize database setting
            $validated['performance']['optimize_database'] = isset( $input['performance']['optimize_database'] ) ? 1 : 0;
            
            // Validate clean transients setting
            $validated['performance']['clean_transients'] = isset( $input['performance']['clean_transients'] ) ? 1 : 0;
            
            // Validate disable emoji setting
            $validated['performance']['disable_emoji'] = isset( $input['performance']['disable_emoji'] ) ? 1 : 0;
        }
        
        // Validate security settings
        if ( isset( $input['security'] ) ) {
            $validated['security'] = array();
            
            // Validate hide WordPress version setting
            $validated['security']['hide_wp_version'] = isset( $input['security']['hide_wp_version'] ) ? 1 : 0;
        }
        
        return $validated;
    }
    
    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_scripts( $hook ) {
        // Only enqueue on plugin pages
        if ( \strpos( $hook, 'wp-clean-admin' ) === false ) {
            return;
        }
        
        // Enqueue main CSS
        \wp_enqueue_style(
            'wpca-admin',
            WPCA_PLUGIN_URL . 'assets/css/wpca-admin.css',
            array(),
            WPCA_VERSION
        );
        
        // Enqueue settings CSS
        \wp_enqueue_style(
            'wpca-settings',
            WPCA_PLUGIN_URL . 'assets/css/wpca-settings.css',
            array( 'wpca-admin' ),
            WPCA_VERSION
        );
        
        // Enqueue main JS
        \wp_enqueue_script(
            'wpca-main',
            WPCA_PLUGIN_URL . 'assets/js/wpca-main.js',
            array( 'jquery' ),
            WPCA_VERSION,
            true
        );
        
        // Enqueue settings JS
        \wp_enqueue_script(
            'wpca-settings',
            WPCA_PLUGIN_URL . 'assets/js/wpca-settings.js',
            array( 'jquery', 'wpca-main' ),
            WPCA_VERSION,
            true
        );
        
        // Localize script
        \wp_localize_script( 'wpca-settings', 'wpca_settings_vars', array(
            'ajax_url' => \admin_url( 'admin-ajax.php' ),
            'nonce' => \wp_create_nonce( 'wpca_settings_nonce' ),
            'save' => \__( 'Save Changes', WPCA_TEXT_DOMAIN ),
            'saved' => \__( 'Settings saved successfully!', WPCA_TEXT_DOMAIN ),
            'error' => \__( 'An error occurred while saving settings.', WPCA_TEXT_DOMAIN )
        ));
    }
}
