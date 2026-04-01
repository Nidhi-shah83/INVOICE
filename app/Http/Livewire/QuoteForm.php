<?php

namespace App\Http\Livewire;

use App\Models\Client;
use App\Models\Quote;
use App\Services\InvoiceService;
use App\Services\QuoteService;
use Illuminate\Contracts\View\View;
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

    protected QuoteService $quoteService;
    protected InvoiceService $invoiceService;

    public function mount(QuoteService $quoteService, InvoiceService $invoiceService, ?Quote $quote = null): void
    {
        $this->quoteService = $quoteService;
        $this->invoiceService = $invoiceService;
        $this->quote = $quote;

        $this->issue_date = $quote?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->validity_date = $quote?->validity_date?->format('Y-m-d') ?? now()->addWeek()->format('Y-m-d');
        $this->client_id = $quote?->client_id ?? null;
        $this->status = $quote?->status ?? 'draft';
        $this->notes = $quote?->notes ?? null;

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

        $this->dispatch('quote-saved', ['message' => 'Quote saved as draft.']);
    }

    public function sendNow(): void
    {
        $quote = $this->saveQuote('sent');

        $this->redirectRoute('quotes.show', $quote);
    }

    public function getTotalsProperty(): array
    {
        $client = Client::find($this->client_id);
        $subtotal = $cgst = $sgst = $igst = 0;

        foreach ($this->items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $rate = (float) ($item['rate'] ?? 0);
            $gst = (float) ($item['gst_percent'] ?? 0);

            $line = $qty * $rate;
            $subtotal += $line;

            if ($client) {
                $charges = $this->invoiceService->calculateGST($client, $line, $gst);
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
            'clients' => Client::where('user_id', auth()->id())->orderBy('name')->get(),
            'totals' => $this->totals,
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

        $quote = $this->quoteService->persist($payload, $this->quote);
        $this->quote = $quote;

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
}
