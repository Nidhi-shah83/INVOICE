@php
    $clientType = old('client_type', optional($client)->client_type ?? 'individual');
    $stateValue = old('state', optional($client)->state ?? '');
    $placeOfSupplyValue = old('place_of_supply', optional($client)->place_of_supply ?? '');
@endphp

<div class="space-y-6" data-client-form>
    <div class="grid gap-6 xl:grid-cols-2">
        <section class="space-y-4 rounded-2xl border border-slate-200 bg-white/60 p-5 shadow-sm">
        <header class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Basic info</p>
                <h2 class="text-lg font-semibold text-slate-900">Client profile</h2>
            </div>
        </header>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700" for="name">Name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $client->name ?? '') }}"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    required
                >
                @error('name')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="client_type">Client type</label>
                    <select
                        id="client_type"
                        name="client_type"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        required
                    >
                        <option value="individual" @selected(old('client_type', $client->client_type ?? 'individual') === 'individual')>Individual</option>
                        <option value="business" @selected(old('client_type', $client->client_type ?? 'individual') === 'business')>Business</option>
                    </select>
                    @error('client_type')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div
                    data-business-only
                    class="space-y-1"
                    style="display: none;"
                >
                    <label class="block text-sm font-semibold text-slate-700" for="company_name">Company name</label>
                    <input
                        id="company_name"
                        name="company_name"
                        type="text"
                        value="{{ old('company_name', $client->company_name ?? '') }}"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                    @error('company_name')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </section>

    <section class="space-y-4 rounded-2xl border border-slate-200 bg-white/60 p-5 shadow-sm">
        <header class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Contact info</p>
                <h2 class="text-lg font-semibold text-slate-900">How we reach them</h2>
            </div>
        </header>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-slate-700" for="email">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $client->email ?? '') }}"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
                @error('email')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700" for="phone">Phone</label>
                <input
                    id="phone"
                    name="phone"
                    type="text"
                    value="{{ old('phone', $client->phone ?? '') }}"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    required
                >
                @error('phone')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700" for="alternate_phone">Alternate phone</label>
                <input
                    id="alternate_phone"
                    name="alternate_phone"
                    type="text"
                    value="{{ old('alternate_phone', $client->alternate_phone ?? '') }}"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
                @error('alternate_phone')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="space-y-4 rounded-2xl border border-slate-200 bg-white/60 p-5 shadow-sm">
        <header class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">GST info</p>
                <h2 class="text-lg font-semibold text-slate-900">Compliance</h2>
            </div>
        </header>

        <div class="grid gap-4 md:grid-cols-2">
            <div
                data-business-only
                class="space-y-1"
                style="display: none;"
            >
                <label class="block text-sm font-semibold text-slate-700" for="gstin">GSTIN</label>
                <input
                    id="gstin"
                    name="gstin"
                    type="text"
                    value="{{ old('gstin', $client->gstin ?? '') }}"
                    maxlength="15"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm uppercase tracking-[0.2em] focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
                @error('gstin')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700" for="state">State</label>
                <select
                    id="state"
                    name="state"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    required
                >
                    <option value="" disabled>Select state</option>
                    @foreach ($states as $state)
                        <option value="{{ $state }}" @selected(old('state', $client->state ?? '') === $state)>{{ $state }}</option>
                    @endforeach
                </select>
                @error('state')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700" for="place_of_supply">Place of supply</label>
                <input
                    id="place_of_supply"
                    name="place_of_supply"
                    type="text"
                    value="{{ old('place_of_supply', $client->place_of_supply ?? '') }}"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    required
                >
                @error('place_of_supply')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

    </section>

    <section class="space-y-4 rounded-2xl border border-slate-200 bg-white/60 p-5 shadow-sm">
        <header class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Address</p>
                <h2 class="text-lg font-semibold text-slate-900">Where they operate</h2>
            </div>
        </header>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700" for="address">Address</label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    required
                >{{ old('address', $client->address ?? '') }}</textarea>
                @error('address')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="city">City</label>
                    <input
                        id="city"
                        name="city"
                        type="text"
                        value="{{ old('city', $client->city ?? '') }}"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        required
                    >
                    @error('city')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="pincode">Pincode</label>
                    <input
                        id="pincode"
                        name="pincode"
                        type="text"
                        value="{{ old('pincode', $client->pincode ?? '') }}"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        required
                    >
                    @error('pincode')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="country">Country</label>
                    <input
                        id="country"
                        name="country"
                        type="text"
                        value="{{ old('country', $client->country ?? 'India') }}"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        required
                    >
                    @error('country')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        </section>
    </div>

    <section class="space-y-2 rounded-2xl border border-slate-200 bg-white/60 p-5 shadow-sm">
        <header>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Notes</p>
            <h2 class="text-lg font-semibold text-slate-900">Internal details</h2>
        </header>
        <div>
            <textarea
                id="notes"
                name="notes"
                rows="4"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >{{ old('notes', $client->notes ?? '') }}</textarea>
            @error('notes')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-client-form]').forEach(form => {
            const clientTypeSelect = form.querySelector('#client_type');
            const businessBlocks = form.querySelectorAll('[data-business-only]');
            const stateSelect = form.querySelector('#state');
            const placeInput = form.querySelector('#place_of_supply');

            const toggleBusinessFields = () => {
                const showBusiness = clientTypeSelect?.value === 'business';
                businessBlocks.forEach(block => {
                    block.style.display = showBusiness ? 'block' : 'none';
                });
            };

            toggleBusinessFields();
            clientTypeSelect?.addEventListener('change', toggleBusinessFields);

            const syncPlaceOfSupply = () => {
                if (!placeInput || !stateSelect) {
                    return;
                }

                if (!placeInput.value) {
                    placeInput.value = stateSelect.value;
                    placeInput.dataset.sync = 'true';
                    return;
                }
            };

            stateSelect?.addEventListener('change', () => {
                if (!placeInput) {
                    return;
                }

                if (placeInput.dataset.sync !== 'false') {
                    placeInput.value = stateSelect.value;
                    placeInput.dataset.sync = 'true';
                }
            });

            placeInput?.addEventListener('input', () => {
                if (!stateSelect) {
                    return;
                }

                placeInput.dataset.sync = placeInput.value === stateSelect.value ? 'true' : 'false';
            });

            syncPlaceOfSupply();
        });
    });
</script>

</div>
