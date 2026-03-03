<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Location Model
 * 
 * Simple model to manage available regions/divisions for filtering
 * Halls and Events. Used to populate dropdowns in the Admin panel and public pages.
 * 
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Location extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name'];
}
