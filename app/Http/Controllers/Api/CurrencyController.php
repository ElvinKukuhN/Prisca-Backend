<?php

namespace App\Http\Controllers\Api;

use App\Models\Currency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CurrencyController extends Controller
{
    //


    public function index()
    {
        $currency = Currency::all();

        return response()->json([
            'success' => true,
            'currency' => $currency
        ]);
    }

    public function show($id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'currency' => $currency
        ]);
    }

    public function store(Request $request)
    {
        $currency = new Currency();
        $currency->name = $request->name;
        $currency->symbol = $request->symbol;
        $currency->save();

        return response()->json([
            'success' => true,
            'currency' => $currency
        ]);
    }

    public function update(Request $request, $id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found'
            ], 400);
        }

        $currency->name = $request->name;
        $currency->symbol = $request->symbol;
        $currency->save();

        return response()->json([
            'success' => true,
            'currency' => $currency
        ]);
    }

    public function destroy($id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found'
            ], 400);
        }

        $currency->delete();

        return response()->json([
            'success' => true,
            'message' => 'Currency deleted'
        ]);
    }
}
