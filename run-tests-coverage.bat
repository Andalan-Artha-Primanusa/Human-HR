@echo off
cd /d "%~dp0"
call vendor\bin\phpunit.bat --coverage-html=coverage-html
if %ERRORLEVEL% EQU 0 (
    start "" coverage-html\index.html
)
