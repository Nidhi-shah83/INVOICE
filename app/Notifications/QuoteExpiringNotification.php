<?php

namespace App\Notifications;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class QuoteExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(public Quote $quote)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $daysUntilExpiry = now()->diffInDays($this->quote->validity_date, false);

        return [
            'type' => 'quote_expiring',
            'title' => 'Quote Expiring Soon',
            'message' => sprintf('Quote %s expires in %d days.', $this->quote->quote_number, max($daysUntilExpiry, 0)),
            'quote_number' => $this->quote->quote_number,
            'client_name' => $this->quote->client->name ?? 'Unknown client',
            'expires_in_days' => max($daysUntilExpiry, 0),
            'action_label' => 'View Quote',
            'action_url' => route('quotes.show', $this->quote),
            'icon' => 'clock',
            'severity' => 'blue',
        ];
    }
}
