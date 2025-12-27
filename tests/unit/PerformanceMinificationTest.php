<?php
/**
 * Unit tests for Performance class - minification and combination functionality
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 *
 * @noinspection PhpUndefinedClassInspection PHPUnit is loaded via composer
 * @noinspection PhpUndefinedMethodInspection PHPUnit methods are available
 */

use PHPUnit\Framework\TestCase;
use WPCleanAdmin\Performance;

/**
 * Test class for Performance - Minification and Combination features
 * @covers Performance
 */
class PerformanceMinificationTest extends TestCase {
    
    /**
     * Test instance creation
     */
    public function test_instance_creation() {
        $performance = Performance::getInstance();
        $this->assertInstanceOf( Performance::class, $performance );
    }
    
    /**
     * Test minify_css method exists
     */
    public function test_minify_css_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'minify_css' ) );
    }
    
    /**
     * Test minify_js method exists
     */
    public function test_minify_js_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'minify_js' ) );
    }
    
    /**
     * Test minify_css_content method exists
     */
    public function test_minify_css_content_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'minify_css_content' ) );
    }
    
    /**
     * Test minify_js_content method exists
     */
    public function test_minify_js_content_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'minify_js_content' ) );
    }
    
    /**
     * Test minify_css_content removes comments
     */
    public function test_minify_css_content_removes_comments() {
        $performance = Performance::getInstance();
        
        $css = '/* This is a comment */ body { color: red; }';
        $result = $performance->minify_css_content( $css );
        
        $this->assertEquals( 'body{color:red;}', $result );
    }
    
    /**
     * Test minify_css_content removes whitespace
     */
    public function test_minify_css_content_removes_whitespace() {
        $performance = Performance::getInstance();
        
        $css = 'body {   color:  red ;  }';
        $result = $performance->minify_css_content( $css );
        
        $this->assertEquals( 'body{color:red;}', $result );
    }
    
    /**
     * Test minify_css_content handles empty input
     */
    public function test_minify_css_content_handles_empty_input() {
        $performance = Performance::getInstance();
        
        $this->assertEquals( '', $performance->minify_css_content( '' ) );
    }
    
    /**
     * Test minify_css_content handles complex CSS
     */
    public function test_minify_css_content_handles_complex_css() {
        $performance = Performance::getInstance();
        
        $css = <<<CSS
        .container {
            width: 100%;
            height: auto;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }
        
        .container .inner {
            display: flex;
            justify-content: center;
            align-items: center;
        }
CSS;
        $result = $performance->minify_css_content( $css );
        
        $this->assertNotEmpty( $result );
        $this->assertStringNotContainsString( "\n", $result );
        $this->assertStringNotContainsString( '  ', $result );
    }
    
    /**
     * Test minify_js_content method exists
     */
    public function test_minify_js_content_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'minify_js_content' ) );
    }
    
    /**
     * Test minify_js_content removes multi-line comments
     */
    public function test_minify_js_content_removes_multiline_comments() {
        $performance = Performance::getInstance();
        
        $js = '/* This is a multi-line comment */ var x = 1;';
        $result = $performance->minify_js_content( $js );
        
        $this->assertEquals( 'var x=1;', $result );
    }
    
    /**
     * Test minify_js_content removes whitespace
     */
    public function test_minify_js_content_removes_whitespace() {
        $performance = Performance::getInstance();
        
        $js = 'var x   =   1  +  2 ;';
        $result = $performance->minify_js_content( $js );
        
        $this->assertEquals( 'var x=1+2;', $result );
    }
    
    /**
     * Test minify_js_content handles empty input
     */
    public function test_minify_js_content_handles_empty_input() {
        $performance = Performance::getInstance();
        
        $this->assertEquals( '', $performance->minify_js_content( '' ) );
    }
    
    /**
     * Test combine_css method exists
     */
    public function test_combine_css_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'combine_css' ) );
    }
    
    /**
     * Test combine_js method exists
     */
    public function test_combine_js_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'combine_js' ) );
    }
    
    /**
     * Test combine_css_files method exists
     */
    public function test_combine_css_files_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'combine_css_files' ) );
    }
    
    /**
     * Test combine_js_files method exists
     */
    public function test_combine_js_files_method_exists() {
        $performance = Performance::getInstance();
        $this->assertTrue( method_exists( $performance, 'combine_js_files' ) );
    }
    
    /**
     * Test combine_css_files returns null for empty array
     */
    public function test_combine_css_files_returns_null_for_empty_array() {
        $performance = Performance::getInstance();
        
        $result = $performance->combine_css_files( array() );
        $this->assertNull( $result );
    }
    
    /**
     * Test combine_js_files returns null for empty array
     */
    public function test_combine_js_files_returns_null_for_empty_array() {
        $performance = Performance::getInstance();
        
        $result = $performance->combine_js_files( array() );
        $this->assertNull( $result );
    }
    
    /**
     * Test combine_css_files returns null for non-array
     */
    public function test_combine_css_files_returns_null_for_non_array() {
        $performance = Performance::getInstance();
        
        $result = $performance->combine_css_files( 'not an array' );
        $this->assertNull( $result );
    }
    
    /**
     * Test combine_js_files returns null for non-array
     */
    public function test_combine_js_files_returns_null_for_non_array() {
        $performance = Performance::getInstance();
        
        $result = $performance->combine_js_files( 'not an array' );
        $this->assertNull( $result );
    }
}
