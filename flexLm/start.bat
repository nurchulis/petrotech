@echo off
title FlexLM Dummy System Agent

cd /d "%~dp0"

echo =========================================
echo    Starting FlexLM Dummy System Agent    
echo =========================================

:: Check Python
where python >nul 2>nul
if %errorlevel% neq 0 (
    echo Error: python is not installed or not in PATH.
    pause
    exit /b 1
)

:: Set up virtual environment
if not exist "venv" (
    echo Creating virtual environment...
    python -m venv venv
)

:: Activate virtualenv and install requirements
call venv\Scripts\activate.bat
echo Installing dependencies from requirements.txt...
pip install -r requirements.txt

:: Start Generator in a new command prompt window
echo Starting Dummy Log Generator in background window...
start "FlexLM Log Generator" cmd /k "venv\Scripts\activate.bat && python -u generator.py"

:: Wait 1 second
timeout /t 1 /nobreak >nul

:: Start Sync Agent in current window
echo Starting Sync Agent...
python -u agent.py

pause
