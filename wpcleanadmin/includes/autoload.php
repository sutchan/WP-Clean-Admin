<?php
/**
 * WPCleanAdmin PSR-4 Autoloader
 * 
 * Implements a PSR-4 compliant autoloader for the WPCleanAdmin plugin.
 * This allows for automatic class loading without requiring manual includes.
 * 
 * @package WPCleanAdmin
 * @since 1.4.1
 */

// Exit if accessed directly
// defined是PHP语言结构，不需要function_exists检查
if ( ! defined( 'ABSPATH' ) ) {
    if ( function_exists( 'exit' ) ) {
        exit;
    } else {
        return;
    }
}

/**
 * Register the autoloader
 * 
 * @return void
 */
function wpca_register_autoloader() {
    // Register the autoloader function
    if ( function_exists( 'spl_autoload_register' ) ) {
        spl_autoload_register('wpca_autoloader');
    }
}

/**
 * PSR-4 compliant autoloader function
 * 
 * @param string $class_name The fully qualified class name
 * @return void
 */
function wpca_autoloader($class_name) {
    // Check if the class belongs to our plugin namespace
    if ( function_exists( 'strpos' ) && strpos($class_name, 'WPCA_') === 0 ) {
        // Remove the namespace prefix
        if ( function_exists( 'substr' ) && function_exists( 'strlen' ) ) {
            $relative_class = substr($class_name, strlen('WPCA_'));
            
            // Convert class name to file path
            if ( function_exists( 'strtolower' ) && function_exists( 'str_replace' ) ) {
                $class_file = 'class-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';
                
                // Define the base directory for classes
                $base_dir = ( defined( 'WPCA_PLUGIN_DIR' ) ? WPCA_PLUGIN_DIR : '' ) . 'includes/';
                
                // Construct the full file path
                $file_path = $base_dir . $class_file;
                
                // Check if the file exists and include it
                // file_exists是PHP内置函数，可以安全使用
                // require_once是PHP语言结构，不需要function_exists检查
                if ( file_exists($file_path) ) {
                    require_once $file_path;
                }
            }
        }
    }
}

/**
 * Initialize the autoloader
 */
if (function_exists('wpca_register_autoloader')) {
    wpca_register_autoloader();
}
?>
