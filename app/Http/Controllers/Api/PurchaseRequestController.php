<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\LineItem;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Http\Controllers\Controller;

class PurchaseRequestController extends Controller
{

    public function createPurchaseRequest(Request $request)
    {
        $purchaseRequest = PurchaseRequest::create([
            'user_id' => auth()->user()->id,
            'code' => "PR-" . date("Ymd") . rand(100, 999),
            'description' => $request->description,
            'status' => $request->status ?? 'draft'
        ]);

        if ($purchaseRequest) {
            if ($request->has('lineItems') && is_array($request->lineItems) && count($request->lineItems) > 0) {
                $lineItemsData = [];
                foreach ($request->lineItems as $lineItem) {
                    $lineItemModel = new LineItem([
                        'purchase_request_id' => $purchaseRequest->id,
                        'product_id' => $lineItem['product_id'],
                        'quantity' => $lineItem['quantity'],
                        'name' => $lineItem['name'],
                        'price' => $lineItem['price']
                    ]);
                    $lineItemModel->save();
                    $lineItemsData[] = $lineItemModel;
                }

                if ($lineItemsData) {
                    $productIds = array_column($lineItemsData, 'product_id');
                    $deleteCart = Cart::where('user_id', auth()->user()->id)
                        ->whereIn('product_id', $productIds)
                        ->delete();
                }

                return response()->json([
                    'message' => 'Purchase Request created successfully',
                    'purchaseRequest' => [
                        'id' => $purchaseRequest->id,
                        'user_id' => $purchaseRequest->user_id,
                        'code' => $purchaseRequest->code,
                        'description' => $purchaseRequest->description,
                        'status' => $purchaseRequest->status,
                    ],
                    'lineItems' => $lineItemsData
                ], 201);
            } else {
                // Jika tidak ada line items yang diberikan
                $purchaseRequest->delete();
                return response()->json([
                    'message' => 'No line items provided'
                ], 400);
            }
        }

        return response()->json([
            'message' => 'Failed to create Purchase Request'
        ], 400);
    }

    public function getPurchaseRequestByUserId()
    {
        $purchaseRequests = PurchaseRequest::where('user_id', auth()->user()->id)->with('lineItems')->get();

        return response()->json([
            'purchaseRequests' => $purchaseRequests
        ], 200);
    }

    public function getPurchaseRequestById($id)
    {
        $purchaseRequest = PurchaseRequest::where('id', $id)->first();

        $lineItems = $purchaseRequest->lineItems;

        $lineItemsData = [];
        foreach ($lineItems as $lineItem) {
            if ($lineItem->purchase_request_id === $id) {
                $lineItemsData[] = [
                    'id' => $lineItem->id,
                    'purchase_request_id' => $lineItem->purchase_request_id,
                    'vendor_id' => $lineItem->product->user_id,
                    'vendor_name' => $lineItem->product->user->name,
                    'product_name' => $lineItem->product->name,
                    'product_id' => $lineItem->product_id,
                    'quantity' => $lineItem->quantity,
                    'name' => $lineItem->name,
                    'price' => $lineItem->price
                ];
            }
        }

        return response()->json([
            'message' => 'Success',
            'purchaseRequest' => [
                'id' => $purchaseRequest->id,
                'user_id' => $purchaseRequest->user_id,
                'code' => $purchaseRequest->code,
                'description' => $purchaseRequest->description,
                'status' => $purchaseRequest->status,
                'lineItems' => $lineItemsData
            ]
        ], 200);
    }

    public function getLineItem($id)
    {
        $lineItem = LineItem::where('id', $id)->with('product')->first();

        return response()->json([
            'lineItem' => $lineItem
        ], 200);
    }

    public function updateLineItem(Request $request, $id)
    {
        try {
            $lineItem = LineItem::findOrFail($id);

            $lineItem->quantity = $request->input('quantity');
            $lineItem->save();

            $product = $lineItem->product;
            $commercialInfo = $product->commercialInfo;

            $price = $commercialInfo->price;
            if ($commercialInfo->grosir && $commercialInfo->grosir->price && $commercialInfo->grosir->qty) {
                if ($lineItem->quantity >= $commercialInfo->grosir->qty) {
                    $price = $commercialInfo->grosir->price;
                }
            }

            $totalPrice = $price * $lineItem->quantity;

            $lineItem->price = $totalPrice;
            $lineItem->save();

            return response()->json([
                'message' => 'Line Item updated successfully',
                'lineItem' => $lineItem
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating Line Item: ' . $e->getMessage()
            ], 500);
        }
    }

}
