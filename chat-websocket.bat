@echo off
cd /d "%~dp0" && cd websocket
echo Starting WebSocket server...
npm run chat
pause
