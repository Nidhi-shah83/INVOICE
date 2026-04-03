<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Http\Request;
use RuntimeException;

class RazorpayService
{
    protected mixed $client = null;

    public function createPaymentLink(Invoice $invoice): array
    {
        $invoice->loadMissing('client');

        $amount = (float) ($invoice->total_amount ?? $invoice->total ?? 0);
        $payload = [
            'amount' => (int) round($amount * 100),
            'currency' => 'INR',
            'description' => "Invoice #{$invoice->invoice_number}",
            'notify' => [
                'email' => true,
            ],
            'callback_url' => route('payment.success'),
            'callback_method' => 'get',
        ];

        if (! empty($invoice->client?->name) || ! empty($invoice->client?->email)) {
            $payload['customer'] = array_filter([
                'name' => (string) ($invoice->client?->name ?? ''),
                'email' => (string) ($invoice->client?->email ?? ''),
            ], fn ($value) => $value !== '');
        }

        $paymentLink = $this->client()->paymentLink->create($payload);

        return method_exists($paymentLink, 'toArray') ? $paymentLink->toArray() : (array) $paymentLink;
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $signature = (string) $request->header('X-Razorpay-Signature', '');
        $secret = (string) config('razorpay.webhook_secret', '');
        $payload = (string) $request->getContent();

        if ($signature === '' || $secret === '' || $payload === '') {
            return false;
        }

        $computedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($computedSignature, $signature);
    }

    public function getCheckoutUrl(string $orderId): string
    {
        return "https://rzp.io/i/{$orderId}";
    }

    protected function client(): object
    {
        if ($this->client !== null) {
            return $this->client;
        }

        $apiClass = \Razorpay\Api\Api::class;

        if (! class_exists($apiClass)) {
            throw new RuntimeException('Razorpay SDK is not installed. Run: composer install (or composer require razorpay/razorpay).');
        }

        $keyId = (string) config('razorpay.key');
        $secret = (string) config('razorpay.secret');

        if ($keyId === '' || $secret === '') {
            throw new RuntimeException('Razorpay credentials are missing. Set RAZORPAY_KEY and RAZORPAY_SECRET in .env.');
        }

        $this->client = new $apiClass($keyId, $secret);

        return $this->client;
    }
}
