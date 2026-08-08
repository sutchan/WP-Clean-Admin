<?php
/**
 * AJAX 网关基类（原型骨架）
 *
 * 统一固化：
 *  - nonce 字段名：_wpnonce（与前端发送字段一致，修复 1.8.3 一致性缺陷）
 *  - nonce action：wpca_ajax_nonce
 *  - 权限校验：current_user_can( 'manage_options' )
 *  - 输出：wp_send_json_success / wp_send_json_error
 *
 * 所有 AJAX handler 须经由此网关注册，禁止裸 add_action('wp_ajax_*')。
 *
 * @package WPCleanAdmin\AJAX
 * @version 1.8.3
 */

namespace WPCleanAdmin\AJAX;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Gateway_Base {

    /**
     * @var static
     */
    private static $instance = null;

    /**
     * 已注册的 AJAX action 映射
     *
     * @var array
     */
    private $handlers = array();

    /**
     * 单例
     *
     * @return static
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 注册 AJAX action
     *
     * @param string   $action  action 名
     * @param callable $callback 回调
     * @param bool     $public  是否公开
     * @return void
     */
    public function register( $action, $callback, $public = false ) {
        $this->handlers[ $action ] = array(
            'callback' => $callback,
            'public'   => $public,
        );

        add_action( 'wp_ajax_' . $action, array( $this, 'dispatch' ) );
        if ( $public ) {
            add_action( 'wp_ajax_nopriv_' . $action, array( $this, 'dispatch' ) );
        }
    }

    /**
     * 统一分发：校验 nonce + 权限，再调用回调
     *
     * @return void
     */
    public function dispatch() {
        // 统一字段名 _wpnonce（与前端一致）
        $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

        if ( ! function_exists( '\wp_verify_nonce' ) || ! \wp_verify_nonce( $nonce, 'wpca_ajax_nonce' ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => 'invalid_nonce' ), 403 );
            }
            exit;
        }

        $action  = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
        $handler = isset( $this->handlers[ $action ] ) ? $this->handlers[ $action ] : null;

        if ( ! $handler ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => 'unknown_action' ), 404 );
            }
            exit;
        }

        if ( ! $handler['public'] && function_exists( '\current_user_can' ) && ! \current_user_can( 'manage_options' ) ) {
            if ( function_exists( '\wp_send_json_error' ) ) {
                \wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
            }
            exit;
        }

        call_user_func( $handler['callback'] );
    }
}
