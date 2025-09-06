<?php
// includes/class-wpca-settings.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPCA_Settings
 * Handles the plugin settings page.
 */
class WPCA_Settings {

    /**
     * Stores the plugin options.
     * @var array
     */
    private $options;

    /**
     * WPCA_Settings constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
        $this->options = self::get_options(); // Load options with defaults
    }

    /**
     * Get plugin options, merged with defaults.
     *
     * @return array
     */
    public static function get_options() {
        return wp_parse_args( get_option( 'wpca_settings', array() ), self::get_default_settings() );
    }

    /**
     * Get default plugin settings.
     *
     * @return array
     */
    public static function get_default_settings() {
        return array(
            'hide_dashboard_widgets' => array(),
            'theme_style'            => 'default',
            'hide_admin_menu_items'  => array(),
            'hide_admin_bar_items'   => array(),
        );
    }

    /**
     * Add options page to the admin menu.
     */
    public function add_admin_menu() {
        add_options_page(
            __( 'WP Clean Admin Settings', 'wp-clean-admin' ), // Page title
            __( 'WP Clean Admin', 'wp-clean-admin' ),         // Menu title
            'manage_options',                                  // Capability
            'wp_clean_admin',                                  // Menu slug
            array( $this, 'options_page' )                     // Function
        );
    }

    /**
     * Initialize settings.
     */
    public function settings_init() {
        register_setting( 'wpcaSettingsGroup', 'wpca_settings' );

        // Main section
        add_settings_section(
            'wpca_settings_general_section', // Changed section ID for clarity
            __( 'General Settings', 'wp-clean-admin' ),
            array( $this, 'settings_section_callback' ),
            'wpcaSettingsGroup'
        );

        // Example setting field: Hide Dashboard Widgets
        add_settings_field(
            'wpca_hide_dashboard_widgets',
            __( 'Hide Dashboard Widgets', 'wp-clean-admin' ),
            array( $this, 'hide_dashboard_widgets_render' ),
            'wpcaSettingsGroup',
            'wpca_settings_general_section' // Use new section ID
        );

        // Example setting field: Theme Style
        add_settings_field(
            'wpca_theme_style',
            __( 'Admin Theme Style', 'wp-clean-admin' ),
            array( $this, 'theme_style_render' ),
            'wpcaSettingsGroup',
            'wpca_settings_general_section' // Use new section ID
        );

        // New section: Menu Customization
        add_settings_section(
            'wpca_settings_menu_section',
            __( 'Menu Customization', 'wp-clean-admin' ),
            array( $this, 'menu_section_callback' ),
            'wpcaSettingsGroup'
        );

        // New setting field: Hide Admin Menu Items
        add_settings_field(
            'wpca_hide_admin_menu_items',
            __( 'Hide Admin Menu Items', 'wp-clean-admin' ),
            array( $this, 'hide_admin_menu_items_render' ),
            'wpcaSettingsGroup',
            'wpca_settings_menu_section'
        );

        // New section: Admin Bar Customization
        add_settings_section(
            'wpca_settings_admin_bar_section',
            __( 'Admin Bar Customization', 'wp-clean-admin' ),
            array( $this, 'admin_bar_section_callback' ),
            'wpcaSettingsGroup'
        );

        // New setting field: Hide Admin Bar Items
        add_settings_field(
            'wpca_hide_admin_bar_items',
            __( 'Hide Admin Bar Items', 'wp-clean-admin' ),
            array( $this, 'hide_admin_bar_items_render' ),
            'wpcaSettingsGroup',
            'wpca_settings_admin_bar_section'
        );
    }

    /**
     * Settings section callback.
     */
    public function settings_section_callback() {
        echo __( 'Configure the appearance and behavior of your admin dashboard.', 'wp-clean-admin' );
    }

    /**
     * Menu section callback.
     */
    public function menu_section_callback() {
        echo __( 'Select which admin menu items to hide.', 'wp-clean-admin' );
    }

    /**
     * Admin Bar section callback.
     */
    public function admin_bar_section_callback() {
        echo __( 'Select which admin bar items to hide.', 'wp-clean-admin' );
    }

    /**
     * Render Hide Dashboard Widgets field.
     */
    public function hide_dashboard_widgets_render() {
        $options = $this->options; // Use loaded options
        $widgets_to_hide = $options['hide_dashboard_widgets'] ?? array();
        ?>
        <fieldset>
            <label>
                <input type="checkbox" name="wpca_settings[hide_dashboard_widgets][]" value="dashboard_right_now" <?php checked( in_array( 'dashboard_right_now', $widgets_to_hide ) ); ?>>
                <?php _e( 'Right Now', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_dashboard_widgets][]" value="dashboard_activity" <?php checked( in_array( 'dashboard_activity', $widgets_to_hide ) ); ?>>
                <?php _e( 'Activity', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_dashboard_widgets][]" value="dashboard_quick_press" <?php checked( in_array( 'dashboard_quick_press', $widgets_to_hide ) ); ?>>
                <?php _e( 'Quick Draft', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_dashboard_widgets][]" value="dashboard_primary" <?php checked( in_array( 'dashboard_primary', $widgets_to_hide ) ); ?>>
                <?php _e( 'WordPress Events and News', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_dashboard_widgets][]" value="dashboard_site_health" <?php checked( in_array( 'dashboard_site_health', $widgets_to_hide ) ); ?>>
                <?php _e( 'Site Health Status', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_dashboard_widgets][]" value="dashboard_at_glance" <?php checked( in_array( 'dashboard_at_glance', $widgets_to_hide ) ); ?>>
                <?php _e( 'At a Glance', 'wp-clean-admin' ); ?>
            </label><br>
            <!-- Add more widgets as needed -->
        </fieldset>
        <?php
    }

    /**
     * Render Theme Style field.
     */
    public function theme_style_render() {
        $options = $this->options; // Use loaded options
        ?>
        <select name="wpca_settings[theme_style]">
            <option value="default" <?php selected( $options['theme_style'] ?? '', 'default' ); ?>><?php _e( 'Default (Flat & Clean)', 'wp-clean-admin' ); ?></option>
            <option value="mint" <?php selected( $options['theme_style'] ?? '', 'mint' ); ?>><?php _e( 'Mint Green', 'wp-clean-admin' ); ?></option>
            <option value="dark" <?php selected( $options['theme_style'] ?? '', 'dark' ); ?>><?php _e( 'Dark Mode', 'wp-clean-admin' ); ?></option>
        </select>
        <?php
    }

    /**
     * Render Hide Admin Menu Items field.
     */
    public function hide_admin_menu_items_render() {
        $options = $this->options; // Use loaded options
        $menu_items_to_hide = $options['hide_admin_menu_items'] ?? array();
        ?>
        <fieldset>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_menu_items][]" value="dashboard" <?php checked( in_array( 'dashboard', $menu_items_to_hide ) ); ?>>
                <?php _e( 'Dashboard', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_menu_items][]" value="posts" <?php checked( in_array( 'posts', $menu_items_to_hide ) ); ?>>
                <?php _e( 'Posts', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_menu_items][]" value="media" <?php checked( in_array( 'media', $menu_items_to_hide ) ); ?>>
                <?php _e( 'Media', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_menu_items][]" value="pages" <?php checked( in_array( 'pages', $menu_items_to_hide ) ); ?>>
                <?php _e( 'Pages', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_menu_items][]" value="comments" <?php checked( in_array( 'comments', $menu_items_to_hide ) ); ?>>
                <?php _e( 'Comments', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_menu_items][]" value="themes.php" <?php checked( in_array( 'themes.php', $menu_items_to_hide ) ); ?>>
                <?php _e( 'Appearance', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_menu_items][]" value="plugins.php" <?php checked( in_array( 'plugins.php', $menu_items_to_hide ) ); ?>>
                <?php _e( 'Plugins', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_menu_items][]" value="users.php" <?php checked( in_array( 'users.php', $menu_items_to_hide ) ); ?>>
                <?php _e( 'Users', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_menu_items][]" value="tools.php" <?php checked( in_array( 'tools.php', $menu_items_to_hide ) ); ?>>
                <?php _e( 'Tools', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_menu_items][]" value="options-general.php" <?php checked( in_array( 'options-general.php', $menu_items_to_hide ) ); ?>>
                <?php _e( 'Settings', 'wp-clean-admin' ); ?>
            </label><br>
            <!-- Add more menu items as needed. Use the slug of the top-level menu item. -->
        </fieldset>
        <?php
    }

    /**
     * Render Hide Admin Bar Items field.
     */
    public function hide_admin_bar_items_render() {
        $options = $this->options; // Use loaded options
        $admin_bar_items_to_hide = $options['hide_admin_bar_items'] ?? array();
        ?>
        <fieldset>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_bar_items][]" value="wp-logo" <?php checked( in_array( 'wp-logo', $admin_bar_items_to_hide ) ); ?>>
                <?php _e( 'WordPress Logo', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_bar_items][]" value="site-name" <?php checked( in_array( 'site-name', $admin_bar_items_to_hide ) ); ?>>
                <?php _e( 'Site Name', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_bar_items][]" value="updates" <?php checked( in_array( 'updates', $admin_bar_items_to_hide ) ); ?>>
                <?php _e( 'Updates', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_bar_items][]" value="comments" <?php checked( in_array( 'comments', $admin_bar_items_to_hide ) ); ?>>
                <?php _e( 'Comments', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_bar_items][]" value="new-content" <?php checked( in_array( 'new-content', $admin_bar_items_to_hide ) ); ?>>
                <?php _e( 'New Content', 'wp-clean-admin' ); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wpca_settings[hide_admin_bar_items][]" value="my-account" <?php checked( in_array( 'my-account', $admin_bar_items_to_hide ) ); ?>>
                <?php _e( 'My Account', 'wp-clean-admin' ); ?>
            </label><br>
            <!-- Add more admin bar items as needed. Use the node ID. -->
        </fieldset>
        <?php
    }

    /**
     * Render the options page.
     */
    public function options_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'wpcaSettingsGroup' );
                do_settings_sections( 'wpcaSettingsGroup' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}