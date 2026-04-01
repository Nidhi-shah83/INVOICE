<div class="space-y-5">
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

    <div>
        <label class="block text-sm font-semibold text-slate-700" for="email">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $client->email ?? '') }}"
            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            required
        >
        @error('email')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-semibold text-slate-700" for="phone">Phone</label>
            <input
                id="phone"
                name="phone"
                type="text"
                value="{{ old('phone', $client->phone ?? '') }}"
                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
            @error('phone')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700" for="gstin">GSTIN</label>
            <input
                id="gstin"
                name="gstin"
                type="text"
                value="{{ old('gstin', $client->gstin ?? '') }}"
                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm uppercase tracking-[0.2em] focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
            @error('gstin')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>
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
        <label class="block text-sm font-semibold text-slate-700" for="address">Address</label>
        <textarea
            id="address"
            name="address"
            rows="3"
            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
        >{{ old('address', $client->address ?? '') }}</textarea>
        @error('address')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>
</div>
