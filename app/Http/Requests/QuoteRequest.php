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
            'issue_date' => ['required', 'date'],
            'validity_date' => ['required', 'date', 'after:today'],
            'status' => ['required', Rule::in(['draft', 'sent', 'accepted', 'declined', 'expired', 'converted'])],
            'discount_type' => ['required', 'in:flat,percent'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'round_off' => ['nullable', 'numeric'],
            'currency' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'payment_terms' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'salesperson' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
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
            'issue_date' => $this->input('issue_date') ?? now()->toDateString(),
            'discount_type' => $this->input('discount_type', 'flat'),
            'discount_value' => (float) ($this->input('discount_value', 0)),
            'round_off' => $this->input('round_off') === '' ? null : (float) $this->input('round_off'),
            'currency' => $this->input('currency', 'INR'),
            'payment_terms' => $this->input('payment_terms'),
            'terms_conditions' => $this->input('terms_conditions'),
            'salesperson' => $this->input('salesperson'),
            'reference_no' => $this->input('reference_no'),
        ]);
    }
}
