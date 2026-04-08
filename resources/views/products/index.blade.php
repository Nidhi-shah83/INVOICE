@extends('layouts.app')

@section('page-title', 'Items')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-semibold text-slate-900">Items</h1>
                <p class="text-sm text-slate-500">
                    Track the products and services that you swiftly add to quotes, orders and invoices.
                </p>
            </div>
        </div>

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1.35fr,0.65fr]">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Item catalog</h2>
                        <p class="text-sm text-slate-500">These entries are available in the quote builder.</p>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Built-in</span>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-[0.3em] text-slate-500">
                            <tr>
                                <th class="pb-3">Name</th>
                                <th class="pb-3">Rate</th>
                                <th class="pb-3">GST</th>
                                <th class="pb-3">Unit</th>
                                <th class="pb-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-700">
                            @forelse($products as $product)
                                <tr>
                                    <td class="py-3 font-medium text-slate-900">{{ $product->name }}</td>
                                    <td class="py-3">{{ number_format($product->rate, 2) }}</td>
                                    <td class="py-3">{{ number_format($product->gst_percent, 2) }}%</td>
                                    <td class="py-3">{{ $product->unit }}</td>
                                    <td class="py-3 text-right flex justify-end gap-2">
                                        <a
                                            href="{{ route('products.edit', $product) }}"
                                            class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-slate-700 transition hover:border-slate-300"
                                        >
                                            Edit
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('products.destroy', $product) }}"
                                            data-swal-confirm
                                            data-swal-title="Remove {{ $product->name }}?"
                                            data-swal-text="This item will be deleted from your catalog."
                                            data-swal-confirm-button="Remove item"
                                            data-swal-cancel-button="Cancel"
                                            data-swal-icon="warning"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-rose-200 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-rose-600 transition hover:border-rose-300 hover:text-rose-400">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-sm text-slate-500">No items yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2">
                    <h2 class="text-lg font-semibold text-slate-900">Add new item</h2>
                    <p class="text-sm text-slate-500">Just provide a name and price to make it available in quotes.</p>
                </div>

                <form method="POST" action="{{ route('products.store') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="name">Item name</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200"
                        >
                        @error('name')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="rate">Price</label>
                        <input
                            id="rate"
                            name="rate"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('rate') }}"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200"
                        >
                        @error('rate')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="gst_percent">GST%</label>
                        <input
                            id="gst_percent"
                            name="gst_percent"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            value="{{ old('gst_percent', 18) }}"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200"
                        >
                        @error('gst_percent')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="unit">Unit</label>
                        <input
                            id="unit"
                            name="unit"
                            type="text"
                            value="{{ old('unit', 'unit') }}"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200"
                        >
                        @error('unit')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="description">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-200"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold uppercase tracking-[0.3em] text-white transition hover:bg-slate-800"
                    >
                        Save item
                    </button>
                </form>
            </section>
        </div>
    </div>
@endsection
