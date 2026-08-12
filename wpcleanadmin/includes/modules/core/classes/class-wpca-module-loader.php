<?php
/**
 * WPCleanAdmin Module Loader
 *
 * 承载各模块的加载顺序与实例化逻辑，从 Core 主类抽取。
 *
 * @package WPCleanAdmin\Modules\Core\Classes
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

namespace WPCleanAdmin\Modules\Core\Classes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 模块加载器
 */
class Module_Loader {

    /**
     * 安全实例化模块（捕获异常，避免单模块故障拖垮插件）
     *
     * @param string $class_name 完整类名
     */
    private function safe_init( string $class_name ): void {
        try {
            if ( class_exists( $class_name ) && method_exists( $class_name, 'getInstance' ) ) {
                $class_name::getInstance();
            }
        } catch ( \Exception $e ) {
            if ( function_exists( 'error_log' ) ) {
                \error_log( 'WPCA Module Init Error (' . $class_name . '): ' . $e->getMessage() );
            }
        }
    }

    /**
     * Load legacy modules for backward compatibility
     */
    public function load_legacy_modules(): void {
        $modules = array(
            'Settings',
            'Dashboard',
            'Database',
            'Performance',
            'Menu_Manager',
            'Menu_Customizer',
            'Permissions',
            'User_Roles',
            'Login',
            'Cleanup',
            'Resources',
            'Reset',
            'AJAX',
            'i18n',
            'Error_Handler',
            'Cache',
            'Extension_API',
        );

        foreach ( $modules as $module ) {
            $this->safe_init( 'WPCleanAdmin\\' . $module );
        }
    }

    /**
     * Load modules from new modular structure
     */
    public function load_modular_modules(): void {
        // Load admin settings module
        $this->safe_init( 'WPCleanAdmin\\Modules\\Admin\\Settings\\Settings' );

        // Load core modules
        $this->safe_init( 'WPCleanAdmin\\Modules\\Core\\Classes\\Error_Handler' );

        // Load admin modules
        $admin_modules = array(
            'Dashboard',
            'Menu_Manager',
            'Menu_Customizer',
            'Permissions',
            'User_Roles',
            'Login',
        );
        foreach ( $admin_modules as $module ) {
            $this->safe_init( 'WPCleanAdmin\\Modules\\Admin\\Classes\\' . $module );
        }

        // Load utility modules
        $utility_modules = array(
            'Cache',
            'Helpers',
            'i18n',
            'Resources',
        );
        foreach ( $utility_modules as $module ) {
            $this->safe_init( 'WPCleanAdmin\\Modules\\Utilities\\Classes\\' . $module );
        }
    }
}
