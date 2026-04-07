@extends('layouts.guest')

@section('content')
    @php
        $currencySymbol = setting('currency_symbol', 'Rs');
        $upiId = setting('upi_id');
        $bankName = setting('bank_name');
        $accountNumber = setting('account_number');
        $ifscCode = setting('ifsc_code');
    @endphp
    <style>
        :root {
            --card-bg: #ffffff;
            --card-border: rgba(15, 23, 42, 0.08);
            --text-muted: #475569;
            --primary: #0f766e;
            --primary-dark: #0b5c52;
        }

        .payment-shell {
            max-width: 720px;
            margin: 48px auto;
            padding: 0 16px;
        }

        .payment-card {
            background: var(--card-bg);
            border-radius: 24px;
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.15);
            border: 1px solid var(--card-border);
            overflow: hidden;
            min-width: 100%;
        }

        .payment-header {
            padding: 32px 28px 12px;
            background: linear-gradient(135deg, #f0fdfa 0%, #ecfeff 100%);
            border-bottom: 1px solid #e2e8f0;
        }

        .payment-header p {
            margin: 0;
            color: var(--text-muted);
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .payment-header h1 {
            margin: 12px 0 2px;
            font-size: 26px;
            color: #0f172a;
            font-weight: 700;
        }

        .payment-amount {
            margin: 0;
            color: #0f172a;
            font-size: 22px;
            font-weight: 600;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            padding: 32px 28px;
        }

        .payment-block {
            background: #f8fafc;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .payment-block h3 {
            margin: 0;
            font-size: 17px;
            font-weight: 600;
            color: #0f172a;
        }

        .qr-wrapper {
            width: 176px;
            height: 176px;
            border-radius: 16px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            display: grid;
            place-items: center;
            margin: 0 auto;
        }

        .payment-input {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 15px;
            background: #ffffff;
            color: #0f172a;
        }

        .payment-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .payment-select {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 14px 16px;
            background: #ffffff;
            font-size: 15px;
            color: #0f172a;
        }

        .payment-footer {
            padding: 0 28px 32px;
        }

        .pay-button {
            width: 100%;
            border: none;
            border-radius: 14px;
            background: var(--primary);
            padding: 16px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .pay-button:hover {
            background: var(--primary-dark);
        }

        .payment-note {
            margin: 14px 0 0;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }

        .netbanking-block {
            grid-column: 1 / -1;
        }

        @media (max-width: 640px) {
            .payment-header {
                padding: 24px 20px 12px;
            }

            .payment-grid {
                grid-template-columns: 1fr;
                padding: 28px 20px;
            }

            .payment-row {
                grid-template-columns: 1fr;
            }

            .payment-footer {
                padding: 0 20px 28px;
            }
        }
    </style>

    <div class="payment-shell">
        <div class="payment-card">
            <div class="payment-header">
                <p>Invoice Payment</p>
                <h1>Invoice {{ $invoice->invoice_number }}</h1>
                <p class="payment-amount">Amount: {{ $currencySymbol }}{{ number_format($invoice->amount_due, 2) }}</p>
            </div>

            <form method="POST" action="{{ route('invoices.pay.process', $invoice->id) }}">
                @csrf

                <div class="payment-grid">
                    <div class="payment-block">
                        <h3>UPI QR</h3>
                        <div class="qr-wrapper" aria-label="UPI QR Code">
                            <svg width="140" height="140" viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true">
                                <rect width="140" height="140" fill="#ffffff"/>
                                <rect x="10" y="10" width="32" height="32" fill="#111827"/>
                                <rect x="98" y="10" width="32" height="32" fill="#111827"/>
                                <rect x="10" y="98" width="32" height="32" fill="#111827"/>
                                <rect x="52" y="10" width="10" height="10" fill="#111827"/>
                                <rect x="70" y="18" width="10" height="10" fill="#111827"/>
                                <rect x="80" y="34" width="10" height="10" fill="#111827"/>
                                <rect x="62" y="42" width="10" height="10" fill="#111827"/>
                                <rect x="52" y="58" width="10" height="10" fill="#111827"/>
                                <rect x="70" y="66" width="10" height="10" fill="#111827"/>
                                <rect x="88" y="58" width="10" height="10" fill="#111827"/>
                                <rect x="44" y="82" width="10" height="10" fill="#111827"/>
                                <rect x="62" y="90" width="10" height="10" fill="#111827"/>
                                <rect x="80" y="82" width="10" height="10" fill="#111827"/>
                                <rect x="98" y="90" width="10" height="10" fill="#111827"/>
                                <rect x="116" y="74" width="10" height="10" fill="#111827"/>
                                <rect x="98" y="106" width="10" height="10" fill="#111827"/>
                            </svg>
                        </div>
                        <p class="payment-note" style="text-align: left; margin-top: 8px;">
                            @if($upiId)
                                Scan with your UPI app or pay to <strong>{{ $upiId }}</strong>.
                            @else
                                Scan with your UPI app to complete the payment.
                            @endif
                        </p>
                    </div>

                    <div class="payment-block">
                        <h3>Card Payment</h3>
                        <input class="payment-input" type="text" placeholder="Card Number" maxlength="19">
                        <input class="payment-input" type="text" placeholder="Cardholder Name">
                        <div class="payment-row">
                            <input class="payment-input" type="text" placeholder="MM/YY" maxlength="5">
                            <input class="payment-input" type="password" placeholder="CVV" maxlength="3">
                        </div>
                    </div>

                    <div class="payment-block netbanking-block">
                        <h3>Netbanking</h3>
                        <select class="payment-select">
                            <option value="">Select Bank</option>
                            @if($bankName)
                                <option>{{ $bankName }}</option>
                            @endif
                            <option>State Bank of India</option>
                            <option>HDFC Bank</option>
                            <option>ICICI Bank</option>
                            <option>Axis Bank</option>
                            <option>Kotak Mahindra Bank</option>
                        </select>
                        <p class="payment-note" style="margin-top: 4px;">
                            @if($bankName || $accountNumber || $ifscCode)
                                Beneficiary:
                                {{ $bankName ?: 'Bank not set' }}
                                @if($accountNumber)
                                    | A/C: {{ $accountNumber }}
                                @endif
                                @if($ifscCode)
                                    | IFSC: {{ $ifscCode }}
                                @endif
                            @else
                                We redirect to your chosen bank for secure processing.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="payment-footer">
                    <button type="submit" class="pay-button">Pay Now</button>
                    <p class="payment-note">Demo payment only. No real transaction is processed.</p>
                </div>
            </form>
        </div>
    </div>
@endsection
