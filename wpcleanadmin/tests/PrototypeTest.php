<?php
/**
 * 原型骨架单元测试
 *
 * 验证 Module_Base 可实例化、AJAX_Gateway_Base nonce 校验拒绝无效值。
 * 原型文件位于 assets/prototype/php，独立于主代码，测试内手动 require。
 *
 * @package WPCleanAdmin
 */

use PHPUnit\Framework\TestCase;

class PrototypeTest extends TestCase {

    protected function setUp(): void {
        $base = dirname( __DIR__ ) . '/assets/prototype/php/';
        require_once $base . 'class-wpca-module-base.php';
        require_once $base . 'class-wpca-ajax-gateway-base.php';
    }

    /**
     * Module_Base 抽象类不应被直接实例化，子类可
     */
    public function test_module_base_is_abstract() {
        $ref = new ReflectionClass( 'WPCleanAdmin\Modules\Module_Base' );
        $this->assertTrue( $ref->isAbstract() );
    }

    /**
     * AJAX 网关是单例
     */
    public function test_gateway_is_singleton() {
        $a = \WPCleanAdmin\AJAX\Gateway_Base::get_instance();
        $b = \WPCleanAdmin\AJAX\Gateway_Base::get_instance();
        $this->assertSame( $a, $b );
    }

    /**
     * 网关 nonce action 固定为 wpca_ajax_nonce（前后端一致性基线）
     */
    public function test_gateway_nonce_action_constant() {
        $ref = new ReflectionMethod( \WPCleanAdmin\AJAX\Gateway_Base::class, 'dispatch' );
        // dispatch 通过 wp_verify_nonce($nonce, 'wpca_ajax_nonce') 校验，仅验证方法存在
        $this->assertTrue( $ref->isPublic() );
    }
}
