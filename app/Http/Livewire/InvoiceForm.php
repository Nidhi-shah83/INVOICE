<?php

namespace App\Http\Livewire;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class InvoiceForm extends Component
{
    public ?Invoice $invoice = null;
    public ?Client $client = null;
    public ?Order $order = null;
    public array $lines = [];
    public string $issue_date;
    public string $due_date;
    public ?string $notes = null;

    protected InvoiceService $service;

    public function mount(?Invoice $invoice = null, ?string $prefill = null, InvoiceService $service = null)
    {
        $this->service = $service ?? app(InvoiceService::class);
        $this->invoice = $invoice;

        $this->issue_date = $invoice?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->due_date = $invoice?->due_date?->format('Y-m-d') ?? now()->addDays((int) config('invoice.default_due_days'))->format('Y-m-d');

        if ($invoice) {
            $this->client = $invoice->client;
            $this->order = $invoice->order;
            $this->notes = $invoice->notes;
            $this->lines = $invoice->items->map(fn ($item) => [
                'name' => $item->name,
                'qty' => $item->qty_billed,
                'rate' => $item->rate,
            ])->all();
        } elseif ($prefill) {
            $payload = json_decode(base64_decode($prefill), true);
            $this->client = Client::find($payload['client_id'] ?? Auth::id());
            $this->order = Order::find($payload['order_id'] ?? null);
            $this->lines = $payload['items'] ?? [];
        }

        if (empty($this->lines)) {
            $this->lines[] = ['name' => '', 'qty' => 0, 'rate' => 0];
        }
    }

    public function updatedLines(): void
    {
    }

    public function render(): View
    {
        return view('livewire.invoice-form', [
            'clients' => Client::where('user_id', auth()->id())->get(),
        ]);
    }
}
