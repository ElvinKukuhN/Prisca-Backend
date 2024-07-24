<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CommercialInfo;
use App\Models\Pengembalian;
use Illuminate\Support\Facades\Validator;

class PengembalianController extends Controller
{
    public function ajuanPengembalian(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pengembalians' => 'required|array',
                'pengembalians.*.order_id' => 'required|exists:orders,id',
                'pengembalians.*.product_id' => 'required|exists:products,id',
                'pengembalians.*.quantity' => 'required|integer|min:1',
                'pengembalians.*.reason' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $pengembalians = [];
            foreach ($request->pengembalians as $pengembalianRequest) {
                $pengembalian = Pengembalian::create([
                    'order_id' => $pengembalianRequest['order_id'],
                    'product_id' => $pengembalianRequest['product_id'],
                    'quantity' => $pengembalianRequest['quantity'],
                    'reason' => $pengembalianRequest['reason'],
                    'status' => $pengembalianRequest['status'] ?? 'pending',
                ]);
                $pengembalians[] = $pengembalian;
            }

            return response()->json([
                'message' => 'Returns created successfully',
                'pengembalians' => $pengembalians
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Returns failed to create with error: ' . $th->getMessage()
            ], 400);
        }
    }

    /*
    * get data barang yang di retur berdasarkan order id
    */
    public function getPengembalianByOrderId($id)
    {
        try {
            $pengembalians = Pengembalian::where('order_id', $id)->get();

            if ($pengembalians->isEmpty()) {
                return response()->json([
                    'message' => 'No returns found for this order ID'
                ], 404);
            }

            $pengembalians = $pengembalians->map(function ($pengembalian) {
                return [
                    'id' => $pengembalian->id,
                    'order_id' => $pengembalian->order_id,
                    'product_id' => $pengembalian->product_id,
                    'product_name' => $pengembalian->product->name,
                    'quantity' => $pengembalian->quantity,
                    'reason' => $pengembalian->reason,
                    'status' => $pengembalian->status,
                    'created_at' => $pengembalian->created_at,
                    'updated_at' => $pengembalian->updated_at
                ];
            });

            return response()->json([
                'message' => 'Returns retrieved successfully',
                'pengembalians' => $pengembalians
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to retrieve returns with error: ' . $th->getMessage()
            ], 400);
        }
    }

    /*
    *Update status pengembalian untuk setiap barang yang diajukan
    */

    public function updateStatusByOrderId(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'statuses' => 'required|array',
                'statuses.*.product_id' => 'required|exists:products,id',
                'statuses.*.status' => 'required|string|in:pending,approved,rejected',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            // Temukan semua pengembalian berdasarkan order_id
            $pengembalians = Pengembalian::where('order_id', $id)->get();

            if ($pengembalians->isEmpty()) {
                return response()->json([
                    'message' => 'No returns found for this order ID'
                ], 404);
            }

            // Buat array untuk memetakan product_id ke status
            $statusMap = collect($request->statuses)->keyBy('product_id');

            $updatedPengembalians = [];
            foreach ($pengembalians as $pengembalian) {
                // Jika status untuk product_id ini ada dalam array request, perbarui statusnya
                if ($statusMap->has($pengembalian->product_id)) {
                    $pengembalian->status = $statusMap->get($pengembalian->product_id)['status'];
                    $pengembalian->save();
                    $updatedPengembalians[] = $pengembalian;
                }
            }

            return response()->json([
                'message' => 'Returns status updated successfully',
                'pengembalians' => $updatedPengembalians
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to update returns status with error: ' . $th->getMessage()
            ], 400);
        }
    }

    /*
    *Menggganti barang yang telah di setujui
    */
    public function replaceReturnedItems(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
                'replacements' => 'required|array',
                // 'replacements.*.pengembalian_id' => 'required|exists:pengembalians,id',
                'replacements.*.product_id' => 'required|exists:products,id',
                // 'replacements.*.quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $replacements = $request->replacements;
            $updatedPengembalians = [];

            foreach ($replacements as $replacement) {
                $pengembalian = Pengembalian::where('product_id', $replacement['product_id'])
                    ->where('status', 'approved')->first();

                if (!$pengembalian || $pengembalian->order_id != $request->order_id) {
                    return response()->json([
                        'message' => 'Invalid return request'
                    ], 400);
                }

                $pengembalian->status = 'completed';
                $pengembalian->save();

                if ($pengembalian) {
                    $replacementProduct = CommercialInfo::where('product_id', $replacement['product_id'])->first();
                    $replacementProduct->stock = $replacementProduct->stock - $pengembalian->quantity;
                    $replacementProduct->save();
                }

                $dataMap = [
                    'pengembalian_id' => $pengembalian->id,
                    'product_id' => $pengembalian->product_id,
                    'product_name' => $pengembalian->product->name,
                    'quantity' => $pengembalian->quantity,
                    'reason' => $pengembalian->reason,
                    'status' => $pengembalian->status
                ];

                $updatedPengembalians[] = $dataMap;
            }

            return response()->json([
                'message' => 'Returns replaced successfully',
                'data' => [
                    'order_id' => $request->order_id,
                    'pengembalians' => $updatedPengembalians
                ]
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to replace returns status with error: ' . $th->getMessage()
            ], 400);
        }
    }
}
