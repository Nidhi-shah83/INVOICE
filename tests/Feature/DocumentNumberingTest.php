<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quote;
use App\Models\Setting;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\OrderService;
use App\Services\QuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DocumentNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_number_generation_is_scoped_per_user_for_quote_order_and_invoice(): void
    {
        $year = now()->format('Y');
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        $this->seedSettings($userOne, [
            'company_prefix' => 'KD',
            'quote_prefix' => 'QT',
            'order_prefix' => 'ORD',
            'invoice_prefix' => 'INV',
        ]);
        $this->seedSettings($userTwo, [
            'company_prefix' => 'ABC',
            'quote_prefix' => 'QT',
            'order_prefix' => 'ORD',
            'invoice_prefix' => 'INV',
        ]);

        $quoteService = app(QuoteService::class);
        $orderService = app(OrderService::class);
        $invoiceService = app(InvoiceService::class);

        $this->assertSame("KD-QT-{$year}-001", $quoteService->generateQuoteNumber($userOne->id));
        $this->assertSame("ABC-QT-{$year}-001", $quoteService->generateQuoteNumber($userTwo->id));
        $this->assertSame("KD-ORD-{$year}-001", $orderService->generateOrderNumber($userOne->id));
        $this->assertSame("ABC-ORD-{$year}-001", $orderService->generateOrderNumber($userTwo->id));
        $this->assertSame("KD-INV-{$year}-001", $invoiceService->generateInvoiceNumber($userOne->id));
        $this->assertSame("ABC-INV-{$year}-001", $invoiceService->generateInvoiceNumber($userTwo->id));
    }

    public function test_company_prefix_falls_back_to_business_name_initials(): void
    {
        $year = now()->format('Y');
        $user = User::factory()->create();

        $this->seedSettings($user, [
            'business_name' => 'Kedar Developers',
            'company_prefix' => '',
            'quote_prefix' => 'QT',
        ]);

        $quoteService = app(QuoteService::class);

        $this->assertSame("KD-QT-{$year}-001", $quoteService->generateQuoteNumber($user->id));
    }

    public function test_same_document_number_can_exist_for_different_users(): void
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();
        $quoteNumber = 'SAME-QT-2026-001';
        $orderNumber = 'SAME-ORD-2026-001';
        $invoiceNumber = 'SAME-INV-2026-001';

        $clientOne = $this->createClient($userOne, 'one');
        $clientTwo = $this->createClient($userTwo, 'two');

        Quote::withoutGlobalScopes()->create([
            'user_id' => $userOne->id,
            'client_id' => $clientOne->id,
            'quote_number' => $quoteNumber,
            'issue_date' => now()->toDateString(),
            'validity_date' => now()->addDays(7)->toDateString(),
            'status' => 'draft',
            'subtotal' => 0,
            'cgst' => 0,
            'sgst' => 0,
            'igst' => 0,
            'total' => 0,
        ]);
        Quote::withoutGlobalScopes()->create([
            'user_id' => $userTwo->id,
            'client_id' => $clientTwo->id,
            'quote_number' => $quoteNumber,
            'issue_date' => now()->toDateString(),
            'validity_date' => now()->addDays(7)->toDateString(),
            'status' => 'draft',
            'subtotal' => 0,
            'cgst' => 0,
            'sgst' => 0,
            'igst' => 0,
            'total' => 0,
        ]);

        Order::withoutGlobalScopes()->create([
            'user_id' => $userOne->id,
            'client_id' => $clientOne->id,
            'order_number' => $orderNumber,
            'status' => 'pending',
            'total_amount' => 0,
            'billed_amount' => 0,
        ]);
        Order::withoutGlobalScopes()->create([
            'user_id' => $userTwo->id,
            'client_id' => $clientTwo->id,
            'order_number' => $orderNumber,
            'status' => 'pending',
            'total_amount' => 0,
            'billed_amount' => 0,
        ]);

        Invoice::withoutGlobalScopes()->create([
            'user_id' => $userOne->id,
            'client_id' => $clientOne->id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'status' => 'sent',
            'subtotal' => 0,
            'cgst' => 0,
            'sgst' => 0,
            'igst' => 0,
            'total' => 0,
            'round_off' => 0,
            'grand_total' => 0,
            'amount_paid' => 0,
            'amount_due' => 0,
            'payment_status' => 'unpaid',
            'currency' => 'INR',
        ]);
        Invoice::withoutGlobalScopes()->create([
            'user_id' => $userTwo->id,
            'client_id' => $clientTwo->id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'status' => 'sent',
            'subtotal' => 0,
            'cgst' => 0,
            'sgst' => 0,
            'igst' => 0,
            'total' => 0,
            'round_off' => 0,
            'grand_total' => 0,
            'amount_paid' => 0,
            'amount_due' => 0,
            'payment_status' => 'unpaid',
            'currency' => 'INR',
        ]);

        $this->assertSame(2, Quote::withoutGlobalScopes()->where('quote_number', $quoteNumber)->count());
        $this->assertSame(2, Order::withoutGlobalScopes()->where('order_number', $orderNumber)->count());
        $this->assertSame(2, Invoice::withoutGlobalScopes()->where('invoice_number', $invoiceNumber)->count());
    }

    private function createClient(User $user, string $suffix): Client
    {
        return Client::withoutGlobalScopes()->create([
            'user_id' => $user->id,
            'name' => "Client {$suffix}",
            'email' => "{$suffix}@example.test",
            'phone' => '9999999999',
            'state' => 'Karnataka',
            'address' => 'Bangalore',
        ]);
    }

    private function seedSettings(User $user, array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::withoutGlobalScopes()->updateOrCreate(
                ['user_id' => $user->id, 'key' => (string) $key],
                ['value' => $value],
            );
        }

        Cache::forget("settings_user_{$user->id}");
    }
}
