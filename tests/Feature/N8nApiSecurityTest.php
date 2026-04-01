<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class N8nApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_n8n_api_route_requires_secret_header(): void
    {
        config()->set('services.n8n.secret', 'n8n-secret');

        $response = $this->postJson('/api/n8n/expense/tag', [
            'user_id' => 1,
            'expense_id' => 5,
            'description' => 'Fuel bill',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthorized n8n request.',
        ]);
    }

    public function test_n8n_api_routes_are_rate_limited_to_ten_per_minute_per_user(): void
    {
        config()->set('services.n8n.secret', 'n8n-secret');
        config()->set('services.n8n.webhook_expense', 'https://n8n.example/webhook/expense');

        Http::fake([
            'https://n8n.example/webhook/expense' => Http::response([], 200),
        ]);

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->withHeader('X-N8N-Secret', 'n8n-secret')
                ->postJson('/api/n8n/expense/tag', [
                    'user_id' => 25,
                    'expense_id' => $attempt,
                    'description' => 'Office supplies',
                ])
                ->assertOk();
        }

        $this->withHeader('X-N8N-Secret', 'n8n-secret')
            ->postJson('/api/n8n/expense/tag', [
                'user_id' => 25,
                'expense_id' => 11,
                'description' => 'Office supplies',
            ])
            ->assertStatus(429)
            ->assertJson([
                'message' => 'Rate limit exceeded. Please retry in a minute.',
            ]);
    }
}
