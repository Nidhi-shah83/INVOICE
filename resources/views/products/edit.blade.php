@extends('layouts.app')

@section('page-title', 'Edit Item')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Edit item</h1>
                    <p class="text-sm text-slate-500">Adjust the name, price or GST details for the catalog entry.</p>
                </div>
                <a href="{{ route('products.index') }}" class="text-sm text-slate-500 hover:text-slate-900">Back to list</a>
            </div>

            <form method="POST" action="{{ route('products.update', $product) }}" class="mt-6 space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="text-sm font-semibold text-slate-700" for="name">Item name</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $product->name) }}"
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
                        value="{{ old('rate', $product->rate) }}"
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
                        value="{{ old('gst_percent', $product->gst_percent) }}"
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
                        value="{{ old('unit', $product->unit) }}"
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
                    >{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('products.index') }}" class="rounded-full border border-slate-200 px-5 py-2 text-sm font-semibold uppercase tracking-[0.3em] text-slate-600 transition hover:border-slate-300">Cancel</a>
                    <button
                        type="submit"
                        class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold uppercase tracking-[0.3em] text-white transition hover:bg-slate-800"
                    >
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
