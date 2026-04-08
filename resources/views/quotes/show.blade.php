@extends('layouts.app')

@section('page-title', $quote->quote_number)

@section('primary-action')
    <div class="flex flex-wrap gap-3">
        <form method="POST" action="{{ route('quotes.send', $quote->id) }}" class="js-send-quote" data-swal-confirm data-swal-title="Send {{ $quote->quote_number }}?" data-swal-text="Email this quote to {{ $quote->client->name ?? 'client' }}?" data-swal-confirm-button="Send quote" data-swal-cancel-button="Cancel" data-swal-icon="question">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-slate-800 transition">
                Send Quote
            </button>
        </form>
        @if($quote->status === 'accepted')
            <form method="POST" action="{{ route('quotes.convert', $quote) }}" class="js-convert-quote" data-swal-confirm data-swal-title="Convert {{ $quote->quote_number }}?" data-swal-text="This will turn the quote into an order." data-swal-confirm-button="Convert" data-swal-cancel-button="Cancel" data-swal-icon="warning" data-swal-confirm-color="#10b981">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-600 transition">
                    Convert to Order
                </button>
            </form>
        @endif
        <a
            href="{{ route('quotes.download', $quote) }}"
            class="inline-flex items-center gap-2 rounded-full border border-slate-900 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-900 hover:text-white transition js-download-quote"
            data-swal-link-confirm
            data-swal-title="Download {{ $quote->quote_number }}?"
            data-swal-text="The PDF will download after you confirm."
            data-swal-confirm-button="Download"
            data-swal-cancel-button="Cancel"
            data-swal-icon="info"
        >
            Download PDF
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        @php
            $currencySymbol = setting('currency_symbol', 'Rs');
        @endphp
        @php
            $businessDefaults = [
                'business_name' => setting('business_name', 'Invoice Pro'),
                'address' => setting('address', 'Address not set'),
                'gstin' => setting('gstin', ''),
                'email' => setting('email', ''),
                'phone' => setting('phone', ''),
            ];
            $businessInfo = array_filter(array_replace($businessDefaults, $businessSettings ?? []), fn ($value) => $value !== null && $value !== '');
        @endphp
        @include('quotes.partials.card', compact('currencySymbol', 'businessInfo'))
    </div>
@endsection

