$ErrorActionPreference = 'Stop'

$hostsPath = Join-Path $env:SystemRoot 'System32\drivers\etc\hosts'
$hostname = 'msu-eva.test'
$hostsContent = Get-Content -LiteralPath $hostsPath

if ($hostsContent -notmatch "(?m)^\s*127\.0\.0\.1\s+$([regex]::Escape($hostname))(?:\s|$)") {
    Add-Content -LiteralPath $hostsPath -Value "`r`n127.0.0.1 $hostname"
}

ipconfig /flushdns | Out-Null
Write-Output "Configured $hostname in $hostsPath"
