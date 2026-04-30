@echo off
REM Test Update User dengan curl - PowerShell Version
REM File: test_update_user_correct.bat

setlocal enabledelayedexpansion

set TOKEN=49|JvdcM56f0MtHyiS4ox73rkwifHKcXwRnVQ7hGnIw4b1bdd06
set USER_ID=2
set BASE_URL=http://localhost:8000

echo.
echo ========================================
echo ===== UPDATE USER TEST - CORRECT WAY =====
echo ========================================
echo.
echo Using POST method with form-data
echo.

REM Test 1: Simple text update
echo [TEST 1] Update dengan text fields saja
echo.
curl.exe -X POST "%BASE_URL%/api/auth/user/%USER_ID%" ^
  -H "Authorization: Bearer %TOKEN%" ^
  -H "Accept: application/json" ^
  -F "name=Test User Updated" ^
  -F "email=testuser@gmail.com" ^
  -F "username=testuser_updated"

echo.
echo.
echo [TEST 1 COMPLETE] Check response di atas untuk verifikasi data ter-update
echo.

pause
