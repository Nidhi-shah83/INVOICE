<?php

use App\Http\Controllers\PaymentController;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

Route::prefix('n8n')
    ->middleware(['n8n.secret', 'throttle:n8n-user'])
    ->group(function (): void {
        Route::post('expense/tag', function (Request $request, AIService $service): JsonResponse {
            $validated = $request->validate([
                'user_id' => ['required', 'integer', 'min:1'],
                'expense_id' => ['required', 'integer', 'min:1'],
                'description' => ['required', 'string', 'max:2000'],
            ]);

            $service->tagExpense((int) $validated['expense_id'], $validated['description']);

            return response()->json([
                'status' => 'queued',
            ]);
        })->name('api.n8n.expense.tag');
    });
