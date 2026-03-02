<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * UssdSession Model
 * 
 * Manages USSD session state for the stateless USSD protocol.
 * Each USSD interaction gets a unique session_id from the MNO.
 * We store the current menu position and temporary data.
 * 
 * @property int $id
 * @property string $session_id
 * @property string $phone_number
 * @property string $current_menu
 * @property array|null $menu_data
 * @property string|null $last_input
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class UssdSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'session_id',
        'phone_number',
        'current_menu',
        'menu_data',
        'last_input',
        'expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'menu_data' => 'array',  // JSON decode
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Boot method to handle model events.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Set expiry time on creation (180 seconds - MNO timeout)
        static::creating(function ($session) {
            if (empty($session->expires_at)) {
                $session->expires_at = Carbon::now()->addSeconds(
                    config('app.ussd_session_timeout', 180)
                );
            }
        });
    }

    /**
     * Scope query to find session by session_id.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sessionId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySessionId($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope query to find expired sessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now());
    }

    /**
     * Update session state and data.
     *
     * @param string $menu
     * @param array|null $data
     * @param string|null $lastInput
     * @return bool
     */
    public function updateState(string $menu, ?array $data = null, ?string $lastInput = null): bool
    {
        $this->current_menu = $menu;
        
        if ($data !== null) {
            $this->menu_data = array_merge($this->menu_data ?? [], $data);
        }
        
        if ($lastInput !== null) {
            $this->last_input = $lastInput;
        }

        // Extend expiry time on each interaction
        $this->expires_at = Carbon::now()->addSeconds(
            config('app.ussd_session_timeout', 180)
        );

        return $this->save();
    }

    /**
     * Get a specific value from menu_data.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getData(string $key, $default = null)
    {
        return data_get($this->menu_data, $key, $default);
    }

    /**
     * Set a specific value in menu_data.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setData(string $key, $value): bool
    {
        $data = $this->menu_data ?? [];
        data_set($data, $key, $value);
        $this->menu_data = $data;

        return $this->save();
    }

    /**
     * Check if session has expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }

    /**
     * Clear menu data (useful when starting new flow).
     *
     * @return bool
     */
    public function clearData(): bool
    {
        $this->menu_data = [];
        return $this->save();
    }
}
