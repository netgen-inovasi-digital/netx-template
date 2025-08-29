@echo off
echo ===============================================
echo    NetX Template - Database Migration Setup
echo ===============================================
echo.

cd /d C:\xampp\htdocs\netx\appengines

echo [1/4] Checking CodeIgniter environment...
if not exist "spark" (
    echo ERROR: CodeIgniter spark file not found!
    echo Please make sure you're running this from the correct directory.
    pause
    exit /b 1
)

echo [2/4] Running database migrations...
echo.
php spark migrate

echo.
echo [3/4] Running database seeders...
echo.
php spark db:seed NetxTemplateSeeder

echo.
echo [4/4] Migration completed successfully!
echo.
echo ===============================================
echo  Database Structure Created:
echo  - Core tables (users, roles, categories, etc.)
echo  - Page Builder system (page_builder, layout, section_templates)
echo  - Default data seeded
echo  - Foreign key constraints applied
echo ===============================================
echo.
echo Your NetX Template database is ready to use!
echo.
pause
