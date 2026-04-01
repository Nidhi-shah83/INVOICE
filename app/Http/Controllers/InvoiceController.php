<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceSentMail;
use App\Mail\PaymentConfirmedMail;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    protected array $statusTabs = ['draft', 'sent', 'paid', 'cancelled'];

    public function __construct(protected InvoiceService $service)
    {
        $this->middleware('auth')->except(['overdue', 'markPaid']);
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

        $this->service->sendInvoice($invoice);

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice sent.');
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
