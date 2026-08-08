<?php
/**
 * 高保真原型：设置模块（全局配置）
 *
 * 数据结构见 设计规范_20260808.md 第 9.2 节 SettingsConfig。
 *
 * @package WPCleanAdmin\Modules\Settings
 * @version 1.8.3
 */

namespace WPCleanAdmin\Modules\Settings;

use WPCleanAdmin\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Module_Settings extends Module_Base {

    protected function init() {
        $this->register_ajax( 'wpca_settings_save', array( $this, 'ajax_save' ) );
        $this->register_ajax( 'wpca_settings_load', array( $this, 'ajax_load' ) );
    }

    public function get_module_name() {
        return 'settings';
    }

    /**
     * 默认设置（业务规则 R4：保存时合并默认值）
     *
     * @return array
     */
    public function get_defaults() {
        return array(
            'general'     => array( 'keep_revisions' => 10, 'auto_cleanup' => true ),
            'performance' => array( 'disable_emojis' => true, 'lazy_images' => true, 'minify_html' => false ),
            'menu'        => array(),
        );
    }

    /**
     * AJAX：保存设置（递归 sanitize，存储于 wpca_settings）
     */
    public function ajax_save() {
        $raw = isset( $_POST['settings'] ) ? json_decode( wp_unslash( $_POST['settings'] ), true ) : array();
        $raw = is_array( $raw ) ? $raw : array();

        // 合并默认值（R4）
        $merged = array();
        foreach ( $this->get_defaults() as $group => $defs ) {
            $merged[ $group ] = array_merge( $defs, isset( $raw[ $group ] ) ? $this->sanitize_settings( $raw[ $group ] ) : array() );
        }

        if ( function_exists( '\update_option' ) ) {
            \update_option( 'wpca_settings', $merged );
        }

        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( array( 'saved' => true ) );
        }
    }

    /**
     * AJAX：读取设置
     */
    public function ajax_load() {
        $saved = function_exists( '\get_option' ) ? \get_option( 'wpca_settings', array() ) : array();
        $merged = array();
        foreach ( $this->get_defaults() as $group => $defs ) {
            $merged[ $group ] = array_merge( $defs, isset( $saved[ $group ] ) ? $saved[ $group ] : array() );
        }
        if ( function_exists( '\wp_send_json_success' ) ) {
            \wp_send_json_success( array( 'config' => $merged ) );
        }
    }
}
