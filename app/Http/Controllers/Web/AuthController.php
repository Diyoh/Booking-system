<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * AuthController
 * 
 * Handles user authentication for the web interface.
 * 
 * Features:
 * - User registration with phone number validation
 * - Login with email/password
 * - Logout functionality
 * - Password validation and hashing
 * 
 * Note: Users can register via web (email/password) or USSD (phone/PIN).
 * This controller handles web registration only.
 */
class AuthController extends Controller
{
    /**
     * Show registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle user registration.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20', 'unique:users,phone_number', 'regex:/^\+[0-9]{10,15}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'phone_number.regex' => 'Phone number must be in international format (e.g., +254..., +237...)',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => false,
        ]);

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Registration successful! Welcome to Community Booking.');
    }

    /**
     * Show login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validate credentials
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        // Attempt login
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Redirect based on role
            if (Auth::user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Welcome back, ' . Auth::user()->name);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . Auth::user()->name);
        }

        // Login failed
        return redirect()->back()
            ->withErrors(['email' => 'The provided credentials do not match our records.'])
            ->withInput($request->only('email'));
    }

    /**
     * Handle logout request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
