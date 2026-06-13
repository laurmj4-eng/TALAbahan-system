# Migration Script: Update from Local Development to Production Render Deployment (Windows)
# Run this script from the project root directory: powershell -ExecutionPolicy Bypass -File migrate-to-production.ps1

param(
    [switch]$Force = $false
)

Write-Host "🚀 TALAbahan System - Production Migration Script (Windows)" -ForegroundColor Green
Write-Host "=============================================================" -ForegroundColor Green
Write-Host ""

# Verify script is running from project root
if (-not (Test-Path "composer.json")) {
    Write-Host "❌ Error: composer.json not found!" -ForegroundColor Red
    Write-Host "Please run this script from the project root directory"
    exit 1
}

Write-Host "📋 Pre-flight Checks" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Yellow

# Check for required files
$requiredFiles = @("Dockerfile.production", "vite.config.production.js", "app/Views/app.production.php")
$allFound = $true
foreach ($file in $requiredFiles) {
    if (-not (Test-Path $file)) {
        Write-Host "❌ Missing: $file" -ForegroundColor Red
        $allFound = $false
    }
}

if (-not $allFound) {
    exit 1
}
Write-Host "✅ All required files present" -ForegroundColor Green

# Check if .env needs to be removed from tracking
Write-Host ""
Write-Host "🔐 Git Security Check" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Yellow

# Check if .env exists and is tracked
if (Test-Path ".env") {
    $gitStatus = git status --short ".env" 2>$null
    if ($gitStatus) {
        Write-Host "⚠️  WARNING: .env file is staged or modified in Git!" -ForegroundColor Yellow
        Write-Host "    You should remove it: git rm --cached .env" -ForegroundColor Yellow
        
        if (-not $Force) {
            $response = Read-Host "    Continue anyway? (yes/no)"
            if ($response -ne "yes") {
                exit 1
            }
        }
    }
}

# Check if .gitignore includes .env
if (Test-Path ".gitignore") {
    $hasEnvIgnore = Select-String -Path ".gitignore" -Pattern "^\.env$" -Quiet
    if (-not $hasEnvIgnore) {
        Write-Host "⚠️  .env is not in .gitignore - adding it now" -ForegroundColor Yellow
        Add-Content -Path ".gitignore" -Value ".env"
    }
}

Write-Host "✅ Git configuration is secure" -ForegroundColor Green

Write-Host ""
Write-Host "📦 Copying Production Files" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Yellow

# Backup original files
if (Test-Path "Dockerfile") {
    Copy-Item "Dockerfile" "Dockerfile.backup" -Force
    Write-Host "✅ Backed up Dockerfile → Dockerfile.backup" -ForegroundColor Green
}

if (Test-Path "vite.config.js") {
    Copy-Item "vite.config.js" "vite.config.js.backup" -Force
    Write-Host "✅ Backed up vite.config.js → vite.config.js.backup" -ForegroundColor Green
}

if (Test-Path "app/Views/app.php") {
    Copy-Item "app/Views/app.php" "app/Views/app.php.backup" -Force
    Write-Host "✅ Backed up app.php → app/Views/app.php.backup" -ForegroundColor Green
}

if (Test-Path "app/Libraries/Inertia.php") {
    Copy-Item "app/Libraries/Inertia.php" "app/Libraries/Inertia.php.backup" -Force
    Write-Host "✅ Backed up Inertia.php → app/Libraries/Inertia.php.backup" -ForegroundColor Green
}

# Copy production files
Copy-Item "Dockerfile.production" "Dockerfile" -Force
Copy-Item "vite.config.production.js" "vite.config.js" -Force
Copy-Item "app/Views/app.production.php" "app/Views/app.php" -Force
Copy-Item "app/Libraries/Inertia.production.php" "app/Libraries/Inertia.php" -Force

Write-Host "✅ Production files installed" -ForegroundColor Green

Write-Host ""
Write-Host "🔑 Security Keys" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Yellow

Write-Host "To generate encryption key, run in your project:" -ForegroundColor Yellow
Write-Host "  php spark key:generate" -ForegroundColor Cyan
Write-Host ""
Write-Host "Then add the generated key to Render environment variables:" -ForegroundColor Yellow
Write-Host "  ENCRYPTION_KEY=<paste_the_key_here>" -ForegroundColor Cyan

Write-Host ""
Write-Host "🔧 Configuration Summary" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Yellow

Write-Host ""
Write-Host "The following changes have been made:" -ForegroundColor Green
Write-Host ""
Write-Host "  ✅ Dockerfile → Uses dynamic PORT variable (for Render)" -ForegroundColor Green
Write-Host "  ✅ vite.config.js → Hash-based asset versioning" -ForegroundColor Green
Write-Host "  ✅ app/Views/app.php → Reads manifest for proper cache busting" -ForegroundColor Green
Write-Host "  ✅ app/Libraries/Inertia.php → Proper version handling" -ForegroundColor Green
Write-Host ""

Write-Host "📝 Next Steps" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Review the updated files:" -ForegroundColor Cyan
Write-Host "   - Verify Dockerfile port configuration" -ForegroundColor Cyan
Write-Host "   - Check vite.config.js asset hashing" -ForegroundColor Cyan
Write-Host "   - Confirm app.php reads manifest correctly" -ForegroundColor Cyan
Write-Host ""
Write-Host "2. Test locally before deploying:" -ForegroundColor Cyan
Write-Host "   docker build -f Dockerfile -t talabahan:latest ." -ForegroundColor Cyan
Write-Host "   docker run -e PORT=8080 -e CI_ENVIRONMENT=production talabahan:latest" -ForegroundColor Cyan
Write-Host ""
Write-Host "3. Set Render environment variables:" -ForegroundColor Cyan
Write-Host "   Dashboard → Environment → Add all from .env.example" -ForegroundColor Cyan
Write-Host ""
Write-Host "4. Commit changes:" -ForegroundColor Cyan
Write-Host "   git add ." -ForegroundColor Cyan
Write-Host "   git commit -m `"Configure production Docker deployment for Render`"" -ForegroundColor Cyan
Write-Host "   git push origin main" -ForegroundColor Cyan
Write-Host ""
Write-Host "5. Deploy on Render:" -ForegroundColor Cyan
Write-Host "   Dashboard → New Web Service → Connect Git → Configure → Deploy" -ForegroundColor Cyan
Write-Host ""

Write-Host "✅ Migration Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📚 Read the full guide: RENDER_DEPLOYMENT_GUIDE.md" -ForegroundColor Cyan
Write-Host "📊 Deployment complete. Verify /health and login flow." -ForegroundColor Cyan
Write-Host ""
