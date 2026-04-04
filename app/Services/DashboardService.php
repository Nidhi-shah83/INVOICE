<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\InvoiceCallLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function overview(): array
    {
        $user = Auth::user();
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        return [
            // 1. STAT CARDS DATA
            'total_revenue' => $this->getTotalRevenue($user->id, $monthStart),
            'unpaid_amount' => $this->getUnpaidAmount($user->id),
            'overdue_amount' => $this->getOverdueAmount($user->id, $today),
            'active_orders' => $this->getActiveOrdersCount($user->id),

            // 2. RECENT INVOICES (Latest 5)
            'recent_invoices' => $this->getRecentInvoices($user->id),

            // 3. TOP CLIENTS
            'top_clients' => $this->getTopClients($user->id),

            // 4. OVERDUE INVOICES
            'overdue_invoices' => $this->getOverdueInvoices($user->id, $today),

            // 5. AI FOLLOW-UP ACTIVITY (Latest 10)
            'followup_activity' => $this->getFollowupActivity($user->id),

            // Business info
            'business' => $this->getBusinessInfo(),
        ];
    }

    /**
     * Total revenue in current month
     */
    private function getTotalRevenue(int $userId, Carbon $monthStart): float
    {
        return (float) Payment::query()
            ->whereHas('invoice', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('status', 'captured')
            ->whereBetween('created_at', [$monthStart, $monthStart->copy()->endOfMonth()])
            ->sum('amount');
    }

    /**
     * Sum of unpaid invoices (payment_status = 'unpaid' OR status = 'sent' with no full payment)
     */
    private function getUnpaidAmount(int $userId): float
    {
        return (float) Invoice::where('user_id', $userId)
            ->where(function ($q) {
                $q->where('payment_status', 'unpaid')
                  ->orWhere('payment_status', 'partial');
            })
            ->sum('amount_due');
    }

    /**
     * Sum of overdue invoices (status = 'sent' AND due_date < today)
     */
    private function getOverdueAmount(int $userId, Carbon $today): float
    {
        return (float) Invoice::where('user_id', $userId)
            ->where('status', 'sent')
            ->whereDate('due_date', '<', $today)
            ->sum('amount_due');
    }

    /**
     * Count of active orders (NOT in fully_billed, cancelled)
     */
    private function getActiveOrdersCount(int $userId): int
    {
        return Order::where('user_id', $userId)
            ->whereNotIn('status', ['fully_billed', 'cancelled'])
            ->count();
    }

    /**
     * Latest 5 invoices with client info
     */
    private function getRecentInvoices(int $userId): array
    {
        return Invoice::where('user_id', $userId)
            ->with('client:id,name,state')
            ->select('id', 'invoice_number', 'total', 'due_date', 'status', 'payment_status', 'client_id', 'amount_due')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_name' => $invoice->client?->name ?? 'N/A',
                    'amount' => $invoice->total ?? 0,
                    'amount_due' => $invoice->amount_due ?? 0,
                    'due_date' => $invoice->due_date,
                    'status' => $invoice->status,
                    'payment_status' => $invoice->payment_status,
                ];
            })
            ->toArray();
    }

    /**
     * Top 5 clients by total billed amount
     */
    private function getTopClients(int $userId): array
    {
        return Invoice::where('user_id', $userId)
            ->select('client_id')
            ->groupBy('client_id')
            ->selectRaw('client_id, SUM(total) as total_billed, COUNT(*) as invoice_count')
            ->with('client:id,name,state')
            ->orderByDesc('total_billed')
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'client_name' => $invoice->client?->name ?? 'N/A',
                    'state' => $invoice->client?->state ?? 'N/A',
                    'total_billed' => $invoice->total_billed ?? 0,
                    'invoice_count' => $invoice->invoice_count ?? 0,
                ];
            })
            ->toArray();
    }

    /**
     * All overdue invoices (status = 'sent' AND due_date < today)
     */
    private function getOverdueInvoices(int $userId, Carbon $today): array
    {
        return Invoice::where('user_id', $userId)
            ->where('status', 'sent')
            ->whereDate('due_date', '<', $today)
            ->with('client:id,name')
            ->select('id', 'invoice_number', 'client_id', 'amount_due', 'due_date', 'status', 'payment_status')
            ->orderByDesc('due_date')
            ->get()
            ->map(function ($invoice) use ($today) {
                $daysOverdue = $today->diffInDays($invoice->due_date);
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_name' => $invoice->client?->name ?? 'N/A',
                    'amount_due' => $invoice->amount_due ?? 0,
                    'due_date' => $invoice->due_date,
                    'days_overdue' => $daysOverdue,
                    'payment_status' => $invoice->payment_status,
                ];
            })
            ->toArray();
    }

    /**
     * Latest 10 AI follow-up call logs with invoice & client info
     */
    private function getFollowupActivity(int $userId): array
    {
        // First get all invoices for the user
        $invoiceNumbers = Invoice::where('user_id', $userId)
            ->pluck('invoice_number')
            ->toArray();

        // Then get the call logs for those invoices
        return InvoiceCallLog::whereIn('invoice_number', $invoiceNumbers)
            ->with('invoice.client:id,name')
            ->select(
                'id',
                'invoice_number',
                'promised_payment_date',
                'call_started_at',
                'notes',
                'conversation',
                'confidence'
            )
            ->orderByDesc('call_started_at')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                $invoice = $log->invoice;
                $daysOverdue = Carbon::today()->diffInDays($invoice->due_date);
                return [
                    'id' => $log->id,
                    'invoice_number' => $log->invoice_number,
                    'client_name' => $invoice->client?->name ?? 'N/A',
                    'amount' => $invoice->total ?? 0,
                    'amount_due' => $invoice->amount_due ?? 0,
                    'days_overdue' => $daysOverdue < 0 ? 0 : $daysOverdue,
                    'last_contact' => $log->call_started_at,
                    'promised_payment_date' => $log->promised_payment_date,
                    'notes' => $log->notes,
                    'conversation' => $log->conversation,
                    'confidence' => $log->confidence ?? 'low',
                ];
            })
            ->toArray();
    }

    /**
     * Business configuration info
     */
    private function getBusinessInfo(): array
    {
        $invoice = config('invoice');

        return [
            'name' => $invoice['business_name'] ?? 'Your Business Name',
            'prefixes' => [
                'invoice' => $invoice['invoice_prefix'] ?? 'INV',
                'quote' => $invoice['quote_prefix'] ?? 'QUO',
                'order' => $invoice['order_prefix'] ?? 'ORD',
            ],
            'defaults' => [
                'due_days' => $invoice['default_due_days'] ?? 15,
            ],
        ];
    }
}
