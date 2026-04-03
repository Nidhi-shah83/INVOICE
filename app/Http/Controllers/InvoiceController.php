<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceSentMail;
use App\Mail\PaymentConfirmedMail;
use App\Mail\PaymentSuccessMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\RazorpayService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class InvoiceController extends Controller
{
    protected array $statusTabs = ['draft', 'sent', 'paid', 'cancelled'];

    public function __construct(
        protected InvoiceService $service,
        protected RazorpayService $razorpayService,
    )
    {
        $this->middleware('auth')->except(['overdue', 'markPaid', 'showPaymentPage', 'processPayment']);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');

        $query = Invoice::with('client')->where('user_id', $user->id);

        if ($status && $status !== 'all') {
            if ($status === 'overdue') {
                $query->where('status', 'sent')->whereDate('due_date', '<', now()->toDateString());
            } else {
                $query->where('status', $status);
            }
        }

        $counts = collect($this->statusTabs)
            ->mapWithKeys(fn ($tab) => [$tab => Invoice::where('user_id', $user->id)->where('status', $tab)->count()])
            ->toArray();

        $invoices = $query->orderByDesc('created_at')->paginate(10);

        return view('invoices.index', [
            'invoices' => $invoices,
            'statusTabs' => array_merge(['all'], $this->statusTabs, ['overdue']),
            'activeStatus' => $status,
            'counts' => $counts,
        ]);
    }

    public function create(Request $request): View
    {
        $prefill = session('invoice_draft', []);

        if (! is_array($prefill) || $prefill === []) {
            $prefill = $this->decodeLegacyPrefill($request->query('prefill'));
        }

        $userId = (int) $request->user()->id;

        return view('invoices.create', [
            'prefill' => is_array($prefill) ? $prefill : [],
            'clients' => Client::query()
                ->where('user_id', $userId)
                ->orderBy('name')
                ->get(),
            'products' => \Illuminate\Support\Facades\DB::table('products')
                ->where('user_id', $userId)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vendor_name' => ['required', 'string', 'max:255'],
            'gstin' => ['nullable', 'string', 'max:15'],
            'invoice_number' => ['nullable', 'string', 'max:255', Rule::unique('invoices', 'invoice_number')],
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
        $status = ($data['action'] ?? 'final') === 'draft' ? 'draft' : 'final';
        $persistedStatus = $status === 'draft'
            ? 'draft'
            : (in_array('final', $this->statusTabs, true) ? 'final' : 'sent');

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
            userId: $user->id,
            vendorName: trim((string) $data['vendor_name']),
            gstin: isset($data['gstin']) ? strtoupper(trim((string) $data['gstin'])) : null,
        );

        $subtotal = (float) $items->sum('amount');
        $total = array_key_exists('total_amount', $data) && $data['total_amount'] !== null
            ? (float) $data['total_amount']
            : $subtotal;
        $issueDate = $data['date'];
        $dueDate = Carbon::parse($issueDate)
            ->addDays((int) config('invoice.default_due_days', 15))
            ->toDateString();

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => $data['invoice_number'] ?: $this->service->generateInvoiceNumber($user->id),
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'status' => $persistedStatus,
            'subtotal' => $subtotal,
            'cgst' => 0,
            'sgst' => 0,
            'igst' => 0,
            'total' => $total,
            'notes' => $data['notes'] ?? null,
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

        $request->session()->forget('invoice_draft');

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', $status === 'draft' ? 'Invoice saved as draft.' : 'Invoice saved as final.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        $invoice->load(['client', 'order', 'items', 'payments']);

        return view('invoices.show', compact('invoice'));
    }

    public function download(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        if ($invoice->pdf_path && Storage::disk('local')->exists($invoice->pdf_path)) {
            return response()->file(Storage::disk('local')->path($invoice->pdf_path));
        }

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }

    public function send(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        $invoice->load('client');

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        $path = 'invoices/'.$invoice->invoice_number.'.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        // ===== Razorpay Integration (Production Only) =====
        // This code is disabled for demo without KYC
        // Uncomment when using real payments
        // $paymentLinkResponse = $this->razorpayService->createPaymentLink($invoice);
        // $orderId = (string) ($paymentLinkResponse['id'] ?? '');
        // $paymentLink = (string) ($paymentLinkResponse['short_url'] ?? '');
        $orderId = null;
        $paymentLink = route('invoices.pay', $invoice->id);

        $invoice->update([
            'status' => 'sent',
            'razorpay_order_id' => $orderId,
            'payment_link' => $paymentLink,
            'pdf_path' => $path,
        ]);

        Mail::to($invoice->client->email)
            ->send(new InvoiceSentMail($invoice, $paymentLink, $pdf));

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
        $amountPaid = (float) ($invoice->grand_total ?? $invoice->total ?? 0);

        $invoice->status = 'paid';
        $invoice->payment_status = 'paid';
        $invoice->amount_paid = $amountPaid;
        $invoice->amount_due = 0;
        $invoice->save();

        if (! empty($invoice->client?->email)) {
            Mail::to($invoice->client->email)->send(new PaymentSuccessMail($invoice));
        }

        return redirect()
            ->route('payment.success')
            ->with('payment_success', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $amountPaid,
            ]);
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $this->ensureApiSecret($request);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_id' => 'required|string',
            'order_id' => 'required|string',
        ]);

        $this->service->markInvoicePaid($invoice, $data['amount'], $data['payment_id'], $data['order_id']);

        return response()->json(['status' => 'ok']);
    }

    public function markPaidManually(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_id' => 'required|string',
            'order_id' => 'required|string',
        ]);

        $this->service->markInvoicePaid($invoice, $data['amount'], $data['payment_id'], $data['order_id']);

        return redirect()->route('invoices.show', $invoice)->with('status', 'Payment recorded and invoice marked as paid.');
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

    protected function resolveClient(int $userId, string $vendorName, ?string $gstin = null): Client
    {
        $clientQuery = Client::where('user_id', $userId);

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
            'user_id' => $userId,
            'name' => $vendorName,
            'email' => "{$slug}-{$token}@example.com",
            'phone' => 'N/A',
            'gstin' => $gstin,
            'state' => (string) config('invoice.state', 'Karnataka'),
            'address' => 'N/A',
        ]);
    }

    protected function authorizeInvoice(Invoice $invoice): void
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }
    }

    protected function ensureApiSecret(Request $request): void
    {
        $secret = $request->header('X-N8N-Secret');
        $configured = config('invoice.overdue_secret');
        if (! hash_equals($configured, $secret ?? '')) {
            abort(401);
        }
    }
}
