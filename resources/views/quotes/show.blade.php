@extends('layouts.app')

@section('page-title', $quote->quote_number)

@section('primary-action')
    <div class="flex flex-wrap gap-3">
        <form method="POST" action="{{ route('quotes.send', $quote) }}" class="js-send-quote" data-quote-number="{{ $quote->quote_number }}" data-client-name="{{ $quote->client->name ?? 'client' }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-slate-800 transition">
                Send PDF
            </button>
        </form>
        @if($quote->status === 'accepted')
            <form method="POST" action="{{ route('quotes.convert', $quote) }}" class="js-convert-quote" data-quote-number="{{ $quote->quote_number }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-600 transition">
                    Convert to Order
                </button>
            </form>
        @endif
        <a
            href="{{ route('quotes.download', $quote) }}"
            class="inline-flex items-center gap-2 rounded-full border border-slate-900 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-900 hover:text-white transition js-download-quote"
            data-quote-number="{{ $quote->quote_number }}"
        >
            Download PDF
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        @php
            $currencySymbol = config('invoice.currency_symbol', '₹');
        @endphp
        @php
            $businessDefaults = [
                'business_name' => config('invoice.business_name', 'Invoice Pro'),
                'address' => config('company.address', '123 Corporate Blvd, City, State ZIP'),
                'gstin' => config('invoice.gstin', 'XXX0000XXXX'),
                'email' => config('invoice.email', 'contact@example.com'),
                'phone' => config('invoice.phone', ''),
            ];
            $businessInfo = array_filter(array_replace($businessDefaults, $businessSettings ?? []), fn ($value) => $value !== null && $value !== '');
        @endphp
        @include('quotes.partials.card', compact('currencySymbol', 'businessInfo'))
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sendForms = document.querySelectorAll('.js-send-quote');
            sendForms.forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    const quoteNumber = form.dataset.quoteNumber ? form.dataset.quoteNumber : 'quote';
                    const clientName = form.dataset.clientName ? form.dataset.clientName : 'client';
                    Swal.fire({
                        title: `Send ${quoteNumber}?`,
                        text: `Email ${quoteNumber} to ${clientName}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Send quote',
                        cancelButtonText: 'Cancel',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            const convertForms = document.querySelectorAll('.js-convert-quote');
            convertForms.forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    const quoteNumber = form.dataset.quoteNumber ? form.dataset.quoteNumber : 'quote';
                    Swal.fire({
                        title: `Convert ${quoteNumber}?`,
                        text: 'This will turn the quote into an order.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Convert',
                        cancelButtonText: 'Cancel',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            const downloadLinks = document.querySelectorAll('.js-download-quote');
            downloadLinks.forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    const quoteNumber = link.dataset.quoteNumber ? link.dataset.quoteNumber : 'quote';
                    Swal.fire({
                        title: `Download ${quoteNumber}?`,
                        text: 'The PDF will download after you confirm.',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Download',
                        cancelButtonText: 'Cancel',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = link.href;
                        }
                    });
                });
            });
        });
    </script>
@endsection
