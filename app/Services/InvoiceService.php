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

    public function convertQuoteToOrder(Quote $quote): Order
    {
        if ($quote->status !== 'accepted') {
            throw new \LogicException('Only accepted quotes can be converted.');
        }

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
                'subtotal' => 0,
                'cgst' => 0,
                'sgst' => 0,
                'igst' => 0,
                'total' => 0,
                'notes' => $notes,
            ]);

            $subtotal = $cgst = $sgst = $igst = 0;

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
                $gst = $this->calculateGST($order->client, $amount, $orderItem->gst_percent);

                $subtotal += $amount;
                $cgst += $gst['cgst'];
                $sgst += $gst['sgst'];
                $igst += $gst['igst'];

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

            $total = $subtotal + $cgst + $sgst + $igst;
            $invoice->update([
                'subtotal' => $subtotal,
                'cgst' => $cgst,
                'sgst' => $sgst,
                'igst' => $igst,
                'total' => $total,
            ]);

            $order->billed_amount += $total;
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

        $paymentLinkResponse = $this->razorpayService->createPaymentLink($invoice);
        $orderId = (string) ($paymentLinkResponse['id'] ?? '');
        $paymentLink = (string) ($paymentLinkResponse['short_url'] ?? '');

        $invoice->update([
            'status' => 'sent',
            'razorpay_order_id' => $orderId,
            'payment_link' => $paymentLink,
            'pdf_path' => $path,
        ]);

        Mail::to($invoice->client->email)
            ->send(new InvoiceSentMail($invoice, $paymentLink, $pdf));

        return [
            'path' => $path,
            'order_id' => $orderId,
            'link' => $paymentLink,
        ];
    }

    public function markInvoicePaid(Invoice $invoice, float $amount, string $paymentId, string $orderId): Payment
    {
        $invoice->update(['status' => 'paid']);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => $orderId,
            'amount' => $amount,
            'status' => 'captured',
        ]);

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
                'amount' => (float) $invoice->total,
                'due_date' => $invoice->due_date?->toDateString(),
                'days_overdue' => now()->diffInDays($invoice->due_date),
            ]);
    }
}
