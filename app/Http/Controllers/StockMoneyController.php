<?php

namespace App\Http\Controllers;

use App\Models\StockMoney;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StockMoneyController extends Controller
{
    public function index()
    {
        return Inertia::render('FrontEnd/Pathock/StockMoney', [

            // only auth user data
            'stocks' => StockMoney::with('user')
                ->where('user_id', Auth::id())
                ->latest()
                ->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        StockMoney::create([
            'user_id' => Auth::id(),
            'amount' => $data['amount'],
        ]);

        return back()->with('status', 'Stock Money Added');
    }

    public function update(Request $request, StockMoney $stockMoney)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $stockMoney->update([
            'amount' => $data['amount'],
        ]);

        return back()->with('status', 'Stock Money Updated');
    }

    public function destroy(StockMoney $stockMoney)
    {
        $stockMoney->delete();

        return back()->with('status', 'Deleted Successfully');
    }

    // admin function
    public function stockMoney()
    {
        return Inertia::render('Admin/Pathock/StockMoney', [
            'stocks' => StockMoney::with('user')->latest()->paginate(10),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $stock = StockMoney::findOrFail($id);
        $stock->status = $request->status;
        $stock->save();

        return back()->with('status', 'Status updated successfully');
    }
}
