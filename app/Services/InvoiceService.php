<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Order;
use App\Models\Quote;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService extends ModuleService
{
    public function moduleName(): string
    {
        return 'Invoices';
    }

    public function generateQuoteNumber(int $userId): string
    {
        $year = now()->format('Y');
        $prefix = sprintf('QT-%s-', $year);

        $lastQuote = Quote::where('user_id', $userId)
            ->where('quote_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        if (! $lastQuote) {
            $sequence = 1;
        } else {
            $sequence = (int) Str::afterLast($lastQuote->quote_number, '-');
            $sequence++;
        }

        return sprintf('%s%03d', $prefix, $sequence);
    }

    public function calculateGST(Client $client, float $amount, float $gstPercent): array
    {
        $businessState = Config::get('invoice.state');

        $gstValue = ($amount * ($gstPercent / 100));

        if ($client && $client->state === $businessState) {
            $split = $gstValue / 2;

            return [
                'cgst' => $split,
                'sgst' => $split,
                'igst' => 0,
            ];
        }

        return [
            'cgst' => 0,
            'sgst' => 0,
            'igst' => $gstValue,
        ];
    }

    public function convertQuoteToOrder(Quote $quote): Order
    {
        if ($quote->order_id) {
            return $quote->order()->first();
        }

        return DB::transaction(function () use ($quote) {
            $order = Order::create([
                'user_id' => $quote->user_id,
                'client_id' => $quote->client_id,
                'quote_id' => $quote->id,
                'order_number' => str_replace('QT', 'OR', $quote->quote_number),
                'status' => 'confirmed',
                'total_amount' => $quote->total,
                'billed_amount' => 0,
            ]);

            foreach ($quote->items as $item) {
                $order->items()->create([
                    'name' => $item->name,
                    'qty' => $item->qty,
                    'rate' => $item->rate,
                    'gst_percent' => $item->gst_percent,
                ]);
            }

            $quote->status = 'converted';
            $quote->order_id = $order->id;
            $quote->save();

            return $order;
        });
    }
}
