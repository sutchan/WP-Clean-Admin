<#
.SYNOPSIS
Compile PO files to MO files for WP Clean Admin plugin

.DESCRIPTION
This script creates minimal MO files from PO files for WordPress translations

.EXAMPLE
.ompile-mo.ps1

.NOTES
Author: Sut
Date: 2026-01-27
#>

function ParsePoContent {
    param (
        [string]$PoContent
    )
    
    $Entries = @()
    $Lines = $PoContent -split "\r?\n"
    
    $CurrentEntry = @{}
    $InMsgid = $false
    $InMsgstr = $false
    
    foreach ($Line in $Lines) {
        $Line = $Line.Trim()
        
        # Skip comments and empty lines
        if ([string]::IsNullOrEmpty($Line) -or $Line.StartsWith('#')) {
            continue
        }
        
        # Start of new entry
        if ($Line -match '^msgid "(.+)"$') {
            # Save previous entry if exists
            if ($CurrentEntry.ContainsKey('msgid') -and $CurrentEntry.ContainsKey('msgstr')) {
                $Entries += $CurrentEntry
            }
            
            # Start new entry
            $CurrentEntry = @{}
            $CurrentEntry['msgid'] = $matches[1]
            $InMsgid = $true
            $InMsgstr = $false
        }
        # Continue msgid
        elseif ($InMsgid -and $Line -match '^"(.+)"$') {
            $CurrentEntry['msgid'] += $matches[1]
        }
        # Start of msgstr
        elseif ($Line -match '^msgstr "(.+)"$') {
            $CurrentEntry['msgstr'] = $matches[1]
            $InMsgid = $false
            $InMsgstr = $true
        }
        # Continue msgstr
        elseif ($InMsgstr -and $Line -match '^"(.+)"$') {
            $CurrentEntry['msgstr'] += $matches[1]
        }
    }
    
    # Save last entry
    if ($CurrentEntry.ContainsKey('msgid') -and $CurrentEntry.ContainsKey('msgstr')) {
        $Entries += $CurrentEntry
    }
    
    return $Entries
}

function CreateMinimalMo {
    param (
        [array]$Entries
    )
    
    # Create MO file header
    $MoHeader = New-Object System.Byte[] 28
    
    # Magic number: 0x950412de (big-endian)
    $MoHeader[0] = 0x95
    $MoHeader[1] = 0x04
    $MoHeader[2] = 0x12
    $MoHeader[3] = 0xde
    
    # Version: 0
    # Number of strings
    $StringCount = [BitConverter]::GetBytes($Entries.Count)
    [Array]::Reverse($StringCount)  # Convert to big-endian
    [Array]::Copy($StringCount, 0, $MoHeader, 8, 4)
    
    # Offset of original strings table
    $OriginalTableOffset = [BitConverter]::GetBytes(28)
    [Array]::Reverse($OriginalTableOffset)
    [Array]::Copy($OriginalTableOffset, 0, $MoHeader, 12, 4)
    
    # Calculate size of original strings table
    $OriginalStringsSize = 0
    foreach ($Entry in $Entries) {
        $OriginalStringsSize += $Entry['msgid'].Length + 1 + 8  # string + null + size + offset
    }
    
    # Offset of translated strings table
    $TranslatedTableOffset = [BitConverter]::GetBytes(28 + $OriginalStringsSize)
    [Array]::Reverse($TranslatedTableOffset)
    [Array]::Copy($TranslatedTableOffset, 0, $MoHeader, 16, 4)
    
    # Hash table size: 0
    # Hash table offset
    $HashTableOffset = [BitConverter]::GetBytes(28 + $OriginalStringsSize + $OriginalStringsSize)
    [Array]::Reverse($HashTableOffset)
    [Array]::Copy($HashTableOffset, 0, $MoHeader, 24, 4)
    
    # Create original strings table
    $OriginalTable = @()
    $TranslatedTable = @()
    $TranslatedOffset = 0
    
    foreach ($Entry in $Entries) {
        $Msgid = $Entry['msgid']
        $Msgstr = $Entry['msgstr']
        
        # Add to original table
        $MsgidLength = [BitConverter]::GetBytes($Msgid.Length)
        [Array]::Reverse($MsgidLength)
        $OriginalTable += $MsgidLength
        
        $MsgidOffset = [BitConverter]::GetBytes(28 + $OriginalStringsSize + $TranslatedOffset)
        [Array]::Reverse($MsgidOffset)
        $OriginalTable += $MsgidOffset
        
        $OriginalTable += [System.Text.Encoding]::UTF8.GetBytes($Msgid)
        $OriginalTable += 0x00  # Null terminator
        
        # Add to translated table
        $TranslatedTable += [System.Text.Encoding]::UTF8.GetBytes($Msgstr)
        $TranslatedTable += 0x00  # Null terminator
        $TranslatedOffset += $Msgstr.Length + 1
    }
    
    # Combine all parts
    $MoContent = $MoHeader + $OriginalTable + $TranslatedTable
    
    return $MoContent
}

# Define paths
$PluginDir = 'wpcleanadmin'
$LanguagesDir = Join-Path -Path $PluginDir -ChildPath 'languages'

# Check if directory exists
if (-not (Test-Path -Path $LanguagesDir -PathType Container)) {
    Write-Host "Error: Languages directory not found" -ForegroundColor Red
    exit 1
}

Write-Host "=== WP Clean Admin - PO to MO Compiler ==="
Write-Host ""

# Get all PO files
$PoFiles = Get-ChildItem -Path $LanguagesDir -Filter "*.po" -File

if ($PoFiles.Count -eq 0) {
    Write-Host "No PO files found" -ForegroundColor Red
    exit 1
}

Write-Host "Found $($PoFiles.Count) PO files:"
foreach ($PoFile in $PoFiles) {
    Write-Host "- $($PoFile.Name)"
}

Write-Host ""

# Process each PO file
foreach ($PoFile in $PoFiles) {
    $MoFile = Join-Path -Path $LanguagesDir -ChildPath ($PoFile.Name -replace '\.po$', '.mo')
    
    Write-Host "Creating $($MoFile.Name)..."
    
    try {
        # Read PO file
        $PoContent = Get-Content -Path $PoFile.FullName -Encoding UTF8 -Raw
        
        # Parse PO content
        $Entries = ParsePoContent -PoContent $PoContent
        
        if ($Entries.Count -eq 0) {
            Write-Host "  No entries found in $($PoFile.Name)" -ForegroundColor Yellow
            continue
        }
        
        # Create MO file
        $MoContent = CreateMinimalMo -Entries $Entries
        
        # Write MO file
        # Convert to byte array
        $ByteContent = [System.Byte[]]::new($MoContent.Length)
        for ($i = 0; $i -lt $MoContent.Length; $i++) {
            $ByteContent[$i] = $MoContent[$i]
        }
        # Write using .NET method to avoid PowerShell encoding issues
        [System.IO.File]::WriteAllBytes($MoFile, $ByteContent)
        
        Write-Host "✓ Created $($MoFile.Name)" -ForegroundColor Green
        Write-Host "  - Entries: $($Entries.Count)" -ForegroundColor Gray
        
    } catch {
        Write-Host "✗ Error creating $($MoFile.Name): $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=== Compilation Complete ==="
