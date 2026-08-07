<?php
/**
 * Settings AJAX sanitize 逻辑测试
 *
 * 通过反射调用 private static Settings::sanitize_settings，
 * 验证递归清理防止存储型 XSS（对应 P1 修复）。
 *
 * @package WPCleanAdmin
 */

use PHPUnit\Framework\TestCase;
use WPCleanAdmin\AJAX\Settings;

class SettingsAjaxTest extends TestCase {

    /**
     * 反射调用 Settings::sanitize_settings
     */
    private function call_sanitize( $value ) {
        $ref = new ReflectionMethod( Settings::class, 'sanitize_settings' );
        $ref->setAccessible( true );
        return $ref->invoke( null, $value );
    }

    public function test_sanitize_string_strips_tags() {
        $this->assertEquals( 'safe', $this->call_sanitize( '<img src=x onerror=alert(1)>safe' ) );
    }

    public function test_sanitize_array_recursive() {
        $input = array(
            'a' => '<b>bold</b>',
            'b' => array( 'c' => '  spaced  ' ),
        );
        $out = $this->call_sanitize( $input );
        $this->assertEquals( 'bold', $out['a'] );
        $this->assertEquals( 'spaced', $out['b']['c'] );
    }

    public function test_sanitize_preserves_scalars() {
        $this->assertSame( 10, $this->call_sanitize( 10 ) );
        $this->assertTrue( $this->call_sanitize( true ) );
    }
}
