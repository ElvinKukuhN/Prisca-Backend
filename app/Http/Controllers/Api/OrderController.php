<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\CommercialInfo;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    //
        /**
     * Create a new order.
     *
     * @param Request $request The HTTP request object.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the created order or an error message.
     */
    public function create(Request $request)
    {

        $userId = auth()->user()->id;

        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $quoCollection = PurchaseOrder::join('request_for_qoutations as rfq', 'purchase_orders.request_for_qoutations_id', '=', 'rfq.id')
            ->join('quotations as quo', 'quo.request_for_qoutation_id', '=', 'rfq.id')
            ->join('users', 'rfq.user_id', '=', 'users.id')
            ->select('quo.id as id', 'quo.product_id as product_id', 'quo.quantity as quantity')
            ->where('purchase_orders.id', $request->purchase_order_id)
            ->get();

        // Loop through each quotation and update the corresponding commercial info

        $order = Order::create([
            'purchase_order_id' => $request->purchase_order_id,
            'user_id' => $userId,
            'code' => "SO-" . date("Ymd") . rand(100, 999),
            'status' => $request->status ?? 'dikemas'
        ]);


        if ($order) {
            foreach ($quoCollection as $quo) {
                $commercialInfo = CommercialInfo::where('product_id', $quo->product_id)->first();

                if ($commercialInfo) {
                    $new_quantity =  $commercialInfo->stock -  $quo->quantity;

                    $commercialInfo->stock = $new_quantity;
                    $commercialInfo->save();
                } else {
                    // Handle the case where no CommercialInfo was found
                    dd("No commercial information found for the given product_id: " . $quo->product_id);
                }
            }

            return response()->json([
                'message' => 'Order created successfully',
                'order' => [
                    'id' => $order->id,
                    'purchase_order_id' => $order->purchase_order_id,
                    'user_id' => $order->user_id,
                    'so_code' => $order->code,
                    'po_code' => $order->purchaseOrder->code,
                    'status' => $order->status
                ],
            ], 201);
        } else {
            return response()->json([
                'message' => 'Order not created'
            ], 404);
        }
    }

    public function index()
    {
        $user = auth()->user();

        $orders = Order::where('user_id', $user->id)->get();

        if ($orders) {
            return response()->json([
                'success' => true,
                'orders' => $orders
            ], 200);
        }
    }

    public function showVendor()
    {
        $userId = auth()->user()->id;

        $orders = DB::table('orders')
            ->join('purchase_orders as po', 'po.id', '=', 'orders.purchase_order_id')
            ->join('request_for_qoutations as rfq', 'rfq.id', '=', 'po.request_for_qoutations_id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('user_companies as comp', 'users.id', '=', 'comp.user_id')
            ->join('companies', 'comp.company_code', '=', 'companies.code')
            ->select('orders.id as id', 'orders.code as code', 'orders.status as status', 'companies.name as company_name', 'orders.created_at as waktu_order', 'orders.invoice_created')
            ->where('rfq.user_id', $userId)
            ->get();

        if ($orders) {
            return response()->json([
                'success' => true,
                'orders' => $orders
            ], 200);
        }
    }

    public function show($id)
    {
        $order = Order::find($id);

        $lineItems = DB::table('orders')
            ->join('purchase_orders as po', 'po.id', '=', 'orders.purchase_order_id')
            ->join('request_for_qoutations as rfq', 'rfq.id', '=', 'po.request_for_qoutations_id')
            ->join('quotations as quo', 'quo.request_for_qoutation_id', '=', 'rfq.id')
            ->join('users', 'rfq.user_id', '=', 'users.id')
            ->where('orders.id', $id)
            ->select('quo.*')
            ->get();

        $buyer = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('user_companies as comp', 'users.id', '=', 'comp.user_id')
            ->select('users.id as id', 'users.name as name', 'users.telp as telp', 'comp.address as alamat')
            ->where('orders.id', $id)
            ->first();

        $vendor = DB::table('orders')
            ->join('purchase_orders as po', 'po.id', '=', 'orders.purchase_order_id')
            ->join('request_for_qoutations as rfq', 'rfq.id', '=', 'po.request_for_qoutations_id')
            ->join('users', 'rfq.user_id', '=', 'users.id')
            ->join('master_vendors as mv', 'users.id', '=', 'mv.user_id')
            ->select('users.id as id', 'users.name as name', 'users.telp as telp', 'mv.alamat as alamat')
            ->where('orders.id', $id)
            ->first();

        $data = [
            'success' => true,
            'orders' => [
                'id' => $order->id,
                'purchase_order_id' => $order->purchase_order_id,
                'user' => $buyer,
                'vendor' => $vendor,
                'so_code' => $order->code,
                'po_code' => $order->purchaseOrder->code,
                'status' => $order->status,
                'tanggal_order' => $order->created_at,
                'line_items' => $lineItems
            ],

        ];

        return response()->json($data, 200);
    }
}
