<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{

    public function purchaseOrderCreate(Request $request, $id)
    {

        $purchaseOrders = PurchaseOrder::create([
            'request_for_qoutations_id' => $id,
            'code' => "PO-" . date("Ymd") . rand(100, 999),
            'description' => $request->description,
            'status' => $request->status ?? 'draft'
        ]);

        if ($purchaseOrders) {
            return response()->json([
                'message' => 'Purchase Order created successfully',
                'purchaseOrder' => [
                    'id' => $purchaseOrders->id,
                    'request_for_qoutations_id' => $purchaseOrders->request_for_qoutations_id,
                    'code' => $purchaseOrders->code,
                    'description' => $purchaseOrders->description,
                    'status' => $purchaseOrders->status,
                ]
            ], 201);
        } else {
            return response()->json([
                'message' => 'Failed to create Purchase Order'
            ], 400);
        }
    }

    public function purchaseOrderGetByUserId()
    {
        $userId = auth()->user()->id;

        $purchaseOrders = PurchaseOrder::join('request_for_qoutations', 'purchase_orders.request_for_qoutations_id', '=', 'request_for_qoutations.id')
            ->join('purchase_requests', 'request_for_qoutations.purchase_request_id', '=', 'purchase_requests.id')
            ->where('purchase_requests.user_id', $userId)
            ->get();

        if ($purchaseOrders->isEmpty()) {
            return response()->json([
                'message' => 'Purchase Orders not found'
            ], 404);
        } else {
            return response()->json([
                'message' => 'Success',
                'purchaseOrders' => $purchaseOrders
            ], 200);
        }
    }

    public function purchaseOrderGetById ($id) {
        $purchaseOrders = PurchaseOrder::find($id);

        if ($purchaseOrders) {
            return response()->json([
                'message' => 'Success',
                'purchaseOrder' => [
                    'id' => $purchaseOrders->id,
                    'request_for_qoutations_id' => $purchaseOrders->request_for_qoutations_id,
                    'code' => $purchaseOrders->code,
                    'description' => $purchaseOrders->description,
                    'status' => $purchaseOrders->status,
                ]
            ], 200);
        } else {
            return response()->json([
                'message' => 'Purchase Order not found'
            ], 404);
        }
    }
}
