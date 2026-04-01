<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function __construct(protected InvoiceService $invoiceService)
    {
    }

    public function persist(array $data, ?Quote $quote = null): Quote
    {
        $userId = $data['user_id'];
        $client = Client::findOrFail($data['client_id']);
        $quote = $quote ?? new Quote();

        $quoteNumber = $quote->quote_number ?: $this->invoiceService->generateQuoteNumber($userId);
        $quote->fill([
            'user_id' => $userId,
            'client_id' => $client->id,
            'quote_number' => $quoteNumber,
            'issue_date' => $data['issue_date'] ?? now()->toDateString(),
            'validity_date' => $data['validity_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        $quote->subtotal = 0;
        $quote->cgst = 0;
        $quote->sgst = 0;
        $quote->igst = 0;

        DB::transaction(function () use ($quote, $data, $client) {
            $quote->save();

            $quote->items()->delete();

            $items = $data['items'] ?? [];
            foreach ($items as $item) {
                $lineAmount = $item['qty'] * $item['rate'];
                $gst = $this->invoiceService->calculateGST($client, $lineAmount, $item['gst_percent']);

                $quote->subtotal += $lineAmount;
                $quote->cgst += $gst['cgst'];
                $quote->sgst += $gst['sgst'];
                $quote->igst += $gst['igst'];

                $quote->items()->create([
                    'name' => $item['name'],
                    'qty' => $item['qty'],
                    'rate' => $item['rate'],
                    'gst_percent' => $item['gst_percent'],
                    'amount' => $lineAmount,
                ]);
            }

            $quote->total = $quote->subtotal + $quote->cgst + $quote->sgst + $quote->igst;

            $quote->save();
        });

        return $quote;
    }

    public function persistItems(Quote $quote): void
    {
        $quote->load('items');
    }
}
