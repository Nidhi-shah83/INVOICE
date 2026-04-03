<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function webhook(Request $request, RazorpayService $razorpayService, InvoiceService $invoiceService): JsonResponse
    {
        // ===== Razorpay Integration (Production Only) =====
        // This code is disabled for demo without KYC
        // Uncomment when using real payments
        if (! $razorpayService->verifyWebhookSignature($request)) {
            return response()->json([
                'status' => 'invalid signature',
            ], 401);
        }

        $payload = $request->json()->all();
        $event = (string) ($payload['event'] ?? '');

        if (! in_array($event, ['payment.captured', 'order.paid'], true)) {
            return response()->json([
                'status' => 'ignored',
            ]);
        }

        $paymentId = (string) data_get($payload, 'payload.payment.entity.id', '');
        $orderId = (string) data_get(
            $payload,
            'payload.payment.entity.order_id',
            data_get($payload, 'payload.payment_link.entity.order_id', '')
        );
        $amountPaise = (int) data_get($payload, 'payload.payment.entity.amount', 0);

        if ($paymentId === '' || $amountPaise <= 0) {
            return response()->json([
                'status' => 'ignored',
            ]);
        }

        $invoice = $this->resolveInvoiceFromWebhook($payload, $orderId);

        if (! $invoice) {
            return response()->json([
                'status' => 'ignored',
            ]);
        }

        $amount = round($amountPaise / 100, 2);

        try {
            $invoiceService->markInvoicePaid(
                $invoice,
                $amount,
                $paymentId,
                $orderId !== '' ? $orderId : (string) ($invoice->razorpay_order_id ?: $invoice->invoice_number),
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'status' => 'already paid',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'invoice' => $invoiceService->paymentSummary($invoice),
        ]);
    }

    protected function resolveInvoiceFromWebhook(array $payload, string $orderId): ?Invoice
    {
        if ($orderId !== '') {
            $invoice = Invoice::where('razorpay_order_id', $orderId)->first();
            if ($invoice) {
                return $invoice;
            }
        }

        $invoiceId = (int) data_get($payload, 'payload.payment.entity.notes.invoice_id');
        if ($invoiceId > 0) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                return $invoice;
            }
        }

        $invoiceNumberCandidates = array_filter([
            data_get($payload, 'payload.payment.entity.notes.invoice_number'),
            data_get($payload, 'payload.payment_link.entity.reference_id'),
            data_get($payload, 'payload.payment_link.entity.notes.invoice_number'),
        ], fn ($value) => is_string($value) && trim($value) !== '');

        foreach ($invoiceNumberCandidates as $invoiceNumber) {
            $invoice = Invoice::where('invoice_number', trim((string) $invoiceNumber))->first();
            if ($invoice) {
                return $invoice;
            }
        }

        return null;
    }
}
