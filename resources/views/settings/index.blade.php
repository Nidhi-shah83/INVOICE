@extends('layouts.app')

@section('page-title', 'Settings')

@section('content')
    @php
        $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white';
        $helperClass = 'mt-1 text-xs text-slate-500 dark:text-slate-400';
        $completion = (int) ($completion ?? 0);
        $sweetalert = session('sweetalert');
    @endphp

    <div class="max-w-7xl mx-auto px-6 py-6 overflow-visible space-y-6">
        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 shadow-sm">
                <p class="font-semibold">Please correct the highlighted fields.</p>
                <ul class="mt-2 list-disc space-y-1 pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400 dark:text-slate-500">Workspace</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">Settings</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Central configuration for branding, invoice defaults, email delivery, and payment details.</p>
                </div>
                <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 dark:bg-slate-800 dark:text-slate-300">SaaS Ready</div>
            </div>
        </div>

        <div class="rounded-2xl bg-blue-50 p-4 shadow-sm ring-1 ring-blue-100 dark:bg-blue-950/40 dark:ring-blue-900/60">
            <div class="mb-2 flex items-center justify-between">
                <span class="text-sm font-medium text-slate-900 dark:text-white">Profile Completion</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $completion }}%</span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                <div class="h-2 rounded-full bg-blue-600 transition-all" style="width: {{ $completion }}%"></div>
            </div>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Complete your settings to unlock full features.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('settings.update.business') }}" enctype="multipart/form-data" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
                @csrf
                @method('PATCH')

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Business Profile</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Company identity, address, geography, and branding assets.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 dark:bg-slate-800 dark:text-slate-300">Business</span>
                </div>

                <hr class="my-4 border-slate-200 dark:border-slate-800">

                <div class="grid gap-6 xl:grid-cols-2">
                    <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/30">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Business Info</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Core brand details used across your workspace.</p>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label for="business_name" class="text-sm font-medium text-slate-700 dark:text-slate-200">Business Name</label>
                                <input id="business_name" name="business_name" type="text" value="{{ old('business_name', $settings['business_name']) }}" placeholder="e.g. Invoice Pro" class="{{ $inputClass }} mt-1">
                                <p class="{{ $helperClass }}">This will appear on invoices and emails.</p>
                            </div>

                            <div>
                                <label for="email" class="text-sm font-medium text-slate-700 dark:text-slate-200">Business Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $settings['email']) }}" placeholder="e.g. support@yourcompany.com" class="{{ $inputClass }} mt-1">
                                <p class="{{ $helperClass }}">Used in outgoing invoice and quote communications.</p>
                            </div>

                            <div>
                                <label for="phone" class="text-sm font-medium text-slate-700 dark:text-slate-200">Phone</label>
                                <input id="phone" name="phone" type="text" value="{{ old('phone', $settings['phone']) }}" placeholder="e.g. +91 9876543210" class="{{ $inputClass }} mt-1">
                                <p class="{{ $helperClass }}">Shown on invoice PDFs for client contact.</p>
                            </div>

                            <div>
                                <label for="logo" class="text-sm font-medium text-slate-700 dark:text-slate-200">Logo</label>
                                <div class="mt-2 flex items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                                    <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full bg-slate-100 ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                                        @if ($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="Current logo" class="h-full w-full object-contain p-1">
                                        @else
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Logo</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <input id="logo" name="logo" type="file" accept="image/*" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-700 dark:text-slate-300">
                                        <p class="{{ $helperClass }}">Choose a logo file. It will be used in the app and documents.</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="favicon" class="text-sm font-medium text-slate-700 dark:text-slate-200">Favicon</label>
                                <div class="mt-2 rounded-2xl border border-dashed border-slate-300 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                                    <input id="favicon" name="favicon" type="file" accept="image/*" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-700 dark:text-slate-300">
                                    <p class="{{ $helperClass }}">Choose a small icon file for browser tabs.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/30">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Tax Info</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tax and regional details used on documents.</p>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label for="gstin" class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                    <span title="Your official GST registration number">GSTIN</span>
                                </label>
                                <input id="gstin" name="gstin" type="text" value="{{ old('gstin', $settings['gstin']) }}" placeholder="e.g. 27ABCDE1234F1Z5" class="{{ $inputClass }} mt-1">
                                <p class="{{ $helperClass }}">Enter your registered GST number.</p>
                            </div>

                            <div>
                                <label for="address" class="text-sm font-medium text-slate-700 dark:text-slate-200">Address</label>
                                <textarea id="address" name="address" rows="3" placeholder="e.g. 2nd Floor, MG Road, Mumbai" class="{{ $inputClass }} mt-1">{{ old('address', $settings['address']) }}</textarea>
                                <p class="{{ $helperClass }}">Printed on quotes, orders, and invoices.</p>
                            </div>

                            <input type="hidden" name="business_country" value="India">

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="business_state" class="text-sm font-medium text-slate-700 dark:text-slate-200">State</label>
                                    <select id="business_state" name="business_state" class="{{ $inputClass }} mt-1">
                                        <option value="">Select state (e.g. Maharashtra)</option>
                                        @foreach ($states as $code => $name)
                                            <option value="{{ $code }}" @selected(old('business_state', $settings['business_state'] ?: $settings['state']) === $code)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="{{ $helperClass }}">Choose the registered business state.</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Currency</label>
                                    <input type="text" value="INR" readonly class="{{ $inputClass }} mt-1 bg-slate-100 uppercase dark:bg-gray-700">
                                    <p class="{{ $helperClass }}">Default billing currency for this workspace.</p>
                                </div>
                            </div>

                            <div>
                                <label for="terms_conditions" class="text-sm font-medium text-slate-700 dark:text-slate-200">Terms &amp; Conditions</label>
                                <textarea id="terms_conditions" name="terms_conditions" rows="3" placeholder="e.g. Payment due within 15 days from invoice date." class="{{ $inputClass }} mt-1">{{ old('terms_conditions', $settings['terms_conditions']) }}</textarea>
                                <p class="{{ $helperClass }}">This note appears in documents shared with customers.</p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="mt-6 flex justify-end border-t border-slate-200 pt-5 dark:border-slate-800">
                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-white shadow hover:bg-blue-700">Save Business Profile</button>
                </div>
            </form>

            <form method="POST" action="{{ route('settings.update.invoice') }}" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                @csrf
                @method('PATCH')

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Invoice Settings</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Invoice numbering and billing defaults.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 dark:bg-slate-800 dark:text-slate-300">Invoice</span>
                </div>

                <hr class="my-4 border-slate-200 dark:border-slate-800">

                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Numbering System</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Control how your quote, order, and invoice numbers are generated.</p>

                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <label for="company_prefix" class="text-sm font-medium text-slate-700 dark:text-slate-200">Company Prefix</label>
                        <input id="company_prefix" name="company_prefix" type="text" value="{{ old('company_prefix', $settings['company_prefix']) }}" placeholder="e.g. KD" class="{{ $inputClass }} mt-1 uppercase">
                        <p class="{{ $helperClass }}">Used for numbering (e.g. KD-INV-2026-001).</p>
                    </div>

                    <div>
                        <label for="quote_prefix" class="text-sm font-medium text-slate-700 dark:text-slate-200">Quote Prefix</label>
                        <input id="quote_prefix" name="quote_prefix" type="text" value="{{ old('quote_prefix', $settings['quote_prefix']) }}" placeholder="e.g. QT" class="{{ $inputClass }} mt-1 uppercase">
                        <p class="{{ $helperClass }}">Used for numbering (e.g. KD-QT-2026-001).</p>
                    </div>

                    <div>
                        <label for="order_prefix" class="text-sm font-medium text-slate-700 dark:text-slate-200">Order Prefix</label>
                        <input id="order_prefix" name="order_prefix" type="text" value="{{ old('order_prefix', $settings['order_prefix']) }}" placeholder="e.g. ORD" class="{{ $inputClass }} mt-1 uppercase">
                        <p class="{{ $helperClass }}">Used for numbering (e.g. KD-ORD-2026-001).</p>
                    </div>

                    <div>
                        <label for="invoice_prefix" class="text-sm font-medium text-slate-700 dark:text-slate-200">Invoice Prefix</label>
                        <input id="invoice_prefix" name="invoice_prefix" type="text" value="{{ old('invoice_prefix', $settings['invoice_prefix']) }}" placeholder="e.g. INV" class="{{ $inputClass }} mt-1 uppercase">
                        <p class="{{ $helperClass }}">Used for numbering (e.g. KD-INV-2026-001).</p>
                    </div>

                    <div>
                        <label for="default_due_days" class="text-sm font-medium text-slate-700 dark:text-slate-200">Default Due Days</label>
                        <input id="default_due_days" name="default_due_days" type="number" min="0" max="365" placeholder="e.g. 15" value="{{ old('default_due_days', $settings['default_due_days']) }}" class="{{ $inputClass }} mt-1">
                        <p class="{{ $helperClass }}">Auto-sets invoice due date after issue date.</p>
                    </div>

                    <div>
                        <label for="default_gst_rate" class="text-sm font-medium text-slate-700 dark:text-slate-200">Default GST Rate (%)</label>
                        <input id="default_gst_rate" name="default_gst_rate" type="number" step="0.01" min="0" max="100" placeholder="e.g. 18" value="{{ old('default_gst_rate', $settings['default_gst_rate']) }}" class="{{ $inputClass }} mt-1">
                        <p class="{{ $helperClass }}">Default tax percentage while creating new lines.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end border-t border-slate-200 pt-5 dark:border-slate-800">
                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-white shadow hover:bg-blue-700">Save Invoice Settings</button>
                </div>
            </form>

            <form method="POST" action="{{ route('settings.update.email') }}" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                @csrf
                @method('PATCH')

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Email Settings</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">DB-driven SMTP mail configuration for this account.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 dark:bg-slate-800 dark:text-slate-300">Email</span>
                </div>

                <hr class="my-4 border-slate-200 dark:border-slate-800">

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <label for="mail_mailer" class="text-sm font-medium text-slate-700 dark:text-slate-200">Mailer</label>
                        <select id="mail_mailer" name="mail_mailer" class="{{ $inputClass }} mt-1">
                            @foreach (['smtp' => 'SMTP', 'mailgun' => 'Mailgun', 'ses' => 'SES', 'postmark' => 'Postmark', 'log' => 'Log'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('mail_mailer', $settings['mail_mailer']) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="{{ $helperClass }}">Select how emails are delivered from your account.</p>
                    </div>

                    <div>
                        <label for="mail_host" class="text-sm font-medium text-slate-700 dark:text-slate-200">Host</label>
                        <input id="mail_host" name="mail_host" type="text" value="{{ old('mail_host', $settings['mail_host']) }}" placeholder="e.g. smtp.gmail.com" class="{{ $inputClass }} mt-1">
                        <p class="{{ $helperClass }}">SMTP host from your email provider.</p>
                    </div>

                    <div>
                        <label for="mail_port" class="text-sm font-medium text-slate-700 dark:text-slate-200">Port</label>
                        <input id="mail_port" name="mail_port" type="number" min="1" max="65535" value="{{ old('mail_port', $settings['mail_port']) }}" placeholder="e.g. 587" class="{{ $inputClass }} mt-1">
                        <p class="{{ $helperClass }}">Common ports: 587 (TLS), 465 (SSL).</p>
                    </div>

                    <div>
                        <label for="mail_username" class="text-sm font-medium text-slate-700 dark:text-slate-200">Username</label>
                        <input id="mail_username" name="mail_username" type="text" value="{{ old('mail_username', $settings['mail_username']) }}" placeholder="e.g. support@yourcompany.com" class="{{ $inputClass }} mt-1">
                        <p class="{{ $helperClass }}">Login username for your SMTP account.</p>
                    </div>

                    <div>
                        <label for="mail_password" class="text-sm font-medium text-slate-700 dark:text-slate-200">Password</label>
                        <input id="mail_password" name="mail_password" type="password" placeholder="e.g. app password (leave blank to keep existing)" class="{{ $inputClass }} mt-1">
                        <p class="{{ $helperClass }}">For Gmail/Outlook, use an app password if required.</p>
                    </div>

                    <div>
                        <label for="mail_from_address" class="text-sm font-medium text-slate-700 dark:text-slate-200">From Address</label>
                        <input id="mail_from_address" name="mail_from_address" type="email" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" placeholder="e.g. billing@yourcompany.com" class="{{ $inputClass }} mt-1">
                        <p class="{{ $helperClass }}">Displayed as sender email to your customers.</p>
                    </div>

                    <div>
                        <label for="mail_from_name" class="text-sm font-medium text-slate-700 dark:text-slate-200">From Name</label>
                        <input id="mail_from_name" name="mail_from_name" type="text" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" placeholder="e.g. Invoice Pro Billing" class="{{ $inputClass }} mt-1">
                        <p class="{{ $helperClass }}">Name shown in recipients' inbox.</p>
                    </div>

                    <div class="md:col-span-2 xl:col-span-3">
                        <label for="test_email_recipient" class="text-sm font-medium text-slate-700 dark:text-slate-200">Test Email Recipient</label>
                        <input id="test_email_recipient" name="test_email_recipient" type="email" value="{{ old('test_email_recipient', auth()->user()->email) }}" placeholder="e.g. support@yourcompany.com" class="{{ $inputClass }} mt-1">
                        <p class="{{ $helperClass }}">Test delivery target for "Save + Send Test Email".</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-800">
                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-white shadow hover:bg-blue-700">Save Email Settings</button>
                    <button type="submit" name="send_test_email" value="1" class="rounded-lg bg-slate-900 px-5 py-2 text-white shadow hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600">Save + Send Test Email</button>
                </div>
            </form>

            <form method="POST" action="{{ route('settings.update.payment') }}" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2 dark:border-gray-800 dark:bg-gray-900">
                @csrf
                @method('PATCH')

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Payment Settings</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Payment instructions used in the invoice payment section.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 dark:bg-slate-800 dark:text-slate-300">Payment</span>
                </div>

                <hr class="my-4 border-slate-200 dark:border-slate-800">

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="bank_name" class="text-sm font-medium text-slate-700 dark:text-slate-200">Bank Name</label>
                        <input id="bank_name" name="bank_name" type="text" value="{{ old('bank_name', $settings['bank_name']) }}" placeholder="e.g. HDFC Bank" class="{{ $inputClass }} mt-1">
                        <p class="{{ $helperClass }}">Shown to clients as payment instruction.</p>
                    </div>

                    <div>
                        <label for="account_number" class="text-sm font-medium text-slate-700 dark:text-slate-200">Account Number</label>
                        <input id="account_number" name="account_number" type="text" value="{{ old('account_number', $settings['account_number']) }}" placeholder="e.g. 50200012345678" class="{{ $inputClass }} mt-1">
                        <p class="{{ $helperClass }}">Primary account for receiving payments.</p>
                    </div>

                    <div>
                        <label for="ifsc_code" class="text-sm font-medium text-slate-700 dark:text-slate-200">IFSC Code</label>
                        <input id="ifsc_code" name="ifsc_code" type="text" value="{{ old('ifsc_code', $settings['ifsc_code']) }}" placeholder="e.g. HDFC0001234" class="{{ $inputClass }} mt-1 uppercase">
                        <p class="{{ $helperClass }}">Required for NEFT/IMPS/RTGS transfers.</p>
                    </div>

                    <div>
                        <label for="upi_id" class="text-sm font-medium text-slate-700 dark:text-slate-200">UPI ID</label>
                        <input id="upi_id" name="upi_id" type="text" value="{{ old('upi_id', $settings['upi_id']) }}" placeholder="e.g. yourcompany@upi" class="{{ $inputClass }} mt-1">
                        <p class="{{ $helperClass }}">Optional quick-pay method displayed on invoice.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end border-t border-slate-200 pt-5 dark:border-slate-800">
                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-white shadow hover:bg-blue-700">Save Payment Settings</button>
                </div>
            </form>
        </div>
    </div>

    @if ($sweetalert)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.Swal) {
                    Swal.fire(@json($sweetalert));
                }
            });
        </script>
    @endif

    @if ($completion < 70)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Complete Your Profile',
                        text: 'Some settings are missing. Please complete them.',
                        confirmButtonText: 'Continue',
                    });
                }
            });
        </script>
    @endif
@endsection
