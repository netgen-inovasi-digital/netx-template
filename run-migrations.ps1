# NetX Template - Database Migration Setup
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "   NetX Template - Database Migration Setup" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

Set-Location "C:\xampp\htdocs\netx\appengines"

Write-Host "[1/4] Checking CodeIgniter environment..." -ForegroundColor Yellow
if (-not (Test-Path "spark")) {
    Write-Host "ERROR: CodeIgniter spark file not found!" -ForegroundColor Red
    Write-Host "Please make sure you're running this from the correct directory." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "[2/4] Running database migrations..." -ForegroundColor Yellow
Write-Host ""
php spark migrate

Write-Host ""
Write-Host "[3/4] Running database seeders..." -ForegroundColor Yellow
Write-Host ""
php spark db:seed NetxTemplateSeeder

Write-Host ""
Write-Host "[4/4] Migration completed successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host " Database Structure Created:" -ForegroundColor Green
Write-Host " - Core tables (users, roles, categories, etc.)" -ForegroundColor White
Write-Host " - Page Builder system (page_builder, layout, section_templates)" -ForegroundColor White
Write-Host " - Default data seeded" -ForegroundColor White
Write-Host " - Foreign key constraints applied" -ForegroundColor White
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Your NetX Template database is ready to use!" -ForegroundColor Green
Write-Host ""
Read-Host "Press Enter to exit"
