<?php
/**
 * Test case for WP Clean Admin Core
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 */

use PHPUnit\Framework\TestCase;
use WPCleanAdmin\Core;

class CoreTest extends TestCase {
    
    /**
     * Test core singleton instance
     */
    public function test_get_instance() {
        $instance = Core::getInstance();
        $this->assertInstanceOf( Core::class, $instance );
        
        $second_instance = Core::getInstance();
        $this->assertSame( $instance, $second_instance );
    }
    
    /**
     * Test security headers action registration
     */
    public function test_init_hooks() {
        $core = Core::getInstance();
        $this->assertNotFalse( has_action( 'send_headers', array( $core, 'send_security_headers' ) ) );
    }
}
