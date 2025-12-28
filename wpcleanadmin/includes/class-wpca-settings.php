<?php
/**
 * WPCleanAdmin Settings Class
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 *
 * @noinspection PhpUndefinedFunctionInspection WordPress functions are available in WP environment
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
 *
 * @noinspection PhpUndefinedFunctionInspection WordPress functions are available in WP environment
 */
class Settings {
    
    /**
     * Singleton instance
     *
     * @var Settings
     */
    private static ?Settings $instance = null;
    
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
     *
     * @uses \add_menu_page() To add main settings page to admin menu
     * @uses \add_submenu_page() To add submenu pages to admin menu
     * @uses \esc_html() To escape HTML output
     * @uses \__() To translate strings
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
        
        \add_settings_field(
            'wpca_two_factor_auth',
            \__( 'Enable Two-Factor Authentication', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_two_factor_auth_field' ),
            'wp-clean-admin',
            'wpca_security_settings'
        );
        
        // Register role-based menu settings section
        \add_settings_section(
            'wpca_role_menu_settings',
            \__( 'Role-Based Menu Settings', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_role_menu_settings_section' ),
            'wp-clean-admin'
        );
        
        // Register role-based menu settings fields
        \add_settings_field(
            'wpca_role_based_restrictions',
            \__( 'Enable Role-Based Menu Restrictions', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_role_based_restrictions_field' ),
            'wp-clean-admin',
            'wpca_role_menu_settings'
        );
        
        \add_settings_field(
            'wpca_role_menu_restrictions',
            \__( 'Role Menu Restrictions', WPCA_TEXT_DOMAIN ),
            array( $this, 'render_role_menu_restrictions_field' ),
            'wp-clean-admin',
            'wpca_role_menu_settings'
        );
        
        // Register settings
        \register_setting( 'wp-clean-admin', 'wpca_settings', array( $this, 'validate_settings' ) );
    }
    
    /**
     * Render general settings section
     */
    public function render_general_settings_section(): void {
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
    public function render_cleanup_settings_section(): void {
        echo '<p>' . \__( 'Configure cleanup settings for WP Clean Admin plugin.', WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    /**
     * Render remove dashboard widgets field
     */
    public function render_remove_dashboard_widgets_field(): void {
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
    public function render_performance_settings_section(): void {
        echo '<p>' . \__( 'Configure performance optimization settings for WP Clean Admin plugin.', WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    /**
     * Render optimize database field
     */
    public function render_optimize_database_field(): void {
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
    public function render_security_settings_section(): void {
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
     * Render two-factor authentication field
     */
    public function render_two_factor_auth_field() {
        $settings = \get_option( 'wpca_settings', array() );
        $two_factor_auth = isset( $settings['security']['two_factor_auth'] ) ? $settings['security']['two_factor_auth'] : 0;
        
        echo '<input type="checkbox" name="wpca_settings[security][two_factor_auth]" value="1" ' . \checked( $two_factor_auth, 1, false ) . ' />';
        echo '<label for="wpca_two_factor_auth"> ' . \__( 'Enable two-factor authentication for all users.', WPCA_TEXT_DOMAIN ) . '</label>';
        echo '<p class="description">' . \__( 'When enabled, users will be required to enter a 6-digit code from their authenticator app during login.', WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    /**
     * Render role menu settings section
     */
    public function render_role_menu_settings_section() {
        echo '<p>' . \__( 'Configure role-based menu restrictions for WP Clean Admin plugin.', WPCA_TEXT_DOMAIN ) . '</p>';
    }
    
    /**
     * Render role-based restrictions field
     */
    public function render_role_based_restrictions_field() {
        $settings = \get_option( 'wpca_settings', array() );
        $role_based_restrictions = isset( $settings['menu']['role_based_restrictions'] ) ? $settings['menu']['role_based_restrictions'] : 0;
        
        echo '<input type="checkbox" name="wpca_settings[menu][role_based_restrictions]" value="1" ' . \checked( $role_based_restrictions, 1, false ) . ' />';
        echo '<label for="wpca_role_based_restrictions"> ' . \__( 'Enable role-based menu restrictions for different user roles.', WPCA_TEXT_DOMAIN ) . '</label>';
    }
    
    /**
     * Render role menu restrictions field
     *
     * @uses \get_option() To retrieve saved settings
     * @uses \wp_roles() To get all user roles
     * @uses \esc_html() To escape HTML output
     * @uses \esc_attr() To escape HTML attributes
     * @uses \checked() To output checked attribute for checkboxes
     * @uses \__() To translate strings
     * @uses Menu_Manager::get_menu_items() To retrieve all menu items
     */
    public function render_role_menu_restrictions_field() {
        $settings = \get_option( 'wpca_settings', array() );
        $role_restrictions = isset( $settings['menu']['role_menu_restrictions'] ) ? $settings['menu']['role_menu_restrictions'] : array();
        
        // Get all user roles
        $roles = function_exists( 'wp_roles' ) ? \wp_roles()->get_names() : array();
        
        // Get all menu items
        $menu_manager = Menu_Manager::getInstance();
        $menu_items = $menu_manager->get_menu_items();
        
        echo '<div class="wpca-role-menu-restrictions">';
        
        foreach ( $roles as $role_slug => $role_name ) {
            echo '<h4>' . \esc_html( $role_name ) . '</h4>';
            echo '<table class="form-table">';
            
            // Get role-specific menu restrictions
            $role_menu_restrictions = isset( $role_restrictions[$role_slug]['menu_items'] ) ? $role_restrictions[$role_slug]['menu_items'] : array();
            
            // Render top-level menu items
            foreach ( $menu_items as $menu_item ) {
                $menu_slug = $menu_item['slug'];
                $is_checked = isset( $role_menu_restrictions[$menu_slug] ) ? $role_menu_restrictions[$menu_slug] : 0;
                
                echo '<tr>';
                echo '<th scope="row">' . \esc_html( $menu_item['title'] ) . '</th>';
                echo '<td>';
                echo '<input type="checkbox" name="wpca_settings[menu][role_menu_restrictions][' . \esc_attr( $role_slug ) . '][menu_items][' . \esc_attr( $menu_slug ) . ']" value="1" ' . \checked( $is_checked, 1, false ) . ' />';
                echo '<label> ' . \__( 'Hide this menu item for', WPCA_TEXT_DOMAIN ) . ' ' . \esc_html( $role_name ) . '</label>';
                echo '</td>';
                echo '</tr>';
                
                // Render submenu items
                if ( ! empty( $menu_item['submenu'] ) ) {
                    foreach ( $menu_item['submenu'] as $submenu_item ) {
                        $submenu_slug = $submenu_item['slug'];
                        $submenu_is_checked = isset( $role_menu_restrictions[$submenu_slug] ) ? $role_menu_restrictions[$submenu_slug] : 0;
                        
                        echo '<tr>';
                        echo '<th scope="row" style="padding-left: 20px;">└── ' . \esc_html( $submenu_item['title'] ) . '</th>';
                        echo '<td>';
                        echo '<input type="checkbox" name="wpca_settings[menu][role_menu_restrictions][' . \esc_attr( $role_slug ) . '][menu_items][' . \esc_attr( $submenu_slug ) . ']" value="1" ' . \checked( $submenu_is_checked, 1, false ) . ' />';
                        echo '<label> ' . \__( 'Hide this submenu item for', WPCA_TEXT_DOMAIN ) . ' ' . \esc_html( $role_name ) . '</label>';
                        echo '</td>';
                        echo '</tr>';
                    }
                }
            }
            
            echo '</table>';
        }
        
        echo '</div>';
    }
    
    /**
     * Render settings page
     *
     * @uses \esc_html() To escape HTML output
     * @uses \esc_attr() To escape HTML attributes
     * @uses \get_admin_page_title() To get the current admin page title
     * @uses \settings_fields() To render nonce fields
     * @uses \do_settings_sections() To render all settings sections
     * @uses \submit_button() To render submit button
     * @uses \__() To translate strings
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo \esc_html( \get_admin_page_title() ); ?></h1>
            
            <!-- Settings Search -->
            <div class="wpca-settings-search">
                <input type="text" id="wpca-settings-search" placeholder="<?php echo \esc_attr( \__( 'Search settings...', WPCA_TEXT_DOMAIN ) ); ?>" />
                <button type="button" class="button" id="wpca-clear-search"><?php echo \esc_html( \__( 'Clear', WPCA_TEXT_DOMAIN ) ); ?></button>
            </div>
            
            <form method="post" action="options.php">
                <?php
                // Output settings fields
                \settings_fields( 'wp-clean-admin' );
                \do_settings_sections( 'wp-clean-admin' );
                \submit_button();
                ?>
            </form>
        </div>
        
        <script type="text/javascript">
        (function($) {
            'use strict';
            
            $(document).ready(function() {
                // Settings search functionality
                const searchInput = $('#wpca-settings-search');
                const clearButton = $('#wpca-clear-search');
                const settingSections = $('.wpca-settings-section');
                const settingFields = $('.form-table tr');
                
                // Add class to sections for styling and selection
                $('.settings-section').addClass('wpca-settings-section');
                
                // Search functionality
                searchInput.on('input', function() {
                    const searchTerm = $(this).val().toLowerCase();
                    
                    // Show/hide sections based on search term
                    $('.wpca-settings-section').each(function() {
                        const sectionTitle = $(this).find('h3').text().toLowerCase();
                        const sectionContent = $(this).nextUntil('.wpca-settings-section').text().toLowerCase();
                        const matchesTitle = sectionTitle.includes(searchTerm);
                        const matchesContent = sectionContent.includes(searchTerm);
                        
                        if (matchesTitle || matchesContent) {
                            $(this).show();
                            $(this).nextUntil('.wpca-settings-section').show();
                        } else {
                            $(this).hide();
                            $(this).nextUntil('.wpca-settings-section').hide();
                        }
                    });
                    
                    // Show/hide individual setting fields
                    settingFields.each(function() {
                        const fieldText = $(this).text().toLowerCase();
                        if (fieldText.includes(searchTerm)) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                    
                    // Show clear button if search term is not empty
                    if (searchTerm) {
                        clearButton.show();
                    } else {
                        clearButton.hide();
                    }
                });
                
                // Clear search functionality
                clearButton.on('click', function() {
                    searchInput.val('');
                    settingSections.show();
                    settingFields.show();
                    clearButton.hide();
                });
                
                // Hide clear button initially
                clearButton.hide();
            });
        })(jQuery);
        </script>
        
        <style type="text/css">
            .wpca-settings-search {
                margin: 15px 0;
                padding: 10px;
                background: #f1f1f1;
                border-radius: 4px;
                display: flex;
                gap: 10px;
            }
            
            #wpca-settings-search {
                flex: 1;
                padding: 8px 12px;
                font-size: 14px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            
            #wpca-clear-search {
                white-space: nowrap;
            }
            
            .wpca-settings-section {
                margin-bottom: 20px;
                padding: 15px;
                background: #fff;
                border: 1px solid #e1e1e1;
                border-radius: 4px;
            }
            
            .wpca-role-menu-restrictions {
                max-height: 500px;
                overflow-y: auto;
                padding: 10px;
                background: #f9f9f9;
                border: 1px solid #e1e1e1;
                border-radius: 4px;
            }
            
            .wpca-role-menu-restrictions h4 {
                margin: 15px 0 10px;
                padding: 5px 10px;
                background: #e1e1e1;
                border-radius: 3px;
            }
        </style>
        <?php
    }
    
    /**
     * Validate settings
     *
     * @param array $input Raw input settings from form submission
     * @return array Validated and sanitized settings
     * @uses sanitize_text_field() To sanitize text inputs
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
            
            // Validate role-based restrictions setting
            $validated['menu']['role_based_restrictions'] = isset( $input['menu']['role_based_restrictions'] ) ? 1 : 0;
            
            // Validate role menu restrictions
            if ( isset( $input['menu']['role_menu_restrictions'] ) && is_array( $input['menu']['role_menu_restrictions'] ) ) {
                $validated['menu']['role_menu_restrictions'] = array();
                
                foreach ( $input['menu']['role_menu_restrictions'] as $role_slug => $restrictions ) {
                    // Sanitize role slug
                    $sanitized_role = sanitize_text_field( $role_slug );
                    
                    if ( ! empty( $sanitized_role ) ) {
                        $validated['menu']['role_menu_restrictions'][$sanitized_role] = array();
                        
                        // Validate menu items
                        if ( isset( $restrictions['menu_items'] ) && is_array( $restrictions['menu_items'] ) ) {
                            $validated['menu']['role_menu_restrictions'][$sanitized_role]['menu_items'] = array();
                            
                            foreach ( $restrictions['menu_items'] as $menu_slug => $hide ) {
                                $sanitized_menu = sanitize_text_field( $menu_slug );
                                $validated['menu']['role_menu_restrictions'][$sanitized_role]['menu_items'][$sanitized_menu] = ( $hide == 1 ) ? 1 : 0;
                            }
                        }
                    }
                }
            }
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
            
            // Validate two-factor authentication setting
            $validated['security']['two_factor_auth'] = isset( $input['security']['two_factor_auth'] ) ? 1 : 0;
        }
        
        return $validated;
    }
    
    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook
     * @uses \strpos() To check if current page is plugin page
     * @uses \wp_enqueue_style() To enqueue admin styles
     * @uses \wp_enqueue_script() To enqueue admin scripts
     * @uses \wp_localize_script() To localize script variables
     * @uses \admin_url() To get admin AJAX URL
     * @uses \wp_create_nonce() To create security nonce
     * @uses \__() To translate strings
     */
    public function enqueue_scripts( string $hook ): void {
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
