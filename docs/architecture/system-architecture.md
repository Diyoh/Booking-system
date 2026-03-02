# System Architecture Documentation

## Overview

The Hybrid Web/USSD Community Event Booking System follows a **service-oriented architecture** with clear separation between presentation, business logic, and data layers.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER INTERFACES                          │
├──────────────────────┬──────────────────────────────────────────┤
│   Web Interface      │        USSD Interface                    │
│  (Desktop/Mobile)    │      (Feature Phones)                     │
│  - Blade Templates   │   - Africa's Talking Gateway             │
│  - Alpine.js         │   - Text-based menus                     │
│  - Tailwind CSS      │   - Session state management             │
└──────────┬───────────┴──────────────┬───────────────────────────┘
           │                          │
           │                          │
┌──────────▼──────────────────────────▼───────────────────────────┐
│                      CONTROLLERS LAYER                           │
├──────────────────────┬──────────────────────────────────────────┤
│  Web Controllers     │        API Controllers                   │
│  - AuthController    │   - UssdController                       │
│  - BookingController │   - PaymentController                    │
│  - DashboardController│                                         │
│  - AdminControllers  │                                          │
└──────────┬───────────┴──────────────┬───────────────────────────┘
           │                          │
           │        ┌─────────────────▼────────────────┐
           │        │   Africa's Talking              │
           │        │   - USSD Gateway                 │
           │        │   - Mobile Money API             │
           │        │   - SMS API                      │
           │        └─────────────────┬────────────────┘
           │                          │
           │                          │
┌──────────▼──────────────────────────▼───────────────────────────┐
│                       SERVICE LAYER                             │
│                   (Business Logic Hub)                           │
├──────────────────────────────────────────────────────────────────┤
│  ● BookingService    - Availability checking (race-free)        │
│                      - Reservation holds                         │
│                      - Booking lifecycle management              │
│                                                                   │
│  ● PaymentService    - STK Push initiation                      │
│                      - Callback handling                         │
│                      - Transaction tracking                      │
│                                                                   │
│  ● SmsService        - Booking confirmations                    │
│                      - Payment receipts                          │
│                      - Booking history                           │
│                                                                   │
│  ● UssdMenuService   - State machine                            │
│                      - Menu navigation                           │
│                      - Session management                        │
└──────────┬──────────────────────────────────────────────────────┘
           │
┌──────────▼──────────────────────────────────────────────────────┐
│                        MODEL LAYER                              │
│                    (Eloquent ORM Models)                         │
├──────────────────────────────────────────────────────────────────┤
│  ● User          - Hybrid authentication (Web + USSD)           │
│  ● Hall          - Time-based booking resources                 │
│  ● Event         - Ticket-based booking resources               │
│  ● Booking       - Polymorphic bookings (Hall/Event)            │
│  ● Transaction   - Payment records                              │
│  ● UssdSession   - USSD state storage                           │
└──────────┬──────────────────────────────────────────────────────┘
           │
┌──────────▼──────────────────────────────────────────────────────┐
│                      DATABASE LAYER                             │
│                      MySQL 8.0                                  │
│                         +                                       │
│                    Redis (Cache)                                │
│                  (for USSD sessions)                            │
└──────────────────────────────────────────────────────────────────┘
```

---

## Component Interaction Flows

### 1. Web Booking Flow

```
User (Browser)
    │
    ▼
[BookingController]
    │
    ├─→ [BookingService.createBooking()]
    │       ├─→ Check availability (with DB lock)
    │       ├─→ Create booking (status: pending)
    │       └─→ Return booking object
    │
    ├─→ [PaymentService.initiatePayment()]
    │       ├─→ Create transaction record
    │       └─→ Call Africa's Talking STK Push API
    │
    └─→ Redirect to confirmation page

    [Africa's Talking Payment Callback]
           │
           ▼
    [PaymentController.callback()]
           │
           ├─→ [PaymentService.handleCallback()]
           │       ├─→ Update transaction status
           │       └─→ [BookingService.confirmBooking()]
           │               ├─→ Update booking status
           │               └─→ Increment event slots (if event)
           │
           └─→ [SmsService.sendBookingConfirmation()]
```

### 2. USSD Booking Flow

```
User (Phone)  →  Dials *384*10#
    │
    ▼
[MNO]  →  Africa's Talking  →  POST /api/ussd
    │
    ▼
[UssdController.handle()]
    │
    ▼
[UssdMenuService.handleRequest()]
    │
    ├─→ Get/Create UssdSession from DB
    ├─→ Parse user input
    ├─→ Route to appropriate menu handler
    ├─→ Update session state
    └─→ Return response ("CON..." or "END...")

    [On booking confirmation]
           │
           ├─→ [BookingService.createBooking()]
           ├─→ [PaymentService.initiatePayment()]
           └─→ "END Please check your phone for payment prompt"
```

---

## Key Architectural Decisions

### 1. Service Layer as Single Source of Truth

**Decision:** All business logic resides in services, not controllers.

**Rationale:**

- **Code Reuse:** Web and USSD controllers call the SAME service methods
- **Consistency:** Identical booking logic regardless of interface
- **Testability:** Business logic can be unit tested independently
- **Maintainability:** Changes made once, affect both interfaces

**Example:**

```php
// Web Controller
public function store(Request $request) {
    $booking = $this->bookingService->createBooking(...);
}

// USSD Controller (via UssdMenuService)
$booking = app(BookingService::class)->createBooking(...);
```

### 2. Polymorphic Booking System

**Decision:** Single `bookings` table for both halls and events.

**Rationale:**

- **Simplicity:** Unified booking history queries
- **Extensibility:** Easy to add new resource types (e.g., equipment)
- **Performance:** Single table join for user bookings

**Implementation:**

```php
// Booking polymorphic relationship
$booking->resource(); // Returns Hall or Event model
```

### 3. Database Locking for Concurrency

**Decision:** Use `lockForUpdate()` during availability checks.

**Rationale:**

- **Race Condition Prevention:** Prevents double-booking when multiple users book simultaneously
- **Atomicity:** Availability check and booking creation happen in same transaction
- **Data Integrity:** Ensures database consistency under load

**Implementation:**

```php
DB::transaction(function () {
    $hall = Hall::lockForUpdate()->find($hallId);
    // Check availability
    // Create booking
});
```

### 4. USSD State Management

**Decision:** Store session state in database table (not in-memory).

**Rationale:**

- **USSD Protocol:** Stateless by nature, need persistent state
- **MNO Timeout:** 180-second session timeout, must track across requests
- **Scalability:** Works across multiple servers (load balancing)

**Implementation:**

```php
// UssdSession stores:
// - current_menu: Where user is in menu tree
// - menu_data: Temporary selections (hall_id, date, etc.)
// - expires_at: Auto-cleanup after MNO timeout
```

### 5. Reservation Hold System

**Decision:** 5-minute hold on pending bookings.

**Rationale:**

- **User Experience:** Time to complete payment without losing slot
- **Resource Efficiency:** Prevents indefinite blocking of slots
- **Automated Cleanup:** Scheduled task expires stale bookings

**Implementation:**

```php
// On booking creation
$booking->hold_expires_at = Carbon::now()->addMinutes(5);

// Scheduled task (every minute)
app(BookingService::class)->expireHoldBookings();
```

---

## Technology Choices

| Technology           | Purpose                | Justification                                                                                                 |
| -------------------- | ---------------------- | ------------------------------------------------------------------------------------------------------------- |
| **Laravel 11**       | Backend Framework      | - Industry standard for PHP<br>- Built-in ORM (Eloquent)<br>- Authentication scaffolding<br>- Task scheduling |
| **MySQL 8.0**        | Primary Database       | - ACID compliance<br>- Row-level locking support<br>- JSON column support<br>- Wide hosting availability      |
| **Redis**            | Cache/Sessions         | - Fast session storage for USSD<br>- Optional (fallback to file/database)                                     |
| **Africa's Talking** | USSD/Payment Gateway   | - East Africa market leader<br>- Comprehensive API<br>- Sandbox for testing<br>- SMS integration included     |
| **Tailwind CSS**     | Frontend Styling       | - Utility-first CSS<br>- Responsive by default<br>- Small bundle size                                         |
| **Alpine.js**        | Frontend Interactivity | - Lightweight (~15KB)<br>- Vue-like syntax<br>- No build step required                                        |

---

## Security Considerations

### 1. Authentication

- **Web:** Password hashing via `bcrypt`
- **USSD:** 4-digit PIN (encrypted at rest)
- **Sessions:** Laravel's encrypted session cookies

### 2. Payment Validation

- **Callback Verification:** Validate Africa's Talking IP addresses
- **Transaction Matching:** Cross-reference amount, phone, booking ID

### 3. Input Sanitization

- **Web:** Laravel's validation rules
- **USSD:** Regex validation for date/time inputs
- **SQL Injection:** Protected by Eloquent ORM parameterization

### 4. Rate Limiting

- **API Routes:** Throttle middleware on webhook endpoints
- **USSD:** MNO-level rate limiting (180s session timeout)

---

## Performance Optimizations

### 1. Database Indexing

- **Composite Index:** `(resource_type, resource_id, booking_date, status)` for availability checks
- **Foreign Keys:** Indexed by default for join performance

### 2. Eager Loading

```php
// Prevent N+1 queries
$bookings = Booking::with('resource', 'user')->get();
```

### 3. USSD Character Limits

- **Per Screen:** 160 characters (safe cross-network)
- **Pagination:** 3 items per page to stay under limit

### 4. Response Time

- **Target:** < 2 seconds for USSD responses
- **Caching:** UssdSession in Redis for fast lookups
- **Database:** Optimized queries with indexes

---

## Scalability Considerations

### Horizontal Scaling

- **Stateless Controllers:** Can run on multiple servers
- **Session Storage:** Centralized in Redis (shared across servers)
- **Load Balancer:** Distribute USSD webhook requests

### Database Scaling

- **Read Replicas:** For resource browsing (high read, low write)
- **Partitioning:** bookings table by date (future enhancement)
- **Connection Pooling:** Laravel's database connection management

---

## Error Handling Strategy

### 1. USSD Errors

```php
catch (\Exception $e) {
    Log::error('USSD Error', [...]);
    return "END An error occurred. Please try again.";
}
```

### 2. Payment Failures

- **Transaction Logged:** All responses stored in `provider_response`
- **User Notification:** SMS with failure reason
- **Booking Status:** Remains pending, will expire after hold timeout

### 3. Logging

- **Info:** All USSD requests, payment initiations, confirmations
- **Warning:** Validation failures, duplicate bookings
- **Error:** Exception traces, API failures

**Log Location:** `storage/logs/laravel.log`

---

## Future Enhancements

1. **Multi-Tenancy:** Support for multiple community organizations
2. **Refund System:** Cancellation with automated refunds
3. **Email Notifications:** In addition to SMS
4. **Mobile App:** Native iOS/Android apps
5. **Analytics Dashboard:** Booking trends, revenue reports
6. **Multi-Language:** Swahili + English USSD menus

---

**Document Version:** 1.0  
**Last Updated:** February 2026  
**Maintained By:** Project Team
