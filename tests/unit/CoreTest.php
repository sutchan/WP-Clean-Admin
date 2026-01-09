<?php
/**
 * WPCleanAdmin Core Test
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @since 1.8.0
 */

use PHPUnit\Framework\TestCase;

class CoreTest extends TestCase {
    /**
     * Test Core class singleton instance
     */
    public function test_core_instance() {
        $instance1 = WPCleanAdmin\Core::getInstance();
        $instance2 = WPCleanAdmin\Core::getInstance();
        
        $this->assertSame( $instance1, $instance2 );
    }
    
    /**
     * Test plugin constants are defined
     */
    public function test_plugin_constants() {
        $this->assertDefined( 'WPCA_VERSION' );
        $this->assertDefined( 'WPCA_PLUGIN_DIR' );
        $this->assertDefined( 'WPCA_PLUGIN_URL' );
        $this->assertDefined( 'WPCA_TEXT_DOMAIN' );
    }
    
    /**
     * Test assertDefined helper method
     */
    private function assertDefined( $constant ) {
        $this->assertTrue( defined( $constant ), "Constant $constant should be defined" );
    }
}
