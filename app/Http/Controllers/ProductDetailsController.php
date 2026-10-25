<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductDetailsController extends Controller
{
    public function show(Product $product)
    {
        return Inertia::render('Admin/Product/Show', [
            'product' => $product
        ]);
    }
}
