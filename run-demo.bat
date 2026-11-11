@echo off
REM Chay trong thu muc blog: mo 3 cua so (web, queue, scheduler)
start "WEB" cmd /k php artisan serve
start "QUEUE" cmd /k php artisan queue:work
start "SCHEDULER" cmd /k php artisan schedule:work
echo Mo trinh duyet: http://localhost:8000
