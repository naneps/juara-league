#!/usr/bin/env pwsh
# serve.ps1 - Alternatif php artisan serve untuk Herd Lite
# Usage: .\serve.ps1           -> http://127.0.0.1:8000
# Usage: .\serve.ps1 8080      -> http://127.0.0.1:8080

param(
    [int]$Port = 8000,
    [string]$Address = "127.0.0.1"
)

$phpBin = (Get-Command php).Source
$publicDir = Join-Path $PSScriptRoot "public"
$routerScript = Join-Path $publicDir "index.php"

Write-Host ""
Write-Host "  INFO  Server running on [http://$($Address):$Port]." -ForegroundColor Cyan
Write-Host "  Press Ctrl+C to stop the server." -ForegroundColor Gray
Write-Host ""

& $phpBin -S "$($Address):$Port" -t $publicDir $routerScript
