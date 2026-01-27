#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Compile PO files to MO files for WP Clean Admin plugin

This script compiles all PO translation files to MO files
which are required by WordPress for actual translation loading.
"""

import os
import polib

PLUGIN_DIR = 'wpcleanadmin'
LANGUAGES_DIR = os.path.join(PLUGIN_DIR, 'languages')

# Check if polib is installed
print("Checking for polib library...")
try:
    import polib
    print("polib is installed, proceeding...")
except ImportError:
    print("Error: polib library is not installed.")
    print("Please install it with: pip install polib")
    exit(1)

def compile_po_to_mo():
    """Compile all PO files to MO files"""
    print(f"Scanning {LANGUAGES_DIR} for PO files...")
    
    po_files = [f for f in os.listdir(LANGUAGES_DIR) if f.endswith('.po')]
    
    if not po_files:
        print("No PO files found.")
        return
    
    print(f"Found {len(po_files)} PO files:")
    for po_file in po_files:
        print(f"- {po_file}")
    
    for po_file in po_files:
        po_path = os.path.join(LANGUAGES_DIR, po_file)
        mo_path = os.path.join(LANGUAGES_DIR, po_file.replace('.po', '.mo'))
        
        print(f"\nCompiling {po_file} to {os.path.basename(mo_path)}...")
        
        try:
            # Read PO file
            po = polib.pofile(po_path)
            
            # Write MO file
            po.save_as_mofile(mo_path)
            
            print(f"✓ Compiled successfully: {os.path.basename(mo_path)}")
            print(f"  - Messages: {len(po)}")
            print(f"  - Fuzzy: {len([e for e in po if e.fuzzy])}")
            print(f"  - Untranslated: {len([e for e in po if not e.translated()])}")
            
        except Exception as e:
            print(f"✗ Error compiling {po_file}: {e}")

def main():
    """Main function"""
    print("=== WP Clean Admin - PO to MO Compiler ===")
    print()
    
    if not os.path.exists(LANGUAGES_DIR):
        print(f"Error: {LANGUAGES_DIR} directory not found.")
        return
    
    compile_po_to_mo()
    
    print()
    print("=== Compilation Complete ===")

if __name__ == '__main__':
    main()
