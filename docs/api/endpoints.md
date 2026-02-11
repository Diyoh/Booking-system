# API Endpoints Documentation

## Overview

This document describes all API endpoints in the Hybrid Web/USSD Booking System.

---

## Authentication

### Web API

Most web endpoints require authentication via Laravel's session-based auth.

**Protected Routes:** Use middleware `auth`

```php
Route::middleware(['auth'])->group(function () {
    // Protected routes here
});
```

### Webhook API

USSD and Payment webhooks do NOT require authentication.
They are called by Africa's Talking with specific request formats.

---

## Webhook Endpoints

### 1. USSD Webhook

**Handle USSD Request**

```
POST /api/ussd
Content-Type: application/x-www-form-urlencoded
```

**Request Parameters:**

| Parameter   | Type   | Required | Description                       |
| ----------- | ------ | -------- | --------------------------------- |
| sessionId   | string | Yes      | Unique session ID from MNO        |
| phoneNumber | string | Yes      | User's phone number (MSISDN)      |
| text        | string | No       | User input path (e.g., "1*2*3")   |
| serviceCode | string | Yes      | USSD code dialed (e.g., *384*10#) |

**Request Example:**

```
sessionId=ATUid_12345abcde
phoneNumber=%2B254712345678
text=1*2
serviceCode=*384*10#
```

**Response Format:**

| Type | Description                                |
| ---- | ------------------------------------------ |
| CON  | Continue - Show menu and wait for input    |
| END  | End - Show final message and close session |

**Response Examples:**

```plaintext
CON Welcome to Community Booking
1. Browse Halls
2. Browse Events
3. My Bookings
4. Register
```

```plaintext
END Your booking for Town Hall on Feb 15, 2026 is confirmed.
Ref: HALL-1234
```

**Error Handling:**

````plaintext
```plaintext
END An error occurred. Please try again.
````

---

### 2. Payment Callback

**Handle Payment Notification**

```
POST /api/payment/callback
Content-Type: application/json
```

**Request Body:**

```json
{
  "status": "Success",
  "transactionId": "ATPid_SampleTxnId123",
  "category": "MobileCheckout",
  "productName": "Community Booking System",
  "provider": "Mpesa",
  "providerRefId": "MPES123456",
  "providerMetadata": {
    "recipientIsRegistered": "true",
    "recipientName": "John Doe"
  },
  "phoneNumber": "+254712345678",
  "clientAccount": "Online",
  "value": "KES 5000.00",
  "transactionFee": "KES 10.00",
  "providerFee": "KES 5.00",
  "requestMetadata": {
    "booking_id": "123",
    "reference_code": "HALL-1234"
  },
  "transactionDate": "2026-02-02T10:30:45Z"
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Payment processed successfully"
}
```

**Status Values:**

| Status  | Description                     |
| ------- | ------------------------------- |
| Success | Payment completed successfully  |
| Failed  | Payment failed or was cancelled |
| Pending | Payment still processing (rare) |

**Processing Logic:**

1. Validate callback data
2. Find matching `Transaction` record
3. Update transaction status
4. If success:
   - Confirm booking
   - Send SMS confirmation
5. If failed:
   - Mark transaction as failed
   - Booking expires after hold timeout

---

### 3. Payment Status Check

**Check Transaction Status (Admin/Debug)**

```
GET /api/payment/status?transaction_id={id}
```

**Query Parameters:**

| Parameter      | Type    | Required | Description             |
| -------------- | ------- | -------- | ----------------------- |
| transaction_id | integer | Yes      | Transaction ID to check |

**Response:**

```json
{
  "transaction": {
    "id": 1,
    "booking_id": 123,
    "phone_number": "+254712345678",
    "amount": "50.00",
    "status": "success",
    "provider_transaction_id": "ATPid_12345",
    "created_at": "2026-02-02T10:28:30Z"
  },
  "status_check": null
}
```

---

## Web API Endpoints (Future Implementation)

### Authentication

#### Login

```
POST /login
Content-Type: application/json

Request:
{
  "email": "user@booking.com",
  "password": "password123"
}

Response:
{
  "status": "success",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@booking.com"
  }
}
```

#### Register

```
POST /register
Content-Type: application/json

Request:
{
  "name": "John Doe",
  "phone_number": "+254712345678",
  "email": "user@booking.com",
  "password": "password123",
  "password_confirmation": "password123"
}

Response:
{
  "status": "success",
  "user": { ... }
}
```

#### Logout

```
POST /logout

Response:
{
  "status": "success",
  "message": "Logged out successfully"
}
```

---

### Resource Browsing

#### List Halls

```
GET /api/halls?page=1&capacity_min=50&price_max=100

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Town Hall",
      "capacity": 200,
      "price_per_hour": "50.00",
      "location": "123 Main Street",
      "amenities": ["Parking", "AC"],
      "is_available": true
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 3
  }
}
```

#### List Events

```
GET /api/events?upcoming=true&available=true

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Annual Town Meeting",
      "event_date": "2026-03-15",
      "start_time": "18:00:00",
      "ticket_price": "10.00",
      "remaining_slots": 150
    }
  ]
}
```

---

### Booking Management

#### Create Booking

```
POST /api/bookings
Content-Type: application/json
Authorization: Bearer {token}

Request (Hall):
{
  "resource_type": "hall",
  "resource_id": 1,
  "booking_date": "2026-02-15",
  "start_time": "14:00:00",
  "end_time": "18:00:00"
}

Request (Event):
{
  "resource_type": "event",
  "resource_id": 2,
  "booking_date": "2026-03-15",
  "quantity": 2
}

Response:
{
  "status": "success",
  "booking": {
    "id": 123,
    "reference_code": "HALL-1234",
    "total_amount": "200.00",
    "status": "pending",
    "hold_expires_at": "2026-02-02T10:35:00Z"
  },
  "payment_initiated": true
}
```

#### Get User Bookings

```
GET /api/my-bookings?status=confirmed
Authorization: Bearer {token}

Response:
{
  "data": [
    {
      "id": 123,
      "reference_code": "HALL-1234",
      "resource": {
        "type": "hall",
        "name": "Town Hall"
      },
      "booking_date": "2026-02-15",
      "status": "confirmed",
      "total_amount": "200.00"
    }
  ]
}
```

#### Cancel Booking

```
DELETE /api/bookings/{id}
Authorization: Bearer {token}

Response:
{
  "status": "success",
  "message": "Booking cancelled successfully",
  "refund_initiated": true
}
```

---

## USSD Menu Flow

### Menu Structure

```
Main Menu (Level 1)
├── 1. Browse Halls
│   ├── Page 1: Halls 1-3 (Level 2)
│   │   ├── Select Hall → Enter Date (Level 3)
│   │   │   └── Enter Time → Enter PIN → Confirm (Level 4)
│   ├── 9. Next Page
│   └── 0. Previous Page
│
├── 2. Browse Events
│   ├── Event List (Level 2)
│   │   └── Select Event → Enter PIN → Confirm (Level 3)
│
├── 3. My Bookings
│   └── "History sent via SMS" (END)
│
└── 4. Register
    ├── Enter Name (Level 2)
    ├── Create PIN (Level 3)
    └── Confirm PIN → Complete (Level 4)
```

### Sample USSD Flow

**User Input Path:** `1*2*15-02-2026*14:00*1234*1`

**Breakdown:**

1. `1` - Select "Browse Halls"
2. `2` - Select second hall from list
3. `15-02-2026` - Enter date
4. `14:00` - Enter duration
5. `1234` - Enter PIN
6. `1` - Confirm booking

**Response:**

```
END Please check your phone for payment prompt.
```

---

## Error Responses

### USSD Errors

```plaintext
END An error occurred. Please try again.
```

```plaintext
CON Invalid option. Try again:
1. Browse Halls
2. Browse Events
```

### Web API Errors

#### Validation Error (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "booking_date": ["The booking date must be a future date."],
    "start_time": ["The start time field is required for hall bookings."]
  }
}
```

#### Unauthorized (401)

```json
{
  "message": "Unauthenticated."
}
```

#### Resource Not Found (404)

```json
{
  "message": "Resource not found."
}
```

#### Business Logic Error (400)

```json
{
  "error": "Hall is not available for the selected time slot."
}
```

---

## Rate Limiting

### USSD Endpoints

- **MNO-level:** 180-second session timeout enforces natural rate limit
- **Application-level:** No additional throttling needed

### Web API Endpoints

```php
Route::middleware('throttle:60,1')->group(function () {
    // 60 requests per minute per user
});
```

---

## Testing

### USSD Testing

**Africa's Talking Simulator:**

1. Login to Africa's Talking dashboard
2. Navigate to USSD → Simulator
3. Enter your registered phone number
4. Dial the USSD code (e.g., `*384*10#`)
5. Interact with menus

**Ngrok Setup:**

```bash
ngrok http 8000
# Copy HTTPS URL (e.g., https://abc123.ngrok.io)
# Update .env: NGROK_URL=https://abc123.ngrok.io
# Configure Africa's Talking webhook: https://abc123.ngrok.io/api/ussd
```

### Payment Testing

**Sandbox Mode:**

- Africa's Talking provides test credentials
- Simulated payments (no real money)
- Callbacks sent to configured webhook

**Test Flow:**

1. Initiate payment via API
2. Africa's Talking sends STK Push simulation
3. "User" enters PIN in simulator
4. Callback sent to `/api/payment/callback`
5. Verify booking confirmed

---

## Postman Collection

See `docs/postman/` folder for importable Postman collection with:

- All endpoint definitions
- Sample requests
- Environment variables

---

**Document Version:** 1.0  
**Last Updated:** February 2026
