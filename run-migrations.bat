@echo off
echo ========================================
echo Running Database Migrations
echo ========================================
echo.

echo Creating database tables...
D:\Xamp\php\php.exe artisan migrate:fresh --seed
echo.

echo Database setup complete!
echo.
echo Test credentials:
echo Admin: admin@booking.com / password123
echo User:  user@booking.com / password123
echo.
pause
