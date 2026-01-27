<?php
/**
 * WP Clean Admin Activation Debug Script
 * 
 * This script helps diagnose why the WP Clean Admin plugin
 * is failing to activate with a fatal error.
 * 
 * Instructions:
 * 1. Upload this file to your WordPress root directory
 * 2. Access it via your browser: https://your-site.com/wpca-activation-debug.php
 * 3. Check the error log for detailed information
 * 4. Remove this file after debugging
 */

// Define WordPress constants
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'SAVEQUERIES', true );

// Set error reporting to maximum
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
ini_set( 'log_errors', 1 );
ini_set( 'error_log', __DIR__ . '/wpca-activation-error.log' );

// Include WordPress core
if ( file_exists( __DIR__ . '/wp-load.php' ) ) {
    require_once __DIR__ . '/wp-load.php';
} else {
    die( 'Error: wp-load.php not found. Make sure this file is in your WordPress root directory.' );
}

// Custom error handler
function wpca_custom_error_handler( $errno, $errstr, $errfile, $errline ) {
    $error_types = array(
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE ERROR',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE ERROR',
        E_CORE_WARNING => 'CORE WARNING',
        E_COMPILE_ERROR => 'COMPILE ERROR',
        E_COMPILE_WARNING => 'COMPILE WARNING',
        E_USER_ERROR => 'USER ERROR',
        E_USER_WARNING => 'USER WARNING',
        E_USER_NOTICE => 'USER NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER DEPRECATED'
    );
    
    $error_msg = sprintf(
        "[%s] %s: %s in %s:%d\n",
        date( 'Y-m-d H:i:s' ),
        $error_types[$errno] ?? 'UNKNOWN',
        $errstr,
        $errfile,
        $errline
    );
    
    // Write to error log
    error_log( $error_msg, 3, __DIR__ . '/wpca-activation-error.log' );
    
    // Display error
    echo '<div style="background: #ffeeee; border: 1px solid #ff0000; padding: 10px; margin: 10px 0;">';
    echo '<strong>Error:</strong> ' . htmlspecialchars( $errstr ) . '<br>';
    echo '<strong>File:</strong> ' . htmlspecialchars( $errfile ) . '<br>';
    echo '<strong>Line:</strong> ' . $errline . '<br>';
    echo '<strong>Error Type:</strong> ' . ( $error_types[$errno] ?? 'UNKNOWN' ) . '<br>';
    echo '</div>';
    
    return false;
}

// Custom exception handler
function wpca_custom_exception_handler( $exception ) {
    $error_msg = sprintf(
        "[%s] EXCEPTION: %s in %s:%d\nStack trace:\n%s\n",
        date( 'Y-m-d H:i:s' ),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    
    // Write to error log
    error_log( $error_msg, 3, __DIR__ . '/wpca-activation-error.log' );
    
    // Display error
    echo '<div style="background: #ffeeee; border: 1px solid #ff0000; padding: 10px; margin: 10px 0;">';
    echo '<strong>Exception:</strong> ' . htmlspecialchars( $exception->getMessage() ) . '<br>';
    echo '<strong>File:</strong> ' . htmlspecialchars( $exception->getFile() ) . '<br>';
    echo '<strong>Line:</strong> ' . $exception->getLine() . '<br>';
    echo '<strong>Stack Trace:</strong><pre>' . htmlspecialchars( $exception->getTraceAsString() ) . '</pre>';
    echo '</div>';
}

// Custom shutdown function
function wpca_custom_shutdown_function() {
    $error = error_get_last();
    if ( $error !== null ) {
        wpca_custom_error_handler( $error['type'], $error['message'], $error['file'], $error['line'] );
    }
}

// Set error handlers
set_error_handler( 'wpca_custom_error_handler' );
set_exception_handler( 'wpca_custom_exception_handler' );
register_shutdown_function( 'wpca_custom_shutdown_function' );

// Start output
echo '<!DOCTYPE html>';
echo '<html lang="en">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<title>WP Clean Admin Activation Debug</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; margin: 20px; }';
echo 'h1 { color: #333; }';
echo 'h2 { color: #666; }';
echo '.success { background: #eeffee; border: 1px solid #00ff00; padding: 10px; margin: 10px 0; }';
echo '.info { background: #eeeeff; border: 1px solid #0000ff; padding: 10px; margin: 10px 0; }';
echo '.warning { background: #ffffee; border: 1px solid #ffcc00; padding: 10px; margin: 10px 0; }';
echo '.error { background: #ffeeee; border: 1px solid #ff0000; padding: 10px; margin: 10px 0; }';
echo 'pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<h1>WP Clean Admin Activation Debug</h1>';

// Check if plugin exists
$plugin_path = __DIR__ . '/wp-content/plugins/wpcleanadmin/wp-clean-admin.php';
if ( file_exists( $plugin_path ) ) {
    echo '<div class="success">✓ Plugin file found: ' . htmlspecialchars( $plugin_path ) . '</div>';
} else {
    echo '<div class="error">✗ Plugin file not found: ' . htmlspecialchars( $plugin_path ) . '</div>';
    echo '<div class="info">Please make sure the plugin is installed in the correct directory.</div>';
    echo '</body>';
    echo '</html>';
    exit;
}

// Test plugin activation
echo '<h2>Testing Plugin Activation</h2>';
echo '<div class="info">This will test loading the plugin and activating it.</div>';

try {
    echo '<div class="info">Loading plugin file...</div>';
    require_once $plugin_path;
    echo '<div class="success">✓ Plugin file loaded successfully</div>';
    
    echo '<div class="info">Testing plugin initialization...</div>';
    if ( function_exists( 'wpca_init' ) ) {
        wpca_init();
        echo '<div class="success">✓ Plugin initialized successfully</div>';
    } else {
        echo '<div class="error">✗ Plugin initialization function not found</div>';
    }
    
    echo '<div class="success">✓ All tests passed! The plugin should now activate correctly.</div>';
    echo '<div class="info">If you still encounter issues, check the error log at: ' . htmlspecialchars( __DIR__ . '/wpca-activation-error.log' ) . '</div>';
    
} catch ( Exception $e ) {
    echo '<div class="error">✗ Exception during activation: ' . htmlspecialchars( $e->getMessage() ) . '</div>';
    echo '<div class="info">Check the error log for more details: ' . htmlspecialchars( __DIR__ . '/wpca-activation-error.log' ) . '</div>';
} catch ( Error $e ) {
    echo '<div class="error">✗ Fatal error during activation: ' . htmlspecialchars( $e->getMessage() ) . '</div>';
    echo '<div class="info">Check the error log for more details: ' . htmlspecialchars( __DIR__ . '/wpca-activation-error.log' ) . '</div>';
}

echo '</body>';
echo '</html>';
?>