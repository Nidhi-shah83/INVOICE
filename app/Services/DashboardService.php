<?php

namespace App\Services;

class DashboardService
{
    public function overview(): array
    {
        $invoice = config('invoice');

        return [
            'summary' => [
                [
                    'label' => 'Active clients',
                    'value' => '0',
                    'detail' => 'Sync your CRM or import contacts to see live counts.',
                ],
                [
                    'label' => 'Quotes in progress',
                    'value' => '0',
                    'detail' => 'Track any drafts that need approval.',
                ],
                [
                    'label' => 'Pending orders',
                    'value' => '0',
                    'detail' => 'Link your product catalog when ready.',
                ],
                [
                    'label' => 'Draft invoices',
                    'value' => '0',
                    'detail' => 'Fill in billable items to see totals.',
                ],
            ],
            'business' => [
                'name' => $invoice['business_name'] ?? 'Your Business Name',
                'prefixes' => [
                    'invoice' => $invoice['invoice_prefix'] ?? 'INV',
                    'quote' => $invoice['quote_prefix'] ?? 'QUO',
                    'order' => $invoice['order_prefix'] ?? 'ORD',
                ],
                'defaults' => [
                    'due_days' => $invoice['default_due_days'] ?? 15,
                ],
            ],
        ];
    }
}
