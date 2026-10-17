# Build script to package Cariñena Kebab FSE theme into an installable WordPress zip
$ErrorActionPreference = "Stop"

$ThemeSlug = "carinena-kebab-fse"
$RootDir = Split-Path -Parent $PSScriptRoot
$DistDir = Join-Path $RootDir "dist"
$StagingDir = Join-Path $DistDir $ThemeSlug
$ZipFile = Join-Path $DistDir "$ThemeSlug.zip"

Write-Host "==> Packaging WordPress FSE Theme: $ThemeSlug..." -ForegroundColor Cyan

# 1. Clean previous build
if (Test-Path $DistDir) {
    Remove-Item -Path $DistDir -Recurse -Force
}
New-Item -ItemType Directory -Path $StagingDir -Force | Out-Null

# 2. Copy core theme files
$RootFiles = @("style.css", "theme.json", "DESIGN.md", "functions.php", "README.md")
foreach ($file in $RootFiles) {
    $src = Join-Path $RootDir $file
    if (Test-Path $src) {
        Copy-Item -Path $src -Destination $StagingDir -Force
    }
}

# 3. Copy directories
$IncludeDirs = @("inc", "templates", "parts", "patterns", "css", "js", "fonts")
foreach ($dir in $IncludeDirs) {
    $src = Join-Path $RootDir $dir
    if (Test-Path $src) {
        Copy-Item -Path $src -Destination $StagingDir -Recurse -Force
    }
}

# 4. Copy assets (excluding large zip archives)
$AssetsSrc = Join-Path $RootDir "assets"
$AssetsDest = Join-Path $StagingDir "assets"
if (Test-Path $AssetsSrc) {
    New-Item -ItemType Directory -Path $AssetsDest -Force | Out-Null
    Get-ChildItem -Path $AssetsSrc -Exclude "*.zip" | ForEach-Object {
        Copy-Item -Path $_.FullName -Destination $AssetsDest -Recurse -Force
    }
}

# 5. Compress into zip archive using .NET ZipFile
Write-Host "==> Compressing into $ZipFile..." -ForegroundColor Cyan
Add-Type -AssemblyName System.IO.Compression.FileSystem
Start-Sleep -Milliseconds 500

if (Test-Path $ZipFile) {
    Remove-Item -Path $ZipFile -Force
}

[System.IO.Compression.ZipFile]::CreateFromDirectory($StagingDir, $ZipFile, [System.IO.Compression.CompressionLevel]::Optimal, $false)

$ZipSize = (Get-Item $ZipFile).Length / 1MB
Write-Host ("==> Successfully built: {0} ({1:N2} MB)" -f $ZipFile, $ZipSize) -ForegroundColor Green
