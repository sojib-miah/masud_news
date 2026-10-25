<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Product/Products', [
            'products' => Product::latest()->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'croy' => 'required|numeric|min:0',
            'dipo' => 'required|numeric|min:0',
            'dillar' => 'nullable|string|max:255',
            'sub_dillar' => 'nullable|string|max:255',
            'percentage' => 'nullable|integer|min:0|max:100',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('images/products', 'public');
        }

        Product::create($data);

        return back()->with('status', 'Product Created');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'croy' => 'required|numeric|min:0',
            'dipo' => 'required|numeric|min:0',
            'dillar' => 'nullable|string|max:255',
            'sub_dillar' => 'nullable|string|max:255',
            'percentage' => 'nullable|integer|min:0|max:100',
        ]);

        // If new image uploaded
        if ($request->hasFile('image')) {

            // Delete old image (if exists)
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            // Store new image
            $data['image'] = $request->file('image')->store('images/products', 'public');
        }

        $product->update($data);

        return back()->with('status', 'Product Updated');
    }

    public function destroy(Product $product)
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return back()->with('status', 'Deleted');
    }
}
