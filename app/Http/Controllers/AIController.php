<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AIController extends Controller
{
    public function __construct(private readonly AIService $aiService)
    {
        $this->middleware('auth:sanctum');
    }

    public function chat(): View
    {
        return view('ai.chat');
    }

    public function parse(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:20000'],
        ]);

        try {
            $prefill = $this->aiService->parseInvoiceText($validated['text']);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'text' => 'Unable to parse invoice text right now. Please try again.',
                ]);
        }

        $encoded = base64_encode(json_encode($prefill, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return redirect()->route('invoices.create', [
            'prefill' => $encoded,
        ]);
    }
}
