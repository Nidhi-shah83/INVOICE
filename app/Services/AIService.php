<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AIService
{
    public function parseInvoiceText(string $text): array
    {
        $webhook = (string) config('services.n8n.webhook_parse');

        if (blank($webhook)) {
            throw new RuntimeException('N8N parse webhook is not configured.');
        }

        $response = Http::asJson()
            ->acceptJson()
            ->timeout(30)
            ->withHeaders($this->secretHeader())
            ->post($webhook, [
                'text' => $text,
            ]);

        $response->throw();

        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }

    public function tagExpense(int $id, string $desc): void
    {
        $webhook = (string) config('services.n8n.webhook_expense');

        if (blank($webhook)) {
            Log::warning('N8N expense webhook is not configured.');

            return;
        }

        try {
            Http::async()
                ->asJson()
                ->acceptJson()
                ->timeout(5)
                ->withHeaders($this->secretHeader())
                ->post($webhook, [
                    'expense_id' => $id,
                    'description' => $desc,
                ]);
        } catch (Throwable $exception) {
            Log::warning('Failed to dispatch async expense tagging request.', [
                'expense_id' => $id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function secretHeader(): array
    {
        $secret = (string) config('services.n8n.secret');

        if ($secret === '') {
            return [];
        }

        return [
            'X-N8N-Secret' => $secret,
        ];
    }
}
