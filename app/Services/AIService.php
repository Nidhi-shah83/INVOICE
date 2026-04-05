<?php

namespace App\Services;

use App\Services\SettingService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AIService
{
    public function __construct(protected SettingService $settings)
    {
    }

    public function parseInvoiceText(string $text): array
    {
        if (! $this->settings->get('enable_ai_agent', true)) {
            throw new RuntimeException('AI agent is disabled for your account.');
        }

        if (! $this->settings->get('enable_ai_calls', true)) {
            throw new RuntimeException('AI calls are disabled for your account.');
        }

        $webhook = (string) config('services.n8n.webhook_parse');

        if (blank($webhook)) {
            throw new RuntimeException('N8N parse webhook is not configured.');
        }

        $payload = [
            'text' => $text,
            'tone' => $this->settings->get('ai_call_tone', 'formal'),
            'language' => $this->settings->get('ai_language', 'English'),
            'reminder_delay' => $this->settings->get('ai_reminder_delay', 3),
            'max_follow_up_attempts' => $this->settings->get('ai_max_follow_up_attempts', 3),
        ];

        $response = Http::asJson()
            ->acceptJson()
            ->timeout(30)
            ->withHeaders($this->secretHeader())
            ->post($webhook, $payload);

        $response->throw();

        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }

    public function tagExpense(int $id, string $desc): void
    {
        if (! $this->settings->get('enable_ai_agent', true)) {
            return;
        }

        if (! $this->settings->get('enable_ai_calls', true)) {
            return;
        }

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
