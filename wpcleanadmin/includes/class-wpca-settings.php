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
        add_action( 'wp_ajax_wpca_update_menu_visibility', array( $this, 'ajax_update_menu_visibility' ) );
        $this->options = self::get_options(); // Load options with defaults
    }

    /**
     * Handle AJAX request to update menu visibility
     */
    public function ajax_update_menu_visibility() {
        check_ajax_referer('wpca_ajax_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $menu_slug = sanitize_text_field($_POST['menu_slug']);
        $is_hidden = (bool)$_POST['is_hidden'];

        $options = get_option('wpca_settings');
        $hidden_items = isset($options['menu_hidden_items']) ? $options['menu_hidden_items'] : array();

        if ($is_hidden) {
            if (!in_array($menu_slug, $hidden_items)) {
                $hidden_items[] = $menu_slug;
            }
        } else {
            $hidden_items = array_diff($hidden_items, array($menu_slug));
        }

        $options['menu_hidden_items'] = array_values($hidden_items);
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
        
        // Enqueue CSS files for the settings page
        wp_enqueue_style( 'wpca-admin-style', WPCA_PLUGIN_URL . 'assets/css/wp-clean-admin.css', array(), WPCA_VERSION );
        wp_enqueue_style( 'wpca-menu-toggle-style', WPCA_PLUGIN_URL . 'assets/css/menu-toggle.css', array(), WPCA_VERSION );
        
        // Enqueue custom script for the settings page
        wp_enqueue_script( 'wpca-settings-script', WPCA_PLUGIN_URL . 'assets/js/wpca-settings.js', array( 'jquery', 'jquery-ui-sortable' ), WPCA_VERSION, true );
        
        // Localize script with AJAX URL, nonce and translations
        wp_localize_script( 'wpca-settings-script', 'wpca_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpca_ajax_nonce'),
            'hiddenText' => __('Hidden', 'wp-clean-admin')
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
            'hide_dashboard_widgets' => array(),
            'theme_style'            => 'default',
            'hide_admin_menu_items'  => array(),
            'hide_admin_bar_items'   => array(),
            'menu_order'            => array(),    // New: Menu order settings
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
        register_setting( 'wpca_settings', 'wpca_settings' );

        // Main section
        add_settings_section(
            'wpca_settings_general_section',
            __( 'General Settings', 'wp-clean-admin' ),
            array( $this, 'settings_section_callback' ),
            'wpca_settings'
        );

        // About section
        add_settings_section(
            'wpca_settings_about_section', 
            __( 'About', 'wp-clean-admin' ),
            array( $this, 'about_section_callback' ),
            'wpca_settings_about'
        );

        // Example setting field: Hide Dashboard Widgets
        add_settings_field(
            'wpca_hide_dashboard_widgets',
            __( 'Hide Dashboard Widgets', 'wp-clean-admin' ),
            array( $this, 'hide_dashboard_widgets_render' ),
            'wpca_settings',
            'wpca_settings_general_section'
        );

        // Visual Style section (now includes Layout & Typography)
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

        // Layout & Typography fields (now under Visual Style)
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
        // Hide Admin Menu Items functionality has been moved to menu_order_render()

        add_settings_field(
            'wpca_menu_order',
            __( 'Menu Order', 'wp-clean-admin' ),
            array( $this, 'menu_order_render' ),
            'wpca_settings_menu',
            'wpca_settings_menu_section'
        );

        // Admin Bar Customization field - moved to General Settings
        add_settings_field(
            'wpca_hide_admin_bar_items',
            __( 'Hide Admin Bar Items', 'wp-clean-admin' ),
            array( $this, 'hide_admin_bar_items_render' ),
            'wpca_settings',
            'wpca_settings_general_section'
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
        echo __( 'Customize the overall visual theme and colors.', 'wp-clean-admin' );
    }

    /**
     * Layout & Typography section callback.
     */
    public function layout_typography_section_callback() {
        echo __( 'Adjust layout density, element rounding, and font styles.', 'wp-clean-admin' );
    }

    /**
     * Menu section callback.
     */
    public function menu_section_callback() {
        // Description removed as hide functionality is now integrated with menu ordering
    }



    /**
     * About section callback.
     */
    public function about_section_callback() {
        ?>
        <div class="wpca-about-section">
            <h3><?php _e('WP Clean Admin', 'wp-clean-admin'); ?> v<?php echo WPCA_VERSION; ?></h3>
            <p><?php _e('A plugin to clean up and customize the WordPress admin interface.', 'wp-clean-admin'); ?></p>
            
            <h4><?php _e('Features', 'wp-clean-admin'); ?></h4>
            <ul>
                <li><?php _e('Customize admin menu appearance and order', 'wp-clean-admin'); ?></li>
                <li><?php _e('Hide unnecessary dashboard widgets', 'wp-clean-admin'); ?></li>
                <li><?php _e('Customize admin bar items', 'wp-clean-admin'); ?></li>
                <li><?php _e('Visual style customization', 'wp-clean-admin'); ?></li>
            </ul>

            <h4><?php _e('Support', 'wp-clean-admin'); ?></h4>
            <p><?php _e('For support or feature requests, please visit our website.', 'wp-clean-admin'); ?></p>
        </div>
        <?php
    }

    /**
     * Render Hide Dashboard Widgets field.
     */
    public function hide_dashboard_widgets_render() {
        $options = $this->options; // Use loaded options
        $widgets_to_hide = $options['hide_dashboard_widgets'] ?? array();
        
        // Default core widgets
        $core_widgets = array(
            'dashboard_right_now' => __('Right Now', 'wp-clean-admin'),
            'dashboard_activity' => __('Activity', 'wp-clean-admin'),
            'dashboard_quick_press' => __('Quick Draft', 'wp-clean-admin'),
            'dashboard_primary' => __('WordPress Events and News', 'wp-clean-admin'),
            'dashboard_site_health' => __('Site Health Status', 'wp-clean-admin'),
            'dashboard_at_glance' => __('At a Glance', 'wp-clean-admin')
        );
        
        // Get third-party widgets
        global $wp_meta_boxes;
        $third_party_widgets = array();
        
        if (isset($wp_meta_boxes['dashboard'])) {
            foreach ($wp_meta_boxes['dashboard'] as $context => $priority) {
                foreach ($priority as $widgets) {
                    foreach ($widgets as $widget_id => $widget) {
                        // Skip core widgets we already have
                        if (!isset($core_widgets[$widget_id])) {
                            $third_party_widgets[$widget_id] = isset($widget['title']) ? $widget['title'] : $widget_id;
                        }
                    }
                }
            }
        }
        ?>
        <fieldset>
            <h4><?php _e('Core Widgets', 'wp-clean-admin'); ?></h4>
            <?php foreach ($core_widgets as $widget_id => $title): ?>
                <label>
                    <input type="checkbox" name="wpca_settings[hide_dashboard_widgets][]" 
                           value="<?php echo esc_attr($widget_id); ?>" 
                           <?php checked(in_array($widget_id, $widgets_to_hide)); ?>>
                    <?php echo esc_html($title); ?>
                </label><br>
            <?php endforeach; ?>
            
            <?php if (!empty($third_party_widgets)): ?>
                <h4 style="margin-top:15px;"><?php _e('Third-party Widgets', 'wp-clean-admin'); ?></h4>
                <?php foreach ($third_party_widgets as $widget_id => $title): ?>
                    <label>
                        <input type="checkbox" name="wpca_settings[hide_dashboard_widgets][]" 
                               value="<?php echo esc_attr($widget_id); ?>" 
                               <?php checked(in_array($widget_id, $widgets_to_hide)); ?>>
                        <?php echo esc_html($title); ?>
                    </label><br>
                <?php endforeach; ?>
            <?php endif; ?>
        </fieldset>
        <p class="description">
            <?php _e('Check the widgets you want to hide from the dashboard.', 'wp-clean-admin'); ?>
        </p>
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

    // Hide Admin Menu Items functionality has been moved to menu_order_render()

    /**
     * Render Hide Admin Bar Items field.
     */
    /**
     * Render Menu Order field with nested submenu support
     */
    public function menu_order_render() {
        $options = $this->options;
        $menu_order = $options['menu_order'] ?? array();
        $submenu_order = $options['submenu_order'] ?? array();
        
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

        // Prepare hierarchical structure
        $hierarchical_items = [];
        foreach ($top_level_items as $slug => $item) {
            $hierarchical_items[$slug] = $item;
            if (isset($submenu_items[$slug])) {
                $hierarchical_items[$slug]['children'] = $submenu_items[$slug];
            }
        }
        ?>
        <div class="wpca-menu-order-wrapper">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <p class="description"><?php _e('Drag and drop to reorder menu items', 'wp-clean-admin'); ?></p>
                <button type="button" id="wpca-reset-menu-order" class="button button-secondary">
                    <?php _e('Reset to Default', 'wp-clean-admin'); ?>
                </button>
            </div>
            <div class="wpca-menu-container">
                <ul id="wpca-menu-order" class="wpca-menu-sortable">
                    <?php 
                    // Render hierarchical menu structure
                    $render_menu_items = function($items, $parent_slug = '') use (&$render_menu_items, $options) {
                        foreach ($items as $slug => $item) {
                            $is_submenu = !empty($item['parent']);
                            $level = $is_submenu ? 1 : 0;
                            
                            echo '<li class="menu-item" data-menu-slug="'.esc_attr($slug).'" data-item-type="'.($is_submenu ? 'sub' : 'top').'"';
                            echo $is_submenu ? ' data-parent-slug="'.esc_attr($item['parent']).'"' : '';
                            echo ' style="position: relative;">';
                            
                            // Menu item handle with flex layout
                            echo '<div class="menu-item-handle" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">';
                            echo '<div style="display: flex; align-items: center;">';
                            echo '<span class="dashicons '.($is_submenu ? 'dashicons-arrow-right' : 'dashicons-menu').'"></span>';
                            echo '<span class="menu-item-title">'.esc_html(wp_strip_all_tags($item['title'])).'</span>';
                            
                            // Toggle submenu button
                            if (isset($item['children']) && !empty($item['children'])) {
                                echo '<button class="toggle-submenu dashicons dashicons-arrow-down" style="margin-left: 5px;"></button>';
                            }
                            echo '</div>';
                            
                            // Horizontal toggle switch (now inside menu-item-handle)
                            echo '<div class="wpca-horizontal-toggle" style="margin-left: auto;">';
                            echo '<input type="checkbox" id="toggle-'.esc_attr($slug).'" name="wpca_settings[menu_hidden_items][]" value="'.esc_attr($slug).'" ';
                            echo isset($options['menu_hidden_items']) && in_array($slug, $options['menu_hidden_items']) ? 'checked' : '';
                            echo ' style="display:none;">';
                            echo '<label for="toggle-'.esc_attr($slug).'" class="toggle-slider">';
                            echo '<span class="toggle-handle"></span>';
                            echo '</label>';
                            echo '</div>';
                            
                            echo '</div>';
                            
                            // Hidden input for order
                            echo '<input type="hidden" name="wpca_settings[menu_order][]" value="'.esc_attr($slug).'">';
                            
                            // Submenu items
                            if (isset($item['children']) && !empty($item['children'])) {
                                echo '<ul class="submenu-items wpca-submenu-sortable">';
                                $render_menu_items($item['children'], $slug);
                                echo '</ul>';
                            }
                            
                            echo '</li>';
                        }
                    };
                    
                    $render_menu_items($hierarchical_items);
                    ?>
                </ul>
            </div>
            
            <input type="hidden" id="wpca_menu_order" name="wpca_settings[menu_order]" value="">
            <input type="hidden" id="wpca_submenu_order" name="wpca_settings[submenu_order]" value="">
            
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Initialize sortable for main menu
                $('.wpca-menu-sortable').sortable({
                    handle: '.menu-item-handle',
                    placeholder: 'menu-item-placeholder',
                    update: function() {
                        saveMenuOrder();
                    }
                });
                
                // Initialize sortable for submenus
                $('.wpca-submenu-sortable').sortable({
                    handle: '.menu-item-handle',
                    connectWith: '.wpca-submenu-sortable',
                    placeholder: 'submenu-item-placeholder',
                    update: function() {
                        saveMenuOrder();
                    }
                });
                
                // Toggle submenu visibility
                $(document).on('click', '.toggle-submenu', function(e) {
                    e.preventDefault();
                    $(this).toggleClass('dashicons-arrow-down dashicons-arrow-right')
                           .closest('.menu-item').find('.submenu-items').toggleClass('expanded');
                });
                
                // Toggle menu item visibility with slider and send AJAX request
                $(document).on('click', '.toggle-slider', function(e) {
                    var checkbox = $(this).siblings('input[type="checkbox"]');
                    var isHidden = !checkbox.prop('checked'); // Get new state
                    checkbox.prop('checked', isHidden);
                    $(this).toggleClass('active', !isHidden);
                    
                    // Update menu item UI
                    var menuItem = $(this).closest('.menu-item');
                    menuItem.toggleClass('menu-item-hidden', isHidden);
                    
                    // Update hidden indicator
                    if (isHidden) {
                        menuItem.find('.hidden-indicator').remove();
                        menuItem.find('.menu-item-title').after(
                            '<span class="hidden-indicator">(' + wpca_ajax.hiddenText + ')</span>'
                        );
                    } else {
                        menuItem.find('.hidden-indicator').remove();
                    }
                    
                    // Send AJAX request to update menu state immediately
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wpca_update_menu_visibility',
                            menu_slug: menuItem.data('menu-slug'),
                            is_hidden: isHidden,
                            security: wpca_ajax.nonce
                        },
                        success: function(response) {
                            if (!response.success) {
                                // Revert if failed
                                checkbox.prop('checked', !isHidden);
                                $(this).toggleClass('active', isHidden);
                                menuItem.toggleClass('menu-item-hidden', !isHidden);
                                alert('Failed to update menu visibility');
                            }
                        }
                    });
                });
                
                // Initialize toggle states - ensure consistency between UI and actual state
                $('.wpca-horizontal-toggle input[type="checkbox"]').each(function() {
                    var isChecked = $(this).prop('checked');
                    // Update toggle slider UI
                    $(this).siblings('.toggle-slider').toggleClass('active', !isChecked);
                    // Update menu item visibility class
                    $(this).closest('.menu-item').toggleClass('menu-item-hidden', isChecked);
                    // If menu item is hidden, add visual indicator
                    if (isChecked) {
                        $(this).closest('.menu-item').find('.menu-item-title').after(
                            '<span class="hidden-indicator">(' + wpca_ajax.hiddenText + ')</span>'
                        );
                    }
                });
                
                // Save menu order function
                function saveMenuOrder() {
                    var menuOrder = [];
                    var submenuOrder = {};
                    
                    $('.wpca-menu-sortable > .menu-item').each(function() {
                        var slug = $(this).data('menu-slug');
                        menuOrder.push(slug);
                        
                        // Get submenu order if exists
                        var submenuItems = $(this).find('.submenu-items > .menu-item');
                        if (submenuItems.length > 0) {
                            submenuOrder[slug] = [];
                            submenuItems.each(function() {
                                submenuOrder[slug].push($(this).data('menu-slug'));
                            });
                        }
                    });
                    
                    $('#wpca_menu_order').val(JSON.stringify(menuOrder));
                    $('#wpca_submenu_order').val(JSON.stringify(submenuOrder));
                }
                
                // Initialize with current order
                saveMenuOrder();
                
                // Reset button functionality
                $('#wpca-reset-menu-order').on('click', function() {
                    if (confirm('确定要重置菜单顺序和可见性设置吗？')) {
                        // Reset checkboxes
                        $('.wpca-horizontal-toggle input[type="checkbox"]').prop('checked', false);
                        $('.toggle-slider').removeClass('active');
                        $('.menu-item').removeClass('menu-item-hidden');
                        
                        // TODO: Reset menu order (would require server-side handling)
                        // For now, just reload the page
                        location.reload();
                    }
                });
            });
            </script>
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
     * Render the options page.
     */
    public function options_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <div class="wpca-tabs">
                <div class="wpca-tab active" data-tab="tab-general"><?php _e('General Settings', 'wp-clean-admin'); ?></div>
                <div class="wpca-tab" data-tab="tab-visual-style"><?php _e('Visual Style', 'wp-clean-admin'); ?></div>
                <div class="wpca-tab" data-tab="tab-menu"><?php _e('Menu Customization', 'wp-clean-admin'); ?></div>
                <div class="wpca-tab" data-tab="tab-about"><?php _e('About', 'wp-clean-admin'); ?></div>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields( 'wpca_settings' );
                
                // General tab content
                echo '<div id="tab-general" class="wpca-tab-content active">';
                do_settings_sections( 'wpca_settings' );
                echo '</div>';
                
                // Menu Customization tab content
                echo '<div id="tab-menu" class="wpca-tab-content">';
                do_settings_sections('wpca_settings_menu');
                echo '</div>';
                

                
                // Visual Style tab content
                echo '<div id="tab-visual-style" class="wpca-tab-content">';
                do_settings_sections('wpca_settings_visual_style');
                echo '</div>';
                
                // Layout & Typography tab content
                echo '<div id="tab-layout" class="wpca-tab-content">';
                do_settings_sections('wpca_settings_layout');
                echo '</div>';

                // About tab content
                echo '<div id="tab-about" class="wpca-tab-content">';
                do_settings_sections('wpca_settings_about');
                echo '</div>';
                
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}