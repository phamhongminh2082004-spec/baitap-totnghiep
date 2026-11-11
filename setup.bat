@echo off
chcp 65001 >nul
REM === Cai dat nhanh (Windows) - can PHP 8.3+, Composer, extension gd/sqlite ===
if exist blog (echo Thu muc blog da ton tai, hay xoa hoac doi ten truoc. & exit /b 1)
call composer create-project laravel/laravel blog || exit /b 1
xcopy /E /Y /I code blog >nul
cd blog
call php artisan migrate --seed --force || exit /b 1
call php artisan storage:link
echo.
echo === XONG. Chay demo bang run-demo.bat (trong thu muc blog) ===
copy ..\run-demo.bat run-demo.bat >nul
