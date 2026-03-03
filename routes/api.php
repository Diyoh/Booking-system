<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UssdController;
use App\Http\Controllers\Api\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes handle webhooks from Africa's Talking for USSD and Payment.
| Routes do not require authentication (validated via Africa's Talking).
|
*/

// USSD Webhook - called by Africa's Talking when user dials USSD code (*384*...)
Route::post('/ussd', [UssdController::class, 'handle'])->name('ussd.handle');

// Payment Callback - called by Africa's Talking when STK push payment succeeds or fails
Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');

// Payment status check (for debugging/admin use)
Route::get('/payment/status', [PaymentController::class, 'checkStatus'])->name('payment.status');
