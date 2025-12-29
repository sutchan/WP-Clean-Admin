<?php
/**
 * PHPUnit Framework Stub for WPCleanAdmin Tests
 *
 * @package WPCleanAdmin
 */

// Simple PHPUnit TestCase stub for IDE support
if ( ! class_exists( 'PHPUnit\Framework\TestCase' ) ) {
    // Create PHPUnit framework classes using class_alias to avoid namespace issues
    class _PHPUnit_TestCase {
        public function assertEquals( $expected, $actual, $message = '' ) {}
        public function assertIsBool( $actual, $message = '' ) {}
        public function assertIsString( $actual, $message = '' ) {}
        public function assertNotEmpty( $actual, $message = '' ) {}
        public function assertSame( $expected, $actual, $message = '' ) {}
        public function assertNotSame( $expected, $actual, $message = '' ) {}
        public function assertTrue( $condition, $message = '' ) {}
        public function assertFalse( $condition, $message = '' ) {}
        public function assertNull( $actual, $message = '' ) {}
        public function assertNotNull( $actual, $message = '' ) {}
        public function assertGreaterThan( $expected, $actual, $message = '' ) {}
        public function assertGreaterThanOrEqual( $expected, $actual, $message = '' ) {}
        public function assertLessThan( $expected, $actual, $message = '' ) {}
        public function assertLessThanOrEqual( $expected, $actual, $message = '' ) {}
        public function assertCount( $expected, $actual, $message = '' ) {}
        public function assertContains( $expected, $actual, $message = '' ) {}
        public function assertNotContains( $expected, $actual, $message = '' ) {}
        public function assertInstanceOf( $expected, $actual, $message = '' ) {}
        public function assertNotInstanceOf( $expected, $actual, $message = '' ) {}
        public function assertArrayHasKey( $expected, $actual, $message = '' ) {}
        public function assertArrayNotHasKey( $expected, $actual, $message = '' ) {}
        public function expectException( $exception ) {}
        public function expectExceptionMessage( $message ) {}
        public function expectExceptionCode( $code ) {}
        public function setUp() {}
        public function tearDown() {}
        public static function setUpBeforeClass() {}
        public static function tearDownAfterClass() {}
    }
    
    // Create the PHPUnit\Framework namespace and alias the test case class
    spl_autoload_register(function($class) {
        if ($class === 'PHPUnit\Framework\TestCase') {
            class_alias('_PHPUnit_TestCase', $class);
        }
    });
}