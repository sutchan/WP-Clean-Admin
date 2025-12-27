<?php
/**
 * PHPUnit Framework Stub for Intelephense
 *
 * This file provides stub declarations for PHPUnit framework
 * to enable proper IDE auto-completion and error checking.
 *
 * @package WPCleanAdmin
 */

namespace PHPUnit\Framework {
    if ( ! class_exists( 'TestCase' ) ) {
        abstract class TestCase {
            public function assertInstanceOf( $expected, $actual, $message = '' ) {}
            public function assertSame( $expected, $actual, $message = '' ) {}
            public function assertIsArray( $actual, $message = '' ) {}
            public function assertTrue( $condition, $message = '' ) {}
            public function assertFalse( $condition, $message = '' ) {}
            public function assertEquals( $expected, $actual, $message = '' ) {}
            public function assertNull( $actual, $message = '' ) {}
            public function assertNotNull( $actual, $message = '' ) {}
            public function assertArrayHasKey( $key, $array, $message = '' ) {}
            public function assertArrayNotHasKey( $key, $array, $message = '' ) {}
            public function assertNotEmpty( $actual, $message = '' ) {}
            public function assertStringNotContainsString( $needle, $haystack, $message = '' ) {}
            public function assertIsString( $actual, $message = '' ) {}
            public function assertEmpty( $actual, $message = '' ) {}
            public function assertIsBool( $actual, $message = '' ) {}
            public function assertStringContainsString( $needle, $haystack, $message = '' ) {}
        }
    }
}
