<?php

namespace App\Services;

use App\Models\UssdSession;
use App\Models\User;
use App\Models\Hall;
use App\Models\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * UssdMenuService
 * 
 * USSD State Machine and Menu Navigation System.
 * 
 * USSD Protocol Challenge:
 * - USSD is stateless (each request is independent)
 * - We need to "remember" where the user is in the menu
 * - Solution: Store session state in database/cache
 * 
 * Menu Structure (Max 4 levels deep):
 * Level 1: Main Menu (Browse Halls, Browse Events, My Bookings, Register)
 * Level 2: Resource List (paginated, max 3 items per page)
 * Level 3: Input date/time or quantity
 * Level 4: Confirmation
 * 
 * Character Limit: 160 characters per screen (safe cross-network limit)
 * Response Time: < 2 seconds (MNO requirement)
 * 
 * @see docs/modules/ussd-service.md for detailed menu flowchart
 */
class UssdMenuService
{
    /**
     * Items per page for lists (keeping it small due to character limit).
     */
    const ITEMS_PER_PAGE = 3;

    /**
     * Handle incoming USSD request.
     * 
     * This is the main entry point called by UssdController.
     * Determines the current menu state and returns appropriate response.
     *
     * @param string $sessionId MNO session ID
     * @param string $phoneNumber User's phone number
     * @param string $text User input path (e.g., "1*2*3")
     * @return string USSD screen text
     */
    public function handleRequest(string $sessionId, string $phoneNumber, string $text): string
    {
        // Get or create session
        $session = UssdSession::bySessionId($sessionId)->first();
        
        if (!$session) {
            $session = UssdSession::create([
                'session_id' => $sessionId,
                'phone_number' => $phoneNumber,
                'current_menu' => 'main',
                'menu_data' => [],
            ]);
        }

        // Parse user input
        $inputs = empty($text) ? [] : explode('*', $text);
        $currentInput = end($inputs) ?: '';

        // Route to appropriate menu handler
        return $this->routeToMenu($session, $currentInput, $phoneNumber);
    }

    /**
     * Route request to the appropriate menu based on current state.
     *
     * @param UssdSession $session
     * @param string $input
     * @param string $phoneNumber
     * @return string
     */
    protected function routeToMenu(UssdSession $session, string $input, string $phoneNumber): string
    {
        $menu = $session->current_menu;

        return match ($menu) {
            'main' => $this->mainMenu($session, $input),
            'register' => $this->registerMenu($session, $input, $phoneNumber),
            'browse_halls' => $this->browseHallsMenu($session, $input),
            'browse_events' => $this->browseEventsMenu($session, $input),
            'select_date' => $this->selectDateMenu($session, $input),
            'select_time' => $this->selectTimeMenu($session, $input),
            'enter_pin' => $this->enterPinMenu($session, $input, $phoneNumber),
            'confirm_booking' => $this->confirmBookingMenu($session, $input, $phoneNumber),
            'my_bookings' => $this->myBookingsMenu($session, $phoneNumber),
            default => $this->mainMenu($session, $input),
        };
    }

    /**
     * Main Menu Screen.
     *
     * @param UssdSession $session
     * @param string $input
     * @return string
     */
    protected function mainMenu(UssdSession $session, string $input): string
    {
        if (empty($input)) {
            // First time - show menu
            $session->updateState('main');
            
            return "CON Welcome to Community Booking\n" .
                   "1. Browse Halls\n" .
                   "2. Browse Events\n" .
                   "3. My Bookings\n" .
                   "4. Register";
        }

        // Handle selection
        switch ($input) {
            case '1':
                $session->updateState('browse_halls', ['page' => 1]);
                return $this->browseHallsMenu($session, '');
            
            case '2':
                $session->updateState('browse_events', ['page' => 1]);
                return $this->browseEventsMenu($session, '');
            
            case '3':
                return $this->myBookingsMenu($session, $session->phone_number);
            
            case '4':
                $session->updateState('register', ['step' => 'name']);
                return "CON Enter your full name:";
            
            default:
                return "CON Invalid option. Try again:\n1. Halls\n2. Events\n3. My Bookings\n4. Register";
        }
    }

    /**
     * Registration Menu.
     *
     * @param UssdSession $session
     * @param string $input
     * @param string $phoneNumber
     * @return string
     */
    protected function registerMenu(UssdSession $session, string $input, string $phoneNumber): string
    {
        $step = $session->getData('step');

        if ($step === 'name') {
            $session->setData('name', $input);
            $session->updateState('register', ['step' => 'pin']);
            return "CON Create 4-digit PIN:";
        }

        if ($step === 'pin') {
            if (strlen($input) !== 4 || !is_numeric($input)) {
                return "CON PIN must be 4 digits. Try again:";
            }
            
            $session->setData('pin', $input);
            $session->updateState('register', ['step' => 'confirm_pin']);
            return "CON Confirm your PIN:";
        }

        if ($step === 'confirm_pin') {
            $pin = $session->getData('pin');
            
            if ($input !== $pin) {
                $session->updateState('register', ['step' => 'pin']);
                return "CON PINs don't match. Create PIN again:";
            }

            // Create user
            $user = User::updateOrCreate(
                ['phone_number' => $phoneNumber],
                [
                    'name' => $session->getData('name'),
                    'ussd_pin' => $pin,
                ]
            );

            return "END Registration successful! Welcome " . $user->name . ".";
        }

        return "END Error in registration. Please try again.";
    }

    /**
     * Browse Halls Menu (with pagination).
     *
     * @param UssdSession $session
     * @param string $input
     * @return string
     */
    protected function browseHallsMenu(UssdSession $session, string $input): string
    {
        $page = $session->getData('page', 1);
        
        $halls = Hall::active()
            ->orderBy('name')
            ->skip(($page - 1) * self::ITEMS_PER_PAGE)
            ->take(self::ITEMS_PER_PAGE + 1)  // Get one extra to check if more exist
            ->get();

        if ($halls->isEmpty()) {
            return "END No halls available at the moment.";
        }

        if (empty($input)) {
            // Display list
            $menu = "CON Available Halls (Page $page):\n";
            $displayItems = $halls->take(self::ITEMS_PER_PAGE);
            
            foreach ($displayItems as $index => $hall) {
                $key = $index + 1;
                $menu .= "$key. {$hall->name} - {$hall->formatted_price}/hr\n";
            }

            // Add next/back options
            if ($halls->count() > self::ITEMS_PER_PAGE) {
                $menu .= "9. Next\n";
            }
            if ($page > 1) {
                $menu .= "0. Back";
            }

            return $menu;
        }

        // Handle selection
        if ($input === '9' && $halls->count() > self::ITEMS_PER_PAGE) {
            $session->setData('page', $page + 1);
            return $this->browseHallsMenu($session, '');
        }

        if ($input === '0' && $page > 1) {
            $session->setData('page', $page - 1);
            return $this->browseHallsMenu($session, '');
        }

        if (is_numeric($input) && $input >= 1 && $input <= self::ITEMS_PER_PAGE) {
            $selectedHall = $halls->get($input - 1);
            if ($selectedHall) {
                $session->setData('hall_id', $selectedHall->id);
                $session->updateState('select_date');
                return "CON Enter date (DD-MM-YYYY):";
            }
        }

        return "CON Invalid selection. Try again.";
    }

    /**
     * Browse Events Menu.
     *
     * @param UssdSession $session
     * @param string $input
     * @return string
     */
    protected function browseEventsMenu(UssdSession $session, string $input): string
    {
        $events = Event::active()->upcoming()->available()->take(5)->get();

        if ($events->isEmpty()) {
            return "END No upcoming events available.";
        }

        if (empty($input)) {
            $menu = "CON Upcoming Events:\n";
            foreach ($events as $index => $event) {
                $key = $index + 1;
                $menu .= "$key. {$event->name} - {$event->formatted_price}\n";
            }
            return $menu;
        }

        if (is_numeric($input) && $input >= 1 && $input <= $events->count()) {
            $selectedEvent = $events->get($input - 1);
            $session->setData('event_id', $selectedEvent->id);
            $session->updateState('enter_pin');
            return "CON Enter your 4-digit PIN:";
        }

        return "CON Invalid selection.";
    }

    /**
     * Select Date Menu.
     *
     * @param UssdSession $session
     * @param string $input
     * @return string
     */
    protected function selectDateMenu(UssdSession $session, string $input): string
    {
        // Validate date format
        if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $input)) {
            return "CON Invalid format. Enter date (DD-MM-YYYY):";
        }

        $session->setData('date', $input);
        $session->updateState('select_time');
        return "CON Enter start time (HH:MM):";
    }

    /**
     * Select Time Menu.
     *
     * @param UssdSession $session
     * @param string $input
     * @return string
     */
    protected function selectTimeMenu(UssdSession $session, string $input): string
    {
        if (!preg_match('/^\d{2}:\d{2}$/', $input)) {
            return "CON Invalid format. Enter time (HH:MM):";
        }

        $session->setData('start_time', $input);
        $session->updateState('enter_pin');
        return "CON Enter duration (hours):";
    }

    /**
     * Enter PIN Menu.
     *
     * @param UssdSession $session
     * @param string $input
     * @param string $phoneNumber
     * @return string
     */
    protected function enterPinMenu(UssdSession $session, string $input, string $phoneNumber): string
    {
        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user || !$user->hasUssdPin()) {
            return "END Please register first by dialing *384*10# and selecting option 4.";
        }

        if (!$user->verifyUssdPin($input)) {
            return "END Invalid PIN. Transaction cancelled.";
        }

        $session->updateState('confirm_booking');
        return $this->confirmBookingMenu($session, '', $phoneNumber);
    }

    /**
     * Confirm Booking Menu.
     *
     * @param UssdSession $session
     * @param string $input
     * @param string $phoneNumber
     * @return string
     */
    protected function confirmBookingMenu(UssdSession $session, string $input, string $phoneNumber): string
    {
        $user = User::where('phone_number', $phoneNumber)->first();

        // Summary screen
        if (empty($input)) {
            $hallId = $session->getData('hall_id');
            $hall = Hall::find($hallId);
            
            $summary = "CON Confirm Booking:\n{$hall->name}\nDate: {$session->getData('date')}\n1. Confirm\n2. Cancel";
            return $summary;
        }

        if ($input === '1') {
            // Create booking and initiate payment
            // Implementation continues...
            return "END Please check your phone for payment prompt.";
        }

        return "END Booking cancelled.";
    }

    /**
     * My Bookings Menu.
     *
     * @param UssdSession $session
     * @param string $phoneNumber
     * @return string
     */
    protected function myBookingsMenu(UssdSession $session, string $phoneNumber): string
    {
        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return "END Please register first.";
        }

        // Send booking history via SMS
        app(SmsService::class)->sendBookingHistory($user);

        return "END Your booking history has been sent via SMS.";
    }
}
