#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Check and verify translation files for WP Clean Admin plugin

This script checks translation files structure and verifies WordPress requirements
"""

import os
import re

PLUGIN_DIR = 'wpcleanadmin'
LANGUAGES_DIR = os.path.join(PLUGIN_DIR, 'languages')

# WordPress locale codes
WP_LOCALES = {
    'en_US': 'English (United States)',
    'zh_CN': 'Chinese (Simplified)',
    'zh_TW': 'Chinese (Traditional)',
}

def check_translation_files():
    """Check translation files structure"""
    print("=== WP Clean Admin - Translation Files Check ===")
    print()
    
    if not os.path.exists(LANGUAGES_DIR):
        print(f"Error: {LANGUAGES_DIR} directory not found.")
        return
    
    print(f"Checking {LANGUAGES_DIR}...")
    print()
    
    # List all files in languages directory
    files = os.listdir(LANGUAGES_DIR)
    
    po_files = [f for f in files if f.endswith('.po')]
    mo_files = [f for f in files if f.endswith('.mo')]
    pot_files = [f for f in files if f.endswith('.pot')]
    
    print(f"Files found:")
    print(f"- PO files: {len(po_files)}")
    for f in po_files:
        print(f"  ✓ {f}")
    
    print(f"- MO files: {len(mo_files)}")
    for f in mo_files:
        print(f"  ✓ {f}")
    
    print(f"- POT files: {len(pot_files)}")
    for f in pot_files:
        print(f"  ✓ {f}")
    
    print()
    
    # Check for missing MO files
    print("=== Missing MO Files Check ===")
    missing_mo = []
    for po_file in po_files:
        mo_file = po_file.replace('.po', '.mo')
        if mo_file not in mo_files:
            missing_mo.append(po_file)
    
    if missing_mo:
        print("✗ Missing MO files for:")
        for f in missing_mo:
            print(f"  - {f}")
        print()
        print("Note: WordPress requires MO files to load translations.")
        print("You need to compile PO files to MO files.")
    else:
        print("✓ All PO files have corresponding MO files")
    
    print()
    
    # Check file naming convention
    print("=== File Naming Convention Check ===")
    for po_file in po_files:
        # WordPress expects pluginname-locale.po format
        match = re.match(r'^wp-clean-admin-([a-z]{2}_[A-Z]{2})\.po$', po_file)
        if match:
            locale = match.group(1)
            if locale in WP_LOCALES:
                print(f"✓ {po_file} - Valid naming, locale: {WP_LOCALES[locale]}")
            else:
                print(f"⚠ {po_file} - Valid format but unknown locale: {locale}")
        else:
            print(f"✗ {po_file} - Invalid naming convention")
            print("  Expected format: wp-clean-admin-locale.po (e.g., wp-clean-admin-en_US.po)")
    
    print()
    
    # Check PO file headers
    print("=== PO File Headers Check ===")
    for po_file in po_files:
        po_path = os.path.join(LANGUAGES_DIR, po_file)
        try:
            with open(po_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for required headers
            headers = {
                'Project-Id-Version': re.search(r'"Project-Id-Version: (.+?)\\n"', content),
                'Language': re.search(r'"Language: (.+?)\\n"', content),
                'Content-Type': re.search(r'"Content-Type: text/plain; charset=(.+)\\n"', content),
            }
            
            print(f"Checking {po_file}:")
            for header, match in headers.items():
                if match:
                    print(f"  ✓ {header}: {match.group(1)}")
                else:
                    print(f"  ✗ Missing {header}")
            
            # Check for charset
            if headers['Content-Type'] and 'UTF-8' in headers['Content-Type'].group(1):
                print("  ✓ Charset: UTF-8 (recommended)")
            else:
                print("  ⚠ Charset: Not UTF-8 (UTF-8 recommended)")
                
        except Exception as e:
            print(f"✗ Error reading {po_file}: {e}")
    
    print()
    
    # Check translation functions in PHP files
    print("=== Translation Functions Check ===")
    php_files = []
    for root, dirs, files in os.walk(PLUGIN_DIR):
        dirs[:] = [d for d in dirs if d not in ['vendor', 'node_modules', '.git', 'languages']]
        for file in files:
            if file.endswith('.php'):
                php_files.append(os.path.join(root, file))
    
    print(f"Scanning {len(php_files)} PHP files for translation functions...")
    
    translation_functions = {
        '__': 0,
        '_e': 0,
        '_x': 0,
        '_n': 0,
        '_nx': 0,
    }
    
    for php_file in php_files:
        try:
            with open(php_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            for func in translation_functions.keys():
                # Look for functions with text domain
                pattern = rf'{func}\(["\'](.+?)["\'][,\s]*["\']wp-clean-admin["\']\)'
                matches = re.findall(pattern, content)
                translation_functions[func] += len(matches)
                
        except Exception as e:
            pass
    
    total = sum(translation_functions.values())
    print(f"Found {total} translation function calls:")
    for func, count in translation_functions.items():
        if count > 0:
            print(f"  - {func}: {count}")
    
    if total == 0:
        print("⚠ No translation function calls found with text domain 'wp-clean-admin'")
    
    print()
    print("=== Summary ===")
    if len(missing_mo) > 0:
        print(f"✗ Missing {len(missing_mo)} MO files")
    else:
        print("✓ All MO files present")
    
    print(f"✓ {len(po_files)} PO files found")
    print(f"✓ {len(pot_files)} POT files found")
    print(f"✓ {total} translation function calls found")
    
    print()
    print("=== WordPress Translation Requirements ===")
    print("1. PO files must be compiled to MO files")
    print("2. Files must follow naming convention: pluginname-locale.po/mo")
    print("3. Text domain must match plugin header: 'wp-clean-admin'")
    print("4. load_plugin_textdomain() must be called on 'plugins_loaded' hook")
    print("5. Translation functions must include text domain: __('text', 'wp-clean-admin')")

def main():
    """Main function"""
    check_translation_files()

if __name__ == '__main__':
    main()
