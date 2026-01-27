<?php
/**
 * Check class names against file names to ensure autoloading works correctly
 * 
 * This script checks if all class names in the includes directory match
 * the expected file names based on the autoloading pattern.
 */

// Define constants
define( 'WPCA_PLUGIN_DIR', __DIR__ . '/wpcleanadmin/' );

// Expected class names and their corresponding file names
$expected_classes = array(
    'WPCleanAdmin\\Core' => 'class-wpca-core.php',
    'WPCleanAdmin\\Settings' => 'class-wpca-settings.php',
    'WPCleanAdmin\\Dashboard' => 'class-wpca-dashboard.php',
    'WPCleanAdmin\\Database' => 'class-wpca-database.php',
    'WPCleanAdmin\\Performance' => 'class-wpca-performance.php',
    'WPCleanAdmin\\Menu_Manager' => 'class-wpca-menu-manager.php',
    'WPCleanAdmin\\Menu_Customizer' => 'class-wpca-menu-customizer.php',
    'WPCleanAdmin\\Permissions' => 'class-wpca-permissions.php',
    'WPCleanAdmin\\User_Roles' => 'class-wpca-user-roles.php',
    'WPCleanAdmin\\Login' => 'class-wpca-login.php',
    'WPCleanAdmin\\Cleanup' => 'class-wpca-cleanup.php',
    'WPCleanAdmin\\Resources' => 'class-wpca-resources.php',
    'WPCleanAdmin\\Reset' => 'class-wpca-reset.php',
    'WPCleanAdmin\\AJAX' => 'class-wpca-ajax.php',
    'WPCleanAdmin\\i18n' => 'class-wpca-i18n.php',
    'WPCleanAdmin\\Error_Handler' => 'class-wpca-error-handler.php',
    'WPCleanAdmin\\Cache' => 'class-wpca-cache.php',
    'WPCleanAdmin\\Extension_API' => 'class-wpca-extension-api.php'
);

// Check each class
foreach ( $expected_classes as $class_name => $expected_file ) {
    $file_path = WPCA_PLUGIN_DIR . 'includes/' . $expected_file;
    
    echo "Checking class: $class_name\n";
    echo "Expected file: $expected_file\n";
    
    if ( file_exists( $file_path ) ) {
        echo "✓ File exists: $file_path\n";
        
        // Check if the class is defined in the file
        $file_content = file_get_contents( $file_path );
        $namespace = 'WPCleanAdmin';
        
        // Check if the namespace is correct
        if ( strpos( $file_content, "namespace $namespace;" ) !== false ) {
            echo "✓ Namespace is correct: $namespace\n";
        } else {
            echo "✗ Namespace is incorrect\n";
        }
        
        // Check if the class is defined
        $short_class_name = str_replace( "$namespace\\", '', $class_name );
        if ( strpos( $file_content, "class $short_class_name" ) !== false ) {
            echo "✓ Class is defined: $short_class_name\n";
        } else {
            echo "✗ Class is not defined: $short_class_name\n";
        }
        
    } else {
        echo "✗ File does not exist: $file_path\n";
    }
    
    echo "\n";
}

// Test the autoloading pattern
function test_autoload_pattern( $class_name ) {
    // Check if the class belongs to our namespace
    if ( strpos( $class_name, 'WPCleanAdmin\\' ) !== 0 ) {
        return "Class does not belong to WPCleanAdmin namespace";
    }
    
    // Remove namespace prefix
    $class = str_replace( 'WPCleanAdmin\\', '', $class_name );
    
    // Convert camelCase to kebab-case and replace underscores with hyphens
    $file_path = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $class ) );
    $file_path = str_replace( '_', '-', $file_path );
    
    // Build full file path
    $file = WPCA_PLUGIN_DIR . 'includes/class-wpca-' . $file_path . '.php';
    
    return "Class: $class_name -> File: $file";
}

echo "=== Testing Autoload Pattern ===\n";
foreach ( array_keys( $expected_classes ) as $class_name ) {
    echo test_autoload_pattern( $class_name ) . "\n";
}
?>