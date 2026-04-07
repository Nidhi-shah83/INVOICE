<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use BelongsToUser;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'quote_id',
        'order_number',
        'status',
        'total_amount',
        'billed_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'billed_amount' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total_amount - $this->billed_amount);
    }
}
