<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Shop/Shop', [
            'shops' => Shop::latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
        ]);

        Shop::create($validated);

        return redirect()->back()->with('status', 'Shop created successfully!');
    }

    public function update(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
        ]);

        $shop->update($validated);

        return redirect()->back()->with('status', 'Shop updated successfully!');
    }

    public function destroy(Shop $shop)
    {
        Order::where('shop_id', $shop->id)->update(['shop_id' => null]);
        $shop->delete();

        return redirect()->back()->with('status', 'Shop deleted successfully!');
    }
}
