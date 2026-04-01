<?php

namespace App\Http\Livewire;

use App\Models\Client;
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
    public ?string $notes = null;
    public array $items = [];
    public Collection $clients;
    public ?Client $selectedClient = null;
    public ?string $quoteNumberPreview = null;

    protected ?QuoteService $quoteService = null;
    protected ?InvoiceService $invoiceService = null;

    public function mount(?Quote $quote = null): void
    {
        $this->quoteService = $this->quoteService ?? app(QuoteService::class);
        $this->invoiceService = $this->invoiceService ?? app(InvoiceService::class);
        $this->quote = $quote;

        $this->clients = Client::where('user_id', auth()->id())->orderBy('name')->get();

        $this->issue_date = $quote?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->validity_date = $quote?->validity_date?->format('Y-m-d') ?? now()->addWeek()->format('Y-m-d');
        $this->client_id = $quote?->client_id ?? null;
        $this->status = $quote?->status ?? 'draft';
        $this->notes = $quote?->notes ?? null;

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
        $subtotal = $cgst = $sgst = $igst = 0;

        foreach ($this->items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $rate = (float) ($item['rate'] ?? 0);
            $gst = (float) ($item['gst_percent'] ?? 0);

            $line = $qty * $rate;
            $subtotal += $line;

                if ($client) {
                    $charges = $this->resolveInvoiceService()->calculateGST($client, $line, $gst);
                    $cgst += $charges['cgst'];
                    $sgst += $charges['sgst'];
                    $igst += $charges['igst'];
                }
        }

        return [
            'subtotal' => $subtotal,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total' => $subtotal + $cgst + $sgst + $igst,
        ];
    }

    public function render(): View
    {
        return view('livewire.quote-form', [
            'clients' => $this->clients,
            'totals' => $this->totals,
            'selectedClient' => $this->selectedClient,
            'quoteNumberPreview' => $this->quoteNumberPreview,
        ]);
    }

    protected function rules(): array
    {
        return [
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'validity_date' => ['required', 'date', 'after:today'],
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
        $filtered = collect($this->items)
            ->map(fn ($item) => [
                'name' => trim($item['name'] ?? ''),
                'qty' => (float) ($item['qty'] ?? 0),
                'rate' => (float) ($item['rate'] ?? 0),
                'gst_percent' => (float) ($item['gst_percent'] ?? 18),
            ])
            ->filter(fn ($item) => $item['name'] !== '' || $item['qty'] > 0 || $item['rate'] > 0)
            ->values()
            ->all();

        $this->items = $filtered ?: [$this->blankItem()];
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
