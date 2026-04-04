<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'invoice_overdue',
            'title' => 'Overdue Invoice',
            'message' => sprintf('Invoice %s for %s is overdue by %d days.', $this->invoice->invoice_number, $this->invoice->client->name ?? 'Unknown client', now()->diffInDays($this->invoice->due_date)),
            'invoice_number' => $this->invoice->invoice_number,
            'client_name' => $this->invoice->client->name ?? 'Unknown client',
            'amount_due' => $this->invoice->amount_due,
            'days_overdue' => now()->diffInDays($this->invoice->due_date),
            'action_label' => 'Send Reminder',
            'action_url' => route('ai-assistant.chat', ['invoice_number' => $this->invoice->invoice_number]),
            'icon' => 'warning',
            'severity' => 'red',
        ];
    }
}
