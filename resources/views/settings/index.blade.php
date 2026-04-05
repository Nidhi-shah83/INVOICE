@extends('layouts.app')

@section('page-title', 'Settings')

@section('content')
    <div x-data="settingsForm()" @mounted="updateCurrency()" class="space-y-6">
        <div x-show="toast" x-transition class="fixed right-6 top-6 z-50 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700 shadow-lg">
            Settings saved successfully.
        </div>

        <div class="flex flex-col gap-2 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold text-slate-900">Settings</h1>
            <p class="text-sm text-slate-500">Manage branding, invoices, email delivery, and payment details for your account.</p>

            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="flex items-center justify-between text-sm text-slate-600 mb-2">
                    <span>Setup Progress</span>
                    <span>{{ $progress['percentage'] }}% completed</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2">
                    <div class="bg-emerald-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progress['percentage'] }}%"></div>
                </div>
                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full {{ $progress['sections']['business'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" x-show="{{ $progress['sections']['business'] ? 'true' : 'false' }}">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Business
                    </span>
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full {{ $progress['sections']['invoice'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" x-show="{{ $progress['sections']['invoice'] ? 'true' : 'false' }}">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Invoice
                    </span>
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full {{ $progress['sections']['email'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" x-show="{{ $progress['sections']['email'] ? 'true' : 'false' }}">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Email
                    </span>
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full {{ $progress['sections']['payment'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" x-show="{{ $progress['sections']['payment'] ? 'true' : 'false' }}">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Payment
                    </span>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="font-semibold">Please fix the highlighted fields.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Business Profile Section -->
        <form method="POST" action="{{ route('settings.update.business') }}" enctype="multipart/form-data" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')

            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Business Profile</h2>
                    <p class="text-sm text-slate-500">Your company branding and GST information.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-slate-600">Business</span>
            </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="business_name">Business Name</label>
                        <input id="business_name" name="business_name" type="text" value="{{ old('business_name', $settings['business_name']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="gstin">GSTIN</label>
                    <input id="gstin" name="gstin" type="text" value="{{ old('gstin', $settings['gstin']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm uppercase shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                </div>

                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700" for="address">Address</label>
                        <textarea id="address" name="address" rows="3" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200">{{ old('address', $settings['address']) }}</textarea>
                    </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="country_display">Country</label>
                    <input
                        id="country_display"
                        type="text"
                        value="India"
                        readonly
                        class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 shadow-sm"
                    />
                    <input type="hidden" name="country" value="India" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="state">State / Province</label>
                    <select id="state" name="state" x-model="state" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200">
                        <option value="">Select a state</option>
                        @php($selectedState = trim((string) old('state', $settings['state'] ?? '')))
                        @foreach($states as $stateCode => $stateName)
                            <option value="{{ $stateName }}" @selected(trim((string) $stateName) === $selectedState)>{{ $stateName }}</option>
                        @endforeach
                    </select>
                    @error('state')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700" for="logo">Logo</label>
                    <div class="mt-2 grid gap-4 sm:grid-cols-[auto_1fr]">
                        <div class="grid gap-3">
                            <input id="logo" name="logo" type="file" accept="image/*" @change="previewImage($event)" class="text-sm text-slate-600" />
                        </div>

                        <div class="lg:col-span-2">
                            <div class="flex items-center gap-3">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700" for="favicon">Favicon</label>
                                    <input id="favicon" name="favicon" type="file" accept="image/*" @change="previewFavicon($event)" class="mt-1 text-sm text-slate-600" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700" for="email">Business Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $settings['email']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700" for="terms_conditions">Terms &amp; Conditions</label>
                    <textarea id="terms_conditions" name="terms_conditions" rows="3" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200">{{ old('terms_conditions', $settings['terms_conditions']) }}</textarea>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700">Save Business</button>
            </div>
        </form>

        <!-- Invoice Settings Section -->
        <form method="POST" action="{{ route('settings.update.invoice') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')

            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Invoice Settings</h2>
                    <p class="text-sm text-slate-500">Configure invoice numbering, payment terms and currency.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-slate-600">Invoice</span>
            </div>

            <div class="grid gap-4 lg:grid-cols-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="invoice_prefix">Invoice Prefix</label>
                    <input id="invoice_prefix" name="invoice_prefix" type="text" value="{{ old('invoice_prefix', $settings['invoice_prefix']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    <p class="mt-1 text-xs text-slate-500">Example: INV-2026-</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="default_due_days">Default Due Days</label>
                    <input id="default_due_days" name="default_due_days" type="number" min="0" value="{{ old('default_due_days', $settings['default_due_days']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    <p class="mt-1 text-xs text-slate-500">Number of days before invoice becomes overdue</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="default_gst_rate">Default GST Rate</label>
                    <input id="default_gst_rate" name="default_gst_rate" type="number" step="0.01" min="0" max="100" value="{{ old('default_gst_rate', $settings['default_gst_rate']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    <p class="mt-1 text-xs text-slate-500">Default GST % applied on invoices</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="currency">Currency</label>
                    <input id="currency" name="currency" x-model="invoiceCurrency" type="text" value="{{ old('currency', $settings['currency']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm uppercase shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    <p class="mt-1 text-xs text-slate-500">INR, USD etc.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="currency_symbol">Currency Symbol</label>
                    <input id="currency_symbol" name="currency_symbol" x-model="invoiceCurrencySymbol" type="text" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '₹') }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    <p class="mt-1 text-xs text-slate-500">Symbol for selected currency</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700">Save Invoice</button>
            </div>
        </form>

        <!-- Email Settings Section -->
        <form method="POST" action="{{ route('settings.update.email') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')

            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Email Settings</h2>
                    <p class="text-sm text-slate-500">Configure SMTP settings for sending emails.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-slate-600">Email</span>
            </div>

            <div class="mb-4 rounded-2xl bg-slate-50 p-4 text-sm">
                <p class="font-semibold text-slate-700 mb-2">Example SMTP Configuration:</p>
                <pre class="text-xs text-slate-600">MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525</pre>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="mail_mailer">Mailer</label>
                    <select id="mail_mailer" name="mail_mailer" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200">
                        <option value="smtp" @selected(old('mail_mailer', $settings['mail_mailer']) === 'smtp')>SMTP</option>
                        <option value="mailgun" @selected(old('mail_mailer', $settings['mail_mailer']) === 'mailgun')>Mailgun</option>
                        <option value="ses" @selected(old('mail_mailer', $settings['mail_mailer']) === 'ses')>SES</option>
                        <option value="postmark" @selected(old('mail_mailer', $settings['mail_mailer']) === 'postmark')>Postmark</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="mail_scheme">Scheme</label>
                    <select id="mail_scheme" name="mail_scheme" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200">
                        <option value="null" @selected(in_array(strtolower((string) old('mail_scheme', $settings['mail_scheme'] ?? 'null')), ['', 'null'], true))>None (null)</option>
                        <option value="tls" @selected(strtolower((string) old('mail_scheme', $settings['mail_scheme'] ?? '')) === 'tls')>TLS</option>
                        <option value="ssl" @selected(strtolower((string) old('mail_scheme', $settings['mail_scheme'] ?? '')) === 'ssl')>SSL</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="mail_host">Host</label>
                    <input id="mail_host" name="mail_host" type="text" placeholder="smtp.example.com" value="{{ old('mail_host', $settings['mail_host']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="mail_port">Port</label>
                    <input id="mail_port" name="mail_port" type="number" placeholder="587" value="{{ old('mail_port', $settings['mail_port']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="mail_username">Username</label>
                    <input id="mail_username" name="mail_username" type="text" placeholder="user@example.com" value="{{ old('mail_username', $settings['mail_username']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="mail_password">Password</label>
                    <input id="mail_password" name="mail_password" type="password" placeholder="SMTP password" value="{{ old('mail_password', $settings['mail_password']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="mail_from_address">From Email</label>
                    <input id="mail_from_address" name="mail_from_address" type="email" placeholder="hello@example.com" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="mail_from_name">From Name</label>
                    <input id="mail_from_name" name="mail_from_name" type="text" placeholder="Your company name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-sm font-semibold text-slate-700" for="test_email_recipient">Test Email Recipient</label>
                    <input id="test_email_recipient" name="test_email_recipient" type="email" placeholder="Send test email to" value="{{ old('test_email_recipient', auth()->user()->email) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    <p class="mt-1 text-xs text-slate-500">Send a verification email to this address when you click Test Email.</p>
                    @error('test_email_recipient')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700">Save Email</button>
                <button type="submit" name="send_test_email" value="1" class="inline-flex items-center justify-center rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-200 transition hover:bg-slate-800">Send Test Email</button>
            </div>
        </form>

        <!-- Payment Settings Section -->
        <form method="POST" action="{{ route('settings.update.payment') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')

            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Payment Settings</h2>
                    <p class="text-sm text-slate-500">Customer payment instructions for invoices and receipts.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-slate-600">Payment</span>
            </div>

            <div class="grid gap-4 lg:grid-cols-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="upi_id">UPI ID</label>
                    <input id="upi_id" name="upi_id" type="text" value="{{ old('upi_id', $settings['upi_id']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="bank_name">Bank Name</label>
                    <input id="bank_name" name="bank_name" type="text" value="{{ old('bank_name', $settings['bank_name']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="account_number">Account Number</label>
                    <input id="account_number" name="account_number" type="text" value="{{ old('account_number', $settings['account_number']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="ifsc_code">IFSC Code</label>
                    <input id="ifsc_code" name="ifsc_code" type="text" value="{{ old('ifsc_code', $settings['ifsc_code']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm uppercase shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700">Save Payment</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function settingsForm() {
                return {
                    toast: {{ session('status') ? 'true' : 'false' }},
                    previewUrl: @js($logoUrl),
                    faviconPreviewUrl: @js($faviconUrl),
                    country: 'India',
                    state: @js(old('state', $settings['state'] ?? '')),
                    invoiceCurrency: @js(old('currency', $settings['currency'] ?? 'INR')),
                    invoiceCurrencySymbol: @js(old('currency_symbol', $settings['currency_symbol'] ?? '₹')),
                    
                    // Country to currency mapping
                    currencyMap: @js(
                        array_map(fn($data) => [
                            'currency' => $data['currency'],
                            'symbol' => $data['symbol'],
                        ], $locations)
                    ),

                    init() {
                        this.updateCurrency();
                    },

                    updateCurrency() {
                        const currencyData = this.currencyMap[this.country];
                        if (currencyData) {
                            this.invoiceCurrency = currencyData.currency;
                            this.invoiceCurrencySymbol = currencyData.symbol;
                        }
                    },

                    submit(event) {
                        this.toast = false;
                        event.target.submit();
                    },

                    previewImage(event) {
                        const file = event.target.files?.[0];
                        if (!file) {
                            this.previewUrl = @js($logoUrl);
                            return;
                        }
                        this.previewUrl = URL.createObjectURL(file);
                    },

                    previewFavicon(event) {
                        const file = event.target.files?.[0];
                        if (!file) {
                            this.faviconPreviewUrl = @js($faviconUrl);
                            return;
                        }
                        this.faviconPreviewUrl = URL.createObjectURL(file);
                    },
                };
            }
        </script>
    @endpush
@endsection
