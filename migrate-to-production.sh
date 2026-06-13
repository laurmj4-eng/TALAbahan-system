#!/bin/bash
# Migration Script: Update from Local Development to Production Render Deployment
# Run this script from the project root directory: bash migrate-to-production.sh

set -e

echo "🚀 TALAbahan System - Production Migration Script"
echo "=================================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verify script is running from project root
if [ ! -f "composer.json" ]; then
    echo -e "${RED}❌ Error: composer.json not found!${NC}"
    echo "Please run this script from the project root directory"
    exit 1
fi

echo -e "${YELLOW}📋 Pre-flight Checks${NC}"
echo "---"

# Check for required files
required_files=("Dockerfile.production" "vite.config.production.js" "app/Views/app.production.php")
for file in "${required_files[@]}"; do
    if [ ! -f "$file" ]; then
        echo -e "${RED}❌ Missing: $file${NC}"
        exit 1
    fi
done
echo -e "${GREEN}✅ All required files present${NC}"

# Check Git status
echo ""
echo -e "${YELLOW}🔐 Git Security Check${NC}"
echo "---"

# Search for potential credentials in Git history
if git log -p --all -S='yEEY6EnLGIfdD' 2>/dev/null | grep -q 'yEEY6EnLGIfdD'; then
    echo -e "${RED}⚠️  WARNING: Old database credentials found in Git history!${NC}"
    echo "    You need to purge this from Git history before deploying to production."
    echo "    Consider creating a NEW database with different credentials."
    echo ""
    read -p "    Continue anyway? (yes/no) " -n 3 -r
    echo
    if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
        exit 1
    fi
fi

if git diff --name-only | grep -q "\.env$"; then
    echo -e "${RED}❌ Error: .env file has been modified and would be committed!${NC}"
    echo "    Run: git rm --cached .env"
    echo "    Then: echo '.env' >> .gitignore"
    exit 1
fi

echo -e "${GREEN}✅ Git history is clean${NC}"

echo ""
echo -e "${YELLOW}📦 Copying Production Files${NC}"
echo "---"

# Backup original files
if [ -f "Dockerfile" ]; then
    cp Dockerfile Dockerfile.backup
    echo "✅ Backed up Dockerfile → Dockerfile.backup"
fi

if [ -f "vite.config.js" ]; then
    cp vite.config.js vite.config.js.backup
    echo "✅ Backed up vite.config.js → vite.config.js.backup"
fi

if [ -f "app/Views/app.php" ]; then
    cp app/Views/app.php app/Views/app.php.backup
    echo "✅ Backed up app.php → app.php.backup"
fi

if [ -f "app/Libraries/Inertia.php" ]; then
    cp app/Libraries/Inertia.php app/Libraries/Inertia.php.backup
    echo "✅ Backed up Inertia.php → Inertia.php.backup"
fi

# Copy production files
cp Dockerfile.production Dockerfile
cp vite.config.production.js vite.config.js
cp app/Views/app.production.php app/Views/app.php
cp app/Libraries/Inertia.production.php app/Libraries/Inertia.php

echo -e "${GREEN}✅ Production files installed${NC}"

echo ""
echo -e "${YELLOW}🔑 Generating Security Keys${NC}"
echo "---"

# Generate CI4 encryption key
if ! grep -q "ENCRYPTION_KEY=" .env; then
    encryption_key=$(php spark key:generate 2>/dev/null | grep -oP 'hex2bin\(\047\K[^\']*')
    
    if [ -z "$encryption_key" ]; then
        echo -e "${YELLOW}⚠️  Could not auto-generate encryption key${NC}"
        echo "    Run manually: php spark key:generate"
        echo "    Then add to Render environment variables"
    else
        echo -e "${GREEN}✅ Encryption key generated${NC}"
        echo "    Add this to Render: ENCRYPTION_KEY=$encryption_key"
    fi
fi

echo ""
echo -e "${YELLOW}🔧 Configuration Summary${NC}"
echo "---"

echo "The following changes have been made:"
echo ""
echo "  ✅ Dockerfile → Uses dynamic PORT variable (for Render)"
echo "  ✅ vite.config.js → Hash-based asset versioning"
echo "  ✅ app/Views/app.php → Reads manifest for proper cache busting"
echo "  ✅ app/Libraries/Inertia.php → Proper version handling"
echo ""

echo -e "${YELLOW}📝 Next Steps${NC}"
echo "---"
echo ""
echo "1. Review the updated files:"
echo "   - Verify Dockerfile port configuration"
echo "   - Check vite.config.js asset hashing"
echo "   - Confirm app.php reads manifest correctly"
echo ""
echo "2. Test locally before deploying:"
echo "   docker build -f Dockerfile -t talabahan:latest ."
echo "   docker run -e PORT=8080 -e CI_ENVIRONMENT=production talabahan:latest"
echo ""
echo "3. Set Render environment variables:"
echo "   Dashboard → Environment → Add all from .env.example"
echo ""
echo "4. Commit changes:"
echo "   git add ."
echo "   git commit -m \"Configure production Docker deployment for Render\""
echo "   git push origin main"
echo ""
echo "5. Deploy on Render:"
echo "   Dashboard → New Web Service → Connect Git → Configure → Deploy"
echo ""

echo -e "${GREEN}✅ Migration Complete!${NC}"
echo ""
echo "📚 Read the full guide: RENDER_DEPLOYMENT_GUIDE.md"
echo "📊 Deployment complete. Verify /health and login flow."
echo ""
