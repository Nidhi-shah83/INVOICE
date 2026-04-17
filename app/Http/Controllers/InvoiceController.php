<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceCallLog;
use App\Services\InvoiceService;
use App\Services\SettingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class InvoiceController extends Controller
{
    protected array $statusTabs = ['draft', 'sent', 'paid', 'cancelled'];

    public function __construct(
        protected InvoiceService $service,
        protected SettingService $settings,
    )
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');
        $search = trim((string) $request->query('search', ''));

        $filteredQuery = Invoice::query();

        if ($search !== '') {
            $filteredQuery->where(function ($builder) use ($search) {
                $builder->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $query = (clone $filteredQuery)->with('client');

        if ($status && $status !== 'all') {
            if ($status === 'overdue') {
                $query->where('status', 'sent')->whereDate('due_date', '<', now()->toDateString());
            } else {
                $query->where('status', $status);
            }
        }

        $statusCounts = (clone $filteredQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [
            'all' => (clone $filteredQuery)->count(),
            'draft' => (int) ($statusCounts['draft'] ?? 0),
            'sent' => (int) ($statusCounts['sent'] ?? 0),
            'paid' => (int) ($statusCounts['paid'] ?? 0),
            'cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
            'overdue' => (clone $filteredQuery)
                ->where('status', 'sent')
                ->whereDate('due_date', '<', now()->toDateString())
                ->count(),
        ];

        $invoices = $query->orderByDesc('created_at')->paginate(10);

        return view('invoices.index', [
            'invoices' => $invoices,
            'statusTabs' => array_merge(['all'], $this->statusTabs, ['overdue']),
            'activeStatus' => $status !== '' ? $status : 'all',
            'search' => $search,
            'counts' => $counts,
        ]);
    }

    public function searchSuggestions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $term = trim((string) ($data['q'] ?? ''));
        if (strlen($term) < 2) {
            return response()->json([
                'suggestions' => [],
            ]);
        }

        $invoices = Invoice::query()
            ->with('client:id,name,email,phone')
            ->where(function ($builder) use ($term) {
                $builder->where('invoice_number', 'like', "%{$term}%")
                    ->orWhereHas('client', function ($clientQuery) use ($term) {
                        $clientQuery->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%");
                    });
            })
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $suggestions = $invoices->map(function (Invoice $invoice): array {
            return [
                'id' => $invoice->id,
                'invoice_number' => (string) $invoice->invoice_number,
                'client_name' => (string) ($invoice->client?->name ?? 'Unknown client'),
                'client_email' => (string) ($invoice->client?->email ?? ''),
                'payment_status' => (string) $invoice->payment_status,
                'amount_due' => (float) $invoice->amount_due,
                'url' => route('invoices.show', $invoice),
            ];
        })->values();

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }

    public function getCallLogs(string $invoice_number): JsonResponse
    {
        $invoice = Invoice::query()
            ->where('invoice_number', $invoice_number)
            ->firstOrFail();

        if (! empty($invoice->user_id)) {
            InvoiceCallLog::withoutGlobalScopes()
                ->where('invoice_number', $invoice->invoice_number)
                ->whereNull('user_id')
                ->update(['user_id' => (int) $invoice->user_id]);
        }

        $callLogs = $invoice->callLogs()
            ->orderByDesc('call_started_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($log): array => [
                'id' => $log->id,
                'invoice_number' => (string) $log->invoice_number,
                'promised_payment_date' => $log->promised_payment_date?->toDateString(),
                'confidence' => $log->confidence,
                'notes' => $log->notes,
                'conversation' => $log->conversation,
                'call_started_at' => $log->call_started_at?->toIso8601String(),
                'call_ended_at' => $log->call_ended_at?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'invoice_number' => $invoice->invoice_number,
            'call_logs' => $callLogs,
        ]);
    }

    public function create(Request $request): View
    {
        $prefill = session('invoice_draft', []);

        if (! is_array($prefill) || $prefill === []) {
            $prefill = $this->decodeLegacyPrefill($request->query('prefill'));
        }

        return view('invoices.create', [
            'prefill' => is_array($prefill) ? $prefill : [],
            'clients' => Client::query()
                ->orderBy('name')
                ->get(),
            'products' => \Illuminate\Support\Facades\DB::table('products')
                ->where('user_id', (int) $request->user()->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vendor_name' => ['required', 'string', 'max:255'],
            'gstin' => ['nullable', 'string', 'max:15'],
            'invoice_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('invoices', 'invoice_number')->where(
                    fn ($query) => $query->where('user_id', (int) $request->user()->id)
                ),
            ],
            'date' => ['required', 'date'],
            'total_amount' => ['nullable', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.rate' => ['required', 'numeric', 'gte:0'],
            'items.*.amount' => ['nullable', 'numeric', 'gte:0'],
            'action' => ['nullable', Rule::in(['draft', 'final'])],
        ]);

        $user = $request->user();
        $isDraft = ($data['action'] ?? 'final') === 'draft';
        $persistedStatus = $isDraft ? 'draft' : 'sent';

        $items = collect($data['items'])
            ->map(function (array $item): array {
                $quantity = (float) $item['quantity'];
                $rate = (float) $item['rate'];
                $lineAmount = array_key_exists('amount', $item) && $item['amount'] !== null
                    ? (float) $item['amount']
                    : $quantity * $rate;

                return [
                    'name' => trim((string) ($item['name'] ?? '')),
                    'quantity' => $quantity,
                    'rate' => $rate,
                    'amount' => $lineAmount,
                ];
            })
            ->filter(fn (array $item): bool => $item['name'] !== '' && $item['quantity'] > 0)
            ->values();

        if ($items->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'items' => 'Please add at least one valid item.',
                ]);
        }

        $client = $this->resolveClient(
            vendorName: trim((string) $data['vendor_name']),
            gstin: isset($data['gstin']) ? strtoupper(trim((string) $data['gstin'])) : null,
        );

        $subtotal = (float) $items->sum('amount');
        $grandTotal = array_key_exists('total_amount', $data) && $data['total_amount'] !== null
            ? (float) $data['total_amount']
            : $subtotal;
        $roundOff = round($grandTotal - $subtotal, 2);
        $issueDate = $data['date'];
        $dueDate = Carbon::parse($issueDate)
            ->addDays((int) $this->settings->get('default_due_days', 15))
            ->toDateString();
        $notes = $data['notes'] ?? null;
        $invoiceNumber = $data['invoice_number'] ?? null;

        $invoice = DB::transaction(function () use (
            $user,
            $client,
            $items,
            $issueDate,
            $dueDate,
            $persistedStatus,
            $roundOff,
            $subtotal,
            $grandTotal,
            $notes,
            $invoiceNumber
        ) {
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'client_id' => $client->id,
                'invoice_number' => $invoiceNumber ?: $this->service->generateInvoiceNumber($user->id),
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'status' => $persistedStatus,
                'subtotal' => $subtotal,
                'cgst' => 0,
                'sgst' => 0,
                'igst' => 0,
                'total' => $grandTotal,
                'round_off' => $roundOff,
                'grand_total' => $grandTotal,
                'amount_paid' => 0,
                'amount_due' => $grandTotal,
                'payment_status' => 'unpaid',
                'currency' => (string) setting('currency', 'INR'),
                'notes' => $notes,
            ]);

            foreach ($items as $item) {
                $invoice->items()->create([
                    'name' => $item['name'],
                    'qty_billed' => $item['quantity'],
                    'rate' => $item['rate'],
                    'gst_percent' => 0,
                    'amount' => $item['amount'],
                ]);
            }

            return $invoice;
        });

        $invoice = $this->service->syncInvoicePaymentState($invoice);

        $request->session()->forget('invoice_draft');

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', $isDraft ? 'Invoice saved as draft.' : 'Invoice saved as final.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'order', 'items', 'payments']);

        return view('invoices.show', compact('invoice'));
    }

    public function download(Invoice $invoice)
    {
        return $this->downloadPdf($invoice);
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['client', 'items']);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('INV-'.$invoice->invoice_number.'.pdf');
    }

    public function send(Invoice $invoice)
    {
        $this->service->sendInvoice($invoice);

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice sent.');
    }

    public function showPaymentPage($id): View
    {
        $invoice = Invoice::with('client')->findOrFail($id);

        return view('payments.demo', compact('invoice'));
    }

    public function processPayment(Request $request, $id): RedirectResponse
    {
        $invoice = Invoice::with('client')->findOrFail($id);
        $amount = 0.0;

        try {
            $payment = $this->service->markInvoiceAsPaid(
                $invoice,
                'demo_'.$invoice->id.'_'.Str::uuid()->toString(),
                (string) ($invoice->razorpay_order_id ?: $invoice->invoice_number),
            );
            $amount = (float) $payment->amount;
        } catch (\InvalidArgumentException) {
            $invoice = $this->service->syncInvoicePaymentState($invoice);
            $amount = (float) $invoice->amount_paid;
        }

        return redirect()
            ->route('payment.success')
            ->with('payment_success', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $amount,
            ]);
    }

    public function markPaid(Request $request, Invoice $invoice): JsonResponse
    {
        $this->ensureApiSecret($request);

        return $this->recordPaymentAndRespond($request, $invoice);
    }

    public function markPaidManually(Request $request, Invoice $invoice)
    {
        if ($request->expectsJson()) {
            return $this->recordPaymentAndRespond($request, $invoice);
        }

        $this->recordPaymentAndRespond($request, $invoice);

        return redirect()->route('invoices.show', $invoice)->with('status', 'Payment recorded successfully.');
    }

    public function overdue(Request $request)
    {
        $this->ensureApiSecret($request);

        return response()->json($this->service->overdueInvoices());
    }

    protected function decodeLegacyPrefill(?string $encoded): array
    {
        if (! is_string($encoded) || $encoded === '') {
            return [];
        }

        $decoded = base64_decode($encoded, true);
        if ($decoded === false) {
            return [];
        }

        $payload = json_decode($decoded, true);

        return is_array($payload) ? $payload : [];
    }

    protected function resolveClient(string $vendorName, ?string $gstin = null): Client
    {
        $clientQuery = Client::query();

        if ($gstin) {
            $existingByGstin = (clone $clientQuery)->where('gstin', $gstin)->first();
            if ($existingByGstin) {
                return $existingByGstin;
            }
        }

        $existingByName = (clone $clientQuery)->where('name', $vendorName)->first();
        if ($existingByName) {
            if ($gstin && ! $existingByName->gstin) {
                $existingByName->update(['gstin' => $gstin]);
            }

            return $existingByName;
        }

        $slug = Str::slug($vendorName ?: 'vendor');
        $token = now()->format('YmdHis');

        return Client::create([
            'user_id' => (int) auth()->id(),
            'name' => $vendorName,
            'email' => "{$slug}-{$token}@example.com",
            'phone' => 'N/A',
            'gstin' => $gstin,
            'state' => (string) setting('state', 'Karnataka'),
            'address' => 'N/A',
        ]);
    }

    protected function ensureApiSecret(Request $request): void
    {
        $secret = $request->header('X-N8N-Secret');
        $configured = (string) config('invoice.overdue_secret', '');
        if ($configured === '' || ! hash_equals($configured, $secret ?? '')) {
            abort(401);
        }
    }

    protected function recordPaymentAndRespond(Request $request, Invoice $invoice): JsonResponse
    {
        $invoice = $this->service->syncInvoicePaymentState($invoice);

        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'payment_id' => ['nullable', 'string', 'max:255'],
            'order_id' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = array_key_exists('amount', $data) && $data['amount'] !== null
            ? (float) $data['amount']
            : (float) $invoice->amount_due;
        $paymentId = isset($data['payment_id']) && $data['payment_id'] !== ''
            ? trim((string) $data['payment_id'])
            : 'manual_'.$invoice->id.'_'.Str::uuid()->toString();
        $orderId = isset($data['order_id']) && $data['order_id'] !== ''
            ? trim((string) $data['order_id'])
            : (string) ($invoice->razorpay_order_id ?: $invoice->invoice_number);

        try {
            $payment = $this->service->markInvoicePaid($invoice, $amount, $paymentId, $orderId);
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'amount' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Payment recorded successfully.',
            'payment' => [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'payment_id' => $payment->razorpay_payment_id,
                'order_id' => $payment->razorpay_order_id,
            ],
            'invoice' => $this->service->paymentSummary($invoice),
        ]);
    }
}
