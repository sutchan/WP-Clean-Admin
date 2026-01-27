<#
.SYNOPSIS
Analyze translation files for WP Clean Admin plugin

.DESCRIPTION
This script analyzes translation files to detect redundant strings and missing translations

.EXAMPLE
.nalyze-translations.ps1

.NOTES
Author: Sut
Date: 2026-01-27
#>

# Define paths
$PluginDir = 'wpcleanadmin'
$LanguagesDir = Join-Path -Path $PluginDir -ChildPath 'languages'
$PotFile = Join-Path -Path $LanguagesDir -ChildPath 'wp-clean-admin.pot'
$PoFiles = @(
    Join-Path -Path $LanguagesDir -ChildPath 'wp-clean-admin-en_US.po'
    Join-Path -Path $LanguagesDir -ChildPath 'wp-clean-admin-zh_CN.po'
)

Write-Host "=== WP Clean Admin - Translation Analysis ==="
Write-Host ""

# Check if files exist
if (-not (Test-Path -Path $PotFile -PathType Leaf)) {
    Write-Host "Error: POT file not found: $PotFile" -ForegroundColor Red
    exit 1
}

foreach ($PoFile in $PoFiles) {
    if (-not (Test-Path -Path $PoFile -PathType Leaf)) {
        Write-Host "Error: PO file not found: $PoFile" -ForegroundColor Red
        exit 1
    }
}

Write-Host "Analyzing translation files..."
Write-Host ""

# Function to parse PO/POT file
function ParsePoFile {
    param (
        [string]$FilePath
    )
    
    $Entries = @{}
    $Content = Get-Content -Path $FilePath -Encoding UTF8 -Raw
    $Lines = $Content -split "\r?\n"
    
    $CurrentMsgid = $null
    $CurrentMsgstr = $null
    $InMsgid = $false
    $InMsgstr = $false
    
    foreach ($Line in $Lines) {
        $Line = $Line.Trim()
        
        # Skip comments and empty lines
        if ([string]::IsNullOrEmpty($Line) -or $Line.StartsWith('#')) {
            continue
        }
        
        # Start of msgid
        if ($Line -match '^msgid "(.+)"$') {
            # Save previous entry
            if ($CurrentMsgid -ne $null) {
                $Entries[$CurrentMsgid] = $CurrentMsgstr
            }
            
            $CurrentMsgid = $matches[1]
            $CurrentMsgstr = $null
            $InMsgid = $true
            $InMsgstr = $false
        }
        # Continue msgid
        elseif ($InMsgid -and $Line -match '^"(.+)"$') {
            $CurrentMsgid += $matches[1]
        }
        # Start of msgstr
        elseif ($Line -match '^msgstr "(.+)"$') {
            $CurrentMsgstr = $matches[1]
            $InMsgid = $false
            $InMsgstr = $true
        }
        # Continue msgstr
        elseif ($InMsgstr -and $Line -match '^"(.+)"$') {
            $CurrentMsgstr += $matches[1]
        }
    }
    
    # Save last entry
    if ($CurrentMsgid -ne $null) {
        $Entries[$CurrentMsgid] = $CurrentMsgstr
    }
    
    return $Entries
}

# Parse POT file
Write-Host "Parsing POT file: $($PotFile.Name)..."
$PotEntries = ParsePoFile -FilePath $PotFile
Write-Host "✓ Found $($PotEntries.Count) entries in POT file" -ForegroundColor Green
Write-Host ""

# Parse and analyze each PO file
foreach ($PoFile in $PoFiles) {
    Write-Host "Analyzing $($PoFile.Name)..."
    $PoEntries = ParsePoFile -FilePath $PoFile
    
    # Calculate statistics
    $TotalEntries = $PoEntries.Count
    $TranslatedEntries = ($PoEntries.Values | Where-Object { $_ -ne $null -and $_ -ne '' }).Count
    $UntranslatedEntries = ($PoEntries.Values | Where-Object { $_ -eq $null -or $_ -eq '' }).Count
    
    # Check for redundant strings (not in POT)
    $RedundantEntries = @()
    foreach ($Msgid in $PoEntries.Keys) {
        if (-not $PotEntries.ContainsKey($Msgid)) {
            $RedundantEntries += $Msgid
        }
    }
    
    # Check for missing strings (in POT but not in PO)
    $MissingEntries = @()
    foreach ($Msgid in $PotEntries.Keys) {
        if (-not $PoEntries.ContainsKey($Msgid)) {
            $MissingEntries += $Msgid
        }
    }
    
    # Output results
    Write-Host "✓ Total entries: $TotalEntries" -ForegroundColor Green
    Write-Host "✓ Translated: $TranslatedEntries" -ForegroundColor Green
    Write-Host "✓ Untranslated: $UntranslatedEntries" -ForegroundColor Yellow
    
    if ($RedundantEntries.Count -gt 0) {
        Write-Host "⚠ Redundant entries (not in POT): $($RedundantEntries.Count)" -ForegroundColor Yellow
        if ($RedundantEntries.Count -le 10) {
            foreach ($Msgid in $RedundantEntries) {
                Write-Host "  - '$Msgid'" -ForegroundColor Gray
            }
        } elseif ($RedundantEntries.Count -gt 10) {
            Write-Host "  - (Showing first 10 of $($RedundantEntries.Count))" -ForegroundColor Gray
            foreach ($Msgid in $RedundantEntries | Select-Object -First 10) {
                Write-Host "  - '$Msgid'" -ForegroundColor Gray
            }
        }
    } else {
        Write-Host "✓ No redundant entries" -ForegroundColor Green
    }
    
    if ($MissingEntries.Count -gt 0) {
        Write-Host "⚠ Missing entries (in POT but not in PO): $($MissingEntries.Count)" -ForegroundColor Yellow
        if ($MissingEntries.Count -le 10) {
            foreach ($Msgid in $MissingEntries) {
                Write-Host "  - '$Msgid'" -ForegroundColor Gray
            }
        } elseif ($MissingEntries.Count -gt 10) {
            Write-Host "  - (Showing first 10 of $($MissingEntries.Count))" -ForegroundColor Gray
            foreach ($Msgid in $MissingEntries | Select-Object -First 10) {
                Write-Host "  - '$Msgid'" -ForegroundColor Gray
            }
        }
    } else {
        Write-Host "✓ No missing entries" -ForegroundColor Green
    }
    
    Write-Host ""
}

# Check for duplicate strings in POT
Write-Host "Checking for duplicate strings in POT file..."
$DuplicateStrings = @{}
foreach ($Msgid in $PotEntries.Keys) {
    if ($DuplicateStrings.ContainsKey($Msgid)) {
        $DuplicateStrings[$Msgid]++
    } else {
        $DuplicateStrings[$Msgid] = 1
    }
}

$ActualDuplicates = $DuplicateStrings | Where-Object { $_.Value -gt 1 }
if ($ActualDuplicates.Count -gt 0) {
    Write-Host "⚠ Found $($ActualDuplicates.Count) duplicate strings in POT:" -ForegroundColor Yellow
    foreach ($Duplicate in $ActualDuplicates.GetEnumerator()) {
        Write-Host "  - '$($Duplicate.Key)' (appears $($Duplicate.Value) times)" -ForegroundColor Gray
    }
} else {
    Write-Host "✓ No duplicate strings in POT" -ForegroundColor Green
}

Write-Host ""
Write-Host "=== Analysis Complete ==="
Write-Host ""
Write-Host "Recommendations:" -ForegroundColor Cyan
Write-Host "1. Remove redundant entries from PO files" -ForegroundColor Cyan
Write-Host "2. Add missing entries to PO files" -ForegroundColor Cyan
Write-Host "3. Remove duplicate strings from POT file" -ForegroundColor Cyan
Write-Host "4. Recompile MO files after making changes" -ForegroundColor Cyan
