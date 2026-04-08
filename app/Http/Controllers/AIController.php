<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        ], [
            'text.required' => 'Please enter invoice text before parsing.',
        ]);

        $text = trim((string) $validated['text']);

        if ($text === '') {
            return back()
                ->withInput()
                ->withErrors([
                    'text' => 'Please enter invoice text before parsing.',
                ]);
        }

        try {
            $prefill = $this->aiService->parseInvoiceText($text);
        } catch (Throwable $exception) {
            Log::warning('AI invoice parsing failed. Falling back to manual draft mode.', [
                'user_id' => $request->user()?->id,
                'error' => $exception->getMessage(),
            ]);

            $request->session()->put('invoice_draft', [
                'notes' => $text,
                'raw_text' => $text,
            ]);

            return redirect()
                ->route('invoices.create')
                ->with('status', 'AI parsing unavailable. Draft created with your input.');
        }

        $request->session()->put('invoice_draft', is_array($prefill) ? $prefill : []);

        return redirect()
            ->route('invoices.create')
            ->with('status', 'AI parsed successfully. Review your draft invoice.');
    }
}
