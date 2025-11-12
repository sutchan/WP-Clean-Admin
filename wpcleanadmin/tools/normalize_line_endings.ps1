$ErrorActionPreference = 'Stop'

$exts = @('php','js','css','po','pot','md','txt')

Write-Host "[Start] Normalizing line endings and encoding to UTF-8 (no BOM)"

Get-ChildItem -Path (Get-Location) -Recurse -File | Where-Object {
    $e = $_.Extension.TrimStart('.')
    ($exts -contains $e) -and ($e -ne 'mo')
} | ForEach-Object {
    $path = $_.FullName
    try {
        $bytes = [System.IO.File]::ReadAllBytes($path)
        # strip UTF-8 BOM if present
        if ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
            $bytes = $bytes[3..($bytes.Length-1)]
        }

        $text = [System.Text.Encoding]::UTF8.GetString($bytes)
        $text = $text -replace "`r`n", "`n"
        $text = $text -replace "`r", "`n"

        [System.IO.File]::WriteAllText($path, $text, (New-Object System.Text.UTF8Encoding($false)))
        Write-Host "[OK]" $path
    }
    catch {
        Write-Host "[ERR]" $path "->" $_.Exception.Message
    }
}

Write-Host "[Done] Normalization completed"
