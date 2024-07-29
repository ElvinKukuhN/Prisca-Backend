<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\RequestForQoutation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class Payment__Controller extends Controller
{
    //
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|unique:payments',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $payment = Payment::create([
            'order_id' => $request->order_id,
            'no_invoice' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
            'status' => $request->status ?? 'pending', // pending, success
        ]);

        if ($payment) {
            // Update invoice_created column in orders table
            $order = Order::find($request->order_id);
            $order->invoice_created = true;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan data',
                'payment' => $payment
            ]);
        }
    }

    public function show($id)
    {
        $payment = Payment::where('order_id', $id)->first();

        $batas_bayar = Carbon::parse($payment->created_at)->addDays(14);

        $order = Order::find($payment->order_id);
        $rfqId = $order->purchaseOrder->request_for_qoutations_id;
        $rfq = RequestForQoutation::find($rfqId);
        $companyAddress = $rfq->company_address;

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
            ->select('users.id as id', 'users.name as name', 'users.telp as telp')
            ->where('orders.id', $id)
            ->first();

        $vendor = DB::table('orders')
            ->join('purchase_orders as po', 'po.id', '=', 'orders.purchase_order_id')
            ->join('request_for_qoutations as rfq', 'rfq.id', '=', 'po.request_for_qoutations_id')
            ->join('users', 'rfq.user_id', '=', 'users.id')
            ->join('master_vendors as mv', 'users.id', '=', 'mv.user_id')
            ->select('users.id as id', 'users.name as name', 'users.telp as telp', 'mv.alamat as alamat', 'mv.bank as bank', 'mv.rekening as no_rek','rfq.harga_ongkir as harga_ongkir')
            ->where('orders.id', $id)
            ->first();

        $total_bayar = $lineItems->sum('amount') + $vendor->harga_ongkir;


        $pdfName = url('pdf/invoice/' . $payment->invoice_pdf) ?? null;

        $buyerMap = [
            'id' => $buyer->id,
            'name' => $buyer->name,
            'telp' => $buyer->telp,
            'alamat' => $companyAddress
        ];

        if ($payment) {
            return response()->json([
                'success' => true,
                'message' => 'Berhasil menampilkan data',
                'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'order_id' => $payment->order_id,
                        'no_invoice' => $payment->no_invoice,
                        'bukti' => url('images/' . $payment->bukti) ?? null,
                        'status' => $payment->status,
                        'total_bayar' => $total_bayar,
                        'batas_bayar' => $batas_bayar->format('d-m-Y'),
                        'pdf_url' => $pdfName,
                        'created_at' => $payment->created_at
                    ],
                    'code' => [
                        'so_code' => $payment->order->code,
                        'po_code' => $payment->order->purchaseOrder->code
                    ],
                    'buyer' => $buyerMap,
                    'vendor' => $vendor,
                    'line_items' => $lineItems,
                ]
            ]);
        }
    }

    public function sendInvoice($id)
    {
        $payment = Payment::where('order_id', $id)->first();

        $batas_bayar = Carbon::parse($payment->created_at)->addDays(14);

        $order = Order::find($payment->order_id);
        $rfqId = $order->purchaseOrder->request_for_qoutations_id;
        $rfq = RequestForQoutation::find($rfqId);
        $companyAddress = $rfq->company_address;


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
            ->select('users.id as id', 'users.name as name', 'users.telp as telp', 'mv.alamat as alamat', 'mv.bank as bank', 'mv.rekening as no_rek' ,'rfq.harga_ongkir as harga_ongkir')
            ->where('orders.id', $id)
            ->first();

        $total_bayar = $lineItems->sum('amount') + $vendor->harga_ongkir;


        // Load view PDF dengan data quotation
        $pdf = PDF::loadView('pdf.invoice', compact('payment', 'batas_bayar', 'lineItems', 'buyer', 'vendor', 'total_bayar', 'companyAddress'));

        // Simpan PDF ke dalam direktori lokal
        $pdfPath = public_path('pdf/invoice'); // Direktori untuk menyimpan PDF
        $pdfName = 'invoice_' . $payment->no_invoice . '.pdf';
        $pdf->save($pdfPath . '/' . $pdfName); // Menyimpan PDF ke direktori lokal

        if ($payment) {
            $payment->invoice_pdf = $pdfName;
            $payment->save();
        }

        $buyerMap = [
            'id' => $buyer->id,
            'name' => $buyer->name,
            'telp' => $buyer->telp,
            'alamat' => $companyAddress
        ];

        // Return response API
        return response()->json([
            'message' => 'Invoice PDF berhasil dibuat dan dikirim ke pembeli.',
            'pdf_url' => url('pdf/invoice/' . $pdfName), // URL untuk mengakses PDF
            'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'order_id' => $payment->order_id,
                        'no_invoice' => $payment->no_invoice,
                        'bukti' => url('images/' . $payment->bukti) ?? null,
                        'status' => $payment->status,
                        'total_bayar' => $total_bayar,
                        'batas_bayar' => $batas_bayar->format('d-m-Y'),
                        'pdf_url' => $pdfName,
                        'created_at' => $payment->created_at
                    ],
                    'code' => [
                        'so_code' => $payment->order->code,
                        'po_code' => $payment->order->purchaseOrder->code
                    ],
                    'buyer' => $buyerMap,
                    'vendor' => $vendor,
                    'line_items' => $lineItems,
                ]
        ]);
    }

    public function buktiSend(Request $request, $id)
    {
        $payment = Payment::where('order_id', $id)->first();

        if (!$payment) {
            return response()->json(['error' => 'Not found'], 404);
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

            $payment->bukti = $images;
            $payment->save();

            $imageUrl = url('images/' . $images);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan data',
                'payment' => $payment,
                'image_url' => $imageUrl
            ], 200);
        } else {
            return response()->json(['error' => 'File not uploaded'], 400);
        }
    }

    public function makeSuccess($id)
    {
        $payment = Payment::where('order_id', $id)->first();

        if (!$payment) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Periksa jika atribut 'bukti' pada $payment tidak ada
        if (isset($payment->bukti)) {
            $payment->status = 'success';
            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengubah status',
                'payment' => $payment
            ], 200);
        } else {
            // Jika atribut 'bukti' ada, berikan respons sesuai kebutuhan
            return response()->json([
                'success' => false,
                'message' => 'Gagal Mengubah Status',
                'payment' => $payment
            ], 400);
        }
    }
}
