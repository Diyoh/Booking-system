@echo off
setlocal

echo.
echo ========================================================
echo   SIMULATE AFRICA'S TALKING PAYMENT
echo ========================================================
echo.
echo This script simulates a successful payment callback from Africa's Talking.
echo Use this to mark a booking as PAID without spending real money.
echo.

:ask_id
set /p booking_id="Enter Booking ID (e.g. 1): "
if "%booking_id%"=="" goto ask_id

echo.
echo Simulating payment for Booking #%booking_id%...
echo.

:: Generate a fake transaction ID
set "trans_id=SIM%RANDOM%%RANDOM%"

:: Send the callback request
curl -X POST http://127.0.0.1:8000/api/payment/callback ^
  -H "Content-Type: application/json" ^
  -d "{\"category\":\"MobileCheckout\",\"provider\":\"Mpesa\",\"clientAccount\":\"%booking_id%\",\"productName\":\"CommunityBooking\",\"value\":\"XAF 1000.00\",\"transactionId\":\"%trans_id%\",\"status\":\"Success\",\"description\":\"Payment Confirmed\"}"

echo.
echo.
if %ERRORLEVEL% EQU 0 (
    echo [SUCCESS] Payment simulation sent!
    echo Check your dashboard. The booking should now be CONFIRMED.
) else (
    echo [ERROR] Failed to send simulation request.
    echo Make sure your server is running at http://127.0.0.1:8000
)

pause
