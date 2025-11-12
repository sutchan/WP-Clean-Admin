$ErrorActionPreference = 'Stop'
$exts = @('php','js','css','po','pot','md','txt')
$targets = Get-ChildItem -Recurse -File | Where-Object { $exts -contains $_.Extension.TrimStart('.') }
$withCR = @()
foreach ($f in $targets) {
  $text = [System.IO.File]::ReadAllText($f.FullName)
  if ($text -match "\r") { $withCR += $f.FullName }
}
Write-Host "Files with CR present:" $withCR.Count
if ($withCR.Count -gt 0) { $withCR | ForEach-Object { Write-Host " -" $_ } }
