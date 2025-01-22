@echo off
:loop
echo [%date% %time%] Execution de la commande...
php bin/console app:event-state-worker
timeout /t 60 >nul
goto loop
