@extends('layouts.app')

@section('page-title', 'Settings')

@section('content')
    <div x-data="settingsForm()" class="space-y-6">
        <div x-show="toast" x-transition class="fixed right-6 top-6 z-50 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700 shadow-lg">
            Settings saved successfully.
        </div>

        <div class="flex flex-col gap-2 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold text-slate-900">Settings</h1>
            <p class="text-sm text-slate-500">Manage branding, invoices, email, AI controls and payment details for your account.</p>
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

        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6" @submit.prevent="submit($event)">
            @csrf
            @method('PATCH')

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Business Profile</h2>
                        <p class="text-sm text-slate-500">Your company branding and GST information.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-slate-600">Business</span>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
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
                        <label class="block text-sm font-semibold text-slate-700" for="state">State</label>
                        <input id="state" name="state" type="text" value="{{ old('state', $settings['state']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700" for="logo">Logo</label>
                        <div class="mt-2 flex items-center gap-4">
                            <div class="h-24 w-24 overflow-hidden rounded-3xl border border-slate-200 bg-slate-50">
                                <template x-if="previewUrl">
                                    <img :src="previewUrl" alt="Logo preview" class="h-full w-full object-contain" />
                                </template>
                                <template x-if="!previewUrl">
                                    <div class="flex h-full items-center justify-center text-xs text-slate-400">No logo</div>
                                </template>
                            </div>
                            <div class="space-y-2">
                                <input id="logo" name="logo" type="file" accept="image/*" @change="previewImage($event)" class="text-sm text-slate-600" />
                                <p class="text-xs text-slate-500">Upload PNG/JPG. Stored in storage/app/public/logos/.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Invoice Settings</h2>
                        <p class="text-sm text-slate-500">Configure invoice numbering, payment terms and currency.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-slate-600">Invoice</span>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="invoice_prefix">Invoice Prefix</label>
                        <input id="invoice_prefix" name="invoice_prefix" type="text" value="{{ old('invoice_prefix', $settings['invoice_prefix']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="default_due_days">Default Due Days</label>
                        <input id="default_due_days" name="default_due_days" type="number" min="0" value="{{ old('default_due_days', $settings['default_due_days']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="default_gst_rate">Default GST Rate</label>
                        <input id="default_gst_rate" name="default_gst_rate" type="number" step="0.01" min="0" max="100" value="{{ old('default_gst_rate', $settings['default_gst_rate']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="currency">Currency</label>
                        <input id="currency" name="currency" type="text" value="{{ old('currency', $settings['currency']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm uppercase shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="currency_symbol">Currency Symbol</label>
                        <input id="currency_symbol" name="currency_symbol" type="text" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Email Settings</h2>
                        <p class="text-sm text-slate-500">Set the email sender, signature, and branding for customer notifications.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-slate-600">Email</span>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="email_from_name">From Name</label>
                        <input id="email_from_name" name="email_from_name" type="text" value="{{ old('email_from_name', $settings['email_from_name']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="email_from_address">From Email</label>
                        <input id="email_from_address" name="email_from_address" type="email" value="{{ old('email_from_address', $settings['email_from_address']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    </div>

                    <div class="lg:col-span-3">
                        <label class="block text-sm font-semibold text-slate-700" for="email_signature">Email Signature</label>
                        <textarea id="email_signature" name="email_signature" rows="3" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200">{{ old('email_signature', $settings['email_signature']) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">AI Settings</h2>
                        <p class="text-sm text-slate-500">Control AI collection behavior and reminder workflow.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-slate-600">AI</span>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-4">
                    <div class="lg:col-span-2 flex items-center gap-3">
                        <input id="enable_ai_calls" name="enable_ai_calls" type="checkbox" @checked(old('enable_ai_calls', $settings['enable_ai_calls'])) class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                        <label class="text-sm font-semibold text-slate-700" for="enable_ai_calls">Enable AI Calls</label>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="ai_reminder_delay">Reminder Delay (days)</label>
                        <input id="ai_reminder_delay" name="ai_reminder_delay" type="number" min="0" value="{{ old('ai_reminder_delay', $settings['ai_reminder_delay']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="ai_max_follow_up_attempts">Max Follow-up Attempts</label>
                        <input id="ai_max_follow_up_attempts" name="ai_max_follow_up_attempts" type="number" min="0" value="{{ old('ai_max_follow_up_attempts', $settings['ai_max_follow_up_attempts']) }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="ai_call_tone">Call Tone</label>
                        <select id="ai_call_tone" name="ai_call_tone" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200">
                            <option value="formal" @selected(old('ai_call_tone', $settings['ai_call_tone']) === 'formal')>Formal</option>
                            <option value="friendly" @selected(old('ai_call_tone', $settings['ai_call_tone']) === 'friendly')>Friendly</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="ai_language">Language</label>
                        <select id="ai_language" name="ai_language" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200">
                            <option value="English" @selected(old('ai_language', $settings['ai_language']) === 'English')>English</option>
                            <option value="Hindi" @selected(old('ai_language', $settings['ai_language']) === 'Hindi')>Hindi</option>
                            <option value="Hinglish" @selected(old('ai_language', $settings['ai_language']) === 'Hinglish')>Hinglish</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Payment Settings</h2>
                        <p class="text-sm text-slate-500">Customer payment instructions for invoices and receipts.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-slate-600">Payment</span>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-4">
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
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700">Save Settings</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function settingsForm() {
                return {
                    toast: {{ session('status') ? 'true' : 'false' }},
                    previewUrl: @js($logoUrl),

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
                };
            }
        </script>
    @endpush
@endsection
