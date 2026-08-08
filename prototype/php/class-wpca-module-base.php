<?php
/**
 * 模块抽象基类（原型骨架）
 *
 * 所有模块化功能须继承此类，统一生命周期与 AJAX 注册。
 * 这是新功能开发的权威基线，替代旧式 includes/class-wpca-*.php。
 *
 * @package WPCleanAdmin\Modules
 * @version 1.8.2
 */

namespace WPCleanAdmin\Modules;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 模块基类
 *
 * 子类实现 get_module_name() 与 init() 即可接入插件。
 * AJAX handler 须通过 register_ajax() 注册，由 AJAX_Gateway_Base 统一校验。
 */
abstract class Module_Base {

    /**
     * 单例实例
     *
     * @var static
     */
    private static $instances = array();

    /**
     * 获取单例实例
     *
     * @return static
     */
    public static function get_instance() {
        $class = static::class;
        if ( ! isset( self::$instances[ $class ] ) ) {
            self::$instances[ $class ] = new static();
        }
        return self::$instances[ $class ];
    }

    /**
     * 构造：自动 init
     */
    protected function __construct() {
        $this->init();
    }

    /**
     * 子类实现：注册 hooks / 菜单 / AJAX
     */
    abstract protected function init();

    /**
     * 返回模块名（用于日志与标识）
     *
     * @return string
     */
    abstract public function get_module_name();

    /**
     * 注册 AJAX action，统一走网关校验
     *
     * 注意：字段名固定为 _wpnonce，action 名固定为 wpca_ajax_nonce，
     * 与前端发送字段保持一致（参见 1.8.2 修复）。
     *
     * @param string   $action   AJAX action 名称（不含 wp_ajax_ 前缀）
     * @param callable $callback 处理方法
     * @param bool     $public   是否允许未登录用户访问（默认 false）
     * @return void
     */
    protected function register_ajax( $action, $callback, $public = false ) {
        $ajax = \WPCleanAdmin\AJAX\Gateway_Base::get_instance();
        $ajax->register( $action, $callback, $public );
    }

    /**
     * 递归清理设置值，防止存储型 XSS（基线安全）
     *
     * @param mixed $value 待清理值
     * @return mixed
     */
    protected function sanitize_settings( $value ) {
        if ( is_array( $value ) ) {
            $clean = array();
            foreach ( $value as $key => $item ) {
                $clean[ $key ] = $this->sanitize_settings( $item );
            }
            return $clean;
        }
        if ( is_string( $value ) ) {
            return function_exists( '\sanitize_text_field' )
                ? \sanitize_text_field( $value )
                : strip_tags( trim( $value ) );
        }
        return $value;
    }
}
