<?php

namespace App\Http\Controllers;

use App\Mail\QuoteMail;
use App\Http\Requests\QuoteRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quote;
use App\Services\InvoiceService;
use App\Services\QuoteService;
use App\Services\SettingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuoteController extends Controller
{
    public function __construct(
        protected QuoteService $quoteService,
        protected InvoiceService $invoiceService,
        protected SettingService $settingService,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $urls = ['all', 'draft', 'sent', 'accepted', 'declined', 'expired', 'converted'];

        $query = Quote::with('client')->where('user_id', $user->id);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('quote_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $quotes = $query->orderByDesc('created_at')->paginate(12);

        return view('quotes.index', [
            'quotes' => $quotes,
            'pipeline' => $this->pipeline($user->id),
            'statusTabs' => $urls,
            'activeStatus' => $status,
            'counts' => $this->statusCounts($user->id, $urls),
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('quotes.create', [
            'clients' => Client::query()->orderBy('name')->get(),
        ]);
    }

    public function store(QuoteRequest $request)
    {
        $quote = $this->quoteService->persist($this->payload($request));

        return redirect()->route('quotes.show', $quote)->with('status', 'Quote saved.');
    }

    public function show(Quote $quote)
    {
        $this->ensureOwnership($quote);

        $quote->load('items');

        $businessSettings = $this->settingService->getMany([
            'business_name',
            'gstin',
            'address',
            'state',
            'email',
            'phone',
        ]);

        return view('quotes.show', [
            'quote' => $quote,
            'businessSettings' => $businessSettings,
        ]);
    }

    public function edit(Quote $quote)
    {
        $this->ensureOwnership($quote);

        return view('quotes.edit', [
            'quote' => $quote,
            'clients' => Client::query()->orderBy('name')->get(),
        ]);
    }

    public function update(QuoteRequest $request, Quote $quote)
    {
        $this->ensureOwnership($quote);

        $quote = $this->quoteService->persist($this->payload($request), $quote);

        return redirect()->route('quotes.show', $quote)->with('status', 'Quote updated.');
    }

    public function destroy(Quote $quote)
    {
        $this->ensureOwnership($quote);

        $quote->delete();

        return redirect()->route('quotes.index')->with('status', 'Quote deleted.');
    }

    public function send(Quote $quote)
    {
        $this->ensureOwnership($quote);
        $quote->load('client', 'items');
        $clientEmail = trim((string) ($quote->client?->email ?? ''));

        if ($clientEmail === '' || ! filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors(['quote_send' => 'Client email is missing or invalid. Please update the client email before sending this quote.']);
        }

        apply_user_mail_config((int) $quote->user_id);

        try {
            $token = (string) Str::uuid();
            $quote->approval_token = $token;
            $quote->accept_token = $token;
            $quote->status = 'sent';
            $quote->accepted_at = null;
            $quote->save();

            $pdf = Pdf::loadView('quotes.pdf', compact('quote'));
            $pdfOutput = $pdf->output();

            $path = 'quotes/'.$quote->quote_number.'.pdf';
            Storage::disk('public')->put($path, $pdfOutput);

            Mail::to($clientEmail)->send(new QuoteMail($quote, $pdfOutput));

            return back()->with('success', 'Quote sent successfully');
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['quote_send' => 'Unable to send quote: '.$exception->getMessage()]);
        }
    }

    public function download(Quote $quote)
    {
        $this->ensureOwnership($quote);

        $quote->load('client', 'items');

        $pdf = Pdf::loadView('quotes.pdf', compact('quote'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
            ]);

        return $pdf->download("{$quote->quote_number}.pdf");
    }

    public function downloadPdf(Request $request, Quote $quote)
    {
        $this->ensureOwnership($quote);

        $quote->load('client', 'items');
        $pdf = Pdf::loadView('quotes.pdf', compact('quote'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
            ]);

        if ($request->boolean('download')) {
            return $pdf->download("{$quote->quote_number}.pdf");
        }

        return $pdf->stream("{$quote->quote_number}.pdf");
    }

    public function convert(Quote $quote)
    {
        $this->ensureOwnership($quote);

        $order = $this->invoiceService->convertQuoteToOrder($quote);

        return redirect()->route('orders.show', $order)->with('status', 'Quote converted to order.');
    }

    public function approve(int $id, string $token)
    {
        $quote = Quote::with(['client', 'items', 'order'])
            ->where('id', $id)
            ->where(function ($query) use ($token) {
                $query->where('approval_token', $token)
                    ->orWhere('accept_token', $token);
            })
            ->firstOrFail();

        // Prevent duplicate order conversion on repeated link clicks.
        if (in_array($quote->status, ['accepted', 'converted'], true) && $quote->order) {
            return view('quotes.already-approved', [
                'quote' => $quote,
                'order' => $quote->order,
            ]);
        }

        $quote->status = 'accepted';
        $quote->accepted_at = now();
        $quote->save();

        $order = $this->convertQuoteToOrderFromApproval($quote);

        return view('quotes.approved-success', compact('quote', 'order'));
    }

    public function accept(string $token)
    {
        $quote = Quote::query()
            ->where('accept_token', $token)
            ->orWhere('approval_token', $token)
            ->firstOrFail();

        return $this->approve((int) $quote->id, $token);
    }

    protected function payload(QuoteRequest $request): array
    {
        return array_merge($request->validated(), [
            'user_id' => $request->user()->id,
            'issue_date' => $request->input('issue_date') ?? now()->toDateString(),
        ]);
    }

    protected function ensureOwnership(Quote $quote): void
    {
        if ($quote->user_id !== auth()->id()) {
            abort(403);
        }
    }

    protected function pipeline(int $userId): array
    {
        return [
            'quotes' => Quote::where('user_id', $userId)->count(),
            'orders' => Order::where('user_id', $userId)->count(),
            'invoices' => Invoice::where('user_id', $userId)->count(),
            'paid' => Invoice::where('user_id', $userId)->where('status', 'paid')->count(),
        ];
    }

    protected function statusCounts(int $userId, array $statuses): array
    {
        $counts = [];
        foreach ($statuses as $status) {
            $counts[$status] = Quote::where('user_id', $userId)
                ->when($status && $status !== 'all', fn($q) => $q->where('status', $status))
                ->count();
        }

        return $counts;
    }

    protected function convertQuoteToOrderFromApproval(Quote $quote): Order
    {
        if ($quote->order_id) {
            return $quote->order()->firstOrFail();
        }

        return DB::transaction(function () use ($quote): Order {
            $order = Order::create([
                'user_id' => $quote->user_id,
                'client_id' => $quote->client_id,
                'quote_id' => $quote->id,
                'order_number' => $this->nextOrderNumberFromQuote($quote->quote_number),
                'status' => 'pending',
                'acceptance_token' => (string) Str::uuid(),
                'total_amount' => (float) ($quote->grand_total ?: $quote->total),
                'billed_amount' => 0,
            ]);

            foreach ($quote->items as $item) {
                $order->items()->create([
                    'name' => $item->name,
                    'qty' => $item->qty,
                    'rate' => $item->rate,
                    'gst_percent' => $item->gst_percent,
                ]);
            }

            $quote->order_id = $order->id;
            $quote->status = 'converted';
            $quote->accepted_at = $quote->accepted_at ?: now();
            $quote->save();

            return $order;
        });
    }

    protected function nextOrderNumberFromQuote(string $quoteNumber): string
    {
        $base = Str::startsWith($quoteNumber, 'QT')
            ? Str::replaceFirst('QT', 'OR', $quoteNumber)
            : 'OR-'.$quoteNumber;

        $candidate = $base;
        $suffix = 1;

        while (Order::where('order_number', $candidate)->exists()) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
