@echo off
title E-Catering Enterprise Launcher
color 0A

echo ============================================================
echo      SELAMAT DATANG DI APLIKASI E-CATERING ENTERPRISE
echo ============================================================
echo.
echo [1/4] Memeriksa file konfigurasi (.env)...
if not exist .env (
    echo [.env tidak ditemukan, membuat dari template...]
    copy .env.example .env
    php artisan key:generate
)

echo.
echo [2/4] Memeriksa link folder penyimpanan gambar...
php artisan storage:link >nul 2>&1

echo.
echo [3/4] Membersihkan cache memori...
php artisan optimize:clear

echo.
echo [4/4] Membuka browser dan menjalankan server web...
start "" "http://127.0.0.1:8000"

echo.
echo ============================================================
echo   WEBSITE AKTIF DI: http://127.0.0.1:8000
echo   (JANGAN TUTUP JENDELA INI SELAMA MENGGUNAKAN APLIKASI)
echo ============================================================
echo.

php artisan serve --host=127.0.0.1 --port=8000
pause
