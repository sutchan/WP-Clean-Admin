#!/usr/bin/env php
<?php
/**
 * Script to remove duplicate file header comments from PHP files
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @since 1.8.0
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define directory to scan
$directory = __DIR__ . '/wpcleanadmin';

// Function to remove duplicate file header comments
function remove_duplicate_headers($file_path) {
    // Read file content
    $content = file_get_contents($file_path);
    if ($content === false) {
        echo "Error reading file: $file_path\n";
        return false;
    }

    // Pattern to match file header comments
    $header_pattern = '/^\s*<\?php\s*\/\*\*[\s\S]*?\*\/\s*/';
    
    // Find all matches
    preg_match_all($header_pattern, $content, $matches);
    
    // If more than one header found
    if (count($matches[0]) > 1) {
        echo "Found duplicate headers in: $file_path\n";
        
        // Remove all headers
        $content = preg_replace($header_pattern, '', $content);
        
        // Add back only the first header
        $first_header = $matches[0][0];
        $content = $first_header . $content;
        
        // Write back to file
        if (file_put_contents($file_path, $content) !== false) {
            echo "Fixed duplicate headers in: $file_path\n";
            return true;
        } else {
            echo "Error writing to file: $file_path\n";
            return false;
        }
    }
    
    return false;
}

// Function to scan directory recursively
function scan_directory($dir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    $fixed_files = 0;
    
    foreach ($iterator as $file) {
        if ($file->isFile() && pathinfo($file->getPathname(), PATHINFO_EXTENSION) === 'php') {
            if (remove_duplicate_headers($file->getPathname())) {
                $fixed_files++;
            }
        }
    }
    
    return $fixed_files;
}

// Run the script
echo "Scanning for duplicate file header comments...\n";
echo "==========================================\n";

$fixed = scan_directory($directory);

echo "==========================================\n";
echo "Scan complete!\n";
echo "Fixed $fixed files with duplicate headers.\n";