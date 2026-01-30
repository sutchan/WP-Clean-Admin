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
    
    // Split namespace parts
    $namespace_parts = explode( '\\', $class_name );
    $class_basename = array_pop( $namespace_parts );
    
    // Convert camelCase to kebab-case and replace underscores with hyphens for class name
    $class_file = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $class_basename ) );
    $class_file = str_replace( '_', '-', $class_file );
    
    // Build directory path from namespace parts
    $dir_path = '';
    if ( ! empty( $namespace_parts ) ) {
        foreach ( $namespace_parts as $part ) {
            $dir_path .= strtolower( $part ) . '/';
        }
    }
    
    // Build full file path
    $plugin_dir = defined( 'WPCA_PLUGIN_DIR' ) ? WPCA_PLUGIN_DIR : dirname( dirname( __FILE__ ) ) . '/';
    
    // Check for files in subdirectories
    // For AJAX handlers
    if ( ! empty( $namespace_parts ) && $namespace_parts[0] === 'AJAX' ) {
        $file = $plugin_dir . 'includes/' . $dir_path . $class_file . '-ajax.php';
    } 
    // For Settings handlers
    elseif ( ! empty( $namespace_parts ) && $namespace_parts[0] === 'Settings' ) {
        $file = $plugin_dir . 'includes/' . $dir_path . $class_file . '.php';
    } 
    // For main classes
    else {
        $file = $plugin_dir . 'includes/class-wpca-' . $class_file . '.php';
    }
    
    // Check if file exists and include it
    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );


