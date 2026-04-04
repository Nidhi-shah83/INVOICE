<?php

namespace App\Notifications;

use App\Models\InvoiceCallLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentPromiseReminder extends Notification
{
    use Queueable;

    public function __construct(public InvoiceCallLog $callLog)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $invoice = $this->callLog->invoice;

        return [
            'type' => 'payment_promise',
            'title' => 'Payment Promise Today',
            'message' => sprintf('Client %s promised payment for invoice %s today.', $invoice->client->name ?? 'Unknown client', $invoice->invoice_number),
            'invoice_number' => $invoice->invoice_number,
            'client_name' => $invoice->client->name ?? 'Unknown client',
            'promised_payment_date' => $this->callLog->promised_payment_date?->toDateString(),
            'action_label' => 'View Invoice',
            'action_url' => route('invoices.show', $invoice),
            'icon' => 'calendar',
            'severity' => 'green',
            'call_log_id' => $this->callLog->id,
        ];
    }
}
