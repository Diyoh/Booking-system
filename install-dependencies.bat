@echo off
echo ========================================
echo Installing PHP Dependencies
echo ========================================
echo.
echoLocating composer.phar...
if exist "..\others\composer.phar" (
    echo Found composer.phar in parent directory.
    echo Running composer install...
    D:\Xamp\php\php.exe "..\others\composer.phar" install
) else (
    echo Error: composer.phar not found in "..\others\"
    echo Please make sure the others folder is next to booking-system.
)
echo.
pause
