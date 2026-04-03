<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvoicePaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_invoice_payment_status_for_partial_and_full_payments(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme Pvt Ltd',
            'email' => 'billing@acme.test',
            'phone' => '9999999999',
            'gstin' => null,
            'state' => 'Karnataka',
            'address' => 'Bangalore',
        ]);

        $invoice = Invoice::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-TEST-001',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'status' => 'sent',
            'subtotal' => 1000,
            'total' => 1000,
            'round_off' => 0,
            'grand_total' => 1000,
            'amount_paid' => 0,
            'amount_due' => 1000,
            'payment_status' => 'unpaid',
            'currency' => 'INR',
        ]);

        $service = app(InvoiceService::class);

        $service->markInvoicePaid($invoice, 250, 'pay_test_001', 'order_test_001');

        $invoice->refresh();

        $this->assertSame('partial', $invoice->payment_status);
        $this->assertSame('sent', $invoice->status);
        $this->assertSame('250.00', (string) $invoice->amount_paid);
        $this->assertSame('750.00', (string) $invoice->amount_due);

        $service->markInvoicePaid($invoice, 750, 'pay_test_002', 'order_test_001');

        $invoice->refresh();

        $this->assertSame('paid', $invoice->payment_status);
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('1000.00', (string) $invoice->amount_paid);
        $this->assertSame('0.00', (string) $invoice->amount_due);
    }
}
