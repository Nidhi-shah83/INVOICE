<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'name',
        'qty',
        'rate',
        'gst_percent',
        'amount',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
