<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    public $timestamps = true;

    protected $casts = [
        'value' => 'string',
    ];

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->decodeValue($value),
            set: fn ($value) => $this->encodeValue($value),
        );
    }

    private function decodeValue(?string $value)
    {
        if ($value === null) {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    private function encodeValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) || is_object($value) || is_bool($value) || is_numeric($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }
}
