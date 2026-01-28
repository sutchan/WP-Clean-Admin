<?php
/**
 * WPCleanAdmin PSR-4 Autoloader
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Skip loading stubs in WordPress runtime environment
// These stubs are only for IDE support during development
// They can cause conflicts with actual WordPress functions
// if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
//     // Load WordPress stubs for IDE support
//     require_once __DIR__ . '/wpca-wordpress-stubs.php';
//     
//     // Load Composer stub for IDE support
//     require_once __DIR__ . '/composer-stub.php';
//     
//     // Load Elementor stub for IDE support
//     require_once __DIR__ . '/elementor-stub.php';
// }

/**
 * Register autoloader for WPCleanAdmin classes
 */
spl_autoload_register( function( $class ) {
    // Check if the class belongs to our namespace
    if ( strpos( $class, 'WPCleanAdmin\\' ) !== 0 ) {
        return;
    }
    
    // Remove namespace prefix
    $class_name = str_replace( 'WPCleanAdmin\\', '', $class );
    
    // Convert camelCase to kebab-case and replace underscores with hyphens
    $file_path = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $class_name ) );
    $file_path = str_replace( '_', '-', $file_path );
    
    // Build full file path
    $file = WPCA_PLUGIN_DIR . 'includes/class-wpca-' . $file_path . '.php';
    
    // Check if file exists and include it
    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );


