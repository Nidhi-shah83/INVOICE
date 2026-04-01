<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quote;
use App\Services\InvoiceService;
use App\Services\QuoteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class QuoteController extends Controller
{
    public function __construct(
        protected QuoteService $quoteService,
        protected InvoiceService $invoiceService,
    ) {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');

        $urls = ['draft', 'sent', 'accepted', 'declined', 'expired', 'converted'];

        $query = Quote::with('client')->where('user_id', $user->id);

        if ($status) {
            $query->where('status', $status);
        }

        $quotes = $query->orderByDesc('created_at')->paginate(12);

        return view('quotes.index', [
            'quotes' => $quotes,
            'pipeline' => $this->pipeline($user->id),
            'statusTabs' => $urls,
            'activeStatus' => $status,
        ]);
    }

    public function create()
    {
        return view('quotes.create', [
            'clients' => Client::where('user_id', auth()->id())->orderBy('name')->get(),
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

        $quote->load('items.client');

        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        $this->ensureOwnership($quote);

        return view('quotes.edit', [
            'quote' => $quote,
            'clients' => Client::where('user_id', auth()->id())->orderBy('name')->get(),
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
        $pdf = Pdf::loadView('pdf.quote', compact('quote'));
        $path = 'quotes/'.$quote->quote_number.'.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        Mail::raw("Please find the attached quote {$quote->quote_number}.", function ($message) use ($quote, $pdf) {
            $message->to($quote->client->email)
                ->subject("Quote {$quote->quote_number}")
                ->attachData($pdf->output(), "{$quote->quote_number}.pdf");
        });

        return redirect()->route('quotes.show', $quote)->with('status', 'Quote sent (PDF generated).');
    }

    public function convert(Quote $quote)
    {
        $this->ensureOwnership($quote);

        $order = $this->invoiceService->convertQuoteToOrder($quote);

        return redirect()->route('orders.show', $order)->with('status', 'Quote converted to order.');
    }

    protected function payload(QuoteRequest $request): array
    {
        return array_merge($request->validated(), [
            'user_id' => $request->user()->id,
            'issue_date' => now()->toDateString(),
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
}
