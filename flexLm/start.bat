@echo off
title FlexLM Dummy System Agent
setlocal enabledelayedexpansion

cd /d "%~dp0"

echo =========================================
echo    Starting FlexLM Dummy System Agent
echo =========================================

:: ─── 1. Detect Python ─────────────────────────────────────────────────────
set PYTHON_CMD=

where python >nul 2>nul
if %errorlevel% equ 0 (
    set PYTHON_CMD=python
    goto :python_found
)

where py >nul 2>nul
if %errorlevel% equ 0 (
    set PYTHON_CMD=py
    goto :python_found
)

echo.
echo =======================================================================
echo  ERROR: Python tidak terdeteksi di sistem Anda!
echo =======================================================================
echo  Cara mengatasi:
echo  1. Download Python dari: https://www.python.org/downloads/
echo  2. Jalankan installer-nya.
echo  3. WAJIB CENTANG "Add Python.exe to PATH" di halaman pertama installer.
echo  4. Restart komputer, lalu jalankan kembali start.bat.
echo =======================================================================
pause
exit /b 1

:python_found
echo  Python ditemukan: [%PYTHON_CMD%]
echo.

:: ─── 2. Create Virtual Environment ────────────────────────────────────────
if not exist "venv\Scripts\activate.bat" (
    echo Membuat virtual environment...
    %PYTHON_CMD% -m venv venv
    if %errorlevel% neq 0 (
        echo.
        echo ERROR: Gagal membuat virtual environment.
        echo Coba hapus folder "venv" jika ada, lalu jalankan ulang start.bat.
        pause
        exit /b 1
    )
    echo Virtual environment berhasil dibuat.
    echo.
)

:: ─── 3. Activate venv ─────────────────────────────────────────────────────
call venv\Scripts\activate.bat
if %errorlevel% neq 0 (
    echo.
    echo ERROR: Gagal mengaktifkan virtual environment.
    echo Hapus folder "venv" dan jalankan ulang start.bat.
    pause
    exit /b 1
)

:: ─── 4. Install Dependencies ───────────────────────────────────────────────
echo Installing dependencies dari requirements.txt...
pip install -q -r requirements.txt
if %errorlevel% neq 0 (
    echo.
    echo ERROR: Gagal menginstall dependensi. Periksa koneksi internet Anda.
    pause
    exit /b 1
)
echo Semua dependensi sudah terpasang.
echo.

:: ─── 5. Create log directory if not exist ─────────────────────────────────
if not exist "log\debug" (
    mkdir "log\debug"
    echo Folder log\debug berhasil dibuat.
)

:: ─── 6. Start Generator (background window) ────────────────────────────────
echo Menjalankan Log Generator di window baru...
start "FlexLM Log Generator" cmd /k "cd /d "%~dp0" && call venv\Scripts\activate.bat && python -u generator.py"

:: Wait 1 second for generator to start
timeout /t 1 /nobreak >nul

:: ─── 7. Start Sync Agent (current window) ──────────────────────────────────
echo Menjalankan Sync Agent...
echo =========================================
python -u agent.py

pause
