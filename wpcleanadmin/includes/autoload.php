<?php

/**
 * PSR-4 Autoloader for WPCleanAdmin plugin
 * 
 * @file wpcleanadmin/includes/autoload.php
 * @version 1.7.15
 * @updated 2025-11-28
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * WPCA_Autoloader class
 * Implements PSR-4 autoloading for the plugin
 */
class WPCA_Autoloader {
    
    /**
     * Base directory for the plugin
     * 
     * @var string
     */
    private $base_dir;
    
    /**
     * Class map for faster loading
     * 
     * @var array
     */
    private $class_map;
    
    /**
     * Constructor
     * 
     * @param string $base_dir Base directory for the plugin
     */
    public function __construct($base_dir) {
        $this->base_dir = $base_dir;
        $this->class_map = $this->generate_class_map();
    }
    
    /**
     * Generate class map for all PHP files in the includes directory
     * 
     * @return array Class map array
     */
    private function generate_class_map() {
        $class_map = array();
        $includes_dir = $this->base_dir . '/includes';
        
        // Check if includes directory exists
        if (! file_exists($includes_dir)) {
            return $class_map;
        }
        
        // Scan includes directory for PHP files
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($includes_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $file_path = $file->getPathname();
                $relative_path = str_replace($includes_dir . '/', '', $file_path);
                
                // Convert file path to class name according to PSR-4
                $class_name = $this->file_path_to_class_name($relative_path);
                $class_map[$class_name] = $file_path;
            }
        }
        
        return $class_map;
    }
    
    /**
     * Convert file path to class name according to PSR-4
     * 
     * @param string $file_path Relative file path
     * @return string Class name
     */
    private function file_path_to_class_name($file_path) {
        // Remove .php extension
        $file_path = str_replace('.php', '', $file_path);
        
        // Convert class-wpca-*.php to WPCA_* format
        if (strpos($file_path, 'class-wpca-') === 0) {
            $class_name = str_replace('class-wpca-', 'WPCA_', $file_path);
            $class_name = str_replace('-', '_', $class_name);
            $class_name = ucwords(str_replace('_', ' ', $class_name));
            $class_name = str_replace(' ', '', $class_name);
        } else {
            // For other files, use standard PSR-4 conversion
            $class_name = str_replace('/', '\\', $file_path);
            $class_name = ucfirst($class_name);
        }
        
        return $class_name;
    }
    
    /**
     * Autoload function
     * 
     * @param string $class Class name to load
     * @return bool True if class was loaded, false otherwise
     */
    public function autoload($class) {
        // Check if class is in our namespace
        if (strpos($class, 'WPCA_') === 0) {
            // Check if class is in our class map
            if (isset($this->class_map[$class])) {
                require_once $this->class_map[$class];
                return true;
            }
            
            // If not in class map, try to load it dynamically
            $class_file = $this->class_name_to_file_path($class);
            $file_path = $this->base_dir . '/includes/' . $class_file;
            
            if (file_exists($file_path)) {
                require_once $file_path;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Convert class name to file path
     * 
     * @param string $class Class name
     * @return string File path
     */
    private function class_name_to_file_path($class) {
        // Convert WPCA_* to class-wpca-*.php format
        $file_path = str_replace('WPCA_', 'class-wpca-', $class);
        $file_path = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $file_path));
        $file_path .= '.php';
        
        return $file_path;
    }
    
    /**
     * Register the autoloader
     */
    public function register() {
        spl_autoload_register(array($this, 'autoload'));
    }
    
    /**
     * Unregister the autoloader
     */
    public function unregister() {
        spl_autoload_unregister(array($this, 'autoload'));
    }
}

/**
 * Initialize the autoloader
 */
function wpca_init_autoloader() {
    if (defined('WPCA_PLUGIN_DIR')) {
        $autoloader = new WPCA_Autoloader(WPCA_PLUGIN_DIR);
        $autoloader->register();
    }
}

// Initialize the autoloader
wpca_init_autoloader();