@echo off
echo ========================================
echo Community Booking System - Setup Script
echo ========================================
echo.

REM Check if .env exists
if not exist .env (
    echo Creating .env file...
    copy .env.example .env
    echo .env file created successfully!
    echo.
) else (
    echo .env file already exists
    echo.
)

echo Generating application key...
D:\Xamp\php\php.exe artisan key:generate
echo.

echo Setup complete!
echo.
echo Next steps:
echo 1. Edit .env file with your database credentials
echo 2. Create database 'booking_system' in phpMyAdmin
echo 3. Run: run-migrations.bat
echo.
pause
