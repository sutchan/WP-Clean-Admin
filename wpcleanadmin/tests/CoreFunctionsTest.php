<?php
/**
 * Core Functions Tests
 *
 * @package WPCleanAdmin
 */

use PHPUnit\Framework\TestCase;

class CoreFunctionsTest extends TestCase {

    /**
     * Test that core functions are available
     */
    public function test_core_functions_exist() {
        $this->assertTrue( function_exists( 'wpca_get_settings' ) );
        $this->assertTrue( function_exists( 'wpca_get_version' ) );
        $this->assertTrue( function_exists( 'wpca_get_text_domain' ) );
    }

    /**
     * Test get version function
     */
    public function test_get_version() {
        $version = wpca_get_version();
        $this->assertIsString( $version );
        $this->assertNotEmpty( $version );
    }

    /**
     * Test get text domain function
     */
    public function test_get_text_domain() {
        $domain = wpca_get_text_domain();
        $this->assertEquals( 'wp-clean-admin', $domain );
    }
}
