<?php
/**
 * 设置页面模块
 * 
 * @package WP_Clean_Admin
 */

// 确保直接访问时退出
if (!defined('ABSPATH')) {
    exit;
}

// 确保WordPress环境已加载
if (!function_exists('add_action')) {
    return;
}

class WP_Clean_Admin_Settings {
    private static $instance;
    
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    public function add_settings_page() {
        add_options_page(
            __('WP Clean Admin Settings', 'wp-clean-admin'),
            __('Clean Admin', 'wp-clean-admin'),
            'manage_options',
            'wp-clean-admin',
            [$this, 'render_settings_page']
        );
    }
    
    public function register_settings() {
        // 性能优化设置
        register_setting('wpca_performance', 'wpca_allowed_scripts', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_array'],
            'default' => ['jquery', 'wp-api', 'wp-util']
        ]);
        
        register_setting('wpca_performance', 'wpca_allowed_styles', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_array'],
            'default' => ['admin-bar', 'common']
        ]);
        
        // 菜单管理设置
        register_setting('wpca_menu', 'wpca_hidden_menus', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_array'],
            'default' => []
        ]);
        
        register_setting('wpca_menu', 'wpca_hidden_submenus', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_array'],
            'default' => []
        ]);
        
        register_setting('wpca_menu', 'wpca_menu_order', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_array'],
            'default' => []
        ]);
        
        // 外观设置
        register_setting('wpca_appearance', 'wpca_theme', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'default'
        ]);
        
        register_setting('wpca_appearance', 'wpca_layout_density', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'standard'
        ]);
    }
    
    public function sanitize_array($input) {
        return is_array($input) ? $input : [];
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // 获取当前活动的选项卡
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'performance';
        
        // 保存设置消息
        if (isset($_GET['settings-updated'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                esc_html__('Settings saved.', 'wp-clean-admin') . '</p></div>';
        }
        
        // 显示设置表单
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('WP Clean Admin Settings', 'wp-clean-admin'); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=wp-clean-admin&tab=performance" class="nav-tab <?php echo $active_tab == 'performance' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Performance', 'wp-clean-admin'); ?>
                </a>
                <a href="?page=wp-clean-admin&tab=menu" class="nav-tab <?php echo $active_tab == 'menu' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Menu Management', 'wp-clean-admin'); ?>
                </a>
                <a href="?page=wp-clean-admin&tab=appearance" class="nav-tab <?php echo $active_tab == 'appearance' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Appearance', 'wp-clean-admin'); ?>
                </a>
            </h2>
            
            <div class="tab-content">
                <?php
                if ($active_tab == 'performance') {
                    $this->render_performance_tab();
                } elseif ($active_tab == 'menu') {
                    $this->render_menu_tab();
                } elseif ($active_tab == 'appearance') {
                    $this->render_appearance_tab();
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    private function render_performance_tab() {
        $allowed_scripts = get_option('wpca_allowed_scripts', ['jquery', 'wp-api', 'wp-util']);
        $allowed_styles = get_option('wpca_allowed_styles', ['admin-bar', 'common']);
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('wpca_performance'); ?>
            <h2><?php echo esc_html__('Performance Optimization', 'wp-clean-admin'); ?></h2>
            <p><?php echo esc_html__('Control which scripts and styles are loaded in the admin area.', 'wp-clean-admin'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php echo esc_html__('Allowed Scripts', 'wp-clean-admin'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="wpca_allowed_scripts[]" value="jquery" 
                                    <?php checked(in_array('jquery', $allowed_scripts)); ?>> 
                                jQuery
                            </label><br>
                            <label>
                                <input type="checkbox" name="wpca_allowed_scripts[]" value="wp-api" 
                                    <?php checked(in_array('wp-api', $allowed_scripts)); ?>> 
                                WP REST API
                            </label><br>
                            <label>
                                <input type="checkbox" name="wpca_allowed_scripts[]" value="wp-util" 
                                    <?php checked(in_array('wp-util', $allowed_scripts)); ?>> 
                                WP Utils
                            </label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php echo esc_html__('Allowed Styles', 'wp-clean-admin'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="wpca_allowed_styles[]" value="admin-bar" 
                                    <?php checked(in_array('admin-bar', $allowed_styles)); ?>> 
                                Admin Bar
                            </label><br>
                            <label>
                                <input type="checkbox" name="wpca_allowed_styles[]" value="common" 
                                    <?php checked(in_array('common', $allowed_styles)); ?>> 
                                Common Styles
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }
    
    private function render_menu_tab() {
        global $menu, $submenu;
        $hidden_menus = get_option('wpca_hidden_menus', []);
        $hidden_submenus = get_option('wpca_hidden_submenus', []);
        $menu_order = get_option('wpca_menu_order', array_column($menu, 2));
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('wpca_menu'); ?>
            <h2><?php echo esc_html__('Menu Management', 'wp-clean-admin'); ?></h2>
            <p><?php echo esc_html__('Customize the admin menu by hiding items and changing their order.', 'wp-clean-admin'); ?></p>
            
            <h3><?php echo esc_html__('Menu Order', 'wp-clean-admin'); ?></h3>
            <div id="menu-sortable" class="wpca-menu-sortable">
                <?php
                // 按当前顺序或默认顺序显示菜单
                foreach ($menu_order as $slug) {
                    foreach ($menu as $item) {
                        if ($item[2] === $slug && !empty($item[0])) {
                            ?>
                            <div class="menu-item" data-slug="<?php echo esc_attr($slug); ?>">
                                <input type="hidden" name="wpca_menu_order[]" value="<?php echo esc_attr($slug); ?>">
                                <span class="dashicons dashicons-menu"></span>
                                <?php echo esc_html(strip_tags($item[0])); ?>
                            </div>
                            <?php
                            break;
                        }
                    }
                }
                ?>
            </div>
            <p class="description"><?php echo esc_html__('Drag to reorder menu items', 'wp-clean-admin'); ?></p>
            
            <h3><?php echo esc_html__('Hidden Main Menus', 'wp-clean-admin'); ?></h3>
            <div class="wpca-menu-visibility">
                <?php
                foreach ($menu as $menu_item) {
                    if (!empty($menu_item[0]) && !empty($menu_item[2])) {
                        $menu_name = strip_tags($menu_item[0]);
                        $menu_slug = $menu_item[2];
                        ?>
                        <label>
                            <input type="checkbox" name="wpca_hidden_menus[]" value="<?php echo esc_attr($menu_slug); ?>" 
                                <?php checked(in_array($menu_slug, $hidden_menus)); ?>> 
                            <?php echo esc_html($menu_name); ?>
                        </label><br>
                        <?php
                    }
                }
                ?>
            </div>
            
            <h3><?php echo esc_html__('Hidden Submenus', 'wp-clean-admin'); ?></h3>
            <div class="wpca-submenu-visibility">
                <?php
                foreach ($submenu as $parent_slug => $submenu_items) {
                    if (!empty($submenu_items)) {
                        echo '<div class="submenu-group">';
                        echo '<h4>' . esc_html($parent_slug) . '</h4>';
                        
                        foreach ($submenu_items as $submenu_item) {
                            if (!empty($submenu_item[0]) && !empty($submenu_item[2])) {
                                $submenu_name = strip_tags($submenu_item[0]);
                                $submenu_slug = $submenu_item[2];
                                $value = $parent_slug . '|' . $submenu_slug;
                                ?>
                                <label>
                                    <input type="checkbox" name="wpca_hidden_submenus[]" value="<?php echo esc_attr($value); ?>" 
                                        <?php checked(in_array($value, $hidden_submenus)); ?>> 
                                    <?php echo esc_html($submenu_name); ?>
                                </label><br>
                                <?php
                            }
                        }
                        
                        echo '</div>';
                    }
                }
                ?>
            </div>
            
            <?php submit_button(); ?>
        </form>
        
        <script>
        jQuery(document).ready(function($) {
            if (typeof $.fn.sortable !== "undefined") {
                $("#menu-sortable").sortable({
                    placeholder: "ui-state-highlight",
                    update: function(event, ui) {
                        // 更新隐藏字段的顺序
                        var newOrder = [];
                        $("#menu-sortable .menu-item").each(function() {
                            newOrder.push($(this).data("slug"));
                        });
                    }
                });
            } else {
                console.warn("jQuery UI Sortable is not available");
            }
        });
        </script>
        <?php
    }
    
    private function render_appearance_tab() {
        $theme = get_option('wpca_theme', 'default');
        $layout_density = get_option('wpca_layout_density', 'standard');
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('wpca_appearance'); ?>
            <h2><?php echo esc_html__('Appearance Settings', 'wp-clean-admin'); ?></h2>
            <p><?php echo esc_html__('Customize the look and feel of your admin area.', 'wp-clean-admin'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php echo esc_html__('Admin Theme', 'wp-clean-admin'); ?></th>
                    <td>
                        <select name="wpca_theme">
                            <option value="default" <?php selected($theme, 'default'); ?>><?php echo esc_html__('Default', 'wp-clean-admin'); ?></option>
                            <option value="light_blue_gray" <?php selected($theme, 'light_blue_gray'); ?>><?php echo esc_html__('Light Blue-Gray', 'wp-clean-admin'); ?></option>
                            <option value="mint" <?php selected($theme, 'mint'); ?>><?php echo esc_html__('Mint', 'wp-clean-admin'); ?></option>
                            <option value="dark" <?php selected($theme, 'dark'); ?>><?php echo esc_html__('Dark', 'wp-clean-admin'); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php echo esc_html__('Layout Density', 'wp-clean-admin'); ?></th>
                    <td>
                        <select name="wpca_layout_density">
                            <option value="compact" <?php selected($layout_density, 'compact'); ?>><?php echo esc_html__('Compact', 'wp-clean-admin'); ?></option>
                            <option value="standard" <?php selected($layout_density, 'standard'); ?>><?php echo esc_html__('Standard', 'wp-clean-admin'); ?></option>
                            <option value="spacious" <?php selected($layout_density, 'spacious'); ?>><?php echo esc_html__('Spacious', 'wp-clean-admin'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }
}

// 初始化设置页面模块
WP_Clean_Admin_Settings::get_instance();