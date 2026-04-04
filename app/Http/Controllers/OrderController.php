<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $activeStatus = $status === 'all' ? null : $status;
        $search = trim((string) $request->query('search', ''));

        $query = Order::with('client')->where('user_id', $user->id);

        if ($activeStatus) {
            $query->where('status', $activeStatus);
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate(12);

        return view('orders.index', [
            'orders' => $orders,
            'statusTabs' => array_merge(['all'], $this->allowedStatuses),
            'activeStatus' => $activeStatus,
            'search' => $search,
        ]);
    }

    public function show(Order $order)
    {
        $this->ensureOwnership($order);

        $order->load(['client', 'items', 'invoices']);

        return view('orders.show', compact('order'));
    }

    public function downloadPdf(Request $request, Order $order)
    {
        $this->ensureOwnership($order);

        if (! $order->client) {
            abort(404);
        }

        $order->load('client', 'items', 'quote');

        $logoPath = public_path('images/logo.png');
        $logo = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        $pdf = Pdf::loadView('orders.pdf', compact('order', 'logo'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
            ]);

        if ($request->boolean('download')) {
            return $pdf->download("{$order->order_number}.pdf");
        }

        return $pdf->stream("{$order->order_number}.pdf");
    }

    public function sendPdf(Order $order)
    {
        $this->ensureOwnership($order);

        $order->load('client', 'items', 'quote');
        $logoPath = public_path('images/logo.png');
        $logo = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        $pdf = Pdf::loadView('orders.pdf', compact('order', 'logo'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
            ]);

        $path = 'orders/'.$order->order_number.'.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        Mail::send('emails.order_sent', ['order' => $order], function ($message) use ($order, $pdf) {
            $message->to($order->client->email)
                ->subject("Order {$order->order_number}")
                ->attachData($pdf->output(), "{$order->order_number}.pdf");
        });

        return back()->with('status', 'Order PDF sent.');
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
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
        ]);

        $invoice = $this->invoiceService->createPartialInvoice(
            $order,
            $data['items'],
            $data['notes'] ?? null,
            $data['due_date'] ?? null,
            $data['issue_date'] ?? null
        );

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
