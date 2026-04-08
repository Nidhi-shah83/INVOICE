<?php

namespace Tests\Feature;

use App\Http\Livewire\PartialBillingForm;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PartialBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_overbilling_is_blocked_before_invoice_creation(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderWithItem($user, orderedQty: 10, billedQty: 3, rate: 100);

        $this->actingAs($user);

        Livewire::test(PartialBillingForm::class, ['order' => $order])
            ->set('lines.0.qty', 8)
            ->call('submit')
            ->assertHasErrors('lines');

        $this->assertDatabaseCount('invoices', 0);
        $this->assertSame(3.0, (float) $order->fresh()->items()->first()->qty_billed);
    }

    public function test_bill_remaining_fills_all_available_quantity_and_creates_invoice(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderWithItem($user, orderedQty: 10, billedQty: 3, rate: 100);

        $this->actingAs($user);

        Livewire::test(PartialBillingForm::class, ['order' => $order])
            ->call('billRemaining')
            ->assertSet('lines.0.qty', 7)
            ->call('submit');

        $this->assertDatabaseCount('invoices', 1);

        $freshOrder = $order->fresh(['items']);
        $this->assertSame('fully_billed', $freshOrder->status);
        $this->assertSame(10.0, (float) $freshOrder->items->first()->qty_billed);
        $this->assertSame(1000.0, (float) $freshOrder->billed_amount);

        $invoice = Invoice::withoutGlobalScopes()->latest('id')->first();
        $this->assertNotNull($invoice);
        $this->assertSame('draft', $invoice->status);
    }

    private function createOrderWithItem(User $user, float $orderedQty, float $billedQty, float $rate): Order
    {
        $client = Client::withoutGlobalScopes()->create([
            'user_id' => $user->id,
            'name' => 'Partial Billing Client',
            'email' => 'client@example.test',
            'phone' => '9999999999',
            'state' => 'Karnataka',
            'address' => 'Bangalore',
        ]);

        $order = Order::withoutGlobalScopes()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'quote_id' => null,
            'order_number' => 'ORD-PARTIAL-001',
            'status' => $billedQty > 0 ? 'partially_billed' : 'confirmed',
            'total_amount' => $orderedQty * $rate,
            'billed_amount' => $billedQty * $rate,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'name' => 'Consulting Hours',
            'qty' => $orderedQty,
            'rate' => $rate,
            'gst_percent' => 0,
            'qty_billed' => $billedQty,
        ]);

        return $order->load(['items', 'client']);
    }
}
