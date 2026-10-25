<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shop;
use App\Models\StockMoney;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function create()
    {
        $user = auth()->user();

        $carts = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        $stockMoney = StockMoney::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount');

        return Inertia::render('Admin/Order/Order', [
            'carts' => $carts,
            'shops' => Shop::all(),

            'totalBalance' => (float) $user->total_balance,
            'stockMoney' => (float) $stockMoney,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'delivery_type' => 'required|in:inside_dhaka,outside_dhaka,shop',
            'payment_method' => 'required|string',
            'email' => 'nullable|email',
            'shop_id' => 'nullable|required_if:delivery_type,shop|exists:shops,id',

            'use_balance' => 'nullable|boolean',
            'use_stock_money' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        $carts = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($carts->isEmpty()) {
            return back()->withErrors([
                'cart' => 'Cart is empty'
            ]);
        }

        // subtotal
        $subtotal = $carts->sum(function ($cart) {
            return $cart->product->discount_price * $cart->quantity;
        });

        // delivery
        $delivery = 0;

        if ($request->delivery_type == 'outside_dhaka') {
            $delivery = 100;
        }

        $total = $subtotal + $delivery;

        // original total
        $grandTotal = $total;

        // track deductions
        $balanceUsed = 0;
        $stockMoneyUsed = 0;

        /*
        |--------------------------------------------------------------------------
        | USE BALANCE (MAX 30%)
        |--------------------------------------------------------------------------
        */

        if ($request->use_balance) {

            $maxBalanceUse = $grandTotal * 0.30;

            $balanceUsed = min(
                $user->total_balance,
                $maxBalanceUse
            );

            $total -= $balanceUsed;
        }

        /*
        |--------------------------------------------------------------------------
        | USE STOCK MONEY
        |--------------------------------------------------------------------------
        */

        if ($request->use_stock_money) {

            $approvedStock = StockMoney::where('user_id', $user->id)
                ->where('status', 'approved')
                ->get();

            $availableStock = $approvedStock->sum('amount');

            $stockMoneyUsed = min(
                $availableStock,
                $total
            );

            $total -= $stockMoneyUsed;
        }

        // create order
        $order = Order::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $request->email,
            'phone' => $user->phone,

            'delivery_type' => $request->delivery_type,
            'shop_id' => $request->shop_id,
            'payment_method' => $request->payment_method,

            'subtotal' => $subtotal,
            'delivery_charge' => $delivery,
            'total' => $total,
            'balance_used' => $balanceUsed,
            'stock_money_used' => $stockMoneyUsed,
        ]);

        if ($balanceUsed > 0) {
            $user->total_balance -= $balanceUsed;
            $user->save();
        }
        if ($stockMoneyUsed > 0) {
            $remaining = $stockMoneyUsed;
            $stocks = StockMoney::where('user_id', $user->id)
                ->where('status', 'approved')
                ->orderBy('id')
                ->get();

            foreach ($stocks as $stock) {
                if ($remaining <= 0) {
                    break;
                }
                if ($stock->amount <= $remaining) {
                    $remaining -= $stock->amount;
                    $stock->amount = 0;
                } else {
                    $stock->amount -= $remaining;
                    $remaining = 0;
                }
                $stock->save();
            }
        }

        // order items
        foreach ($carts as $cart) {

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cart->product_id,
                'price' => $cart->product->discount_price,
                'quantity' => $cart->quantity,
            ]);
        }

        // DELETE ONLY USER CART
        Cart::where('user_id', $user->id)->delete();

        return redirect()
            ->route('cart.index')
            ->with('status', 'Order placed successfully');
    }

    private function distributeGenerationCommission($user, $amount)
    {
        // 10 generation levels
        $levels = [10, 9, 8, 7, 6, 5, 4, 3, 2, 1];

        $currentUser = $user;

        foreach ($levels as $percent) {

            if (!$currentUser->referred_by) {
                break;
            }

            $parent = User::find($currentUser->referred_by);

            if (!$parent) {
                break;
            }

            $commission = ($amount * $percent) / 100;

            // generation commission
            $parent->generation_commission += $commission;

            // marketing wallet
            $parent->marketing_wallet += $commission;

            // total earning
            $parent->total_earning += $commission;

            $parent->save();

            $currentUser = $parent;
        }
    }

    private function processMarketingWallet($user)
    {
        while ($user->marketing_wallet >= 100) {

            // process only 100
            $processAmount = 100;

            // 70%
            $balanceAmount = ($processAmount * 70) / 100;

            // 30%
            $generationAmount = ($processAmount * 30) / 100;

            // add balance
            $user->total_balance += $balanceAmount;

            // subtract only 100
            $user->marketing_wallet -= $processAmount;

            $user->save();

            // generation distribution
            $this->distributeGenerationCommission(
                $user,
                $generationAmount
            );

            // refresh
            $user->refresh();
        }
    }

    // admin 
    public function index()
    {
        $orders = Order::with([
            'shop',
            'items.product'
        ])->latest()->paginate(10);

        return Inertia::render('Admin/Order/List', [
            'orders' => $orders
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:processing,delivered,canceled',
        ]);

        // update status
        $order->status = $request->status;
        $order->save();

        /*
        |--------------------------------------------------------------------------
        | COMMISSION ONLY ONE TIME
        |--------------------------------------------------------------------------
        */

        if (
            $request->status == 'delivered'
            && !$order->commission_processed
        ) {

            $user = $order->user;

            // load relation
            $order->load('items.product');

            $totalCommission = 0;

            foreach ($order->items as $item) {

                $product = $item->product;

                // percentage
                $percentage = $product->percentage ?? 0;

                // subtotal
                $subtotal = $item->price * $item->quantity;

                // commission
                $commission = ($subtotal * $percentage) / 100;

                $totalCommission += $commission;
            }

            // add wallet
            $user->marketing_wallet += $totalCommission;

            // total earning
            $user->total_earning += $totalCommission;

            $user->save();

            // process wallet
            $this->processMarketingWallet($user);

            // prevent duplicate commission
            $order->commission_processed = true;
            $order->save();
        }

        return back()->with(
            'status',
            'Order status updated successfully'
        );
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return back()->with('status', 'Order deleted');
    }
}
