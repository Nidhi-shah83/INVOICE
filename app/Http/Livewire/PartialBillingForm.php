<?php

namespace App\Http\Livewire;

use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PartialBillingForm extends Component
{
    public Order $order;

    public array $lines = [];

    public ?string $notes = null;

    protected ?InvoiceService $invoiceService = null;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['items', 'client']);
        $this->invoiceService = app(InvoiceService::class);
        $this->lines = $this->order->items->map(fn ($item) => [
            'order_item_id' => $item->id,
            'name' => $item->name,
            'rate' => $item->rate,
            'qty_remaining' => $item->qty_remaining,
            'qty' => 0,
        ])->all();
    }

    public function getTotalsProperty(): array
    {
        $total = 0;

        foreach ($this->lines as $line) {
            $total += ($line['qty'] ?? 0) * ($line['rate'] ?? 0);
        }

        return ['total' => $total];
    }

    public function submit(): void
    {
        $items = collect($this->lines)
            ->filter(fn ($line) => ($line['qty'] ?? 0) > 0)
            ->map(fn ($line) => [
                'order_item_id' => $line['order_item_id'],
                'qty' => (float) $line['qty'],
            ])
            ->values()
            ->all();

        if ($items === []) {
            $this->addError('lines', 'Please add a quantity for at least one item.');
            return;
        }

        $invoice = $this->resolveInvoiceService()->createPartialInvoice($this->order, $items, $this->notes);

        session()->flash('status', "Invoice {$invoice->invoice_number} created.");
        $this->notes = null;

        $this->redirectRoute('orders.show', $this->order);
    }

    protected function resolveInvoiceService(): InvoiceService
    {
        return $this->invoiceService ??= app(InvoiceService::class);
    }

    public function render(): View
    {
        return view('livewire.partial-billing-form', [
            'totals' => $this->totals,
        ]);
    }
}
