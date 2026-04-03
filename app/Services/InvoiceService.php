<?php

namespace App\Services;

use App\Mail\InvoiceSentMail;
use App\Mail\OrderConfirmedMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Quote;
use App\Services\RazorpayService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceService extends ModuleService
{
    public function moduleName(): string
    {
        return 'Invoices';
    }

    public function __construct(protected RazorpayService $razorpayService)
    {
    }

    public function generateInvoiceNumber(int $userId): string
    {
        $year = now()->format('Y');
        $prefix = sprintf('%s-%s-', Config::get('invoice.invoice_prefix', 'INV'), $year);

        $lastInvoice = Invoice::where('user_id', $userId)
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        $sequence = $lastInvoice ? (int) Str::afterLast($lastInvoice->invoice_number, '-') + 1 : 1;

        return sprintf('%s%03d', $prefix, $sequence);
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

    public function calculateQuoteTotals(?Client $client, array $items, string $discountType = 'flat', float $discountValue = 0, ?float $roundOff = null): array
    {
        $subtotal = 0;
        $normalized = [];

        foreach ($items as $item) {
            $qty = max(0, (float) ($item['qty'] ?? 0));
            $rate = max(0, (float) ($item['rate'] ?? 0));
            $gst = max(0, (float) ($item['gst_percent'] ?? 0));

            $amount = $qty * $rate;
            if ($amount <= 0) {
                continue;
            }

            $subtotal += $amount;
            $normalized[] = [
                'amount' => $amount,
                'gst_percent' => $gst,
            ];
        }

        $subtotal = round($subtotal, 2);
        $discountValue = max(0, $discountValue);
        $discountAmount = $discountType === 'percent'
            ? round($subtotal * ($discountValue / 100), 2)
            : round($discountValue, 2);
        $discountAmount = min($discountAmount, $subtotal);

        $taxableAmount = round(max(0, $subtotal - $discountAmount), 2);
        $cgst = $sgst = $igst = 0;

        if ($client && $subtotal > 0 && $taxableAmount > 0) {
            foreach ($normalized as $line) {
                $share = $subtotal > 0 ? $line['amount'] / $subtotal : 0;
                $lineDiscount = $discountAmount * $share;
                $lineTaxable = max(0, $line['amount'] - $lineDiscount);

                if ($lineTaxable <= 0) {
                    continue;
                }

                $charges = $this->calculateGST($client, $lineTaxable, $line['gst_percent']);
                $cgst += $charges['cgst'];
                $sgst += $charges['sgst'];
                $igst += $charges['igst'];
            }
        }

        $cgst = round($cgst, 2);
        $sgst = round($sgst, 2);
        $igst = round($igst, 2);

        $total = round($taxableAmount + $cgst + $sgst + $igst, 2);
        $calculatedRoundOff = is_null($roundOff)
            ? round($total, 0, PHP_ROUND_HALF_UP) - $total
            : $roundOff;
        $calculatedRoundOff = round($calculatedRoundOff, 2);

        $grandTotal = round($total + $calculatedRoundOff, 2);

        return [
            'subtotal' => $subtotal,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'taxable_amount' => $taxableAmount,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total' => $total,
            'round_off' => $calculatedRoundOff,
            'grand_total' => $grandTotal,
        ];
    }

    public function calculateInvoiceTotals(?Client $client, array $items, string $discountType = 'flat', float $discountValue = 0, ?float $roundOff = null, float $amountPaid = 0): array
    {
        $subtotal = 0;
        $normalized = [];

        foreach ($items as $item) {
            $qty = max(0, (float) ($item['qty'] ?? $item['quantity'] ?? 0));
            $rate = max(0, (float) ($item['rate'] ?? 0));
            $gst = max(0, (float) ($item['gst_percent'] ?? $item['gst'] ?? 0));

            $amount = $qty * $rate;
            if ($amount <= 0) {
                continue;
            }

            $subtotal += $amount;
            $normalized[] = [
                'amount' => $amount,
                'gst_percent' => $gst,
            ];
        }

        $subtotal = round($subtotal, 2);
        $discountValue = max(0, $discountValue);
        $discountAmount = $discountType === 'percent'
            ? round($subtotal * ($discountValue / 100), 2)
            : round($discountValue, 2);
        $discountAmount = min($discountAmount, $subtotal);

        $taxableAmount = round(max(0, $subtotal - $discountAmount), 2);
        $cgst = $sgst = $igst = 0;

        if ($client && $subtotal > 0 && $taxableAmount > 0) {
            foreach ($normalized as $line) {
                $share = $subtotal > 0 ? $line['amount'] / $subtotal : 0;
                $lineDiscount = $discountAmount * $share;
                $lineTaxable = max(0, $line['amount'] - $lineDiscount);

                if ($lineTaxable <= 0) {
                    continue;
                }

                $charges = $this->calculateGST($client, $lineTaxable, $line['gst_percent']);
                $cgst += $charges['cgst'];
                $sgst += $charges['sgst'];
                $igst += $charges['igst'];
            }
        }

        $cgst = round($cgst, 2);
        $sgst = round($sgst, 2);
        $igst = round($igst, 2);

        $total = round($taxableAmount + $cgst + $sgst + $igst, 2);
        $calculatedRoundOff = is_null($roundOff)
            ? round($total, 0, PHP_ROUND_HALF_UP) - $total
            : $roundOff;
        $calculatedRoundOff = round($calculatedRoundOff, 2);

        $grandTotal = round($total + $calculatedRoundOff, 2);
        $amountPaid = max(0, $amountPaid);
        $amountDue = round(max(0, $grandTotal - $amountPaid), 2);
        $paymentStatus = $this->resolvePaymentStatus($amountPaid, $grandTotal);

        return [
            'subtotal' => $subtotal,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'taxable_amount' => $taxableAmount,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total' => $total,
            'round_off' => $calculatedRoundOff,
            'grand_total' => $grandTotal,
            'amount_paid' => $amountPaid,
            'amount_due' => $amountDue,
            'payment_status' => $paymentStatus,
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
                'total_amount' => $quote->grand_total,
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

            Mail::to($quote->client->email)
                ->send(new OrderConfirmedMail($order->load(['items', 'client'])));

            return $order;
        });
    }

    public function createPartialInvoice(Order $order, array $items, ?string $notes = null): Invoice
    {
        return DB::transaction(function () use ($order, $items, $notes) {
            $dueDays = (int) Config::get('invoice.default_due_days', 15);
            $invoice = Invoice::create([
                'user_id' => $order->user_id,
                'client_id' => $order->client_id,
                'order_id' => $order->id,
                'invoice_number' => $this->generateInvoiceNumber($order->user_id),
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays($dueDays)->toDateString(),
                'status' => 'draft',
                'notes' => $notes,
            ]);

            $lineItems = [];

            foreach ($items as $item) {
                $orderItem = $order->items()->findOrFail($item['order_item_id']);

                $qty = max(0, (float) ($item['qty'] ?? 0));
                $remaining = max(0, $orderItem->qty - $orderItem->qty_billed);

                if ($qty <= 0 || $remaining <= 0) {
                    continue;
                }

                if ($qty > $remaining) {
                    throw new \InvalidArgumentException("Cannot bill more than remaining quantity for {$orderItem->name}.");
                }

                $amount = $qty * $orderItem->rate;

                $lineItems[] = [
                    'qty' => $qty,
                    'rate' => $orderItem->rate,
                    'gst_percent' => $orderItem->gst_percent,
                ];

                $invoice->items()->create([
                    'order_item_id' => $orderItem->id,
                    'name' => $orderItem->name,
                    'qty_billed' => $qty,
                    'rate' => $orderItem->rate,
                    'gst_percent' => $orderItem->gst_percent,
                    'amount' => $amount,
                ]);

                $orderItem->increment('qty_billed', $qty);
            }

            $invoiceTotals = $this->calculateInvoiceTotals($order->client, $lineItems, 'flat', 0, 0);
            $invoice->update([
                'subtotal' => $invoiceTotals['subtotal'],
                'cgst' => $invoiceTotals['cgst'],
                'sgst' => $invoiceTotals['sgst'],
                'igst' => $invoiceTotals['igst'],
                'total' => $invoiceTotals['total'],
                'round_off' => $invoiceTotals['round_off'],
                'grand_total' => $invoiceTotals['grand_total'],
                'amount_paid' => $invoiceTotals['amount_paid'],
                'amount_due' => $invoiceTotals['amount_due'],
                'payment_status' => $invoiceTotals['payment_status'],
                'discount_type' => $invoiceTotals['discount_type'],
                'discount_value' => $invoiceTotals['discount_value'],
                'discount_amount' => $invoiceTotals['discount_amount'],
                'currency' => config('invoice.currency', 'INR'),
            ]);

            $order->billed_amount += $invoiceTotals['grand_total'];
            $order->status = $this->resolveOrderStatus($order);
            $order->save();

            return $invoice;
        });
    }

    protected function resolveOrderStatus(Order $order): string
    {
        if ($order->billed_amount >= $order->total_amount) {
            return 'fully_billed';
        }

        if ($order->billed_amount > 0) {
            return 'partially_billed';
        }

        return $order->status ?: 'confirmed';
    }

    public function sendInvoice(Invoice $invoice): array
    {
        $invoice->load('client');

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        $path = 'invoices/'.$invoice->invoice_number.'.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        $grandTotal = (float) $invoice->grand_total;
        $currency = $invoice->currency ?? config('invoice.currency', 'INR');
        $razorpayOrder = $this->razorpayService->createOrder($grandTotal, $currency, $invoice->invoice_number);
        $paymentLink = $this->razorpayService->getCheckoutUrl($razorpayOrder['id']);

        $invoice->update([
            'status' => 'sent',
            'payment_link' => $paymentLink,
            'pdf_path' => $path,
            'amount_due' => max(0, $grandTotal - (float) $invoice->amount_paid),
            'payment_status' => $this->resolvePaymentStatus((float) $invoice->amount_paid, $grandTotal),
        ]);

        Mail::to($invoice->client->email)
            ->send(new InvoiceSentMail($invoice, $paymentLink, $pdf));

        return [
            'path' => $path,
            'order_id' => $razorpayOrder['id'],
            'link' => $paymentLink,
        ];
    }

    public function markInvoicePaid(Invoice $invoice, float $amount, string $paymentId, string $orderId): Payment
    {
        $due = max(0, $invoice->grand_total - $invoice->amount_paid);
        $captureAmount = min(max(0, $amount), $due);

        if ($captureAmount <= 0) {
            throw new \InvalidArgumentException('No outstanding amount remains for this invoice.');
        }

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => $orderId,
            'amount' => $captureAmount,
            'status' => 'captured',
        ]);

        $invoice = $this->applyPayment($invoice, $captureAmount);

        Mail::to($invoice->client->email)
            ->send(new PaymentConfirmedMail($invoice, $payment));

        return $payment;
    }

    public function overdueInvoices()
    {
        return Invoice::with('client')
            ->where('status', 'sent')
            ->whereDate('due_date', '<', now()->toDateString())
            ->get()
            ->map(fn ($invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_name' => $invoice->client->name,
                'client_email' => $invoice->client->email,
                'amount' => (float) $invoice->amount_due,
                'due_date' => $invoice->due_date?->toDateString(),
                'days_overdue' => now()->diffInDays($invoice->due_date),
            ]);
    }

    public function applyPayment(Invoice $invoice, float $amount): Invoice
    {
        $amount = max(0, $amount);
        $due = max(0, $invoice->grand_total - $invoice->amount_paid);
        $applied = min($amount, $due);

        if ($applied <= 0) {
            return $invoice;
        }

        $amountPaid = round($invoice->amount_paid + $applied, 2);
        $amountDue = round(max(0, $invoice->grand_total - $amountPaid), 2);
        $paymentStatus = $this->resolvePaymentStatus($amountPaid, $invoice->grand_total);
        $status = $paymentStatus === 'paid' ? 'paid' : ($invoice->status === 'draft' ? 'sent' : $invoice->status);

        $invoice->update([
            'amount_paid' => $amountPaid,
            'amount_due' => $amountDue,
            'payment_status' => $paymentStatus,
            'status' => $status,
        ]);

        return $invoice->refresh();
    }

    protected function resolvePaymentStatus(float $amountPaid, float $grandTotal): string
    {
        if ($grandTotal <= 0) {
            return $amountPaid > 0 ? 'paid' : 'unpaid';
        }

        if ($amountPaid <= 0) {
            return 'unpaid';
        }

        if ($amountPaid < $grandTotal) {
            return 'partial';
        }

        return 'paid';
    }
}
