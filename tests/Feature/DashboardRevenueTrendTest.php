<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRevenueTrendTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_revenue_trend_groups_invoice_totals_by_month_and_fills_gaps_with_zero(): void
    {
        $user = User::factory()->create();
        $client = Client::withoutGlobalScopes()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
            'email' => 'billing@acme.test',
            'phone' => '9999999999',
            'state' => 'Karnataka',
            'address' => 'Bangalore',
        ]);

        $this->createInvoice($user->id, $client->id, 1, 1250);
        $this->createInvoice($user->id, $client->id, 3, 2750);
        $this->createInvoice($user->id, $client->id, 3, 500);

        $this->actingAs($user);

        $trend = app(DashboardService::class)->overview()['revenue_trend'];

        $this->assertCount(12, $trend['labels']);
        $this->assertCount(12, $trend['values']);
        $this->assertSame(1250.0, $trend['values'][0]);
        $this->assertSame(0.0, $trend['values'][1]);
        $this->assertSame(3250.0, $trend['values'][2]);
    }

    private function createInvoice(int $userId, int $clientId, int $month, float $grandTotal): Invoice
    {
        $timestamp = Carbon::createFromDate(now()->year, $month, 15)->setTime(12, 0);

        return Invoice::withoutGlobalScopes()->forceCreate([
            'user_id' => $userId,
            'client_id' => $clientId,
            'invoice_number' => 'INV-'.$month.'-'.uniqid(),
            'issue_date' => $timestamp->toDateString(),
            'due_date' => $timestamp->copy()->addDays(15)->toDateString(),
            'status' => 'sent',
            'subtotal' => $grandTotal,
            'cgst' => 0,
            'sgst' => 0,
            'igst' => 0,
            'total' => $grandTotal,
            'round_off' => 0,
            'grand_total' => $grandTotal,
            'amount_paid' => 0,
            'amount_due' => $grandTotal,
            'payment_status' => 'unpaid',
            'currency' => 'INR',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
