<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckoutController extends Controller
{

    public function addToCart(Request $request)
    {

        $cart = Cart::create([
            'user_id' => auth()->user()->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity
        ]);
        return response()->json([
            'message' => 'Product added to cart successfully',
            'cart' => $cart
        ], 201);
    }
}
