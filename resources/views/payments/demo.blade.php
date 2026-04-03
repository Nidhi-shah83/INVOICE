@extends('layouts.guest')

@section('content')
    <style>
        .demo-wrap {
            width: min(920px, 100%);
            margin: 0 auto;
            padding: 22px;
        }
        .demo-card {
            background: #ffffff;
            border: 1px solid #dbe3ee;
            border-radius: 18px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }
        .demo-head {
            padding: 24px;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfeff 100%);
            border-bottom: 1px solid #dbe3ee;
        }
        .demo-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            padding: 24px;
        }
        .demo-method {
            border: 1px solid #dbe3ee;
            border-radius: 14px;
            padding: 16px;
            background: #f8fafc;
        }
        .demo-method h3 {
            margin: 0 0 12px;
            font-size: 15px;
            color: #0f172a;
        }
        .demo-qr {
            width: 168px;
            height: 168px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            display: grid;
            place-items: center;
        }
        .demo-input,
        .demo-select {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            margin-bottom: 10px;
            background: #ffffff;
        }
        .demo-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .demo-foot {
            padding: 0 24px 24px;
        }
        .demo-button {
            border: 0;
            width: 100%;
            border-radius: 12px;
            background: #16a34a;
            color: #ffffff;
            padding: 14px 18px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }
        .demo-note {
            margin-top: 10px;
            font-size: 12px;
            color: #64748b;
            text-align: center;
        }
        @media (max-width: 760px) {
            .demo-grid {
                grid-template-columns: 1fr;
                padding: 18px;
            }
            .demo-head {
                padding: 18px;
            }
            .demo-foot {
                padding: 0 18px 18px;
            }
        }
    </style>

    <div class="demo-wrap">
        <div class="demo-card">
            <div class="demo-head">
                <p style="margin:0;color:#475569;font-size:13px;">Invoice Payment</p>
                <h2 style="margin:6px 0 8px;color:#0f172a;">Invoice {{ $invoice->invoice_number }}</h2>
                <p style="margin:0;color:#0f172a;font-weight:600;">Amount: {{ $invoice->formatted_grand_total }}</p>
            </div>

            <form method="POST" action="{{ route('invoices.pay.process', $invoice->id) }}">
                @csrf

                <div class="demo-grid">
                    <div class="demo-method">
                        <h3>UPI QR</h3>
                        <div class="demo-qr" aria-label="UPI QR Code">
                            <svg width="132" height="132" viewBox="0 0 132 132" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true">
                                <rect width="132" height="132" fill="#ffffff"/>
                                <rect x="8" y="8" width="28" height="28" fill="#111827"/>
                                <rect x="96" y="8" width="28" height="28" fill="#111827"/>
                                <rect x="8" y="96" width="28" height="28" fill="#111827"/>
                                <rect x="48" y="8" width="8" height="8" fill="#111827"/>
                                <rect x="64" y="16" width="8" height="8" fill="#111827"/>
                                <rect x="72" y="32" width="8" height="8" fill="#111827"/>
                                <rect x="56" y="40" width="8" height="8" fill="#111827"/>
                                <rect x="48" y="56" width="8" height="8" fill="#111827"/>
                                <rect x="64" y="64" width="8" height="8" fill="#111827"/>
                                <rect x="80" y="56" width="8" height="8" fill="#111827"/>
                                <rect x="40" y="80" width="8" height="8" fill="#111827"/>
                                <rect x="56" y="88" width="8" height="8" fill="#111827"/>
                                <rect x="72" y="80" width="8" height="8" fill="#111827"/>
                                <rect x="88" y="88" width="8" height="8" fill="#111827"/>
                                <rect x="104" y="72" width="8" height="8" fill="#111827"/>
                                <rect x="88" y="104" width="8" height="8" fill="#111827"/>
                            </svg>
                        </div>
                        <p style="margin:10px 0 0;color:#64748b;font-size:13px;">Scan to pay in demo mode.</p>
                    </div>

                    <div class="demo-method">
                        <h3>Card</h3>
                        <input class="demo-input" type="text" placeholder="Card Number" maxlength="19">
                        <input class="demo-input" type="text" placeholder="Cardholder Name">
                        <div class="demo-row">
                            <input class="demo-input" type="text" placeholder="MM/YY" maxlength="5">
                            <input class="demo-input" type="password" placeholder="CVV" maxlength="3">
                        </div>
                    </div>

                    <div class="demo-method" style="grid-column: 1 / -1;">
                        <h3>Netbanking</h3>
                        <select class="demo-select">
                            <option value="">Select Bank</option>
                            <option>State Bank of India</option>
                            <option>HDFC Bank</option>
                            <option>ICICI Bank</option>
                            <option>Axis Bank</option>
                            <option>Kotak Mahindra Bank</option>
                        </select>
                    </div>
                </div>

                <div class="demo-foot">
                    <button type="submit" class="demo-button">Pay Now</button>
                    <p class="demo-note">Demo payment only. No real transaction is processed.</p>
                </div>
            </form>
        </div>
    </div>
@endsection
