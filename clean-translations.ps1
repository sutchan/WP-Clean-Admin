<#
.SYNOPSIS
Clean redundant entries from translation files for WP Clean Admin plugin

.DESCRIPTION
This script removes redundant entries from PO files that are not present in the POT file

.EXAMPLE
.lean-translations.ps1

.NOTES
Author: Sut
Date: 2026-01-27
#>

# Define paths
$PluginDir = 'wpcleanadmin'
$LanguagesDir = Join-Path -Path $PluginDir -ChildPath 'languages'
$PotFile = Join-Path -Path $LanguagesDir -ChildPath 'wp-clean-admin.pot'
$PoFiles = @(
    # Join-Path -Path $LanguagesDir -ChildPath 'wp-clean-admin-en_US.po'
    Join-Path -Path $LanguagesDir -ChildPath 'wp-clean-admin-zh_CN.po'
)

Write-Host "=== WP Clean Admin - Translation Cleaner ==="
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

Write-Host "Cleaning translation files..."
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

# Parse POT file to get valid entries
Write-Host "Parsing POT file..."
$PotEntries = ParsePoFile -FilePath $PotFile
Write-Host "✓ Found $($PotEntries.Count) valid entries in POT file" -ForegroundColor Green
Write-Host ""

# Process each PO file
foreach ($PoFile in $PoFiles) {
    Write-Host "Processing $($PoFile.Name)..."
    
    # Read entire PO file content
    $OriginalContent = Get-Content -Path $PoFile -Encoding UTF8
    
    # Parse PO file to get current entries
    $PoEntries = ParsePoFile -FilePath $PoFile
    $OriginalCount = $PoEntries.Count
    
    # Identify redundant entries
    $RedundantEntries = @()
    foreach ($Msgid in $PoEntries.Keys) {
        if (-not $PotEntries.ContainsKey($Msgid)) {
            $RedundantEntries += $Msgid
        }
    }
    
    if ($RedundantEntries.Count -eq 0) {
        Write-Host "✓ No redundant entries found" -ForegroundColor Green
        Write-Host ""
        continue
    }
    
    Write-Host "⚠ Found $($RedundantEntries.Count) redundant entries to remove" -ForegroundColor Yellow
    
    # Clean up the content by removing redundant entries
    $CleanedContent = @()
    $InEntry = $false
    $CurrentMsgid = $null
    $SkipCurrentEntry = $false
    
    foreach ($Line in $OriginalContent) {
        $TrimmedLine = $Line.Trim()
        
        # Start of a new entry
        if ($TrimmedLine -match '^msgid "(.+)"$') {
            $CurrentMsgid = $matches[1]
            $InEntry = $true
            $SkipCurrentEntry = $RedundantEntries -contains $CurrentMsgid
            
            if (-not $SkipCurrentEntry) {
                $CleanedContent += $Line
            }
        }
        # Continue entry
        elseif ($InEntry) {
            if (-not $SkipCurrentEntry) {
                $CleanedContent += $Line
            }
            
            # End of entry (when we hit a blank line after msgstr)
            if ($TrimmedLine -eq '' -and $CurrentMsgid -ne $null) {
                $InEntry = $false
                $CurrentMsgid = $null
                $SkipCurrentEntry = $false
            }
        }
        # Non-entry lines (headers, comments, etc.)
        else {
            $CleanedContent += $Line
        }
    }
    
    # Write cleaned content back to file
    Set-Content -Path $PoFile -Value $CleanedContent -Encoding UTF8
    
    # Verify the cleanup
    $CleanedEntries = ParsePoFile -FilePath $PoFile
    $CleanedCount = $CleanedEntries.Count
    $RemovedCount = $OriginalCount - $CleanedCount
    
    Write-Host "✓ Cleaned up $RemovedCount redundant entries" -ForegroundColor Green
    Write-Host "✓ Original: $OriginalCount entries" -ForegroundColor Gray
    Write-Host "✓ Cleaned: $CleanedCount entries" -ForegroundColor Gray
    Write-Host ""
}

Write-Host "=== Cleaning Complete ==="
Write-Host ""
Write-Host "Recommendations:" -ForegroundColor Cyan
Write-Host "1. Recompile MO files after cleaning" -ForegroundColor Cyan
Write-Host "2. Verify translations still work correctly" -ForegroundColor Cyan
