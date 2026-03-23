# Replanta Connector with SAP for WooCommerce - Build ZIP para WordPress.org
# Uso: .\build.ps1

$PluginSlug   = "sap-woo-suite-lite"           # nombre del folder local y archivo .php
$WpOrgSlug    = "replanta-connector-sap-woocommerce"  # slug aprobado por WP.org
$MainFile     = ".\${PluginSlug}.php"

# Leer version del plugin
$content = Get-Content $MainFile -Raw
if ($content -match 'Version:\s*([\d\.]+)') {
    $Version = $Matches[1]
} else {
    Write-Host "  ERROR: No se pudo leer la version de $MainFile" -ForegroundColor Red
    exit 1
}

$ZipName  = "${WpOrgSlug}.zip"          # WP.org requires no version in filename
$BuildDir = "$env:TEMP\${WpOrgSlug}-build"
$DistDir  = ".\dist"

Write-Host ""
Write-Host "  Replanta Connector with SAP for WooCommerce - Build $Version" -ForegroundColor Cyan
Write-Host "  WP.org ZIP slug: $WpOrgSlug" -ForegroundColor DarkGray
Write-Host ""

# PHP Lint
Write-Host "  [1/2] PHP lint..." -ForegroundColor Yellow
$phpCmd = Get-Command php -ErrorAction SilentlyContinue
$phpBin = if ($phpCmd) { $phpCmd.Source } else { $null }

if (-not $phpBin) {
    $phpCandidates = Get-ChildItem "$env:APPDATA\Local\lightning-services\php-*\bin\win64\php.exe" -ErrorAction SilentlyContinue |
                     Sort-Object FullName -Descending
    if ($phpCandidates) { $phpBin = $phpCandidates[0].FullName }
}

if ($phpBin) {
    $phpFiles = Get-ChildItem -Path "." -Include "*.php" -Recurse -File |
                Where-Object { $_.FullName -notmatch '\\vendor\\|\\node_modules\\' }
    $lintErrors = @()
    foreach ($f in $phpFiles) {
        $result = & $phpBin -l $f.FullName 2>&1
        if ($LASTEXITCODE -ne 0) { $lintErrors += "  ! $($f.FullName): $result" }
    }
    if ($lintErrors.Count -gt 0) {
        Write-Host "  PHP PARSE ERRORS - build abortado:" -ForegroundColor Red
        $lintErrors | ForEach-Object { Write-Host $_ -ForegroundColor Red }
        exit 1
    }
    Write-Host "     $($phpFiles.Count) archivos PHP - sin errores" -ForegroundColor Green
} else {
    Write-Warning "  php.exe no encontrado - lint omitido"
}

# Construir ZIP
Write-Host "  [2/2] Construyendo $ZipName ..." -ForegroundColor Yellow

if (Test-Path $BuildDir) { Remove-Item $BuildDir -Recurse -Force }

$target = "$BuildDir\$WpOrgSlug"
New-Item -ItemType Directory -Path $target -Force | Out-Null

# Archivos raiz (renombrar main PHP al slug WP.org)
$rootFiles = @("readme.txt", "uninstall.php", "LICENSE")
foreach ($f in $rootFiles) {
    if (Test-Path $f) { Copy-Item $f -Destination $target }
}
# Main PHP renombrado al slug WP.org
if (Test-Path "${PluginSlug}.php") {
    Copy-Item "${PluginSlug}.php" -Destination "$target\${WpOrgSlug}.php"
}

# Carpetas (sin assets/ - los banners/icons/screenshots se suben por SVN aparte)
$dirs = @("admin", "includes", "languages")
foreach ($d in $dirs) {
    if (Test-Path $d) { Copy-Item $d -Destination "$target\$d" -Recurse }
}

if (-not (Test-Path $DistDir)) { New-Item -ItemType Directory -Path $DistDir -Force | Out-Null }

$zipPath = (Join-Path (Resolve-Path $DistDir) $ZipName)
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }

# ZIP con forward slashes (compatible con Linux/PHP ZipArchive)
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$buildRoot = (Get-Item "$BuildDir\$WpOrgSlug").FullName
$archive   = [System.IO.Compression.ZipFile]::Open($zipPath, 'Create')
try {
    $archive.CreateEntry("$WpOrgSlug/") | Out-Null
    Get-ChildItem $buildRoot -Recurse -File | ForEach-Object {
        $relPath   = $_.FullName.Substring($buildRoot.Length + 1).Replace('\', '/')
        $entryName = "$WpOrgSlug/$relPath"
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
            $archive,
            $_.FullName,
            $entryName,
            [System.IO.Compression.CompressionLevel]::Optimal
        ) | Out-Null
    }
} finally {
    $archive.Dispose()
}

Remove-Item $BuildDir -Recurse -Force

$size = [math]::Round((Get-Item $zipPath).Length / 1KB, 1)
Write-Host ""
Write-Host "  OK  $DistDir\$ZipName ($size KB)" -ForegroundColor Green
Write-Host ""
