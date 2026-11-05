@echo off
setlocal

cd /d "%~dp0"

if not defined MAYA_TOPUP_PHP set "MAYA_TOPUP_PHP=php"
"%MAYA_TOPUP_PHP%" -r "exit(PHP_VERSION_ID >= 70103 && PHP_VERSION_ID < 80000 ? 0 : 1);" >nul 2>&1
if errorlevel 1 (
    echo Set MAYA_TOPUP_PHP to the full PHP 7 executable path used by this Laravel 5.6 application.
    pause
    exit /b 1
)

:restart
echo [%date% %time%] Starting Mayadigital top-up queue worker...
"%MAYA_TOPUP_PHP%" artisan topups:work
echo [%date% %time%] Worker stopped. Restarting in 5 seconds...
timeout /t 5 /nobreak >nul
goto restart
