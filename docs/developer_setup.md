# Booking System - Developer Setup Guide

Welcome to the Community Booking System project! This guide will help you set up the project on your local machine, configure the database, and establish a connection to the Africa's Talking USSD Sandbox for local testing.

## Prerequisites

Ensure your local environment has the following installed:

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL
- Git
- [Ngrok](https://ngrok.com/download) (Required for testing the USSD simulator locally)

---

## 1. General Laravel Setup

First, clone the repository and install the standard dependencies:

```bash
# Clone the repository
git clone https://github.com/Diyoh/Booking-system.git
cd Booking-system

# Install PHP dependencies
composer install

# Install Node dependencies and build Tailwind CSS assets
npm install
npm run build
```

Next, duplicate the example environment file to create your own local config:

```bash
cp .env.example .env
```

Generate your unique local application key:

```bash
php artisan key:generate
```

---

## 2. Database Configuration

1. Open your new `.env` file and look for the `DB_` section.
2. Ensure the credentials match your local MySQL server.
3. Create a local database named `booking_system` (or whatever name you set in `.env`).
4. Run the database migrations to build your tables (this includes a `cache` table required for Laravel 11's default cache driver):

```bash
php artisan migrate
```

_(Optional: If the project has initial seeders for dummy Halls and Events, run `php artisan db:seed`)_

---

## 3. Africa\'s Talking Credentials

The system heavily integrates with Africa\'s Talking for SMS and USSD functionality. You must add the shared team credentials (or your own Sandbox credentials) to your `.env` file so the simulator doesn\'t crash:

```env
AT_USERNAME=sandbox
AT_API_KEY=your_sandbox_api_key_here
AT_SENDER_ID=your_sender_id_here
AT_ENVIRONMENT=sandbox
```

---

## 4. Local USSD Simulation Pipeline (Crucial)

Since Africa\'s Talking requires a public URL to send webhooks to when a user dials the USSD simulator, you **must** funnel public internet traffic to your local Laravel server using Ngrok.

You will need **two separate terminal windows** open at the same time:

### Terminal 1: Start Laravel

Start the local artisan development server:

```bash
php artisan serve
```

_(This typically runs on port `8000` by default)_

### Terminal 2: Start Ngrok

In a separate terminal, launch Ngrok and tell it to mirror the exact port Laravel just launched on:

```bash
ngrok http 8000
```

Ngrok will immediately generate a temporary, unique public URL for you (e.g., `https://random-words.ngrok-free.dev`).

---

## 5. Connecting the Simulator to Your Localhot

Finally, you need to link everything together:

1. Copy the unique `ngrok` URL you just generated from Terminal 2.
2. Paste it into your local `.env` file like so:
   ```env
   NGROK_URL=https://your-unique-url.ngrok-free.dev
   ```
3. Run `php artisan config:clear` in your terminal so Laravel registers the new URL mapping.
4. Log into the **Africa\'s Talking Sandbox Dashboard** -> **USSD** -> **Service Codes**.
5. Find the USSD code assigned to the project (e.g., `*384*10#`).
6. Set the **Callback URL** in the dashboard to exactly:
   `https://your-unique-url.ngrok-free.dev/api/ussd`

### Testing

Once you save the Callback URL, open the Africa\'s Talking web simulator and dial the code. Africa\'s Talking will now securely bounce the USSD request to your Ngrok tunnel, straight into your exact local Laravel environment!
