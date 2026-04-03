<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('invoices')
            ->orderBy('id')
            ->chunkById(200, function ($invoices): void {
                foreach ($invoices as $invoice) {
                    $grandTotal = round((float) ($invoice->grand_total ?? 0), 2);

                    if ($grandTotal <= 0) {
                        $grandTotal = round(max(0, (float) ($invoice->total ?? 0)), 2);
                    }

                    $amountPaid = round(max(0, (float) ($invoice->amount_paid ?? 0)), 2);
                    if ($grandTotal > 0) {
                        $amountPaid = min($amountPaid, $grandTotal);
                    }

                    $amountDue = round(max(0, $grandTotal - $amountPaid), 2);

                    if ($amountPaid === 0.0) {
                        $paymentStatus = 'unpaid';
                    } elseif ($amountPaid < $grandTotal) {
                        $paymentStatus = 'partial';
                    } else {
                        $paymentStatus = 'paid';
                    }

                    $status = (string) ($invoice->status ?? 'sent');
                    if ($status !== 'cancelled') {
                        if ($paymentStatus === 'paid') {
                            $status = 'paid';
                        } elseif ($status === 'paid') {
                            $status = 'sent';
                        }
                    }

                    DB::table('invoices')
                        ->where('id', $invoice->id)
                        ->update([
                            'grand_total' => $grandTotal,
                            'amount_paid' => $amountPaid,
                            'amount_due' => $amountDue,
                            'payment_status' => $paymentStatus,
                            'status' => $status,
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Data normalization migration; no rollback.
    }
};
