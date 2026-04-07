<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceCallLog extends Model
{
    use BelongsToUser;
    use HasFactory;

    protected $fillable = [
        'user_id',
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

    protected static function booted(): void
    {
        static::creating(function (self $callLog): void {
            if (! empty($callLog->user_id) || empty($callLog->invoice_number)) {
                return;
            }

            $userId = Invoice::withoutGlobalScopes()
                ->where('invoice_number', $callLog->invoice_number)
                ->value('user_id');

            if ($userId) {
                $callLog->user_id = (int) $userId;
            }
        });
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_number', 'invoice_number');
    }
}
