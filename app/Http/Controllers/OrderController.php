<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    protected array $allowedStatuses = [
        'pending',
        'accepted',
        'confirmed',
        'in_progress',
        'partially_billed',
        'fulfilled',
        'fully_billed',
        'cancelled',
    ];

    public function __construct(protected InvoiceService $invoiceService)
    {
        $this->middleware('auth')->except(['accept', 'reject']);
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $activeStatus = $status === 'all' ? null : $status;
        $search = trim((string) $request->query('search', ''));

        $query = Order::with('client');

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
        $pdf = Pdf::loadView('orders.pdf', compact('order'))
            ->setPaper([0, 0, 595.28, 841.89], 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled'         => true,
                'dpi'                  => 96,
                'margin_top'           => 0,
                'margin_bottom'        => 0,
                'margin_left'          => 0,
                'margin_right'         => 0,
            ]);

        if ($request->boolean('download')) {
            return $pdf->download("{$order->order_number}.pdf");
        }

        return $pdf->stream("{$order->order_number}.pdf");
    }

    public function sendPdf(Order $order)
    {
        $this->ensureOwnership($order);
        apply_user_mail_config((int) $order->user_id);

        if (! $order->acceptance_token) {
            $order->acceptance_token = (string) Str::uuid();
            $order->save();
        }

        $order->load('client', 'items', 'quote');
        $pdf = Pdf::loadView('orders.pdf', compact('order'))
            ->setPaper([0, 0, 595.28, 841.89], 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled'         => true,
                'dpi'                  => 96,
                'margin_top'           => 0,
                'margin_bottom'        => 0,
                'margin_left'          => 0,
                'margin_right'         => 0,
            ]);

        $path = 'orders/'.$order->order_number.'.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        Mail::send('emails.order', ['order' => $order], function ($message) use ($order, $pdf) {
            $message->to($order->client->email)
                ->subject("Order {$order->order_number}")
                ->attachData($pdf->output(), "{$order->order_number}.pdf");
        });

        return redirect()->route('orders.show', $order)->with('status', 'Order PDF sent.');
    }

    public function accept(int $id, string $token)
    {
        $result = DB::transaction(function () use ($id, $token): array {
            $order = Order::query()
                ->with(['client', 'user'])
                ->lockForUpdate()
                ->where('id', $id)
                ->where('acceptance_token', $token)
                ->firstOrFail();

            $existingInvoice = $order->invoices()->latest('id')->first();

            if ($order->status === 'accepted' || $existingInvoice) {
                if ($order->status !== 'accepted') {
                    $order->status = 'accepted';
                    $order->save();
                }

                return [
                    'state' => 'already_accepted',
                    'order' => $order,
                    'invoice' => $existingInvoice,
                ];
            }

            if ($order->status === 'cancelled') {
                return [
                    'state' => 'already_rejected',
                    'order' => $order,
                    'invoice' => null,
                ];
            }

            if ($this->isAcceptanceTokenExpired($order)) {
                return [
                    'state' => 'expired',
                    'order' => $order,
                    'invoice' => null,
                ];
            }

            $order->status = 'accepted';
            $order->save();

            $items = $order->items()
                ->get()
                ->map(fn ($item) => [
                    'order_item_id' => $item->id,
                    'qty' => (float) $item->qty_remaining,
                ])
                ->filter(fn ($item) => $item['qty'] > 0)
                ->values()
                ->all();

            if ($items === []) {
                return [
                    'state' => 'already_accepted',
                    'order' => $order,
                    'invoice' => null,
                ];
            }

            $invoice = $this->invoiceService->createPartialInvoice(
                $order->fresh(['client', 'items']),
                $items,
                'Auto-created after client accepted the order.'
            );

            $order->refresh();
            $order->status = 'accepted';
            $order->save();

            return [
                'state' => 'accepted',
                'order' => $order->fresh(['client', 'user']),
                'invoice' => $invoice->fresh(),
            ];
        });

        if ($result['state'] === 'expired') {
            return view('orders.accept-expired', [
                'order' => $result['order'],
            ]);
        }

        if ($result['state'] === 'already_rejected') {
            return view('orders.reject-success', [
                'order' => $result['order'],
                'already' => true,
            ]);
        }

        if ($result['state'] === 'already_accepted') {
            return view('orders.already-accepted', [
                'order' => $result['order'],
                'invoice' => $result['invoice'],
            ]);
        }

        if ($result['state'] === 'accepted' && $result['invoice'] instanceof Invoice) {
            $this->sendOrderAcceptedNotification($result['order'], $result['invoice']);
        }

        return view('orders.accept-success', [
            'order' => $result['order'],
            'invoice' => $result['invoice'],
        ]);
    }

    public function reject(int $id, string $token)
    {
        $result = DB::transaction(function () use ($id, $token): array {
            $order = Order::query()
                ->lockForUpdate()
                ->where('id', $id)
                ->where('acceptance_token', $token)
                ->firstOrFail();

            if ($order->status === 'accepted' || $order->invoices()->exists()) {
                return [
                    'state' => 'already_accepted',
                    'order' => $order,
                ];
            }

            if ($order->status === 'cancelled') {
                return [
                    'state' => 'already_rejected',
                    'order' => $order,
                ];
            }

            if ($this->isAcceptanceTokenExpired($order)) {
                return [
                    'state' => 'expired',
                    'order' => $order,
                ];
            }

            $order->status = 'cancelled';
            $order->save();

            return [
                'state' => 'rejected',
                'order' => $order,
            ];
        });

        if ($result['state'] === 'expired') {
            return view('orders.accept-expired', [
                'order' => $result['order'],
            ]);
        }

        if ($result['state'] === 'already_accepted') {
            $invoice = $result['order']->invoices()->latest('id')->first();

            return view('orders.already-accepted', [
                'order' => $result['order'],
                'invoice' => $invoice,
            ]);
        }

        return view('orders.reject-success', [
            'order' => $result['order'],
            'already' => $result['state'] === 'already_rejected',
        ]);
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

    protected function isAcceptanceTokenExpired(Order $order): bool
    {
        return (bool) $order->created_at?->lt(now()->subHours(24));
    }

    protected function sendOrderAcceptedNotification(Order $order, Invoice $invoice): void
    {
        if (! filled($order->user?->email)) {
            return;
        }

        apply_user_mail_config((int) $order->user_id);

        try {
            Mail::send('emails.order_accepted_notification', [
                'order' => $order,
                'invoice' => $invoice,
            ], function ($message) use ($order, $invoice) {
                $message->to($order->user->email)
                    ->subject("Order {$order->order_number} accepted - Invoice {$invoice->invoice_number}");
            });
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
