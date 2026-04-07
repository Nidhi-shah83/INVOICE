<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceCallLog;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function overview(): array
    {
        $user = Auth::user();
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $invoiceMetrics = Invoice::query()
            ->where('user_id', (int) $user->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_status IN ('unpaid','partial') THEN amount_due ELSE 0 END), 0) as unpaid_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'sent' AND due_date < ? THEN amount_due ELSE 0 END), 0) as overdue_amount", [$today->toDateString()])
            ->first();

        $totalRevenue = (float) Payment::query()
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->where('invoices.user_id', (int) $user->id)
            ->where('payments.status', 'captured')
            ->whereBetween('payments.created_at', [$monthStart, $monthEnd])
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as total_revenue')
            ->value('total_revenue');

        return [
            'total_revenue' => $totalRevenue,
            'unpaid_amount' => (float) ($invoiceMetrics?->unpaid_amount ?? 0),
            'overdue_amount' => (float) ($invoiceMetrics?->overdue_amount ?? 0),
            'active_orders' => $this->getActiveOrdersCount((int) $user->id),
            'recent_invoices' => $this->getRecentInvoices((int) $user->id),
            'top_clients' => $this->getTopClients((int) $user->id),
            'overdue_invoices' => $this->getOverdueInvoices((int) $user->id, $today),
            'followup_activity' => $this->getFollowupActivity((int) $user->id),
            'business' => $this->getBusinessInfo(),
        ];
    }

    private function getActiveOrdersCount(int $userId): int
    {
        return Order::query()
            ->where('user_id', $userId)
            ->whereNotIn('status', ['fully_billed', 'cancelled'])
            ->count();
    }

    private function getRecentInvoices(int $userId): array
    {
        return Invoice::query()
            ->where('user_id', $userId)
            ->with('client:id,name,state')
            ->select('id', 'invoice_number', 'total', 'due_date', 'status', 'payment_status', 'client_id', 'amount_due')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function (Invoice $invoice): array {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_name' => $invoice->client?->name ?? 'N/A',
                    'amount' => (float) ($invoice->total ?? 0),
                    'amount_due' => (float) ($invoice->amount_due ?? 0),
                    'due_date' => $invoice->due_date,
                    'status' => $invoice->status,
                    'payment_status' => $invoice->payment_status,
                ];
            })
            ->all();
    }

    private function getTopClients(int $userId): array
    {
        return Invoice::query()
            ->where('user_id', $userId)
            ->selectRaw('client_id, COALESCE(SUM(total), 0) as total_billed, COUNT(*) as invoice_count')
            ->groupBy('client_id')
            ->with('client:id,name,state')
            ->orderByDesc('total_billed')
            ->limit(3)
            ->get()
            ->map(function (Invoice $invoice): array {
                return [
                    'client_name' => $invoice->client?->name ?? 'N/A',
                    'state' => $invoice->client?->state ?? 'N/A',
                    'total_billed' => (float) ($invoice->total_billed ?? 0),
                    'invoice_count' => (int) ($invoice->invoice_count ?? 0),
                ];
            })
            ->all();
    }

    private function getOverdueInvoices(int $userId, Carbon $today): array
    {
        return Invoice::query()
            ->where('user_id', $userId)
            ->where('status', 'sent')
            ->whereDate('due_date', '<', $today)
            ->with('client:id,name')
            ->select('id', 'invoice_number', 'client_id', 'amount_due', 'due_date', 'status', 'payment_status')
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(function (Invoice $invoice) use ($today): array {
                $daysOverdue = $invoice->due_date ? $today->diffInDays($invoice->due_date) : 0;

                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_name' => $invoice->client?->name ?? 'N/A',
                    'amount_due' => (float) ($invoice->amount_due ?? 0),
                    'due_date' => $invoice->due_date,
                    'days_overdue' => $daysOverdue,
                    'payment_status' => $invoice->payment_status,
                ];
            })
            ->all();
    }

    private function getFollowupActivity(int $userId): array
    {
        return InvoiceCallLog::query()
            ->whereHas('invoice', fn ($query) => $query->where('user_id', $userId))
            ->with(['invoice:id,invoice_number,client_id,total,amount_due,due_date', 'invoice.client:id,name'])
            ->select('id', 'invoice_number', 'promised_payment_date', 'call_started_at', 'notes', 'conversation', 'confidence')
            ->orderByDesc('call_started_at')
            ->limit(10)
            ->get()
            ->map(function (InvoiceCallLog $log): array {
                $invoice = $log->invoice;
                $daysOverdue = 0;

                if ($invoice?->due_date && $invoice->due_date->isPast()) {
                    $daysOverdue = (int) $invoice->due_date->diffInDays(Carbon::today());
                }

                return [
                    'id' => $log->id,
                    'invoice_number' => $log->invoice_number,
                    'client_name' => $invoice?->client?->name ?? 'N/A',
                    'amount' => (float) ($invoice?->total ?? 0),
                    'amount_due' => (float) ($invoice?->amount_due ?? 0),
                    'days_overdue' => $daysOverdue,
                    'last_contact' => $log->call_started_at,
                    'promised_payment_date' => $log->promised_payment_date,
                    'notes' => $log->notes,
                    'conversation' => $log->conversation,
                    'confidence' => $log->confidence ?? 'low',
                ];
            })
            ->all();
    }

    private function getBusinessInfo(): array
    {
        return [
            'name' => setting('business_name', 'Invoice Pro'),
            'prefixes' => [
                'invoice' => setting('invoice_prefix', 'INV'),
                'quote' => setting('quote_prefix', 'QT'),
                'order' => setting('order_prefix', 'OR'),
            ],
            'defaults' => [
                'due_days' => (int) setting('default_due_days', 15),
            ],
        ];
    }
}
