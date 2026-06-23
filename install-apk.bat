@echo off
set ADB=C:\android-sdk\platform-tools\adb.exe
set PKG=com.mjseafood.app

if not exist %ADB% (
    echo ADB not found at %ADB%
    pause
    exit /b 1
)

if not exist "%~dp0app-debug.apk" (
    echo Put app-debug.apk next to this script first.
    echo Download from: https://github.com/laurmj4-eng/TALAbahan-system/releases/download/latest/app-debug.apk
    pause
    exit /b 1
)

echo Checking device...
%ADB% devices

echo.
echo Installing...
%ADB% install -r "%~dp0app-debug.apk"
if %errorlevel% equ 0 goto success

echo.
echo Signature conflict ^(different build machine^). Uninstalling old version first...
%ADB% uninstall %PKG%
if %errorlevel% neq 0 (
    echo Could not uninstall. Is the device connected?
    pause
    exit /b 1
)

%ADB% install "%~dp0app-debug.apk"
if %errorlevel% neq 0 (
    echo Install failed.
    pause
    exit /b 1
)

:success
echo.
echo Install successful! From now on, auto-updates will work
echo because all CI builds use the same signing key.
pause
