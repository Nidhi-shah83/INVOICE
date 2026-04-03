<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function webhook(Request $request, RazorpayService $razorpayService, InvoiceService $invoiceService): JsonResponse
    {
        if (! $razorpayService->verifyWebhookSignature($request)) {
            return response()->json([
                'status' => 'invalid signature',
            ], 401);
        }

        $payload = $request->json()->all();

        if (($payload['event'] ?? null) !== 'payment.captured') {
            return response()->json([
                'status' => 'ignored',
            ]);
        }

        $paymentId = (string) data_get($payload, 'payload.payment.entity.id', '');
        $orderId = (string) data_get(
            $payload,
            'payload.payment.entity.order_id',
            data_get($payload, 'payload.payment_link.entity.id', '')
        );
        $amountPaise = (int) data_get($payload, 'payload.payment.entity.amount', 0);

        if ($paymentId === '' || $orderId === '' || $amountPaise <= 0) {
            return response()->json([
                'status' => 'ignored',
            ]);
        }

        if (Payment::where('razorpay_payment_id', $paymentId)->exists()) {
            return response()->json([
                'status' => 'already processed',
            ]);
        }

        $invoice = Invoice::where('razorpay_order_id', $orderId)->first();

        if (! $invoice) {
            return response()->json([
                'status' => 'ignored',
            ]);
        }

        $amount = round($amountPaise / 100, 2);

        $invoiceService->markInvoicePaid($invoice, $amount, $paymentId, $orderId);

        return response()->json([
            'status' => 'success',
        ]);
    }
}
