@extends('layouts.guest')

@section('content')
    @php
        $success = session('payment_success', []);
        $invoiceId = $success['invoice_id'] ?? null;
        $invoiceNumber = $success['invoice_number'] ?? 'N/A';
        $amount = isset($success['amount']) ? number_format((float) $success['amount'], 2) : null;
    @endphp

    <style>
        .success-wrap {
            width: min(640px, 100%);
            margin: 0 auto;
            padding: 28px 18px;
        }
        .success-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #d1fae5;
            box-shadow: 0 20px 50px rgba(22, 163, 74, 0.12);
            padding: 32px 24px;
            text-align: center;
            animation: success-enter .6s ease-out forwards;
            transform: scale(.94);
            opacity: 0;
        }
        .success-check {
            width: 84px;
            height: 84px;
            margin: 0 auto 14px;
            border-radius: 999px;
            background: #16a34a;
            color: #ffffff;
            font-size: 42px;
            font-weight: 700;
            display: grid;
            place-items: center;
            animation: pop .55s ease-out .1s both;
        }
        .success-btn {
            display: inline-block;
            margin-top: 18px;
            background: #16a34a;
            color: #ffffff;
            text-decoration: none;
            border-radius: 10px;
            padding: 11px 18px;
            font-weight: 600;
        }
        @keyframes success-enter {
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes pop {
            from { transform: scale(.6); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>

    <div class="success-wrap">
        <div class="success-card">
            <div class="success-check">&#10003;</div>
            <h2 style="margin:0;color:#065f46;font-size:30px;font-weight:800;">Payment Successful</h2>
            <p style="margin:12px 0 6px;color:#334155;">Invoice: <strong>{{ $invoiceNumber }}</strong></p>
            @if($amount !== null)
                <p style="margin:0;color:#334155;">Amount: <strong>INR {{ $amount }}</strong></p>
            @endif

            @if($invoiceId)
                <a href="{{ route('invoices.pay', $invoiceId) }}" class="success-btn">Back to Invoice</a>
            @endif
        </div>
    </div>
@endsection
