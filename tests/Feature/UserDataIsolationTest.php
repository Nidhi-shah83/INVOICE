<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDataIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_open_another_users_invoice(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $invoiceA = $this->createInvoiceForUser($userA, 'INV-A-001');
        $invoiceB = $this->createInvoiceForUser($userB, 'INV-B-001');

        $this->actingAs($userA)
            ->get(route('invoices.show', $invoiceA))
            ->assertOk();

        $this->actingAs($userA)
            ->get(route('invoices.show', $invoiceB))
            ->assertNotFound();
    }

    public function test_invoice_index_shows_only_logged_in_users_data(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->createInvoiceForUser($userA, 'INV-A-002');
        $this->createInvoiceForUser($userB, 'INV-B-002');

        $this->actingAs($userA)
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertSee('INV-A-002')
            ->assertDontSee('INV-B-002');
    }

    public function test_setting_model_is_isolated_by_user_scope(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Setting::withoutGlobalScopes()->create([
            'user_id' => $userA->id,
            'key' => 'business_name',
            'value' => 'Alpha Co',
        ]);

        Setting::withoutGlobalScopes()->create([
            'user_id' => $userB->id,
            'key' => 'business_name',
            'value' => 'Beta Co',
        ]);

        $this->actingAs($userA);

        $this->assertSame(
            'Alpha Co',
            Setting::query()->where('key', 'business_name')->value('value')
        );
    }

    private function createInvoiceForUser(User $user, string $invoiceNumber): Invoice
    {
        $client = Client::withoutGlobalScopes()->create([
            'user_id' => $user->id,
            'name' => 'Client '.$invoiceNumber,
            'email' => strtolower($invoiceNumber).'@example.test',
            'phone' => '9999999999',
            'state' => 'Karnataka',
            'place_of_supply' => 'Karnataka',
            'address' => 'Bangalore',
            'city' => 'Bangalore',
            'pincode' => '560001',
            'country' => 'India',
            'client_type' => 'individual',
        ]);

        return Invoice::withoutGlobalScopes()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'status' => 'sent',
            'subtotal' => 1000,
            'cgst' => 0,
            'sgst' => 0,
            'igst' => 0,
            'total' => 1000,
            'round_off' => 0,
            'grand_total' => 1000,
            'amount_paid' => 0,
            'amount_due' => 1000,
            'payment_status' => 'unpaid',
            'currency' => 'INR',
        ]);
    }
}
