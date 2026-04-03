<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AIControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_screen_can_be_rendered_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('ai-assistant.chat'));

        $response->assertOk();
        $response->assertSee('Paste invoice text and prefill a draft invoice');
    }

    public function test_parse_redirects_to_invoice_create_and_stores_invoice_draft_in_session(): void
    {
        $user = User::factory()->create();

        config()->set('services.n8n.webhook_parse', 'http://localhost:5678/webhook/parse-invoice');
        config()->set('services.n8n.secret', 'top-secret');

        Http::fake([
            'http://localhost:5678/webhook/parse-invoice' => Http::response([
                'client_name' => 'Acme Enterprises',
                'total' => 1500,
            ], 200),
        ]);

        $response = $this->actingAs($user)->post(route('ai-assistant.parse'), [
            'text' => 'Vendor Acme Enterprises total 1500',
        ]);

        $response->assertRedirect(route('invoices.create', [], false));
        $response->assertSessionHas('invoice_draft', [
            'client_name' => 'Acme Enterprises',
            'total' => 1500,
        ]);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'http://localhost:5678/webhook/parse-invoice'
                && $request->hasHeader('X-N8N-Secret', 'top-secret')
                && $request['text'] === 'Vendor Acme Enterprises total 1500';
        });
    }
}
