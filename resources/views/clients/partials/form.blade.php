<div
    x-data="{
        clientType: @json(old('client_type', $client->client_type ?? 'individual')),
        state: @json(old('state', $client->state ?? '')),
        placeOfSupply: @json(old('place_of_supply', $client->place_of_supply ?? '')),
        manualPlace: @json((bool) old('place_of_supply', $client->place_of_supply ?? '')),
        init() {
            if (!this.placeOfSupply) {
                this.placeOfSupply = this.state;
            }
        }
    }"
    x-init="init"
    x-effect="if (!manualPlace) placeOfSupply = state"
    class="space-y-6"
>
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
                        x-model="clientType"
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
                    x-show="clientType === 'business'"
                    x-transition
                    style="display: none;"
                    class="space-y-1"
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
                x-show="clientType === 'business'"
                x-transition
                style="display: none;"
                class="space-y-1"
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
                    x-model="state"
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
                    x-model="placeOfSupply"
                    @input="manualPlace = true"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    required
                >
                @error('place_of_supply')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <template x-if="!manualPlace">
            <div x-effect="placeOfSupply = state"></div>
        </template>
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

</div>
