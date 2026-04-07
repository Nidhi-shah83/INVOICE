<?php

namespace App\Services;

use App\Mail\InvoiceSentMail;
use App\Mail\OrderConfirmedMail;
use App\Mail\PaymentConfirmedMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Quote;
use App\Services\RazorpayService;
use App\Services\SettingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\UniqueConstraintViolationException;
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

    public function __construct(
        protected RazorpayService $razorpayService,
        protected SettingService $settings,
    ) {
    }

    public function generateInvoiceNumber(int $userId): string
    {
        $year = now()->format('Y');
        $prefix = (string) $this->settings->get('invoice_prefix', Config::get('invoice.invoice_prefix', 'INV'));
        $prefix = trim($prefix);
        $prefix = rtrim($prefix, '-');
        $fullPrefix = sprintf('%s-%s-', $prefix, $year);

        $sequence = $this->nextInvoiceSequence($fullPrefix);

        return sprintf('%s%03d', $fullPrefix, $sequence);
    }

    private function nextInvoiceSequence(string $fullPrefix): int
    {
        $maxAttempts = 5;
        $attempts = 0;
        $indexName = 'invoice_number_sequences_prefix_unique';

        while (true) {
            try {
                return DB::transaction(function () use ($fullPrefix) {
                    $sequenceRow = DB::table('invoice_number_sequences')
                        ->where('prefix', $fullPrefix)
                        ->lockForUpdate()
                        ->first();

                    if ($sequenceRow) {
                        $next = (int) $sequenceRow->sequence + 1;

                        DB::table('invoice_number_sequences')
                            ->where('id', $sequenceRow->id)
                            ->update([
                                'sequence' => $next,
                                'updated_at' => now(),
                            ]);

                        return $next;
                    }

                    DB::table('invoice_number_sequences')->insert([
                        'prefix' => $fullPrefix,
                        'sequence' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return 1;
                });
            } catch (UniqueConstraintViolationException $exception) {
                if (! str_contains($exception->getMessage(), $indexName) || ++$attempts >= $maxAttempts) {
                    throw $exception;
                }
            }
        }
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
        $amountPaid = round(max(0, $amountPaid), 2);

        if ($grandTotal > 0) {
            $amountPaid = min($amountPaid, $grandTotal);
        }

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
                'status' => 'pending',
                'acceptance_token' => (string) Str::uuid(),
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

            apply_user_mail_config((int) $quote->user_id);
            Mail::to($quote->client->email)
                ->send(new OrderConfirmedMail($order->load(['items', 'client'])));

            return $order;
        });
    }

    public function createPartialInvoice(Order $order, array $items, ?string $notes = null, ?string $dueDate = null, ?string $issueDate = null): Invoice
    {
        return DB::transaction(function () use ($order, $items, $notes, $dueDate, $issueDate) {
            $dueDays = (int) $this->settings->get('default_due_days', Config::get('invoice.default_due_days', 15));
            $invoiceAttributes = [
                'user_id' => $order->user_id,
                'client_id' => $order->client_id,
                'order_id' => $order->id,
                'invoice_number' => $this->generateInvoiceNumber($order->user_id),
                'issue_date' => $issueDate ?: now()->toDateString(),
                'due_date' => $dueDate ?: now()->addDays($dueDays)->toDateString(),
                'status' => 'draft',
                'notes' => $notes,
            ];

            $invoice = Invoice::create($invoiceAttributes);

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
                'currency' => $this->settings->get('currency', 'INR'),
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

        // ===== Razorpay Integration (Production Only) =====
        // This code is disabled for demo without KYC
        // Uncomment when using real payments
        // $currency = $invoice->currency ?? config('invoice.currency', 'INR');
        // $razorpayOrder = $this->razorpayService->createOrder($grandTotal, $currency, $invoice->invoice_number);
        // $orderId = (string) ($razorpayOrder['id'] ?? '');
        // $paymentLink = $this->razorpayService->getCheckoutUrl($orderId);
        $orderId = null;
        $paymentLink = route('invoices.pay', $invoice->id);

        $status = $this->resolveInvoiceStatus((string) $invoice->status, (string) $invoice->payment_status);
        if ($status !== 'paid' && $status !== 'cancelled') {
            $status = 'sent';
        }

        $invoice->update([
            'status' => $status,
            'razorpay_order_id' => $orderId,
            'payment_link' => $paymentLink,
            'pdf_path' => $path,
        ]);

        $invoice = $this->syncInvoicePaymentState($invoice);

        apply_user_mail_config((int) $invoice->user_id);
        $fromName = setting_for_user((int) $invoice->user_id, 'mail_from_name', 'Invoice App');
        $fromAddress = setting_for_user((int) $invoice->user_id, 'mail_from_address', 'no-reply@example.com');
        $emailSignature = setting_for_user((int) $invoice->user_id, 'email_signature', '');

        $mail = (new InvoiceSentMail($invoice, $paymentLink, $pdf))
            ->from($fromAddress, $fromName)
            ->with([
                'emailSignature' => $emailSignature,
                'businessName' => setting_for_user((int) $invoice->user_id, 'business_name', 'Invoice Pro'),
            ]);

        Mail::to($invoice->client->email)->send($mail);

        return [
            'path' => $path,
            'order_id' => $orderId,
            'link' => $paymentLink,
        ];
    }

    public function markInvoicePaid(Invoice $invoice, float $amount, string $paymentId, string $orderId): Payment
    {
        return DB::transaction(function () use ($invoice, $amount, $paymentId, $orderId): Payment {
            $lockedInvoice = Invoice::query()
                ->with('client')
                ->lockForUpdate()
                ->findOrFail($invoice->id);

            $existingPayment = Payment::where('razorpay_payment_id', $paymentId)->first();
            if ($existingPayment) {
                return $existingPayment;
            }

            $grandTotal = $this->resolveInvoiceGrandTotal($lockedInvoice);
            $due = round(max(0, $grandTotal - (float) $lockedInvoice->amount_paid), 2);
            $captureAmount = min(round(max(0, $amount), 2), $due);

            if ($captureAmount <= 0) {
                throw new \InvalidArgumentException('No outstanding amount remains for this invoice.');
            }

            $payment = Payment::create([
                'invoice_id' => $lockedInvoice->id,
                'razorpay_payment_id' => $paymentId,
                'razorpay_order_id' => $orderId,
                'amount' => $captureAmount,
                'status' => 'captured',
            ]);

            $lockedInvoice = $this->recalculatePaymentStateFromLedger($lockedInvoice);

            if (! empty($lockedInvoice->client?->email)) {
                apply_user_mail_config((int) $lockedInvoice->user_id);
                $fromName = setting_for_user((int) $lockedInvoice->user_id, 'mail_from_name', 'Invoice App');
                $fromAddress = setting_for_user((int) $lockedInvoice->user_id, 'mail_from_address', 'no-reply@example.com');
                $emailSignature = setting_for_user((int) $lockedInvoice->user_id, 'email_signature', '');

                $mail = (new PaymentConfirmedMail($lockedInvoice, $payment))
                    ->from($fromAddress, $fromName)
                    ->with([
                        'emailSignature' => $emailSignature,
                        'businessName' => setting_for_user((int) $lockedInvoice->user_id, 'business_name', 'Invoice Pro'),
                    ]);

                Mail::to($lockedInvoice->client->email)->send($mail);
            }

            return $payment;
        });
    }

    public function markInvoiceAsPaid(Invoice $invoice, ?string $paymentId = null, ?string $orderId = null): Payment
    {
        $invoice = $this->syncInvoicePaymentState($invoice);
        $due = round(max(0, (float) $invoice->amount_due), 2);

        if ($due <= 0) {
            throw new \InvalidArgumentException('This invoice is already fully paid.');
        }

        $paymentId = $paymentId ?: 'manual_'.$invoice->id.'_'.Str::uuid()->toString();
        $orderId = $orderId ?: (string) ($invoice->razorpay_order_id ?: $invoice->invoice_number);

        return $this->markInvoicePaid($invoice, $due, $paymentId, $orderId);
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
        $grandTotal = $this->resolveInvoiceGrandTotal($invoice);
        $due = round(max(0, $grandTotal - (float) $invoice->amount_paid), 2);
        $applied = min($amount, $due);

        if ($applied <= 0) {
            return $invoice;
        }

        $amountPaid = round((float) $invoice->amount_paid + $applied, 2);

        if ($grandTotal > 0) {
            $amountPaid = min($amountPaid, $grandTotal);
        }

        $amountDue = round(max(0, $grandTotal - $amountPaid), 2);
        $paymentStatus = $this->resolvePaymentStatus($amountPaid, $grandTotal);
        $status = $this->resolveInvoiceStatus($invoice->status, $paymentStatus);

        $invoice->update([
            'grand_total' => $grandTotal,
            'amount_paid' => $amountPaid,
            'amount_due' => $amountDue,
            'payment_status' => $paymentStatus,
            'status' => $status,
        ]);

        return $invoice->refresh();
    }

    public function syncInvoicePaymentState(Invoice $invoice): Invoice
    {
        $grandTotal = $this->resolveInvoiceGrandTotal($invoice);
        $amountPaid = round(max(0, (float) $invoice->amount_paid), 2);

        if ($grandTotal > 0) {
            $amountPaid = min($amountPaid, $grandTotal);
        }

        $amountDue = round(max(0, $grandTotal - $amountPaid), 2);
        $paymentStatus = $this->resolvePaymentStatus($amountPaid, $grandTotal);
        $status = $this->resolveInvoiceStatus((string) $invoice->status, $paymentStatus);

        $invoice->update([
            'grand_total' => $grandTotal,
            'amount_paid' => $amountPaid,
            'amount_due' => $amountDue,
            'payment_status' => $paymentStatus,
            'status' => $status,
        ]);

        return $invoice->refresh();
    }

    public function paymentSummary(Invoice $invoice): array
    {
        $invoice = $this->syncInvoicePaymentState($invoice);

        return [
            'id' => $invoice->id,
            'status' => $invoice->status,
            'payment_status' => $invoice->payment_status,
            'amount_paid' => (float) $invoice->amount_paid,
            'amount_due' => (float) $invoice->amount_due,
            'grand_total' => (float) $invoice->grand_total,
        ];
    }

    protected function recalculatePaymentStateFromLedger(Invoice $invoice): Invoice
    {
        $paidTotal = round((float) $invoice->payments()
            ->where('status', 'captured')
            ->sum('amount'), 2);

        $invoice->amount_paid = $paidTotal;

        return $this->syncInvoicePaymentState($invoice);
    }

    protected function resolveInvoiceGrandTotal(Invoice $invoice): float
    {
        $grandTotal = round((float) ($invoice->grand_total ?? 0), 2);
        if ($grandTotal > 0) {
            return $grandTotal;
        }

        return round(max(0, (float) ($invoice->total ?? 0)), 2);
    }

    protected function resolveInvoiceStatus(string $currentStatus, string $paymentStatus): string
    {
        if ($currentStatus === 'cancelled') {
            return $currentStatus;
        }

        if ($paymentStatus === 'paid') {
            return 'paid';
        }

        if ($currentStatus === 'draft') {
            return 'draft';
        }

        if ($currentStatus === 'paid') {
            return 'sent';
        }

        return $currentStatus !== '' ? $currentStatus : 'sent';
    }

    protected function resolvePaymentStatus(float $amountPaid, float $grandTotal): string
    {
        $amountPaid = round(max(0, $amountPaid), 2);
        $grandTotal = round(max(0, $grandTotal), 2);

        if ($amountPaid === 0.0) {
            return 'unpaid';
        }

        if ($amountPaid < $grandTotal) {
            return 'partial';
        }

        return 'paid';
    }
}
