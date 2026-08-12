<?php
/**
 * WPCleanAdmin Menu Customizer Class
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

require_once __DIR__ . '/class-wpca-menu-customizer-options.php';
require_once __DIR__ . '/class-wpca-menu-customizer-tree.php';
require_once __DIR__ . '/class-wpca-menu-customizer-render.php';

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Declare WordPress functions for IDE compatibility
if ( ! function_exists( 'wpca_get_settings' ) ) {
    function wpca_get_settings() {}
}
if ( ! function_exists( 'add_action' ) ) {
    function add_action() {}
}
if ( ! function_exists( 'add_filter' ) ) {
    function add_filter() {}
}
if ( ! function_exists( 'get_option' ) ) {
    function get_option() {}
}
if ( ! function_exists( 'update_option' ) ) {
    function update_option() {}
}
if ( ! function_exists( 'delete_option' ) ) {
    function delete_option() {}
}

/**
 * Menu_Customizer class
 */
class Menu_Customizer {

    /**
     * Singleton instance
     *
     * @var Menu_Customizer
     */
    private static $instance = null;

    /**
     * 设置操作处理器
     *
     * @var Menu_Customizer_Options
     */
    private $options;

    /**
     * 菜单结构构建处理器
     *
     * @var Menu_Customizer_Tree
     */
    private $tree;

    /**
     * 菜单渲染/导出处理器
     *
     * @var Menu_Customizer_Render
     */
    private $render;

    /**
     * Get singleton instance
     *
     * @return Menu_Customizer
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
        $this->options = new Menu_Customizer_Options();
        $this->tree    = new Menu_Customizer_Tree();
        $this->render  = new Menu_Customizer_Render();
        $this->init();
    }

    /**
     * Initialize the menu customizer module
     */
    public function init() {
        // Load settings
        $settings = \wpca_get_settings();

        if ( isset( $settings['menu_customizer'] ) && function_exists( 'add_action' ) ) {
            // Add admin menu customization
            \add_action( 'admin_menu', array( $this, 'customize_admin_menu' ), 999 );
            \add_action( 'admin_bar_menu', array( $this, 'customize_admin_bar' ), 999 );

            // Add AJAX handlers
            \add_action( 'wp_ajax_wpca_save_menu_customizer', array( $this, 'save_settings' ) );
            \add_action( 'wp_ajax_wpca_reset_menu_customizer', array( $this, 'reset_settings' ) );

            // Export/Import handlers
            \add_action( 'wp_ajax_wpca_export_menu_customizer', array( $this, 'export_settings' ) );
            \add_action( 'wp_ajax_wpca_import_menu_customizer', array( $this, 'import_settings' ) );
        }
    }

    /**
     * Customize admin menu (delegated)
     */
    public function customize_admin_menu(): void {
        $this->tree->customize_admin_menu();
    }

    /**
     * Customize admin bar (delegated)
     */
    public function customize_admin_bar(): void {
        $this->render->customize_admin_bar();
    }

    /**
     * Get menu customizer settings (delegated)
     *
     * @return array
     */
    public function get_settings(): array {
        return $this->options->get_settings();
    }

    /**
     * Save menu customizer settings (delegated)
     *
     * @param array $settings
     * @return bool
     */
    public function save_settings( array $settings ): bool {
        return $this->options->save_settings( $settings );
    }

    /**
     * Reset menu customizer settings (delegated)
     *
     * @return bool
     */
    public function reset_settings(): bool {
        return $this->options->reset_settings();
    }

    /**
     * Create menu group (delegated)
     *
     * @param string $group_id
     * @param string $group_name
     * @param array  $menu_items
     * @return bool
     */
    public function create_menu_group( $group_id, $group_name, $menu_items = array() ): bool {
        return $this->options->create_menu_group( $group_id, $group_name, $menu_items );
    }

    /**
     * Delete menu group (delegated)
     *
     * @param string $group_id
     * @return bool
     */
    public function delete_menu_group( $group_id ): bool {
        return $this->options->delete_menu_group( $group_id );
    }

    /**
     * Update menu group (delegated)
     *
     * @param string $group_id
     * @param array  $group_settings
     * @return bool
     */
    public function update_menu_group( $group_id, $group_settings ): bool {
        return $this->options->update_menu_group( $group_id, $group_settings );
    }

    /**
     * Get menu group settings (delegated)
     *
     * @return array
     */
    public function get_menu_groups(): array {
        return $this->options->get_menu_groups();
    }

    /**
     * Get admin menu structure (delegated)
     *
     * @return array
     */
    public function get_admin_menu_structure(): array {
        return $this->render->get_admin_menu_structure();
    }

    /**
     * Get admin bar structure (delegated)
     *
     * @return array
     */
    public function get_admin_bar_structure(): array {
        return $this->render->get_admin_bar_structure();
    }

    /**
     * Export menu customizer settings (delegated)
     *
     * @return array
     */
    public function export_settings(): array {
        return $this->render->export_settings();
    }

    /**
     * Import menu customizer settings (delegated)
     *
     * @param array $import_data
     * @return bool
     */
    public function import_settings( array $import_data ): bool {
        return $this->render->import_settings( $import_data );
    }

    /**
     * Apply menu order (delegated)
     *
     * @param array $menu
     * @param array $menu_order
     */
    public function apply_menu_order( array &$menu, array $menu_order ): void {
        $this->tree->apply_menu_order( $menu, $menu_order );
    }

    /**
     * Apply menu groups (delegated)
     *
     * @param array $menu
     * @param array $menu_groups
     */
    public function apply_menu_groups( array &$menu, array $menu_groups ): void {
        $this->tree->apply_menu_groups( $menu, $menu_groups );
    }
}
