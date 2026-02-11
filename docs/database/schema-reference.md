# Database Schema Documentation

## Entity Relationship Diagram (Text Format)

```
┌──────────────────┐
│      users       │
├──────────────────┤
│ PK id            │──┐
│  U phone_number  │  │
│  U email         │  │
│    name          │  │
│    password      │  │
│    ussd_pin      │  │
│    is_admin      │  │
└──────────────────┘  │
                      │
                      │ 1:N
                      │
                ┌─────▼────────┐
                │   bookings   │
                ├──────────────┤
                │ PK id        │
                │ FK user_id   │
                │    resource_type  (enum: 'hall', 'event')
                │    resource_id
                │    booking_date
                │    start_time      (nullable, for halls)
                │    end_time        (nullable, for halls)
                │    quantity        (default 1, for events)
                │    total_amount
                │    status          (enum: pending, confirmed, cancelled, expired)
                │  U reference_code
                │    hold_expires_at
                │    confirmed_at
                │    source          (enum: 'web', 'ussd')
                └─────┬────────┘
                      │
       ┌──────────────┼──────────────┐
       │1:1           │              │ Polymorphic
       │              │              │
┌──────▼────────┐    │         ┌────┴──────┐   ┌────────────┐
│ transactions  │    │         │   halls   │   │   events   │
├───────────────┤    │         ├───────────┤   ├────────────┤
│ PK id         │    │         │ PK id     │   │ PK id      │
│ FK booking_id │    │         │  name     │   │  name      │
│  phone_number │    │         │  description   │  description
│  amount       │    │         │  location │   │  event_date│
│  provider_transaction_id      │  capacity │   │  start_time│
│  status       │    │         │  price_per_hour│  end_time  │
│  provider_response (JSON)     │  amenities (JSON) │  location
└───────────────┘    │         │  image_url│   │  ticket_price
                     │         │  is_active│   │  available_slots
                     │         └───────────┘   │  booked_slots
                     │                         │  image_url │
                     │                         │  is_active │
                     │                         └────────────┘
                     │
              ┌──────▼───────────┐
              │  ussd_sessions   │
              ├──────────────────┤
              │ PK id            │
              │  U session_id    │
              │    phone_number  │
              │    current_menu  │
              │    menu_data (JSON)
              │    last_input    │
              │    expires_at    │
              └──────────────────┘
```

**Legend:**

- PK: Primary Key
- FK: Foreign Key
- U: Unique Index
- 1:N: One-to-Many Relationship
- 1:1: One-to-One Relationship

---

## Table Schemas

### 1. users

**Purpose:** Store user accounts for both Web and USSD interfaces.

| Column       | Type            | Nullable | Default        | Description                                        |
| ------------ | --------------- | -------- | -------------- | -------------------------------------------------- |
| id           | BIGINT UNSIGNED | NO       | AUTO_INCREMENT | Primary key                                        |
| phone_number | VARCHAR(20)     | NO       | -              | **Universal identifier** (e.g., +254712345678)     |
| email        | VARCHAR(255)    | YES      | NULL           | Email for web login (optional for USSD-only users) |
| password     | VARCHAR(255)    | YES      | NULL           | Hashed password for web (null for USSD-only)       |
| name         | VARCHAR(255)    | NO       | -              | Full name                                          |
| ussd_pin     | VARCHAR(4)      | YES      | NULL           | Encrypted 4-digit PIN for USSD transactions        |
| is_admin     | BOOLEAN         | NO       | false          | Admin flag for dashboard access                    |
| created_at   | TIMESTAMP       | YES      | NULL           |                                                    |
| updated_at   | TIMESTAMP       | YES      | NULL           |                                                    |

**Indexes:**

- PRIMARY KEY (id)
- UNIQUE (phone_number)
- UNIQUE (email)
- INDEX (phone_number)
- INDEX (email)

**Business Rules:**

- Phone number is mandatory (serves as common identifier)
- Web users MUST have email + password
- USSD users MUST have ussd_pin
- Hybrid users can have both authentication methods

---

### 2. halls

**Purpose:** Community hall resources for time-based bookings.

| Column         | Type            | Nullable | Default        | Description                                  |
| -------------- | --------------- | -------- | -------------- | -------------------------------------------- |
| id             | BIGINT UNSIGNED | NO       | AUTO_INCREMENT | Primary key                                  |
| name           | VARCHAR(255)    | NO       | -              | Hall name                                    |
| description    | TEXT            | NO       | -              | Detailed description                         |
| location       | VARCHAR(255)    | NO       | -              | Physical address                             |
| capacity       | INT             | NO       | -              | Maximum occupancy                            |
| price_per_hour | DECIMAL(10,2)   | NO       | -              | Rental price per hour                        |
| amenities      | JSON            | YES      | NULL           | Array of amenities (e.g., ["Parking", "AC"]) |
| image_url      | VARCHAR(255)    | YES      | NULL           | Image path/URL                               |
| is_active      | BOOLEAN         | NO       | true           | Soft delete flag                             |
| created_at     | TIMESTAMP       | YES      | NULL           |                                              |
| updated_at     | TIMESTAMP       | YES      | NULL           |                                              |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (is_active, name)

**Sample Data:**

```json
{
  "name": "Town Hall",
  "capacity": 200,
  "price_per_hour": 50.0,
  "amenities": ["Parking", "Air Conditioning", "Sound System"]
}
```

---

### 3. events

**Purpose:** Event resources for ticket-based bookings.

| Column          | Type            | Nullable | Default        | Description              |
| --------------- | --------------- | -------- | -------------- | ------------------------ |
| id              | BIGINT UNSIGNED | NO       | AUTO_INCREMENT | Primary key              |
| name            | VARCHAR(255)    | NO       | -              | Event name               |
| description     | TEXT            | NO       | -              | Event description        |
| event_date      | DATE            | NO       | -              | Event occurrence date    |
| start_time      | TIME            | NO       | -              | Event start time         |
| end_time        | TIME            | NO       | -              | Event end time           |
| location        | VARCHAR(255)    | NO       | -              | Event venue              |
| ticket_price    | DECIMAL(10,2)   | NO       | -              | Price per ticket         |
| available_slots | INT             | NO       | -              | Total tickets available  |
| booked_slots    | INT             | NO       | 0              | Number of tickets booked |
| image_url       | VARCHAR(255)    | YES      | NULL           | Event poster/image       |
| is_active       | BOOLEAN         | NO       | true           | Soft delete flag         |
| created_at      | TIMESTAMP       | YES      | NULL           |                          |
| updated_at      | TIMESTAMP       | YES      | NULL           |                          |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (is_active, event_date)

**Computed Fields:**

```php
remaining_slots = available_slots - booked_slots
is_sold_out = (booked_slots >= available_slots)
```

---

### 4. bookings

**Purpose:** Central table managing all resource reservations (polymorphic).

| Column          | Type                  | Nullable | Default        | Description                            |
| --------------- | --------------------- | -------- | -------------- | -------------------------------------- |
| id              | BIGINT UNSIGNED       | NO       | AUTO_INCREMENT | Primary key                            |
| user_id         | BIGINT UNSIGNED       | NO       | -              | FK to users table                      |
| resource_type   | ENUM('hall', 'event') | NO       | -              | Type of resource                       |
| resource_id     | BIGINT UNSIGNED       | NO       | -              | ID of hall or event                    |
| booking_date    | DATE                  | NO       | -              | Date of booking                        |
| start_time      | TIME                  | YES      | NULL           | **Hall only:** booking start time      |
| end_time        | TIME                  | YES      | NULL           | **Hall only:** booking end time        |
| quantity        | INT                   | NO       | 1              | **Event only:** number of tickets      |
| total_amount    | DECIMAL(10,2)         | NO       | -              | Total cost                             |
| status          | ENUM                  | NO       | 'pending'      | pending, confirmed, cancelled, expired |
| reference_code  | VARCHAR(20)           | NO       | -              | Unique code (e.g., HALL-1234)          |
| hold_expires_at | TIMESTAMP             | YES      | NULL           | 5-min hold expiry time                 |
| confirmed_at    | TIMESTAMP             | YES      | NULL           | Payment success timestamp              |
| source          | ENUM('web', 'ussd')   | NO       | -              | Booking interface used                 |
| created_at      | TIMESTAMP             | YES      | NULL           |                                        |
| updated_at      | TIMESTAMP             | YES      | NULL           |                                        |

**Indexes:**

- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- UNIQUE (reference_code)
- INDEX (resource_type, resource_id, booking_date, status) ← **Critical for availability checks**
- INDEX (user_id, status)
- INDEX (status, hold_expires_at)

**Status Lifecycle:**

```
pending → confirmed (payment success)
pending → expired (hold timeout)
confirmed → cancelled (user cancels)
```

**Polymorphic Relationship:**

```php
// Booking can belong to Hall OR Event
if (resource_type === 'hall') {
    $resource = Hall::find(resource_id);
} else {
    $resource = Event::find(resource_id);
}
```

---

### 5. transactions

**Purpose:** Track payment transactions for bookings.

| Column                  | Type                                 | Nullable | Default        | Description                       |
| ----------------------- | ------------------------------------ | -------- | -------------- | --------------------------------- |
| id                      | BIGINT UNSIGNED                      | NO       | AUTO_INCREMENT | Primary key                       |
| booking_id              | BIGINT UNSIGNED                      | NO       | -              | FK to bookings table              |
| phone_number            | VARCHAR(20)                          | NO       | -              | Phone used for payment            |
| amount                  | DECIMAL(10,2)                        | NO       | -              | Amount charged                    |
| provider_transaction_id | VARCHAR(255)                         | YES      | NULL           | Africa's Talking transaction ID   |
| status                  | ENUM('pending', 'success', 'failed') | NO       | 'pending'      | Payment status                    |
| provider_response       | JSON                                 | YES      | NULL           | Full callback payload (for audit) |
| created_at              | TIMESTAMP                            | YES      | NULL           |                                   |
| updated_at              | TIMESTAMP                            | YES      | NULL           |                                   |

**Indexes:**

- PRIMARY KEY (id)
- FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
- INDEX (booking_id)
- INDEX (status, created_at)

**Audit Trail:**

```json
{
  "provider_response": {
    "status": "Success",
    "transactionId": "ATPid_12345",
    "amount": "50.00",
    "phoneNumber": "+254712345678"
  }
}
```

---

### 6. ussd_sessions

**Purpose:** Store USSD session state for stateless protocol.

| Column       | Type            | Nullable | Default        | Description                               |
| ------------ | --------------- | -------- | -------------- | ----------------------------------------- |
| id           | BIGINT UNSIGNED | NO       | AUTO_INCREMENT | Primary key                               |
| session_id   | VARCHAR(255)    | NO       | -              | MNO-provided session ID                   |
| phone_number | VARCHAR(20)     | NO       | -              | User's MSISDN                             |
| current_menu | VARCHAR(255)    | NO       | -              | Current menu state (e.g., 'browse_halls') |
| menu_data    | JSON            | YES      | NULL           | Temporary session data                    |
| last_input   | VARCHAR(255)    | YES      | NULL           | Last user input                           |
| expires_at   | TIMESTAMP       | NO       | -              | Session expiry (180s from creation)       |
| created_at   | TIMESTAMP       | YES      | NULL           |                                           |
| updated_at   | TIMESTAMP       | YES      | NULL           |                                           |

**Indexes:**

- PRIMARY KEY (id)
- UNIQUE (session_id)
- INDEX (session_id)
- INDEX (expires_at)

**Sample Session Data:**

```json
{
  "session_id": "ATUid_abc123",
  "current_menu": "select_date",
  "menu_data": {
    "hall_id": 3,
    "page": 1,
    "date_input": "15-02-2026"
  },
  "expires_at": "2026-02-02 10:15:00"
}
```

---

## Relationships

### One-to-Many

1. **User → Bookings**

   ```php
   User::find(1)->bookings; // Get all user's bookings
   ```

2. **Booking → Transaction**
   ```php
   Booking::find(1)->transaction; // Get payment transaction
   ```

### Polymorphic

3. **Booking → Resource (Hall or Event)**

   ```php
   $booking = Booking::find(1);
   $resource = $booking->resource; // Returns Hall or Event model
   ```

   Implemented via:
   - `resource_type` column (stores 'hall' or 'event')
   - `resource_id` column (stores ID)

---

## Data Integrity Rules

### Foreign Key Constraints

- `bookings.user_id` → `users.id` (ON DELETE CASCADE)
- `transactions.booking_id` → `bookings.id` (ON DELETE CASCADE)

### Unique Constraints

- `users.phone_number` (enforces one account per phone)
- `users.email` (enforces one account per email)
- `bookings.reference_code` (prevents duplicate codes)
- `ussd_sessions.session_id` (one session per id)

### Check Constraints (Application Level)

- Hall bookings MUST have `start_time` and `end_time`
- Event bookings MUST have `quantity >= 1`
- `booked_slots <= available_slots` for events

---

## Normalization

**Database is normalized to 3NF (Third Normal Form):**

1. **1NF:** All columns contain atomic values (no arrays except JSON)
2. **2NF:** No partial dependencies (all non-key columns depend on entire primary key)
3. **3NF:** No transitive dependencies (non-key columns don't depend on other non-key columns)

**Example:** `total_amount` is calculated at booking time and stored (denormalized for query performance), but can be recalculated from resource price.

---

## Scheduled Maintenance

### Cleanup Tasks

#### 1. Expire Hold Bookings

```sql
UPDATE bookings
SET status = 'expired'
WHERE status = 'pending'
  AND hold_expires_at < NOW();
```

**Frequency:** Every minute  
**Laravel Command:** `php artisan booking:expire-holds`

#### 2. Clean Expired USSD Sessions

```sql
DELETE FROM ussd_sessions
WHERE expires_at < NOW();
```

**Frequency:** Every 5 minutes  
**Laravel Command:** `php artisan ussd:cleanup-sessions`

---

## Sample Queries

### Check Hall Availability

```sql
SELECT COUNT(*) as conflicts
FROM bookings
WHERE resource_type = 'hall'
  AND resource_id = 1
  AND booking_date = '2026-02-15'
  AND status IN ('pending', 'confirmed')
  AND (
    (start_time < '18:00:00' AND end_time > '14:00:00')
  );
-- If conflicts = 0, slot is available
```

### Get Event Remaining Slots

```sql
SELECT
  id,
  name,
  (available_slots - booked_slots) as remaining
FROM events
WHERE is_active = true
  AND event_date >= CURRENT_DATE
  AND booked_slots < available_slots;
```

### User Booking History

```sql
SELECT
  b.reference_code,
  CASE
    WHEN b.resource_type = 'hall' THEN h.name
    WHEN b.resource_type = 'event' THEN e.name
  END as resource_name,
  b.status,
  b.total_amount
FROM bookings b
LEFT JOIN halls h ON b.resource_type = 'hall' AND b.resource_id = h.id
LEFT JOIN events e ON b.resource_type = 'event' AND b.resource_id = e.id
WHERE b.user_id = 1
ORDER BY b.created_at DESC;
```

---

**Document Version:** 1.0  
**Schema Version:** 1.0  
**Last Updated:** February 2026
