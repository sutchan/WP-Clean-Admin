# Simple batch file encoding converter

# Function to convert single file
function Convert-SingleFile($filePath) {
    # Skip binary files and directories
    if ((Get-Item $filePath) -isnot [System.IO.FileInfo]) {
        return
    }
    
    try {
        # Read file as bytes
        [byte[]]$content = [System.IO.File]::ReadAllBytes($filePath)
        
        # Check for BOM
        $hasBOM = $false
        if ($content.Length -ge 3 -and $content[0] -eq 0xEF -and $content[1] -eq 0xBB -and $content[2] -eq 0xBF) {
            $hasBOM = $true
            # Remove BOM
            [byte[]]$newContent = New-Object byte[] ($content.Length - 3)
            [Array]::Copy($content, 3, $newContent, 0, $newContent.Length)
            $content = $newContent
        }
        
        # Convert to string
        $text = [System.Text.Encoding]::UTF8.GetString($content)
        
        # Normalize line endings
        $text = $text -replace "\r\n", "\n" -replace "\r", "\n"
        
        # Write back as UTF-8 without BOM
        [System.IO.File]::WriteAllText($filePath, $text, [System.Text.UTF8Encoding]::new($false))
        
        Write-Output "Converted: $filePath"
    } catch {
        Write-Output "Error with: $filePath"
    }
}

# Main processing
Write-Output "Starting conversion..."

# Get all relevant files
$files = Get-ChildItem -Path . -Recurse -File -Include *.php,*.js,*.css,*.po,*.pot,*.md,*.txt | 
         Where-Object { $_.FullName -notmatch "\\\.(git|trae)\\\\" -and 
                        $_.FullName -notmatch "\\\/(node_modules|vendor|build|dist|tests)\\\\" }

# Process each file
foreach ($file in $files) {
    Convert-SingleFile $file.FullName
}

Write-Output "Conversion completed!"
