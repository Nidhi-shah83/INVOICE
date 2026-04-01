<?php

namespace App\Services;

use Razorpay\Api\Api;

class RazorpayService
{
    protected Api $client;

    public function __construct()
    {
        $keyId = config('razorpay.key_id');
        $secret = config('razorpay.key_secret');

        $this->client = new Api($keyId, $secret);
    }

    public function createOrder(float $amount, string $currency, string $receipt): array
    {
        $order = $this->client->order->create([
            'amount' => (int) round($amount * 100),
            'currency' => strtoupper($currency),
            'receipt' => $receipt,
            'payment_capture' => 1,
        ]);

        return method_exists($order, 'toArray') ? $order->toArray() : (array) $order;
    }

    public function getCheckoutUrl(string $orderId): string
    {
        return "https://rzp.io/i/{$orderId}";
    }
}
