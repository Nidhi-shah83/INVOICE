<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceCallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'promised_payment_date',
        'confidence',
        'notes',
        'conversation',
        'call_started_at',
        'call_ended_at',
    ];

    protected $casts = [
        'promised_payment_date' => 'date',
        'call_started_at' => 'datetime',
        'call_ended_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_number', 'invoice_number');
    }
}
