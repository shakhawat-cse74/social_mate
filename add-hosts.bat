@echo off
:: Check for admin rights
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [INFO] Requesting Administrator Privileges...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

:: Add socialmate.test to hosts
findstr /C:"socialmate.test" %WINDIR%\System32\drivers\etc\hosts >nul 2>&1
if %errorLevel% equ 0 (
    echo [OK] socialmate.test is already present in your hosts file.
) else (
    echo 127.0.0.1 socialmate.test >> %WINDIR%\System32\drivers\etc\hosts
    echo [SUCCESS] Added 127.0.0.1 socialmate.test to hosts file!
)

ipconfig /flushdns >nul
echo.
echo ========================================================
echo  SocialMate is now ready at: http://socialmate.test
echo ========================================================
pause

