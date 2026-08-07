<?php
/**
 * 高保真原型：安全与菜单模块（菜单定制 + 权限 + 登录优化）
 *
 * @package WPCleanAdmin\Modules\Security
 * @version 1.8.2
 */

namespace WPCleanAdmin\Modules\Security;

use WPCleanAdmin\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Module_Security extends Module_Base {

    protected function init() {
        $this->register_ajax( 'wpca_security_save', array( $this, 'ajax_save' ) );
    }

    public function get_module_name() {
        return 'security';
    }

    /**
     * 后台菜单树（高保真静态数据，含可见性开关）
     *
     * @return array
     */
    public function get_menu_tree() {
        return array(
            array( 'slug' => 'index.php', 'label' => '仪表盘', 'visible' => true, 'system' => true ),
            array( 'slug' => 'edit.php', 'label' => '文章', 'visible' => true, 'system' => false ),
            array( 'slug' => 'upload.php', 'label' => '媒体', 'visible' => true, 'system' => false ),
            array( 'slug' => 'themes.php', 'label' => '外观', 'visible' => false, 'system' => false ),
            array( 'slug' => 'plugins.php', 'label' => '插件', 'visible' => true, 'system' => true ),
            array( 'slug' => 'users.php', 'label' => '用户', 'visible' => true, 'system' => false ),
            array( 'slug' => 'options-general.php', 'label' => '设置', 'visible' => true, 'system' => true ),
        );
    }

    /**
     * AJAX：保存菜单可见性
     */
    public function ajax_save() {
        $menus = isset( $_POST['menus'] ) ? $this->sanitize_settings( \wp_unslash( $_POST['menus'] ) ) : array();

        if ( function_exists( '\update_option' ) ) {
            \update_option( 'wpca_menu_visibility', $menus );
        }

        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( array( 'saved' => count( $menus ) ) );
        }
    }
}
