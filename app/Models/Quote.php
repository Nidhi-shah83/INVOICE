<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'quote_number',
        'issue_date',
        'validity_date',
        'status',
        'subtotal',
        'cgst',
        'sgst',
        'igst',
        'total',
        'notes',
        'order_id',
        'accept_token',
        'accepted_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'validity_date' => 'date',
        'accepted_at' => 'datetime',
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
        return $this->hasMany(QuoteItem::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
}
