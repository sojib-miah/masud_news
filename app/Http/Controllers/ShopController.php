<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Shop/Shop', [
            'shops' => Shop::latest()->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required',
        ]);

        Shop::create($request->all());

        return redirect()->back()->with('status', 'Shop created!');
    }

    public function update(Request $request, Shop $shop)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required',
        ]);

        $shop->update($request->all());

        return redirect()->back()->with('status', 'Shop updated!');
    }

    public function destroy(Shop $shop)
    {
        $shop->delete();

        return redirect()->back()->with('status', 'Deleted!');
    }
}
