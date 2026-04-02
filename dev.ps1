param(
    [int]$ApiPort = 8000,
    [string]$ApiAddress = "127.0.0.1",
    [int]$WebPort = 3000
)

$Root     = $PSScriptRoot
$ApiDir   = Join-Path $Root "juara-league-api"
$WebDir   = Join-Path $Root "juara-league-web"

# ── Pastikan dependency sudah terinstall ──────────────────────────────────────
$vendorExists = Test-Path (Join-Path $ApiDir "vendor")
$nodeModWeb   = Test-Path (Join-Path $WebDir "node_modules")

if (-not $vendorExists) {
    Write-Host "  [WARN] vendor/ tidak ditemukan. Jalankan .\install.ps1 terlebih dahulu." -ForegroundColor Yellow
    exit 1
}
if (-not $nodeModWeb) {
    Write-Host "  [WARN] node_modules (web) tidak ditemukan. Jalankan .\install.ps1 terlebih dahulu." -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "  =============================================" -ForegroundColor Cyan
Write-Host "       Juara League - Dev Server               " -ForegroundColor Cyan
Write-Host "  =============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Backend  -> http://$($ApiAddress):$ApiPort" -ForegroundColor Green
Write-Host "  Frontend -> http://localhost:$WebPort" -ForegroundColor Blue
Write-Host ""
Write-Host "  Tekan Ctrl+C untuk menghentikan semua server." -ForegroundColor Gray
Write-Host ""

# ── Validasi PHP ──────────────────────────────────────────────────────────────
$phpCmd = Get-Command php -ErrorAction SilentlyContinue
$phpBin = if ($phpCmd) { $phpCmd.Source } else { $null }

if (-not $phpBin) {
    Write-Host "  [ERROR] PHP tidak ditemukan di PATH." -ForegroundColor Red
    exit 1
}

# ── Fungsi cleanup: matikan semua job saat Ctrl+C ────────────────────────────
function Stop-AllJobs {
    Write-Host ""
    Write-Host "  Menghentikan semua server..." -ForegroundColor Yellow
    Get-Job | Stop-Job
    Get-Job | Remove-Job
    Write-Host "  Semua server telah dihentikan." -ForegroundColor Gray
}

# ── Start Backend (Laravel via php -S) ───────────────────────────────────────
$publicDir    = Join-Path $ApiDir "public"
$routerScript = Join-Path $publicDir "index.php"

$apiJob = Start-Job -Name "Backend" -ScriptBlock {
    param($php, $addr, $port, $pubDir, $router)
    & $php -S "$($addr):$port" -t $pubDir $router 2>&1
} -ArgumentList $phpBin, $ApiAddress, $ApiPort, $publicDir, $routerScript

# ── Start Frontend (Nuxt dev) ─────────────────────────────────────────────────
$webJob = Start-Job -Name "Frontend" -ScriptBlock {
    param($dir, $port)
    Set-Location $dir
    & npm run dev -- --port $port 2>&1
} -ArgumentList $WebDir, $WebPort

Write-Host "  [INFO] Menunggu server siap..." -ForegroundColor Gray
Start-Sleep -Seconds 2
Write-Host ""

# ── Stream output kedua job ───────────────────────────────────────────────────
try {
    while ($true) {
        $apiOut = Receive-Job -Job $apiJob
        foreach ($line in $apiOut) {
            if ($line) { Write-Host "  [Backend ] $line" -ForegroundColor Green }
        }

        $webOut = Receive-Job -Job $webJob
        foreach ($line in $webOut) {
            if ($line) { Write-Host "  [Frontend] $line" -ForegroundColor Blue }
        }

        if ($apiJob.State -eq 'Failed') {
            Write-Host ""
            Write-Host "  [ERROR] Backend berhenti tidak terduga!" -ForegroundColor Red
            break
        }
        if ($webJob.State -eq 'Failed') {
            Write-Host ""
            Write-Host "  [ERROR] Frontend berhenti tidak terduga!" -ForegroundColor Red
            break
        }

        Start-Sleep -Milliseconds 500
    }
} finally {
    Stop-AllJobs
}
