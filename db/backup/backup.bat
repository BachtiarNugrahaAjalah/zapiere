@echo off
setlocal enabledelayedexpansion

set "backupDir=C:\laragon\www\zapiere\db\backup\hasil_backup"
set "mysqlDir=C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin"

:: ambil tanggal (format Indonesia: dd/mm/yyyy)
for /f "tokens=1-3 delims=/" %%a in ("%date%") do (
    set "day=%%a"
    set "month=%%b"
    set "year=%%c"
)

:: ambil waktu
for /f "tokens=1-2 delims=:" %%a in ("%time%") do (
    set "hour=%%a"
    set "minute=%%b"
)

:: rapikan jam
if "!hour:~0,1!"==" " set "hour=0!hour:~1,1!"

set "timestamp=!year!-!month!-!day!_!hour!-!minute!"

"%mysqlDir%\mysqldump.exe" -u root --no-tablespaces zapiere > "%backupDir%\backup_zapiere_%timestamp%.sql"

endlocal