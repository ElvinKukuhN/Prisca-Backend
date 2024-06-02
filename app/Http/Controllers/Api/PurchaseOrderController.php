<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{

    public function purchaseOrderCreate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'request_for_qoutations_id'      => 'required|unique:purchase_orders',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $purchaseOrders = PurchaseOrder::create([
            'request_for_qoutations_id' => $request->request_for_qoutations_id,
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
            ->select('purchase_orders.*')
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

    public function purchaseOrderGetById($id)
    {
        $purchaseOrders = PurchaseOrder::find($id);

        $lineItems = DB::table('purchase_orders as po')
            ->join('request_for_qoutations as rfq', 'rfq.id', '=', 'po.request_for_qoutations_id')
            ->join('quotations as quo', 'quo.request_for_qoutation_id', '=', 'rfq.id')
            ->join('users', 'rfq.user_id', '=', 'users.id')
            ->where('po.id', $id)
            ->select('quo.*', 'users.name as vendor_name')
            ->get();



        if ($purchaseOrders) {
            return response()->json([
                'message' => 'Success',
                'purchaseOrder' => [
                    'id' => $purchaseOrders->id,
                    'request_for_qoutations_id' => $purchaseOrders->request_for_qoutations_id,
                    'code' => $purchaseOrders->code,
                    'description' => $purchaseOrders->description,
                    'status' => $purchaseOrders->status,
                    'line_items' => $lineItems
                ]
            ], 200);
        } else {
            return response()->json([
                'message' => 'Purchase Order not found'
            ], 404);
        }
    }
}
