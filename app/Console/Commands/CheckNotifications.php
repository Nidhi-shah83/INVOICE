<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceCallLog;
use App\Models\Quote;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CheckNotifications extends Command
{
    protected $signature = 'app:check-notifications';

    protected $description = 'Check invoices, quotes, and promises and create notification alerts.';

    public function __construct(protected NotificationService $notifications)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $today = Carbon::today();

        $userIds = $this->collectUserIds();

        foreach ($userIds as $userId) {
            $user = User::find($userId);

            if (! $user) {
                continue;
            }

            $this->processOverdueInvoices($user, $today);
            $this->processExpiringQuotes($user, $today);
            $this->processPaymentPromises($user, $today);
            $this->processBrokenPromises($user, $today);
        }

        return Command::SUCCESS;
    }

    protected function collectUserIds(): Collection
    {
        $invoiceUsers = Invoice::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $quoteUsers = Quote::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $callLogUsers = InvoiceCallLog::query()
            ->join('invoices', 'invoice_call_logs.invoice_number', '=', 'invoices.invoice_number')
            ->whereNotNull('invoices.user_id')
            ->distinct()
            ->pluck('invoices.user_id');

        return $invoiceUsers->merge($quoteUsers)->merge($callLogUsers)->unique()->values();
    }

    protected function processOverdueInvoices(User $user, Carbon $today): void
    {
        $invoices = Invoice::query()
            ->where('user_id', $user->id)
            ->where('status', 'sent')
            ->whereDate('due_date', '<', $today)
            ->where('payment_status', '!=', 'paid')
            ->get();

        foreach ($invoices as $invoice) {
            $this->notifications->sendInvoiceOverdue($invoice);
        }
    }

    protected function processExpiringQuotes(User $user, Carbon $today): void
    {
        $expiringDate = $today->copy()->addDays(3);

        $quotes = Quote::query()
            ->where('user_id', $user->id)
            ->where('status', 'sent')
            ->whereDate('validity_date', '>=', $today)
            ->whereDate('validity_date', '<=', $expiringDate)
            ->get();

        foreach ($quotes as $quote) {
            $this->notifications->sendQuoteExpiring($quote);
        }
    }

    protected function processPaymentPromises(User $user, Carbon $today): void
    {
        $callLogs = InvoiceCallLog::query()
            ->whereDate('promised_payment_date', $today)
            ->whereHas('invoice', function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('payment_status', '!=', 'paid');
            })
            ->get();

        foreach ($callLogs as $callLog) {
            if ($callLog->invoice?->payment_status !== 'paid') {
                $this->notifications->sendPaymentPromiseReminder($callLog);
            }
        }
    }

    protected function processBrokenPromises(User $user, Carbon $today): void
    {
        $callLogs = InvoiceCallLog::query()
            ->whereDate('promised_payment_date', '<', $today)
            ->whereHas('invoice', function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('payment_status', '!=', 'paid');
            })
            ->get();

        foreach ($callLogs as $callLog) {
            if ($callLog->invoice?->payment_status !== 'paid') {
                $this->notifications->sendBrokenPromiseAlert($callLog);
            }
        }
    }
}
