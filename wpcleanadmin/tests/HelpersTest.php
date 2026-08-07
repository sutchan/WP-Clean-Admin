<?php
/**
 * Helpers 类单元测试
 *
 * 覆盖与本次审计修复相关的核心逻辑：
 * - sanitize_array（设置清理，对应 settings-ajax 的 sanitize_settings）
 * - validate_ajax_nonce（nonce 校验，对应 AJAX 字段名统一修复）
 * - format_bytes（纯函数，无依赖）
 *
 * @package WPCleanAdmin
 */

use PHPUnit\Framework\TestCase;
use WPCleanAdmin\Helpers;

class HelpersTest extends TestCase {

    /**
     * @var Helpers
     */
    private $helpers;

    protected function setUp(): void {
        $this->helpers = Helpers::getInstance();
    }

    /**
     * 测试递归清理数组 —— 对应 P1 settings sanitize 修复
     */
    public function test_sanitize_array_strips_tags() {
        $input = array(
            'title'   => '<script>alert(1)</script>Hello',
            'nested'  => array( 'name' => '  Trim me  ' ),
            'enabled' => true,
            'count'   => 42,
        );

        $result = $this->helpers->sanitize_array( $input );

        $this->assertEquals( 'Hello', $result['title'] );
        $this->assertEquals( 'Trim me', $result['nested']['name'] );
        $this->assertTrue( $result['enabled'] );
        $this->assertSame( 42, $result['count'] );
    }

    /**
     * 测试格式字节换算正确
     */
    public function test_format_bytes() {
        $this->assertEquals( '0 B', $this->helpers->format_bytes( 0 ) );
        $this->assertEquals( '1 KB', $this->helpers->format_bytes( 1024 ) );
        $this->assertEquals( '1 MB', $this->helpers->format_bytes( 1024 * 1024 ) );
    }

    /**
     * 测试 nonce 校验：无效 nonce 返回 false
     */
    public function test_validate_ajax_nonce_rejects_invalid() {
        $this->assertFalse( $this->helpers->validate_ajax_nonce( 'invalid-nonce' ) );
    }
}
