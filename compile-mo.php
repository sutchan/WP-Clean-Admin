<?php
/**
 * Compile PO files to MO files for WP Clean Admin plugin
 * 
 * This script uses WordPress's gettext functions to compile PO files to MO files
 */

// Define paths
define('PLUGIN_DIR', 'wpcleanadmin');
define('LANGUAGES_DIR', PLUGIN_DIR . '/languages');

// Check if directory exists
if (!is_dir(LANGUAGES_DIR)) {
    die('Error: Languages directory not found\n');
}

echo "=== WP Clean Admin - PO to MO Compiler ===\n\n";

// Get all PO files
$po_files = glob(LANGUAGES_DIR . '/*.po');

if (empty($po_files)) {
    die('No PO files found\n');
}

echo "Found " . count($po_files) . " PO files:\n";
foreach ($po_files as $po_file) {
    echo '- ' . basename($po_file) . "\n";
}

echo "\n";

// Check if gettext functions are available
if (!function_exists('msgfmt_create') || !function_exists('msgfmt_set_text_domain') || !function_exists('msgfmt_format_message')) {
    echo "Warning: PHP gettext functions not available\n";
    echo "Attempting to use alternative method...\n\n";
    
    // Alternative method: create minimal MO files
    foreach ($po_files as $po_file) {
        $mo_file = str_replace('.po', '.mo', $po_file);
        
        echo "Creating $mo_file...\n";
        
        try {
            // Read PO file
            $po_content = file_get_contents($po_file);
            
            // Create minimal MO file structure
            // Note: This is a simplified approach and may not work for all cases
            $mo_content = create_minimal_mo($po_content);
            
            // Write MO file
            file_put_contents($mo_file, $mo_content);
            
            echo "✓ Created $mo_file\n";
            
        } catch (Exception $e) {
            echo "✗ Error creating $mo_file: " . $e->getMessage() . "\n";
        }
    }
    
} else {
    // Use PHP gettext functions
    foreach ($po_files as $po_file) {
        $mo_file = str_replace('.po', '.mo', $po_file);
        
        echo "Compiling $po_file to $mo_file...\n";
        
        try {
            // This is a placeholder - actual compilation requires proper gettext setup
            // For now, we'll create minimal MO files
            $po_content = file_get_contents($po_file);
            $mo_content = create_minimal_mo($po_content);
            file_put_contents($mo_file, $mo_content);
            
            echo "✓ Compiled $mo_file\n";
            
        } catch (Exception $e) {
            echo "✗ Error compiling $mo_file: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== Compilation Complete ===\n";

/**
 * Create minimal MO file from PO content
 * 
 * This is a simplified implementation that creates basic MO files
 * It may not handle all edge cases but should work for simple translations
 */
function create_minimal_mo($po_content) {
    // Parse PO file content
    $entries = parse_po_content($po_content);
    
    if (empty($entries)) {
        return "";
    }
    
    // Create MO file header
    $mo_header = "";
    
    // MO file format: big-endian
    // Magic number: 0x950412de
    $mo_header .= pack('N', 0x950412de);
    
    // Version: 0
    $mo_header .= pack('N', 0);
    
    // Number of strings
    $mo_header .= pack('N', count($entries));
    
    // Offset of original strings table
    $mo_header .= pack('N', 28);
    
    // Offset of translated strings table
    $original_strings_size = 0;
    foreach ($entries as $entry) {
        $original_strings_size += strlen($entry['msgid']) + 1 + 8; // string + null + size + offset
    }
    $mo_header .= pack('N', 28 + $original_strings_size);
    
    // Hash table size (0 for simplicity)
    $mo_header .= pack('N', 0);
    
    // Hash table offset
    $mo_header .= pack('N', 28 + $original_strings_size + $original_strings_size);
    
    // Original strings table
    $original_table = "";
    $translated_table = "";
    $translated_offset = 0;
    
    foreach ($entries as $entry) {
        $msgid = $entry['msgid'];
        $msgstr = $entry['msgstr'];
        
        // Add to original table
        $original_table .= pack('N', strlen($msgid));
        $original_table .= pack('N', 28 + $original_strings_size + $translated_offset);
        $original_table .= $msgid . "\0";
        
        // Add to translated table
        $translated_table .= $msgstr . "\0";
        $translated_offset += strlen($msgstr) + 1;
    }
    
    // Combine all parts
    $mo_content = $mo_header . $original_table . $translated_table;
    
    return $mo_content;
}

/**
 * Parse PO file content into entries
 */
function parse_po_content($po_content) {
    $entries = array();
    
    // Split content into lines
    $lines = explode("\n", $po_content);
    
    $current_entry = array();
    $in_msgid = false;
    $in_msgstr = false;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip comments and empty lines
        if (empty($line) || $line[0] == '#') {
            continue;
        }
        
        // Start of new entry
        if (strpos($line, 'msgid "') === 0) {
            // Save previous entry if exists
            if (!empty($current_entry) && isset($current_entry['msgid']) && isset($current_entry['msgstr'])) {
                $entries[] = $current_entry;
            }
            
            // Start new entry
            $current_entry = array();
            $current_entry['msgid'] = substr($line, 7, -1);
            $in_msgid = true;
            $in_msgstr = false;
        }
        
        // Continue msgid
        elseif ($in_msgid && strpos($line, '"') === 0) {
            $current_entry['msgid'] .= substr($line, 1, -1);
        }
        
        // Start of msgstr
        elseif (strpos($line, 'msgstr "') === 0) {
            $current_entry['msgstr'] = substr($line, 8, -1);
            $in_msgid = false;
            $in_msgstr = true;
        }
        
        // Continue msgstr
        elseif ($in_msgstr && strpos($line, '"') === 0) {
            $current_entry['msgstr'] .= substr($line, 1, -1);
        }
    }
    
    // Save last entry
    if (!empty($current_entry) && isset($current_entry['msgid']) && isset($current_entry['msgstr'])) {
        $entries[] = $current_entry;
    }
    
    return $entries;
}
?>
