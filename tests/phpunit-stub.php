<?php
/**
 * PHPUnit Framework Stub for IDE Support
 *
 * This file provides stub implementations for PHPUnit framework classes
 * to enable proper IDE auto-completion and type checking.
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @since 1.8.0
 */

// PHPUnit\Framework\Test interface
interface PHPUnit_Framework_Test {
}

// PHPUnit\Framework\TestCase class
class PHPUnit_Framework_TestCase implements PHPUnit_Framework_Test {
    /**
     * Asserts that two variables are identical.
     *
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     */
    public function assertSame($expected, $actual, $message = '') {}
    
    /**
     * Asserts that a variable is not null.
     *
     * @param mixed $actual
     * @param string $message
     */
    public function assertNotNull($actual, $message = '') {}
    
    /**
     * Asserts that a condition is true.
     *
     * @param bool $condition
     * @param string $message
     */
    public function assertTrue($condition, $message = '') {}
    
    /**
     * Asserts that a condition is false.
     *
     * @param bool $condition
     * @param string $message
     */
    public function assertFalse($condition, $message = '') {}
    
    /**
     * Asserts that a variable is an array.
     *
     * @param mixed $actual
     * @param string $message
     */
    public function assertIsArray($actual, $message = '') {}
    
    /**
     * Asserts that an array has a specified key.
     *
     * @param mixed $key
     * @param array $array
     * @param string $message
     */
    public function assertArrayHasKey($key, $array, $message = '') {}
    
    /**
     * Skips the current test.
     *
     * @param string $message
     */
    public function markTestSkipped($message = '') {}
    
    /**
     * Marks the current test as incomplete.
     *
     * @param string $message
     */
    public function markTestIncomplete($message = '') {}
    
    /**
     * Sets up the test environment.
     */
    protected function setUp(): void {}
    
    /**
     * Cleans up the test environment.
     */
    protected function tearDown(): void {}
    
    /**
     * Tells the test runner that no assertions will be performed.
     */
    public function expectNotToPerformAssertions(): void {}
    
    /**
     * Creates a mock object builder.
     *
     * @param string $className
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function getMockBuilder($className) {
        return new PHPUnit_Framework_MockObject_MockBuilder();
    }
}

// PHPUnit\Framework\MockObject\MockBuilder class
class PHPUnit_Framework_MockObject_MockBuilder {
    /**
     * Sets the methods to be mocked.
     *
     * @param array|string $methods
     * @return $this
     */
    public function setMethods($methods) { return $this; }
    
    /**
     * Disables original constructor.
     *
     * @return $this
     */
    public function disableOriginalConstructor() { return $this; }
    
    /**
     * Sets a return callback for a method.
     *
     * @param callable $callback
     * @return $this
     */
    public function returnCallback($callback) { return $this; }
    
    /**
     * Creates the mock object.
     *
     * @return object
     */
    public function getMock() { return new stdClass(); }
}

// PHPUnit\Framework\MockObject\Matcher\Invocation class
class PHPUnit_Framework_MockObject_Matcher_Invocation {
}

// PHPUnit\Framework\MockObject\Stub\ReturnValueMap class
class PHPUnit_Framework_MockObject_Stub_ReturnValueMap {
    public function __construct($valueMap) {}
}

// PHPUnit\Framework\MockObject\Builder\InvocationMocker class
class PHPUnit_Framework_MockObject_Builder_InvocationMocker {
    public function with($argument1 = null, ...$arguments) { return $this; }
    public function will($stub) { return $this; }
    public function withConsecutive(...$argumentGroups) { return $this; }
    public function willReturnMap($valueMap) { return $this; }
}

// PHPUnit\Framework\MockObject\Builder\MethodNameMatch class
class PHPUnit_Framework_MockObject_Builder_MethodNameMatch {
    public function method($methodName) {
        return new PHPUnit_Framework_MockObject_Builder_InvocationMocker();
    }
}

// Class aliases for namespaced versions
if ( ! class_exists( 'PHPUnit\Framework\Test' ) ) {
    class_alias( 'PHPUnit_Framework_Test', 'PHPUnit\Framework\Test' );
}

if ( ! class_exists( 'PHPUnit\Framework\TestCase' ) ) {
    class_alias( 'PHPUnit_Framework_TestCase', 'PHPUnit\Framework\TestCase' );
}

if ( ! class_exists( 'PHPUnit\Framework\MockObject\MockBuilder' ) ) {
    class_alias( 'PHPUnit_Framework_MockObject_MockBuilder', 'PHPUnit\Framework\MockObject\MockBuilder' );
}
