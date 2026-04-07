<?php

namespace App\Http\Controllers;

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
        apply_user_mail_config((int) $quote->user_id);

        $quote->load('client', 'items');
        $token = (string) Str::uuid();
        $quote->accept_token = $token;
        $quote->status = 'sent';
        $quote->accepted_at = null;
        $quote->save();

        $pdf = Pdf::loadView('quotes.pdf', compact('quote'));
        $path = 'quotes/'.$quote->quote_number.'.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        Mail::send('emails.quote', [
            'quote' => $quote,
            'acceptUrl' => route('quotes.accept', ['token' => $token]),
        ], function ($message) use ($quote, $pdf) {
            $message->to($quote->client->email)
                ->subject("Quote {$quote->quote_number}")
                ->attachData($pdf->output(), "{$quote->quote_number}.pdf");
        });

        return redirect()->route('quotes.show', $quote)->with('status', 'Quote sent (PDF generated).');
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

        $logoPath = public_path('images/logo.png');
        $logo = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        $pdf = Pdf::loadView('quotes.pdf', compact('quote', 'logo'))
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

    public function accept(string $token)
    {
        $quote = Quote::where('accept_token', $token)->firstOrFail();

        if (in_array($quote->status, ['accepted', 'converted'])) {
            return view('quotes.accept', [
                'quote' => $quote,
                'already' => true,
            ]);
        }

        $quote->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'accept_token' => null,
        ]);

        return view('quotes.accept', [
            'quote' => $quote,
            'already' => false,
        ]);
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
            'orders' => Order::count(),
            'invoices' => Invoice::count(),
            'paid' => Invoice::where('status', 'paid')->count(),
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
}
