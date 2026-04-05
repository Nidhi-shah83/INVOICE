<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('products.index', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'rate' => ['required', 'numeric', 'gte:0'],
            'gst_percent' => ['required', 'numeric', 'between:0,100'],
            'unit' => ['nullable', 'string', 'max:50'],
        ]);

        Product::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'rate' => $validated['rate'],
            'gst_percent' => $validated['gst_percent'],
            'unit' => $validated['unit'] ?? 'unit',
        ]);

        return redirect()
            ->route('products.index')
            ->with('status', 'Item saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeOwnership($product);

        $product->delete();

        return back()->with('status', 'Item removed.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeOwnership($product);

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeOwnership($product);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'rate' => ['required', 'numeric', 'gte:0'],
            'gst_percent' => ['required', 'numeric', 'between:0,100'],
            'unit' => ['nullable', 'string', 'max:50'],
        ]);

        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'rate' => $validated['rate'],
            'gst_percent' => $validated['gst_percent'],
            'unit' => $validated['unit'] ?? 'unit',
        ]);

        return redirect()
            ->route('products.index')
            ->with('status', 'Item updated.');
    }

    protected function authorizeOwnership(Product $product): void
    {
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
