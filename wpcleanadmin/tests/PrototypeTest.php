<?php
/**
 * 原型骨架单元测试（高保真）
 *
 * 验证 Module_Base 抽象类、AJAX_Gateway_Base 单例，
 * 以及 6 个核心模块可被加载（数据接口可调用）。
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
        foreach ( glob( $base . 'modules/class-wpca-module-*.php' ) as $file ) {
            require_once $file;
        }
    }

    public function test_module_base_is_abstract() {
        $ref = new ReflectionClass( 'WPCleanAdmin\Modules\Module_Base' );
        $this->assertTrue( $ref->isAbstract() );
    }

    public function test_gateway_is_singleton() {
        $a = \WPCleanAdmin\AJAX\Gateway_Base::get_instance();
        $b = \WPCleanAdmin\AJAX\Gateway_Base::get_instance();
        $this->assertSame( $a, $b );
    }

    /**
     * 验证 6 个核心模块均定义了 get_module_name()
     *
     * @return void
     */
    public function test_core_modules_expose_name() {
        $expected = array(
            'WPCleanAdmin\Modules\Admin\Module_Dashboard'     => 'dashboard',
            'WPCleanAdmin\Modules\Cleanup\Module_Cleanup'     => 'cleanup',
            'WPCleanAdmin\Modules\Performance\Module_Performance' => 'performance',
            'WPCleanAdmin\Modules\Security\Module_Security'   => 'security',
            'WPCleanAdmin\Modules\Database\Module_Database'    => 'database',
            'WPCleanAdmin\Modules\Diagnostics\Module_Diagnostics' => 'diagnostics',
        );
        foreach ( $expected as $class => $name ) {
            $this->assertTrue( class_exists( $class ), $class . ' 应存在' );
            $inst = $class::get_instance();
            $this->assertEquals( $name, $inst->get_module_name() );
        }
    }

    /**
     * Dashboard 体检指标返回数组且含 level 键
     */
    public function test_dashboard_metrics_shape() {
        $dash = \WPCleanAdmin\Modules\Admin\Module_Dashboard::get_instance();
        $metrics = $dash->get_health_metrics();
        $this->assertIsArray( $metrics );
        $this->assertArrayHasKey( 'spam', $metrics );
        $this->assertArrayHasKey( 'level', $metrics['spam'] );
    }
}
