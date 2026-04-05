<?php

namespace App\Http\Livewire;

use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use App\Services\InvoiceService;
use App\Services\QuoteService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class QuoteForm extends Component
{
    public ?Quote $quote = null;

    public ?int $client_id = null;
    public string $issue_date = '';
    public string $validity_date = '';
    public string $status = 'draft';
    public string $discount_type = 'flat';
    public float $discount_value = 0;
    public float $round_off = 0;
    public string $currency = 'INR';
    public ?string $payment_terms = null;
    public ?string $terms_conditions = null;
    public ?string $salesperson = null;
    public ?string $reference_no = null;
    public ?string $notes = null;
    public bool $autoRound = true;
    public array $items = [];
    public Collection $clients;
    public ?Client $selectedClient = null;
    public ?string $quoteNumberPreview = null;
    public Collection $products;

    protected ?QuoteService $quoteService = null;
    protected ?InvoiceService $invoiceService = null;

    public function mount(?Quote $quote = null): void
    {
        $this->quoteService = $this->quoteService ?? app(QuoteService::class);
        $this->invoiceService = $this->invoiceService ?? app(InvoiceService::class);
        $this->quote = $quote;

        $this->clients = Client::where('user_id', auth()->id())->orderBy('name')->get();
        $this->products = Product::where('user_id', auth()->id())->orderBy('name')->get();

        $this->issue_date = $quote?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->validity_date = $quote?->validity_date?->format('Y-m-d') ?? now()->addWeek()->format('Y-m-d');
        $this->client_id = $quote?->client_id ?? null;
        $this->status = $quote?->status ?? 'draft';
        $this->discount_type = $quote?->discount_type ?? 'flat';
        $this->discount_value = (float) ($quote?->discount_value ?? 0);
        $this->round_off = (float) ($quote?->round_off ?? 0);
        $this->currency = $quote?->currency ?? 'INR';
        $this->payment_terms = $quote?->payment_terms ?? config('invoice.quote_payment_terms');
        $this->terms_conditions = $quote?->terms_conditions;
        $this->salesperson = $quote?->salesperson;
        $this->reference_no = $quote?->reference_no;
        $this->notes = $quote?->notes ?? null;
        $this->autoRound = $quote ? false : true;

        $this->selectedClient = $this->loadClient($this->client_id);
        $this->quoteNumberPreview = $quote?->quote_number ?? $this->resolveQuoteService()->generateQuoteNumber(auth()->id());

        if ($quote && $quote->items->isNotEmpty()) {
            $this->items = $quote->items->map(fn ($item) => [
                'name' => $item->name,
                'qty' => $item->qty,
                'rate' => $item->rate,
                'gst_percent' => $item->gst_percent,
            ])->all();
        }

        if (empty($this->items)) {
            $this->items[] = $this->blankItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = $this->blankItem();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if (empty($this->items)) {
            $this->items[] = $this->blankItem();
        }
    }

    public function updatedItems($value, $name): void
    {
        if (str_starts_with($name, 'items.') && str_ends_with($name, '.name')) {
            if (preg_match('/items\.([0-9]+)\.name$/', $name, $matches)) {
                $this->tryAutoFillProduct((int) $matches[1], $value);
            }
        }

        $this->autoRound = true;
    }

    public function updatedDiscountValue(): void
    {
        $this->autoRound = true;
    }

    public function updatedDiscountType(): void
    {
        $this->autoRound = true;
    }

    public function updatedRoundOff(): void
    {
        $this->autoRound = false;
    }

    public function saveDraft(): void
    {
        $quote = $this->saveQuote('draft');

        session()->flash('status', 'Quote saved as draft.');
    }

    public function sendNow(): void
    {
        $quote = $this->saveQuote('sent');

        $this->redirectRoute('quotes.show', $quote);
    }

    public function getTotalsProperty(): array
    {
        $client = $this->loadClient($this->client_id);
        $itemsForTotals = $this->normalizedItemsForTotals();
        $totals = $this->resolveInvoiceService()->calculateQuoteTotals(
            $client,
            $itemsForTotals,
            $this->discount_type,
            (float) $this->discount_value,
            $this->autoRound ? null : (float) $this->round_off,
        );

        if ($this->autoRound) {
            $this->round_off = $totals['round_off'];
        }

        return $totals;
    }

    public function render(): View
    {
        return view('livewire.quote-form', [
            'clients' => $this->clients,
            'totals' => $this->totals,
            'selectedClient' => $this->selectedClient,
            'quoteNumberPreview' => $this->quoteNumberPreview,
            'currencySymbol' => config('invoice.currency_symbol', 'â‚¹'),
            'products' => $this->products,
        ]);
    }

    protected function rules(): array
    {
        return [
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'issue_date' => ['required', 'date'],
            'validity_date' => ['required', 'date', 'after:today'],
            'status' => ['required', Rule::in(['draft', 'sent', 'accepted', 'declined', 'expired', 'converted'])],
            'discount_type' => ['required', 'in:flat,percent'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'round_off' => ['nullable', 'numeric'],
            'currency' => ['required', 'string'],
            'payment_terms' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'salesperson' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.rate' => ['required', 'numeric', 'gte:0'],
            'items.*.gst_percent' => ['required', 'numeric', 'between:0,100'],
        ];
    }

    protected function saveQuote(string $status): Quote
    {
        $this->sanitizeItems();

        $this->validate();

        $payload = [
            'user_id' => auth()->id(),
            'client_id' => $this->client_id,
            'issue_date' => $this->issue_date,
            'validity_date' => $this->validity_date,
            'status' => $status,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'round_off' => $this->round_off,
            'currency' => $this->currency,
            'payment_terms' => $this->payment_terms,
            'terms_conditions' => $this->terms_conditions,
            'salesperson' => $this->salesperson,
            'reference_no' => $this->reference_no,
            'notes' => $this->notes,
            'items' => $this->items,
        ];

        $quote = $this->resolveQuoteService()->persist($payload, $this->quote);
        $this->quote = $quote;
        $this->quoteNumberPreview = $quote->quote_number;
        $this->selectedClient = $this->loadClient($quote->client_id);

        return $quote;
    }

    protected function sanitizeItems(): void
    {
        $filtered = $this->normalizedItemsForTotals();

        $this->items = $filtered ?: [$this->blankItem()];
    }

    protected function normalizedItemsForTotals(): array
    {
        return collect($this->items)
            ->map(fn ($item) => [
                'name' => trim($item['name'] ?? ''),
                'qty' => max(0, (float) ($item['qty'] ?? 0)),
                'rate' => max(0, (float) ($item['rate'] ?? 0)),
                'gst_percent' => max(0, (float) ($item['gst_percent'] ?? 18)),
            ])
            ->filter(fn ($item) => $item['name'] !== '' || $item['qty'] > 0 || $item['rate'] > 0)
            ->values()
            ->all();
    }

    protected function blankItem(): array
    {
        return [
            'name' => '',
            'qty' => 1,
            'rate' => 0,
            'gst_percent' => 18,
        ];
    }

    protected function tryAutoFillProduct(int $index, $name): void
    {
        $name = trim((string) $name);

        if ($name === '' || ! isset($this->items[$index])) {
            return;
        }

        $product = $this->products->firstWhere('name', $name);

        if (! $product) {
            return;
        }

        $this->items[$index]['rate'] = (float) $product->rate;
        $this->items[$index]['gst_percent'] = (float) $product->gst_percent;
    }

    public function updatedClientId($value): void
    {
        $this->selectedClient = $this->loadClient($value);
    }

    protected function resolveQuoteService(): QuoteService
    {
        return $this->quoteService ??= app(QuoteService::class);
    }

    protected function resolveInvoiceService(): InvoiceService
    {
        return $this->invoiceService ??= app(InvoiceService::class);
    }

    protected function loadClient(?int $clientId): ?Client
    {
        if (! $clientId) {
            return null;
        }

        return Client::where('user_id', auth()->id())->find($clientId);
    }
}
