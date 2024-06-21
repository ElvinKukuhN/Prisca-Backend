<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class Shipment_Controller extends Controller
{
    //
    public function index()
    {
        //
        $user = auth()->user();

        $shipment = Shipment::where('user_id', $user->id)->get();

        if ($shipment) {
            return response()->json([
                'success' => true,
                'shipment' => $shipment
            ], 200);
        }
    }

    public function create(Request $request)
    {
        //
        $userId = auth()->user()->id;

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|unique:shipments',
            'no_resi' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $shipment = Shipment::create([
            'order_id' => $request->order_id,
            'user_id' => $userId,
            'no_resi' => $request->no_resi,
        ]);

        $status_order = Order::where('id', $request->order_id)->first();
        $status_order->status = $request->status ?? 'dikirim';
        $status_order->save();

        if ($shipment) {
            $status_order;
            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan data',
                'shipment' => [
                    'id' => $shipment->id,
                    'order_id' => $shipment->order_id,
                    'no_resi' => $shipment->no_resi,
                    'created_at' => $shipment->created_at,
                    'status' => $shipment->order->status
                ]
            ], 200);
        }
    }


    public function show($id)
    {
        $shipment = Shipment::where('order_id', $id)->first();

        if ($shipment) {
            // Membuat URL lengkap untuk gambar bukti
            $imageUrl = url('images/' . $shipment->bukti);

            return response()->json([
                'success' => true,
                'shipment' => [
                    'id' => $shipment->id,
                    'order_id' => $shipment->order_id,
                    'no_resi' => $shipment->no_resi,
                    'created_at' => $shipment->created_at,
                    'status' => $shipment->order->status,
                    'bukti' => $imageUrl // Menggunakan URL gambar lengkap
                ]
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data shipment tidak ditemukan'
            ], 404);
        }
    }

    public function showResiBuyer($id)
    {
        $order = Order::where('id', $id)->first();

        if ($order && $order->shipment) {
            $imageUrl = url('images/' . $order->shipment->bukti);

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'no_resi' => $order->shipment->no_resi,
                    'bukti' => $imageUrl
                ]
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data shipment tidak ditemukan'
            ], 404);
        }
    }


    public function buktiDiterima(Request $request, $id)
    {
        $shipment = Shipment::where('order_id', $id)->first();

        if (!$shipment) {
            return response()->json(['error' => 'Shipment not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'bukti' => 'required|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $imageName = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $imageName);
            $images = $imageName;

            $shipment->bukti = $images;
            $shipment->save();

            $imageUrl = url('images/' . $images);

            $status_order = Order::where('id', $id)->first();
            $status_order->status = $request->status ?? 'selesai';
            $status_order->save();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan data',
                'shipment' => $shipment,
                'image_url' => $imageUrl
            ], 200);
        } else {
            return response()->json(['error' => 'File not uploaded'], 400);
        }
    }
}
