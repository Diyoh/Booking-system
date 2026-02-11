# Hybrid Web/USSD Community Event Booking System

A professional-grade booking system built with Laravel, supporting both modern web interface and USSD (Unstructured Supplementary Service Data) for feature phone access, with simulated mobile money payments via Africa's Talking sandbox.

---

## 📋 Project Overview

This is a **Final Year Academic Capstone Project** focusing on:

- Hybrid software architecture (Web + USSD dual interfaces)
- Real-time resource booking with race condition prevention
- Mobile money integration (STK Push)
- State management across stateless USSD and stateful web sessions

**Demo Purpose**: Sandbox environment - no real money transactions

---

## 🛠️ Technology Stack

| Component         | Technology                                 |
| ----------------- | ------------------------------------------ |
| **Backend**       | Laravel 11.x (PHP 8.2+)                    |
| **Database**      | MySQL 8.0                                  |
| **Cache/Session** | Redis                                      |
| **Frontend**      | Blade Templates + Alpine.js + Tailwind CSS |
| **USSD/Payment**  | Africa's Talking (Sandbox)                 |
| **Web Server**    | Nginx / Apache (XAMPP)                     |

---

## 📦 Installation

### Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0
- Redis (optional but recommended)
- XAMPP/WAMP or standalone LAMP stack
- Ngrok (for USSD webhook testing)
- Africa's Talking Sandbox Account

### Setup Steps

1. **Clone the Repository**

   ```bash
   cd booking-system
   ```

2. **Install Dependencies**

   ```bash
   # Using XAMPP PHP
   D:\Xamp\php\php.exe ../composer.phar install

   # Or if composer is globally installed
   composer install
   ```

3. **Environment Configuration**

   ```bash
   copy .env.example .env
   ```

   Edit `.env` file with your database credentials:

   ```env
   APP_NAME="Community Booking System"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=booking_system
   DB_USERNAME=root
   DB_PASSWORD=

   CACHE_DRIVER=redis  # or 'file' if Redis unavailable
   SESSION_DRIVER=redis

   # Africa's Talking Credentials (Sandbox)
   AT_USERNAME=sandbox
   AT_API_KEY=your_africastalking_api_key_here
   AT_SENDER_ID=your_sender_id

   # Ngrok URL for webhooks (update when starting ngrok)
   NGROK_URL=https://your-ngrok-url.ngrok.io
   ```

4. **Generate Application Key**

   ```bash
   D:\Xamp\php\php.exe artisan key:generate
   ```

5. **Create Database**
   - Start XAMPP MySQL
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create database: `booking_system`

6. **Run Migrations and Seeders**

   ```bash
   D:\Xamp\php\php.exe artisan migrate --seed
   ```

7. **Start Development Server**

   ```bash
   D:\Xamp\php\php.exe artisan serve
   ```

   Application will be available at: `http://localhost:8000`

8. **Setup Ngrok for USSD Testing** (Optional)

   ```bash
   ngrok http 8000
   ```

   Update `NGROK_URL` in `.env` with the provided ngrok URL
   Configure Africa's Talking USSD callback URL:
   - USSD Callback: `https://your-ngrok-url.ngrok.io/api/ussd`
   - Payment Callback: `https://your-ngrok-url.ngrok.io/api/payment/callback`

---

## 🗂️ Project Structure

```
booking-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Web/           # Web interface controllers
│   │   │   └── Api/           # USSD & Payment webhook controllers
│   │   └── Middleware/        # Custom middleware
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic layer
│   │   ├── BookingService.php     # Core booking logic (shared)
│   │   ├── PaymentService.php     # Africa's Talking payments
│   │   ├── SmsService.php         # SMS notifications
│   │   └── UssdMenuService.php    # USSD state machine
│   └── Traits/                # Reusable traits
├── database/
│   ├── migrations/            # Database schema
│   ├── seeders/               # Sample data
│   └── factories/             # Model factories
├── resources/
│   └── views/                 # Blade templates
├── routes/
│   ├── web.php                # Web routes
│   └── api.php                # USSD/Payment webhooks
├── docs/                      # Comprehensive documentation
│   ├── architecture/          # System architecture docs
│   ├── database/              # Database schema & relationships
│   ├── api/                   # API endpoint documentation
│   └── modules/               # Module-specific docs
└── public/                    # Public assets
```

---

## 🗄️ Database Schema

### Tables Overview

1. **users** - User accounts (Web & USSD)
2. **halls** - Community hall resources
3. **events** - Event resources
4. **bookings** - Resource reservations
5. **transactions** - Payment records
6. **ussd_sessions** - USSD session state

See `docs/database/schema-reference.md` for detailed schema documentation.

---

## 🌐 Features

### Web Interface

- User authentication (login/register)
- Browse halls and events with images, details, pricing
- Interactive booking calendar
- Real-time availability checking
- Payment via Mobile Money (STK Push)
- Booking history and management
- Admin dashboard for resource/booking management

### USSD Interface

- Text-based menu navigation (max 4 levels deep)
- User registration with 4-digit PIN
- Browse halls/events (paginated for 3 items max)
- Make bookings with PIN authentication
- Payment via STK Push
- Receive SMS confirmations with reference codes

### Payment Integration

- Africa's Talking Sandbox (M-Pesa simulation)
- STK Push initiation
- Asynchronous callback handling
- Transaction status tracking
- 5-minute reservation hold system

### Notifications

- SMS booking confirmations with reference codes
- Payment receipts
- Booking history via SMS (for USSD users)

---

## 🚀 Usage

### Web Application

1. **Register/Login**
   - Navigate to http://localhost:8000
   - Create account or login
2. **Browse Resources**
   - View available halls and events
   - Filter by date, capacity, price
3. **Make Booking**
   - Select resource
   - Choose date/time (halls) or quantity (events)
   - Review and confirm
   - Complete payment via STK Push
   - Receive SMS confirmation

4. **Admin Panel** (if logged in as admin)
   - Manage halls, events, bookings
   - View analytics and reports

### USSD Application

1. **Dial USSD Code** (e.g., `*384*10#` in Africa's Talking simulator)
2. **Register** (first-time users)
   - Enter name
   - Create 4-digit PIN
3. **Browse and Book**
   - Select "Browse Halls" or "Browse Events"
   - Navigate paginated lists
   - Select resource and enter details
   - Confirm with PIN
   - Receive STK Push
   - Get SMS confirmation

---

## 📖 Documentation

Comprehensive documentation is available in the `docs/` folder:

- **Architecture**: System design, component interactions, data flow
- **Database**: ER diagrams, schema reference, relationships
- **API**: All endpoints with request/response examples
- **Modules**: Detailed documentation for each module
- **Deployment**: Installation, configuration, troubleshooting

---

## 🧪 Testing

### Manual Testing

1. **Web Booking Flow**
   - Test user registration and login
   - Browse resources
   - Complete a booking
   - Verify payment and confirmation

2. **USSD Booking Flow**
   - Use Africa's Talking USSD simulator
   - Test registration, browsing, booking
   - Verify SMS notifications

3. **Concurrency Testing**
   - Open two browsers
   - Attempt to book same resource simultaneously
   - Verify only one succeeds (double-booking prevention)

### Automated Tests

```bash
D:\Xamp\php\php.exe artisan test
```

---

## 🔒 Default Credentials

**Admin User** (created by seeder):

- Email: admin@booking.com
- Password: password123
- Phone: +254712345678

**Test User**:

- Email: user@booking.com
- Password: password123
- Phone: +254798765432

---

## 📝 API Endpoints

### USSD Webhook

```
POST /api/ussd
Content-Type: application/x-www-form-urlencoded

Parameters:
- sessionId: string
- phoneNumber: string
- text: string (user input path)
```

### Payment Callback

```
POST /api/payment/callback
Content-Type: application/json

Body: Africa's Talking payment notification
```

See `docs/api/` for complete API documentation.

---

## 💡 Key Technical Features

1. **State Management**: USSD session state stored in Redis/cache
2. **Concurrency Control**: Database locking prevents double-booking
3. **Atomic Transactions**: Booking and payment as single unit
4. **Error Handling**: Graceful error messages, validation
5. **Performance**: USSD responses < 2 seconds
6. **Security**: Password hashing, PIN encryption, webhook validation

---

## 🛣️ Roadmap

- [ ] Add multi-language support (English/Swahili)
- [ ] Implement booking cancellation
- [ ] Add email notifications
- [ ] Create mobile app (Flutter/React Native)
- [ ] Integration with live payment gateways
- [ ] Add booking analytics dashboard

---

## 🐛 Troubleshooting

### Common Issues

**Issue**: USSD webhook not receiving calls

- **Solution**: Ensure ngrok is running and URL is updated in Africa's Talking

**Issue**: Database connection error

- **Solution**: Verify MySQL is running in XAMPP, check .env credentials

**Issue**: Redis connection error

- **Solution**: Set `CACHE_DRIVER=file` in .env if Redis unavailable

See `docs/deployment/troubleshooting.md` for more solutions.

---

## 👥 Contributors

This is a **Final Year Project** developed by **[Your Name]**.

**Supervisor**: [Supervisor Name]  
**Institution**: [University Name]  
**Year**: 2026

---

## 📄 License

This project is developed for educational purposes. MIT License.

---

## 🙏 Acknowledgments

- Laravel Framework
- Africa's Talking API
- XAMPP Development Environment
- Tailwind CSS & Alpine.js

---

## 📞 Support

For issues or questions about this project:

- Check documentation in `docs/` folder
- Review troubleshooting guide
- Contact project supervisor

---

**Note**: This is a **Sandbox/Demo** system. No real money transactions occur. All payments are simulated using Africa's Talking sandbox environment.
