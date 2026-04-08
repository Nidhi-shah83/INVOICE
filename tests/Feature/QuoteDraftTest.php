<?php

namespace Tests\Feature;

use App\Http\Livewire\QuoteForm;
use App\Models\Client;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuoteDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_as_draft_persists_draft_status_without_full_validation(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);

        $this->actingAs($user);

        Livewire::test(QuoteForm::class)
            ->set('client_id', $client->id)
            ->call('saveDraft')
            ->assertHasNoErrors();

        $quote = Quote::withoutGlobalScopes()->latest('id')->first();

        $this->assertNotNull($quote);
        $this->assertSame($user->id, $quote->user_id);
        $this->assertSame($client->id, $quote->client_id);
        $this->assertSame('draft', $quote->status);
        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'status' => 'draft',
        ]);
    }

    public function test_send_now_persists_sent_status(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);

        $this->actingAs($user);

        Livewire::test(QuoteForm::class)
            ->set('client_id', $client->id)
            ->set('items', [
                [
                    'name' => 'Consulting',
                    'qty' => 1,
                    'rate' => 1500,
                    'gst_percent' => 18,
                ],
            ])
            ->call('sendNow');

        $quote = Quote::withoutGlobalScopes()->latest('id')->first();

        $this->assertNotNull($quote);
        $this->assertSame('sent', $quote->status);
        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'status' => 'sent',
        ]);
    }

    private function createClient(User $user): Client
    {
        return Client::withoutGlobalScopes()->create([
            'user_id' => $user->id,
            'name' => 'Test Client',
            'email' => 'client@example.test',
            'phone' => '9999999999',
            'state' => 'Karnataka',
            'address' => 'Bangalore',
        ]);
    }
}
