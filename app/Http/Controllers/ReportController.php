<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceCallLog;
use App\Models\Client;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class ReportController extends ModuleResourceController
{
    public function __construct(ReportService $service)
    {
        parent::__construct($service);
    }

    public function index(): View
    {
        $request = request();
        $fromDate = $request->get('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $reportType = $request->get('report_type', 'gst');

        $data = $this->generateReportData($reportType, $fromDate, $toDate);

        return view('reports.index', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'reportType' => $reportType,
            'data' => $data,
        ]);
    }

    private function generateReportData(string $reportType, string $fromDate, string $toDate): array
    {
        switch ($reportType) {
            case 'gst':
                return $this->getGstSummary($fromDate, $toDate);
            case 'revenue_by_client':
                return $this->getRevenueByClient($fromDate, $toDate);
            case 'ar_aging':
                return $this->getArAging($fromDate, $toDate);
            case 'ai_performance':
                return $this->getAiPerformance($fromDate, $toDate);
            case 'followup_insights':
                return $this->getFollowupInsights($fromDate, $toDate);
            default:
                return [];
        }
    }

    private function getGstSummary(string $fromDate, string $toDate): array
    {
        return Invoice::where('payment_status', 'paid')
            ->whereBetween('issue_date', [$fromDate, $toDate])
            ->selectRaw("
                DATE_FORMAT(issue_date, '%Y-%m') as month,
                SUM(subtotal) as subtotal,
                SUM(cgst) as cgst,
                SUM(sgst) as sgst,
                SUM(igst) as igst,
                SUM(total) as total
            ")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    private function getRevenueByClient(string $fromDate, string $toDate): array
    {
        return Invoice::whereBetween('issue_date', [$fromDate, $toDate])
            ->with('client')
            ->selectRaw("
                client_id,
                SUM(total) as total_billed,
                SUM(amount_paid) as total_paid,
                SUM(amount_due) as outstanding,
                COUNT(*) as invoice_count
            ")
            ->groupBy('client_id')
            ->get()
            ->map(function ($invoice) {
                return [
                    'client_name' => $invoice->client->name ?? 'Unknown',
                    'state' => $invoice->client->state ?? 'Unknown',
                    'total_billed' => $invoice->total_billed,
                    'total_paid' => $invoice->total_paid,
                    'outstanding' => $invoice->outstanding,
                    'invoice_count' => $invoice->invoice_count,
                ];
            })
            ->toArray();
    }

    private function getArAging(string $fromDate, string $toDate): array
    {
        $invoices = Invoice::where('status', 'sent')
            ->where('payment_status', '!=', 'paid')
            ->whereBetween('issue_date', [$fromDate, $toDate])
            ->get();

        $buckets = [
            'current' => ['amount' => 0, 'count' => 0],
            '1-30' => ['amount' => 0, 'count' => 0],
            '31-60' => ['amount' => 0, 'count' => 0],
            '61-90' => ['amount' => 0, 'count' => 0],
            '90+' => ['amount' => 0, 'count' => 0],
        ];

        $now = Carbon::now();

        foreach ($invoices as $invoice) {
            $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date) : now();
            $daysOverdue = $now->diffInDays($dueDate, false);

            if ($daysOverdue >= 0) {
                $bucket = 'current';
            } elseif ($daysOverdue >= -30) {
                $bucket = '1-30';
            } elseif ($daysOverdue >= -60) {
                $bucket = '31-60';
            } elseif ($daysOverdue >= -90) {
                $bucket = '61-90';
            } else {
                $bucket = '90+';
            }

            $buckets[$bucket]['amount'] += $invoice->amount_due;
            $buckets[$bucket]['count']++;
        }

        return $buckets;
    }

    private function getAiPerformance(string $fromDate, string $toDate): array
    {
        $callLogs = InvoiceCallLog::whereHas('invoice', function ($query) use ($fromDate, $toDate) {
            $query->whereBetween('issue_date', [$fromDate, $toDate]);
        })->get();

        $totalCalls = $callLogs->count();
        $invoicesWithFollowups = $callLogs->pluck('invoice_number')->unique()->count();

        $promises = $callLogs->whereNotNull('promised_payment_date');
        $avgPromisedDelay = $promises->isNotEmpty() ?
            $promises->sum(function ($log) {
                $dueDate = $log->invoice->due_date ?? now();
                $promisedDate = $log->promised_payment_date ?? now();
                return Carbon::parse($dueDate)->diffInDays($promisedDate, false);
            }) / $promises->count() : 0;

        $highConfidence = $callLogs->where('confidence', 'high')->count();
        $lowConfidence = $callLogs->where('confidence', 'low')->count();
        $confidenceRatio = $lowConfidence > 0 ? $highConfidence / $lowConfidence : ($highConfidence > 0 ? '∞' : 0);

        $fulfilledPromises = 0;
        $brokenPromises = 0;

        foreach ($promises as $promise) {
            $invoice = $promise->invoice;
            if ($invoice && $invoice->payment_status === 'paid' && $promise->promised_payment_date) {
                $paymentDate = $invoice->payments->sortBy('created_at')->first()?->created_at;
                if ($paymentDate && Carbon::parse($paymentDate)->lte($promise->promised_payment_date)) {
                    $fulfilledPromises++;
                } else {
                    $brokenPromises++;
                }
            } else {
                $brokenPromises++;
            }
        }

        return [
            'total_calls' => $totalCalls,
            'invoices_with_followups' => $invoicesWithFollowups,
            'avg_promised_delay' => round($avgPromisedDelay, 1),
            'confidence_ratio' => $confidenceRatio,
            'fulfilled_promises' => $fulfilledPromises,
            'broken_promises' => $brokenPromises,
        ];
    }

    private function getFollowupInsights(string $fromDate, string $toDate): array
    {
        return InvoiceCallLog::whereHas('invoice', function ($query) use ($fromDate, $toDate) {
            $query->whereBetween('issue_date', [$fromDate, $toDate]);
        })
        ->with(['invoice.client'])
        ->get()
        ->groupBy('invoice_number')
        ->map(function ($logs, $invoiceNumber) {
            if ($logs->isEmpty()) {
                return null;
            }

            $invoice = $logs->first()->invoice;
            $lastCall = $logs->sortByDesc('created_at')->first();
            $promisedDate = $lastCall ? $lastCall->promised_payment_date : null;

            $actualPaymentDate = null;
            $delay = null;

            if ($invoice && $invoice->payment_status === 'paid') {
                $payment = $invoice->payments->sortBy('created_at')->first();
                if ($payment) {
                    $actualPaymentDate = $payment->created_at;
                    if ($promisedDate) {
                        $delay = Carbon::parse($actualPaymentDate)->diffInDays($promisedDate, false);
                    }
                }
            }

            return [
                'invoice_number' => $invoiceNumber,
                'client_name' => $invoice->client->name ?? 'Unknown',
                'amount' => $invoice->amount_due,
                'last_call_date' => $lastCall && $lastCall->created_at ? $lastCall->created_at->format('Y-m-d') : null,
                'promised_payment_date' => $promisedDate ? $promisedDate->format('Y-m-d') : null,
                'actual_payment_date' => $actualPaymentDate ? Carbon::parse($actualPaymentDate)->format('Y-m-d') : null,
                'delay_days' => $delay,
            ];
        })
        ->filter() // remove null values
        ->values()
        ->toArray();
    }

    public function export()
    {
        $request = request();
        $fromDate = $request->get('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $reportType = $request->get('report_type', 'gst');

        $data = $this->generateReportData($reportType, $fromDate, $toDate);

        return $this->generateCsvResponse($reportType, $data);
    }

    private function generateCsvResponse(string $reportType, array $data): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = $reportType . '_report_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->stream(function () use ($reportType, $data) {
            $handle = fopen('php://output', 'w');

            switch ($reportType) {
                case 'gst':
                    fputcsv($handle, ['Month', 'Subtotal', 'CGST', 'SGST', 'IGST', 'Total']);
                    foreach ($data as $row) {
                        fputcsv($handle, [
                            Carbon::createFromFormat('Y-m', $row['month'])->format('M Y'),
                            $row['subtotal'],
                            $row['cgst'],
                            $row['sgst'],
                            $row['igst'],
                            $row['total']
                        ]);
                    }
                    break;
                case 'revenue_by_client':
                    fputcsv($handle, ['Client', 'State', 'Total Billed', 'Paid', 'Outstanding', 'Invoices']);
                    foreach ($data as $row) {
                        fputcsv($handle, [
                            $row['client_name'],
                            $row['state'],
                            $row['total_billed'],
                            $row['total_paid'],
                            $row['outstanding'],
                            $row['invoice_count']
                        ]);
                    }
                    break;
                case 'ar_aging':
                    fputcsv($handle, ['Bucket', 'Amount', 'Count']);
                    $bucketLabels = [
                        'current' => 'Current',
                        '1-30' => '1-30 Days',
                        '31-60' => '31-60 Days',
                        '61-90' => '61-90 Days',
                        '90+' => '90+ Days'
                    ];
                    foreach ($data as $bucket => $info) {
                        fputcsv($handle, [
                            $bucketLabels[$bucket] ?? $bucket,
                            $info['amount'],
                            $info['count']
                        ]);
                    }
                    break;
                case 'ai_performance':
                    fputcsv($handle, ['Metric', 'Value']);
                    fputcsv($handle, ['Total Calls', $data['total_calls']]);
                    fputcsv($handle, ['Invoices with Follow-ups', $data['invoices_with_followups']]);
                    fputcsv($handle, ['Avg Promised Delay (Days)', $data['avg_promised_delay']]);
                    fputcsv($handle, ['Confidence Ratio', is_numeric($data['confidence_ratio']) ? $data['confidence_ratio'] : $data['confidence_ratio']]);
                    fputcsv($handle, ['Fulfilled Promises', $data['fulfilled_promises']]);
                    fputcsv($handle, ['Broken Promises', $data['broken_promises']]);
                    break;
                case 'followup_insights':
                    fputcsv($handle, ['Invoice #', 'Client', 'Amount', 'Last Call', 'Promised Date', 'Actual Payment', 'Delay (Days)', 'Status']);
                    foreach ($data as $row) {
                        $status = $row['actual_payment_date'] ? 'Paid' : ($row['promised_payment_date'] ? 'Promised' : 'Follow-up');
                        fputcsv($handle, [
                            $row['invoice_number'],
                            $row['client_name'],
                            $row['amount'],
                            $row['last_call_date'],
                            $row['promised_payment_date'] ?? '',
                            $row['actual_payment_date'] ?? '',
                            $row['delay_days'] ?? '',
                            $status
                        ]);
                    }
                    break;
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }}
