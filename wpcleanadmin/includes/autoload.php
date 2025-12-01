<?php
/**
 * PSR-4 Autoloader for WPCleanAdmin plugin
 *
 * @package WPCleanAdmin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
    
    // Convert camelCase to kebab-case
    $file_path = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $class_name ) );
    
    // Build full file path
    $file = WPCA_PLUGIN_DIR . 'includes/class-wpca-' . $file_path . '.php';
    
    // Check if file exists and include it
    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );
