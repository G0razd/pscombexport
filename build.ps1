# Build script for PsCombExport PrestaShop module
# Creates a release-ready ZIP package

$ModuleName = "pscombexport"
$Version = "2.2"
$OutputDir = ".\release"
$TempDir = ".\temp_build"

Write-Host "Building $ModuleName v$Version..." -ForegroundColor Green

# Clean up old builds
if (Test-Path $OutputDir) {
	Write-Host "Cleaning old release directory..." -ForegroundColor Yellow
	Remove-Item -Path "$OutputDir\*" -Recurse -Force
}
else {
	New-Item -ItemType Directory -Path $OutputDir | Out-Null
}

if (Test-Path $TempDir) {
	Remove-Item -Path $TempDir -Recurse -Force
}
New-Item -ItemType Directory -Path $TempDir | Out-Null
New-Item -ItemType Directory -Path "$TempDir\$ModuleName" | Out-Null

# Copy module files
Write-Host "Copying module files..." -ForegroundColor Yellow
$FilesToInclude = @(
	"pscombexport.php"
	"index.php"
	"logo.png"
)

foreach ($file in $FilesToInclude) {
	if (Test-Path $file) {
		Copy-Item -Path $file -Destination "$TempDir\$ModuleName\" -Force
		Write-Host "  OK $file" -ForegroundColor Gray
	}
}

# Create ZIP package
$ZipFileName = "$ModuleName-v$Version.zip"
$ZipPath = Join-Path $OutputDir $ZipFileName

Write-Host "Creating ZIP package..." -ForegroundColor Yellow
Compress-Archive -Path "$TempDir\$ModuleName" -DestinationPath $ZipPath -Force

# Clean up temp directory
Remove-Item -Path $TempDir -Recurse -Force

# Display results
$ZipSize = (Get-Item $ZipPath).Length / 1KB
Write-Host ""
Write-Host "Build complete!" -ForegroundColor Green
Write-Host "  Package: $ZipPath" -ForegroundColor Cyan
Write-Host "  Size: $([math]::Round($ZipSize, 2)) KB" -ForegroundColor Cyan
Write-Host ""
Write-Host "Ready for distribution to PrestaShop modules directory." -ForegroundColor Green
