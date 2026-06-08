@echo off
set MYSQL=d:\wamp\bin\mysql\mysql8.2.0\bin\mysql.exe
set DB=sultans_smoke_db
set SCHEMA=d:\wamp\www\Phantom Smoking\database\schema.sql
set SEEDS=d:\wamp\www\Phantom Smoking\database\seeds.sql

echo Importing schema...
%MYSQL% -u root %DB% < "%SCHEMA%"
if %errorlevel% neq 0 (echo SCHEMA FAILED) else (echo SCHEMA OK)

echo Importing seeds...
%MYSQL% -u root %DB% < "%SEEDS%"
if %errorlevel% neq 0 (echo SEEDS FAILED) else (echo SEEDS OK)

echo Done.
pause
