<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PathockController extends Controller
{
    public function pathockRefferals()
    {
        $referrals = User::where('referred_by', auth()->id())
            ->latest()
            ->get();
        return Inertia::render('FrontEnd/Pathock/Referral', [
            'referrals' => $referrals
        ]);
    }

    public function pathockOrders()
    {
        $orders = Order::with([
            'items.product'
        ])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return Inertia::render('FrontEnd/Pathock/OrderList', [
            'orders' => $orders
        ]);
    }

    // invoice pdf download
    public function invoice($id)
    {
        $order = Order::with([
            'items.product',
            'user'
        ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return Inertia::render('FrontEnd/Pathock/Invoice', [
            'order' => $order
        ]);
    }

    public function invoicePdf($id)
    {
        $order = Order::with([
            'items.product',
            'user'
        ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.order', [
            'order' => $order
        ]);

        return $pdf->stream('invoice-' . $order->id . '.pdf');
    }
}
