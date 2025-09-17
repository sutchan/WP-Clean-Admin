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
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'wp_ajax_wpca_save_tab_preference', array( $this, 'ajax_save_tab_preference' ) );
        $this->options = self::get_options(); // Load options with defaults
    }
    
    /**
     * AJAX handler to save tab preference
     */
    public function ajax_save_tab_preference() {
        // Check nonce for security
        check_ajax_referer('wpca_settings-options');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
            return;
        }
        
        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'tab-general';
        
        // Get current options
        $options = self::get_options();
        
        // Update tab preference
        $options['current_tab'] = $tab;
        
        // Save updated options
        update_option('wpca_settings', $options);
        
        wp_send_json_success();
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wp_clean_admin') === false) {
            return;
        }
        
        // Enqueue jQuery UI for sortable functionality
        wp_enqueue_script('jquery-ui-sortable');
        
        // Enqueue CSS file for the settings page
        wp_enqueue_style( 'wpca-admin-style', WPCA_PLUGIN_URL . 'assets/css/wp-clean-admin.css', array(), WPCA_VERSION );
        
        // Enqueue custom script for the settings page
        wp_enqueue_script( 'wpca-settings-script', WPCA_PLUGIN_URL . 'assets/js/wpca-settings.js', array( 'jquery', 'jquery-ui-sortable' ), WPCA_VERSION, true );
        
        // Localize script with ajaxurl and nonce
        wp_localize_script( 'wpca-settings-script', 'wpca_settings', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'current_tab' => $this->options['current_tab'] ?? 'tab-general'
        ));
        
        // 添加菜单自定义所需的数据
        wp_localize_script( 'wpca-settings-script', 'wpca_admin', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce('wpca_menu_toggle'),
            'reset_confirm' => __('Are you sure you want to reset all menu settings to default?', 'wp-clean-admin'),
            'resetting_text' => __('Resetting...', 'wp-clean-admin'),
            'reset_text' => __('Reset Defaults', 'wp-clean-admin'),
            'reset_failed' => __('Reset failed. Please try again.', 'wp-clean-admin')
        ));
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
            'current_tab' => 'tab-general',
            'menu_toggle' => 1, // Default to enabled
            'menu_visibility' => array(), // Stores visibility state for each menu item
            'hide_dashboard_widgets' => array(),
            'theme_style'            => 'default',
            'hide_admin_menu_items'  => array(),
            'hide_admin_bar_items'   => array(),
            'menu_order'            => array(),    // Menu order settings
            'submenu_order'         => array(),    // Submenu order settings
            'layout_density'         => 'standard', // standard, compact, spacious
            'border_radius_style'    => 'small',    // none, small, large
            'shadow_style'           => 'subtle',   // none, subtle
            'primary_color'          => '#4A90E2',  // New default: 清新蓝
            'background_color'       => '#F8F9FA',  // New default
            'text_color'             => '#2D3748',  // New default
            'font_stack'             => 'system',   // system, google_fonts (future)
            'font_size_base'         => 'medium',   // small, medium, large
            'icon_style'             => 'dashicons',// dashicons, linear_icons (future)
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
        register_setting( 
            'wpca_settings', 
            'wpca_settings',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings')
            )
        );

        // Main section
        add_settings_section(
            'wpca_settings_general_section',
            __( 'General Settings', 'wp-clean-admin' ),
            array( $this, 'settings_section_callback' ),
            'wpca_settings'
        );

        // Example setting field: Hide Dashboard Widgets
        add_settings_field(
            'wpca_hide_dashboard_widgets',
            __( 'Hide Dashboard Widgets', 'wp-clean-admin' ),
            array( $this, 'hide_dashboard_widgets_render' ),
            'wpca_settings',
            'wpca_settings_general_section'
        );

        // Visual Style section
        add_settings_section(
            'wpca_settings_visual_style_section',
            __( 'Visual Style', 'wp-clean-admin' ),
            array( $this, 'visual_style_section_callback' ),
            'wpca_settings_visual_style'
        );

        // Visual Style fields
        add_settings_field(
            'wpca_theme_style',
            __( 'Theme Style', 'wp-clean-admin' ),
            array( $this, 'theme_style_render' ),
            'wpca_settings_visual_style',
            'wpca_settings_visual_style_section'
        );

        add_settings_field(
            'wpca_primary_color',
            __( 'Primary Color', 'wp-clean-admin' ),
            array( $this, 'primary_color_render' ),
            'wpca_settings_visual_style',
            'wpca_settings_visual_style_section'
        );

        add_settings_field(
            'wpca_background_color',
            __( 'Background Color', 'wp-clean-admin' ),
            array( $this, 'background_color_render' ),
            'wpca_settings_visual_style',
            'wpca_settings_visual_style_section'
        );

        add_settings_field(
            'wpca_text_color',
            __( 'Text Color', 'wp-clean-admin' ),
            array( $this, 'text_color_render' ),
            'wpca_settings_visual_style',
            'wpca_settings_visual_style_section'
        );

        add_settings_field(
            'wpca_shadow_style',
            __( 'Shadow Style', 'wp-clean-admin' ),
            array( $this, 'shadow_style_render' ),
            'wpca_settings_visual_style',
            'wpca_settings_visual_style_section'
        );
        
        // Layout & Typography fields (moved to Visual Style)
        add_settings_field(
            'wpca_layout_density',
            __( 'Layout Density', 'wp-clean-admin' ),
            array( $this, 'layout_density_render' ),
            'wpca_settings_visual_style',
            'wpca_settings_visual_style_section'
        );

        add_settings_field(
            'wpca_border_radius_style',
            __( 'Border Radius', 'wp-clean-admin' ),
            array( $this, 'border_radius_style_render' ),
            'wpca_settings_visual_style',
            'wpca_settings_visual_style_section'
        );

        add_settings_field(
            'wpca_font_stack',
            __( 'Font Stack', 'wp-clean-admin' ),
            array( $this, 'font_stack_render' ),
            'wpca_settings_visual_style',
            'wpca_settings_visual_style_section'
        );

        add_settings_field(
            'wpca_font_size_base',
            __( 'Font Size', 'wp-clean-admin' ),
            array( $this, 'font_size_base_render' ),
            'wpca_settings_visual_style',
            'wpca_settings_visual_style_section'
        );

        add_settings_field(
            'wpca_icon_style',
            __( 'Icon Style', 'wp-clean-admin' ),
            array( $this, 'icon_style_render' ),
            'wpca_settings_visual_style',
            'wpca_settings_visual_style_section'
        );



        // Menu Customization section
        add_settings_section(
            'wpca_settings_menu_section',
            __( 'Menu Customization', 'wp-clean-admin' ),
            array( $this, 'menu_section_callback' ),
            'wpca_settings_menu'
        );

        // Menu Customization fields
        add_settings_field(
            'wpca_menu_order',
            __( 'Menu Order', 'wp-clean-admin' ),
            array( $this, 'menu_order_render' ),
            'wpca_settings_menu',
            'wpca_settings_menu_section'
        );

        // Admin Bar Customization field (moved to General section)
        add_settings_field(
            'wpca_hide_admin_bar_items',
            __( 'Hide Admin Bar Items', 'wp-clean-admin' ),
            array( $this, 'hide_admin_bar_items_render' ),
            'wpca_settings',
            'wpca_settings_general_section'
        );
        
        // About section
        add_settings_section(
            'wpca_settings_about_section',
            __( 'About WP Clean Admin', 'wp-clean-admin' ),
            array( $this, 'about_section_callback' ),
            'wpca_settings_about'
        );
    }

    /**
     * Settings section callback.
     */
    public function settings_section_callback() {
        echo __( 'Configure the appearance and behavior of your admin dashboard.', 'wp-clean-admin' );
    }

    /**
     * Visual Style section callback.
     */
    public function visual_style_section_callback() {
        echo __( 'Customize the overall visual theme, colors, layout and typography.', 'wp-clean-admin' );
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
     * About section callback.
     */
    public function about_section_callback() {
        echo __( 'Information about WP Clean Admin plugin.', 'wp-clean-admin' );
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
            <option value="light_blue_gray" <?php selected( $options['theme_style'] ?? '', 'light_blue_gray' ); ?>><?php _e( 'Light Blue Gray', 'wp-clean-admin' ); ?></option>
            <option value="mint" <?php selected( $options['theme_style'] ?? '', 'mint' ); ?>><?php _e( 'Mint Green', 'wp-clean-admin' ); ?></option>
            <option value="dark" <?php selected( $options['theme_style'] ?? '', 'dark' ); ?>><?php _e( 'Dark Mode', 'wp-clean-admin' ); ?></option>
            <option value="custom" <?php selected( $options['theme_style'] ?? '', 'custom' ); ?>><?php _e( 'Custom Colors', 'wp-clean-admin' ); ?></option>
        </select>
        <p class="description"><?php _e( 'Choose a predefined theme or select "Custom Colors" to define your own.', 'wp-clean-admin' ); ?></p>
        <?php
    }

    /**
     * Render Primary Color field.
     */
    public function primary_color_render() {
        $options = $this->options;
        ?>
        <input type="text" name="wpca_settings[primary_color]" value="<?php echo esc_attr( $options['primary_color'] ); ?>" class="wpca-color-picker" data-default-color="<?php echo esc_attr( self::get_default_settings()['primary_color'] ); ?>">
        <p class="description"><?php _e( 'Choose the primary accent color for links, buttons, and active states.', 'wp-clean-admin' ); ?></p>
        <?php
    }

    /**
     * Render Background Color field.
     */
    public function background_color_render() {
        $options = $this->options;
        ?>
        <input type="text" name="wpca_settings[background_color]" value="<?php echo esc_attr( $options['background_color'] ); ?>" class="wpca-color-picker" data-default-color="<?php echo esc_attr( self::get_default_settings()['background_color'] ); ?>">
        <p class="description"><?php _e( 'Choose the main background color of the admin area.', 'wp-clean-admin' ); ?></p>
        <?php
    }

    /**
     * Render Text Color field.
     */
    public function text_color_render() {
        $options = $this->options;
        ?>
        <input type="text" name="wpca_settings[text_color]" value="<?php echo esc_attr( $options['text_color'] ); ?>" class="wpca-color-picker" data-default-color="<?php echo esc_attr( self::get_default_settings()['text_color'] ); ?>">
        <p class="description"><?php _e( 'Choose the default text color.', 'wp-clean-admin' ); ?></p>
        <?php
    }

    /**
     * Render Shadow Style field.
     */
    public function shadow_style_render() {
        $options = $this->options;
        ?>
        <select name="wpca_settings[shadow_style]">
            <option value="none" <?php selected( $options['shadow_style'] ?? '', 'none' ); ?>><?php _e( 'None', 'wp-clean-admin' ); ?></option>
            <option value="subtle" <?php selected( $options['shadow_style'] ?? '', 'subtle' ); ?>><?php _e( 'Subtle (Default)', 'wp-clean-admin' ); ?></option>
        </select>
        <p class="description"><?php _e( 'Choose the shadow style for elements like post boxes and buttons.', 'wp-clean-admin' ); ?></p>
        <?php
    }

    /**
     * Render Layout Density field.
     */
    public function layout_density_render() {
        $options = $this->options;
        ?>
        <select name="wpca_settings[layout_density]">
            <option value="compact" <?php selected( $options['layout_density'] ?? '', 'compact' ); ?>><?php _e( 'Compact', 'wp-clean-admin' ); ?></option>
            <option value="standard" <?php selected( $options['layout_density'] ?? '', 'standard' ); ?>><?php _e( 'Standard (Default)', 'wp-clean-admin' ); ?></option>
            <option value="spacious" <?php selected( $options['layout_density'] ?? '', 'spacious' ); ?>><?php _e( 'Spacious', 'wp-clean-admin' ); ?></option>
        </select>
        <p class="description"><?php _e( 'Adjust the spacing and padding of elements in the admin interface.', 'wp-clean-admin' ); ?></p>
        <?php
    }

    /**
     * Render Border Radius Style field.
     */
    public function border_radius_style_render() {
        $options = $this->options;
        ?>
        <select name="wpca_settings[border_radius_style]">
            <option value="none" <?php selected( $options['border_radius_style'] ?? '', 'none' ); ?>><?php _e( 'None (Sharp Corners)', 'wp-clean-admin' ); ?></option>
            <option value="small" <?php selected( $options['border_radius_style'] ?? '', 'small' ); ?>><?php _e( 'Small (4px, Default)', 'wp-clean-admin' ); ?></option>
            <option value="large" <?php selected( $options['border_radius_style'] ?? '', 'large' ); ?>><?php _e( 'Large (8px, Rounded)', 'wp-clean-admin' ); ?></option>
        </select>
        <p class="description"><?php _e( 'Choose the border-radius style for various elements.', 'wp-clean-admin' ); ?></p>
        <?php
    }

    /**
     * Render Font Stack field.
     */
    public function font_stack_render() {
        $options = $this->options;
        ?>
        <select name="wpca_settings[font_stack]">
            <option value="system" <?php selected( $options['font_stack'] ?? '', 'system' ); ?>><?php _e( 'System Default', 'wp-clean-admin' ); ?></option>
            <!-- Future: <option value="google_fonts" <?php selected( $options['font_stack'] ?? '', 'google_fonts' ); ?>><?php _e( 'Google Fonts', 'wp-clean-admin' ); ?></option> -->
        </select>
        <p class="description"><?php _e( 'Select the font stack to be used in the admin area.', 'wp-clean-admin' ); ?></p>
        <?php
    }

    /**
     * Render Base Font Size field.
     */
    public function font_size_base_render() {
        $options = $this->options;
        ?>
        <select name="wpca_settings[font_size_base]">
            <option value="small" <?php selected( $options['font_size_base'] ?? '', 'small' ); ?>><?php _e( 'Small', 'wp-clean-admin' ); ?></option>
            <option value="medium" <?php selected( $options['font_size_base'] ?? '', 'medium' ); ?>><?php _e( 'Medium (Default)', 'wp-clean-admin' ); ?></option>
            <option value="large" <?php selected( $options['font_size_base'] ?? '', 'large' ); ?>><?php _e( 'Large', 'wp-clean-admin' ); ?></option>
        </select>
        <p class="description"><?php _e( 'Adjust the base font size for the admin interface.', 'wp-clean-admin' ); ?></p>
        <?php
    }

    /**
     * Render Icon Style field.
     */
    public function icon_style_render() {
        $options = $this->options;
        ?>
        <select name="wpca_settings[icon_style]">
            <option value="dashicons" <?php selected( $options['icon_style'] ?? '', 'dashicons' ); ?>><?php _e( 'WordPress Dashicons', 'wp-clean-admin' ); ?></option>
            <!-- Future: <option value="linear_icons" <?php selected( $options['icon_style'] ?? '', 'linear_icons' ); ?>><?php _e( 'Linear Icons', 'wp-clean-admin' ); ?></option> -->
        </select>
        <p class="description"><?php _e( 'Choose the icon set for the admin interface.', 'wp-clean-admin' ); ?></p>
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
    /**
     * Render Menu Order field
     */
    public function menu_order_render() {
        $options = $this->options;
        $menu_order = $options['menu_order'] ?? array();
        $submenu_order = $options['submenu_order'] ?? array();
        
        // Ensure menu_toggle is always set
        $options['menu_toggle'] = isset($options['menu_toggle']) ? (int)$options['menu_toggle'] : 1;
        
        echo '<div class="wpca-menu-toggle-wrapper">';
        echo '<label>';
        echo '<input type="checkbox" id="wpca-menu-toggle" name="wpca_settings[menu_toggle]" value="1" ' . checked( $options['menu_toggle'], 1, false ) . '>';
        echo __('Enable Menu Customization', 'wp-clean-admin');
        echo '</label>';
        echo '<p class="description">' . __('Toggle to show/hide and reorder admin menu items', 'wp-clean-admin') . '</p>';
        echo '</div>';
        
        // Enhanced toggle functionality with state sync
        echo '<script>
        jQuery(document).ready(function($) {
            // Initialize menu items with saved visibility states
            $(".wpca-menu-toggle-switch input[type=checkbox]").each(function() {
                var slug = $(this).closest("li").data("menu-slug");
                var isVisible = $(this).is(":checked");
                $(this).closest(".wpca-menu-toggle-switch").toggleClass("checked", isVisible);
            });

            // Update menu visibility based on main toggle
            function updateMenuVisibility(isEnabled) {
                $(".wpca-menu-sortable, .wpca-menu-order-header").toggle(isEnabled);
                if (!isEnabled) {
                    $(".wpca-menu-toggle-switch input[type=checkbox]")
                        .prop("checked", false)
                        .trigger("change")
                        .closest(".wpca-menu-toggle-switch")
                        .removeClass("checked");
                }
            }
            
            $("#wpca-menu-toggle").on("change", function() {
                updateMenuVisibility(this.checked);
            }).trigger("change");
            
            // Handle individual menu item toggle clicks
            $(document).on("click", ".wpca-menu-toggle-switch", function(e) {
                e.preventDefault();
                var checkbox = $(this).find("input[type=checkbox]");
                var newState = !checkbox.prop("checked");
                checkbox.prop("checked", newState).trigger("change");
                $(this).toggleClass("checked", newState);
                
                // Update main toggle state if needed
                var anyEnabled = $(".wpca-menu-toggle-switch input[type=checkbox]:checked").length > 0;
                $("#wpca-menu-toggle").prop("checked", anyEnabled);
            });
        });
        </script>';
        
        // Add dynamic CSS for toggle switch
        echo '<style>
        .wpca-menu-toggle-switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 20px;
            margin-left: 10px;
            vertical-align: middle;
            cursor: pointer;
        }
        .wpca-menu-toggle-switch .wpca-toggle-slider {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            border-radius: 20px;
            transition: .4s;
        }
        .wpca-menu-toggle-switch.checked .wpca-toggle-slider {
            background-color: #2271b1;
        }
        .wpca-menu-toggle-switch .wpca-toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            border-radius: 50%;
            transition: .4s;
        }
        .wpca-menu-toggle-switch.checked .wpca-toggle-slider:before {
            transform: translateX(20px);
        }
        .wpca-menu-toggle-switch input[type="checkbox"] {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
        }
        </style>';
        
        echo '<ul id="wpca-menu-order" class="wpca-menu-sortable" style="' . (isset($options['menu_toggle']) && !$options['menu_toggle'] ? 'display:none;' : '') . '">';
        
        echo '<div class="wpca-menu-order-wrapper" style="' . (isset($options['menu_toggle']) && !$options['menu_toggle'] ? 'display:none;' : '') . '">';
        
        // Get all menu items including top-level and submenus
        $menu_customizer = new WPCA_Menu_Customizer();
        $all_menu_items = $menu_customizer->get_all_menu_items();
        
        // Group items by type (top-level or submenu)
        $top_level_items = [];
        $submenu_items = [];
        foreach ($all_menu_items as $slug => $item) {
            if ($item['type'] === 'top') {
                $top_level_items[$slug] = $item;
            } else {
                if (!isset($submenu_items[$item['parent']])) {
                    $submenu_items[$item['parent']] = [];
                }
                $submenu_items[$item['parent']][$slug] = $item;
            }
        }
        ?>
        <div class="wpca-menu-order-wrapper">
            <div class="wpca-menu-order-header" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px; border-left: 4px solid #2271b1;">
                <h3 style="margin-top: 0; color: #2271b1;">
                    <span class="dashicons dashicons-menu" style="vertical-align: middle;"></span>
                    <?php _e('Menu Order Customization', 'wp-clean-admin'); ?>
                </h3>
                <p class="description" style="margin-bottom: 15px;">
                    <?php _e('Drag and drop menu items to reorder them. Use the toggle switches to show/hide items.', 'wp-clean-admin'); ?>
                </p>
                <button type="button" id="wpca-reset-menu-order" class="button button-secondary" style="margin-top: 10px;">
                    <span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-right: 5px;"></span>
                    <?php _e('Reset to Default Order', 'wp-clean-admin'); ?>
                </button>
                <script>
                jQuery(document).ready(function($) {
                    $("#wpca-reset-menu-order").on("click", function() {
                        if (confirm("<?php _e('Are you sure you want to reset all menu items to default order and visibility?', 'wp-clean-admin'); ?>")) {
                            var $button = $(this);
                            var originalText = $button.html();
                            
                            // Show loading state
                            $button.html('<span class="dashicons dashicons-update spin" style="vertical-align: middle; margin-right: 5px;"></span> <?php _e('Resetting...', 'wp-clean-admin'); ?>');
                            
                            // Reset operations
                            setTimeout(function() {
                                // Reset visibility to default (all enabled)
                                $(".wpca-menu-toggle-switch input[type=checkbox]").prop("checked", true)
                                    .trigger("change")
                                    .closest(".wpca-menu-toggle-switch")
                                    .addClass("checked");
                                
                                // Reset menu order to default WordPress order
                                var $sortable = $(".wpca-menu-sortable");
                                $sortable.find("li").sort(function(a, b) {
                                    return $(a).data("menu-slug").localeCompare($(b).data("menu-slug"));
                                }).appendTo($sortable);
                                
                                // Update the hidden fields with new order
                                $sortable.find("input[name='wpca_settings[menu_order][]']").each(function(index) {
                                    $(this).val($(this).closest("li").data("menu-slug"));
                                });
                                
                                // Also reset the main toggle
                                $("#wpca-menu-toggle").prop("checked", true).trigger("change");
                                
                                // Restore button text
                                $button.html(originalText);
                                
                                // Show success notice
                                showSuccessNotice('<?php _e('Menu order has been reset to default', 'wp-clean-admin'); ?>', $('.wrap h1').first());
                            }, 500);
                        }
                    });
                });
                </script>
            </div>
            <ul id="wpca-menu-order" class="wpca-menu-sortable">
                <?php 
                // Combine all menu items (top level and submenus) into a single flat list
                $all_items = [];
                
                // First add top level items in saved order
                foreach ($menu_order as $item_slug) {
                    if (isset($top_level_items[$item_slug])) {
                        $all_items[$item_slug] = $top_level_items[$item_slug];
                        
                        // Add submenu items in saved order
                        if (isset($submenu_items[$item_slug])) {
                            $parent_order = $submenu_order[$item_slug] ?? [];
                            foreach ($parent_order as $sub_slug) {
                                if (isset($submenu_items[$item_slug][$item_slug.'|'.$sub_slug])) {
                                    $all_items[$item_slug.'|'.$sub_slug] = $submenu_items[$item_slug][$item_slug.'|'.$sub_slug];
                                }
                            }
                        }
                    }
                }
                
                // Then add remaining top level items not in saved order
                foreach ($top_level_items as $item_slug => $item) {
                    if (!in_array($item_slug, $menu_order)) {
                        $all_items[$item_slug] = $item;
                        
                        // Add submenu items not in saved order
                        if (isset($submenu_items[$item_slug])) {
                            foreach ($submenu_items[$item_slug] as $sub_slug => $sub_item) {
                                $all_items[$sub_slug] = $sub_item;
                            }
                        }
                    }
                }
                
                // Render items with hierarchy
                $render_menu_items = function($items, $level = 0) use (&$render_menu_items) {
                    foreach ($items as $slug => $item) {
                        $is_submenu = $level > 0;
                        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                        
                        echo '<li data-menu-slug="'.esc_attr($slug).'" data-item-type="'.($is_submenu ? 'sub' : 'top').'"';
                        echo $is_submenu ? ' data-parent-slug="'.esc_attr($item['parent']).'"' : '';
                        echo $is_submenu ? ' class="submenu-item level-'.$level.'"' : '';
                        echo '>';
                        echo '<span class="dashicons dashicons-menu"></span> ';
                        // Clean up menu title - remove numbers and additional info
                        $clean_title = preg_replace([
                            '/\s*[\d]+\s*/',  // Remove numbers
                            '/条评论待审/',    // Remove Chinese status
                            '/待审$/',         // Remove trailing status
                            '/条$/'            // Remove counter
                        ], '', strip_tags($item['title']));
                        echo $indent . esc_html(trim($clean_title));
                        echo '<label class="wpca-menu-toggle-switch">';
                        echo '<input type="checkbox" name="wpca_settings[menu_toggles]['.esc_attr($slug).']" value="1" ' 
                            . checked( isset($options['menu_toggles'][$slug]) ? $options['menu_toggles'][$slug] : 1, 1, false ) . '>';
                        echo '<span class="wpca-toggle-slider"></span>';
                        echo '</label>';
                        echo '<input type="hidden" name="wpca_settings[menu_order][]" value="'.esc_attr($slug).'">';
                        echo '</li>';
                        
                        // Render children if exists
                        if (!empty($item['children'])) {
                            $render_menu_items($item['children'], $level + 1);
                        }
                    }
                };
                
                // Build hierarchical menu structure
                $hierarchical_items = [];
                foreach ($all_items as $slug => $item) {
                    if (empty($item['parent'])) {
                        $hierarchical_items[$slug] = $item;
                    } else {
                        if (!isset($hierarchical_items[$item['parent']]['children'])) {
                            $hierarchical_items[$item['parent']]['children'] = [];
                        }
                        $hierarchical_items[$item['parent']]['children'][$slug] = $item;
                    }
                }
                
                $render_menu_items($hierarchical_items);
                ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Render the About tab content.
     */
    public function render_about_tab() {
        ?>
        <div class="wpca-about-wrapper">
            <div class="wpca-about-header">
                <h2><?php _e('WP Clean Admin', 'wp-clean-admin'); ?></h2>
                <p class="wpca-version"><?php echo sprintf(__('Version %s', 'wp-clean-admin'), WPCA_VERSION); ?></p>
            </div>
            
            <div class="wpca-about-section">
                <h3><?php _e('Plugin Description', 'wp-clean-admin'); ?></h3>
                <p><?php _e('WP Clean Admin is a powerful WordPress plugin designed to streamline and customize your WordPress admin experience. It provides a cleaner, more efficient interface by allowing you to hide unnecessary elements, customize the visual appearance, and reorganize the admin menu to better suit your workflow.', 'wp-clean-admin'); ?></p>
            </div>
            
            <div class="wpca-about-section">
                <h3><?php _e('Key Features', 'wp-clean-admin'); ?></h3>
                <ul>
                    <li><?php _e('Hide unnecessary dashboard widgets', 'wp-clean-admin'); ?></li>
                    <li><?php _e('Customize admin menu by hiding or reordering items', 'wp-clean-admin'); ?></li>
                    <li><?php _e('Hide admin bar items for a cleaner top bar', 'wp-clean-admin'); ?></li>
                    <li><?php _e('Choose from multiple visual themes or create your own', 'wp-clean-admin'); ?></li>
                    <li><?php _e('Adjust layout density and typography settings', 'wp-clean-admin'); ?></li>
                    <li><?php _e('Customize colors, shadows, and border styles', 'wp-clean-admin'); ?></li>
                </ul>
            </div>
            
            <div class="wpca-about-section">
                <h3><?php _e('Author', 'wp-clean-admin'); ?></h3>
                <p><?php _e('Created by WordPress Admin UI Specialists', 'wp-clean-admin'); ?></p>
                <p><a href="https://github.com/sutchan/WP-Clean-Admin" target="_blank"><?php _e('Visit Plugin Website', 'wp-clean-admin'); ?></a> | 
                <a href="https://github.com/sutchan/WP-Clean-Admin" target="_blank"><?php _e('Documentation', 'wp-clean-admin'); ?></a> | 
                <a href="https://github.com/sutchan/WP-Clean-Admin/issues" target="_blank"><?php _e('Support', 'wp-clean-admin'); ?></a></p>
            </div>
            
            <div class="wpca-about-section">
                <h3><?php _e('Rate & Review', 'wp-clean-admin'); ?></h3>
                <p><?php _e('If you find this plugin useful, please consider leaving a review on WordPress.org. Your feedback helps improve the plugin and reach more users.', 'wp-clean-admin'); ?></p>
                <p><a href="https://github.com/sutchan/WP-Clean-Admin" target="_blank" class="button button-primary"><?php _e('Rate on WordPress.org', 'wp-clean-admin'); ?></a></p>
            </div>
        </div>

        <?php
    }

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
     * Sanitize plugin settings before saving
     */
    public function sanitize_settings($input) {
        $output = array();
        
        // Validate and sanitize menu settings
        $output['menu_toggle'] = isset($input['menu_toggle']) ? (int)$input['menu_toggle'] : 0;
        
        // Validate menu toggle states
        $output['menu_toggles'] = array();
        if (isset($input['menu_toggles']) && is_array($input['menu_toggles'])) {
            foreach ($input['menu_toggles'] as $slug => $value) {
                $output['menu_toggles'][sanitize_text_field($slug)] = (int)$value;
            }
        }
        
        // Validate menu order
        $output['menu_order'] = array();
        if (isset($input['menu_order']) && is_array($input['menu_order'])) {
            $output['menu_order'] = array_map('sanitize_text_field', $input['menu_order']);
        }
        
        // Validate submenu order
        $output['submenu_order'] = array();
        if (isset($input['submenu_order']) && is_array($input['submenu_order'])) {
            $output['submenu_order'] = array_map('sanitize_text_field', $input['submenu_order']);
        }
        
        // Sanitize other settings
        $output['current_tab'] = isset($input['current_tab']) ? sanitize_text_field($input['current_tab']) : 'tab-general';
        $output['hide_dashboard_widgets'] = isset($input['hide_dashboard_widgets']) ? 
            array_map('sanitize_text_field', $input['hide_dashboard_widgets']) : 
            array();
        $output['theme_style'] = isset($input['theme_style']) ? sanitize_text_field($input['theme_style']) : 'default';
        $output['hide_admin_menu_items'] = isset($input['hide_admin_menu_items']) ? 
            array_map('sanitize_text_field', $input['hide_admin_menu_items']) : 
            array();
        $output['hide_admin_bar_items'] = isset($input['hide_admin_bar_items']) ? 
            array_map('sanitize_text_field', $input['hide_admin_bar_items']) : 
            array();
        $output['layout_density'] = isset($input['layout_density']) ? sanitize_text_field($input['layout_density']) : 'standard';
        $output['border_radius_style'] = isset($input['border_radius_style']) ? sanitize_text_field($input['border_radius_style']) : 'small';
        $output['shadow_style'] = isset($input['shadow_style']) ? sanitize_text_field($input['shadow_style']) : 'subtle';
        $output['primary_color'] = isset($input['primary_color']) ? sanitize_text_field($input['primary_color']) : '#4A90E2';
        $output['background_color'] = isset($input['background_color']) ? sanitize_text_field($input['background_color']) : '#F8F9FA';
        $output['text_color'] = isset($input['text_color']) ? sanitize_text_field($input['text_color']) : '#2D3748';
        $output['font_stack'] = isset($input['font_stack']) ? sanitize_text_field($input['font_stack']) : 'system';
        $output['font_size_base'] = isset($input['font_size_base']) ? sanitize_text_field($input['font_size_base']) : 'medium';
        $output['icon_style'] = isset($input['icon_style']) ? sanitize_text_field($input['icon_style']) : 'dashicons';
        
        return $output;
    }

    /**
     * Render the options page.
     */
    public function options_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <div class="wpca-tabs">
                <div class="wpca-tab active" data-tab="tab-general"><?php _e('General', 'wp-clean-admin'); ?></div>
                <div class="wpca-tab" data-tab="tab-visual-style"><?php _e('Visual Style', 'wp-clean-admin'); ?></div>
                <div class="wpca-tab" data-tab="tab-menu"><?php _e('Menu Customization', 'wp-clean-admin'); ?></div>
                <div class="wpca-tab" data-tab="tab-about"><?php _e('About', 'wp-clean-admin'); ?></div>
            </div>
            
            <form action="options.php" method="post" id="wpca-settings-form">
                <input type="hidden" id="wpca-current-tab" name="wpca_settings[current_tab]" value="<?php echo esc_attr($this->options['current_tab'] ?? 'tab-general'); ?>">
                <script>
                jQuery(document).ready(function($) {
                    // Update current tab before form submission
                    $('#wpca-settings-form').on('submit', function() {
                        var activeTab = $('.wpca-tab.active').data('tab');
                        $('#wpca-current-tab').val(activeTab);
                    });
                });
                </script>
                <?php
                settings_fields( 'wpca_settings' );
                
                // General tab content
                $active_tab = $this->options['current_tab'] ?? 'tab-general';
                echo '<div id="tab-general" class="wpca-tab-content ' . ($active_tab === 'tab-general' ? 'active' : '') . '">';
                do_settings_sections( 'wpca_settings' );
                echo '</div>';
                
                // Menu Customization tab content
                echo '<div id="tab-menu" class="wpca-tab-content ' . ($active_tab === 'tab-menu' ? 'active' : '') . '">';
                do_settings_sections('wpca_settings_menu');
                echo '</div>';
                

                
                // Visual Style tab content
                echo '<div id="tab-visual-style" class="wpca-tab-content ' . ($active_tab === 'tab-visual-style' ? 'active' : '') . '">';
                do_settings_sections('wpca_settings_visual_style');
                echo '</div>';
                

                
                // About tab content
                echo '<div id="tab-about" class="wpca-tab-content ' . ($active_tab === 'tab-about' ? 'active' : '') . '">';
                $this->render_about_tab();
                echo '</div>';
                
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}