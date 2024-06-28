<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
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

    public function getCart()
    {
        try {
            $cart = Cart::where('user_id', auth()->user()->id)->get();

            return response()->json([
                'cart' => $cart->map(function ($item) {
                    $product = $item->product;
                    $quantity = $item->quantity;
                    $commercialInfo = $product->commercialInfo;

                    if ($commercialInfo->grosir->price != null && $commercialInfo->grosir->qty != null) {
                        $price = $quantity >= $commercialInfo->grosir->qty ? $commercialInfo->grosir->price : $commercialInfo->price;
                    } else {
                        $price = $commercialInfo->price;
                    }

                    $totalPrice = $price * $quantity;

                    return [
                        'id' => $item->id,
                        'name' => $product->name,
                        'user_id' => $item->user_id,
                        'product_id' => $product->id,
                        'vendor' => $product->user->name,
                        'quantity' => $quantity,
                        'stock' => $commercialInfo->stock,
                        'price' => $price,
                        'total_price' => $totalPrice
                    ];
                })
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'message' => $e->getMessage()
                ],
                500
            );
        }
    }

    public function removeCart($id)
    {
        try {
            $cart = Cart::find($id);

            if (!$cart) {
                return response()->json([
                    'message' => 'product not found'
                ], 400);
            }

            $cart->delete();

            return response()->json([
                'message' => 'Product removed from cart successfully'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function updateCart(Request $request, $id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json([
                'message' => 'product not found'
            ], 400);
        }

        $cart->quantity = $request->quantity;
        $cart->save();

        $product = $cart->product;
        $commercialInfo = $product->commercialInfo;
        $quantity = $cart->quantity;

        if ($commercialInfo->grosir->price != null && $commercialInfo->grosir->qty != null) {
            $price = $quantity >= $commercialInfo->grosir->qty ? $commercialInfo->grosir->price : $commercialInfo->price;
        } else {
            $price = $commercialInfo->price;
        }
        $totalPrice = $price * $quantity;

        return response()->json([
            'message' => 'Cart updated successfully',
            'cart' => [
                'id' => $cart->id,
                'name' => $product->name,
                'user_id' => $cart->user_id,
                'product_id' => $product->id,
                'vendor' => $product->user->name,
                'quantity' => $quantity,
                'price' => $price,
                'total_price' => $totalPrice
            ]
        ], 200);
    }
}
