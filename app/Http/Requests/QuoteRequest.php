<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'validity_date' => ['required', 'date', 'after:today'],
            'status' => ['required', Rule::in(['draft', 'sent', 'accepted', 'declined', 'expired', 'converted'])],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.rate' => ['required', 'numeric', 'gte:0'],
            'items.*.gst_percent' => ['required', 'numeric', 'between:0,100'],
        ];
    }

    public function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))->map(function ($item) {
            return array_merge([
                'qty' => (float) ($item['qty'] ?? 0),
                'rate' => (float) ($item['rate'] ?? 0),
                'gst_percent' => (float) ($item['gst_percent'] ?? 0),
            ], $item);
        })->all();

        $this->merge([
            'items' => $items,
        ]);
    }
}
