<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User Model
 * 
 * Represents user accounts for both Web and USSD interfaces.
 * The phone_number field serves as the universal identifier,
 * allowing users to authenticate via web (email/password) or
 * USSD (phone/PIN) seamlessly.
 * 
 * Relationships:
 * - bookings: One-to-many with Booking model
 * - transactions: One-to-many through bookings
 * 
 * @property int $id
 * @property string $phone_number
 * @property string|null $email
 * @property string|null $password
 * @property string $name
 * @property string|null $ussd_pin
 * @property bool $is_admin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone_number',
        'email',
        'name',
        'password',
        'ussd_pin',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'ussd_pin',  // Never expose PIN in API responses
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get all bookings made by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if user has set a USSD PIN.
     * Required before making USSD bookings.
     *
     * @return bool
     */
    public function hasUssdPin(): bool
    {
        return !is_null($this->ussd_pin);
    }

    /**
     * Verify USSD PIN for transaction authorization.
     *
     * @param string $pin
     * @return bool
     */
    public function verifyUssdPin(string $pin): bool
    {
        return $this->ussd_pin === $pin;
    }

    /**
     * Set USSD PIN (stores encrypted).
     *
     * @param string $pin
     * @return void
     */
    public function setUssdPin(string $pin): void
    {
        $this->ussd_pin = $pin;
        $this->save();
    }

    /**
     * Check if user is an administrator.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Scope query to only admin users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    /**
     * Scope query to only regular users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRegularUsers($query)
    {
        return $query->where('is_admin', false);
    }
}
