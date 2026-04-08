<?php

namespace App\Services;

use App\Models\Order;
use App\Services\Concerns\GeneratesDocumentNumbers;

class OrderService extends ModuleService
{
    use GeneratesDocumentNumbers;

    public function moduleName(): string
    {
        return 'Orders';
    }

    public function generateOrderNumber(int $userId): string
    {
        return $this->generateDocumentNumber(
            Order::class,
            'order_number',
            $userId,
            'order_prefix',
            'ORD',
        );
    }
}
