@extends('layouts.app')

@section('page-title', 'Settings')

@section('content')
    <div
        x-data="settingsPage({
            logoUrl: @js($logoUrl),
            faviconUrl: @js($faviconUrl),
        })"
        class="space-y-6"
    >
        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <p class="font-semibold">Please correct the highlighted fields.</p>
                <ul class="mt-2 list-disc pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-semibold text-slate-900">Settings</h1>
            <p class="mt-1 text-sm text-slate-500">Central configuration for branding, invoice defaults, email delivery, and payment details.</p>
        </div>

        <form method="POST" action="{{ route('settings.update.business') }}" enctype="multipart/form-data" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Business Profile</h2>
                    <p class="text-sm text-slate-500">Company identity, address, geography, and branding assets.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">Business</span>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                <div>
                    <label for="business_name" class="text-sm font-medium text-slate-700">Business Name</label>
                    <input id="business_name" name="business_name" type="text" value="{{ old('business_name', $settings['business_name']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="gstin" class="text-sm font-medium text-slate-700">GSTIN</label>
                    <input id="gstin" name="gstin" type="text" value="{{ old('gstin', $settings['gstin']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div class="lg:col-span-2">
                    <label for="address" class="text-sm font-medium text-slate-700">Address</label>
                    <textarea id="address" name="address" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">{{ old('address', $settings['address']) }}</textarea>
                </div>

                <input type="hidden" name="business_country" value="India">

                <div>
                    <label for="business_state" class="text-sm font-medium text-slate-700">State</label>
                    <select id="business_state" name="business_state" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        @foreach ($states as $code => $name)
                            <option value="{{ $code }}" @selected(old('business_state', $settings['business_state'] ?: $settings['state']) === $code)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Currency</label>
                    <input type="text" value="INR" readonly class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-sm uppercase">
                </div>

                <div>
                    <label for="email" class="text-sm font-medium text-slate-700">Business Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $settings['email']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="phone" class="text-sm font-medium text-slate-700">Phone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $settings['phone']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div class="lg:col-span-2">
                    <label for="terms_conditions" class="text-sm font-medium text-slate-700">Terms &amp; Conditions</label>
                    <textarea id="terms_conditions" name="terms_conditions" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">{{ old('terms_conditions', $settings['terms_conditions']) }}</textarea>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-sm font-medium text-slate-700">Logo</p>
                    <label class="mt-2 flex h-44 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50">
                        <input type="file" name="logo" accept="image/*" class="hidden" @change="previewFile($event, 'logo')">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" alt="Logo preview" class="h-full w-full object-contain p-4">
                        </template>
                        <template x-if="!logoPreview">
                            <div class="text-center text-sm text-slate-500">
                                <p class="font-medium text-slate-700">Upload Logo</p>
                                <p class="mt-1">PNG, JPG, SVG or WEBP</p>
                            </div>
                        </template>
                    </label>
                    <p class="mt-2 text-xs text-slate-500">Shows in navbar/sidebar and invoice views.</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-slate-700">Favicon</p>
                    <label class="mt-2 flex h-44 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50">
                        <input type="file" name="favicon" accept="image/*" class="hidden" @change="previewFile($event, 'favicon')">
                        <template x-if="faviconPreview">
                            <img :src="faviconPreview" alt="Favicon preview" class="h-20 w-20 rounded-xl border border-slate-200 bg-white p-2">
                        </template>
                        <template x-if="!faviconPreview">
                            <div class="text-center text-sm text-slate-500">
                                <p class="font-medium text-slate-700">Upload Favicon</p>
                                <p class="mt-1">Square image recommended</p>
                            </div>
                        </template>
                    </label>
                    <p class="mt-2 text-xs text-slate-500">Used in browser tabs and app title bar.</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Save Business Profile</button>
            </div>
        </form>

        <form method="POST" action="{{ route('settings.update.invoice') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Invoice Settings</h2>
                    <p class="text-sm text-slate-500">Invoice numbering and billing defaults.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">Invoice</span>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label for="company_prefix" class="text-sm font-medium text-slate-700">Company Prefix</label>
                    <input id="company_prefix" name="company_prefix" type="text" value="{{ old('company_prefix', $settings['company_prefix']) }}" placeholder="KD" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm uppercase focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="quote_prefix" class="text-sm font-medium text-slate-700">Quote Prefix</label>
                    <input id="quote_prefix" name="quote_prefix" type="text" value="{{ old('quote_prefix', $settings['quote_prefix']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm uppercase focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="order_prefix" class="text-sm font-medium text-slate-700">Order Prefix</label>
                    <input id="order_prefix" name="order_prefix" type="text" value="{{ old('order_prefix', $settings['order_prefix']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm uppercase focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="invoice_prefix" class="text-sm font-medium text-slate-700">Invoice Prefix</label>
                    <input id="invoice_prefix" name="invoice_prefix" type="text" value="{{ old('invoice_prefix', $settings['invoice_prefix']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm uppercase focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="default_due_days" class="text-sm font-medium text-slate-700">Default Due Days</label>
                    <input id="default_due_days" name="default_due_days" type="number" min="0" max="365" value="{{ old('default_due_days', $settings['default_due_days']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="default_gst_rate" class="text-sm font-medium text-slate-700">Default GST Rate (%)</label>
                    <input id="default_gst_rate" name="default_gst_rate" type="number" step="0.01" min="0" max="100" value="{{ old('default_gst_rate', $settings['default_gst_rate']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Save Invoice Settings</button>
            </div>
        </form>

        <form method="POST" action="{{ route('settings.update.email') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Email Settings</h2>
                    <p class="text-sm text-slate-500">DB-driven SMTP mail configuration for this account.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">Email</span>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div>
                    <label for="mail_mailer" class="text-sm font-medium text-slate-700">Mailer</label>
                    <select id="mail_mailer" name="mail_mailer" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        @foreach (['smtp' => 'SMTP', 'mailgun' => 'Mailgun', 'ses' => 'SES', 'postmark' => 'Postmark', 'log' => 'Log'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('mail_mailer', $settings['mail_mailer']) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="mail_host" class="text-sm font-medium text-slate-700">Host</label>
                    <input id="mail_host" name="mail_host" type="text" value="{{ old('mail_host', $settings['mail_host']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="mail_port" class="text-sm font-medium text-slate-700">Port</label>
                    <input id="mail_port" name="mail_port" type="number" min="1" max="65535" value="{{ old('mail_port', $settings['mail_port']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="mail_username" class="text-sm font-medium text-slate-700">Username</label>
                    <input id="mail_username" name="mail_username" type="text" value="{{ old('mail_username', $settings['mail_username']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="mail_password" class="text-sm font-medium text-slate-700">Password</label>
                    <input id="mail_password" name="mail_password" type="password" placeholder="Leave blank to keep existing password" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="mail_from_address" class="text-sm font-medium text-slate-700">From Address</label>
                    <input id="mail_from_address" name="mail_from_address" type="email" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="mail_from_name" class="text-sm font-medium text-slate-700">From Name</label>
                    <input id="mail_from_name" name="mail_from_name" type="text" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div class="md:col-span-2">
                    <label for="test_email_recipient" class="text-sm font-medium text-slate-700">Test Email Recipient</label>
                    <input id="test_email_recipient" name="test_email_recipient" type="email" value="{{ old('test_email_recipient', auth()->user()->email) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <button type="submit" class="rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Save Email Settings</button>
                <button type="submit" name="send_test_email" value="1" class="rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Save + Send Test Email</button>
            </div>
        </form>

        <form method="POST" action="{{ route('settings.update.payment') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Payment Settings</h2>
                    <p class="text-sm text-slate-500">Payment instructions used in the invoice payment section.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">Payment</span>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="bank_name" class="text-sm font-medium text-slate-700">Bank Name</label>
                    <input id="bank_name" name="bank_name" type="text" value="{{ old('bank_name', $settings['bank_name']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="account_number" class="text-sm font-medium text-slate-700">Account Number</label>
                    <input id="account_number" name="account_number" type="text" value="{{ old('account_number', $settings['account_number']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="ifsc_code" class="text-sm font-medium text-slate-700">IFSC Code</label>
                    <input id="ifsc_code" name="ifsc_code" type="text" value="{{ old('ifsc_code', $settings['ifsc_code']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm uppercase focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="upi_id" class="text-sm font-medium text-slate-700">UPI ID</label>
                    <input id="upi_id" name="upi_id" type="text" value="{{ old('upi_id', $settings['upi_id']) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Save Payment Settings</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function settingsPage({
                logoUrl,
                faviconUrl,
            }) {
                return {
                    logoPreview: logoUrl || null,
                    faviconPreview: faviconUrl || null,

                    previewFile(event, type) {
                        const file = event.target?.files?.[0];
                        if (!file) {
                            return;
                        }

                        const url = URL.createObjectURL(file);

                        if (type === 'logo') {
                            this.logoPreview = url;
                            return;
                        }

                        this.faviconPreview = url;
                    },
                };
            }
        </script>
    @endpush
@endsection
