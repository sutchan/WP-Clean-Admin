#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Update POT file for WP Clean Admin plugin

This script scans all PHP and JavaScript files in the plugin directory
and extracts translatable strings using WordPress translation functions.
"""

import os
import re
import datetime

PLUGIN_DIR = 'wpcleanadmin'
LANGUAGES_DIR = os.path.join(PLUGIN_DIR, 'languages')
POT_FILE = os.path.join(LANGUAGES_DIR, 'wp-clean-admin.pot')

# Translation functions patterns
TRANSLATION_PATTERNS = {
    'php': [
        r'__\(["\'](.+?)["\'][,\s]*["\']wp-clean-admin["\']\)',
        r'_e\(["\'](.+?)["\'][,\s]*["\']wp-clean-admin["\']\)',
        r'_x\(["\'](.+?)["\'][,\s]*["\'](.+?)["\'][,\s]*["\']wp-clean-admin["\']\)',
        r'_n\(["\'](.+?)["\'][,\s]*["\'](.+?)["\'][,\s]*\$[a-zA-Z0-9_]+[,\s]*["\']wp-clean-admin["\']\)',
        r'_nx\(["\'](.+?)["\'][,\s]*["\'](.+?)["\'][,\s]*\$[a-zA-Z0-9_]+[,\s]*["\'](.+?)["\'][,\s]*["\']wp-clean-admin["\']\)',
    ],
    'js': [
        r'__\(["\'](.+?)["\'][,\s]*["\']wp-clean-admin["\']\)',
        r'_e\(["\'](.+?)["\'][,\s]*["\']wp-clean-admin["\']\)',
        r'_x\(["\'](.+?)["\'][,\s]*["\'](.+?)["\'][,\s]*["\']wp-clean-admin["\']\)',
        r'_n\(["\'](.+?)["\'][,\s]*["\'](.+?)["\'][,\s]*[0-9a-zA-Z_]+[,\s]*["\']wp-clean-admin["\']\)',
        r'_nx\(["\'](.+?)["\'][,\s]*["\'](.+?)["\'][,\s]*[0-9a-zA-Z_]+[,\s]*["\'](.+?)["\'][,\s]*["\']wp-clean-admin["\']\)',
    ]
}

def extract_strings(file_path):
    """Extract translatable strings from a file"""
    strings = []
    file_ext = os.path.splitext(file_path)[1].lstrip('.')
    
    if file_ext not in TRANSLATION_PATTERNS:
        return strings
    
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
    except Exception as e:
        print(f"Error reading {file_path}: {e}")
        return strings
    
    patterns = TRANSLATION_PATTERNS[file_ext]
    for pattern in patterns:
        matches = re.findall(pattern, content, re.DOTALL)
        for match in matches:
            if isinstance(match, tuple):
                # For functions with context or plural
                strings.append(match[0])
                if len(match) > 1:
                    strings.append(match[1])
            else:
                # For simple __() and _e()
                strings.append(match)
    
    return strings

def scan_directory(directory):
    """Scan directory for translatable strings"""
    all_strings = []
    
    for root, dirs, files in os.walk(directory):
        # Skip certain directories
        dirs[:] = [d for d in dirs if d not in ['vendor', 'node_modules', '.git']]
        
        for file in files:
            if file.endswith(('.php', '.js')):
                file_path = os.path.join(root, file)
                strings = extract_strings(file_path)
                all_strings.extend(strings)
    
    return list(set(all_strings))  # Remove duplicates

def generate_pot(strings):
    """Generate POT file content"""
    now = datetime.datetime.utcnow().strftime('%Y-%m-%d %H:%M+0000')
    
    pot_header = f'''
msgid ""
msgstr ""
"Project-Id-Version: WP Clean Admin 1.8.0\n"
"Report-Msgid-Bugs-To: https://github.com/sutchan/WP-Clean-Admin\n"
"POT-Creation-Date: {now}\n"
"PO-Revision-Date: \n"
"Last-Translator: \n"
"Language-Team: \n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"X-Generator: Custom Script\n"
"X-Poedit-Basepath: ..\n"
"X-Poedit-KeywordsList: __;_e;_x\n"
"X-Poedit-SearchPath-0: .\n"
'''
    
    pot_entries = []
    for string in sorted(strings):
        # Escape special characters
        escaped_string = string.replace('\\', '\\\\').replace('"', '\\"').replace('\n', '\\n')
        pot_entries.append(f'''
msgid "{escaped_string}"
msgstr ""
''')
    
    return pot_header + ''.join(pot_entries)

def main():
    """Main function"""
    print(f"Scanning {PLUGIN_DIR} for translatable strings...")
    strings = scan_directory(PLUGIN_DIR)
    print(f"Found {len(strings)} translatable strings")
    
    print(f"Generating {POT_FILE}...")
    pot_content = generate_pot(strings)
    
    # Create languages directory if it doesn't exist
    os.makedirs(LANGUAGES_DIR, exist_ok=True)
    
    # Write POT file
    try:
        with open(POT_FILE, 'w', encoding='utf-8') as f:
            f.write(pot_content)
        print(f"POT file updated successfully: {POT_FILE}")
    except Exception as e:
        print(f"Error writing POT file: {e}")

if __name__ == '__main__':
    main()
