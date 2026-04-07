<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use BelongsToUser;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'order_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'status',
        'payment_link',
        'razorpay_order_id',
        'subtotal',
        'cgst',
        'sgst',
        'igst',
        'total',
        'notes',
        'pdf_path',
        'discount_type',
        'discount_value',
        'discount_amount',
        'round_off',
        'grand_total',
        'amount_paid',
        'amount_due',
        'payment_status',
        'invoice_type',
        'currency',
        'po_number',
        'reference_no',
        'payment_terms',
        'terms_conditions',
        'bank_name',
        'account_number',
        'ifsc_code',
        'upi_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'cgst' => 'decimal:2',
        'sgst' => 'decimal:2',
        'igst' => 'decimal:2',
        'total' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
    ];

    protected $appends = [
        'is_overdue',
        'formatted_grand_total',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function callLogs()
    {
        return $this->hasMany(InvoiceCallLog::class, 'invoice_number', 'invoice_number');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'sent' && $this->due_date?->isPast();
    }

    public function getFormattedGrandTotalAttribute(): string
    {
        $symbol = $this->resolveCurrencySymbol();

        return $symbol.number_format($this->grand_total ?? 0, 2);
    }

    protected function resolveCurrencySymbol(): string
    {
        $map = [
            'INR' => 'Rs ',
            'USD' => '$',
            'EUR' => 'EUR ',
            'GBP' => 'GBP ',
        ];

        $currency = strtoupper($this->currency ?? '');

        if ($currency && isset($map[$currency])) {
            return $map[$currency];
        }

        return config('invoice.currency_symbol', 'Rs ');
    }
}
