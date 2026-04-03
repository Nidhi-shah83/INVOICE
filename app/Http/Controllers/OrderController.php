<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    protected array $allowedStatuses = [
        'confirmed',
        'in_progress',
        'partially_billed',
        'fulfilled',
        'fully_billed',
        'cancelled',
    ];

    public function __construct(protected InvoiceService $invoiceService)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');

        $query = Order::with('client')->where('user_id', $user->id);

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->orderByDesc('created_at')->paginate(12);

        return view('orders.index', [
            'orders' => $orders,
            'statusTabs' => array_merge(['all'], $this->allowedStatuses),
            'activeStatus' => $status,
        ]);
    }

    public function show(Order $order)
    {
        $this->ensureOwnership($order);

        $order->load(['client', 'items', 'invoices']);

        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->ensureOwnership($order);

        $data = $request->validate([
            'status' => ['required', Rule::in($this->allowedStatuses)],
        ]);

        $order->update(['status' => $data['status']]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Order status updated.',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status,
                ],
            ]);
        }

        return back()->with('status', 'Order status updated.');
    }

    public function createInvoice(Request $request, Order $order)
    {
        $this->ensureOwnership($order);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => [
                'required',
                Rule::exists('order_items', 'id')->where(fn ($query) => $query->where('order_id', $order->id)),
            ],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $invoice = $this->invoiceService->createPartialInvoice($order, $data['items'], $data['notes'] ?? null);

        return redirect()->route('orders.show', $order)->with('status', "Invoice {$invoice->invoice_number} created.");
    }

    public function destroy(Order $order)
    {
        $this->ensureOwnership($order);

        $order->delete();

        return redirect()->route('orders.index')->with('status', 'Order deleted.');
    }

    protected function ensureOwnership(Order $order): void
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
