<?php

namespace App\Http\Livewire;

use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
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
            'ordered_qty' => (float) $item->qty,
            'billed_qty' => (float) $item->qty_billed,
            'available_qty' => (float) $item->qty_remaining,
            'rate' => $item->rate,
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

    public function billRemaining(): void
    {
        foreach ($this->lines as $index => $line) {
            $this->lines[$index]['qty'] = (float) ($line['available_qty'] ?? 0);
        }
    }

    public function submit(): void
    {
        $items = $this->selectedItems();

        if ($items === []) {
            $this->addError('lines', 'Please add a quantity for at least one item.');
            $this->dispatchBillingAlert(
                'error',
                'No quantities selected',
                'Please add a quantity for at least one item before creating the invoice.'
            );
            return;
        }

        if ($message = $this->validateQuantities($items)) {
            $this->addError('lines', $message);
            $this->dispatchBillingAlert('error', 'Quantity exceeds remaining', $message);
            return;
        }

        try {
            $invoice = $this->resolveInvoiceService()->createPartialInvoice($this->order, $items, $this->notes);

            session()->flash('status', "Invoice {$invoice->invoice_number} created successfully.");
            $this->notes = null;
            $this->order->refresh();
        } catch (InvalidArgumentException $exception) {
            $message = $this->friendlyExceptionMessage($exception->getMessage());
            $this->addError('lines', $message);
            $this->dispatchBillingAlert('error', 'Billing blocked', $message);
            return;
        } catch (\Throwable $exception) {
            report($exception);

            $message = 'We could not create the invoice right now. Please try again.';
            $this->addError('lines', $message);
            $this->dispatchBillingAlert('error', 'Billing failed', $message);
            return;
        }

        $this->redirectRoute('orders.show', $this->order);
    }

    protected function resolveInvoiceService(): InvoiceService
    {
        return $this->invoiceService ??= app(InvoiceService::class);
    }

    protected function selectedItems(): array
    {
        return collect($this->lines)
            ->filter(fn ($line) => (float) ($line['qty'] ?? 0) > 0)
            ->map(fn ($line) => [
                'order_item_id' => (int) $line['order_item_id'],
                'qty' => (float) $line['qty'],
                'available_qty' => (float) ($line['available_qty'] ?? 0),
                'name' => (string) ($line['name'] ?? ''),
            ])
            ->values()
            ->all();
    }

    protected function validateQuantities(array $items): ?string
    {
        foreach ($items as $item) {
            $remaining = max(0, (float) ($item['available_qty'] ?? 0));
            $qty = max(0, (float) ($item['qty'] ?? 0));

            if ($qty > $remaining) {
                $name = trim((string) ($item['name'] ?? 'Item'));

                return "You can only bill remaining quantity ({$this->formatQuantity($remaining)}) for {$name}.";
            }
        }

        return null;
    }

    protected function friendlyExceptionMessage(string $message): string
    {
        if (str_contains($message, 'Cannot bill more than remaining quantity')) {
            return 'You can only bill the remaining quantity for that item.';
        }

        return 'Billing could not be completed. Please check the quantities and try again.';
    }

    protected function dispatchBillingAlert(string $icon, string $title, string $text): void
    {
        $this->dispatch('swal',
            icon: $icon,
            title: $title,
            text: $text
        );
    }

    protected function formatQuantity(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    public function render(): View
    {
        return view('livewire.partial-billing-form', [
            'totals' => $this->totals,
        ]);
    }
}
