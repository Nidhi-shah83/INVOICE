<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class QuoteService
{
    public function __construct(protected InvoiceService $invoiceService)
    {
    }

    public function generateQuoteNumber(int $userId): string
    {
        return $this->invoiceService->generateQuoteNumber($userId);
    }

    public function persist(array $data, ?Quote $quote = null): Quote
    {
        $userId = $data['user_id'];
        $client = Client::findOrFail($data['client_id']);
        $quote = $quote ?? new Quote();

        $items = $this->sanitizeItems($data['items'] ?? []);

        $totals = $this->invoiceService->calculateQuoteTotals(
            $client,
            $items,
            $data['discount_type'] ?? 'flat',
            (float) ($data['discount_value'] ?? 0),
            array_key_exists('round_off', $data) ? (float) $data['round_off'] : null,
        );

        $quoteNumber = $quote->quote_number ?: $this->invoiceService->generateQuoteNumber($userId);
        $quote->fill([
            'user_id' => $userId,
            'client_id' => $client->id,
            'quote_number' => $quoteNumber,
            'issue_date' => $data['issue_date'] ?? now()->toDateString(),
            'validity_date' => $data['validity_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'discount_type' => $totals['discount_type'],
            'discount_value' => $totals['discount_value'],
            'discount_amount' => $totals['discount_amount'],
            'round_off' => $totals['round_off'],
            'grand_total' => $totals['grand_total'],
            'currency' => $data['currency'] ?? 'INR',
            'payment_terms' => $data['payment_terms'] ?? null,
            'terms_conditions' => $data['terms_conditions'] ?? null,
            'salesperson' => $data['salesperson'] ?? null,
            'reference_no' => $data['reference_no'] ?? null,
        ]);

        $quote->subtotal = $totals['subtotal'];
        $quote->cgst = $totals['cgst'];
        $quote->sgst = $totals['sgst'];
        $quote->igst = $totals['igst'];
        $quote->total = $totals['total'];

        DB::transaction(function () use ($quote, $items) {
            $quote->save();

            $quote->items()->delete();

            foreach ($items as $item) {
                $lineAmount = $item['qty'] * $item['rate'];

                $quote->items()->create([
                    'name' => $item['name'],
                    'qty' => $item['qty'],
                    'rate' => $item['rate'],
                    'gst_percent' => $item['gst_percent'],
                    'amount' => $lineAmount,
                ]);
            }

            $quote->save();
        });

        return $quote;
    }

    public function persistItems(Quote $quote): void
    {
        $quote->load('items');
    }

    protected function sanitizeItems(array $items): array
    {
        $filtered = collect($items)
            ->map(fn ($item) => [
                'name' => trim($item['name'] ?? ''),
                'qty' => max(0, (float) ($item['qty'] ?? 0)),
                'rate' => max(0, (float) ($item['rate'] ?? 0)),
                'gst_percent' => max(0, (float) ($item['gst_percent'] ?? 0)),
            ])
            ->filter(fn ($item) => $item['name'] !== '' || $item['qty'] > 0 || $item['rate'] > 0)
            ->values()
            ->all();

        if (empty($filtered)) {
            throw new InvalidArgumentException('At least one quote item must be provided.');
        }

        return $filtered;
    }
}
