<?php
/**
 * WP Clean Admin Translation Debug Tool
 * This script helps diagnose translation issues and provides useful debugging information
 */

// Ensure we're being called from WordPress
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all available translation files in the languages directory
 * @return array List of available language files with their paths
 */
function wpca_get_available_translations() {
    $domain = 'wp-clean-admin';
    $lang_dir = function_exists('plugin_dir_path') ? plugin_dir_path(__FILE__) . 'languages/' : dirname(__FILE__) . '/languages/';
    $available = array();
    
    if (is_dir($lang_dir)) {
        $files = scandir($lang_dir);
        foreach ($files as $file) {
            if (preg_match('/^' . $domain . '-(.+?)\.(po|mo)$/', $file, $matches)) {
                $locale = $matches[1];
                $type = $matches[2];
                if (!isset($available[$locale])) {
                    $available[$locale] = array();
                }
                $available[$locale][$type] = $lang_dir . $file;
            }
        }
    }
    
    return $available;
}

/**
 * Read and parse a PO file to extract translations
 * @param string $file_path Path to the PO file
 * @return array Array of translations from the PO file
 */
function wpca_read_po_file($file_path) {
    $translations = array();
    
    if (file_exists($file_path) && is_readable($file_path)) {
        $content = file_get_contents($file_path);
        
        // Simple PO file parsing (not comprehensive, but sufficient for debug purposes)
        preg_match_all('/msgid\s+"(.*?)"\s+msgstr\s+"(.*?)"/s', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            if (isset($match[1]) && isset($match[2])) {
                $translations[$match[1]] = $match[2];
            }
        }
    }
    
    return $translations;
}

/**
 * Get a list of common strings to test translations
 * @return array List of strings for translation testing
 */
function wpca_get_test_strings() {
    return array(
        // Core plugin strings
        'WP Clean Admin',
        'WP Clean Admin Settings',
        'General',
        'Settings',
        'Visual Style',
        'Login Page',
        'Menu Customizer',
        'Dashboard Cleanup',
        
        // Navigation and actions
        'Save Changes',
        'Reset Defaults',
        'Apply Changes',
        'Back to Top',
        
        // Error and status messages
        'Invalid request',
        'Security verification failed',
        'Settings saved successfully',
        'An error occurred while saving settings',
        'Request processing failed',
        'You do not have permission to perform this action',
        'Invalid request parameters',
        'Please log in first',
        'Internal server error',
        
        // Menu customization
        'Menu Item',
        'Add Menu Item',
        'Remove Menu Item',
        'Drag to reorder',
        
        // Login page settings
        'Login Logo',
        'Background Color',
        'Custom CSS',
        'No logo selected',
        
        // Dashboard cleanup
        'Remove Dashboard Widgets',
        'Remove Welcome Panel',
        'Remove Help Tab',
        
        // Permissions
        'Insufficient permissions',
        'WPCA Permissions: Missing required configuration'
    );
}

/**
 * Debug function for translation issues
 * Outputs detailed translation debugging information
 * @return string HTML content for the debug page
 */
function wpca_translation_debug() {
    // Start output buffer
    ob_start();
    
    // Get domain and current locale
    $domain = 'wp-clean-admin';
    $current_locale = function_exists('get_locale') ? get_locale() : 'en_US';
    $lang_dir = function_exists('plugin_dir_path') ? plugin_dir_path(__FILE__) . 'languages/' : dirname(__FILE__) . '/languages/';
    $available_translations = wpca_get_available_translations();
    
    // Get test strings
    $test_strings = wpca_get_test_strings();
    
    // Get current translation file contents
    $po_file_path = $lang_dir . $domain . '-' . $current_locale . '.po';
    $current_translations = file_exists($po_file_path) ? wpca_read_po_file($po_file_path) : array();
    
    echo '<div class="wrap">';
    echo '<h1>WP Clean Admin Translation Debug</h1>';
    
    // Add a header note
    echo '<div class="notice notice-info">';
    echo '<p>This tool helps diagnose translation issues with WP Clean Admin plugin. Check the sections below for detailed information.</p>';
    echo '</div>';
    
    // WordPress Language Settings
    echo '<div class="card">';
    echo '<h2 class="title">Current WordPress Language Settings</h2>';
    echo '<div class="inside">';
    
    // Display current locale
    echo '<table class="widefat fixed">';
    echo '<tbody>';
    echo '<tr class="alternate">';
    echo '<td><strong>Current Locale:</strong></td>';
    echo '<td>' . $current_locale . '</td>';
    echo '</tr>';
    
    // Check if WPLANG constant is defined
    if (defined('WPLANG')) {
        echo '<tr>';
        echo '<td><strong>WPLANG Constant:</strong></td>';
        echo '<td>' . WPLANG . '</td>';
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<td><strong>WPLANG Constant:</strong></td>';
        echo '<td>Not defined (using site settings)</td>';
        echo '</tr>';
    }
    
    // Get WordPress version
    if (function_exists('get_bloginfo')) {
        echo '<tr class="alternate">';
        echo '<td><strong>WordPress Version:</strong></td>';
        echo '<td>' . get_bloginfo('version') . '</td>';
        echo '</tr>';
    }
    
    // Get PHP version
    echo '<tr>';
    echo '<td><strong>PHP Version:</strong></td>';
    echo '<td>' . phpversion() . '</td>';
    echo '</tr>';
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
    
    // Translation File Status
    echo '<div class="card" style="margin-top: 20px;">';
    echo '<h2 class="title">Translation File Status</h2>';
    echo '<div class="inside">';
    
    // Check if text domain is registered
    $is_loaded = function_exists('is_textdomain_loaded') ? is_textdomain_loaded($domain) : false;
    $loaded_text = $is_loaded ? '<span style="color: green;">Yes</span>' : '<span style="color: red;">No</span>';
    
    echo '<table class="widefat fixed">';
    echo '<tbody>';
    echo '<tr class="alternate">';
    echo '<td><strong>Text Domain Loaded:</strong></td>';
    echo '<td>' . $loaded_text . '</td>';
    echo '</tr>';
    
    // Get the language directory path
    echo '<tr>';
    echo '<td><strong>Language Directory:</strong></td>';
    echo '<td>' . $lang_dir . '</td>';
    echo '</tr>';
    
    // Check if .mo and .po files exist for current locale
    $mo_file = $lang_dir . $domain . '-' . $current_locale . '.mo';
    $po_file = $lang_dir . $domain . '-' . $current_locale . '.po';
    
    echo '<tr class="alternate">';
    echo '<td><strong>MO File Path:</strong></td>';
    echo '<td>' . $mo_file . '</td>';
    echo '</tr>';
    
    $mo_exists = file_exists($mo_file);
    $mo_text = $mo_exists ? '<span style="color: green;">Yes</span>' : '<span style="color: red;">No</span>';
    echo '<tr>';
    echo '<td><strong>MO File Exists:</strong></td>';
    echo '<td>' . $mo_text . '</td>';
    echo '</tr>';
    
    if ($mo_exists) {
        echo '<tr class="alternate">';
        echo '<td><strong>MO File Size:</strong></td>';
        echo '<td>' . (function_exists('filesize') ? filesize($mo_file) : 'N/A') . ' bytes</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td><strong>MO File Modified:</strong></td>';
        echo '<td>' . (function_exists('filemtime') && function_exists('date') ? date('Y-m-d H:i:s', filemtime($mo_file)) : 'N/A') . '</td>';
        echo '</tr>';
    }
    
    echo '<tr class="alternate">';
    echo '<td><strong>PO File Path:</strong></td>';
    echo '<td>' . $po_file . '</td>';
    echo '</tr>';
    
    $po_exists = file_exists($po_file);
    $po_text = $po_exists ? '<span style="color: green;">Yes</span>' : '<span style="color: red;">No</span>';
    echo '<tr>';
    echo '<td><strong>PO File Exists:</strong></td>';
    echo '<td>' . $po_text . '</td>';
    echo '</tr>';
    
    if ($po_exists) {
        echo '<tr class="alternate">';
        echo '<td><strong>PO File Size:</strong></td>';
        echo '<td>' . (function_exists('filesize') ? filesize($po_file) : 'N/A') . ' bytes</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td><strong>PO File Modified:</strong></td>';
        echo '<td>' . (function_exists('filemtime') && function_exists('date') ? date('Y-m-d H:i:s', filemtime($po_file)) : 'N/A') . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
    
    // Available Translations
    if (!empty($available_translations)) {
        echo '<div class="card" style="margin-top: 20px;">';
        echo '<h2 class="title">Available Translations</h2>';
        echo '<div class="inside">';
        
        echo '<table class="widefat fixed">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Locale</th>';
        echo '<th>Language</th>';
        echo '<th>PO File</th>';
        echo '<th>MO File</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        $locales = array(
            'en_US' => 'English (US)',
            'zh_CN' => 'Chinese (Simplified)',
        );
        
        $row_class = 'alternate';
        foreach ($available_translations as $locale => $files) {
            echo '<tr class="' . $row_class . '">';
            echo '<td>' . $locale . '</td>';
            echo '<td>' . (isset($locales[$locale]) ? $locales[$locale] : 'Unknown') . '</td>';
            echo '<td>' . (isset($files['po']) ? '<span style="color: green;">✓</span>' : '<span style="color: red;">✗</span>') . '</td>';
            echo '<td>' . (isset($files['mo']) ? '<span style="color: green;">✓</span>' : '<span style="color: red;">✗</span>') . '</td>';
            echo '</tr>';
            $row_class = $row_class === 'alternate' ? '' : 'alternate';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }
    
    // Translation Test
    echo '<div class="card" style="margin-top: 20px;">';
    echo '<h2 class="title">Translation Test</h2>';
    echo '<div class="inside">';
    
    // Test translation functions
    echo '<p><strong>Direct Translation Test (for current locale):</strong></p>';
    echo '<div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">';
    echo '<table class="widefat fixed">';
    echo '<thead>';
    echo '<tr>';
    echo '<th width="40%">Original String</th>';
    echo '<th width="60%">Translated Result</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    $row_class = 'alternate';
    foreach ($test_strings as $string) {
        $translated = function_exists('__') ? __('' . $string, $domain) : $string;
        $highlight = ($translated === $string && $current_locale !== 'en_US') ? ' style="background-color: #fff9c4;"' : '';
        
        echo '<tr class="' . $row_class . '"' . $highlight . '>';
        echo '<td><code>' . htmlspecialchars($string) . '</code></td>';
        echo '<td>' . htmlspecialchars($translated) . '</td>';
        echo '</tr>';
        $row_class = $row_class === 'alternate' ? '' : 'alternate';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '<p style="margin-top: 10px; font-style: italic; color: #666;">Note: Yellow highlighted rows indicate strings that might not be translated correctly.</p>';
    echo '</div>';
    echo '</div>';
    
    // Manual Language Switch
    echo '<div class="card" style="margin-top: 20px;">';
    echo '<h2 class="title">Manual Language Switch</h2>';
    echo '<div class="inside">';
    echo '<p>You can temporarily switch the language for debugging purposes:</p>';
    
    // Simple form to test different languages
    echo '<form method="post" style="margin-top: 15px;">';
    echo '<input type="hidden" name="wpca_lang_debug" value="1">';
    echo '<select name="wpca_test_lang">';
    echo '<option value="en_US"' . ($current_locale === 'en_US' ? ' selected' : '') . '>English (US)</option>';
    echo '<option value="zh_CN"' . ($current_locale === 'zh_CN' ? ' selected' : '') . '>Chinese (Simplified)</option>';
    echo '</select>';
    echo '<input type="submit" value="Test Language" class="button button-primary" style="margin-left: 10px;">';
    echo '</form>';
    echo '</div>';
    echo '</div>';
    
    // Translation File Content Preview
    if ($po_exists) {
        echo '<div class="card" style="margin-top: 20px;">';
        echo '<h2 class="title">Current PO File Content Preview</h2>';
        echo '<div class="inside">';
        
        echo '<p><strong>First 20 translated strings in ' . basename($po_file) . ':</strong></p>';
        echo '<div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9; font-family: monospace;">';
        
        $counter = 0;
        foreach ($current_translations as $msgid => $msgstr) {
            if ($counter >= 20) break;
            echo 'msgid: "' . htmlspecialchars($msgid) . '"<br>';
            echo 'msgstr: "' . htmlspecialchars($msgstr) . '"<br><br>';
            $counter++;
        }
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    // Troubleshooting Tips
    echo '<div class="card" style="margin-top: 20px;">';
    echo '<h2 class="title">Troubleshooting Tips</h2>';
    echo '<div class="inside">';
    
    echo '<ul style="list-style-type: disc; padding-left: 20px;">';
    echo '<li>If text domain is not loaded, check that the plugin is properly calling <code>load_plugin_textdomain()</code></li>';
    echo '<li>Ensure your .mo and .po files are in the correct language directory</li>';
    echo '<li>Verify that your translation files are properly named (e.g., <code>wp-clean-admin-zh_CN.mo</code>)</li>';
    echo '<li>Use the <code>generate_mo.php</code> script in the languages directory to regenerate .mo files if needed</li>';
    echo '<li>Check file permissions to ensure WordPress can read the translation files</li>';
    echo '</ul>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
    
    return ob_get_clean();
}

/**
 * Handle language test form submission
 * Processes the language change request and reloads the text domain
 */
function wpca_handle_lang_debug() {
    if (isset($_POST['wpca_lang_debug']) && $_POST['wpca_lang_debug'] == 1 && isset($_POST['wpca_test_lang'])) {
        $test_lang = function_exists('sanitize_text_field') ? sanitize_text_field($_POST['wpca_test_lang']) : filter_var($_POST['wpca_test_lang'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        
        // This is a temporary filter to override the locale for debugging
        if (function_exists('add_filter')) {
            add_filter('locale', function() use ($test_lang) {
                return $test_lang;
            });
        }
        
        // Deactivate and reactivate the text domain
        $domain = 'wp-clean-admin';
        $lang_dir = function_exists('plugin_dir_path') ? plugin_dir_path(__FILE__) . 'languages/' : dirname(__FILE__) . '/languages/';
        
        if (function_exists('is_textdomain_loaded') && is_textdomain_loaded($domain) && function_exists('unload_textdomain')) {
            unload_textdomain($domain);
        }
        
        if (function_exists('load_plugin_textdomain')) {
            load_plugin_textdomain($domain, false, $lang_dir);
        }
        
        // Add an admin notice
        if (function_exists('add_action')) {
            add_action('admin_notices', function() use ($test_lang) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>Language temporarily set to ' . $test_lang . ' for debugging.</p>';
                echo '</div>';
            });
        }
    }
}

/**
 * Add the debug page to the admin menu
 * Registers the translation debug page in WordPress admin area
 */
function wpca_add_translation_debug_page() {
    if (function_exists('add_submenu_page')) {
        add_submenu_page(
            'options-general.php',
            'WP Clean Admin Translation Debug',
            'WPCA Translation Debug',
            'manage_options',
            'wpca-translation-debug',
            'wpca_translation_debug'
        );
    }
}

// Hook into admin menu and form handling
if (function_exists('add_action')) {
    add_action('admin_menu', 'wpca_add_translation_debug_page');
    add_action('admin_init', 'wpca_handle_lang_debug');
}
?>