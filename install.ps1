param(
    [switch]$SkipComposer,
    [switch]$SkipNpm,
    [switch]$Fresh
)

$Root   = $PSScriptRoot
$ApiDir = Join-Path $Root "juara-league-api"
$WebDir = Join-Path $Root "juara-league-web"

Write-Host ""
Write-Host "  =============================================" -ForegroundColor Cyan
Write-Host "       Juara League - Install Dependencies     " -ForegroundColor Cyan
Write-Host "  =============================================" -ForegroundColor Cyan
Write-Host ""

# ── Validasi tools ────────────────────────────────────────────────────────────
if (-not $SkipComposer) {
    if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
        Write-Host "  [ERROR] Composer tidak ditemukan di PATH." -ForegroundColor Red
        Write-Host "          Instal Composer atau gunakan -SkipComposer." -ForegroundColor Red
        exit 1
    }
}
if (-not $SkipNpm) {
    if (-not (Get-Command npm -ErrorAction SilentlyContinue)) {
        Write-Host "  [ERROR] npm tidak ditemukan di PATH." -ForegroundColor Red
        Write-Host "          Instal Node.js atau gunakan -SkipNpm." -ForegroundColor Red
        exit 1
    }
}

# ── Fresh mode: hapus vendor & node_modules ───────────────────────────────────
if ($Fresh) {
    Write-Host "  [FRESH] Menghapus vendor/ dan node_modules/..." -ForegroundColor Yellow
    $toRemove = @(
        (Join-Path $ApiDir "vendor"),
        (Join-Path $ApiDir "node_modules"),
        (Join-Path $WebDir "node_modules")
    )
    foreach ($d in $toRemove) {
        if (Test-Path $d) {
            Remove-Item $d -Recurse -Force
            Write-Host "    Dihapus: $d" -ForegroundColor Gray
        }
    }
    Write-Host ""
}

# ── 1. Composer install (Backend) ─────────────────────────────────────────────
if (-not $SkipComposer) {
    Write-Host "  [1/3] Backend - composer install (juara-league-api)..." -ForegroundColor Cyan
    Push-Location $ApiDir
    try {
        composer install --no-interaction
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  [OK]  composer install selesai." -ForegroundColor Green
        } else {
            Write-Host "  [FAIL] composer install gagal (exit code $LASTEXITCODE)." -ForegroundColor Red
        }
    } finally {
        Pop-Location
    }
    Write-Host ""
}

# ── 2. npm install (Backend — Vite assets) ────────────────────────────────────
if (-not $SkipNpm) {
    Write-Host "  [2/3] Backend - npm install (juara-league-api)..." -ForegroundColor Cyan
    Push-Location $ApiDir
    try {
        npm install
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  [OK]  npm install (api) selesai." -ForegroundColor Green
        } else {
            Write-Host "  [FAIL] npm install (api) gagal (exit code $LASTEXITCODE)." -ForegroundColor Red
        }
    } finally {
        Pop-Location
    }
    Write-Host ""
}

# ── 3. npm install (Frontend — Nuxt) ─────────────────────────────────────────
if (-not $SkipNpm) {
    Write-Host "  [3/3] Frontend - npm install (juara-league-web)..." -ForegroundColor Cyan
    Push-Location $WebDir
    try {
        npm install
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  [OK]  npm install (web) selesai." -ForegroundColor Green
        } else {
            Write-Host "  [FAIL] npm install (web) gagal (exit code $LASTEXITCODE)." -ForegroundColor Red
        }
    } finally {
        Pop-Location
    }
    Write-Host ""
}

# ── Selesai ───────────────────────────────────────────────────────────────────
Write-Host "  =============================================" -ForegroundColor Cyan
Write-Host "  Semua dependency berhasil diinstall!" -ForegroundColor Green
Write-Host "  Sekarang jalankan: .\dev.ps1" -ForegroundColor Yellow
Write-Host ""
