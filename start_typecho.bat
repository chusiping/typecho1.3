@echo off
title Start Typecho Environment
echo ==============================
echo Starting Apache...
echo ==============================

cd /d E:\xampp
start "" apache_start.bat

timeout /t 3 >nul

echo ==============================
echo Starting MySQL...
echo ==============================

start "" mysql_start.bat

timeout /t 3 >nul

echo ==============================
echo Starting Meilisearch...
echo ==============================

cd /d E:\meilisearch
start "" meilisearch.exe --master-key ""

timeout /t 5 >nul

echo ==============================
echo Opening Browser...
echo ==============================

start http://test.qy:8001/typecho/

echo.
echo All services started!
pause