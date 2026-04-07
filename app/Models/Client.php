<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Client extends Model
{
    use BelongsToUser;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'company_name',
        'email',
        'phone',
        'alternate_phone',
        'gstin',
        'state',
        'place_of_supply',
        'address',
        'city',
        'pincode',
        'country',
        'client_type',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getGstTypeAttribute(): string
    {
        $place = $this->place_of_supply ?: $this->state;

        return $this->state && $place && $this->state === $place ? 'intra' : 'inter';
    }

    public function isBusiness(): bool
    {
        return $this->client_type === 'business';
    }
}
