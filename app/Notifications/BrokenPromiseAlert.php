<?php

namespace App\Notifications;

use App\Models\InvoiceCallLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BrokenPromiseAlert extends Notification
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
            'type' => 'broken_promise',
            'title' => 'Broken Payment Promise',
            'message' => sprintf('Invoice %s still unpaid after a missed promise from %s.', $invoice->invoice_number, $invoice->client->name ?? 'Unknown client'),
            'invoice_number' => $invoice->invoice_number,
            'client_name' => $invoice->client->name ?? 'Unknown client',
            'promised_payment_date' => $this->callLog->promised_payment_date?->toDateString(),
            'action_label' => 'Call Again',
            'action_url' => route('ai-assistant.chat', ['invoice_number' => $invoice->invoice_number]),
            'icon' => 'alert',
            'severity' => 'darkred',
            'call_log_id' => $this->callLog->id,
        ];
    }
}
