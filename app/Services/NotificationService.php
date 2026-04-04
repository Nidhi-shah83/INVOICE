<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceCallLog;
use App\Models\Quote;
use App\Models\User;
use App\Notifications\BrokenPromiseAlert;
use App\Notifications\InvoiceOverdueNotification;
use App\Notifications\PaymentPromiseReminder;
use App\Notifications\QuoteExpiringNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function exists(User $user, string $notificationType, array $attributes = []): bool
    {
        $query = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('type', $notificationType);

        foreach ($attributes as $key => $value) {
            $query->where('data->' . $key, $value);
        }

        return $query->exists();
    }

    public function send(User $user, Notification $notification, array $distinctAttributes = []): void
    {
        if (! $this->exists($user, get_class($notification), $distinctAttributes)) {
            $user->notify($notification);
        }
    }

    public function sendInvoiceOverdue(Invoice $invoice): void
    {
        $this->send(
            $invoice->user,
            new InvoiceOverdueNotification($invoice),
            ['invoice_number' => $invoice->invoice_number],
        );
    }

    public function sendQuoteExpiring(Quote $quote): void
    {
        $this->send(
            $quote->user,
            new QuoteExpiringNotification($quote),
            ['quote_number' => $quote->quote_number],
        );
    }

    public function sendPaymentPromiseReminder(InvoiceCallLog $callLog): void
    {
        $this->send(
            $callLog->invoice->user,
            new PaymentPromiseReminder($callLog),
            ['call_log_id' => $callLog->id],
        );
    }

    public function sendBrokenPromiseAlert(InvoiceCallLog $callLog): void
    {
        $this->send(
            $callLog->invoice->user,
            new BrokenPromiseAlert($callLog),
            ['call_log_id' => $callLog->id],
        );
    }
}
