@extends('layouts.app')

@section('page-title', 'AI Assistant')

@section('content')
    <div
        x-data="{
            loading: false,
            text: @js(old('text', '')),
            handleSubmit(event) {
                const value = (this.text || '').trim();

                if (!value) {
                    this.loading = false;
                    if (window.swalFire) {
                        window.swalFire({
                            icon: 'error',
                            title: 'Please enter invoice text before parsing',
                        });
                    }
                    return;
                }

                this.loading = true;
                event.target.submit();
            }
        }"
        class="relative space-y-6"
    >
        <div
            x-cloak
            x-show="loading"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 text-center shadow-2xl">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white/5">
                    <div class="flex items-end gap-1">
                        <span class="h-3 w-3 animate-bounce rounded-full bg-emerald-400 [animation-delay:-0.2s]"></span>
                        <span class="h-3 w-3 animate-bounce rounded-full bg-sky-400 [animation-delay:-0.1s]"></span>
                        <span class="h-3 w-3 animate-bounce rounded-full bg-indigo-400"></span>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-white">AI is analyzing invoice...</h3>
                <p class="mt-2 text-sm text-slate-300">Please wait while we parse the invoice text and build your draft.</p>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Invoice Parsing</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Paste invoice text and prefill a draft invoice</h2>
            <p class="mt-2 text-sm text-slate-500">
                Add OCR output, WhatsApp notes, or plain text. We try your n8n parser first and fall back to a draft invoice if it is unavailable.
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">AI parsing failed. You can still create invoice manually.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('ai-assistant.parse') }}" class="space-y-5" @submit.prevent="handleSubmit($event)">
                @csrf

                <div>
                    <label for="invoice-text" class="mb-2 block text-sm font-semibold text-slate-700">Invoice text</label>
                    <textarea
                        id="invoice-text"
                        name="text"
                        rows="12"
                        class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        placeholder="Paste vendor name, GSTIN, items, tax, totals..."
                        x-model="text"
                        :disabled="loading"
                    ></textarea>
                </div>

                <div class="space-y-2">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Example hints</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-slate-400 hover:bg-slate-50"
                            @click="text = 'Vendor: Shree Stationers Pvt Ltd | GSTIN: 27ABCDE1234F1Z5 | Invoice No: SS-8042 | Date: 2026-03-28 | Items: A4 Paper 10 ream x 260, Gel Pen Blue 50 x 12 | Subtotal: 3200 | CGST 9%: 288 | SGST 9%: 288 | Grand Total: 3776'"
                            :disabled="loading"
                        >
                            Office supplies
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-slate-400 hover:bg-slate-50"
                            @click="text = 'Vendor: Metro Electronics | GSTIN: 29AAACM9999K1Z2 | Invoice#: ME-1902 | Item: Router Service x1 4500 | Item: Cable 20m x 35 | Taxable: 5200 | IGST 18%: 936 | Total: 6136'"
                            :disabled="loading"
                        >
                            Electronics service
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-slate-400 hover:bg-slate-50"
                            @click="text = 'Vendor Name: Fresh Farm Traders; Invoice Date 2026-03-31; Bill No FFT-58; Product Tomato 40kg @ 32, Onion 30kg @ 28; Subtotal 2120; SGST 2.5% 53; CGST 2.5% 53; Amount 2226'"
                            :disabled="loading"
                        >
                            Grocery vendor
                        </button>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="inline-flex items-center gap-2 text-sm text-slate-500" x-show="loading">
                        <svg class="h-4 w-4 animate-spin text-emerald-600" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-30" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-90" d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                        </svg>
                        <span>AI is analyzing invoice...</span>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-full bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="loading"
                    >
                        Parse and Open Invoice Draft
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
