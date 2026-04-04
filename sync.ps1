# Obenlo Maintenance: Sync with GitHub
# This script will hard reset your local code to the latest stable version on GitHub.

Write-Host "Fetching latest code from Obenlo repository..." -ForegroundColor Cyan

# Fetch and reset
git fetch origin
git reset --hard origin/main
git clean -fd

Write-Host "Success: Your local environment is now synced with v1.3.0!" -ForegroundColor Green
