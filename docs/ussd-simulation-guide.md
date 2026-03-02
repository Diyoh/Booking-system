# USSD Simulation Guide with Africa's Talking

This document provides a step-by-step guide on how to simulate USSD interactions for the Community Booking System using the Africa's Talking (AT) Simulator.

## Prerequisites

1.  **Africa's Talking Sandbox Account**: You need an account on [Africa's Talking](https://account.africastalking.com/).
2.  **Ngrok (or similar tunnel)**: To expose your local Laravel booking system to the public internet so the AT webhook can reach it.
3.  **Local Application Running**:
    - Start your Laravel server: `php artisan serve` (e.g., runs on `http://127.0.0.1:8000`)
    - Start ngrok: `ngrok http 8000`
    - Update your `.env` file with the ngrok URL:
      ```env
      NGROK_URL=https://<your-ngrok-id>.ngrok.io
      ```

## Configuration in Africa's Talking

1.  Log in to the **Africa's Talking Sandbox**.
2.  Go to **USSD** -> **Service Codes**.
3.  Create a new USSD Channel:
    - **Channel**: `*384*10#` (or any available sandbox code).
    - **Callback URL**: Set this to your ngrok URL appended with `/api/ussd` (e.g., `https://<your-ngrok-id>.ngrok.io/api/ussd`).
4.  Save the USSD channel.

## Simulating the Booking Process

1.  In the AT Sandbox, navigate to **Simulator**.
2.  Enter a simulated phone number (e.g., `+237600000000`).
3.  Click **Connect**.
4.  In the phone simulator screen, dial your USSD code (e.g., `*384*10#`) and press **Send**.

### 1. Register a New User

Before booking, you must register a user account with a PIN.

1.  **Dial**: `*384*10#`
2.  **Main Menu**: Reply `4` (Register)
3.  **Name**: Reply with your name (e.g., `John Doe`)
4.  **Create PIN**: Reply with a 4-digit PIN (e.g., `1234`)
5.  **Confirm PIN**: Reply with the same 4-digit PIN (e.g., `1234`)
6.  _Success Message: "Registration successful! Welcome John Doe."_

### 2. Book an Event

This flow demonstrates booking a ticket for an existing event.

1.  **Dial**: `*384*10#`
2.  **Main Menu**: Reply `2` (Browse Events)
3.  **Select Event**: Reply with the number corresponding to the event you want to book (e.g., `1`).
4.  **Enter PIN**: Reply with your registered 4-digit PIN (e.g., `1234`).
5.  **Confirm Booking**: A summary will be displayed. Reply `1` to Confirm.
6.  _Success Message: "Please check your phone for payment prompt."_

### 3. Book a Hall

This flow demonstrates booking a community hall.

1.  **Dial**: `*384*10#`
2.  **Main Menu**: Reply `1` (Browse Halls)
3.  **Select Hall**: Reply with the number corresponding to the hall (e.g., `1`).
4.  **Enter Date**: Reply with the desired date in `DD-MM-YYYY` format (e.g., `15-10-2026`).
5.  **Enter Start Time**: Reply with the start time in `HH:MM` format (e.g., `14:00`).
6.  **Enter Duration / PIN**: Follow the remaining prompts to input the details and confirm the booking using your PIN.
7.  **Confirm Booking**: Reply `1` to confirm.

### 4. View My Bookings

1.  **Dial**: `*384*10#`
2.  **Main Menu**: Reply `3` (My Bookings)
3.  _Success Message: "Your booking history has been sent via SMS."_

## Troubleshooting

- **"Application Error" or Timeout**: This usually means the AT Simulator couldn't reach your application. Ensure `ngrok` is running and the Callback URL in AT matches your active ngrok tunnel.
- **"Invalid PIN"**: Ensure you have completed the Registration setup first using the same simulated phone number.
- **Missing API Route**: Make sure your `routes/api.php` has the `/ussd` route properly defined pointing to the `UssdController@handle` method.
